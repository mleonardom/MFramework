<?php
class Admin_IndexController extends MF_Controller{
	
	public function _init(){
		$auth = MF_Auth::getInstance();
		if( !$auth->isAdmin() ){
			$this->redirect( array('controller'=>'auth', 'action'=>'login') );
		}
	}
	
	public function indexAction(){
		
	}
}