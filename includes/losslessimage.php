<?php   /* * File: SimpleImage.php * Author: Simon Jarvis * Copyright: 2006 Simon Jarvis * Date: 08/11/06 * Link: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php * * This program is free software; you can redistribute it and/or * modify it under the terms of the GNU General Public License * as published by the Free Software Foundation; either version 2 * of the License, or (at your option) any later version. * * This program is distributed in the hope that it will be useful, * but WITHOUT ANY WARRANTY; without even the implied warranty of * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the * GNU General Public License for more details: * http://www.gnu.org/licenses/gpl.html * */   

/* Editor & additions: Carl Turechek*/

class SimpleImage {   

	var $image; 

	var $image_type;  

	var $brightness=0;	

	var $contrast=0;

	var $orientation=0;

	//var $image_filename;  

	function load($filename) {   

		//$this->image_filename = 'images/'.$filename; 

		$image_info = getimagesize($filename); 

		$this->image_type = $image_info[2]; 

		if( $this->image_type == IMAGETYPE_JPEG ) {   

			$this->image = imagecreatefromjpeg($filename); 

		} elseif( $this->image_type == IMAGETYPE_GIF ) {   

			$this->image = imagecreatefromgif($filename); 

		} elseif( $this->image_type == IMAGETYPE_PNG ) {   

			$this->image = imagecreatefrompng($filename); 

		} 

	} 



	function save($filename, $image_type=IMAGETYPE_JPEG, $compression=100, $permissions=null) {   

		

		if( $image_type == IMAGETYPE_JPEG ) { 

			imagejpeg($this->image,$filename,$compression); 

		} elseif( $image_type == IMAGETYPE_GIF ) {  

			imagegif($this->image,$filename); 

		} elseif( $image_type == IMAGETYPE_PNG ) {   

			imagepng($this->image,$filename); 

		} 

		if( $permissions != null) {  

			chmod($filename,$permissions); 

		} 

	 }





	function storeImage($filename,$uniqueID) {   

	/**/	
		if( $this->image_type == IMAGETYPE_JPEG ) { 

			$ext = 'jpg';

		} elseif( $this->image_type == IMAGETYPE_GIF ) { 

			$ext = 'gif';

		} elseif( $this->image_type == IMAGETYPE_PNG ) {   

			$ext = 'png';

		}


		$filenameFull='images/'.$filename.$uniqueID.'.'.$ext;

		$_SESSION['simple-image'][$filename] = $filenameFull;

		$this->save($filenameFull,$this->image_type);

	}

	function loadSessImage($filename) {   		

		$this->load($_SESSION['simple-image'][$filename]);		

	}

	

	function getWidth() {   

		return imagesx($this->image); 

	} 

	function getHeight() {   

		return imagesy($this->image); 

	} 

	function resizeToHeight($height) {   

		$ratio = $height / $this->getHeight(); 

		$width = $this->getWidth() * $ratio; 

		$this->resize($width,$height); 

	}   

	function resizeToWidth($width) { 

		$ratio = $width / $this->getWidth(); 

		$height = $this->getHeight() * $ratio; 

		$this->resize($width,$height); 

	}  



	function resize($width,$height) { 

		$new_image = imagecreatetruecolor($width, $height); 

		imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight()); 

		

			$this->image = $new_image; //$this->ImageTrueColorToPalette2($new_image, false, imagecolorstotal($new_image)) ;

	}  

	function crop($width,$height,$left,$top) { 

		$new_image = imagecreatetruecolor($width, $height); 

		

		$this->brightnessContrast();

		

		

		imagecopy($new_image, $this->image,  0, 0,$left, $top, $this->getWidth(), $this->getHeight()); 

		

		$this->image = $new_image; //$this->ImageTrueColorToPalette2($new_image, false,  imagecolorstotal($new_image)) ;

	}  

	function brightnessContrast(){

		if (function_exists('imagefilter')){

			if($this->contrast!=0){

				imagefilter($this->image, IMG_FILTER_CONTRAST, $this->contrast* -1);

			}

			if($this->brightness !=0){			

				imagefilter($this->image, IMG_FILTER_BRIGHTNESS, $this->brightness );

			}

			

		}

	}	

	function rotate(){

		if($this->orientation!=0){

			$this->image=imagerotate($this->image, $this->orientation, 0);	//1 ignores transparency

		}

	}

//zmorris at zsculpt dot com function, a bit completed

function ImageTrueColorToPalette2($image, $dither, $ncolors) {

    $width = imagesx( $image );

    $height = imagesy( $image );

    $colors_handle = ImageCreateTrueColor( $width, $height );

    ImageCopyMerge( $colors_handle, $image, 0, 0, 0, 0, $width, $height, 100 );

    ImageTrueColorToPalette( $image, $dither, $ncolors );

    ImageColorMatch( $colors_handle, $image );

    ImageDestroy($colors_handle);

    return $image;

}

 	

}





/*	function storeImage($filename) {    

	ob_start();

		imagepng($this->image);

	$final_image = ob_get_contents();



    ob_end_clean();

		

		$_SESSION['simple-image']['current'] = $final_image;

	

	}

	function loadSessImage() {   

		 //$data = base64_decode();



		$this->image = imagecreatefromstring($_SESSION['simple-image']['current']);

		if ($this->image !== false) {

			header('Content-Type: image/png');

			imagepng($im);

			imagedestroy($im);

		}

		else {

			echo 'An error occurred.';

		}

	}

	

		function output($image_type=IMAGETYPE_JPEG) {   

		if( $image_type == IMAGETYPE_JPEG ) { 

			header('Content-type: image/jpg');

			imagejpeg($this->image); 

		} elseif( $image_type == IMAGETYPE_GIF ) { 

			header('Content-type: image/gif');		

			imagegif($this->image); 

		} elseif( $image_type == IMAGETYPE_PNG ) {   

			header('Content-type: image/png');	

			imagepng($this->image); 

		} 

	} 

		function scale($scale) { 

		$width = $this->getWidth() * $scale/100; $height = $this->getheight() * $scale/100; $this->resize($width,$height); 

	}   

	

	*/











 ?>