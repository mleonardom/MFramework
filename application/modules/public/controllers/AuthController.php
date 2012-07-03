<?php
class AuthController extends MF_Controller{
	
	public function loginAction(){
		$auth = MF_Auth::getInstance();
		if( $auth->isLogged() ){
			$this->redirect( array() );
			exit;
		}
		$request = MF_Request::getInstance();
		$do = $request->getParam('do', false);
		if( $request->isPost() && $do && $do == 'save' ){
			$username = $request->getParam('username');
			$password = $request->getParam('password');
			if( $auth->login($username, $password) ){
				if( $auth->user->active ){
					$this->redirect( array() );
				}else{
					$auth->logout();
					$this->view->addFlashMessage( array("error", "This user is not currently active") );
					$this->redirect( array('controller'=>'auth', 'action'=>'login') );
				}
			}else{
				$this->view->addFlashMessage( array("error", "Incorrect user or password") );
				$this->redirect( array('controller'=>'auth', 'action'=>'login') );
			}
			
		}
	}
	
	public function logoutAction(){
		$auth = MF_Auth::getInstance();
		$auth->logout();
		$this->redirect( array('controller'=>'auth', 'action'=>'login') );
	}
	
}