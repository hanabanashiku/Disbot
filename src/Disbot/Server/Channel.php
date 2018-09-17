<?php
/**
 * Created by PhpStorm.
 * User: Michael
 * Date: 9/16/18
 * Time: 10:21 PM
 */

namespace Disbot\Server;

define("GUILD_TEXT", 0);
define("DM", 1);
define("GROUP_DM", 2);
define("GUILD_VOICE", 3);

abstract class Channel {
	protected $id;
	protected $name;
	protected $type;

	/**
	 * @return integer The id of the channel.
	 */
	public function getId() { return $this->id; }

	/**
	 * @return integer The type of channel.
	 */
	public function getType() { return $this->type; }
}