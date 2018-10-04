<?php

use Disbot\Disbot;
use Disbot\Permissions\DiscordPermissions;
use Disbot\Server\Gateway;

if($argc == 1)
	die('No option was given upon execution. Please type "disbot help" for more information.');

//spl_autoload_register(function($class) { require_once($class . '.php'); });
require('../vendor/autoload.php');

array_shift($argv);

switch(strtolower($argv[0])){

	case "start":
		$v = (sizeof($argv) > 0 && strtolower($argv[1]) == "--verbose" || strtolower($argv[1]) == "-v");
		Disbot::start($v);
		break;

	case "setup":
		setup();
		break;

	case "set":
		set($argv);
		break;
}

function read($length){
	$f = fopen("php://stdin", "r");
	$input = fgets($f, $length + 1);
	fclose($f);
	$input = rtrim($input);
	return $input;
}

function setup(){
	print("Welcome to Disbot! This prompt will walk you through the first-time setup.\n");
	print("First, please enter your Discord client id: ");
	$client_id = read(18);
	Disbot::getSettings()->setClientId($client_id);
	print("Next, please enter your Discord bot token: ");
	$token = read(59);
	Disbot::getSettings()->setToken($token);
	print("Next, please select the permissions that your bot will request. You may type 'user', 'moderator', 'all', 
	or type specific permissions, followed by a space. You may type 'disbot help set permissions' for more information
	 about specific permissions. ");
	$permissions = read(2048);
	$permissions = explode(" ", $permissions);
	setPermissions($permissions);
	print("Finally, we will need to authenticate your bot. Please visit the following URL (put it in your browser if it does not load)\n");
	print(Gateway::getAuthUrl() . "\n");
	print("Once you have authorized your bot, the set up will be completed.\n");
}

function set($arg){
	if(sizeof($arg) < 3)
		die("To change a setting, a key and a new value must be specified. For more information, type 'disbot help set'.");

	switch(strtolower($arg[1])){
		case "client_id":case "clientid":
			if(sizeof($arg[2]) != 18 || intval($arg[2]) == 0)
				die("The provided Client Id was of the wrong format.\n");
			Disbot::getSettings()->setClientId((int)$arg[2]);
			break;

		case "token":
			if(sizeof($arg[2]) != 59)
				die("The provided bot token was the wrong size.\n");
			Disbot::getSettings()->setToken($arg[2]);
			break;

		case "permissions":
			setPermissions(array_slice($arg, 2));
			break;

		default:
			die("Invalid key, could not set.\n");
	}
}

function setPermissions($permissions){
	if(strtolower($permissions[0]) == "user")
		Disbot::getSettings()->setPermissions(DiscordPermissions::getUserLevelPermissions());
	elseif(strtolower($permissions[0]) == "moderator")
		Disbot::getSettings()->setPermissions(DiscordPermissions::getModeratorPermissions());
	elseif(strtolower($permissions[0]) == "all")
		Disbot::getSettings()->setPermissions(DiscordPermissions::getAllPermissions());
	else{
		$arr = array();
		foreach($permissions as $p){
			$perm = constant("DiscordPermissions::".strtoupper($p));
			if($perm == null)
				die("Unknown permission " . $p . "\n");
			array_push($arr, $perm);
		}
		Disbot::getSettings()->setPermissions(DiscordPermissions::getPermissionInt($arr));
	}
}