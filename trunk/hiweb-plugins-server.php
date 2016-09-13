<?php
	/**
	 * Created by PhpStorm.
	 * User: hiweb
	 * Date: 01.06.2016
	 * Time: 15:11
	 */

	/*
	Plugin Name: hiWeb Plugins Server
	Plugin URI: http://hiweb.moscow/plugins-server
	Description: Create your own plug-ins server. Создай свой собственный сервер плагинов.
	Version: 2.0.1.0
	Author: Den Media
	Author URI: http://hiweb.moscow
	*/


	require_once 'define.php';
	require_once 'inc/class.php';
	require_once 'inc/ajax.php';
	require_once 'inc/options.php';
	require_once 'inc/hooks.php';
	require_once 'inc/script-styles.php';


	//todo
	hiweb_plugins_server()->local()->plugin('hiweb-core-2/hiweb-core-2.php');