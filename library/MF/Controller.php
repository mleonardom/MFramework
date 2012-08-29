<?php
abstract class MF_Controller{
	
	/**
	 * 
	 * The default module
	 * @var string
	 */
	protected $default_module;
	
	/**
	 * 
	 * The default controller
	 * @var string
	 */
	protected $default_controller;
	
	/**
	 * 
	 * The default action
	 * @var string
	 */
	protected $default_action;
	
	/**
	 * 
	 * The layout for this controller
	 * @var MF_View
	 */
	protected $view;
	
	/**
	 * 
	 * The action view for this controller
	 * @var MF_View
	 */
	protected $view_content;
	
	/**
	 * 
	 * true when is called the redirect function, this prevents for views renders and wrong headers senders
	 * @var boolean
	 */
	protected $is_redirected = false;
	
	/**
	 * 
	 * true when is called the renderJSON function, this prevents for views renders and wrong headers senders
	 * @var boolean
	 */
	protected $is_alredy_render = false;
	
	/**
	 * 
	 * Construct for controller, The $view_content var is an instance for the action view to render 
	 * @param MF_View $view_content
	 */
	public function __construct()  {
		$this->view_content = MF_Bootstrap::getController().'/'.MF_Bootstrap::getAction();
		$this->view = new MF_View();
		$this->view->setContent($this->view_content);
		$this->setLayout(DEFAULT_LAYOUT.".phtml");
	}
	
	/**
	 * 
	 * This function is called before any (*)Action function (for override)
	 */
	public function _init(){}
	
	/**
	 * 
	 * This function will render a response in JSON
	 */
	public function renderJSON( $response ){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		echo json_encode( $response );
		$this->is_alredy_render = true;
	}
	
	/**
	 * 
	 * This function will render the views
	 */
	public function renderView(){
		if( !$this->is_redirected && !$this->is_alredy_render ){
			if( $this->view->getFile() === null || $this->view->getFile() === false ){
				$this->view->render($this->view_content);
			}else{
				$this->view->render();
			}
		}
	}
	
	/**
	 * 
	 * Redirect the app to any specified URL
	 * @param string $to
	 */
	public function redirect($to){
		if( is_array($to) ) $to = $this->view->getURL($to);
		$this->is_redirected = true;
		header("Location: $to");
	}
	
	/**
	 * 
	 * Set the layout, this can have the .phtml extension or not, i.e. 'layout' or 'layout.phtml' is the same
	 * @param string $layout_name
	 */
	public function setLayout($layout_name){
		$this->view->setFile( $layout_name );
	}
	
	/**
	 * 
	 * This function is for the layout render disable 
	 */
	public function disableLayout(){
		$this->view->setFile(false);
	}
}