<?php
class MF_Request{
	
	private static $_instance;
	
	private $_get_params;
	private $_post_params;
	
	private function __construct(){
		$this->gotPostParams();
	}
	
	/**
	 * @return MF_Request
	 */
	public static function getInstance(){
		if( self::$_instance == null ){
			self::$_instance = new MF_Request();
		}
		return self::$_instance;
	}
	
	public function isPost(){
		return count($this->_post_params)>0;
	}
	
	public function getParamGet($key, $default = null){
		if( isset($this->_get_params[$key]) ){
			return $this->_get_params[$key];
		}else{
			return $default;
		}
	}
	
	public function getParamPost($key, $default = null){
		if( isset($this->_post_params[$key]) ){
			return $this->_post_params[$key];
		}else{
			return $default;
		}
	}
	
	public function getParam($key, $default = null){
		if( $this->getParamGet($key) !== null ){
			return $this->getParamGet($key);
		}elseif( $this->getParamPost($key) !== null ){
			return $this->getParamPost($key);
		}else{
			return $default;
		}
	}
	
	public function getParams(){
		return array_merge($this->_get_params, $this->_post_params);
	}
	
	public function getParamsGet(){
		return $this->_get_params;
	}
	
	public function getParamsPost(){
		return $this->_post_params;
	}
	
	public function setGetParams(array $params){
		$this->_get_params = $params;
	}
	
	public function setPostParams(array $params){
		$this->_post_params = $params;
	}
	
	public function gotPostParams(){
		$this->_post_params = $_POST;
	}
}