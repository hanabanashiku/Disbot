<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 9/16/18
 * Time: 9:00 PM
 */

namespace Disbot\Server;


class GuildMember {
	private $user;
	private $nick;
	private $roles;
	private $deaf;
	private $mute;

	/**
	 * GuildMember constructor.
	 * @param $user User The member's user info
	 * @param $nick string The member's nickname, or null.
	 * @param $roles Roles The collection of roles.
	 * @param $deaf bool Is the member deafened?
	 * @param $mute bool Is the member muted?
	 */
	public function __construct($user, $nick, $roles, $deaf, $mute) {
		$this->user = $user;
		$this->nick = $nick;
		$this->roles = $roles;
		$this->deaf = $deaf;
		$this->mute = $mute;
	}

	/**
	 * @return User The member's user info
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * @return string|null
	 */
	public function getNick() {
		return $this->nick;
	}

	/**
	 * @param string $nick The new nickname
	 * @return GuildMember
	 */
	public function setNick($nick) {
		$this->nick = $nick;
		return $this;
	}

	/**
	 * @return Roles
	 */
	public function getRoles() {
		return $this->roles;
	}

	/**
	 * @return bool
	 */
	public function isDeaf() {
		return $this->deaf;
	}

	/**
	 * @return bool
	 */
	public function isMute() {
		return $this->mute;
	}





}