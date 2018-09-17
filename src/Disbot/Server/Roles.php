<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 9/16/18
 * Time: 9:19 PM
 */

namespace Disbot\Server;


class Roles implements Collection{
	private $roles;

	/**
	 * Roles constructor.
	 * @param $roles array List of Role objects.
	 */
	public function __construct($roles = array()) {
		$this->roles = array();
		foreach($roles as $r)
			array_push($this->roles, $r);
	}

	/**
	 * @param $id integer The id of the role to get.
	 * @return Role|null
	 */
	public function get($id){
		foreach($this->roles as $role)
			if($role->getId() == $id)
				return $role;
		return null;
	}

	/**
	 * @param $role Role The role to look for.
	 * @return bool true if the role exists in the collection.
	 */
	public function exists($role){
		return $this->get($role->getId()) == null;
	}
	/**
	 * @param $role Role The role to add
	 */
	public function add($role){
		if(!$this->exists($role))
			array_push($this->roles, $role);
	}

	/**
	 * @param $id integer The role Id to remove
	 */
	public function remove($id){
		$found = false;
		for($i = 0; $i < sizeof($this->roles); $i++){
			if($this->roles[$i]->getId() == $id){
				$found = true;
				break;
			}
		}

		if(!$found) return;
		array_splice($this->roles, $i, 1);
	}
}