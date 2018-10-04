<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 9/15/18
 * Time: 10:14 PM
 */

namespace Disbot\Server;


class User {
	private $id;
	private $username;
	private $discriminator;
	private $bot;
	private $locale;

	/**
	 * User constructor.
	 * @param $obj array The JSON object to construct
	 */
	public function __construct($obj) {
		$this->id = $obj["id"];
		$this->username = $obj["username"];
		$this->discriminator = $obj["discriminator"];
		$this->bot = $obj["bot"];
		$this->locale = array_key_exists('locale', $obj) ? $obj["locale"] : "en-US";
	}

	public function update($obj){
		if($obj["id"] != $this->id) return false;
		$this->username = $obj["username"];
		$this->discriminator = $obj["discriminator"];
		$this->bot = $obj["bot"];
        $this->locale = array_key_exists('locale', $obj) ? $obj["locale"] : "en-US";
	}

	/**
	 * @return integer User ID
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string Username
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * @return int 4-digit discord-tag
	 */
	public function getDiscriminator() {
		return $this->discriminator;
	}

	/**
	 * @return bool True if the user is a bot
	 */
	public function isBot() {
		return $this->bot;
	}

	/**
	 * @return string The user's locale
	 */
	public function getLocale() {
		return $this->locale;
	}

}