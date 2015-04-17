<?php

namespace clausi\tenefixes\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class main_listener implements EventSubscriberInterface
{
	
	static public function getSubscribedEvents()
	{
		return array(
			'core.page_footer'    => 'tene_page_footer',
		);
	}

	protected $helper;
	protected $template;
	protected $config;
	protected $auth;
	protected $request;
	protected $user;
	protected $phpbb_root_path;
	protected $php_ext;


	public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\config\config $config, \phpbb\auth\auth $auth, \phpbb\request\request $request, \phpbb\user $user, $phpbb_root_path, $php_ext)
	{
		$this->helper = $helper;
		$this->template = $template;
		$this->config = $config;
		$this->auth = $auth;
		$this->request = $request;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}
	
	
	public function tene_page_footer($event)
	{
		if($this->auth->acl_get('u_viewonline'))
		{
			$this->change_memberlist_ordering();
		}
	}
	
	
	private function change_memberlist_ordering()
	{
		$default_key = 'c';
		
		$sort_key = $this->request->variable('sk', 'l');
		$sort_dir = $this->request->variable('sd', 'a');
		$mode =  $this->request->variable('mode', '');
		$select_single 	= $this->request->variable('select_single', false);
		
		// Build a relevant pagination_url
		$params = $sort_params = array();

		// We do not use request_var() here directly to save some calls (not all variables are set)
		$check_params = array(
			'g'				=> array('g', 0),
			'sk'			=> array('sk', $default_key),
			'sd'			=> array('sd', 'a'),
			'form'			=> array('form', ''),
			'field'			=> array('field', ''),
			'select_single'	=> array('select_single', $select_single),
			'username'		=> array('username', '', true),
			'email'			=> array('email', ''),
			'jabber'		=> array('jabber', ''),
			'search_group_id'	=> array('search_group_id', 0),
			'joined_select'	=> array('joined_select', 'lt'),
			'active_select'	=> array('active_select', 'lt'),
			'count_select'	=> array('count_select', 'eq'),
			'joined'		=> array('joined', ''),
			'active'		=> array('active', ''),
			'count'			=> (request_var('count', '') !== '') ? array('count', 0) : array('count', ''),
			'ip'			=> array('ip', ''),
			'first_char'	=> array('first_char', ''),
		);

		$u_first_char_params = array();
		foreach ($check_params as $key => $call)
		{
			if (!isset($_REQUEST[$key]))
			{
				continue;
			}

			$param = call_user_func_array('request_var', $call);
			$param = urlencode($key) . '=' . ((is_string($param)) ? urlencode($param) : $param);
			$params[] = $param;

			if ($key != 'first_char')
			{
				$u_first_char_params[] = $param;
			}
			if ($key != 'sk' && $key != 'sd')
			{
				$sort_params[] = $param;
			}
		}
		
		if ($mode)
		{
			$params[] = "mode=$mode";
			$u_first_char_params[] = "mode=$mode";
		}
		$sort_params[] = "mode=$mode";
		
		$sort_url = append_sid("{$this->phpbb_root_path}memberlist.$this->php_ext", implode('&amp;', $sort_params));
		$this->template->assign_var('U_SORT_ACTIVE', ($this->auth->acl_get('u_viewonline')) ? $sort_url . '&amp;sk=l&amp;sd=' . (($sort_key == 'l' && $sort_dir == 'a') ? 'd' : 'a') : '');
	}
	
	
	private function var_display($var)
	{
		echo "<pre>";
		print_r($var);
		echo "</pre>";
	}

}
