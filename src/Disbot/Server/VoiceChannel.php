<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 9/16/18
 * Time: 10:41 PM
 */

namespace Disbot\Server;


class VoiceChannel extends Channel {
	private $nsfw;
	private $bitrate;
	private $userLimit;

	public function __construct($json) {
		$this->id = $json["id"];
		$this->name = $json["name"];
		$this->type = $json["type"];
		$this->nsfw = $json["nsfw"];
		$this->bitrate = $json["bitrate"];
		$this->userLimit = $json["user_limit"];
	}

	/**
	 * @return bool true if the channel is not safe for work.
	 */
	public function isNsfw() { return $this->nsfw; }

	/**
	 * @return integer the bit rate of the voice channel.
	 */
	public function getBitrate() { return $this->bitrate; }

	/**
	 * @return integer The user limit of the voice channel.
	 */
	public function getUserLimit() { return $this->userLimit; }

	public function isVoice() { return true;}


}