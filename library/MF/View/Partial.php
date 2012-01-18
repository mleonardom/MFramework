<?php
class MF_View_Partial extends MF_View
{
	public function __construct( $file, $vars ){
		parent::__construct($file);
		$this->setVars( $vars );
	}
	
	protected function inspectFilename( $filename ){
		if( empty( $filename ) ) return false;
		$filename = strtolower(preg_replace('/([a-z]+)([A-Z])/', '$1-$2', $filename));
		if( !preg_match('/\.phtml/', $filename) ) $filename .= '.phtml';
		if( !file_exists($filename) ){
			if( file_exists(PARTIALS_PATH.$filename) ){
				$filename = PARTIALS_PATH.$filename;
			}else{
				MF_Error::dieError('Partial file: <strong>'.$filename.'</strong> not found.', 404);
			}
		}
		return $filename;
	}
	
	public function setVars($vars){
		foreach( $vars as $k => $v ){
			$this->$k = $v;
		}
	}
}