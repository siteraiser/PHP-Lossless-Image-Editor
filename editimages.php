<?php  /* Author: Carl Turechek*/

ini_set('upload_max_filesize', '20M');
ini_set('post_max_size', '20M');
ini_set('max_input_time', 300);
ini_set('max_execution_time', 300);


session_start();

include('includes/losslessimage.php'); 
include('includes/cropbox.php'); 
include('includes/manage.php'); 

$defCropW = 450;
$defCropH = 230;

$original = 'orig';
$preview = 'prev';



/****************-----Logic Start--------******************/
//
$tempName=(isset($_FILES[0]['tmp_name']) ? $_FILES[0]['tmp_name'] : '');//check for d&drop upload
$uploadName=@$_FILES[0]["name"];

if (!isset($tempName) || $tempName==''){

	$tempName=@$_FILES['uploaded_image']['tmp_name'];//if no drag & drop upload check for regular ajax upload  
	$uploadName=@$_FILES['uploaded_image']["name"];
}



if((!isset($tempName) || $tempName=='') && empty($_SERVER['HTTP_X_REQUESTED_WITH']) && empty($_POST)){ //if is not ajax, just a first load.
	//reset any session values
	unset($_SESSION['simple-image']);	
	$_SESSION['simple-image']['isUpload']=0;
	unset($_SESSION['simple-image']['uploadName']);
	unset($_SESSION['getImage']);
	
	$manage = new manage(); 

	$manage->init();

	
	$tempName=$manage->origLink;//get the provided original link and use like an upload
	$uploadName=$manage->origLink;
	$noAjax=1;
}



 if( isset($_POST['submit']) && $_POST['submit'] == 'Upload' || (isset($tempName)&&$tempName!='')) {   
	
	unset($_SESSION['simple-image']);

$_SESSION['simple-image']['isUpload']=1;
if(isset($uploadName) && $uploadName!=''){
$_SESSION['simple-image']['uploadName'] = $uploadName;
}
	if(!isset($manage)){

		$manage = new manage(); 

		$manage->init();

	}



	$image = new SimpleImage();
	$image->load($tempName); 

	$image->storeImage($original,$manage->uniqueID); 	

	
$origW=$image->getWidth();

$origH=$image->getHeight();



	if($image->getWidth() > 1000 || $image->getHeight() > 1000){

		if($image->getHeight() > $image->getWidth()){

			$image->resizeToHeight(1000);

		}else{		

			$image->resizeToWidth(1000);

		}	

	}



		$ratioX=$image->getWidth() / $origW;

	$ratioY=$image->getHeight() / $origH;

	



	$image->storeImage($preview,$manage->uniqueID); 

	$_SESSION['simple-image']['origWidth']=$origW;

	$_SESSION['simple-image']['origHeight']=$origH;

	$_SESSION['simple-image']['virtWidth']=$_SESSION['simple-image']['origWidth'] * $ratioX;

	$_SESSION['simple-image']['virtHeight']=$_SESSION['simple-image']['origHeight'] * $ratioY;

	$_SESSION['simple-image']['history'][] = array("lastResizeX"=>$image->getWidth(),"lastResizeY"=>$image->getHeight(),"lastCropX"=>$image->getWidth(),"lastCropY"=>$image->getHeight(),"lastOffsetLeft"=>@$_POST['offsetLeft'],"lastOffsetTop"=>@$_POST['offsetTop'],"lastVirtWidth"=>$_SESSION['simple-image']['virtWidth'],"lastVirtHeight"=>$_SESSION['simple-image']['virtHeight'],"lastBrightness"=>0,"lastContrast"=>0,"lastRotate" =>0,"lastOrientation"=>0);

	

	

$_SESSION['simple-image']['constrain']=1;

if(!isset($noAjax)){

$obj = array(); 

$obj['width'] = $image->getWidth();

$obj['height'] =$image->getHeight();

$obj['src'] = $_SESSION['simple-image'][$preview];



$response = json_encode($obj); 

 print $response; 

  $manage->cleanUp();

   exit();

  }

}



		//----Edit Section------------

 else if(@$_POST['save'] == '0' && (isset($_POST['resizeWidth']) || isset($_POST['rotate']) && (@$_POST['submit'] == 'Preview' || @$_POST['submit'] == 'Back' || @$_POST['rotate'] == '90' || @$_POST['rotate'] == '-90')) ) {
//echo $_POST['save'];
 //if crop is larger than image, get left and top margins, and add to virt ratios..

	if($_SESSION['simple-image'][$original]){  
	

	$_SESSION['simple-image'][$preview]=$_SESSION['simple-image'][$original];

	
	$manage = new manage(); 

	$manage->init();

	
	$image = new SimpleImage(); 

	$image->loadSessImage($original); //count of last

	$box = new virtualBox();	

	
	if(@$_POST['submit']== 'Back' || @$_POST['back'] ==1 ) {

		$box->initBack();	

	}
	

 	$box->init();

	

	$image->brightness=$box->brightness;

	$image->contrast=$box->contrast;

	

	$box->rotate();//figure current orientation based on this change and last orientation, and calculate new coords for crop at 0 degrees

	$image->orientation=$box->orientation;//set image rotation to be done later 
	

	if(@$_POST['submit'] != 'Back' && @$_POST['back'] !=1) {

		$box->updateSize();		
			

		$offsetLeft=((($_POST['offsetLeft'] ) * $box->cropRatioX()) + $box->lastOffsetLeft());///store calculated scaled pixels

		$offsetTop=(($_POST['offsetTop'] * $box->cropRatioY()) + $box->lastOffsetTop()) ;
		

		$_SESSION['simple-image']['history'][] = array("lastResizeX"=>$_POST['resizeWidth'],"lastResizeY"=>$_POST['resizeHeight'],"lastCropX"=>$_POST['crWidth'],"lastCropY"=>$_POST['crHeight'],"lastOffsetLeft"=>$offsetLeft,"lastOffsetTop"=>$offsetTop,"lastVirtWidth"=>$_SESSION['simple-image']['virtWidth'],"lastVirtHeight"=>$_SESSION['simple-image']['virtHeight'],"lastBrightness"=>$_POST['brightness'],"lastContrast"=>$_POST['contrast'],"lastRotate" =>@$_POST['rotate'],"lastOrientation"=>$box->orientation);

	}else{

		$offsetLeft=$box->lastOffsetLeft;

		$offsetTop=$box->lastOffsetTop;

	}

	$cropX = ($_POST['crWidth'] * $box->cropRatioX());//find the difference between last and this size

	$cropY = ($_POST['crHeight'] * $box->cropRatioY());



	$image->crop($cropX,$cropY,$offsetLeft ,$offsetTop);



	$image->resize($_POST['crWidth'],$_POST['crHeight']);

$image->rotate();

	$image->storeImage($preview,$manage->uniqueID); 

	

		if(isset($_POST['constrain'])){

			 

				$_SESSION['simple-image']['constrain']=1;

			 

		}else if($_POST['submit'] != 'Back'|| @$_POST['back'] !=1){	

				$_SESSION['simple-image']['constrain']=0;

			 

		}

	}	

	//SAVE

	}else if((@$_POST['save'] != '0') && isset($_POST['submit']) && ($_POST['submit'] == 'Preview' || $_POST['submit'] == 'Save') || @$_POST['save'] == 1 || @$_POST['save']!='') {

	$manage = new manage(); 

	$manage->init();
	
	if($_POST['save']==1){
		$manage->saveUpload();
		}else{
		
		$manage->saveUpload($_POST['save']);

	}
	$html='';
	foreach(glob('banners/'.'*') as $filename){
		$html.= '<a href="'.$_SERVER["SCRIPT_NAME"].'?image='.$filename.'">'.basename($filename) . "</a><br>\n";
	}
	
	$obj['save'] = 1;
	$obj['origLink'] = $manage->origLink;
	$obj['thumbLink'] = $manage->thumbLink;
	$obj['html'] = $html;
	$response = json_encode($obj); 

	print $response; 

	exit();

	}



//if(!isset($image)){

if(@$_POST['save'] == '0' && (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && !isset($noAjax))) {

$obj = array(); 

$obj['width'] = $image->getWidth();

$obj['height'] =$image->getHeight();

$obj['brightness'] = $_POST['brightness'];

$obj['contrast'] = $_POST['contrast'];

$obj['src'] = @$_SESSION['simple-image'][$preview];

$obj['back'] = (count(@$_SESSION['simple-image']['history']) < 2 ? 0 : 1);

$response = json_encode($obj); 

 print $response; 

   exit();

}

 ?><!DOCTYPE html> 
<html>

 <head>
<title>Image Editor</title>
<meta charset="utf-8">
<script>


var whenReady = (function(){ 

	var funcs = [];

	var ready = false;

	

	function handler(e){

		

		if(ready) return;		

		

		if(e.type === "readystatechange" && document.readyState !== "complete")

		return;

			

		for(var i = 0; i < funcs.length; i++)

			funcs[i].call(document);

			

			

			ready = true;

			funcs = null;

	}

	

	//Register the handler for any event we receive

	if (document.addEventListener){

		document.addEventListener("DOMContentLoaded", handler, false);

		document.addEventListener("readystatechange", handler, false);

		window.addEventListener("load", handler, false);

	}

	else if (document.attachEvent){

	document.attachEvent("onreadystatechange", handler);

	window.attachEvent("onload", handler);

	}

	

	//Return the whenReady funtion

	return function whenReady(f){

		if (ready) f.call(document); //If already ready, just run it

		else funcs.push(f);

	}

}());

	


window.onload=function(){

	var elts = document.getElementsByClassName("fileDropTarget");

	

	for(var i = 0; i < elts.length; i++){

		var target = elts[i];

		var url = target.getAttribute("data-uploadto");

		if(!url) continue;

		createFileUploadDropTarget(target, url);

	}

	

	function createFileUploadDropTarget(target, url){

		var uploading = false;

		

		console.log(target, url);

		

		target.ondragenter = function(e){

			console.log("dragenter");

			if(uploading) return; //ignore drags if we are busy

			var types = e.dataTransfer.types;

			if (types &&

				((types.contains && types.contains("Files")) ||

				(types.indexOf && types.indexOf("Files") !== -1))){

				target.classList.add("wantdrop");

				return false;

			}

		};

		

		target.ondragover = function(e){ if (!uploading) return false;};

		target.ondragleave = function(e){

			if (!uploading) target.classList.remove("wantdrop");

		};

		target.ondrop = function(e){console.log("drop");

			if (uploading) return false;

			var files = e.dataTransfer.files;
			


//if (files[0].type != 'image/png' && files[0].type != 'image/x-png' && files[0].type != 'image/jpeg' && files[0].type != 'image/pjpeg' && files[0].type != 'image/gif' ) return false;
if ( !(/\.(gif|jpg|jpeg|png)$/i).test( files[0].name.toLowerCase())) {
  alert('Accepted image types: gif, jpg, jpeg or png.');
  return false;
}

			if (files && files.length){

				uploading = true;

				var message ="Uploading file:<ul>";

				for(var i = 0; i < files.length; i++)

					message += "<li>" + files[i].name + "</li>";

					message += "</ul>";

					

					target.innerHTML = message;

					target.classList.remove("wantdrop");

					target.classList.add("uploading");

					

					var xhr = new XMLHttpRequest();

					xhr.open("POST", url, true);

					

		

					xhr.addEventListener("load", function () {

						var str=xhr.responseText;var data = JSON.parse(str);

						console.log(data);

						uploaded(data);

					}, false);

					

							

					var body = new FormData();

					for(var i = 0; i < files.length; i++) body.append(i, files[i]);

					xhr.upload.onprogress = function(e){

						if (e.lengthComputable){

						target.innerHTML = message +

						Math.round(e.loaded/e.total*100) +

						"% Complete";

						}

					};

					xhr.upload.onload = function(e){

						uploading = false;

						target.classList.remove("uploading");

						target.innerHTML = "drop image to upload";

						

					};

					xhr.send(body);

					

					return false;

					}

					target.classList.remove("wantdrop");

				}

			}

			

				


var files;



$('input[type=file]').on('change', prepareUpload);


function prepareUpload(event)

{

  files = event.target.files;

}	

$('#upload').on('submit', uploadFiles);



function uploadFiles(event)

{  event.stopPropagation();

    event.preventDefault();

//console.log(files[0].name);

if ( !(/\.(gif|jpg|jpeg|png)$/i).test( files[0].name.toLowerCase())) {
  alert('Accepted image types: gif, jpg, jpeg or png.');
  return false;
}

//if (files[0].type != 'image/png' && files[0].type != 'image/jpeg' && files[0].type != 'image/gif' ) return false;
			var message ="Uploading file:<ul>";

				for(var i = 0; i < files.length; i++)

					message += "<li>" + files[i].name + "</li>";

					message += "</ul>";

					

					target.innerHTML = message;

					target.classList.remove("wantdrop");

					target.classList.add("uploading");

					

					var xhr = new XMLHttpRequest();

					xhr.open("POST", url, true);

					

					xhr.addEventListener("load", function () {

					var str=xhr.responseText;var data = JSON.parse(str);

					uploaded(data);

					console.log(data);

					}, false);

					

					var body = new FormData();

					for(var i = 0; i < files.length; i++) body.append(i, files[i]);

					xhr.upload.onprogress = function(e){

						if (e.lengthComputable){

						target.innerHTML = message +

						Math.round(e.loaded/e.total*100) +

						"% Complete";

						}

					};

					xhr.upload.onload = function(e){

					uploading = false;

					target.classList.remove("uploading");

					target.innerHTML = "drop image to upload";

						$('input[type=file]').wrap('<form>').closest('form').get(0).reset();

						$('input[type=file]').unwrap();

						

						

					};

					xhr.send(body);

					return false;

}	



	function uploaded(data){

		var defCropW =450;

		var defCropH =230;

		//Show image panel

		$("img.image").attr("src", data.src + "?" + new Date().getTime());

		

		$("#reWidth").val(data.width);

		$("#reHeight").val(data.height);	

		$("#cropWidth").val(defCropW);

        $("#cropHeight").val(defCropH);

		$("#offLeft").val(0);

		$("#offTop").val(0);



		



		  $(".image,#mirror,.ui-wrapper").width(data.width);

		  $(".image,#mirror,.ui-wrapper").height(data.height);

		  $("#border").width(defCropW);

		  $("#border").height(defCropH);

		  

		  $("#button-back").prop("disabled", true);

	$( ".pre-hidden" ).css("display" , "block");

	        $( ".image" ).resizable( "option", "minWidth", parseInt($("#border").width()) + parseInt($("#offLeft").val()));

		$( ".image" ).resizable( "option", "minHeight", parseInt($("#border").height()) + parseInt($("#offTop").val()));  

	}

	

}

</script>

<style>.wantdrop{background-color:#228;}.uploading{background-color:#282;}.fileDropTarget{width:475px;border: solid 1px #090;}</style>





 <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>

<link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">

<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>

 <style>  

body{width:1000px;margin:0px auto;}

 #mirror{border:dotted 2px #000;} 

 div#mirror:hover {border:solid 2px #00F;}

   

 #inside-border{border:dashed 2px #888;margin:-2px;width:inherit;height:inherit;}

 #inside-border:hover{border:dashed 2px #AAF;}

 #inside-border:hover div#mirror:hover {border:solid 2px #005;}

  

 .resizing{border:dashed 2px yellow !important;}

 #control-panel{padding:12px;padding-bottom:50px;margin-bottom:25px;border: solid 2px #494;}



.adjust-box div{float:left;}

.adjust-box div.wrapper{width:75px;float:left;padding:0px 25px;margin:0px 0px 0px 10px;text-align:center;}

.upload-box{width:480px;float:left;margin-bottom:-75px}

.size-box{width:480px;float:left;margin-top:75px}

#slider-contrast,#slider-brightness{height:137px;}

#contrast-text,#brightness-text{width:30px;margin:5px;}

div.label{width:100%;}



.size-box,.upload-box,.adjust-box div.wrapper{border: solid 2px #494;padding:10px;}

form {line-height:2em;}





#button-save{position: relative;left:50px;z-index:400;float:left;font-size: 150%;}
#button-save-as{position: relative;left:70px;z-index:400;float:left;font-size: 150%;}
#button-back{position: relative;left:0px;z-index:400;float:left;font-size: 150%;}

#button-preview{position:absolute;left:43%;font-size: 150%;}



form input[type=text]{width:136px;}
#files{}

</style>
<script>
$(document).ready(function(){
 $("div#files").hide();

    $("#toggle").click(function(){
        $("div#files").slideToggle();
    });
});
</script>


</head>

<body>
<?php
echo'editing:'.$manage->origName;
?>
<button id="toggle">show files</button>
<div id="files"> 
<?php

foreach(glob('banners/'.'*') as $filename){
    echo '<a href="'.$_SERVER["SCRIPT_NAME"].'?image='.$filename.'">'.basename($filename) . "</a><br>\n";
}
?>
</div>



<div id="control-panel">

	<div class="upload-box">

		<form id="upload" action="<?php echo $_SERVER["SCRIPT_NAME"];?>" method="post" enctype="multipart/form-data"> 

			<input type="file" name="uploaded_image" >   

			<input type="submit" name="submit" value="Upload" >   

		</form>  <div id="fileselect" type="file" class="fileDropTarget" data-uploadto="/editimages/editimages.php">Drop image</div> 

	</div>



<div class="pre-hidden">	 

<?php /* if(isset($image)){ */?>	 

	<form id="edit" action="<?php echo $_SERVER["SCRIPT_NAME"];?>" method="post" enctype="application/x-www-form-urlencoded"> 

	<div class="size-box">

	<label for="reWidth" >Image Width</label>

	<input id="reWidth" type="text" name="resizeWidth" value="<?php echo(@method_exists($image,'getHeight') ? @$image->getWidth() : ''); ?>" > 

	<label for="reHeight" >Image Height</label>

	<input id="reHeight" type="text" name="resizeHeight" value="<?php echo(@method_exists($image,'getHeight') ? @$image->getHeight() : ''); ?>" >  

	<br/>

	<label for="reConstrain" >Constrain Proportions</label>





	<input id="reConstrain" type="checkbox" value="1" checked name="constrain" >  

	

	<!--<label for="containCrop" >Contain Crop</label>

	<input id="containCrop" type="checkbox" checked value="1" name="contain" >  

	-->

	<br/>

	<span class="hidden">

		<label for="offTop" >top</label>

		<input id="offTop" type="text" name="offsetTop" value="0" > 

		<label for="offLeft" >left</label>

		<input id="offLeft" type="text" name="offsetLeft" value="0" >  

		<br/>

	</span>



	<label for="cropWidth" >Crop Width</label>

	<input id="cropWidth" type="text" name="crWidth" value="450" > 

	<label for="cropHeight" >Crop Height</label>

	<input id="cropHeight" type="text" name="crHeight" value="230<?php /*echo (isset($_POST['crHeight']) ? $_POST['crHeight'] : $defCropH)*/?>" >  

	</div>

	<div class="adjust-box">

	<div class="wrapper">

	<div class="label">Brightness</div>

	<div id="slider-brightness"></div>



	<input id="brightness-text" type="text" name="brightness" value="<?php echo (isset($_POST['brightness']) ? $_POST['brightness'] : 0)?>" > 

	</div>



	<div class="wrapper">

	<div class="label">Contrast</div>

	<div id="slider-contrast"></div>



	<input id="contrast-text" type="text" name="contrast" value="<?php echo (isset($_POST['contrast']) ? $_POST['contrast'] : 0)?>" > 

	</div>

	

	

	<div class="wrapper">

	<label for="rotate" >Rotate</label>

	<input id="rotateClock" type="submit" name="rotate" value="-90" >  

	<input id="rotateCounter" type="submit" name="rotate" value="90" > 

	</div>

	

	</div>

	<br style="clear:both"/>

	<input id="button-preview" type="submit" name="submit" value="Preview" >   

	</form>  



	<form action="<?php echo $_SERVER["SCRIPT_NAME"];?>" method="post" enctype="application/x-www-form-urlencoded"> 

	<input id="button-back" type="submit" name="submit" <?php if(count(@$_SESSION['simple-image']['history']) < 2)echo'disabled';?> value="Back" >  

	</form>

	<form action="<?php echo $_SERVER["SCRIPT_NAME"];?>" method="post" enctype="application/x-www-form-urlencoded"> 

	<input id="button-save" type="submit" name="submit" value="Save" >  

	</form> 
	<form action="<?php echo $_SERVER["SCRIPT_NAME"];?>" method="post" enctype="application/x-www-form-urlencoded"> 

	<input id="button-save-as" type="submit" name="submit" value="SaveAs" >  

	</form>
	

	</div>   

</div>

<!-- end panel -->







<div class="pre-hidden">	

<div id="mirror" style="position:relative;width:<?php echo(@method_exists($image,'getHeight') ? @$image->getWidth() : '0'); ?>px;height:<?php echo(@method_exists($image,'getHeight') ? @$image->getHeight() : '0'); ?>px;">

<div id="border" style="position:absolute; width:450px;height:230px;  box-shadow: 10px 10px 10px grey, 0 0 10px black;z-index:50;"><div id="inside-border"></div></div>

<img class="image" style="height:<?php echo(@method_exists($image,'getHeight') ? @$image->getHeight() : '0'); ?>px;width:<?php echo(@method_exists($image,'getHeight') ? @$image->getWidth() : '0'); ?>px;" src="<?php echo $_SESSION["simple-image"][$preview].'?last_picture_update=' . filemtime($_SESSION['simple-image'][$preview]);?>"/>

</div>



<div style="width:100%;height:1000px;"></div>

</div>



<?php

//}







//	echo '<pre>';

 // echo htmlspecialchars(print_r($_SESSION['simple-image']['history'], true));

// echo '</pre>';

 

 ?>

 

<script>





//--Sliders-------

 $(function() {

$( "#slider-contrast" ).slider({

orientation: "vertical",

range: "min",

min: -100,

max: 100,

value: <?php echo (isset($_POST['contrast']) ? $_POST['contrast'] : 0)?>,

slide: function( event, ui ) {

$( "#contrast-text" ).val( ui.value );

}

});

$( "#contrast-text" ).val( $( "#slider-contrast" ).slider( "value" ) );





$( "#slider-brightness" ).slider({

orientation: "vertical",

range: "min",

min: -100,

max: 100,

value: <?php echo (isset($_POST['brightness']) ? $_POST['brightness'] : 0)?>,

slide: function( event, ui ) {

$( "#brightness-text" ).val( ui.value );

}

});

$( "#brightness-text" ).val( $( "#slider-brightness" ).slider( "value" ) );



$('.pre-hidden').on('click', 'input[type="submit"]', function(event) {

 

 if($(this).attr('value') == -90 || $(this).attr('value') == 90){

	var rotate=$(this).attr('value') ; 

 }else{var rotate=0;}

 

 if($(this).attr('value') == 'Back'){

	var back=1;		

}else{ var back=0; }

 

  if($(this).attr('value') == 'Save')
  {

	var save=1;		

	}else if($(this).attr('value') == 'SaveAs')
	{
	
    var save = prompt("Please enter image name", "default");
    
    if (save == null) {
	return false;
       // save=0;
    }

		
	}else{ var save=0; }

 

 

	event.stopPropagation();

	event.preventDefault();

	

	$.ajax({'type':'POST', data: { 

		resizeWidth:$("#reWidth").val(),

		resizeHeight:$("#reHeight").val(),

		crWidth:$("#cropWidth").val(),

		crHeight:$("#cropHeight").val(),

		offsetLeft:$("#offLeft").val(),

		offsetTop:$("#offTop").val(),

		constrain:$("#reConstrain").val(),

		brightness:$("#brightness-text").val(),		

		contrast:$("#contrast-text").val(),	

		rotate:rotate,	

		back:back,

		save:save



},'success':function(data)

 {	

 

var data = JSON.parse(data);

if(data.save != 1){

	$("img.image").attr("src", data.src + "?" + new Date().getTime());

		

		$("#reWidth").val(data.width);

		$("#reHeight").val(data.height);	

		$("#cropWidth").val(data.width);

        $("#cropHeight").val(data.height);

      // $("#reConstrain").val(data.constrain);		

		$("#slider-brightness").slider('option','value',data.brightness);

		$("#slider-contrast").slider('option','value',data.contrast);

		$("#brightness-text").val(data.brightness);

		$("#contrast-text").val(data.contrast);

		

		

		



		$("#border").css("left","0px");

		$("#border").css("top","0px");

		$("#offLeft").val(0);

		$("#offTop").val(0);

		  $(".image,#mirror,.ui-wrapper").width(data.width);

		  $(".image,#mirror,.ui-wrapper").height(data.height);

		  $("#border").width(data.width);

		  $("#border").height(data.height);	

        $( ".image" ).resizable( "option", "minWidth", parseInt($("#border").width()) + parseInt($("#offLeft").val()));

		$( ".image" ).resizable( "option", "minHeight", parseInt($("#border").height()) + parseInt($("#offTop").val()));  

		if(data.back){

			$("#button-back").prop("disabled", false);

		}else{$("#button-back").prop("disabled", true);}

}else{ document.getElementById("files").innerHTML = data.html;}



		

 },'url': 'http://'+location.host+'<?php echo $_SERVER["SCRIPT_NAME"];?>','cache':false});









	});



});





$( ".hidden" ).css("display" , "none");

<?php 

if(!isset($noAjax) || !isset($image)){

?>

$( "div.pre-hidden" ).css("display" , "none");

<?php

}

?>

var constrain=<?php echo (@$_SESSION['simple-image']['constrain'] == 1 ? 'true' : 'false')?>;

var containCrop=1;



var imageMinWidth=50;

var imageMinHeight=50;

var cropMinWidth=50;

var cropMinHeight=50;

var borderWidth =0;



imageMinWidth = 450;

imageMinHeight = 230;

$( "#reWidth" ).blur(function() {

  $(".image,#mirror,.ui-wrapper").width($(this).val());

 

});

$( "#reHeight" ).blur(function() {

  $(".image,#mirror,.ui-wrapper").height($(this).val());

   

});



$( "#cropWidth" ).blur(function() {

  $("#border").width($(this).val());

  

});

$( "#cropHeight" ).blur(function() {

  $("#border").height($(this).val());

});









//Resizable Image

$( ".image" ).resizable({

aspectRatio: constrain, 

minWidth: imageMinWidth, 

minHeight: imageMinHeight,

alsoResize: "#mirror",

start: function(event, ui) { 

$("div#mirror").addClass('resizing');



},

stop: function(event, ui) { 

	$("div#mirror").removeClass('resizing');
        $("#reWidth").val($(this).width());

        $("#reHeight").val($(this).height());



       }

});





$( "#border" ).resizable({

handles: 'all',

 containment: 'parent' , 

minWidth: cropMinWidth , 

minHeight: cropMinHeight ,

start: function(event, ui) { 

$("#inside-border").addClass('resizing');



},

stop: function(event, ui) { 



var offLeft = $(this).css('left')

var offTop = $(this).css('top')

offLeft=offLeft.substr(0, offLeft.length - 2);

offTop=offTop.substr(0, offTop.length - 2);

if(isNaN(offLeft)){offLeft='0px';}

if(isNaN(offTop)){offTop='0px';}



        $("#cropWidth").val($(this).width());

        $("#cropHeight").val($(this).height());

		$("#offLeft").val(offLeft);

		$("#offTop").val(offTop);

		

		

$( ".image" ).resizable( "option", "minWidth", parseInt($(this).width()) + parseInt($("#offLeft").val()));

$( ".image" ).resizable( "option", "minHeight", parseInt($(this).height()) + parseInt($("#offTop").val()));

$("#inside-border").removeClass('resizing');//effects

}

}).draggable({

 containment: 'parent' , 

 

start: function(event, ui) { 

	$("#inside-border").addClass('resizing');



},

 

 

 stop: function(event, ui) { 

 

 $("#inside-border").removeClass('resizing');



var offLeft = $(this).css('left')

var offTop = $(this).css('top')

 $("#offLeft").val(offLeft.substr(0, offLeft.length - 2));

 $("#offTop").val(offTop.substr(0, offTop.length - 2));

$( ".image" ).resizable( "option", "minWidth", parseInt($(this).width()) + parseInt($("#offLeft").val()));

$( ".image" ).resizable( "option", "minHeight", parseInt($(this).height()) + parseInt($("#offTop").val()));



 }

 /*,

	handle:	'div.bar',

	 

        $.cookie('div1w', $(this).width());

 $.cookie('div1h', $(this).height());

   $.cookie('div1x', $(this).css('left'));

       $.cookie('div1y', $(this).css('top'));  

       }*/

	   });







//Hack to make aspectRatio, etc. changeable after init.

(function() {

    var oldSetOption = $.ui.resizable.prototype._setOption;

    $.ui.resizable.prototype._setOption = function(key, value) {

        oldSetOption.apply(this, arguments);

        if (key === "aspectRatio") {

            this._aspectRatio = !!value;

        }

    };

})();



(function() {

    var oldSetOption = $.ui.resizable.prototype._setOption;

    $.ui.resizable.prototype._setOption = function(key, value) {

        oldSetOption.apply(this, arguments);

        if (key === "minHeight") {

            this._minHeight = !!value;

        }

    };

})();

(function() {

    var oldSetOption = $.ui.resizable.prototype._setOption;

    $.ui.resizable.prototype._setOption = function(key, value) {

        oldSetOption.apply(this, arguments);

        if (key === "minWidth") {

            this._minWidth = !!value;

        }

    };

})();



(function() {

    var oldSetOption = $.ui.resizable.prototype._setOption;

    $.ui.resizable.prototype._setOption = function(key, value) {

        oldSetOption.apply(this, arguments);

        if (key === "containment") {

            this._containment = !!value;

        }

    };

})();

(function() {

    var oldSetOption = $.ui.draggable.prototype._setOption;

    $.ui.draggable.prototype._setOption = function(key, value) {

        oldSetOption.apply(this, arguments);

        if (key === "containment") {

            this._containment = !!value;

        }

    };

})();

/*

 $("#containCrop").on('click', function() {	   

 

if(containCrop == true){

containCrop = false;

 $("#border").resizable( "option", "containment", false );

 $("#border").draggable( "option", "containment", false );

 $(".image").resizable( "option", "containment", false );

}

else 

{

containCrop = true;

$("#border").resizable( "option", "containment", true );

$("#border").draggable( "option", "containment", true );

$(".image").resizable( "option", "containment", true );

}

});  

*/ 

$("#reConstrain").on('click', function() {	   
if(constrain == true){

constrain = false;

$( ".image" ).resizable( "option", "aspectRatio", false );

}

else 

{

constrain = true;

$( ".image" ).resizable( "option", "aspectRatio", true );

}

});  

</script>
</body>

</html>

<?php //}

