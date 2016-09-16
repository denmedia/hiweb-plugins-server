<?php
	
	
	/**
	 * Created by PhpStorm.
	 * User: hiweb
	 * Date: 01.06.2016
	 * Time: 15:33
	 */
	class hw_plugins_server{
		
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
		 * @return hw_plugins_server_remote_host
		 */
		public function remote_host(){
			static $class;
			if( !$class instanceof hw_plugins_server_remote_host ){
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
			if( $host_plugin->is_exists() && !$replace_host_plugin ){
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
					if( !is_dir( $path ) ){
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