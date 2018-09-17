<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 9/16/18
 * Time: 8:46 PM
 */

namespace Disbot\Server;

class Role {
	private $id;
	private $name;
	private $permissions;
	private $mentionable;

	/**
	 * Role constructor.
	 * @param $json array The JSON array of the role
	 */
	public function __construct($json) {
		$this->id = $json["id"];
		$this->name = $json["name"];
		$this->permissions = $json["permissions"];
		$this->mentionable = $json["mentionable"];
	}

	/**
	 * @return integer The role ID
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string The role name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return integer The permission bit set
	 */
	public function getPermissions() {
		return $this->permissions;
	}

	/**
	 * @return true if the role is mentionable.
	 */
	public function isMentionable() {
		return $this->mentionable;
	}


}