<?php
namespace Disbot\Handlers;

use Disbot\Server\Gateway;
use Disbot\Server\User;

function receiveReady($message, Gateway $gateway){
	$gateway->setUser(new User($message["user"]))->setSessionId($message["session_id"]);
	$gateway->getLogger()->debug("CLIENT_READY", $message["_trace"]);
}