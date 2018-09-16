<?php

namespace Disbot;
require '../vendor/autoload.php';

/********* FLAGS
 * VERBOSE - Log everything to the terminal?
 * TOKEN - What is the token?
 * Client ID - What is the client ID?
 */

define("LOGGER_DIR", realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . 'logs'));
$settings = new Settings();

array_shift($argv);
switch(strtolower($argv[0])){

    case "start":
        if(in_array("--verbose", $argv))
            define('VERBOSE', true);
        else define('VERBOSE', false);
        define('TOKEN', $settings->getToken());
        define('CLIENT_ID', $settings->getClientId());
        break;

    case "server":
        if(sizeof($argv) < 3){
            print("Error: Missing arguments\n");
        }
        $arg = strtolower($argv[1]);

        switch($arg){
	        case "add":
	        	break;
	        case "remove":
	        	break;
	        case "list":
	        	break;
	        default:
	        	die("Error: Unknown argument '$arg''\n");
        }
        break;

    case "status":
        break;

	case "set":
		switch(strtolower($argv[1])){
			case "token":
				break;
			case "client": // client_id and client_secret
				break;
			case "client_id":
				break;
			case "client_secret":
				break;
			case "permissions":
				break;
			default:
				printf("Error: Invalid parameter '%s'\n", $argv[1]);
				break;
		}
		break;

    case "help":
        break;
}