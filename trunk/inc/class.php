<?php
	/**
	 * Created by PhpStorm.
	 * User: hiweb
	 * Date: 01.06.2016
	 * Time: 15:33
	 */
	
	
	if (!class_exists('hw_plugins_server')) {
		
		function hw_plugins_server() {
			static $class;
			if (!$class instanceof hw_plugins_server) $class = new hw_plugins_server();
			return $class;
		}
		
		
		class hw_plugins_server {
			
			/**
			 * Возвращает статус сервера. TRUE - сервер включен
			 * @return bool
			 */
			public function get_server_status() {
				return !(get_option(HW_PLUGINS_SERVER_OPTIONS_STATUS, '0') == '0');
			}
			
			/**
			 * Переключает статус сервера
			 * @return bool
			 */
			public function do_server_status_toggle() {
				$status = get_option(HW_PLUGINS_SERVER_OPTIONS_STATUS, '0') === '0';
				return update_option(HW_PLUGINS_SERVER_OPTIONS_STATUS, $status ? '1' : '0');
			}

			/**
			 * Возвращает статус KICKBACK сервера. TRUE - сервер включен
			 * @return bool
			 */
			public function get_server_kickback_status() {
				return ($this->get_server_status() && !(get_option(HW_PLUGINS_SERVER_OPTIONS_KICKBACK_STATUS, '0') == '0'));
			}

			/**
			 * Переключает статус KICKBACK сервера
			 * @return bool
			 */
			public function do_server_kickback_status_toggle() {
				$status = get_option(HW_PLUGINS_SERVER_OPTIONS_KICKBACK_STATUS, '0') === '0';
				return update_option(HW_PLUGINS_SERVER_OPTIONS_KICKBACK_STATUS, $status ? '1' : '0');
			}
			
			
			public function get_server_plugins($onlyHosted = true) {
				$database = $this->get_database_plugins();
				$plugins = get_plugins();
				$R = array();
				foreach ($database as $slug => $plugin) {
					if ((!$onlyHosted || $plugin['hosted'] == 1) && isset($plugins[$slug])) $R[$slug] = $plugins[$slug] + $plugin;
				}
				return $R;
			}
			
			
			public function do_server_plugin_hosted($slug, $hosted = true) {
				$data = get_plugin_data($slug);
				$archiveFileName = md5($slug) . '.zip';
				$archivePath = HW_PLUGINS_SERVER_ROOT . '/' . $archiveFileName;
				$pluginFiles = get_plugin_files($slug);
				
				if (file_exists($archivePath)) @unlink($archivePath);
				
				$zip = new ZipArchive();
				$zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
				
				foreach ($pluginFiles as $file) {
					$path = WP_PLUGIN_DIR . '/' . $file;
					if (!is_dir($path)) {
						$zip->addFile($path, $file);
					}
				}
				$zip->close();
				$plugin_data = get_plugin_data($slug);
				$addition_data = array('archive_name' => $archiveFileName, 'hosted' => $hosted);
				$this->update_database_plugin($slug, array_merge($plugin_data, $addition_data));
				return true;
			}
			
			
			/**
			 * Выполнить загрузку архива с сервера на локальный сайт по слугу
			 * @param $slug
			 * @param null $url
			 * @return bool|int
			 */
			public function do_remote_download_plugin($slug, $url = null) {
				$remote_plugins = $this->get_remote_data($url);
				if (!is_array($remote_plugins)) return -1;
				if (!$remote_plugins['status']) return -2;
				if (!isset($remote_plugins['plugins'][$slug])) return -3;
				$remote_plugin = $remote_plugins['plugins'][$slug];
				if (!isset($remote_plugin['archive_name']) || trim($remote_plugin['archive_name']) == '') return -4;
				$url = $remote_plugins['archives_url'] . '/' . $remote_plugin['archive_name'];
				///Download
				$raw = file_get_contents($url);
				$local_archive = WP_PLUGIN_DIR . '/' . $remote_plugin['archive_name'];
				file_put_contents($local_archive, $raw);
				///Unpack
				$zip = new ZipArchive;
				$res = $zip->open($local_archive);
				if ($res !== TRUE) return -5;
				$R = $zip->extractTo(WP_PLUGIN_DIR);
				$zip->close();
				///
				return $R ? true : -6;
			}
			
			/**
			 * Выполнить активацию плагина (если его нет, предварительно скачать с сервера)
			 * @param $slug
			 * @return int|null|WP_Error
			 */
			public function do_activate($slug) {
				if (!$this->is_plugin_exists($slug)) {
					if ($this->do_remote_download_plugin($slug) !== true) return -1;
				}
				require_once ABSPATH . '/wp-admin/includes/plugin.php';
				return activate_plugin($slug);
			}
			
			/**
			 * Отключить плагин
			 * @param $slug
			 * @return bool
			 */
			public function do_deactivate($slug) {
				if (!$this->is_plugin_exists($slug)) return true;
				deactivate_plugins($slug);
				return true;
			}
			
			/**
			 * Удалить плагин по слугу
			 * @param $slug
			 * @return bool
			 */
			public function do_remove_plugin($slug) {
				if (!$this->is_plugin_exists($slug)) return true;
				$this->do_deactivate($slug);
				if (strpos($slug, '/') !== false) return $this->do_removeDir(WP_PLUGIN_DIR . '/' . dirname($slug));
				else return $this->do_removeDir(WP_PLUGIN_DIR . '/' . $slug);
			}
			
			/**
			 * @param $dirPath
			 * @return bool
			 */
			private function do_removeDir($dirPath) {
				if (!is_dir($dirPath)) {
					throw new InvalidArgumentException("$dirPath must be a directory");
				}
				if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
					$dirPath .= '/';
				}
				$files = glob($dirPath . '*', GLOB_MARK);
				foreach ($files as $file) {
					if (is_dir($file)) {
						$this->do_removeDir($file);
					} else {
						unlink($file);
					}
				}
				return rmdir($dirPath);
			}
			
			
			/**
			 * Читает данные архива плагинов
			 * @return array|mixed|void
			 */
			public function get_database_plugins($onlyHosted = true) {
				$database = get_option(HW_PLUGINS_SERVER_OPTIONS_DATABASE, array());
				if (!is_array($database)) $database = array();
				$R = array();
				if ($onlyHosted) {
					foreach ($database as $slug => $plugin) {
						$R[$slug] = $plugin;
					}
					return $R;
				} else return $database;
			}
			
			/**
			 * Устанавливает данные архива плагинов в базе
			 * @param $database
			 * @return bool
			 */
			public function set_database_plugins($database) {
				if (!is_array($database)) $database = array();
				return update_option(HW_PLUGINS_SERVER_OPTIONS_DATABASE, $database);
			}
			
			/**
			 * Обновить данные архива плагина
			 * @param $slug
			 * @param $data
			 * @return array|mixed|void
			 */
			public function update_database_plugin($slug, $data) {
				$database = $this->get_database_plugins();
				if (isset($database[$slug])) $data = array_merge($database[$slug], $data);
				$database[$slug] = $data;
				$database[$slug]['slug'] = $slug;
				$this->set_database_plugins($database);
				return $database;
			}
			
			/**
			 * Возвращает данные плагина, если он есть в архиве, либо FALSE
			 * @param $slug
			 * @return bool|mixed
			 */
			public function get_database_plugin($slug) {
				$database = $this->get_database_plugins();
				if (!isset($database[$slug])) return false;
				$databasePlugin = $database[$slug];
				$databasePlugin['archive_path'] = HW_PLUGINS_SERVER_ROOT . '/' . $databasePlugin['archive_name'];
				$databasePlugin['archive_size'] = filesize($databasePlugin['archive_path']);
				$databasePlugin['archive_time'] = filemtime($databasePlugin['archive_path']);
				$databasePlugin['archive_url'] = str_replace(WP_CONTENT_DIR, WP_CONTENT_URL, HW_PLUGINS_SERVER_ROOT) . '/' . $databasePlugin['archive_name'];
				return $databasePlugin;
			}
			
			/**
			 * Возвращает TRUE, если плагин в архиве
			 * @param $slug
			 * @return bool
			 */
			public function is_database_plugin_hosted($slug) {
				$pluginDatabase = $this->get_database_plugin($slug);
				return !($pluginDatabase == false || $pluginDatabase['hosted'] == false);
			}
			
			/**
			 * Очистить информацию об арзивах
			 * @return bool
			 */
			private function clear_database() {
				return $this->set_database_plugins(array());
			}
			
			
			/**
			 * Возвращает статус удаленного сервера
			 * Возможные ответы:
			 * -1 → Не указан URL удаленного сервера
			 * -2 → Не удалось соединиться с сервером
			 * -3 → Возможно плагин сервера не установлен на удаленном сайте
			 * -4 → Ошибка в результатах ответа от сервера
			 * false → Сервер установлен, но выключен
			 * true → Сервер запущен и готов к работе
			 * @param null $url
			 * @return int
			 */
			public function get_remote_status($url = null, $textual = false) {
				if (!is_string($url)) $url = get_option(HW_PLUGINS_SERVER_OPTIONS_REMOTE_URL, false);
				if (!is_string($url) || strpos($url, 'http') !== 0) return $textual ? 'NO CONNECT' : -1;
				$response = file_get_contents(rtrim($url, '/\\') . '/wp-admin/admin-ajax.php?action=hw_plugins_server_get');
				if (!is_string($response)) return $textual ? 'NO CONNECT: ERROR' : -2;
				$data = json_decode($response, true);
				if (json_last_error() != 0) return $textual ? 'NO CONNECT: RESPONSE IS NOT JSON' : -3;
				if (!isset($data['status'])) return $textual ? 'NO CONNECT: STATUS NOT EXISTS' : -4;
				return $textual ? ($data['status'] ? 'CONNECT' : 'CONNECT: SERVER is OFF') : $data['status'];
			}
			
			/**
			 * Возвращает данные от удаленного сервера, либо значение ошибки
			 * @param null $url
			 * @return array|int|mixed|object
			 */
			public function get_remote_data($url = null) {
				if (!is_string($url)) $url = get_option(HW_PLUGINS_SERVER_OPTIONS_REMOTE_URL, false);
				if (!is_string($url) || strpos($url, 'http') !== 0) return -1;
				$response = file_get_contents(rtrim($url, '/\\') . '/wp-admin/admin-ajax.php?action=hw_plugins_server_get');
				if (!is_string($response)) return -2;
				$data = json_decode($response, true);
				if (json_last_error() != 0) return -3;
				if (!isset($data['status'])) return -4;
				return $data;
			}
			
			/**
			 * Возвращает
			 * @param null $url
			 * @return int
			 */
			public function get_remote_plugins($url = null) {
				$data = $this->get_remote_data($url);
				if (!isset($data['status'])) return -4;
				if ($data['status'] != true) {
					return $data['status'];
				} else {
					return $data['plugins'];
				}
			}
			
			/**
			 * @param $slug
			 * @return bool
			 */
			public function is_plugin_active($slug) {
				include_once(ABSPATH . 'wp-admin/includes/plugin.php');
				return is_plugin_active($slug);
			}
			
			/**
			 * Возвращает TRUE, если плагин существует
			 * @param $slug
			 * @return bool
			 */
			public function is_plugin_exists($slug) {
				return file_exists(WP_PLUGIN_DIR . '/' . $slug);
			}
			
			/**
			 * Возвращает версию локального плагина, либо FALSE
			 * @param $slug
			 * @return int|mixed
			 */
			public function get_plugin_version($slug) {
				if (!$this->is_plugin_exists($slug)) return -1;
				$plugin = get_plugin_data(WP_PLUGIN_DIR . '/' . $slug);
				if (!is_array($plugin) || !isset($plugin['Version']) || trim($plugin['Version']) == '') return -2;
				return $plugin['Version'];
			}
			
			
		}
		
	}