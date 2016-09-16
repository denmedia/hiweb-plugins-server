<?php
	/*
	Plugin Name: hiWeb Plugins Server
	Plugin URI: http://hiweb.moscow/plugins-server
	Description: Create your own plug-ins server. Создай свой собственный сервер плагинов.
	Version: 2.0.0.0
	Author: Den Media
	Author URI: http://hiweb.moscow
	*/
	if( !function_exists( 'hiweb_plugins_server' ) ) :
		
		function hiweb_plugins_server(){
			static $class;
			if( !$class instanceof hw_plugins_server ){
				$class = new hw_plugins_server();
			}
			return $class;
		}
		
		require_once 'inc/define.php';
		require_once 'inc/class.php';
		require_once 'inc/class-host.php';
		require_once 'inc/class-local.php';
		require_once 'inc/class-remote.php';
		require_once 'inc/class-hooks.php';
		require_once 'inc/options.php';
		require_once 'inc/hooks.php';
		require_once 'inc/script-styles.php';
	endif;