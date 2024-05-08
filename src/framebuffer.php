<?php /* manage framebuffers. */

function framebuffer_open($file){
	if(!is_writable($file)){
		return false;
	}

	$dimension_string=file_get_contents("/sys/class/graphics/".substr($file, strrpos($file, '/')+1)."/virtual_size");
	if(! $dimension_string ){
		return false;
	}
	$w=intval(explode(',',$dimension_string)[0]);
	$h=intval(explode(',',$dimension_string)[1]);

	return ['w'=>$w,'h'=>$h,'file'=>$file];
}

function framebuffer_blit(&$framebuffer,&$image){
	if( !$framebuffer or $framebuffer['w']!=$image['w'] or $framebuffer['h']!=$image['h'] ){
		return false;
	}

	$fb=fopen($framebuffer['file'], "w");
	if(!$fb){ echo("Error writing to framebuffer file:".$framebuffer['file']."\n"); return false; }
	fwrite($fb,img_getD($image));
	fclose($fb);
	return true;
}

?>
