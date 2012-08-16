<?php
class MF_Image{
	
	protected $image;
	protected $n_image;
	protected $image_path;
	protected $image_extension;
	protected $max_width = 100;
	protected $max_height = 100;
	
	public function __construct( $image_path ){
		if( !file_exists($image_path) ){
			MF_Error::dieError( "Image \"{$image_path}\" don't exists", 500 );
		}
		$path_info = pathinfo( $image_path );
		$ext = strtolower($path_info['extension']);
		if( $ext != 'jpg' &&
			$ext != 'jpeg' &&
			$ext != 'png' &&
			$ext != 'gif'
		){
			MF_Error::dieError( "\"{$ext}\" is not a valid image extension", 500 );
		}
		if( $ext=='jpg' || $ext=='jpeg' ){
			$this->image = imagecreatefromjpeg( $image_path );
		}elseif( $ext=='png' ){
			$this->image = imagecreatefrompng( $image_path );
		}elseif( $ext=='gif' ){
			$this->image = imagecreatefromgif( $image_path );
		}
		$this->image_extension = $ext;
		$this->image_path = $image_path;
	}
	
	public function setMaxSize( $max_width, $max_height ){
		$this->max_width = $max_width;
		$this->max_height = $max_height;
	}
	
	public function crop( $src_x, $src_y, $width, $height ){
		$image_size = getimagesize( $this->image_path );
		$real_width = $image_size[0];
		$real_height = $image_size[1];
		$this->n_image = imagecreatetruecolor($this->max_width, $this->max_height);
		imagecopyresized( $this->n_image, $this->image,  0, 0, $src_x, $src_y, $this->max_width, $this->max_height, $width, $height);
	}
	
	public function fit( $background_color = null ){
		$image_size = getimagesize( $this->image_path );
		$real_width = $image_size[0];
		$real_height = $image_size[1];
		$coc = $real_width/$real_height;
		$n_coc = $this->max_width/$this->max_height;
		$n_width = 0;
		$n_height = 0;
		$dst_x = 0;
		$dst_y = 0;
		if( $coc > $n_coc ){
			$n_width = $this->max_width;
			$n_height = $this->max_width/$coc;
			if( !is_null( $background_color ) ) $dst_y = ( ($this->max_height-$n_height)/2 );
		}else{
			$n_height = $this->max_height;
			$n_width = $this->max_height*$coc;
			if( !is_null( $background_color ) ) $dst_x = ( ($this->max_width-$n_width)/2 );
		}
		if( is_null( $background_color ) ){
			$this->n_image = imagecreatetruecolor($n_width, $n_height);
		}else{
			$this->n_image = imagecreatetruecolor($this->max_width, $this->max_height);
			$background_color = $this->html2rgb( $background_color );
			$color = imagecolorallocate($this->n_image, $background_color[0], $background_color[1], $background_color[2]);
			imagefilledrectangle($this->n_image, 0, 0, $this->max_width, $this->max_height, $color);
		}
		
		imagecopyresized( $this->n_image, $this->image,  $dst_x, $dst_y, 0, 0, $n_width, $n_height, $real_width, $real_height);
	}
	
	protected function html2rgb($color)
	{
		if ($color[0] == '#') $color = substr($color, 1);
		if (strlen($color) == 6)
			list($r, $g, $b) = array($color[0].$color[1], $color[2].$color[3], $color[4].$color[5]);
		elseif (strlen($color) == 3)
			list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
		else
		return false;
		$r = hexdec($r); $g = hexdec($g); $b = hexdec($b);
		return array($r, $g, $b);
	}
	
	public function fill(){
		$image_size = getimagesize( $this->image_path );
		$real_width = $image_size[0];
		$real_height = $image_size[1];
		$coc = $real_width/$real_height;
		$n_coc = $this->max_width/$this->max_height;
		$n_width = 0;
		$n_height = 0;
		$src_x = 0;
		$src_y = 0;
		if( $coc > $n_coc ){
			$n_width = ( $this->max_height*$coc );
			$n_height = $this->max_height;
			$src_x = ( ($n_width-$this->max_width)/2 );
		}else{
			$n_height = ( $this->max_width/$coc );
			$n_width = $this->max_width;
			$src_y = ( ($n_height-$this->max_height)/2 );
		}
		$this->n_image = imagecreatetruecolor($this->max_width, $this->max_height);
		
		imagecopyresized( $this->n_image, $this->image,  0, 0, $src_x, $src_y, $n_width, $n_height, $real_width, $real_height);
	}
	
	public function stretch(){
		$image_size = getimagesize( $this->image_path );
		$real_width = $image_size[0];
		$real_height = $image_size[1];
		$this->n_image = imagecreatetruecolor($this->max_width, $this->max_height);
		
		imagecopyresized( $this->n_image, $this->image,  0, 0, 0, 0, $this->max_width, $this->max_height, $real_width, $real_height);
	}
	
	public function saveImage( $dest, $quality = 100 ){
		imagejpeg( $this->n_image, $dest, $quality );
		chmod( $dest, 0755 );
		imagedestroy( $this->n_image );
	}
	
}
