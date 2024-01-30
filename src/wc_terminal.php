<?php /* Manage a built-in terminal window */


function wc_init_terminal(&$w, $data){
	return [ 'w'=>&$w, 'string'=>'' ];
} // returned data is stored in $w['wc']

function wc_destroy_terminal(&$w){}


function wc_keypress_terminal(&$w,$keypress){
	$char=kbd_getChar($keypress);
	if(!$char) return;

	$w['wc']['string']=$w['wc']['string'].$char;
	img_drawString($w['buffer'], 10,10,15,800, rgb(10,200,10), $w['wc']['string']);
}


function wc_hover_terminal(&$w, $mx, $my){}

function wc_click_terminal(&$w, $click){}

?>
