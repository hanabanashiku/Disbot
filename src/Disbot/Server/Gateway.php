<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 8/24/18
 * Time: 6:10 PM
 */

namespace Disbot\Server;

use Disbot\Disbot;
use Disbot\Handlers;
use Katzgrau\KLogger\Logger;


class Gateway {
	const VERSION = 6; // The Discord gateway version to request
	const ENCODING = 'json';
	const OAUTH_URL = 'https://discordapp.com/api/oauth2/authorize?'; // the OAuth URL
	const GATEWAY_URL = 'https://discordapp.com/api/gateway/bot'; // The URL used to fetch the Gateway address
	const MAX_LENGTH = 4096; // Max number of bytes that can be sent

	// connection information
	private $address;
	private $socket;
	private $token; // bot's token
	private $client_id; // the bot's client ID
	private $permissions;

	// state information
	private $endSession; // terminate the session?
	private $interval;  // the interval between heartbeats, in seconds
	private $s = null;  // the last heartbeat number
	private $timer = 0; // timestamp of last heartbeat
	private $user;      // The bot itself
	private $ack;       // Are we waiting for a heartbeat ACK?


	public function __construct() {
		$this->endSession = false;
		$this->client_id = Disbot::getSettings()->getClientId();
		$this->token = Disbot::getSettings()->getToken();
		$this->permissions = Disbot::getSettings()->getPermissions();
	}

	/**
	 * This is the URL used to authenticate the client with Discord.
	 * Running this attempts to open the URL in the browser as well.
	 * @return string The URL the user must navigate to
	 */
	public static function getAuthUrl() {
		$url = Gateway::OAUTH_URL . "client_id=".Disbot::getSettings()->getClientId()."&scope=bot&permissions=".Disbot::getSettings()->getPermissions();
		switch(php_uname('s')){ // Let's attempt to open the url
			case 'Linux': case 'Unix': // Linux
				exec('xdg-open ' . $url);
				break;
			case 'WIN32':case 'WINNT':case 'Windows': // Windows
				exec('rundll32 url.dll,FileProtocolHandler ' . $url);
				break;
			case 'Darwin': // Max
				exec('open ' . $url);
				break;
		}
		return $url;
	}

	/**
	 * Fetches the gateway URL the socket must connect to
	 */
	private function getGatewayUrl() {
		$ch = curl_init($this::GATEWAY_URL);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bot {$this->token}"));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$response = json_decode(curl_exec($ch), true);
		if ($response["url"] == null){
			Disbot::getLogger()->error('GATEWAY_URL', array(curl_error($ch), $response));
			die("Could not retrieve Gateway socket URL");
		}
		$this->address = $response["url"] . "?v=".$this::VERSION."&encoding=".$this::ENCODING;
		Disbot::getLogger()->info("GATEWAY_URL", array($this->address));
	}

	/**
	 * Signals for the client to create a socket and begin communicating with the gateway.
	 * @return true on successful close, false if the client should resume.
	 */
	public function listen() {
		if (is_null($this->token)) {
			Disbot::getLogger()->error("NULL_TOKEN");
			print("To start the server, a valid token must be supplied. Set the token by running `disbot set token.` See help for more information\n");
			exit(2);
		}

		// initiate the connection
		$this->connect();

		Disbot::getLogger()->info("BEGIN_LISTEN");

		// lets start our main loop
		while(true) {
			if($this->endSession){
				socket_close($this->socket);
				return true;
			}

			// we need to send a heartbeat
			if(time() >= $this->timer + $this->interval)
				$this->sendHeartbeat();

			// the
			else if($this->ack != false && time() > $this->ack + 2){
				socket_close($this->socket);
				return false;
			}

			$data = fread($this->socket, $this::MAX_LENGTH);
			if(Disbot::isVerbose()) Disbot::getLogger()->debug("SOCKET_RECEIVE", $data);
			Handlers\receiveSocketMessage($data);
		}
		return true;
	}

	private function connect(){
		// fetch our URL from the server (can change.. don't cache!)
		$this->getGatewayUrl();

		// create and connect to our socket
		$this->socket = stream_socket_client($this->address, $errno, $errstr);
		if(!$this->socket){
			Disbot::getLogger()->error("CONNECT_SOCKET", array($errno, $errstr));
			die("Could not connect to socket!");
		}
		Disbot::getLogger()->info("CREATE_SOCKET");
	}

	/**
	 * Sends an OP2 Identify to the gateway. Should only be called after receiving OP10 Hello.
	 * @return true On success
	 */
	public function identify(){
		$msg = array(
			"token" => Disbot::getSettings()->getToken(),
			"properties" => array(
				"os" => PHP_OS,
				"browser" => "disbot",
				"device" => "disbot"
			)
		);
		Disbot::getLogger()->info("IDENTIFY_SEND");
		return $this->sendRaw(json_encode($msg));
	}

	/**
	 * Send a heartbeat. The heartbeat must be sent every $heartbeatInterval seconds.
	 * @return true on success
	 */
	public function sendHeartbeat() {
		$this->timer = time(); // update timestamp
		$this->ack = time();   // waiting for an ACK
		return $this->sendPayload(HEARTBEAT, $this->s);
	}

	/**
	 * Sends a payload through the socket
	 * @param $op int The gateway op code
	 * @param $payload array The JSON payload to send
	 * @return true on success
	 */
	private function sendPayload($op, $payload) {
		$msg = array(
			"op" => $op,
			"d" => $payload
		);
		return $this->sendRaw(json_encode($msg));
	}

	/**
	 * Sends a custom request through the socket
	 * @param string $payload The payload to send
	 * @return true on success
	 */
	private function sendRaw($payload){
		if(is_null($this->socket)) return false;
		$res = socket_write($this->socket, $payload);
		if(!$res){
			Disbot::getLogger()->error("SOCKET_SEND", array($payload, socket_strerror(socket_last_error($this->socket))));
			return false;
		}
		return true;
	}

	/**
	 * Used to set the heartbeat interval; should only be called when receiving a OP10 Hello
	 * @param $int integer The new heartbeat interval, in milliseconds
	 * @return $this The gateway
	 */
	public function setHeartbeatInterval($int){
		Disbot::getLogger()->info("HELLO_RECEIVE HEARTBEAT INTERVAL " . $int);
		$this->interval = ($int / 1000);
		return $this;
	}

	/**
	 * @return User The bot's User information
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * @param User $user The bot itself
	 * @return $this The gateway
	 */
	public function setUser(User $user){
		$this->user = $user;
		return $this;
	}

	/**
	 * @param string $s
	 * @return $this The gateway
	 */
	public function setSessionId($s) {
		$this->s = $s;
		return $this;
	}

	/**
	 * @return Logger The logger instance
	 */
	public function getLogger() {
		return Disbot::getLogger();
	}

	public function setAck($ack){
		$this->ack = $ack;
		return $this;
	}

	/**
	 * Signal for the bot to end after the next loop.
	 */
	public function endSession(){
		$this->endSession = true;
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