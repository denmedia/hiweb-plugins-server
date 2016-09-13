<?php
	/**
	 * Created by PhpStorm.
	 * User: hiweb
	 * Date: 01.06.2016
	 * Time: 15:13
	 */


	if ( get_option( HW_PLUGINS_SERVER_OPTIONS_STATUS ) === false ) {
		add_option( HW_PLUGINS_SERVER_OPTIONS_STATUS, '0' );
	}


	add_action( 'admin_menu', '_hw_ps_options' );

	function _hw_ps_options() {

		add_submenu_page( 'options-general.php', 'hiWeb Server Settings', 'hiWeb Plugins Server', 'activate_plugins', HW_PLUGINS_SERVER_OPTIONS_PAGE_SLUG, '_hw_plugins_server_options_settings' );

		add_submenu_page( 'plugins.php', 'hiWeb Remote Plugins', 'hiWeb Remote Plugins', 'activate_plugins', HW_PLUGINS_SERVER_REMOTE_PAGE_SLUG, '_hw_plugins_server_remote_page' );


		if ( hiweb_plugins_server()->host()->get_status() ) {
			add_menu_page( 'Plugins Server', 'Plugins Server', 'activate_plugins', HW_PLUGINS_SERVER_PAGE_SLUG, '_hw_plugins_server_page', 'dashicons-list-view' );
		}

	}

	function _hw_plugins_server_options_settings() {
		include HW_PLUGINS_SERVER_DIR_TEMPLATES . '/options-page.php';
	}


	function _hw_plugins_server_page() {
		include HW_PLUGINS_SERVER_DIR_TEMPLATES . '/server-page.php';
	}


	function _hw_plugins_server_remote_page() {
		include HW_PLUGINS_SERVER_DIR_TEMPLATES . '/remote-plugins.php';
	}