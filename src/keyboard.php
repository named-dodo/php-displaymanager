<?php /* Managing keyboard devices */

// Note: this will only return the FIRST keyboard found.
function kbd_find() {
	$list = glob("/dev/input/by-id/*-event-kbd");
	foreach( $list as $file ){
		// The "-if01-" entries aren't actual keyboard devices.
		if( preg_match("/-if..-event-kbd$/" ,$file) ) continue;
		return $file;
	}
}

// Initialize the keyboard filestream
function kbd_open($device){
	if(!is_readable($device)) return false;

	$keyboard = fopen($device, "r");
	socket_set_blocking($keyboard,false);
	return [ 'kbdfd'=>$keyboard , 'pressed'=>[], ];
}

// read and return a single key event.
function kbd_read(&$kbd){
	while($packet=fread($kbd['kbdfd'],24) ){
		$p_type=int2($packet,16);
		$p_code=int2($packet,18);
		$p_val =int4($packet,20);
		if($p_type!==1 or $p_val>2 or $p_val<0) continue;

		if($p_val==0){ // release key
			$index=array_search($p_code, $kbd["pressed"]);
			if($index!==false) unset($kbd["pressed"][$index]);
		}
		if($p_val==1){ // press key
			$index=array_search($p_code, $kbd["pressed"]);
			if($index==false) $kbd["pressed"][]=$p_code;
		}

		return ['key'=>$p_code,'action'=>$p_val, 'kbd'=>&$kbd];
	}
	return false;
}

// Check if a certain key is pressed.
function kbd_isPressed(&$kbd, $key_id){
	foreach( $kbd["pressed"] as $press ){
		if($press===$key_id) return true;
	}
	return false;
}

// Key mapping source: https://github.com/torvalds/linux/blob/master/include/uapi/linux/input-event-codes.h
define('KBD_CODENAMES', [ "RESERVED", "ESC", "1", "2", "3", "4", "5", "6", "7", "8", "9", "0", "MINUS", "EQUAL", "BACKSPACE", "TAB", 
"Q", "W", "E", "R", "T", "Y", "U", "I", "O", "P", "LEFTBRACE", "RIGHTBRACE", "ENTER", "LEFTCTRL", 
"A", "S", "D", "F", "G", "H", "J", "K", "L", "SEMICOLON", "APOSTROPHE", "GRAVE", "LEFTSHIFT", "BACKSLASH", 
"Z", "X", "C", "V", "B", "N", "M", "COMMA", "DOT", "SLASH", "RIGHTSHIFT", "KPASTERISK", "LEFTALT", "SPACE", "CAPSLOCK", 
"F1", "F2", "F3", "F4", "F5", "F6", "F7", "F8", "F9", "F10", "NUMLOCK", "SCROLLLOCK", "KP7", "KP8", "KP9", "KPMINUS", "KP4", "KP5", "KP6", "KPPLUS", "KP1", "KP2", "KP3", "KP0", "KPDOT", 
"UNNAMED", "ZENKAKUHANKAKU", "102ND", "F11", "F12", "RO", "KATAKANA", "HIRAGANA", "HENKAN", "KATAKANAHIRAGANA", "MUHENKAN", 
"KPJPCOMMA", "KPENTER", "RIGHTCTRL", "KPSLASH", "SYSRQ", "RIGHTALT", "LINEFEED", "HOME", "UP", "PAGEUP", "LEFT", "RIGHT", "END", "DOWN", "PAGEDOWN", "INSERT", "DELETE",
 "MACRO", "MUTE", "VOLUMEDOWN", "VOLUMEUP", "POWER", "KPEQUAL", "KPPLUSMINUS", "PAUSE", "SCALE", "KPCOMMA", "HANGEUL", "HANJA", "YEN", "LEFTMETA", "RIGHTMETA", "COMPOSE"] );

// convert keyboard ID's to human readable key names.
function kbd_getName($key_id){
	return KBD_CODENAMES[$key_id];
}
// convert human readable key names to ID's.
function kbd_getID($key_name){
	return array_search(strtoupper($key_name), KBD_CODENAMES);
}

define('KBD_CHAR_NORMAL', [ false, false, "1", "2", "3", "4", "5", "6", "7", "8", "9", "0", "-", "=", false, "\t", "q", "w", "e", "r", "t", "y", "u", "i", "o", "p", 
"[", "]", "\n", false, "a", "s", "d", "f", "g", "h", "j", "k", "l", ";", "'", "`", false, "\\", "z", "x", "c", "v", "b", "n", "m", ",", ".", "/", false, "*", false, " ", 
false, false, false, false, false, false, false, false, false, false, false, false, false, "7", "8", "9", "-", "4", "5", "6", "+", "1", "2", "3", "0", ".", false, false, false, 
false, false, false, false, false, false, false, false, ",", "\n", false, "/", false, false, false, false, false, false, false, false, false, false, false, false, false, false, false, 
false, false, false, "=", false, false, false, ",", false, false, false, false, false, false ] );

define('KBD_CHAR_SHIFTED', [ false, false, "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "+", false, "\t", "Q", "W", "E", "R", "T", "Y", "U", "I", "O", "P", 
"{", "}", "\n", false, "A", "S", "D", "F", "G", "H", "J", "K", "L", ":", "\"", "~", false, "|", "Z", "X", "C", "V", "B", "N", "M", "<", ">", "?", false, "*", false, " ", 
false, false, false, false, false, false, false, false, false, false, false, false, false, "7", "8", "9", "-", "4", "5", "6", "+", "1", "2", "3", "0", ".", false, false, false, 
false, false, false, false, false, false, false, false, ",", "\n", false, "/", false, false, false, false, false, false, false, false, false, false, false, false, false, false, false, 
false, false, false, "=", false, false, false, ",", false, false, false, false, false, false ] );

// returns a corresponding ASCII character or false if there is none.
function kbd_getChar($key_event){
	if($key_event['action']==0) return false;
	
	if(kbd_isPressed($key_event['kbd'], kbd_getID('LEFTSHIFT')) or kbd_isPressed($key_event['kbd'], kbd_getID('RIGHTSHIFT')) ){
		return KBD_CHAR_SHIFTED[$key_event['key']];
	}else{
		return KBD_CHAR_NORMAL[$key_event['key']];
	}
}
// check if key event is key of certain keyname.
function kbd_isKey($key_event, $key_name){
	return kbd_getID($key_name)==$key_event['key'];
}
// check if key event is up press
function kbd_isUp($key_event){
	return $key_event['action']==0;
}
// check if key event is down press
function kbd_isDown($key_event){
	return $key_event['action']==1;
}
// check if key event is down press
function kbd_isRepeat($key_event){
	return $key_event['action']==2;
}

?>
