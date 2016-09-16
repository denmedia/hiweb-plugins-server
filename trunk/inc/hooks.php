<?php
	/**
	 * Created by PhpStorm.
	 * User: hiweb
	 * Date: 01.06.2016
	 * Time: 21:02
	 */
	///HOOKS
	add_filter( 'plugin_action_links', array( hiweb_plugins_server()->hooks(), 'plugin_action_links' ), 99999, 2 );
	add_filter( 'plugin_action_links_hiweb-plugins-server/hiweb-plugins-server.php', array( hiweb_plugins_server()->hooks(), 'plugin_action_links_settings' ) );
	add_action( 'admin_notices', array( hiweb_plugins_server()->hooks(), 'admin_notices' ) );
	add_action( 'pre_current_active_plugins', array( hiweb_plugins_server()->hooks(), 'pre_current_active_plugins' ) );
	//
	///AJAX
	add_action( 'wp_ajax_hw_plugins_server_status_toggle', array( hiweb_plugins_server()->hooks(), 'ajax_host_toggle_status' ) );
	add_action( 'wp_ajax_hw_plugins_server_kickback_status_toggle', array( hiweb_plugins_server()->hooks(), 'ajax_host_toggle_kickback_status' ) );
	add_action( 'wp_ajax_hw_plugins_server_plugin_hosted', array( hiweb_plugins_server()->hooks(), 'ajax_host_plugin_action' ) );
	add_action( 'wp_ajax_hw_plugins_server_plugin_remote', array( hiweb_plugins_server()->hooks(), 'ajax_remote_plugin_action' ) );
	add_action( 'wp_ajax_hw_plugins_server_remote_url_update', array( hiweb_plugins_server()->hooks(), 'ajax_remote_url_update' ) );
	add_action( 'wp_ajax_nopriv_hw_plugins_server_get', array( hiweb_plugins_server()->hooks(), 'ajax_server_get' ) );