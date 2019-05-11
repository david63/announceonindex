<?php
/**
*
* @package Announcements on index
* @copyright (c) 2015 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\announceonindex\acp;

class announceonindex_module
{
	public $u_action;

	function main($id, $mode)
	{
		global $phpbb_container;

		$this->tpl_name		= 'announce_on_index';
		$this->page_title	= $phpbb_container->get('language')->lang('ANNOUNCE_ON_INDEX');

		// Get an instance of the admin controller
		$admin_controller = $phpbb_container->get('david63.announceonindex.admin.controller');

		// Make the $u_action url available in the admin controller
		$admin_controller->set_page_url($this->u_action);

		$admin_controller->display_options();
	}
}
