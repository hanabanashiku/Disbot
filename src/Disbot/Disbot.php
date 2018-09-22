<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 9/16/18
 * Time: 8:00 PM
 */

namespace Disbot;

use Disbot\Server\Channels;
use Disbot\Server\Gateway;
use Disbot\Server\Guilds;
use Disbot\Server\User;
use Disbot\Server\Users;
use Katzgrau\KLogger\Logger;

//define("LOGGER_DIR", realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . 'logs'));

class Disbot {
	private static $init = false;
	private static $logger;
	private static $settings;
	private static $user;
	private static $guilds;
	private static $dms;
	private static $gateway;
	private static $users;
	private static $verbose;

	public static function init(){
		$logger_dir = getcwd() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'logs';

		if(!self::$init){
			self::$logger = new Logger($logger_dir);
			self::$settings = new Settings();
			self::$guilds = new Guilds();
			self::$dms = array();
			self::$users = new Users();
			self::$init = true;
		}
	}

	/**
	 * @param bool $verbose Set the bot to verbose mode?
	 */
	public static function start($verbose = false){
		self::$verbose = $verbose;
		if(self::getSettings()->getClientId() == null)
			die("Error: Please define the client id by using set ClientId");
		if(self::getSettings()->getToken() == null)
			die("Error: Please define the bot token by using set Token");

		// TODO Deal with reconnects
		Disbot::$gateway = new Gateway();
		self::getGateway()->listen();
	}

	/**
	 * @return boolean true if the bot is set to verbose.
	 */
	public static function isVerbose() { return self::$verbose; }

	/**
	 * @return Logger
	 */
	public static function getLogger() { return self::$logger; }

	/**
	 * @return Settings
	 */
	public static function getSettings() { return self::$settings; }

	/**
	 * @return User The bot itself.
	 */
	public static function getSelf() {
		return self::$user;
	}

	/**
	 * @param User $user The bot's user data.
	 */
	public static function setSelf($user) {
		self::$user = $user;
	}


	/**
	 * @return Guilds The collection of guilds.
	 */
	public static function getGuilds() { return self::$guilds; }

	/**
	 * @return Channels The collection of direct messages
	 */
	public static function getDms() { return self::$dms; }

	/**
	 * @return Users All users the bot has encountered.
	 */
	public static function getUsers(){
		return self::$users;
	}

	/**
	 * @return Gateway
	 */
	public static function getGateway() { return self::$gateway; }

}

Disbot::init();