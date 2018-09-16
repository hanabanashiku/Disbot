<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 8/29/18
 * Time: 8:52 PM
 */

namespace Disbot\Handlers;

use Disbot\Server\Gateway;

/**
 * Handle receiving a message from the gateway.
 * @param $message string The JSON-encoded message.
 * @param Gateway $gateway The gateway.
 */
function receiveSocketMessage($message, Gateway $gateway){
	$message = json_decode($message, true);

	// We are receiving a Hello
	if(array_key_exists("heartbeat_interval", $message)){
		receiveHello($message["heartbeat_interval"], $gateway);
		return;
	}

	// We are receiving a Ready
	if(array_key_exists("v", $message) && array_key_exists("user", $message) && array_key_exists("session_id", $message))
		receiveReady($message, $gateway);

	switch($message["op"]){
		case HEARTBEAT_ACK:
			$gateway->setAck(false);
			break;
	}
}

function receiveHello($interval, Gateway $gateway){
	$gateway->setHeartbeatInterval($interval);
	$gateway->identify();
}