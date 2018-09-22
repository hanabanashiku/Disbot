<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 9/21/18
 * Time: 11:41 PM
 */

namespace Disbot\Server;

class Guilds implements Collection {
	private $guilds;

	/**
	 * Guilds constructor.
	 * @param array $guilds List of Guild objects.
	 */
	public function __construct($guilds = array()){
		$this->guilds = array();
		foreach($guilds as $g)
			array_push($this->guilds, $g);
	}

	/**
	 * @param $id integer The Guild ID
	 * @return Guild|null The guild if it exists, or null otherwise.
	 */
	public function get($id){
		foreach($this->guilds as $guild)
			if($guild->getId() == $id)
				return $guild;
		return null;
	}

	/**
	 * @param $guild Guild The guild to look for.
	 * @return bool true if the guild exists in the collection.
	 */
	public function exists($guild){
		return $this->get($guild->getId()) != null;
	}

	/**
	 * @param $guild Guild The guild to add
	 */
	public function add($guild){
		if(!$this->exists($guild))
			array_push($this->guilds, $guild);
	}

	/**
	 * @param $id integer The id of the guild to remove.
	 */
	public function remove($id){
		$found = false;
		for($i = 0; $i < sizeof($this->guilds); $i++){
			if($this->guilds[$i]->getId() == $id){
				$found = true;
				break;
			}
		}
		if(!$found) return;
		array_splice($this->guilds, $i, 1);
	}


}