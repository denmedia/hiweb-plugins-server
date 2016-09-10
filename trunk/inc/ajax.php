<?php
	/**
	 * Created by PhpStorm.
	 * User: hiweb
	 * Date: 01.06.2016
	 * Time: 20:21
	 */
	
	add_action('wp_ajax_hw_plugins_server_status_toggle', '_hw_plugins_server_status_toggle');
	function _hw_plugins_server_status_toggle() {
		hw_plugins_server()->do_server_status_toggle();
		include HW_PLUGINS_SERVER_DIR_TEMPLATES . '/options-page.php';
		die;
	}

	add_action('wp_ajax_hw_plugins_server_kickback_status_toggle', '_hw_plugins_server_kickback_status_toggle');
	function _hw_plugins_server_kickback_status_toggle() {
		hw_plugins_server()->do_server_kickback_status_toggle();
		include HW_PLUGINS_SERVER_DIR_TEMPLATES . '/options-page.php';
		die;
	}


	add_action('wp_ajax_hw_plugins_server_plugin_hosted', '_hw_plugins_server_plugin_hosted');
	function _hw_plugins_server_plugin_hosted() {
		$do = $_POST['do'];
		$slug = $_POST['plugin'];
		if ($do == 'hosted') {
			$R = hw_plugins_server()->do_server_plugin_hosted($slug);
		}
		if ($do == 'unhosted') {
			$R = hw_plugins_server()->do_server_plugin_hosted($slug, false);
		}
		ob_start();
		_hw_plugins_server_page();
		$html = ob_get_clean();
		echo json_encode(array('result' => $R, 'html' => $html));
		die;
	}


	add_action('wp_ajax_hw_plugins_server_plugin_remote', '_hw_plugins_server_plugin_remote');
	function _hw_plugins_server_plugin_remote() {
		ob_start();
		$do = $_POST['do'];
		$slug = $_POST['plugin'];
		if ($do == 'download') {
			$R = hw_plugins_server()->do_remote_download_plugin($slug, false);
		}
		if ($do == 'activate') {
			$R = hw_plugins_server()->do_activate($slug);
		}
		if ($do == 'deactivate') {
			$R = hw_plugins_server()->do_deactivate($slug);
		}
		if ($do == 'remove') {
			$R = hw_plugins_server()->do_remove_plugin($slug);
		}
		_hw_plugins_server_remote_page();
		$html = ob_get_clean();
		echo json_encode(array('result' => $R, 'html' => $html));
		die;
	}
	
	
	add_action('wp_ajax_hw_plugins_server_remote_url_update', '_hw_plugins_server_remote_url_update');
	function _hw_plugins_server_remote_url_update() {
		$bool = update_option(HW_PLUGINS_SERVER_OPTIONS_REMOTE_URL, $_POST['url']);
		if ($bool == false) {
			$R = array('result' => false, 'message' => 'Не удалось внедрить значение ['.$_POST['url'].'] ключа [' . HW_PLUGINS_SERVER_OPTIONS_REMOTE_URL . '] в опции...');
		} else {
			$R = array('result' => true, 'message' => hw_plugins_server()->get_remote_status($_POST['url']));
		}
		echo json_encode($R);
		die;
	}
	
	
	add_action('wp_ajax_nopriv_hw_plugins_server_get', '_hw_plugins_server_get');
	function _hw_plugins_server_get() {
		$R = array(
			'status' => hw_plugins_server()->get_server_status(),
			'archives_url' => HW_PLUGINS_SERVER_ROOT_URL,
			'plugins' => array()
		);
		if ($R['status']) {
			$R['plugins'] = hw_plugins_server()->get_server_plugins();
		}
		echo json_encode($R);
		die;
	}