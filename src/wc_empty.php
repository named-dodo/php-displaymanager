<?php /* Manage an "empty" (template) window */


function wc_init_empty(&$w, $data){
	return [ 'w'=>&$w, 'color'=>rgb(0,0,0) ];
} // returned data is stored in $w['wc']

function wc_destroy_empty(&$w){}

function wc_tick_empty(&$w){}


function wc_keypress_empty(&$w, $keypress){}


function wc_hover_empty(&$w, $mx, $my){
	return 1;
}

function wc_click_empty(&$w, $click){
	GLOBAL $compositor;
	if(!$click['press']) return;
	$w['wc']['color']=rgb(random_int(0,255),random_int(0,255),random_int(0,255));
	img_clear( $w['buffer'] , $w['wc']['color'] );
	com_requestWUpdate($compositor, $w, WgetRect($w) );
	
//TODO debug
	//echo("\n\rsize: ".$w['buffer']['w']."x".$w['buffer']['h']);
//	img_drawLine($w['buffer'], 0,0, $w['buffer']['w'],$w['buffer']['h'], rgb(255,0,0) );
//	img_drawLine($w['buffer'], $w['buffer']['w'],0, 0,$w['buffer']['h'], rgb(255,0,0) );
}

?>
