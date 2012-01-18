<?php
class MF_Bootstrap{
	
	/**
	 * 
	 * The request URI parts
	 * @var array
	 */
	protected $request_uri_parts=array();
	
	/**
	 * 
	 * The extracted controller name
	 * @var string
	 */
	protected $controller;
	
	/**
	 * 
	 * The extracted action name
	 * @var string
	 */
	protected $action;
	
	/**
	 * 
	 * The extracted GET params
	 * @var array
	 */
	protected $params=array();
	
	/**
	 * 
	 * The default controller name ('index' is default)
	 * @var string
	 */
	protected $default_controller;
	
	/**
	 * 
	 * The default action name ('index' is default)
	 * @var string
	 */
	protected $default_action;
	
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
		$this->default_controller = $default_controller;
		$this->default_action = $default_action;
		$this->controller=$this->default_controller;
		$this->action=$this->default_action;
		$this->explode_http_request()->parse_http_request()->route_request();
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
				$this->controller = $part;
			} elseif( $index == 1 && !empty($part) ) {
				$this->action = $part;
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
		$controllerfile=CONTROLLERS_PATH.ucfirst($this->controller).'Controller.php';
		$class_name = ucfirst($this->controller).'Controller';
		if (!preg_match('#^[A-Za-z0-9_-]+$#',$this->controller) || !file_exists($controllerfile)){
			MF_Error::dieError('Controller file not found: '.$controllerfile, 404);
		}else{
			include_once($controllerfile);
		}
		if( !class_exists($class_name) || !is_subclass_of($class_name, 'MF_Controller') ){
			MF_Error::dieError($controllerfile.' exists, but class: <strong>'.$class_name.'</strong> is not defined or not exteds from MF_Controller Class',404);
		}
		$viewfile = $this->controller.'/'.$this->action;
		
		$controllerObj = new $class_name($viewfile, $this);
		if( strrpos($this->action,'-') !== false ){
			$action_s = explode('-',$this->action);
			$action_fm = "";
			foreach( $action_s as $k => $a_p ){
				$action_fm .= $k==0? strtolower($a_p):ucfirst($a_p);
			}
		}else{
			$action_fm = $this->action;
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
	 * Get the current controller name
	 * @return string controller
	 */
	public function getController(){
		return $this->controller;
	}
	
	/**
	 * 
	 * Get the current action name
	 * @return string action
	 */
	public function getAction(){
		return $this->action;
	}
	
	/**
	 * 
	 * Get the default controller name
	 * @return string default_controller
	 */
	public function getDefaultController(){
		return $this->default_controller;
	}
	
	/**
	 * 
	 * Get the default action name
	 * @return string default_action
	 */
	public function getDefaultAction(){
		return $this->default_action;
	}
}