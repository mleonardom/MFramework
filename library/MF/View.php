<?php
class MF_View {
	protected $file = null;
	protected $content = null;
	protected $styles = array();
	protected $scripts = array();
	protected static $flash_messages;
	public function __construct($file=null){
		if( $file !== null ) $this->setFile($file);
		if( isset($_SESSION['flash_messages']) ){
			self::$flash_messages = $_SESSION['flash_messages'];
			unset( $_SESSION['flash_messages'] );
		}else{
			self::$flash_messages = array();
		}
	}
	
	public function addStyle( $file_name ){
		$this->styles[] = $file_name;
	}
	
	public function addScript( $file_name ){
		$this->scripts[] = $file_name;
	}
	
	public function addFlashMessage( $message ){
		self::$flash_messages[] = $message;
		$_SESSION['flash_messages'] = self::$flash_messages;
	}
	
	public function getFlashMessages(){
		return count($flash_messages)>0?self::$flash_messages:false;
	}
	
	protected function getStyles(){
		$xhtml = "<!-- Styles -->\n";
		foreach( $this->styles as $s ){
			if( preg_match('~^http://~', $s) || preg_match('~^https://~', $s) ){
				$href = $s;
			}else{
				$href = '/'.BASE_URL.'/'.$s;
			}
			$xhtml .= '<link rel="stylesheet" href="'.$href.'" type="text/css" media="screen" />'."\n";
		}
		return $xhtml;
	}
	
	protected function getScripts(){
		$xhtml = "<!-- Scripts -->\n";
		foreach( $this->scripts as $s ){
			if( preg_match('~^http://~', $s) || preg_match('~^https://~', $s) ){
				$src = $s;
			}else{
				$src = '/'.BASE_URL.'/'.$s;
			}
			$xhtml .= '<script src="'.$src.'" type="text/javascript"></script>'."\n";
		}
		return $xhtml;
	}
	
	public function getHeaderIncludes(){
		$xhtml = $this->getStyles();
		$xhtml .= $this->getScripts();
		return $xhtml;
	}
	
	protected function inspectFilename( $filename ){
		if( empty( $filename ) ) return false;
		$filename = strtolower(preg_replace('/([a-z]+)([A-Z])/', '$1-$2', $filename));
		if( !preg_match('/\.phtml/', $filename) ) $filename .= '.phtml';
		if( !file_exists($filename) ){
			if( file_exists(VIEWS_PATH.$filename) ){
				$filename = VIEWS_PATH.$filename;
			}else{
				MF_Error::dieError('View file: <strong>'.$filename.'</strong> not found.', 404);
			}
		}
		return $filename;
	}
	
	public function partial($partial, $vars = array()){
		$v_partial = new MF_View_Partial($partial, $vars);
		$v_partial->render();
	}
	
	public function setFile($file){
		$this->file = $this->inspectFilename($file);
	}
	
	public function getFile(){
		return $this->file;
	}
	
	public function render( $file = null ){
		if( $file === null ) $file = $this->file;
		$file = $this->inspectFilename( $file );
		require($file);
	}
	
	public function haveContent(){
		return $this->content !== null;
	}
	
	public function setContent($view){
		$this->content = $view;
	}
	
	public function getContent(){
		if( $this->content === null ){
			MF_Error::dieError('This is a view not an layout, getContent is not avalibale.', 500);
		}
		else{
			$this->render( $this->content );
		}
	}
	
	public static function getURL( array $parts, $overwrite = false ){ // TODO
		/*$url = '/'.BASE_URL.'/';
		$vars = "";
		if( !isset($parts['controller']) ) $parts['controller'] = $overwrite? MF_Application::getBootstrap()->getDefaultController():MF_Application::getBootstrap()->getController();
		if( !isset($parts['action']) ) $parts['action'] = $overwrite? MF_Application::getBootstrap()->getDefaultAction():MF_Application::getBootstrap()->getAction();
		foreach( $parts as $k => $v ){
			if( $k != 'controller' && $k != 'action' ){
				if( $overwrite ){
					
				}else{
					$vars .= "{$k}/{$v}/";
				}
			}
		}
		
		if( $overwrite ){
			
		}else{
			
		}
		var_dump($url);exit;*/
	}
}