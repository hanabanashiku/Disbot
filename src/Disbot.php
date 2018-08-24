<?php

namespace Disbot;

/*
 * True if the verbose flag was set
 */
$verbose = false;
$token = '';

array_shift($argv);
switch(strtolower($argv[0])){
    case "start":
        if(in_array("--verbose", $argv))
            $verbose = true;
        break;

    case "server":
        if(sizeof($argv) < 3){
            print("Error: Missing arguments\n");
        }
        $arg = strtolower($argv[1]);
        if($arg == "add"){

        }
        else if($arg == "remove"){

        }
        else if($arg == "list"){

        }
        else {
            printf("Error: Unknown argument '%s'\n", $arg);
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