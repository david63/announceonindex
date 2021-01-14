<?php
/**
*
* @package Announcements on index
* @copyright (c) 2015 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\announceonindex\controller;

use phpbb\config\config;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use phpbb\language\language;
use phpbb\log\log;
use david63\announceonindex\core\functions;

/**
* Admin controller
*/
class admin_controller
{
	/** @var config */
	protected $config;

	/** @var request */
	protected $request;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/** @var language */
	protected $language;

	/** @var log */
	protected $log;

	/** @var functions */
	protected $functions;

	/** @var string */
	protected $ext_images_path;

	/** @var string Custom form action */
	protected $u_action;

	/**
	* Constructor for admin controller
	*
	* @param config			$config				Config object
	* @param request		$request			Request object
	* @param template		$template			Template object
	* @param user			$user				User object
	* @param language		$language			Language object
	* @param log			$log				Log object
	* @param functions		functions			Functions for the extension
	* @param string			$ext_images_path    Path to this extension's images
	*
	* @return \david63\announceonindex\controller\admin_controller
	* @access public
	*/
	public function __construct(config $config, request $request, template $template, user $user, language $language, log $log, functions $functions, string $ext_images_path)
	{
		$this->config			= $config;
		$this->request			= $request;
		$this->template			= $template;
		$this->user				= $user;
		$this->language			= $language;
		$this->log				= $log;
		$this->functions		= $functions;
		$this->ext_images_path	= $ext_images_path;
	}

	/**
	* Display the options a user can configure for this extension
	*
	* @return null
	* @access public
	*/
	public function display_options()
	{
		// Add the language files
		$this->language->add_lang(array('acp_announceonindex', 'acp_common'), $this->functions->get_ext_namespace());

		// Create a form key for preventing CSRF attacks
		$form_key = 'announce_on_index';
		add_form_key($form_key);

		$back = false;

		// Is the form being submitted
		if ($this->request->is_set_post('submit'))
		{
			// Is the submitted form is valid
			if (!check_form_key($form_key))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			// If no errors, process the form data
			// Set the options the user configured
			$this->set_options();

			// Add option settings change action to the admin log
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'ANNOUNCE_ON_INDEX_LOG');

			// Option settings have been updated and logged
			// Confirm this to the user and provide link back to previous page
			trigger_error($this->language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
		}

		// Template vars for header panel
		$version_data	= $this->functions->version_check();

		// Are the PHP and phpBB versions valid for this extension?
		$valid = $this->functions->ext_requirements();

		$this->template->assign_vars(array(
			'DOWNLOAD'			=> (array_key_exists('download', $version_data)) ? '<a class="download" href =' . $version_data['download'] . '>' . $this->language->lang('NEW_VERSION_LINK') . '</a>' : '',

			'EXT_IMAGE_PATH'	=> $this->ext_images_path,

			'HEAD_TITLE'		=> $this->language->lang('ANNOUNCE_ON_INDEX'),
			'HEAD_DESCRIPTION'	=> $this->language->lang('ANNOUNCE_ON_INDEX_EXPLAIN'),

			'NAMESPACE'			=> $this->functions->get_ext_namespace('twig'),

			'PHP_VALID' 		=> $valid[0],
			'PHPBB_VALID' 		=> $valid[1],

			'S_BACK'			=> $back,
			'S_VERSION_CHECK'	=> (array_key_exists('current', $version_data)) ? $version_data['current'] : false,

			'VERSION_NUMBER'	=> $this->functions->get_meta('version'),
		));

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'ALLOW_EVENTS'				=> isset($this->config['announce_event']) ? $this->config['announce_event'] : '',
			'ALLOW_GUESTS'				=> isset($this->config['announce_guest']) ? $this->config['announce_guest'] : '',
			'ANNOUNCE_AVATAR'			=> isset($this->config['announce_avatar']) ? $this->config['announce_avatar'] : '',
			'ANNOUNCE_AVATAR_SIZE'		=> isset($this->config['announce_avatar_size']) ? $this->config['announce_avatar_size'] : '',
			'ANNOUNCE_ON_INDEX_ENABLED'	=> isset($this->config['announce_on_index_enable']) ? $this->config['announce_on_index_enable'] : '',

			'SHOW_ANNOUNCEMENTS'		=> isset($this->config['announce_announcement_on_index']) ? $this->config['announce_announcement_on_index'] : '',
			'SHOW_GLOBAL_ICON'			=> isset($this->config['announce_global_icon_on_index']) ? $this->config['announce_global_icon_on_index'] : '',
			'SHOW_GLOBALS'				=> isset($this->config['announce_global_on_index']) ? $this->config['announce_global_on_index'] : '',

			'U_ACTION' => $this->u_action,
		));
	}

	/**
	* Set the options a user can configure
	*
	* @return null
	* @access protected
	*/
	protected function set_options()
	{
		$this->config->set('announce_announcement_on_index', $this->request->variable('announce_announcement_on_index', 0));
		$this->config->set('announce_avatar', $this->request->variable('announce_avatar', 0));
		$this->config->set('announce_avatar_size', $this->request->variable('announce_avatar_size', 30));
		$this->config->set('announce_event', $this->request->variable('announce_event', 0));
		$this->config->set('announce_global_on_index', $this->request->variable('announce_global_on_index', 0));
		$this->config->set('announce_global_icon_on_index', $this->request->variable('announce_global_icon_on_index', 1));
		$this->config->set('announce_guest', $this->request->variable('announce_guest', 0));
		$this->config->set('announce_on_index_enable', $this->request->variable('announce_on_index_enable', 0));
	}

	/**
	* Set page url
	*
	* @param string $u_action Custom form action
	* @return null
	* @access public
	*/
	public function set_page_url($u_action)
	{
		return $this->u_action = $u_action;
	}
}
