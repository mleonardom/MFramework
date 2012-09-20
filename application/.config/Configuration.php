<?php
class Configuration{
	
	
	// set here the domains and IP's
	protected $_hosts = array(
		'development' => array('localhost', '127.0.0.1'),
		'production' => 'productiondomain.com' // you can add here any domains into an array
	);
	
	// set here the global configurations
	protected $_default_layout = 'layout';
	
	
	// Do nothing here
	public function loadConfiguration(){
		
		$host = $_SERVER['HTTP_HOST'];
		
		foreach( $this->_hosts as $e => $h ){
			if( is_array( $h ) ){
				if( in_array( $host , $h) ){
					$function = "{$e}Configurations";
					break;
				}
			}elseif( $h == $host ){
				$function = "{$e}Configurations";
				break;
			}
		}
		if( isset($function) ){
			define("DEFAULT_LAYOUT", $this->_default_layout);
			if( !method_exists($this, $function) ){
				die( "<i>{$e}</i> is not a valid envirotment" );
			}else{
				$this->$function();
				return true;
			}
		}
		die( "{$host} is not a valid host" );
		
	}
	
	// set here the development configurations
	protected function developmentConfigurations(){
		define('ENVIROTMENT','development');
		
		// set the base URL
		define('BASE_URL','MFramework');
		
		// set the database
		define("DB_HOST", "localhost");
		define("DB_USER", "root");
		define("DB_PASS", "");
		define("DB_NAME", "mf_db");
	}
	
	// set here the production configurations
	protected function productionConfigurations(){
		define('ENVIROTMENT','production');
		
		// set the base URL
		define('BASE_URL','MFramework');
		
		// set the database
		define("DB_HOST", "localhost");
		define("DB_USER", "root");
		define("DB_PASS", "");
		define("DB_NAME", "mf_db");
	}
	
}