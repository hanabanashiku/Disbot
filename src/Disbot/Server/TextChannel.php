<?php

namespace Disbot\Server;

class TextChannel extends Channel {
	private $name;
	private $topic;
	private $nsfw;

	public function __construct($json) {
		$this->id = $json["id"];
		$this->name = $json["name"];
		$this->type = $json["type"];
		$this->nsfw = $json["nsfw"];
		$this->topic = $json["topic"];
	}

	/**
	 * @return string The name of the channel.
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string The topic of the channel.
	 */
	public function getTopic() {
		return $this->topic;
	}

	/**
	 * @return bool True if the channel is nsfw.
	 */
	public function isNsfw() {
		return $this->nsfw;
	}


}