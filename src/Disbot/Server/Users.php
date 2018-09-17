<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 9/16/18
 * Time: 9:30 PM
 */

namespace Disbot\Server;


class Users implements Collection {
	private $users;

	/**
	 * Users constructor.
	 * @param $users array List of User objects.
	 */
	public function __construct($users = array()) {
		$this->users = array();
		foreach($users as $r)
			array_push($this->users, $r);
	}

	/**
	 * @param $id integer The id of the user to get
	 * @return User|null
	 */
	public function get($id){
		foreach($this->users as $role)
			if($role->getId() == $id)
				return $role;
		return null;
	}

	/**
	 * @param $user User The user to check for.
	 * @return bool true if the user exists in the collection.
	 */
	public function exists($user) {
		return $this->get($user->getId()) == null;
	}


	/**
	 * @param $user User The user to add
	 */
	public function add($user){
		if($this->get($user->getId()) == null)
			array_push($this->users, $user);
	}

	/**
	 * @param $id integer The role Id to remove
	 */
	public function remove($id){
		$found = false;
		for($i = 0; $i < sizeof($this->users); $i++){
			if($this->users[$i]->getId() == $id){
				$found = true;
				break;
			}
		}

		if(!$found) return;
		array_splice($this->users, $i, 1);
	}
}