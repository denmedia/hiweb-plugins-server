<?php
	/**
	 * Created by PhpStorm.
	 * User: hiweb
	 * Date: 01.06.2016
	 * Time: 21:02
	 */


	add_filter( 'plugin_action_links', array( hiweb_plugins_server()->hooks(), 'plugin_action_links' ), 99999, 2 );
	add_filter( 'plugin_action_links_hiweb-plugins-server/hiweb-plugins-server.php', array( hiweb_plugins_server()->hooks(), 'plugin_action_links_settings' ) );
	add_action( 'admin_notices', array( hiweb_plugins_server()->hooks(), 'admin_notices' ) );
	add_action( 'pre_current_active_plugins', array( hiweb_plugins_server()->hooks(), 'pre_current_active_plugins' ) );