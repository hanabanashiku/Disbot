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
function ReceiveSocketMessage($message, Gateway $gateway){
	$message = json_decode($message);

	switch($message["op"]){

	}
}