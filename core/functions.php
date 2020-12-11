<?php
/**
*
* @package Announcements on index
* @copyright (c) 2015 david63
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace david63\announceonindex\core;

use phpbb\extension\manager;
use phpbb\exception\version_check_exception;

/**
* functions
*/
class functions
{
	/** @var \phpbb\extension\manager */
	protected $phpbb_extension_manager;

	/**
	* Constructor for functions
	*
	* @param \phpbb\extension\manager 	$phpbb_extension_manager	Extension manager
	*
	* @access public
	*/
	public function __construct(manager $phpbb_extension_manager)
	{
		$this->ext_manager	= $phpbb_extension_manager;

		$this->namespace	= __NAMESPACE__;
	}

	/**
	* Get the extension's namespace
	*
	* @return $extension_name
	* @access public
	*/
	public function get_ext_namespace($mode = 'php')
	{
		// Let's extract the extension name from the namespace
		$extension_name = substr($this->namespace, 0, -(strlen($this->namespace) - strrpos($this->namespace, '\\')));

		// Now format the extension name
		switch ($mode)
		{
			case 'php':
				$extension_name = str_replace('\\', '/', $extension_name);
			break;

			case 'twig':
				$extension_name = str_replace('\\', '_', $extension_name);
			break;
		}

		return $extension_name;
	}

	/**
	* Check if there is an updated version of the extension
	*
	* @return $new_version
	* @access public
	*/
	public function version_check()
	{
		if ($this->get_meta('host') == 'www.phpbb.com')
		{
			$port 	= 'https://';
			$stable	= null;
		}
		else
		{
			$port 	= 'http://';
			$stable = 'unstable';
		}

		// Can we access the version srver?
		if (@fopen($port . $this->get_meta('host') . $this->get_meta('directory') . '/' . $this->get_meta('filename'), 'r'))
		{
			try
			{
				$md_manager 	= $this->ext_manager->create_extension_metadata_manager($this->get_ext_namespace());
				$version_data	= $this->ext_manager->version_check($md_manager, true, false, $stable);
			}
			catch (version_check_exception $e)
			{
				$version_data['current'] = 'fail';
			}
		}
		else
		{
			$version_data['current'] = 'fail';
		}

		return $version_data;
	}

	/**
	* Get a meta_data key value
	*
	* @return $meta_data
	* @access public
	*/
	public function get_meta($data)
	{
		$meta_data	= '';
		$md_manager = $this->ext_manager->create_extension_metadata_manager($this->get_ext_namespace());

		foreach (new \RecursiveIteratorIterator(new \RecursiveArrayIterator($md_manager->get_metadata('all'))) as $key => $value)
		{
			if ($data === $key)
			{
				$meta_data = $value;
			}
		}

		return $meta_data;
	}

	/**
	 * Check that the reqirements are met for this extension
	 *
	 * @return array
	 * @access public
	 */
	public function ext_requirements()
	{
		$php_valid = $phpbb_valid = false;

		// Check the PHP version is valid
		$php_versn = htmlspecialchars_decode($this->get_meta('php'));

		if ($php_versn)
		{
			// Get the conditions
			preg_match('/\d/', $php_versn, $php_pos, PREG_OFFSET_CAPTURE);
			$php_valid = phpbb_version_compare(PHP_VERSION, substr($php_versn, $php_pos[0][1]), substr($php_versn, 0, $php_pos[0][1]));
		}

		// Check phpBB versions are valid
		$phpbb_versn = htmlspecialchars_decode($this->get_meta('phpbb/phpbb'));
		$phpbb_vers  = explode(',', $phpbb_versn);

		if ($phpbb_vers[0])
		{
			// Get the first conditions
			preg_match('/\d/', $phpbb_vers[0], $phpbb_pos_0, PREG_OFFSET_CAPTURE);
			$phpbb_valid = phpbb_version_compare(PHPBB_VERSION, substr($phpbb_vers[0], $phpbb_pos_0[0][1]), substr($phpbb_vers[0], 0, $phpbb_pos_0[0][1]));

			if ($phpbb_vers[1] && !$phpbb_valid)
			{
				// Get the second conditions
				preg_match('/\d/', $phpbb_vers[1], $phpbb_pos_1, PREG_OFFSET_CAPTURE);
				$phpbb_valid = phpbb_version_compare(PHPBB_VERSION, substr($phpbb_vers[0], $phpbb_pos_0[0][1]), substr($phpbb_vers[0], 0, $phpbb_pos_0[0][1]));
			}
		}

		return [$php_valid, $phpbb_valid];
	}
}
