<?php

// Initialize the keyboard input
function openKeyboard($device){
	if(!isset($device)) exit('Keyboard device not provided!'."\n");
	if(!is_readable($device)) exit("Can't open keyboard device $device!\n");

	$keyboard = fopen($device, "r");
	socket_set_blocking($keyboard,false);
	return [ 'kbdfd'=>$keyboard , 'keys'=>[] ];
}

// TODO implement keyboard device functions... 
// see https://www.linuxquestions.org/questions/linux-general-1/reading-and-writing-to-the-linux-keyboard-buffer-4175416506/

?>
