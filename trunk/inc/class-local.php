<?php
	/**
	 * Created by PhpStorm.
	 * User: hiweb
	 * Date: 16.09.2016
	 * Time: 9:44
	 */
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
		 * @param $slug
		 * @return hw_plugins_server_local_plugin
		 */
		public function plugin( $slug ){
			if( !isset( $this->plugins[ $slug ] ) ){
				$data = isset( $this->wp_plugins[ $slug ] ) ? $this->wp_plugins[ $slug ] : array();
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
			if( is_array( $this->wp_plugins ) ){
				foreach( $this->wp_plugins as $slug => $plugin ){
					$R[ $slug ] = $this->plugin( $slug );
				}
			}
			return $R;
		}
		
		
	}
	
	
	class hw_plugins_server_local_plugin{
		
		public $slug;
		public $Name;
		public $Description;
		public $Version;
		
		
		public function __construct( $slug ){
			$this->slug = $slug;
			$this->data_update();
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
			$R = array();
			$keys = call_user_func( 'get_object_vars', $this );
			foreach( $keys as $key => $value ){
				if( property_exists( $this, $key ) ){
					$R[ $key ] = $this->{$key};
				}
			}
			return $R;
		}
		
		
		/**
		 * Обновить информацию о плагине
		 */
		public function data_update(){
			$pluginData = get_plugin_data( $this->path() );
			$keys = call_user_func( 'get_object_vars', $this );
			if( is_array( $pluginData ) ){
				foreach( $pluginData as $key => $value ){
					if( array_key_exists( $key, $keys ) ){
						$this->{$key} = $value;
					}
				}
			}
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
		 * @param bool $active
		 * @return bool|null|WP_Error
		 */
		public function activate( $active = true ){
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			$active_plugins = get_option( 'active_plugins' );
			if( $active ){
				if( !isset( $active_plugins[ $this->slug ] ) ){
					return activate_plugin( $this->slug );
				}
			}elseif( isset( $active_plugins[ $this->slug ] ) ){
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
		 * @param bool $dirname - только имя папки
		 * @return string
		 */
		public function path( $dirname = false ){
			return WP_PLUGIN_DIR . '/' . ( $dirname ? dirname( $this->slug ) : $this->slug );
		}
		
		
		/**
		 * Возвращает URL до папки плагина
		 * @param bool $dirname - только имя папки
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
			if( $this->is_exists() ){
				return get_plugin_files( $this->slug );
			}else{
				return array();
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
			return !file_exists( $path );
		}
		
		
		private function removeDir( $path ){
			if( !file_exists( $path ) || !is_dir( $path ) ){
				return;
			}
			$dirs = array( $path );
			$files = array();
			for( $i = 0; ; $i ++ ){
				if( isset( $dirs[ $i ] ) ){
					$dir = $dirs[ $i ];
				}else{
					break;
				}
				if( $openDir = opendir( $dir ) ){
					while( $readDir = @readdir( $openDir ) ){
						if( $readDir != "." && $readDir != ".." ){
							if( is_dir( $dir . "/" . $readDir ) ){
								$dirs[] = $dir . "/" . $readDir;
							}else{
								$files[] = $dir . "/" . $readDir;
							}
						}
					}
				}
			}
			foreach( $files as $file ){
				unlink( $file );
			}
			$dirs = array_reverse( $dirs );
			foreach( $dirs as $dir ){
				rmdir( $dir );
			}
		}
		
	}