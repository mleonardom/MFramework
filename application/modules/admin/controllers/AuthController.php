<?php
class Admin_AuthController extends MF_Controller{
	
	public function loginAction(){
		$request = MF_Request::getInstance();
		$do = $request->getParam('do', false);
		if( $request->isPost() && $do && $do == 'save' ){
			$username = $request->getParam('username');
			$password = $request->getParam('password');
			
			$auth = MF_Auth::getInstance();
			
			if( $auth->login($username, $password) ){
				if( $auth->active ){
					$this->view->message = "Logged !!!!!!";
				}else{
					$this->view->message = "No active";
				}
			}else{
				$this->view->message = "User or password incorrect";
			}
			
		}
	}
	
	public function logoutAction(){
		$auth = MF_Auth::getInstance();
		$auth->logout();
		$this->redirect( $this->getUrl( array('action'=>'login') ) );
	}
	
}