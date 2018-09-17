<?php
namespace Disbot\Handlers;

use Disbot\Server\User;
use Disbot\Disbot;

function receiveReady($message){
	Disbot::setSelf(new User($message["user"]));
	Disbot::getGateway()->setSessionId($message["session_id"]);
	Disbot::getLogger()->debug("CLIENT_READY", $message["_trace"]);
}