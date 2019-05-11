<?php
/**
*
* @package Announcements on index
* @copyright (c) 2015 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\announceonindex\migrations;

class version_2_1_0 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('david63\announceonindex\migrations\version_1_0_0');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('announce_avatar', 0)),
			array('config.add', array('announce_avatar_size', 30)),
			array('config.add', array('announce_global_icon_on_index', 1)),

			array('config.remove', array('version_globalonindex')),
		);
	}
}
