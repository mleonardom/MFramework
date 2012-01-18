<?php
class User extends MF_Model{
	
	public function __construct( $id = null ){
		parent::__construct('users', $id);
	}
	
}