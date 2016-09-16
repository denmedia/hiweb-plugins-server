<?php
	/**
	 * Created by PhpStorm.
	 * User: hiweb
	 * Date: 16.09.2016
	 * Time: 9:45
	 */
	
	
	/**
	 * Класс для работы с удаленным хостом
	 * Class hiweb_plugins_server_remote_host
	 */
	class hw_plugins_server_remote{
		
		private $url;
		/** @var bool|array */
		private $data = false;
		private $connect;
		private $status = 0;
		private $status_text = 'No connect: Status = 0!';
		/** @var  array|hw_plugins_server_remote_plugin[] */
		private $plugins = array();
		/** @var string */
		private $version;
		/** @var string */
		private $url_root;
		
		
		public function __construct( $url = null ){
			if( !is_string( $url ) )
				$this->url = get_option( HW_PLUGINS_SERVER_OPTIONS_REMOTE_URL, false );
			$this->connect();
		}
		
		
		public function connect(){
			if( is_null( $this->connect ) ){
				if( !is_string( $this->url ) || strpos( $this->url, 'http' ) !== 0 ){
					$this->status = - 1;
					$this->status_text = 'NO CONNECT: URL ERROR';
					return false;
				}else{
					///
					$response = @file_get_contents( rtrim( $this->url, '/\\' ) . '/wp-admin/admin-ajax.php?action=hw_plugins_server' );
					///
					if( !is_string( $response ) ){
						$this->status = - 2;
						$this->status_text = 'NO CONNECT: ERROR';
						return false;
					}else{
						///
						$data = json_decode( $response, true );
						///
						if( json_last_error() != 0 ){
							$this->status = - 3;
							$this->status_text = 'NO CONNECT: RESPONSE IS NOT JSON';
						}elseif( !is_array( $data ) ){
							$this->status = - 4;
							$this->status_text = 'NO CONNECT: RESPONSE NOT CONTAIN DATA';
						}elseif( !isset( $data['status'] ) ){
							$this->status = - 5;
							$this->status_text = 'NO CONNECT: STATUS NOT EXISTS';
						}else{
							$this->status = (int)$data['status'];
							$this->status_text = 'Connected: All Is OK!';
							$this->data = $data;
							if( isset( $data['plugins'] ) && is_array( $data['plugins'] ) )
								foreach( $data['plugins'] as $slug => $plugin ){
									$this->plugins[ $slug ] = new hw_plugins_server_remote_plugin( $slug, $plugin );
								}
							if( isset( $data['version'] ) )
								$this->version = $data['varsion'];
							if( isset( $data['url_root'] ) )
								$this->url_root = $data['url_root'];
						}
					}
				}
			}
			return $this->data;
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
		 * @param bool $textual
		 * @return int
		 */
		public function status( $textual = false ){
			return $textual ? $this->status_text : $this->status;
		}
		
		
		/**
		 * Возвращает массив удаленных плагинов
		 * @return array|hw_plugins_server_remote_plugin[]
		 */
		public function plugins(){
			return $this->plugins;
		}


		/**
		 * Возвращает удаленный плагин
		 * @param $slug
		 * @return hw_plugins_server_remote_plugin
		 */
		public function plugin( $slug ){
			if( !isset( $this->plugins[ $slug ] ) ){
				$this->plugins[ $slug ] = new hw_plugins_server_remote_plugin( $slug );
			}
			return $this->plugins[ $slug ];
		}
		
	}
	
	
	class hw_plugins_server_remote_plugin{
		
		public $slug;
		public $Name;
		public $Description;
		public $Version;
		public $hosted;
		public $url;
		public $url_info;
		public $file_name;
		public $filemtime;

		
		public function __construct( $slug, $data = array() ){
			$this->slug = $slug;
			$vars = call_user_func( 'get_object_vars', $this );
			foreach( $vars as $key => $var ){
				if( isset( $data[ $key ] ) )
					$this->{$key} = $data[ $key ];
			}
		}


		/**
		 * Возвращает массив данных
		 * @return mixed
		 */
		public function data(){
			return call_user_func( 'get_object_vars', $this );
		}


		/**
		 * Возвращает TRUE, если данные архива переданы
		 * @return bool
		 */
		public function is_exist(){
			return strpos( $this->url, 'http' ) === 0 && strpos( $this->url_info, 'http' ) === 0;
		}


		/**
		 * Выполнить загрузку архива с сервера на локальный сайт
		 * @return bool|int
		 */
		public function download(){
			if( !$this->is_exist() )
				return false;
			///Download
			$raw = file_get_contents( $this->url );
			$local_archive = WP_PLUGIN_DIR . '/' . $this->file_name;
			file_put_contents( $local_archive, $raw );
			///Unpack
			$zip = new ZipArchive;
			if( $zip->open( $local_archive ) != true )
				return false;
			$R = $zip->extractTo( WP_PLUGIN_DIR );
			$zip->close();
			@unlink( $local_archive );
			///
			return $R ? true : - 6;
		}
		
	}