<?php
class MF_Bootstrap{
	
	/**
	 * 
	 * The defaults routes
	 * @var array
	 */
	protected $routes=array();
	
	/**
	 * 
	 * The request URI parts
	 * @var array
	 */
	protected $request_uri_parts=array();
	
	/**
	 * 
	 * List of active modules
	 * @var array
	 */
	protected static $modules_list;
	
	/**
	 * 
	 * The extracted module name
	 * @var string
	 */
	protected static $module;
	
	/**
	 * 
	 * The extracted controller name
	 * @var string
	 */
	protected static $controller;
	
	/**
	 * 
	 * The extracted action name
	 * @var string
	 */
	protected static $action;
	
	/**
	 * 
	 * The extracted GET params
	 * @var array
	 */
	protected $params=array();
	
	/**
	 * 
	 * The default module name
	 * @var string
	 */
	protected static $default_module;
	
	/**
	 * 
	 * The default controller name ('index' is default)
	 * @var string
	 */
	protected static $default_controller;
	
	/**
	 * 
	 * The default action name ('index' is default)
	 * @var string
	 */
	protected static $default_action;
	
	/**
	 * 
	 * This class explode, parse and route the HTTP requests
	 */
	public function __construct(){ }
	
	/**
	 * 
	 * Run the bootstrap
	 * @param string $default_controller (default value is 'index')
	 * @param string $default_action (default value is 'index')
	 */
	public function _run($default_controller = 'index', $default_action= 'index'){
		self::$default_controller = $default_controller;
		self::$default_action = $default_action;
		self::$controller=self::$default_controller;
		self::$action=self::$default_action;
		$this->explode_http_request()->parse_http_request()->route_request();
	}
	
	/**
	 * Add a default route, i.e. addRoute( 'login', array('controller'=>'auth','action=>'login') )
	 * @param string $route_alias
	 * @param array $route
	 */
	public function addRoute( $route_alias, array $route ){
		$parts = explode( '/:', $route_alias );
		if( count($parts) > 1 ){
			$embeded_vars = array();
			for( $i=1; $i<count($parts); $i++ ){
				$embeded_vars[] = $parts[$i];
			}
			$route['embeded_vars'] = $embeded_vars;
			$this->routes[$parts[0]] = $route;
		}else{
			$this->routes[$route_alias] = $route;
		}
	}
	
	/**
	 * Set the modules, if are more that one
	 * @param array $modules
	 */
	public function setModules( array $modules ){
		if( !is_null($modules) && !empty($modules) && count($modules) > 0 ){
			$modules_list = array();
			foreach( $modules as $m ){
				if( is_array($m) ){
					$modules_list[] = $m['module'];
					if( $m['default'] ){
						self::$default_module = $m['module'];
					}
				}else{
					$modules_list[] = $m;
				}
			}
			if( is_null(self::$default_module) || empty(self::$default_module) ){
				self::$default_module = $modules_list[0];
			}
			self::$modules_list = $modules_list;
		}else{
			self::$modules_list = null;
		}
	}
	
	/**
	 * 
	 * Explode the HTTP request
	 * @return MF_Bootstrap This instance
	 */
	protected function explode_http_request() {
		$requri = $_SERVER['REQUEST_URI'];
		if (strpos($requri,BASE_URL)===0){
			$requri=substr($requri,strlen(BASE_URL));
		}elseif($requri[0] == '/' && strpos($requri,BASE_URL)===1){
			$requri=substr($requri,strlen(BASE_URL)+1);
		}
		if( $requri[0] == "/" ) $requri=substr($requri,1);
		$this->request_uri_parts = $requri ? explode('/',$requri) : array();
		//var_dump( $this->request_uri_parts );exit;
		if( isset($this->request_uri_parts[0]) ){
			if( array_key_exists( $this->request_uri_parts[0], $this->routes) ){
				$req = array();
				if( array_key_exists( 'model', $this->routes[$this->request_uri_parts[0]] ) ){
					$req[] = $this->routes[$this->request_uri_parts[0]]['model'];
					unset( $this->routes[$this->request_uri_parts[0]]['model'] );
				}
				if( array_key_exists( 'controller', $this->routes[$this->request_uri_parts[0]] ) ){
					$req[] = $this->routes[$this->request_uri_parts[0]]['controller'];
					unset( $this->routes[$this->request_uri_parts[0]]['controller'] );
				}
				if( array_key_exists( 'action', $this->routes[$this->request_uri_parts[0]] ) ){
					$req[] = $this->routes[$this->request_uri_parts[0]]['action'];
					unset( $this->routes[$this->request_uri_parts[0]]['action'] );
				}
				$vars = array();
				if( isset($this->routes[$this->request_uri_parts[0]]['embeded_vars']) ){
					foreach($this->routes[$this->request_uri_parts[0]]['embeded_vars'] as $k => $v) {
						$req[] = $v;
						$req[] = isset($this->request_uri_parts[($k+1)])? $this->request_uri_parts[($k+1)]: false;
					}
					unset($this->routes[$this->request_uri_parts[0]]['embeded_vars']);
				}
				foreach( $this->routes[$this->request_uri_parts[0]] as $k => $v ){
					$req[] = $k;
					$req[] = $v;
				}
				$this->request_uri_parts = $req;
			}
		}
		return $this;
	}
	
	/**
	 * 
	 * This function parses the HTTP request to get the controller name, action name and parameter array.
	 * @return MF_Bootstrap This instance
	 */
	protected function parse_http_request() {
		$this->params = array();
		$p = $this->request_uri_parts;
		$p_index = null;
		$p_value = null;
		if( !is_null(self::$modules_list) && !empty(self::$modules_list) ){
			if( isset($p[0]) && !empty($p[0]) && in_array($p[0], self::$modules_list) ){
				self::$module = $p[0];
				unset($p[0]);
				$p = array_values( $p );
			}else{
				self::$module = self::$default_module;
			}
		}
		foreach( $p as $index => $part ){
			if( strrpos($part, "?") !== false ) {
				$params_ov = substr( $part, strrpos($part, "?")+1 );
				$params_ov = explode("&",$params_ov);
				foreach( $params_ov as $par ){
					$p_v = explode('=',$par);
					$this->params[utf8_decode(urldecode($p_v[0]))] = isset($p_v[1])? utf8_decode(urldecode($p_v[1])):'';
				}
				$part = substr( $part, 0, strrpos($part,"?") );
			}
			if( $index == 0 && !empty($part) ) {
				self::$controller = $part;
			} elseif( $index == 1 && !empty($part) ) {
				self::$action = $part;
			} else {
				if( $p_index === null ) {
					$p_index = utf8_decode(urldecode($part));
				} else {
					$this->params[$p_index] = utf8_decode(urldecode($part));
					$p_index = null;
				}
			}
		}
		if( $p_index !== null && !empty($p_index) ){
			$this->params[$p_index] = null;
		}
		MF_Request::getInstance()->setGetParams($this->params);
		return $this;
	}
	
	/**
	 * 
	 * This function maps the controller name and action name to the file location of the .php file to include
	 * @return MF_Bootstrap This instance
	 */
	protected function route_request() {
		$controllerfile=self::getControllersPath().ucfirst(self::$controller).'Controller.php';
		$class_name = ucfirst(self::$controller).'Controller';
		if( self::$module != self::$default_module ){
			$class_name = ucfirst(self::$module).'_'.$class_name;
		}
		if (!preg_match('#^[A-Za-z0-9_-]+$#',self::$controller) || !file_exists($controllerfile)){
			MF_Error::dieError('Controller file not found: '.$controllerfile, 404);
		}else{
			include_once($controllerfile);
		}
		if( !class_exists($class_name) || !is_subclass_of($class_name, 'MF_Controller') ){
			MF_Error::dieError($controllerfile.' exists, but class: <strong>'.$class_name.'</strong> is not defined or not exteds from MF_Controller Class',404);
		}
		
		$controllerObj = new $class_name();
		if( strrpos(self::$action,'-') !== false ){
			$action_s = explode('-',self::$action);
			$action_fm = "";
			foreach( $action_s as $k => $a_p ){
				$action_fm .= $k==0? strtolower($a_p):ucfirst($a_p);
			}
		}else{
			$action_fm = self::$action;
		}
		$function=$action_fm.'Action';
		if (!preg_match('#^[A-Za-z_][A-Za-z0-9_-]*$#',$function))
			MF_Error::dieError('Invalid function name: '.$function, 404);
		if (!method_exists($controllerObj,$function))
			MF_Error::dieError('Action '.$function." not found on ".$class_name, 404);
		$controllerObj->_init();
		$controllerObj->$function();
		$controllerObj->renderView();
		
		return $this;
	}
	
	/**
	 * 
	 * Get the controllers path
	 * @return string controllers_path
	 */
	public static function getControllersPath(){
		if( !is_null(self::$module) ){
			return APPLICATION_PATH."/modules/".self::$module.'/controllers/';
		}
		return APPLICATION_PATH.'/controllers/';
	}
	
	/**
	 * 
	 * Get the views path
	 * @return string views_path
	 */
	public static function getViewsPath(){
		if( !is_null(self::$module) ){
			return APPLICATION_PATH."/modules/".self::$module.'/views/scripts/';
		}
		return APPLICATION_PATH.'/views/scripts/';
	}
	
	/**
	 * 
	 * Get the partials path
	 * @return string partials_path
	 */
	public static function getPartialsPath(){
		if( !is_null(self::$module) ){
			return APPLICATION_PATH."/modules/".self::$module.'/views/partials/';
		}
		return APPLICATION_PATH.'/views/partials/';
	}
	
	/**
	 * 
	 * Get the current module name
	 * @return string controller
	 */
	public static function getModule(){
		return self::$module;
	}
	
	/**
	 * 
	 * Get the current controller name
	 * @return string controller
	 */
	public static function getController(){
		return self::$controller;
	}
	
	/**
	 * 
	 * Get the current action name
	 * @return string action
	 */
	public static function getAction(){
		return self::$action;
	}
	
	/**
	 * 
	 * Get the default module name
	 * @return string default_module
	 */
	public static function getDefaultModule(){
		return self::$default_module;
	}
	
	/**
	 * 
	 * Get the default controller name
	 * @return string default_controller
	 */
	public static function getDefaultController(){
		return self::$default_controller;
	}
	
	/**
	 * 
	 * Get the default action name
	 * @return string default_action
	 */
	public static function getDefaultAction(){
		return self::$default_action;
	}
}