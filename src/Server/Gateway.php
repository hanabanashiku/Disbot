<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 8/24/18
 * Time: 6:10 PM
 */

namespace Server;

class Gateway {
	const VERSION = 6;
	const ENCODING = 'json';
	const OAUTH_URL = 'https://discordapp.com/api/oauth2/authorize?';
	const GATEWAY_URL = 'https://discordapp.com/gateway/bot';

	private $address;
	private $socket;
	private $token;
	private $client_id;
	private $permissions;

	public function __construct($client_id, $token, $permissions) {
		$this->client_id = $client_id;
		$this->token = $token;
		$this->permissions = $permissions;
	}

	public function getAuthUrl(){
		return $this::OAUTH_URL."client_id=".$this->client_id."&scope=bot&permissions=".$this->permissions;
	}

	private function getGatewayUrl(){
		$ch = curl_init($this::GATEWAY_URL);
		curl_setopt($ch, CURLOPT_HTTPHEADER, "Authorization: Bot ".$this->token);
		$response = json_decode(curl_exec($ch));
		if($response["url"] == null) return false;
		return $response["url"];
	}





}