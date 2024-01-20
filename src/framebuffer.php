<?php /* manage framebuffers. */

if(!isset($maxw)) exit('screen width ($maxw) not provided!'."\n");
if(!isset($maxh)) exit('screen width ($maxh) not provided!'."\n");
if(!isset($framebuffer)) exit('framebuffer device not provided!'."\n");

// preliminary checks on file access...
if(!is_writable($framebuffer)){
	echo "Can't open framebuffer!\n";
	exit(1);
}

// internal framebuffer. dimensions are width x heigth x 4.
function createFrameBuffer($color){
	GLOBAL $maxw, $maxh;
	return createImage($maxw, $maxh, $color );
}

// Framebuffer functions
function blit(&$buff){
	GLOBAL $framebuffer;
	$fb=fopen($framebuffer, "w");
	fwrite($fb,getD($buff));
	fclose($fb);
}

?>
