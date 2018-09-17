<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 9/16/18
 * Time: 9:32 PM
 */

namespace Disbot\Server;


use Disbot\Disbot;

class GuildMembers implements Collection{
	private $members;

	/**
	 * GuildMembers constructor.
	 * @param $members array List of GuildMember objects.
	 */
	public function __construct($members = array()) {
		$this->members = array();
		foreach($members as $m)
			array_push($this->members, $m);
	}

	/**
	 * @param $id integer The User id
	 * @return GuildMember|null
	 */
	public function get($id){
		foreach($this->members as $member)
			if($member->getUser()->getId() == $id)
				return $member;
		return null;
	}

	/**
	 * @param $member GuildMember The member to look for.
	 * @return bool true if the member exists in the collection.
	 */
	public function exists($member){
		return $this->get($member->getUser()->getId()) == null;
	}

	/**
	 * @param $member GuildMember The member to add
	 */
	public function add($member){
		if($this->exists($member))
			array_push($this->members, $member);
	}

	/**
	 * Creates a new GuildMember object and adds it to the collection
	 * @param $member array The JSON information from the GUILD_CREATE event
	 * @param $guild Guild The guild the member is a part of
	 */
	public function addFromRaw($member, $guild){
		$user = Disbot::getUsers()->get($member["user"]["id"]);
		if($user == null){
			$user = new User($member["user"]);
			Disbot::getUsers()->add($user);
		}

		if(key_exists("nick", $member))
			$nick = $member["nick"];
		else $nick = null;

		$roles = new Roles();
		foreach($member["roles"] as $role) // array of role ids
			$roles->add($guild->getRoles()->get($role));

		$deaf = $member["deaf"];
		$mute = $member["mute"];

		$ret = new GuildMember($user, $nick, $roles, $deaf, $mute);
		$this->add($ret);
	}

	/**
	 * @param $id integer The id of the member to remove
	 */
	public function remove($id){
		$found = false;
		for($i = 0; $i < sizeof($this->members); $i++){
			if($this->members[$i]->getUser()->getId() == $id){
				$found = true;
				break;
			}
		}

		if(!$found) return;
		array_splice($this->members, $i, 1);
	}
}