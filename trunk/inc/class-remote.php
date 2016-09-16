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
		 * @param null $url
		 * @return int
		 */
		public function get_status( $url = null, $textual = false ){
			if( !is_string( $url ) ){
				$url = get_option( HW_PLUGINS_SERVER_OPTIONS_REMOTE_URL, false );
			}
			if( !is_string( $url ) || strpos( $url, 'http' ) !== 0 ){
				return $textual ? 'NO CONNECT' : - 1;
			}
			$response = file_get_contents( rtrim( $url, '/\\' ) . '/wp-admin/admin-ajax.php?action=hw_plugins_server_get' );
			if( !is_string( $response ) ){
				return $textual ? 'NO CONNECT: ERROR' : - 2;
			}
			$data = json_decode( $response, true );
			if( json_last_error() != 0 ){
				return $textual ? 'NO CONNECT: RESPONSE IS NOT JSON' : - 3;
			}
			if( !isset( $data['status'] ) ){
				return $textual ? 'NO CONNECT: STATUS NOT EXISTS' : - 4;
			}
			return $textual ? ( $data['status'] ? 'CONNECT' : 'CONNECT: SERVER is OFF' ) : $data['status'];
		}
		
		
		/**
		 * Возвращает данные от удаленного сервера, либо значение ошибки
		 * @param null $url
		 * @return array|int|mixed|object
		 */
		public function get_data( $url = null ){
			if( !is_string( $url ) ){
				$url = get_option( HW_PLUGINS_SERVER_OPTIONS_REMOTE_URL, false );
			}
			if( !is_string( $url ) || strpos( $url, 'http' ) !== 0 ){
				return - 1;
			}
			$response = file_get_contents( rtrim( $url, '/\\' ) . '/wp-admin/admin-ajax.php?action=hw_plugins_server_get' );
			if( !is_string( $response ) ){
				return - 2;
			}
			$data = json_decode( $response, true );
			if( json_last_error() != 0 ){
				return - 3;
			}
			if( !isset( $data['status'] ) ){
				return - 4;
			}
			return $data;
		}
		
		
		/**
		 * Возвращает
		 * @param null $url
		 * @return int
		 */
		public function get_plugins( $url = null ){
			$data = $this->get_data( $url );
			if( !isset( $data['status'] ) ){
				return - 4;
			}
			if( $data['status'] != true ){
				return $data['status'];
			}else{
				return $data['plugins'];
			}
		}
		
		
		/**
		 * Выполнить загрузку архива с сервера на локальный сайт по слугу
		 * @param $slug
		 * @param null $url
		 * @return bool|int
		 */
		public function download( $slug, $url = null ){
			$remote_plugins = $this->get_remote_data( $url );
			if( !is_array( $remote_plugins ) ){
				return - 1;
			}
			if( !$remote_plugins['status'] ){
				return - 2;
			}
			if( !isset( $remote_plugins['plugins'][ $slug ] ) ){
				return - 3;
			}
			$remote_plugin = $remote_plugins['plugins'][ $slug ];
			if( !isset( $remote_plugin['archive_name'] ) || trim( $remote_plugin['archive_name'] ) == '' ){
				return - 4;
			}
			$url = $remote_plugins['archives_url'] . '/' . $remote_plugin['archive_name'];
			///Download
			$raw = file_get_contents( $url );
			$local_archive = WP_PLUGIN_DIR . '/' . $remote_plugin['archive_name'];
			file_put_contents( $local_archive, $raw );
			///Unpack
			$zip = new ZipArchive;
			$res = $zip->open( $local_archive );
			if( $res !== true ){
				return - 5;
			}
			$R = $zip->extractTo( WP_PLUGIN_DIR );
			$zip->close();
			///
			return $R ? true : - 6;
		}
		
	}
	
	
	class hw_plugins_server_remote_client{
		
	}