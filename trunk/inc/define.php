<?php
	/**
	 * Created by PhpStorm.
	 * User: hiweb
	 * Date: 01.06.2016
	 * Time: 15:35
	 */
	if( ! defined( 'HW_PLUGINS_SERVER_DIR' ) ){
		define( 'HW_PLUGINS_SERVER_DIR', dirname( dirname( __FILE__ ) ) );
	}
	if( ! defined( 'HW_PLUGINS_SERVER_DIR_URL' ) ){
		define( 'HW_PLUGINS_SERVER_DIR_URL', rtrim( plugin_dir_url( dirname(__FILE__) ), '/' ) );
	}
	if( ! defined( 'HW_PLUGINS_SERVER_DIR_TEMPLATES' ) ){
		define( 'HW_PLUGINS_SERVER_DIR_TEMPLATES', HW_PLUGINS_SERVER_DIR . '/templates' );
	}
	if( ! defined( 'HW_PLUGINS_SERVER_OPTIONS_STATUS' ) ){
		define( 'HW_PLUGINS_SERVER_OPTIONS_STATUS', 'hw_plugins_server_status' );
	}
	if( ! defined( 'HW_PLUGINS_SERVER_OPTIONS_KICKBACK_STATUS' ) ){
		define( 'HW_PLUGINS_SERVER_OPTIONS_KICKBACK_STATUS', 'hw_plugins_server_kickback_status' );
	}
	if( ! defined( 'HW_PLUGINS_SERVER_OPTIONS_REMOTE_URL' ) ){
		define( 'HW_PLUGINS_SERVER_OPTIONS_REMOTE_URL', 'hw_plugins_server_remote_url' );
	}
	if( ! defined( 'HW_PLUGINS_SERVER_OPTIONS_PAGE_SLUG' ) ){
		define( 'HW_PLUGINS_SERVER_OPTIONS_PAGE_SLUG', 'hiweb-plugins-server-settings' );
	}
	if( ! defined( 'HW_PLUGINS_SERVER_REMOTE_PAGE_SLUG' ) ){
		define( 'HW_PLUGINS_SERVER_REMOTE_PAGE_SLUG', 'hiweb-plugins-server-remote' );
	}
	if( ! defined( 'HW_PLUGINS_SERVER_PAGE_SLUG' ) ){
		define( 'HW_PLUGINS_SERVER_PAGE_SLUG', 'hiweb-plugins-server' );
	}
	if( ! defined( 'HW_PLUGINS_SERVER_OPTIONS_DATABASE' ) ){
		define( 'HW_PLUGINS_SERVER_OPTIONS_DATABASE', 'hw_plugins_server_database' );
	}
	if( ! defined( 'HW_PLUGINS_SERVER_ROOT' ) ){
		define( 'HW_PLUGINS_SERVER_ROOT', WP_CONTENT_DIR . '/hiweb-plugins-server-root' );
	}
	if( ! defined( 'HW_PLUGINS_SERVER_ROOT_URL' ) ){
		define( 'HW_PLUGINS_SERVER_ROOT_URL', WP_CONTENT_URL . '/hiweb-plugins-server-root' );
	}
	if( ! defined( 'HW_PLUGINS_SERVER_HOST_INFO_FILE_LIMIT' ) ){
		define( 'HW_PLUGINS_SERVER_HOST_INFO_FILE_LIMIT', 1024 );
	}