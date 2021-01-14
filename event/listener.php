<?php
/**
*
* @package Announcements on index
* @copyright (c) 2015 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\announceonindex\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use david63\announceonindex\controller\main_controller;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var main_controller */
	protected $indexoutput;

	/**
	* Constructor for listener
	*
	* @param main_controller	$main_controller	Main controller
	*
	* @access public
	*/
	public function __construct(main_controller $main_controller)
	{
		 $this->main_controller = $main_controller;
	}

	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	* @static
	* @access public
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.index_modify_page_title' => 'add_announcements_to_index',
		);
	}

	/**
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function add_announcements_to_index($event)
	{
		 $this->main_controller->indexoutput();
	}
}
