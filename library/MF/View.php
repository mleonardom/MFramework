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
		}elseif( !isset(self::$flash_messages) ){
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
		if( !in_array($message, self::$flash_messages) ){
			if( is_array($message) ){
				if( isset(self::$flash_messages[$message[0]]) ){
					self::$flash_messages[$message[0]][] = $message[1];
				}else{
					self::$flash_messages[$message[0]] = array($message[1]);
				}
			}else{
				self::$flash_messages[] = $message;
			}
			var_dump(self::$flash_messages);
			$_SESSION['flash_messages'] = self::$flash_messages;
		}
	}
	
	public function getFlashMessages(){
		return count(self::$flash_messages)>0?self::$flash_messages:false;
	}
	
	protected function getStyles(){
		$xhtml = "<!-- Styles -->\n";
		foreach( $this->styles as $s ){
			$href = preg_match('~^http(s?)://~', $s)? $s: '/'.BASE_URL.'/'.$s;
			$xhtml .= '<link rel="stylesheet" href="'.$href.'" type="text/css" media="screen" />'."\n";
		}
		return $xhtml;
	}
	
	protected function getScripts(){
		$xhtml = "<!-- Scripts -->\n";
		foreach( $this->scripts as $s ){
			$src = preg_match('~^http(s?)://~', $s)? $s: '/'.BASE_URL.'/'.$s;
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
			if( file_exists(MF_Bootstrap::getViewsPath().$filename) ){
				$filename = MF_Bootstrap::getViewsPath().$filename;
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
	
	public static function getURL( $url_p, $overwrite = false ){
		$url = '/'.BASE_URL.'/';
		if( is_array($url_p) ){
			if( $overwrite ){
				$request = MF_Request::getInstance();
				$vars = $request->getParamsGet();
			}else{
				$vars = array();
			}
			foreach( $url_p as $k => $v ){
				if( $k != 'module' && $k != 'controller' && $k != 'action' ){
					$vars[$k] = $v;
				}
			}
			//if( !isset($url_p['module']) ) $url_p['module'] = $overwrite? MF_Bootstrap::getModule() : MF_Bootstrap::getDefaultModule();
			if( !isset($url_p['module']) ) $url_p['module'] = MF_Bootstrap::getModule(); // Always to the same module
			if( !isset($url_p['controller']) ) $url_p['controller'] = $overwrite? MF_Bootstrap::getController() : MF_Bootstrap::getDefaultController();
			if( !isset($url_p['action']) ) $url_p['action'] = $overwrite? MF_Bootstrap::getAction() : MF_Bootstrap::getDefaultAction();
			
			$print_vars = count($vars) > 0;
			$print_action = $print_vars || $url_p['action'] != MF_Bootstrap::getDefaultAction();
			$print_controller = $print_action || $url_p['controller'] != MF_Bootstrap::getDefaultController();
			$print_module = $url_p['module'] != MF_Bootstrap::getDefaultModule();
			
			if( $print_module ){
				$url .= $url_p['module'].'/';
			}
			if( $print_controller ){
				$url .= $url_p['controller'].'/';
			}
			if( $print_action ){
				$url .= $url_p['action'].'/';
			}
			if( $print_vars ){
				foreach( $vars as $k => $v ){
					$url .= "{$k}/{$v}/";
				}
			}
		}else{
			$url .= $url_p[0]=='/' || $url_p[0]=='\\'? substr($url_p, 1) : $url_p;
		}
		return $url;
	}
}