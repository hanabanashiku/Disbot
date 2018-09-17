<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 8/29/18
 * Time: 8:52 PM
 */

namespace Disbot\Handlers;

use Disbot\Disbot;

/**
 * Handle receiving a message from the gateway.
 * @param $message string The JSON-encoded message.
 */
function receiveSocketMessage($message){
	$message = json_decode($message, true);

	// We are receiving a Ready
	if(array_key_exists("v", $message) &&
		array_key_exists("user", $message) && array_key_exists("session_id", $message)){
		receiveReady($message);
		return;
	}


	// we received an error code
	if(array_key_exists("code", $message)){
		Disbot::getGateway()->getLogger()->error("ERROR_CODE_RECEIVED", $message);
		if(in_array($message["code"], array(20001, 50011, 50014, 50025, 50041))){
			// TODO handle errors
		}
	}

	switch($message["op"]){
		case DISPATCH:
			switch(strtolower($message["t"])){
				case "ready":
					receiveReady($message);
					break;
			}
			break;

		case HELLO:
			receiveHello($message["heartbeat_interval"]);
			break;

		case HEARTBEAT_ACK:
			Disbot::getGateway()->setAck(false);
			break;

	}
}

function receiveHello($interval){
	Disbot::getGateway()->setHeartbeatInterval($interval);
	Disbot::getGateway()->identify();
}