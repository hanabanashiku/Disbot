<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 8/24/18
 * Time: 6:10 PM
 */

namespace Disbot\Server;

use Disbot\Handlers;

class Gateway {
	const VERSION = 6; // The Discord gateway version to request
	const ENCODING = 'json';
	const OAUTH_URL = 'https://discordapp.com/api/oauth2/authorize?'; // the OAuth URL
	const GATEWAY_URL = 'https://discordapp.com/gateway/bot'; // The URL used to fetch the Gateway address
	const MAX_LENGTH = 4096; // Max number of bytes that can be sent

	private $address;
	private $socket;
	private $token; // bot's token
	private $client_id; // the bot's client ID
	private $permissions;

	/**
	 * @param $client_id int The client ID assigned by Discord
	 * @param $token int The bot token assigned by Discord
	 * @param $permissions int The bot's permissions
	 */
	public function __construct($client_id, $token, $permissions) {
		$this->client_id = VERBOSE;
		$this->token = $token;
		$this->permissions = $permissions;
	}

	/**
	 * This is the URL used to authenticate the client with Discord
	 * @return string The URL the user must navigate to
	 */
	public function getAuthUrl(){
		return $this::OAUTH_URL."client_id={$this->client_id}&scope=bot&permissions={$this->permissions}";
	}

	/**
	 * Fetches the gateway URL the socket must connect to
	 */
	private function getGatewayUrl(){
		$ch = curl_init($this::GATEWAY_URL);
		curl_setopt($ch, CURLOPT_HTTPHEADER, "Authorization: Bot {$this->token}");
		$response = json_decode(curl_exec($ch));
		if($response["url"] == null) die("Could not retrieve Gateway socket URL");
		$this->address = $response["url"]."?v={$this::VERSION}&encoding={$this::ENCODING}";
	}

	/**
	 * Signals for the client to create a socket and begin communicating with the gateway.
	 */
	public function listen(){
		if(is_null($this->token)){
			print("To start the server, a valid token must be supplied. Get the token by running `disbot auth.` See help for more information\n");
			exit(2);
		}

		$this->getGatewayUrl();

		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("Socket could not be created\n");
		socket_bind($this->socket, '127.0.0.1');
		socket_connect($this->socket, gethostbyname($this->address), 443);

		// lets start our main loop
		while(true){
			$data = socket_read($this->socket, $this::MAX_LENGTH);
			if($data === false){
				$err = socket_last_error($this->socket);
				die("Connection closed! [$err] ".Gateway::$CloseCodes[$err]);
			}
			Handlers\ReceiveSocketMessage($data, $this);
		}
	}

	/**
	 * Sends a payload through the socket
	 * @param $op int The gateway op code
	 * @param $payload array The JSON payload to send
	 * @return true on success
	 */
	private function sendPayload($op, $payload){
		if(is_null($this->socket)) return false;
		$msg = array(
			"op" => $op,
			"d" => $payload
		);
		$res = socket_write($this->socket, json_encode($msg));
		return ($res === false) ? false : true;
	}

	/****************************
	 * Enumerations for Opcodes and status codes
	 ***************************/

	/**
	 * @var array Gateway Opcodes
	 */
	public static $OpCodes = array(
		"Dispatch" => 0,                // dispatches an event
		"Heartbeat" => 1,               // used for ping checking
		"Identify" => 2,                // used for client handshake
		"Status Update" => 3,           // used to update the client status
		"Voice Status Update" => 4,     // used to join/move/leave voice channels
		"Voice Server Ping" => 5,       // used for voice ping checking
		"Resume" => 6,                  // used to resume a closed connection
		"Reconnect" => 7,               // used to tell clients to reconnect to the gateway
		"Request Guild Members" => 8,   // used to request guild members
		"Invalid Session" => 9,         // used to notify client they have an invalid session id
		"Hello" => 10,                  // sent immediately after connecting, contains heartbeat and server debug information
		"Heartbeat ACK" => 11           // sent immediately following a client heartbeat that was received
	);

	/**
	 * @var array Voice server Opcodes
	 */
	public static $OpCodesVoice = array(
		"Identify" => 0,                // begin a voice websocket connection
		"Select Protocol" => 1,         // select the voice protocol
		"Ready" => 2,                   // complete the websocket handshake
		"Heartbeat" => 3,               // keep the websocket connection alive
		"Session Description" => 4,     // describe the session
		"Speaking" => 5,                // indicate which users are speaking
		"Heartbeat ACK" => 6,           // sent immediately following a received client heartbeat
		"Resume" => 7,                  // resume a connection
		"Hello" => 8,                   // the continuous interval in milliseconds after which the client should send a heartbeat
		"Resumed" => 9,                 // acknowledge Resume
		"Client Disconnect" => 13       // a client has disconnected from the voice channel
	);

	/**
	 * @var array Gateway Close Event Codes
	 */
	public static $CloseCodes = array(
		4000 => "unknown error",        // We're not sure what went wrong. Try reconnecting?
		4001 => "unknown opcode",       // You sent an invalid Gateway opcode or an invalid payload for an opcode. Don't do that!
		4002 => "decode error",         // You sent an invalid payload to us. Don't do that!
		4003 => "not authenticated",    // You sent us a payload prior to identifying.
		4004 => "authentication failed",// The account token sent with your identify payload is incorrect.
		4005 => "already authenticated",// You sent more than one identify payload. Don't do that!
		4007 => "invalid seq",          // The sequence sent when resuming the session was invalid. Reconnect and start a new session.
		4008 => "rate limited",         // Woah nelly! You're sending payloads to us too quickly. Slow it down!
		4009 => "session timeout",      // Your session timed out. Reconnect and start a new one.
		4010 => "invalid shard",        // You sent us an invalid shard when identifying.
		4011 => "sharding required"     // The session would have handled too many guilds - you are required to shard your connection in order to connect.
	);

	/**
	 * @var array Voice Server Close Event Codes
	 */
	public static $CloseCodesVoice = array(
		4001 => "Unknown opcode",       // You sent an invalid opcode.
		4003 => "Not authenticated",    // You sent a payload before identifying with the Gateway.
		4004 => "Authentication failed",// The token you sent in your identify payload is incorrect.
		4005 => "Already authenticated",// You sent more than one identify payload. Stahp.
		4006 => "Session no longer valid",//Your session is no longer valid.
		4009 => "Session timeout",      // Your session has timed out.
		4011 => "Server not found",     // We can't find the server you're trying to connect to.
		4012 => "Unknown protocol",     // We didn't recognize the protocol you sent.
		4014 => "Disconnected",         // Oh no! You've been disconnected! Try resuming.
		4015 => "Voice server crashed", // The server crashed. Our bad! Try resuming.
		4016 => "Unknown Encryption Mode"// We didn't recognize your encryption.
	);
}