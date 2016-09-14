<?php
	/**
	 * Created by PhpStorm.
	 * User: hiweb
	 * Date: 01.06.2016
	 * Time: 15:33
	 */
	if ( ! class_exists( 'hw_plugins_server' ) ){
		function hiweb_plugins_server(){
			static $class;
			if ( ! $class instanceof hw_plugins_server ){
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
				if ( ! $class instanceof hw_plugins_server_local ){
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
				if ( ! $class instanceof hw_plugins_server_host ){
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
				if ( ! $class instanceof hw_plugins_server_remote_host ){
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
				if ( ! $class instanceof hw_plugins_server_hooks ){
					$class = new hw_plugins_server_hooks();
				}
				return $class;
			}


			/**
			 * Выполнить загрузку архива с сервера на локальный сайт по слугу
			 *
			 * @param $slug
			 * @param null $url
			 *
			 * @return bool|int
			 */
			public function do_remote_download_plugin( $slug, $url = null ){
				$remote_plugins = $this->get_remote_data( $url );
				if ( ! is_array( $remote_plugins ) ){
					return - 1;
				}
				if ( ! $remote_plugins['status'] ){
					return - 2;
				}
				if ( ! isset( $remote_plugins['plugins'][ $slug ] ) ){
					return - 3;
				}
				$remote_plugin = $remote_plugins['plugins'][ $slug ];
				if ( ! isset( $remote_plugin['archive_name'] ) || trim( $remote_plugin['archive_name'] ) == '' ){
					return - 4;
				}
				$url = $remote_plugins['archives_url'] . '/' . $remote_plugin['archive_name'];
				///Download
				$raw           = file_get_contents( $url );
				$local_archive = WP_PLUGIN_DIR . '/' . $remote_plugin['archive_name'];
				file_put_contents( $local_archive, $raw );
				///Unpack
				$zip = new ZipArchive;
				$res = $zip->open( $local_archive );
				if ( $res !== true ){
					return - 5;
				}
				$R = $zip->extractTo( WP_PLUGIN_DIR );
				$zip->close();
				///
				return $R ? true : - 6;
			}

			/**
			 * @return hw_plugins_server_local_plugin[]|hw_plugins_server_host_plugin[]
			 */
			public function plugins(){
				$R = array_merge( $this->local()->plugins(), $this->host()->plugins() );
				ksort( $R );
				return $R;
			}


		}


		//MODULES
		/**
		 * Класс для работы локального клиента
		 * Class hiweb_plugins_server_client
		 */
		class hw_plugins_server_local{

			/** @var  hw_plugins_server_local_plugin[] */
			private $plugins;
			private $wp_plugins;


			public function __construct(){
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				$this->wp_plugins = get_plugins();
			}


			/**
			 * Возвращает локальный плагин, возможно, которого еще не существует
			 *
			 * @param $slug
			 *
			 * @return hw_plugins_server_local_plugin
			 */
			public function plugin( $slug ){
				if ( ! isset( $this->plugins[ $slug ] ) ){
					$data                   = isset( $this->wp_plugins[ $slug ] ) ? $this->wp_plugins[ $slug ] : array();
					$this->plugins[ $slug ] = new hw_plugins_server_local_plugin( $slug, $data );
				}
				return $this->plugins[ $slug ];
			}


			/**
			 * Возвращает все локальные sплагины
			 * @return array|hw_plugins_server_local_plugin[]
			 */
			public function plugins(){
				$R = array();
				if ( is_array( $this->wp_plugins ) ){
					foreach ( $this->wp_plugins as $slug => $plugin ){
						$R[ $slug ] = $this->plugin( $slug );
					}
				}
				return $R;
			}


		}


		/**
		 * Класс для работы локального хоста
		 * Class hiweb_plugins_server_host
		 */
		class hw_plugins_server_host{

			/** @var bool */
			private $status = false;
			/** @var bool */
			private $kickback_status = false;
			/** @var  hw_plugins_server_host_plugin[] */
			private $plugins = array();


			public function __construct(){
				$this->status          = ! ( get_option( HW_PLUGINS_SERVER_OPTIONS_STATUS, '0' ) == '0' );
				$this->kickback_status = ! ( get_option( HW_PLUGINS_SERVER_OPTIONS_KICKBACK_STATUS, '0' ) == '0' );
			}


			/**
			 * Возвращает статус сервера. TRUE - сервер включен
			 * @return bool
			 */
			public function status(){
				return $this->status;
			}


			public function set_status( $new_status = true ){
				$this->status = (bool)$new_status;
				return update_option( HW_PLUGINS_SERVER_OPTIONS_STATUS, $new_status ? '1' : '0' );
			}


			/**
			 * Переключает статус сервера
			 * @return bool
			 */
			public function toggle_status(){
				$this->status = ! $this->status;
				return $this->set_status( $this->status );
			}


			/**
			 * Возвращает статус KICKBACK сервера. TRUE - сервер включен
			 * @return bool
			 */
			public function kickback_status(){
				return ( $this->status() && $this->kickback_status );
			}


			public function set_kickback_status( $new_status = false ){
				$this->kickback_status = $new_status;
				return update_option( HW_PLUGINS_SERVER_OPTIONS_KICKBACK_STATUS, $this->kickback_status ? '1' : '0' );
			}


			/**
			 * Переключает статус KICKBACK сервера
			 * @return bool
			 */
			public function toggle_kickback_status(){
				$this->kickback_status = ! $this->kickback_status();
				return $this->set_kickback_status( $this->kickback_status );
			}


			/**
			 * Обновить данные архива плагина
			 *
			 * @param $slug
			 * @param $data
			 *
			 * @return array|mixed|void
			 */
			public function update_plugin( $slug, $data ){
				$database = $this->data();
				if ( isset( $database[ $slug ] ) ){
					$data = array_merge( $database[ $slug ], $data );
				}
				$database[ $slug ]         = $data;
				$database[ $slug ]['slug'] = $slug;
				$this->set_database( $database );
				return $database;
			}


			/**
			 * Возвращает данные плагина, если он есть в архиве, либо FALSE
			 *
			 * @param $slug
			 *
			 * @return bool|mixed
			 */
			/**
			 * Возвращает локальный плагин, возможно, которого еще не существует
			 *
			 * @param $slug
			 *
			 * @return hw_plugins_server_host_plugin
			 */
			public function plugin( $slug ){
				if ( ! isset( $this->plugins[ $slug ] ) ){
					$this->plugins[ $slug ] = new hw_plugins_server_host_plugin( $slug );
				}
				return $this->plugins[ $slug ];
			}


			/**
			 * Возвращает все локальные sплагины
			 * @return array|hw_plugins_server_local_plugin[]
			 */
			public function plugins(){
				$R = array();
				foreach ( scandir( HW_PLUGINS_SERVER_ROOT ) as $file ){
					if ( preg_match( '/(.zip)$/i', $file ) > 0 ){
						$plugin             = $this->plugin( HW_PLUGINS_SERVER_ROOT . '/' . $file );
						$R[ $plugin->slug ] = $plugin;
					}
				}
				return $R;
			}


			/**
			 * Очистить информацию об арзивах
			 * @return bool
			 */
			private function clear_database(){
				return $this->set_database( array() );
			}


		}


		/**
		 * Класс для работы с удаленным хостом
		 * Class hiweb_plugins_server_remote_host
		 */
		class hw_plugins_server_remote_host{


			/**
			 * Возвращает статус удаленного сервера
			 * Возможные ответы:
			 * -1 → Не указан URL удаленного сервера
			 * -2 → Не удалось соединиться с сервером
			 * -3 → Возможно плагин сервера не установлен на удаленном сайте
			 * -4 → Ошибка в результатах ответа от сервера
			 * false → Сервер установлен, но выключен
			 * true → Сервер запущен и готов к работе
			 *
			 * @param null $url
			 *
			 * @return int
			 */
			public function get_status( $url = null, $textual = false ){
				if ( ! is_string( $url ) ){
					$url = get_option( HW_PLUGINS_SERVER_OPTIONS_REMOTE_URL, false );
				}
				if ( ! is_string( $url ) || strpos( $url, 'http' ) !== 0 ){
					return $textual ? 'NO CONNECT' : - 1;
				}
				$response = file_get_contents( rtrim( $url, '/\\' ) . '/wp-admin/admin-ajax.php?action=hw_plugins_server_get' );
				if ( ! is_string( $response ) ){
					return $textual ? 'NO CONNECT: ERROR' : - 2;
				}
				$data = json_decode( $response, true );
				if ( json_last_error() != 0 ){
					return $textual ? 'NO CONNECT: RESPONSE IS NOT JSON' : - 3;
				}
				if ( ! isset( $data['status'] ) ){
					return $textual ? 'NO CONNECT: STATUS NOT EXISTS' : - 4;
				}
				return $textual ? ( $data['status'] ? 'CONNECT' : 'CONNECT: SERVER is OFF' ) : $data['status'];
			}


			/**
			 * Возвращает данные от удаленного сервера, либо значение ошибки
			 *
			 * @param null $url
			 *
			 * @return array|int|mixed|object
			 */
			public function get_data( $url = null ){
				if ( ! is_string( $url ) ){
					$url = get_option( HW_PLUGINS_SERVER_OPTIONS_REMOTE_URL, false );
				}
				if ( ! is_string( $url ) || strpos( $url, 'http' ) !== 0 ){
					return - 1;
				}
				$response = file_get_contents( rtrim( $url, '/\\' ) . '/wp-admin/admin-ajax.php?action=hw_plugins_server_get' );
				if ( ! is_string( $response ) ){
					return - 2;
				}
				$data = json_decode( $response, true );
				if ( json_last_error() != 0 ){
					return - 3;
				}
				if ( ! isset( $data['status'] ) ){
					return - 4;
				}
				return $data;
			}


			/**
			 * Возвращает
			 *
			 * @param null $url
			 *
			 * @return int
			 */
			public function get_plugins( $url = null ){
				$data = $this->get_data( $url );
				if ( ! isset( $data['status'] ) ){
					return - 4;
				}
				if ( $data['status'] != true ){
					return $data['status'];
				}else{
					return $data['plugins'];
				}
			}

		}


		class hw_plugins_server_remote_client{

		}


		//PLUGINS
		class hw_plugins_server_local_plugin{

			public $slug;
			public $Name;
			public $Description;
			public $Version;


			public function __construct( $slug ){
				$this->slug = $slug;
				$pluginData = get_plugin_data( $this->path() );
				$keys       = call_user_func( 'get_object_vars', $this );
				if ( is_array( $pluginData ) ){
					foreach ( $pluginData as $key => $value ){
						if ( array_key_exists( $key, $keys ) ){
							$this->{$key} = $value;
						}
					}
				}
			}


			/**
			 * @return hw_plugins_server_host_plugin
			 */
			private function host(){
				return hiweb_plugins_server()->host()->plugin( $this->slug );
			}


			/**
			 * Возвращает данные плагина
			 * @return array
			 */
			public function data(){
				$R    = array();
				$keys = call_user_func( 'get_object_vars', $this );
				foreach ( $keys as $key => $value ){
					if ( property_exists( $this, $key ) ){
						$R[ $key ] = $this->{$key};
					}
				}
				return $R;
			}


			/**
			 * Возвращает TRUE, если плагин существует
			 * @return bool
			 */
			public function is_exists(){
				$path = $this->path();
				return ( file_exists( $path ) && is_readable( $path ) );
			}


			/**
			 * Возвращает TRUE, если плагин активирован
			 * @return bool
			 */
			public function is_active(){
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				return is_plugin_active( $this->slug );
			}


			/**
			 * Включить или выключить плагин
			 *
			 * @param bool $active
			 *
			 * @return bool|null|WP_Error
			 */
			public function activate( $active = true ){
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
				$active_plugins = get_option( 'active_plugins' );
				if ( $active ){
					if ( ! isset( $active_plugins[ $this->slug ] ) ){
						return activate_plugin( $this->slug );
					}
				}elseif ( isset( $active_plugins[ $this->slug ] ) ){
					deactivate_plugins( $this->slug, true );
				}
				return true;
			}


			/**
			 * Отключить плагин
			 * @return bool|null|WP_Error
			 */
			public function deactivate(){
				return $this->activate( false );
			}


			/**
			 * Возвращает путь до папки плагина
			 *
			 * @param bool $dirname - только имя папки
			 *
			 * @return string
			 */
			public function path( $dirname = false ){
				return WP_PLUGIN_DIR . '/' . ( $dirname ? dirname( $this->slug ) : $this->slug );
			}


			/**
			 * Возвращает URL до папки плагина
			 *
			 * @param bool $dirname - только имя папки
			 *
			 * @return string
			 */
			public function url( $dirname = false ){
				return WP_PLUGIN_URL . '/' . ( $dirname ? dirname( $this->slug ) : $this->slug );
			}


			public function slug(){
				return $this->slug;
			}


			/**
			 * Возвращает массив файлов
			 * @return array
			 */
			public function files(){
				if ( $this->is_exists() ){
					return get_plugin_files( $this->slug );
				}else{
					return array();
				}
			}


			/**
			 * Создать архив плагина
			 * @return bool
			 */
			public function make_archive(){
				$host_plugin = $this->host();
				$pluginFiles = $this->files();
				///MAKE ARCHIVE
				$archivePath = $host_plugin->path();
				if ( file_exists( $archivePath ) ){
					@unlink( $archivePath );
				}
				$zip = new ZipArchive();
				$B   = $zip->open( $archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE );
				if ( $B ){
					foreach ( $pluginFiles as $file ){
						$path = WP_PLUGIN_DIR . '/' . $file;
						if ( ! is_dir( $path ) ){
							$zip->addFile( $path, $file );
						}
					}
					$zip->close();
					///MAKE INFO
					$this->host()->data_write();
					return true;
				}else{
					return false;
				}
			}

			/**
			 * Разместить плагин на хосте, выполнив архивацию
			 *
			 * @param bool $hosted
			 *
			 * @return bool
			 */
			public function do_host( $hosted = true ){
				if ( $this->host()->is_exists() ){
					$this->host()->hosted = $hosted;
					$this->host()->data_write();
				}else if ( $this->make_archive() ){
					$this->host()->hosted = $hosted;
					$this->host()->data_write();
				}else{
					return false;
				}
			}


			/**
			 * Удалить плагин, предварительно деактивировав
			 * @return bool
			 */
			public function remove(){
				$this->deactivate();
				$path = $this->path( true );
				$this->removeDir( $path );
				return ! file_exists( $path );
			}


			private function removeDir( $path ){
				if ( ! file_exists( $path ) || ! is_dir( $path ) ){
					return;
				}
				$dirs  = array( $path );
				$files = array();
				for ( $i = 0; ; $i ++ ){
					if ( isset( $dirs[ $i ] ) ){
						$dir = $dirs[ $i ];
					}else{
						break;
					}
					if ( $openDir = opendir( $dir ) ){
						while ( $readDir = @readdir( $openDir ) ){
							if ( $readDir != "." && $readDir != ".." ){
								if ( is_dir( $dir . "/" . $readDir ) ){
									$dirs[] = $dir . "/" . $readDir;
								}else{
									$files[] = $dir . "/" . $readDir;
								}
							}
						}
					}
				}
				foreach ( $files as $file ){
					unlink( $file );
				}
				$dirs = array_reverse( $dirs );
				foreach ( $dirs as $dir ){
					rmdir( $dir );
				}
			}

		}


		class hw_plugins_server_host_plugin{

			public $slug;
			public $Name;
			public $Description;
			public $Version;
			public $hosted;
			private $id;


			public function __construct( $slug ){
				if ( preg_match( '/(.zip)$/i', $slug ) > 0 ){
					$infoFile = preg_replace( '/(.zip)$/i', '.json', $slug );
					if ( file_exists( $infoFile ) ){
						$this->data_load( $infoFile );
						$this->id = md5( $this->slug );
					}else{
						$this->id   = preg_replace( '/(.zip)$/i', '', basename( $slug ) );
						$this->slug = $this->id;
						$this->Name = $this->id;
						$this->data_write();
					}

				}elseif ( preg_match( '/(.php)$/i', $slug ) > 0 ){
					$this->slug = $slug;
					$this->id   = md5( $this->slug );
					$this->data_load();
				}
			}


			/**
			 * @return hw_plugins_server_local_plugin
			 */
			private function local(){
				return hiweb_plugins_server()->local()->plugin( $this->slug );
			}


			/**
			 * @return array
			 */
			public function data(){
				return call_user_func( 'get_object_vars', $this );
			}


			/**
			 * Загружает данные из инфо-файла
			 *
			 * @param bool $infoFilePath
			 *
			 * @return array|mixed|object|string
			 */
			public function data_load( $infoFilePath = false ){
				if ( ! is_string( $infoFilePath ) ){
					$infoFilePath = $this->path( true );
				}
				if ( is_file( $infoFilePath ) && is_readable( $infoFilePath ) && filesize( $this->path( true ) ) < HW_PLUGINS_SERVER_HOST_INFO_FILE_LIMIT ){
					$info = @file_get_contents( $infoFilePath );
					$info = @json_decode( $info, true );
					if ( json_last_error() == JSON_ERROR_NONE ){
						$data = $this->data();
						if ( is_array( $data ) ){
							foreach ( $data as $key => $value ){
								if ( isset( $info[ $key ] ) ){
									$this->{$key} = $info[ $key ];
								}else{
									$dataLocal = hiweb_plugins_server()->local()->plugin( $this->slug )->data();
									if ( isset( $dataLocal[ $key ] ) ){
										$this->{$key} = $dataLocal[ $key ];
									}
								}
							}
						}
					}
					return $info;
				}
				return false;
			}

			/**
			 * Записать данные в инфо-файл
			 */
			public function data_write(){
				$data     = $this->data();
				$infoPath = $this->path( true );
				return file_put_contents( $infoPath, json_encode( $data ) );
			}


			/**
			 * Возвращает TRUE, если плагин размещен на хосте
			 * @return bool
			 */
			public function is_hosted(){
				return $this->hosted;
			}


			/**
			 * Имя файла плагина
			 *
			 * @param bool $infoFile
			 *
			 * @return string
			 */
			public function file_name( $infoFile = false ){
				return $this->id . ( $infoFile ? '.json' : '.zip' );
			}


			/**
			 * Возвращает путь до файла архива
			 *
			 * @param bool $infoFile - путь до инфо-файла
			 *
			 * @return string
			 */
			public function path( $infoFile = false ){
				return HW_PLUGINS_SERVER_ROOT . '/' . $this->file_name( $infoFile );
			}


			/**
			 * Возвращает размер архива плагина, либо FALSE, если файла нет
			 * @return int|bool
			 */
			public function size(){
				return $this->is_exists() ? filesize( $this->path() ) : false;
			}


			/**
			 * Возвращает время измение файла архива
			 * @return bool|int
			 */
			public function time(){
				return $this->is_exists() ? filemtime( $this->path() ) : false;
			}


			/**
			 * Возвращает URL до файла архива
			 * @return string
			 */
			public function url(){
				return HW_PLUGINS_SERVER_ROOT_URL . '/' . $this->file_name();
			}


			/**
			 * Возвращает TRUE, если плагин существует
			 * @return bool
			 */
			public function is_exists(){
				$path = $this->path();
				return ( file_exists( $path ) && is_readable( $path ) );
			}


			/**
			 * Произвести распаковку архива(установку плагина) в локальную директорию
			 * @return bool|int
			 */
			public function install(){
				if ( ! $this->is_exists() ){
					return false;
				}
				///Unpack
				$zip = new ZipArchive;
				$res = $zip->open( $this->path() );
				if ( $res !== true ){
					return - 5;
				}
				$R = $zip->extractTo( WP_PLUGIN_DIR );
				$zip->close();
				return $R;
			}

			/**
			 * Удалить архив плагина с хоста
			 * @return bool
			 */
			public function remove(){
				@unlink($this->path(true));
				return @unlink($this->path());
			}


		}


		/**
		 * Класс для работы хуков WP
		 * Class hw_plugins_server_hooks
		 */
		class hw_plugins_server_hooks{

			public function plugin_action_links( $links, $plugin ){
				if ( $plugin != 'hiweb-plugins-server/hiweb-plugins-server.php' ){
					$links[] = '<a href=""><i class="dashicons dashicons-upload"></i> Upload To Server</a>';
				}
				return $links;
			}


			public function plugin_action_links_settings( $links ){
				$links[] = '<a href="' . esc_url( get_admin_url( null, 'options-general.php?page=' . HW_PLUGINS_SERVER_OPTIONS_PAGE_SLUG ) ) . '">Client / Server Settings</a>';
				return $links;
			}


			public function admin_notices(){
				if ( get_current_screen()->base == 'plugins' ){
					ob_start();
				}
			}


			public function pre_current_active_plugins(){
				if ( get_current_screen()->base == 'plugins' ){
					$html   = ob_get_clean();
					$button = '<a href="' . self_admin_url( 'plugins.php?page=hiweb-plugins-server-remote' ) . '" title="Add New Plugin from hiWeb Remote Server" class="page-title-action">Add Remote Plugins</a>';
					echo str_replace( '</h1>', $button . '</h1>', $html );
				}
			}

		}
	}