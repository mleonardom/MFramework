<?php
class MF_Error{
	
	protected static $error_code;
	protected static $error_message;
	
	protected function __construct(){}
	
	public static function getError($message, $code = null){
		self::$error_code = $code;
		
		if( $code == 401 )
			header("HTTP/1.0 401 Unauthorized");
		elseif( $code == 403 )
			header("HTTP/1.0 403 Forbiden");
		elseif( $code == 404 )
			header("HTTP/1.0 404 Not Found");
		elseif( $code == 500 )
			header("HTTP/1.0 500 Internal Server Error");
		elseif( $code == 503 )
			header("HTTP/1.0 503 Service Unavailable");
		elseif( $code !== null && is_int($code) )
			header("HTTP/1.0 ".$code);
		
		if( ENVIROTMENT == 'production' ){
			if( file_exists(MF_Bootstrap::getViewsPath().'errors/'.$code.'.phtml') )
				require(MF_Bootstrap::getViewsPath().'errors/'.$code.'.phtml');
			else
				require(MF_Bootstrap::getViewsPath().'errors/default.phtml');
		}else{
			self::$error_message = $message;
			require(MF_Bootstrap::getViewsPath().'errors/development.phtml');
		}
	}
	
	public static function showError($message, $code = null){
		self::getError($message, $code);
	}
	
	public static function dieError($message, $code = null){
		self::showError($message, $code);
		exit;
	}
	
}