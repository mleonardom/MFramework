<?php
class MF_Application{
	
	/**
	 * 
	 * The bottstrap
	 * @var MF_Bootstrap
	 */
	private static $_bootstrap;
	
	/**
	 * 
	 * Constructor
	 */
	public function __construct(){
		spl_autoload_register(array($this,'_autoload'));
		$this->setBootstrap( new MF_Bootstrap() );
	}
	
	/**
	 * 
	 * Set a custom MF_Bootstrap
	 * @param MF_Bootstrap $bootstrap
	 */
	public function setBootstrap(MF_Bootstrap $bootstrap){
		self::$_bootstrap = $bootstrap;
	}
	
	/**
	 * 
	 * Get the current MF_Bootstrap
	 * @return MF_Bootstrap
	 */
	public static function getBootstrap(){
		return self::$_bootstrap;
	}
	
	/**
	 * 
	 * The __autoload function for this framework
	 * @param string $class
	 */
	public function _autoload($class){
		$parts = explode('_',$class);
		$filename = implode("/",$parts).'.php';
		if( !file_exists($filename) && count($parts) == 1 ){
			$filename = APPLICATION_PATH."/models/".$class.'.php';
		}
		try{
			@include_once($filename);
		}catch( Exception $e ){
			//TODO MF_Error::dieError('File: <strong>'.$filename.'</strong> not found.', 404);
		}
	}
	
}