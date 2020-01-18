<?php
/**
*
* @package Announcements on index
* @copyright (c) 2015 david63
* * @license GNU General Public License, version 2 (GPL-2.0)
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'ALLOW_EVENTS'					=> 'Allow template events',
	'ALLOW_EVENTS_EXPLAIN'			=> 'Allow the use of template events in the announcements.<br>Turn this off if other template events are causing problems and/or undesirable results.',
	'ALLOW_GUESTS'					=> 'Allow guests to see announcements',
	'ALLOW_GUESTS_EXPLAIN'			=> 'Allow guests to see the announcements.',

	'ANNOUNCE_AVATAR'				=> 'Show last poster’s avatar',
	'ANNOUNCE_AVATAR_SIZE'			=> 'Avatar size',
	'ANNOUNCE_AVATAR_SIZE_EXPLAIN'	=> 'The size of the last poster’s avatar',
	'ANNOUNCE_ON_INDEX'				=> 'Announcements on index',
	'ANNOUNCE_ON_INDEX_ENABLE'		=> 'Enable announcements',
	'ANNOUNCE_ON_INDEX_EXPLAIN' 	=> 'Manage the announcement options.',
	'ANNOUNCE_ON_INDEX_OPTIONS'		=> 'Announcement options',

	'SHOW_ANNOUNCEMENTS'			=> 'Show announcements',
	'SHOW_ANNOUNCEMENTS_EXPLAIN'	=> 'Display announcements on the index page.',
	'SHOW_GLOBAL_ICON'				=> 'Show global topic icon',
	'SHOW_GLOBAL_ICON_EXPLAIN'		=> 'Display global topic icon when the announcement is a global announcement.<br><strong>You may need to turn this off if there are no global topic icons for your style.</strong>',
	'SHOW_GLOBALS'					=> 'Show global announcements',
	'SHOW_GLOBALS_EXPLAIN'			=> 'Display global announcements on the index page.',
));
