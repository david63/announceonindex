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
use phpbb\template\template;
use phpbb\user;
use phpbb\db\driver\driver_interface;
use phpbb\content_visibility;
use phpbb\auth\auth;
use phpbb\cache\service;
use phpbb\path_helper;
use phpbb\language\language;
use david63\announceonindex\core\functions;

class main_controller
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\template\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string PHP extension */
	protected $phpEx;

	/** @var \phpbb\content_visibility */
	protected $content_visibility;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \david63\announceonindex\core\functions */
	protected $functions;

	/** @var string custom tables */
	protected $tables;

	/**
	* Constructor for listener
	*
	* @param \phpbb\config\config						$config		Config object
	* @param \phpbb\template\template\template			$template	Template object
	* @param \phpbb\user                				$user		User object
	* @param \phpbb\db\driver\driver_interface			$db			The db connection
	* @param string 									$root_path
	* @param string 									$php_ext
	* @param \phpbb\content_visibility 					$content_visibility
	* @param \phpbb\auth\auth 							$auth
	* @param \phpbb\cache\service						$cache
	* @param \phpbb\path_helper							$path_helper	phpBB path helper
	* @param \phpbb\language\language					$language
	* @param \david63\announceonindex\core\functions	functions	Functions for the extension
	* @param array										$tables			phpBB db tables
	*
	* @return \david63\announceonindex\controller\main_controller
	* @access public
	*/
	public function __construct(config $config, template $template, user $user, driver_interface $db, string $root_path, string $php_ext, content_visibility $content_visibility, auth $auth, service $cache, path_helper $path_helper, language $language, functions $functions, array $tables)
	{
		$this->config				= $config;
		$this->template				= $template;
		$this->user					= $user;
		$this->db					= $db;
		$this->root_path			= $root_path;
		$this->phpEx				= $php_ext;
		$this->content_visibility	= $content_visibility;
		$this->auth					= $auth;
		$this->cache				= $cache;
		$this->path_helper 			= $path_helper;
		$this->language				= $language;
		$this->functions			= $functions;
		$this->tables				= $tables;
	}

	/**
	* Controller for announceonindex
	*
	* @param string		$name
	* @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	*/
	public function indexoutput()
	{
		if ($this->config['announce_on_index_enable'] && ($this->config['announce_global_on_index'] || $this->config['announce_announcement_on_index']))
		{
			$sql_from	= $this->tables['topics'] . ' t ';
			$sql_select	= '';

			if ($this->config['load_db_track'])
			{
				$sql_from .= ' LEFT JOIN ' . $this->tables['topics_posted'] . ' tp ON (tp.topic_id = t.topic_id
					AND tp.user_id = ' . (int) $this->user->data['user_id'] . ')';
				$sql_select .= ', tp.topic_posted';
			}

			if ($this->config['load_db_lastread'])
			{
				$sql_from .= ' LEFT JOIN ' . $this->tables['topics_track'] . ' tt ON (tt.topic_id = t.topic_id
					AND tt.user_id = ' . (int) $this->user->data['user_id'] . ')';
				$sql_select .= ', tt.mark_time';
			}

			// Get cleaned up list... return only those forums not having the f_read permission
			$forum_ary = $this->auth->acl_getf('f_read', true);
			$forum_ary = array_unique(array_keys($forum_ary));

			// Determine first forum the user is able to read into - for global announcement link
			$sql = 'SELECT forum_id
				FROM ' . $this->tables['forums'] . '
				WHERE forum_type = ' . FORUM_POST;

			if (is_array($forum_ary))
			{
				$sql .= ' AND ' . $this->db->sql_in_set('forum_id', $forum_ary, false);
			}

			$result = $this->db->sql_query_limit($sql, 1);

			$g_forum_id = (int) $this->db->sql_fetchfield('forum_id');
			$this->db->sql_freeresult($result);

			if ($g_forum_id)
			{
				$topic_list	= $rowset = [];
				$sql_where 	= POST_GLOBAL;

				if ($this->config['announce_announcement_on_index'])
				{
					$sql_where = POST_ANNOUNCE;
				}

				if ($this->config['announce_global_on_index'] && $this->config['announce_announcement_on_index'])
				{
					$sql_where = POST_ANNOUNCE . ' OR t.topic_type =  ' . POST_GLOBAL;
				}

				$sql = "SELECT t.* $sql_select
					FROM $sql_from
					WHERE t.topic_type = $sql_where
					ORDER BY t.topic_last_post_time DESC";

				$result = $this->db->sql_query($sql);

				while ($row = $this->db->sql_fetchrow($result))
				{
					$topic_list[] = $row['topic_id'];
					$rowset[$row['topic_id']] = $row;
				}
				$this->db->sql_freeresult($result);

				$topic_tracking_info = [];
				if ($this->config['load_db_lastread'] && $this->user->data['is_registered'])
				{
					$topic_tracking_info = get_topic_tracking(0, $topic_list, $rowset, false, $topic_list);
				}
				else
				{
					$topic_tracking_info = get_complete_topic_tracking(0, $topic_list, $topic_list);
				}

				foreach ($topic_list as $topic_id)
				{
					$row = &$rowset[$topic_id];

					$forum_id = $row['forum_id'];
					$topic_id = $row['topic_id'];

					$unread_topic = (isset($topic_tracking_info[$topic_id]) && $row['topic_last_post_time'] > $topic_tracking_info[$topic_id]) ? true : false;

					// Grab icons
					$icons = $this->cache->obtain_icons();

					$folder_img = $folder_alt = '';
					if ($row['topic_type'] == POST_GLOBAL && $this->config['announce_global_icon_on_index'])
					{
						$folder_img = ($unread_topic) ? 'global_a_unread' : 'global_a_read';
					}
					else
					{
						$folder_img = ($unread_topic) ? 'announce_unread' : 'announce_read';
					}

					$folder_alt	= ($unread_topic) ? 'UNREAD_POSTS' : (($row['topic_status'] == ITEM_LOCKED) ? 'TOPIC_LOCKED' : 'NO_UNREAD_POSTS');

					if ($row['topic_status'] == ITEM_LOCKED)
					{
						$folder_img .= '_locked';
					}

					if (!empty($row['topic_posted']) && $row['topic_posted'])
					{
						$folder_img .= '_mine';
					}

					$this->template->assign_block_vars('topicrow', array(
						'FIRST_POST_TIME'		=> $this->user->format_date($row['topic_time']),
						'LAST_AUTHOR_AVATAR'	=> $this->get_last_poster_avatar($row['topic_last_poster_id']),
						'LAST_POST_TIME'		=> $this->user->format_date($row['topic_last_post_time']),
						'REPLIES'				=> $this->content_visibility->get_count('topic_posts', $row, $forum_id) - 1,
						'TOPIC_AUTHOR_FULL'		=> get_username_string('full', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
						'TOPIC_FOLDER_IMG_ALT'	=> $this->language->lang($folder_alt),
						'TOPIC_ICON_IMG'		=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['img'] : '',
						'TOPIC_IMG_STYLE'		=> $folder_img,
						'TOPIC_LAST_AUTHOR'		=> get_username_string('full', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
						'TOPIC_TITLE'			=> censor_text($row['topic_title']),
						'VIEWS'					=> $row['topic_views'],

						'S_UNREAD'				=> $unread_topic,

						'U_LAST_POST'			=> append_sid("{$this->root_path}viewtopic.$this->phpEx", "f=$g_forum_id&amp;t=$topic_id&amp;p=" . $row['topic_last_post_id']) . '#p' . $row['topic_last_post_id'],
						'U_NEWEST_POST'			=> append_sid("{$this->root_path}viewtopic.$this->phpEx", "f=$g_forum_id&amp;t=$topic_id&amp;view=unread") . '#unread',
						'U_VIEW_TOPIC'			=> append_sid("{$this->root_path}viewtopic.$this->phpEx", "f=$g_forum_id&amp;t=$topic_id"),
					));
				}
			}

			$this->template->assign_vars(array(
				'NAMESPACE'				=> $this->functions->get_ext_namespace('twig'),

				'S_ALLOW_EVENTS'		=> $this->config['announce_event'],
				'S_ALLOW_GUESTS' 		=> $this->config['announce_guest'],
				'S_ANNOUNCE_ENABLED'	=> $this->config['announce_on_index_enable'],
				'S_SHOW_LAST_AVATAR'	=> $this->config['announce_avatar'],
			));
		}

	}

	// Get the last poster's avatar and resize it if necessary
	private function get_last_poster_avatar($user_id)
	{
		// No point doing this if it is not required
		if ($this->config['announce_avatar'])
		{
			// Just grab the fields that we need here
			$sql = 'SELECT user_avatar, user_avatar_height, user_avatar_width, user_avatar_type
				FROM ' . $this->tables['users'] . "
				WHERE user_id = $user_id";

			$result = $this->db->sql_query($sql);

			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			// Resize the avatar if necessary
			if ($row['user_avatar_width'] >= $row['user_avatar_height'])
			{
				$avatar_width = ($row['user_avatar_width'] > $this->config['announce_avatar_size']) ? $this->config['announce_avatar_size'] : $row['user_avatar_width'];
				$row['user_avatar_height'] = ($avatar_width == $this->config['announce_avatar_size']) ? round($this->config['announce_avatar_size'] / $row['user_avatar_width'] * $row['user_avatar_height']) : $row['user_avatar_height'];
				$row['user_avatar_width'] = $avatar_width;
			}
			else
			{
				$avatar_height = ($row['user_avatar_height'] > $this->config['announce_avatar_size']) ? $this->config['announce_avatar_size'] : $row['user_avatar_height'];
				$row['user_avatar_width'] = ($avatar_height == $this->config['announce_avatar_size']) ? round($this->config['announce_avatar_size'] / $row['user_avatar_height'] * $row['user_avatar_width']) : $row['user_avatar_width'];
				$row['user_avatar_height'] = $avatar_height;
			}

			$last_avatar = phpbb_get_user_avatar($row);

			// If no avatar then use "no avatar image" from the user's style
			if (!$last_avatar)
			{
				$theme_path		= $this->path_helper->get_web_root_path() . 'styles/' . rawurlencode($this->user->style['style_path']) . '/theme';
				$last_avatar	= '<img src="'  . $theme_path . '/images/no_avatar.gif" width="' . $this->config['announce_avatar_size'] . '" height="' . $this->config['announce_avatar_size'] . '" />';
			}

			return $last_avatar;
		}
	}
}
