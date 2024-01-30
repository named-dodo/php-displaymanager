<?php /* Manage an "empty" (template) window */


function wc_init_empty(&$w, $data){
	return [ 'w'=>&$w, 'color'=>rgb(0,0,0) ];
} // returned data is stored in $w['wc']

function wc_destroy_empty(&$w){}


function wc_keypress_empty(&$w, $keypress){}


function wc_hover_empty(&$w, $mx, $my){
	return 1;
}

function wc_click_empty(&$w, $click){
	if(!$click['press']) return;
	$w['wc']['color']=rgb(random_int(0,255),random_int(0,255),random_int(0,255));
	img_clear( $w['buffer'] , $w['wc']['color'] );
}

?>
