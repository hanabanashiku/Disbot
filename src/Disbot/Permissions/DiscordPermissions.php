<?php

namespace Disbot\Permissions;

class DiscordPermissions {
	const CREATE_INSTANT_INVITE = 0x00000001;
	const KICK_MEMBERS = 0x00000002;
	const BAN_MEMBERS = 0x00000004;
	const ADMINISTRATOR = 0x00000008;
	const MANAGE_CHANNELS = 0x00000010;
	const MANAGE_GUILD = 0x00000020;
	const ADD_REACTIONS = 0x00000040;
	const VIEW_AUDIT_LOG = 0x00000080;
	const VIEW_CHANNEL = 0x00000400;
	const SEND_MESSAGES = 0x00000800;
	const SEND_TTS = 0x00001000;
	const MANAGE_MESSAGES = 0x00002000;
	const EMBED_LINKS = 0x00004000;
	const ATTACH_FILES = 0x00008000;
	const READ_MESSAGE_HISTORY = 0x00010000;
	const MENTION_EVERYONE = 0x00020000;
	const USE_EXTERNAL_EMOJIS = 0x00040000;
	const CONNECT_VOICE = 0x00100000;
	const SPEAK = 0x00200000;
	const MUTE_MEMBERS = 0x00400000;
	const DEAFEN_MEMBERS = 0x00800000;
	const MOVE_MEMBERS = 0x01000000;
	const USE_VAD = 0x02000000;
	const PRIORITY_SPEAKER = 0x00000100;
	const CHANGE_NICKNAME = 0x04000000;
	const MANAGE_NICKNAMES = 0x08000000;
	const MANAGE_ROLES = 0x10000000;
	const MANAGE_WEBHOOKS = 0x20000000;
	const MANAGE_EMOJIS = 0x40000000;

	/**
	 * Gets a permission integer from the selection of permissions.
	 * All the applicable permissions are constants of the DiscordPermissions class.
	 * @param integer ...$permissions
	 * @return integer The permission integer.
	 */
	public static function getPermissionInt(...$permissions){
		$res = 0x0;
		foreach($permissions as $p)
			$res = $res | $p;
		return $res;
	}

	/**
	 * @return integer Permission set as if the bot was just a normal user.
	 * Change Nickname, Read Messages, Send TTS, Embed links, Read Message History, Send Messages, Attach files,
	 * Mention @everyone, Add Reactions, View Channel, Connect, Mute Members, Move Members, Speak, Use Voice Activity.
	 */
	public static function getUserLevelPermissions(){
		return 125033536;
	}

	/**
	 * @return integer All available permissions except administrator (i.e. still has to follow hierarchy).
	 * May require 2FA enabled on owner's account if the server requires 2FA.
	 */
	public static function getAllPermissions(){
		return 2146958839;
	}

	/**
	 * @return integer User permissions, and kicking, banning, creating invites, deleting messages.
	 */
	public static function getModeratorPermissions(){
		return 133692743;
	}


}