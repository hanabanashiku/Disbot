<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 8/29/18
 * Time: 8:52 PM
 */

namespace Disbot\Handlers;

use Disbot\Disbot;
use Disbot\Server\User;

/**
 * Handle receiving a message from the gateway.
 * @param $message string The JSON-encoded message.
 */
function receiveSocketMessage($message){
    if(Disbot::isVerbose()) Disbot::getLogger()->info("RECEIVE_MESSAGE", array(time(), $message));
	$message = json_decode($message, true);
    $content = (array_key_exists('d', $message)) ? $message["d"] : null;

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
					receiveReady($content);
					break;
			}
			break;

		case HELLO:
			receiveHello($content["heartbeat_interval"]);
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

function receiveReady($message){
    Disbot::setSelf(new User($message["user"]));
    Disbot::getGateway()->setSessionId($message["session_id"]);
    Disbot::getGateway()->setTimer();
    Disbot::getLogger()->debug("CLIENT_READY", $message["_trace"]);
}