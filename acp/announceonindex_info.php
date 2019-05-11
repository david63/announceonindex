<?php
/**
*
* @package Announcements on index
* @copyright (c) 2015 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\announceonindex\acp;

class announceonindex_info
{
	function module()
	{
		return array(
			'filename'	=> '\david63\announceonindex\acp\announceonindex_module',
			'title'		=> 'ANNOUNCE_ON_INDEX',
			'modes'		=> array(
				'main'	=> array('title' => 'ANNOUNCE_ON_INDEX_MANAGE', 'auth' => 'ext_david63/announceonindex && acl_a_forum', 'cat' => array('ANNOUNCE_ON_INDEX')),
			),
		);
	}
}
