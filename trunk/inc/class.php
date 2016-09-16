<?php
	/**
	 * Created by PhpStorm.
	 * User: hiweb
	 * Date: 01.06.2016
	 * Time: 15:33
	 */
	if( ! class_exists( 'hw_plugins_server' ) ){
		function hiweb_plugins_server(){
			static $class;
			if( ! $class instanceof hw_plugins_server ){
				$class = new hw_plugins_server();
			}
			return $class;
		}
		
		
		class hw_plugins_server{
			
			/**
			 * Возвращает класс для работы локального клиента
			 * @return hw_plugins_server_local
			 */
			public function local(){
				static $class;
				if( ! $class instanceof hw_plugins_server_local ){
					$class = new hw_plugins_server_local();
				}
				return $class;
			}
			
			
			/**
			 * Возвращает класс для работы локального хоста
			 * @return hw_plugins_server_host
			 */
			public function host(){
				static $class;
				if( ! $class instanceof hw_plugins_server_host ){
					$class = new hw_plugins_server_host();
				}
				return $class;
			}
			
			
			/**
			 * Возвращает класс для работы удаленного хоста
			 * @return hw_plugins_server_remote_host
			 */
			public function remote_host(){
				static $class;
				if( ! $class instanceof hw_plugins_server_remote_host ){
					$class = new hw_plugins_server_remote_host();
				}
				return $class;
			}
			
			
			/**
			 *
			 */
			public function remote_client(){
				//todo!!!
			}
			
			
			/**
			 * Возвращает класс для работы с хуками
			 * @return hw_plugins_server_hooks
			 */
			public function hooks(){
				static $class;
				if( ! $class instanceof hw_plugins_server_hooks ){
					$class = new hw_plugins_server_hooks();
				}
				return $class;
			}
			
			
			/**
			 * @return hw_plugins_server_local_plugin[]|hw_plugins_server_host_plugin[]
			 */
			public function plugins(){
				$R = array_merge( $this->local()->plugins(), $this->host()->plugins() );
				ksort( $R );
				return $R;
			}


			/**
			 * Создать архив плагина
			 * @param $slug
			 * @param bool $replace_host_plugin
			 * @return bool
			 */
			public function make_archive( $slug, $replace_host_plugin = true ){
				///find local plugin
				$local_plugin = $this->local()->plugin( $slug );
				if( ! $local_plugin->is_exists() ){
					return false;
				}
				$pluginFiles = $local_plugin->files();
				///get host plugin
				$host_plugin = $this->host()->plugin( $slug );
				if( $host_plugin->is_exists() && ! $replace_host_plugin ){
					return $host_plugin;
				}
				$host_plugin->remove();
				///MAKE ARCHIVE
				if( file_exists( $host_plugin->path() ) ){
					@unlink( $host_plugin->path() );
				}
				$zip = new ZipArchive();
				$B = $zip->open( $host_plugin->path(), ZipArchive::CREATE | ZipArchive::OVERWRITE );
				if( $B ){
					foreach( $pluginFiles as $file ){
						$path = WP_PLUGIN_DIR . '/' . $file;
						if( ! is_dir( $path ) ){
							$zip->addFile( $path, $file );
						}
					}
					$zip->close();
					///MAKE INFO
					$host_plugin->data_update();
					return true;
				}else{
					return false;
				}
			}
			
			
		}
		
		
		/**
		 * Класс для работы хуков WP
		 * Class hw_plugins_server_hooks
		 */
		class hw_plugins_server_hooks{

			public function ajax_host_toggle_status(){
				hiweb_plugins_server()->host()->toggle_status();
				include HW_PLUGINS_SERVER_DIR_TEMPLATES . '/options-page.php';
				wp_die();
			}


			public function ajax_host_toggle_kickback_status(){
				hiweb_plugins_server()->host()->toggle_kickback_status();
				include HW_PLUGINS_SERVER_DIR_TEMPLATES . '/options-page.php';
				wp_die();
			}
			

			public function ajax_host_plugin_action(){
				$do = $_POST['do'];
				$slug = $_POST['plugin'];
				$R = false;
				if( $do == 'host' ){
					$R = hiweb_plugins_server()->local()->plugin( $slug )->do_host();
				}
				if( $do == 'unhosted' ){
					$R = hiweb_plugins_server()->local()->plugin( $slug )->do_host( false );
				}
				if( $do == 'install' ){
					$R = hiweb_plugins_server()->host()->plugin( $slug )->install();
				}
				ob_start();
				_hw_plugins_server_page();
				$html = ob_get_clean();
				echo json_encode( array( 'result' => $R, 'html' => $html ) );
				wp_die();
			}


			public function ajax_remote_plugin_action(){
				ob_start();
				$do = $_POST['do'];
				$slug = $_POST['plugin'];
				if( $do == 'download' ){
					$R = hiweb_plugins_server()->do_remote_download_plugin( $slug, false );
				}
				if( $do == 'activate' ){
					$R = hiweb_plugins_server()->do_activate( $slug );
				}
				if( $do == 'deactivate' ){
					$R = hiweb_plugins_server()->do_deactivate( $slug );
				}
				if( $do == 'remove' ){
					$R = hiweb_plugins_server()->do_remove_plugin( $slug );
				}
				_hw_plugins_server_remote_page();
				$html = ob_get_clean();
				echo json_encode( array( 'result' => $R, 'html' => $html ) );
				wp_die();
			}


			public function ajax_remote_url_update(){
				$bool = update_option( HW_PLUGINS_SERVER_OPTIONS_REMOTE_URL, $_POST['url'] );
				if( $bool == false ){
					$R = array( 'result' => false, 'message' => 'Не удалось внедрить значение [' . $_POST['url'] . '] ключа [' . HW_PLUGINS_SERVER_OPTIONS_REMOTE_URL . '] в опции...' );
				}else{
					$R = array( 'result' => true, 'message' => hiweb_plugins_server()->remote_host()->get_status( $_POST['url'] ) );
				}
				echo json_encode( $R );
				wp_die();
			}


			public function ajax_server_get(){
				$R = array(
					'status' => hiweb_plugins_server()->host()->status(), 'archives_url' => HW_PLUGINS_SERVER_ROOT_URL, 'plugins' => array()
				);
				if( $R['status'] ){
					$R['plugins'] = hiweb_plugins_server()->plugins();
				}
				echo json_encode( $R );
				wp_die();
			}


			public function plugin_action_links( $links, $plugin ){
				if( $plugin != 'hiweb-plugins-server/hiweb-plugins-server.php' ){
					$links[] = '<a href=""><i class="dashicons dashicons-upload"></i> Upload To Server</a>';
				}
				return $links;
			}
			
			
			public function plugin_action_links_settings( $links ){
				$links[] = '<a href="' . esc_url( get_admin_url( null, 'options-general.php?page=' . HW_PLUGINS_SERVER_OPTIONS_PAGE_SLUG ) ) . '">Client / Server Settings</a>';
				return $links;
			}
			
			
			public function admin_notices(){
				if( get_current_screen()->base == 'plugins' ){
					ob_start();
				}
			}
			
			
			public function pre_current_active_plugins(){
				if( get_current_screen()->base == 'plugins' ){
					$html = ob_get_clean();
					$button = '<a href="' . self_admin_url( 'plugins.php?page=hiweb-plugins-server-remote' ) . '" title="Add New Plugin from hiWeb Remote Server" class="page-title-action">Add Remote Plugins</a>';
					echo str_replace( '</h1>', $button . '</h1>', $html );
				}
			}
			
		}
	}