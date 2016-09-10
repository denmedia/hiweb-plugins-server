<?php
	/**
	 * Created by PhpStorm.
	 * User: hiweb
	 * Date: 01.06.2016
	 * Time: 21:02
	 */





	add_filter('plugin_action_links','_hw_plugins_server_plugin_action_links', 99999, 2);

	function _hw_plugins_server_plugin_action_links($links, $plugin){
		if($plugin != 'hiweb-plugins-server/hiweb-plugins-server.php') $links[] = '<a href=""><i class="dashicons dashicons-upload"></i> Upload To Server</a>';
		return $links;
	}


	add_filter('plugin_action_links_hiweb-plugins-server/hiweb-plugins-server.php', '_hw_plugins_server_plugin_action_links_settings');

	function _hw_plugins_server_plugin_action_links_settings($links) {
		$links[] = '<a href="' . esc_url(get_admin_url(null, 'options-general.php?page=' . HW_PLUGINS_SERVER_OPTIONS_PAGE_SLUG)) . '">Client / Server Settings</a>';
		return $links;
	}
