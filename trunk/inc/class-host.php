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
			$this->status = !( get_option( HW_PLUGINS_SERVER_OPTIONS_STATUS, '0' ) == '0' );
			$this->kickback_status = !( get_option( HW_PLUGINS_SERVER_OPTIONS_KICKBACK_STATUS, '0' ) == '0' );
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
			$this->status = !$this->status;
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
			$this->kickback_status = !$this->kickback_status();
			return $this->set_kickback_status( $this->kickback_status );
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
			if( !isset( $this->plugins[ $slug ] ) ){
				$plugin = new hw_plugins_server_host_plugin( $slug );
				$slug = $plugin->slug();
				if( !isset( $this->plugins[ $slug ] ) ){
					$this->plugins[ $slug ] = $plugin;
				}
			}
			return $this->plugins[ $slug ];
		}
		
		
		/**
		 * Возвращает все локальные sплагины
		 * @param bool $onlyHosted - только размещенные на хосте, если указать -1 - вернет неразмещенные плагины
		 * @return array|hw_plugins_server_host_plugin[]
		 */
		public function plugins( $onlyHosted = false ){
			$R = array();
			foreach( scandir( HW_PLUGINS_SERVER_ROOT ) as $file ){
				if( preg_match( '/(.zip)$/i', $file ) > 0 ){
					$id = hiweb_plugins_server()->id( $file );
					$plugin = $this->plugin( $id );
					if( $onlyHosted === false ){
						$R[ $plugin->id() ] = $plugin;
					}elseif( $onlyHosted === - 1 && !$plugin->is_hosted() ){
						$R[ $plugin->id() ] = $plugin;
					}elseif( $onlyHosted === true && $plugin->is_hosted() ){
						$R[ $plugin->id() ] = $plugin;
					}
				}
			}
			return $R;
		}
		
		
	}
	
	
	class hw_plugins_server_host_plugin{
		
		private $id;

		public $slug;
		public $Name;
		public $Description;
		public $Version;
		public $hosted;
		
		
		public function __construct( $id ){
			$this->id = $id;
			///SLUG
			$slug = hiweb_plugins_server()->slug($id);
			if(hiweb_plugins_server()->is_slug($slug)) $this->slug = $slug;
			///LOAD DATA
			$this->info_load();
		}


		/**
		 * Возвращает ID
		 * @return mixed
		 */
		public function id(){
			return $this->id;
		}


		/**
		 * Возвращает SLUG
		 * @param bool $initial_slug - вернуть изначальный SLUG
		 * @return string
		 */
		public function slug( $initial_slug = false ){
			return $initial_slug ? $this->initial_slug : $this->slug;
		}
		
		
		/**
		 * @return hw_plugins_server_local_plugin
		 */
		private function local(){
			return hiweb_plugins_server()->local()->plugin( $this->slug );
		}


		/**
		 * Возвращает время модификации архива
		 * @param string $format - формат времени, если указать FALSE - вернеться временной штамп
		 * @return bool|int
		 */
		public function date( $format = 'Y.m.d - H:i' ){
			$R = false;
			if( $this->is_exists() )
				$R = is_string( $format ) ? date( $format, filemtime( $this->path() ) ) : filemtime( $this->path() );
			return $R;
		}
		
		
		/**
		 * @return array
		 */
		public function info(){
			return call_user_func( 'get_object_vars', $this );
		}

		public function info_path(){
			return HW_PLUGINS_SERVER_ROOT.'/'.$this->id;
		}
		
		
		/**
		 * Загружает данные из инфо-файла
		 * @return array|mixed|object|string
		 */
		public function info_load(){
			$infoFilePath = $this->info_path();
			if( file_exists( $infoFilePath ) && is_file( $infoFilePath ) && is_readable( $infoFilePath ) && filesize( $this->path( true ) ) < HW_PLUGINS_SERVER_HOST_INFO_FILE_LIMIT ){
				$info = @file_get_contents( $infoFilePath );
				$info = @json_decode( $info, true );
				if( json_last_error() == JSON_ERROR_NONE ){
					$data = $this->info();
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
		 * Обновить информацию о плагине из локального плагина
		 */
		private function info_update_from_local(){
			if( !$this->local()->is_exists() ){
				return false;
			}
			///
			$pluginData = $this->local()->data();
			$keys = call_user_func( 'get_object_vars', $this );
			if( is_array( $pluginData ) ){
				foreach( $pluginData as $key => $value ){
					if( array_key_exists( $key, $keys ) ){
						$this->{$key} = $value;
					}
				}
			}
			return true;
		}
		
		
		/**
		 * Записать данные в инфо-файл
		 * @return bool
		 */
		public function info_write(){
			$data = $this->info();
			$infoPath = $this->path( true );
			preg_replace( "/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V', hexdec('U$1')))", json_encode( $data ) );
			return @file_put_contents( $infoPath, $data ) != false;
		}
		
		
		/**
		 * Обновить архив плагина с локального
		 * @return bool
		 */
		public function do_update(){
			if( !$this->local()->is_exists() )
				return false;
			///
			if( !hiweb_plugins_server()->make_archive( $this->slug ) )
				return false;
			///
			$this->Version = $this->local()->Version;
			return $this->info_write();
		}
		
		
		/**
		 * Разместить плагин на хосте. Если его не существует, предварительно создать архив
		 * @param bool $update - попутно обновить архив
		 * @return bool
		 */
		public function do_host( $update = true ){
			$this->hosted = true;
			if( $update || !$this->is_exists() )
				return $this->do_update();else return $this->info_write();
		}
		
		
		/**
		 * Убрать с размещения, с возможностью удаления архива
		 * @param bool $remove - попутно удвалить архив
		 * @return bool
		 */
		public function do_unhost( $remove = true ){
			if( $remove )
				return $this->remove();
			$this->hosted = false;
			return $this->info_write();
		}
		
		
		/**
		 * Возвращает TRUE, если плагин размещен на хосте
		 * @return bool
		 */
		public function is_hosted(){
			return $this->is_exists() && $this->hosted;
		}
		
		
		/**
		 * Имя файла плагина
		 * @param bool $infoFile
		 * @return string
		 */
		public function file_name( $infoFile = false ){
			return $this->file_name . ( $infoFile ? '.json' : '.zip' );
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
		 * @param bool $infoFile - вернуть URL до инфо-файла
		 * @return string
		 */
		public function url( $infoFile = false ){
			return HW_PLUGINS_SERVER_ROOT_URL . '/' . $this->file_name( $infoFile );
		}
		
		
		/**
		 * Возвращает TRUE, если плагин существует
		 * @param bool $infoFile - проверить существование инфо-файла
		 * @return bool
		 */
		public function is_exists( $infoFile = false ){
			$path = $this->path( $infoFile );
			return ( file_exists( $path ) && is_readable( $path ) );
		}
		
		
		/**
		 * Произвести распаковку архива(установку плагина) в локальную директорию
		 * @return bool|int
		 */
		public function install(){
			if( !$this->is_exists() ){
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
			$this->hosted = false;
			@unlink( $this->path( true ) );
			return @unlink( $this->path() );
		}
		
		
	}