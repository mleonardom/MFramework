<?php
abstract class MF_Controller{
	
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
	 * Construct for controller, The $view_content var is an instance for the action view to render 
	 * @param MF_View $view_content
	 */
	public function __construct($view_content)  {
		$this->view_content = $view_content;
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
	 * This function will render the views
	 */
	public function renderView(){
		if( !$this->is_redirected ){
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
		// TODO accept arrays too
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