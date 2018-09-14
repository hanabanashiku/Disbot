<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 8/24/18
 * Time: 6:10 PM
 */

namespace Disbot\Server;

use Disbot\Handlers;
use Disbot;

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
	private $interval; // the interval between heartbeats
	private $s = null; // the last heartbeat number
	private $timer = 0; // time since last heartbeat

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
	public function getAuthUrl() {
		return $this::OAUTH_URL . "client_id={$this->client_id}&scope=bot&permissions={$this->permissions}";
	}

	/**
	 * Fetches the gateway URL the socket must connect to
	 */
	private function getGatewayUrl() {
		$ch = curl_init($this::GATEWAY_URL);
		curl_setopt($ch, CURLOPT_HTTPHEADER, "Authorization: Bot {$this->token}");
		$response = json_decode(curl_exec($ch));
		if ($response["url"] == null) die("Could not retrieve Gateway socket URL");
		$this->address = $response["url"] . "?v={$this::VERSION}&encoding={$this::ENCODING}";
	}

	/**
	 * Signals for the client to create a socket and begin communicating with the gateway.
	 */
	public function listen() {
		if (is_null($this->token)) {
			print("To start the server, a valid token must be supplied. Get the token by running `disbot auth.` See help for more information\n");
			exit(2);
		}

		$this->getGatewayUrl();

		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("Socket could not be created\n");
		socket_bind($this->socket, '127.0.0.1');
		socket_connect($this->socket, gethostbyname($this->address), 443);

		// lets start our main loop
		while (true) {
			$data = socket_read($this->socket, $this::MAX_LENGTH);
			if ($data === false) {
				$err = socket_last_error($this->socket);
				die("Connection closed! [$err]");
			}
			Handlers\ReceiveSocketMessage($data, $this);
		}
	}

	private function identify(){
		if(is_null($this->socket)) return false;
		$msg = array(
			"token" => TOKEN,
			"properties" => array(
				"os" => PHP_OS,
				"browser" => "disbot",
				"device" => "disbot"
			)
		);
		$res = socket_write($this->socket, json_encode($msg));
		return ($res == false) ? false : true;
	}

	/**
	 * Send a heartbeat. The heartbeat must be sent every $heartbeatInterval seconds.
	 */
	public function sendHeartbeat() {
		$this->sendPayload(HEARTBEAT, $this->s);
	}

	/**
	 * To be called when the gateway receives a heartbeat ACK or hello
	 * @param $int integer interval in which to heartbeat
	 * @param $s integer the last heartbeat number
	 */
	public function receiveHeartbeat($int, $s){
		$this->interval = $int;
		$this->s = $s;
		$this->timer = time();
	}

	/**
	 * Sends a payload through the socket
	 * @param $op int The gateway op code
	 * @param $payload array The JSON payload to send
	 * @return true on success
	 */
	private function sendPayload($op, $payload) {
		if (is_null($this->socket)) return false;
		$msg = array(
			"op" => $op,
			"d" => $payload
		);
		$res = socket_write($this->socket, json_encode($msg));
		return ($res === false) ? false : true;
	}
}

/****************************
 * Enumerations for Opcodes and status codes
 ***************************/

// Gateway Opcodes
define('DISPATCH', 0);                  // dispatches an event
define('HEARTBEAT', 1);                 // used for ping checking
define('IDENTIFY', 2);                  // used for client handshake
define('STATUS_UPDATE', 3);             // used to update the client status
define('VOICE_STATUS_UPDATE', 4);       // used to join/move/leave voice channels
define('VOICE_SERVER_PING', 5);         // used for voice ping checking
define('RESUME', 6);                    // used to resume a closed connection
define('RECONNECT', 7);                 // used to tell clients to reconnect to the gateway
define('REQUEST_GUILD_MEMBERS', 8);     // used to request guild members
define('INVALID_SESSION', 9);           // used to notify client they have an invalid session id
define('HELLO', 10);                    // sent immediately after connecting, contains heartbeat and server debug information
define('HEARTBEAT_ACK', 11);            // sent immediately following a client heartbeat that was received

// Voice Server Opcodes
define('VOICE_IDENTIFY', 0);            // begin a voice websocket connection
define('VOICE_SELECT_PROTOCOL', 1);     // select the voice protocol
define('VOICE_READY', 2);               // complete the websocket handshake
define('VOICE_HEARTBEAT', 3);           // keep the websocket connection alive
define('VOICE_SESSION_DESCRIPTION', 4); // describe the session
define('VOICE_SPEAKING', 5);            // indicate which users are speaking
define('VOICE_HEARTBEAT_ACK', 6);       // sent immediately following a received client heartbeat
define('VOICE_RESUME', 7);              // resume a connection
define('VOICE_HELLO', 8);               // the continuous interval in milliseconds after which the client should send a heartbeat
define('VOICE_RESUMED', 9);             // acknowledge Resume
define('VOICE_CLIENT_DISCONNECT', 10);  // a client has disconnected from the voice channel

// Close Codes
define('ERROR_UNKNOWN', 4000);          // We're not sure what went wrong. Try reconnecting?
define('ERROR_OPCODE', 4001);           // You sent an invalid Gateway opcode or an invalid payload for an opcode. Don't do that!
define('ERROR_DECODE', 4002);           // You sent an invalid payload to us. Don't do that!
define('ERROR_NOT_AUTHENTICATED', 4003);// You sent us a payload prior to identifying.
define('ERROR_AUTHENTICATION', 4004);   // The account token sent with your identify payload is incorrect.
define('ERROR_AUTHENTICATED', 4005);    // You sent more than one identify payload. Don't do that!
define('ERROR_INVALID_SEQ', 4007);      // The sequence sent when resuming the session was invalid. Reconnect and start a new session.
define('ERROR_RATE_LIMITED', 4008);     // Woah nelly! You're sending payloads to us too quickly. Slow it down!
define('ERROR_TIMEOUT', 4009);          // Your session timed out. Reconnect and start a new one.
define('ERROR_INVALID_SHARD', 4010);    // You sent us an invalid shard when identifying.
define('ERROR_SHARDING_REQUIRED', 4011);// The session would have handled too many guilds - you are required to shard your connection in order to connect.

// Voice Server Close Codes
define('VOICE_ERROR_OP', 4001);                 // You sent an invalid opcode.
define('VOICE_ERROR_NOT_AUTHENTICATED', 4003);  // You sent a payload before identifying with the Gateway.
define('VOICE_ERROR_AUTHENTICATION', 4004);     // The token you sent in your identify payload is incorrect.
define('VOICE_ERROR_AUTHENTICATED', 4005);      // You sent more than one identify payload. Stahp.
define('VOICE_ERROR_INVALID_SESSION', 4006);    // Your session is no longer valid.
define('VOICE_ERROR_TIMEOUT', 4009);            // Your session has timed out.
define('VOICE_ERROR_NOT_FOUND', 4011);          // We can't find the server you're trying to connect to.
define('VOICE_ERROR_PROTOCOL', 4012);           // We didn't recognize the protocol you sent.
define('VOICE_ERROR_DISCONNECTED', 4014);       // Oh no! You've been disconnected! Try resuming.
define('VOICE_ERROR_CRASHED', 4015);            // The server crashed. Our bad! Try resuming.
define('VOICE_ERROR_ENCRYPTION', 4016);         // We didn't recognize your encryption.