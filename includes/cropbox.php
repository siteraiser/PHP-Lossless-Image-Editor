<?php //Lossless Image - Author: Carl Turechek 2015
class virtualBox{
	var $originalBoxW;
	var $originalBoxH;
	var $virtualBoxW;
	var $virtualBoxH;
	
	var $resizeW;
	var $resizeH;
	var $cropW;
	var $cropH;
	
	var $offsetLeft;
	var $offsetTop;

	var $brightness;
	var $contrast;
	var $orientation;
	
	var $lastResizeW;
	var $lastResizeH;
	var $lastCropW;
	var $lastCropH;
	var $lastOffsetLeft;
	var $lastOffsetTop;
	var $lastBrightness;
	var $lastContrast;
	var $lastRotate;		
	var $lastRotateTotal;	
	
	var $back=0;
	//var $leftMargin;
	//var $rightMargin;
	function init(){
		$this->originalBoxW = $_SESSION['simple-image']['origWidth'];
		$this->originalBoxH = @$_SESSION['simple-image']['origHeight'];
		$this->virtualBoxW = @$_SESSION['simple-image']['virtWidth'];
		$this->virtualBoxH = @$_SESSION['simple-image']['virtHeight'];
		
		$this->resizeW= $_POST['resizeWidth'];
		$this->resizeH= $_POST['resizeHeight'];
		$this->cropW = $_POST['crWidth'];
		$this->cropH = $_POST['crHeight'];
		$this->offsetLeft = $_POST['offsetLeft'];
		$this->offsetTop = $_POST['offsetTop'];
		$this->brightness = $_POST['brightness'];
		$this->contrast = $_POST['contrast'];		
		
		$this->rotate = @$_POST['rotate'];	
		
		$this->lastCropW = $_SESSION['simple-image']['history'][$this->lastHistIndex()-1]["lastCropX"];
		$this->lastCropH =	$_SESSION['simple-image']['history'][$this->lastHistIndex()-1]["lastCropY"];
		$this->lastOffsetLeft = $_SESSION['simple-image']['history'][$this->lastHistIndex()-1]["lastOffsetLeft"];
		$this->lastOffsetTop = $_SESSION['simple-image']['history'][$this->lastHistIndex()-1]["lastOffsetTop"];
		
		$this->lastRotate = $_SESSION['simple-image']['history'][$this->lastHistIndex()-1]["lastRotate"];
		
		$this->lastOrientation = $_SESSION['simple-image']['history'][$this->lastHistIndex()-1]["lastOrientation"];
		
		
	}
	
	function rotate(){
		
		//Calculate right & bottom offsets
		$offRight = $this->resizeW - ($this->offsetLeft + $this->cropW );
		$offBottom = $this->resizeH - ($this->offsetTop + $this->cropH );
	
		
			if($this->back==0)
			{
				$this->orientation = $this->rotate + $this->lastOrientation;
			}else
			{
				$this->orientation = $this->lastOrientation;			
			}			
			
			if($this->orientation == 360 )
			{
				$this->orientation =0;
			}
			if($this->orientation == -90)
			{
				$this->orientation =270;
			}
			
			
			
			if($this->orientation == 0)
			{
				if($this->orientation!=$this->lastOrientation){
					$tempH=$this->resizeH;
					$this->resizeH=$this->resizeW;
					$this->resizeW=$tempH;	
					
					
					$this->cropW = $_POST['crHeight'];
					$this->cropH = $_POST['crWidth'];
					$_POST['crWidth']=$this->cropW;
					$_POST['crHeight']=$this->cropH;
					
					if( $this->lastOrientation ==270){	
						$this->offsetLeft = $this->offsetTop ;					
						$this->offsetTop = $offRight;
					
					}
					if( $this->lastOrientation ==90){	
						$this->offsetTop = $this->offsetLeft ;
						$this->offsetLeft = $offBottom;
					
					}
				}
			
		
			}else if($this->orientation == 90)
			{
				if($this->orientation==$this->lastOrientation && $this->back==0){
				
					$tempH=$this->resizeH;
					$this->resizeH=$this->resizeW;
					$this->resizeW=$tempH;	
					
					
					$this->cropW = $_POST['crHeight'];
					$this->cropH = $_POST['crWidth'];
					
					$this->offsetTop = $this->offsetLeft ;
					
					$this->offsetLeft = $offBottom;
				}
				if($this->orientation!=$this->lastOrientation){
						if( $this->lastOrientation ==180){	
						$this->offsetLeft = $offRight;
						$this->offsetTop = $offBottom;
					
					}
					
				
				}
	
			}else if($this->orientation == 180)
			{
			if($this->orientation!=$this->lastOrientation && $this->back==0){
					$tempH=$this->resizeH;
					$this->resizeH=$this->resizeW;
					$this->resizeW=$tempH;	
					
					
					$this->cropW = $_POST['crHeight'];
					$this->cropH = $_POST['crWidth'];
					$_POST['crWidth']=$this->cropW;
					$_POST['crHeight']=$this->cropH;
				}
				
				$tempTop=$this->offsetTop;
				$tempLeft=$this->offsetLeft;
				if($this->back==0){//

					
					$this->offsetLeft = $offRight;
					$this->offsetTop = $offBottom;
					
					
				}
				
				if( $this->lastOrientation ==270){	
						$this->offsetLeft = $tempTop ;					
						$this->offsetTop = $offRight;
					
					}
					if( $this->lastOrientation ==90){	
						$this->offsetTop = $tempLeft ;
						$this->offsetLeft = $offBottom;
					
					}
				
			}else if($this->orientation == 270)
			{	
				if($this->orientation==$this->lastOrientation && $this->back==0){
				
					$tempH=$this->resizeH;
					$this->resizeH=$this->resizeW;
					$this->resizeW=$tempH;	
					
					
					$this->cropW = $_POST['crHeight'];
					$this->cropH = $_POST['crWidth'];
					
					$this->offsetLeft = $this->offsetTop ;
					
					$this->offsetTop = $offRight;
				}
				if($this->orientation!=$this->lastOrientation){
						if( $this->lastOrientation ==0){	
			
					}
					if( $this->lastOrientation ==180){	
						$this->offsetLeft = $offRight;
						$this->offsetTop = $offBottom;
					
					}
				
				}
			
			}
			//set height and crop in post as if it was at 0 degrees rotation
			$_POST['resizeWidth']=$this->resizeW;
			$_POST['resizeHeight']=$this->resizeH;
			$_POST['crWidth']=$this->cropW;
			$_POST['crHeight']=$this->cropH;
			$_POST['offsetLeft']=$this->offsetLeft;
			$_POST['offsetTop']=$this->offsetTop;

			
			
	
	}
	function initBack(){

		$_SESSION['simple-image']['virtWidth'] = $_SESSION['simple-image']['history'][$this->lastHistIndex()-2]["lastVirtWidth"];
		$_SESSION['simple-image']['virtHeight'] = $_SESSION['simple-image']['history'][$this->lastHistIndex()-2]["lastVirtHeight"];
		unset($_SESSION['simple-image']['history'][$this->lastHistIndex()-1]);	
			
		$_POST['resizeWidth'] = $_SESSION['simple-image']['history'][$this->lastHistIndex()-1]["lastResizeX"];
		$_POST['resizeHeight'] = $_SESSION['simple-image']['history'][$this->lastHistIndex()-1]["lastResizeY"];
		$_POST['crWidth'] = $_SESSION['simple-image']['history'][$this->lastHistIndex()-1]["lastCropX"];
		$_POST['crHeight'] = $_SESSION['simple-image']['history'][$this->lastHistIndex()-1]["lastCropY"];
		$_POST['offsetLeft'] =$_SESSION['simple-image']['history'][$this->lastHistIndex()-1]["lastOffsetLeft"];
		$_POST['offsetTop'] = $_SESSION['simple-image']['history'][$this->lastHistIndex()-1]["lastOffsetTop"];
		$_POST['brightness'] = $_SESSION['simple-image']['history'][$this->lastHistIndex()-1]["lastBrightness"];
		$_POST['contrast'] = $_SESSION['simple-image']['history'][$this->lastHistIndex()-1]["lastContrast"];
		
		$_POST['rotate'] = $_SESSION['simple-image']['history'][$this->lastHistIndex()-1]["lastRotate"];	

		
	$this->back=1;
	}
		//function setMargins($l,$r){
	//	$this->leftMargin =$l;
		//$this->rightMargin =$r;
		//}
	
	function lastHistIndex() 
	{
		return count($_SESSION['simple-image']['history']);
	}
	
	function virtRatioX()
	{	//image size difference before crop, for updating total virtual size (amount stretched by user)
		return (@$this->resizeW )/ ($this->lastCropW);
	}	
	function virtRatioY()
	{
		return @$this->resizeH / $this->lastCropH;
	}
	
	function updateSize()
	{
			$_SESSION['simple-image']['virtWidth'] = ($_SESSION['simple-image']['virtWidth'] * $this->virtRatioX());	//ratio of change from last to this time times self equals new virtual box size

			$_SESSION['simple-image']['virtHeight'] = $_SESSION['simple-image']['virtHeight'] * $this->virtRatioY();
	}	
	function cropRatioX()
	{		
		return ($_SESSION['simple-image']['origWidth']) / ($_SESSION['simple-image']['virtWidth'] );// how much to adjust crop size based on the difference between the virtual size and original size.
	}
	function cropRatioY()
	{			
		return $_SESSION['simple-image']['origHeight'] / $_SESSION['simple-image']['virtHeight'];
	}	
	

	function lastOffsetLeft()
	{	
		return $this->lastOffsetLeft;
	}
	function lastOffsetTop()
	{
		return $this->lastOffsetTop;
	}
}
