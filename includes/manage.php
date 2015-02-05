<?php   /* Author: Carl Turechek*/
class manage {   
	var $destDir='banners/';//DIR_FS_BANNERS_IMG
	var $editDir;//dealer id
	var $origLink=''; 	
	var $thumbLink ; 
	var $origName; 	
	var $thumbPrefix='th_'; 	
	var $uploadName='';
	var $getImage='';
	
	var $tempFolder='images';
	var $editOrig;//should contain unique id- eg prev23618354.png
	var $editPrev;
	var $uniqueID;  
	var $isUpload=false;//set on upload in session too
	
	var $saveExt='';
	var $editExt='';
	function init(){
			

		if(isset($_SESSION['simple-image']['uploadName']) && $_SESSION['simple-image']['uploadName'] != ''){//get uploaded file's extension
			$this->editExt = pathinfo($_SESSION['simple-image']['uploadName'], PATHINFO_EXTENSION);
		}

		
		
		$this->uniqueID = session_id ();
		
		
		if(isset($_GET['image']) && $_GET['image'] != ''){
		$_SESSION['getImage']=$_GET['image'];
		
		$this->origName=basename($_GET['image']);
		$this->saveExt = pathinfo($_GET['image'], PATHINFO_EXTENSION);
		
		
		
		$this->editOrig="{$this->tempFolder}/orig{$this->uniqueID}.{$this->editExt}";
		$this->editPrev="{$this->tempFolder}/prev{$this->uniqueID}.{$this->editExt}";
			
		if($this->origName!=''){//if name is not null, 
			//set full paths for saving
			$this->origLink = $this->destDir  . $this->origName;
			//$this->thumbLink = $this->destDir . $this->thumbPrefix.$this->origName;			
			//make a copy to edit
			if(!@$_SESSION['simple-image']['isUpload']){
			//echo $this->editOrig;
				copy($this->origLink, $this->editOrig);//or use rename() to move
			}
			
			
		}
			
		}
		
	}

	function saveUpload($saveAs=''){// if isUpload else 
	if($this->origName==''){
		//$this->origName=$this->updateDB();
		if(isset($_SESSION['simple-image']['uploadName'])){
			$this->origName=basename($_SESSION['simple-image']['uploadName']);		
		}		
		
		$this->origLink = $this->destDir . $this->origName;
		//$this->thumbLink = $this->destDir . $this->thumbPrefix.$this->origName;
	}	
	if(isset($_SESSION['getImage']))
	{ 
		
			$this->origName=basename($_SESSION['getImage']);
			$this->origLink = $this->destDir . $this->origName;
			$this->saveExt = pathinfo($this->origLink, PATHINFO_EXTENSION);
	}	

		if( $saveAs!=''){
			$this->origLink = $this->destDir . 	$saveAs.'.'.$this->editExt;
		}
		
		$this->editOrig="{$this->tempFolder}/orig{$this->uniqueID}.{$this->editExt}";
		$this->editPrev="{$this->tempFolder}/prev{$this->uniqueID}.{$this->editExt}";

		if(@$_SESSION['simple-image']['isUpload']==1){// echo$this->origLink;echo$this->editOrig;

			if(count(@$_SESSION['simple-image']['history']) < 2 ){
				copy($this->editOrig, $this->origLink);//or use rename() to move
			}else{
				copy($this->editPrev, $this->origLink);
			}
			
			
		}
		//copy($this->editPrev, $this->thumbLink);//for save as...
	}


	
	
	
	function cleanUp(){	
		$dir = getcwd()."../{$this->tempFolder}/";//dir absolute path - deal_id
		$interval = strtotime('-1 hours');//files older than 24hours

		foreach (glob($dir."*") as $file) 
			//delete if older
			if (filemtime($file) <= $interval ) unlink($file);
	}
}
/*		

function updateDB(){
        $img_1_1 = 'revpostpic_' . md5(rand() . time()) . @$_SESSION['rev_id'];// $insert_id;
        $update_photo = "UPDATE `post_reviews` SET `image`=? WHERE `id`=?";
        re_db_prepare($update_photo);
		re_db_execute(array($img_1_1.'.'.$this->ext ,@$_SESSION['rev_id']));	

		return $img_1_1.'.'.$this->ext;
		
	}
	function init(){
	
		$this->uniqueID = session_id ();
		$this->isUpload=@$_SESSION['simple-image']['isUpload'];

		
	//is there an image already?
		if(isset($_GET['review_id'])){//if dealerid == session dealer id continue
			re_db_prepare("select image,dealer_id from post_reviews WHERE id = ?");
			$res=re_db_execute(array($_GET['review_id']));
			$row=re_db_fetch_array($res);
			//var_dump($row);
					
			//$row=array('dealer_id'=>1008,'image'=>'anOrig.jpg');		

			$this->isUpload=@$_SESSION['simple-image']['isUpload'];

			$_SESSION['rev_id']=$_GET['review_id'];
			
			if($_SESSION['deardirtusrid']==$row['dealer_id']){
			
			$this->editDir = $row['dealer_id'];
			$this->editOrig="{$this->tempFolder}/{$this->editDir}/orig{$this->uniqueID}.{$this->ext}";
			$this->editPrev="{$this->tempFolder}/{$this->editDir}/prev{$this->uniqueID}.{$this->ext}";	
			
			if(!is_dir('images/'.$this->editDir)){//$tempfolder
			mkdir('images/'.$this->editDir, 0777);
			
			}
			
			
			}						

		}	
	}*/