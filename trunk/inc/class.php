<?php
	
	
	/**
	 * Created by PhpStorm.
	 * User: hiweb
	 * Date: 01.06.2016
	 * Time: 15:33
	 */
	class hw_plugins_server{
		
		
		public function __construct(){
			if( !file_exists( HW_PLUGINS_SERVER_ROOT ) )
				mkdir( HW_PLUGINS_SERVER_ROOT, 0755 );
		}
		
		
		/**
		 * Возвращает класс для работы локального клиента
		 * @return hw_plugins_server_local
		 */
		public function local(){
			static $class;
			if( !$class instanceof hw_plugins_server_local ){
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
			if( !$class instanceof hw_plugins_server_host ){
				$class = new hw_plugins_server_host();
			}
			return $class;
		}
		
		
		/**
		 * Возвращает класс для работы удаленного хоста
		 * @return hw_plugins_server_remote
		 */
		public function remote( $url = null ){
			static $class;
			if( !$class instanceof hw_plugins_server_remote ){
				$class = new hw_plugins_server_remote( $url );
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
			if( !$class instanceof hw_plugins_server_hooks ){
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
		 * Возвращает массив плагинов с несовпадающими версиями
		 * @return array
		 */
		public function plugins_compare_local_host(){
			$plugins = $this->plugins();
			$R = array();
			foreach( $plugins as $slug => $plugin ){
				if( $this->compare_version_local_host( $slug ) == 1 )
					$R[ $slug ] = $plugin;
			}
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
			if( !$local_plugin->is_exists() ){
				return false;
			}
			$pluginFiles = $local_plugin->files();
			///get host plugin
			$host_plugin = $this->host()->plugin( $slug );
			if( file_exists( $host_plugin->path() ) ){
				if( $replace_host_plugin )
					@unlink( $host_plugin->path() );else return true;
			}
			///MAKE ARCHIVE
			$zip = new ZipArchive();
			$B = $zip->open( $host_plugin->path(), ZipArchive::CREATE | ZipArchive::OVERWRITE );
			if( $B ){
				foreach( $pluginFiles as $file ){
					$path = $file;
					if( !is_dir( $path ) )
						$zip->addFile( $path, str_replace( WP_PLUGIN_DIR, '', $file ) );
				}
				$zip->close();
				///MAKE INFO
				$host_plugin->data_update();
				return true;
			}else{
				return false;
			}
		}
		
		
		/**
		 * Сравнить версии плагина
		 * @param $slug
		 * @return int
		 * -2 → Плагины отсутствуют
		 * -1 → Один из плагинов отсутствует
		 * 0 → Плагины имеют одинаковые вресии
		 * 1 → Версии плагинов разнятся
		 */
		public function compare_version_local_host( $slug ){
			$local = $this->local()->plugin( $slug );
			$host = $this->host()->plugin( $slug );
			if( !$local->is_exists() && !$host->is_exists() ){
				return - 2;
			}elseif( !$local->is_exists() || !$host->is_exists() ){
				return - 1;
			}elseif( $local->Version == $host->Version ){
				return 0;
			}else return 1;
		}
		
		
	}