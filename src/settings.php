<?php
namespace Disbot;

class Settings{
	private $dir;
	const FNAME = 'settings.ini';
	private $path;

	private $token;
	private $client_id;

	function __construct() {
		$dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'data';
		$path = $dir . DIRECTORY_SEPARATOR . Settings::FNAME;

		if(!file_exists($path))
			return;

		$settings = parse_ini_file($path, true);

		$token = $settings["credentials"]["token"];
		$client_id = $settings["credentials"]["client_id"];
	}

	function __destruct(){
		$settings = array();
		$settings["credentials"]["token"] = $this->getToken();
		$settings["credentials"]["client_id"] = $this->getClientId();

		$out = $this::ini_to_string($settings);
		$file = fopen($this->path, "w");
		fwrite($file, $out);
		fclose($file);
	}

	/**
	 * @return string The token
	 */
	public function getToken() {
		return $this->token;
	}

	/**
	 * @param string $token
	 * @return Settings
	 */
	public function setToken($token) {
		$this->token = $token;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getClientId() {
		return $this->client_id;
	}

	/**
	 * @param string $client_id
	 * @return Settings
	 */
	public function setClientId($client_id) {
		$this->client_id = $client_id;
		return $this;
	}

	private static function ini_to_string($array){
		$lines = array();

		foreach($array as $key => $val) {
			if (is_array($val)) {
				$lines[] = "[$key]";
				foreach ($val as $subkey => $subval) $res[] = "$subkey = " . (is_numeric($val) ? $subval : '"' . $subval . '"');
			} else $res[] = "$key = " . (is_numeric($val) ? $val : '"' . $val . '"');
		}
		return implode(PHP_EOL, $lines);
	}
}