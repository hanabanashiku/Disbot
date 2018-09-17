<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 9/16/18
 * Time: 10:30 PM
 */

namespace Disbot\Server;

class Channels implements Collection{
	private $channels;

	public function __construct($channels = array()){
		$this->channels = array();
		foreach($channels as $channel)
			array_push($this->channels, $channel);
	}

	/**
	 * @param $channel Channel The channel to add.
	 */
	public function add($channel) {
		if(!$this->exists($channel))
			array_push($channels, $channel);
	}

	/**
	 * @param $id integer The id of the channel to remove.
	 */
	public function remove($id) {
		$found = false;
		for($i = 0; $i < sizeof($this->channels); $i++)
			if($this->channels[$i]->getId() == $id){
				$found = true;
				break;
			}
		if(!$found) return;
		array_splice($this->channels, $i, 1);
	}

	/**
	 * @param $id integer The id of the channel to look for.
	 * @return Channel|null
	 */
	public function get($id) {
		foreach($this->channels as $channel)
			if($channel->getId() == $id)
				return $channel;
		return null;
	}

	/**
	 * @param $channel Channel The channel to look for
	 * @return bool true if the channel exists in the collection.
	 */
	public function exists($channel) {
		return $this->get($channel->getId()) == null;
	}
}