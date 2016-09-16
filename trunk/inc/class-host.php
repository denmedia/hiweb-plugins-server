<?php
	/**
	 * Created by PhpStorm.
	 * User: hiweb
	 * Date: 16.09.2016
	 * Time: 9:44
	 */
	
	
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
			$this->status = ! ( get_option( HW_PLUGINS_SERVER_OPTIONS_STATUS, '0' ) == '0' );
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
		 * @param $slug
		 * @param $data
		 * @return array|mixed|void
		 */
		public function update_plugin( $slug, $data ){
			$database = $this->data();
			if( isset( $database[ $slug ] ) ){
				$data = array_merge( $database[ $slug ], $data );
			}
			$database[ $slug ] = $data;
			$database[ $slug ]['slug'] = $slug;
			$this->set_database( $database );
			return $database;
		}
		
		
		/**
		 * Возвращает данные плагина, если он есть в архиве, либо FALSE
		 * @param $slug
		 * @return bool|mixed
		 */
		/**
		 * Возвращает локальный плагин, возможно, которого еще не существует
		 * @param $slug
		 * @return hw_plugins_server_host_plugin
		 */
		public function plugin( $slug ){
			if( ! isset( $this->plugins[ $slug ] ) ){
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
			foreach( scandir( HW_PLUGINS_SERVER_ROOT ) as $file ){
				if( preg_match( '/(.zip)$/i', $file ) > 0 ){
					$plugin = $this->plugin( HW_PLUGINS_SERVER_ROOT . '/' . $file );
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
	
	
	class hw_plugins_server_host_plugin{
		
		public $slug;
		public $Name;
		public $Description;
		public $Version;
		public $hosted;
		private $id;
		
		
		public function __construct( $slug ){
			if( preg_match( '/(.zip)$/i', $slug ) > 0 ){
				$infoFile = preg_replace( '/(.zip)$/i', '.json', $slug );
				if( file_exists( $infoFile ) ){
					$this->data_load( $infoFile );
					$this->id = md5( $this->slug );
				}else{
					$this->id = preg_replace( '/(.zip)$/i', '', basename( $slug ) );
					$this->slug = $this->id;
					$this->Name = $this->id;
					$this->data_update();
				}
			}elseif( preg_match( '/(.php)$/i', $slug ) > 0 ){
				$this->slug = $slug;
				$this->id = md5( $this->slug );
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
		 * @param bool $infoFilePath
		 * @return array|mixed|object|string
		 */
		public function data_load( $infoFilePath = false ){
			if( ! is_string( $infoFilePath ) ){
				$infoFilePath = $this->path( true );
			}
			if( is_file( $infoFilePath ) && is_readable( $infoFilePath ) && filesize( $this->path( true ) ) < HW_PLUGINS_SERVER_HOST_INFO_FILE_LIMIT ){
				$info = @file_get_contents( $infoFilePath );
				$info = @json_decode( $info, true );
				if( json_last_error() == JSON_ERROR_NONE ){
					$data = $this->data();
					if( is_array( $data ) ){
						foreach( $data as $key => $value ){
							if( isset( $info[ $key ] ) ){
								$this->{$key} = $info[ $key ];
							}else{
								$dataLocal = hiweb_plugins_server()->local()->plugin( $this->slug )->data();
								if( isset( $dataLocal[ $key ] ) ){
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
		public function data_update(){
			$data = $this->data();
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
		 * @param bool $infoFile
		 * @return string
		 */
		public function file_name( $infoFile = false ){
			return $this->id . ( $infoFile ? '.json' : '.zip' );
		}
		
		
		/**
		 * Возвращает путь до файла архива
		 * @param bool $infoFile - путь до инфо-файла
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
			if( ! $this->is_exists() ){
				return false;
			}
			///Unpack
			$zip = new ZipArchive;
			$res = $zip->open( $this->path() );
			if( $res !== true ){
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
			@unlink( $this->path( true ) );
			return @unlink( $this->path() );
		}
		
		
	}