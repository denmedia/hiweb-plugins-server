<?php
	$_hw_plugins_server_styles = array();
	$_hw_plugins_server_scripts = array();
	function _hw_plugins_server_style( $name ){
		if( did_action( 'wp_enqueue_scripts' ) > 0 || did_action( 'admin_enqueue_scripts' ) > 0 ){
			echo '<link rel="stylesheet" href="' . HW_PLUGINS_SERVER_DIR_URL . ( strpos( $name, '/' ) === false ? '/css/' . $name : '/' . $name ) . '.css"/>';
		}else{
			global $_hw_plugins_server_styles;
			$_hw_plugins_server_styles[ 'hw-' . $name . '-css' ] = HW_PLUGINS_SERVER_DIR_URL . ( strpos( $name, '/' ) === false ? '/css/' . $name : '/' . $name ) . '.css';
		}
	}
	
	function _hw_plugins_server_script( $name, $afterJS = array(), $in_footer = false ){
		if( did_action( 'wp_enqueue_scripts' ) > 0 || did_action( 'admin_enqueue_scripts' ) > 0 ){
			echo '<script src="' . HW_PLUGINS_SERVER_DIR_URL . ( strpos( $name, '/' ) === false ? '/js/' . $name : '/' . $name ) . '.js"></script>';
		}else{
			global $_hw_plugins_server_scripts;
			$_hw_plugins_server_scripts[ 'hw-' . $name . '-js' ] = array( HW_PLUGINS_SERVER_DIR_URL . ( strpos( $name, '/' ) === false ? '/js/' . $name : '/' . $name ) . '.js', $afterJS, $in_footer );
		}
	}
	
	function _hw_plugins_server_wp_enqueue_scripts(){
		global $_hw_plugins_server_styles, $_hw_plugins_server_scripts;
		foreach( $_hw_plugins_server_styles as $slug => $url ){
			wp_register_style( $slug, $url );
			wp_enqueue_style( $slug );
			unset( $_hw_plugins_server_styles[ $slug ] );
		}
		foreach( $_hw_plugins_server_scripts as $slug => $script ){
			wp_register_script( $slug, $script[0], $script[1], false, $script[2] );
			wp_enqueue_script( $slug );
			unset( $_hw_plugins_server_scripts[ $slug ] );
		}
	}
	
	add_action( 'wp_enqueue_scripts', '_hw_plugins_server_wp_enqueue_scripts' );
	add_action( 'admin_enqueue_scripts', '_hw_plugins_server_wp_enqueue_scripts' );
