<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 9/16/18
 * Time: 7:49 PM
 */

namespace Disbot\Server;

use Disbot\Disbot;

class Guild {
	private $id;            // The guild id
	private $name;          // The guild's name
	private $permissions;   // The bot's permissions in the guild
	private $region;        // The voice chat region
	private $roles;         // The list of guild roles.
	private $available;     // true if the guild is available.
	private $members;       // The list of guild members.
	private $channels;      // The list of guild channels.

	/**
	 * Guild constructor.
	 * @param $json array The JSON array from the GUILD_CREATE event
	 */
	public function __construct($json) {
		$this->id = $json["id"];
		$this->name = $json["name"];
		$this->permissions = $json["permissions"];
		$this->region = $json["region"];
		$this->available = !$json["unavailable"];

		$this->roles = new Roles();
		foreach($json["roles"] as $role)
			$this->roles->add(new Role($role));

		$this->members = new GuildMembers();
		foreach($json["members"] as $member)
			$this->members->addFromRaw($member, $this);

		$this->channels = new Channels();
		foreach($json["channels"] as $channel){
			if($channel["type"] == GUILD_TEXT)
				$this->channels->add(new TextChannel($channel));
			else if($channel["type"] == GUILD_VOICE)
				$this->channels->add(new VoiceChannel($channel));
		}

		Disbot::getLogger()->info("GUILD_DISCOVERED [" . $this->id . "] " . $this->name);
	}

	/**
	 * @return integer The guild id
	 */
	public function getId() { return $this->id; }

	/**
	 * @return string The guild name
	 */
	public function getName() { return $this->name; }

	/**
	 * @return integer The bot's permission bit set for this guild.
	 */
	public function getPermissions() { return $this->permissions; }

	/**
	 * @return string The voice chat region.
	 */
	public function getRegion() { return $this->region; }

	/**
	 * @return Roles The collection of guild roles.
	 */
	public function getRoles() { return $this->roles; }

	/**
	 * @return bool true if the guild is available.
	 */
	public function isAvailable() { return $this->available; }

	/**
	 * @return GuildMembers The collection of guild members.
	 */
	public function getMembers() { return $this->members; }

	/**
	 * @return Channels The collection of text and voice channels.
	 */
	public function getChannels() { return $this->channels; }
}