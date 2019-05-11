<?php
/**
*
* @package Announcements on index
* @copyright (c) 2015 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\announceonindex\migrations;

class version_1_0_0 extends \phpbb\db\migration\migration
{
	public function update_data()
	{
		return array(
			array('config.add', array('announce_announcement_on_index', '1')),
			array('config.add', array('announce_event', '1')),
			array('config.add', array('announce_global_on_index', '0')),
			array('config.add', array('announce_guest', '0')),
			array('config.add', array('announce_on_index_enable', '0')),
			array('config.add', array('version_globalonindex', '1.0.0')),

		// Add the ACP module
			array('module.add', array('acp', 'ACP_CAT_DOT_MODS', 'ANNOUNCE_ON_INDEX')),

			array('module.add', array(
				'acp', 'ANNOUNCE_ON_INDEX', array(
					'module_basename'	=> '\david63\announceonindex\acp\announceonindex_module',
					'modes'				=> array('main'),
				),
			)),
		);
	}
}
