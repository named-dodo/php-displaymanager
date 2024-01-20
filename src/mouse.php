<?php /* Process mouse input */

// Initialize the mouse input
function openMouse($mx, $my, $device){
	if(!isset($device)) exit('Mice device not provided!'."\n");
	if(!is_readable($device)) exit("Can't open mouse device $device!\n");

	$mice = fopen($device, "r");
	socket_set_blocking($mice,false);
	return [ 'micefd'=>$mice , 'x'=>$mx/2,'y'=>$my/2, 'mx'=>$mx,'my'=>$my, 'clicks'=>[], 1=>false, 2=>false, 3=>false, 'wait'=>[1=>true, 2=>true, 3=>true] ];
}


// clicks are an [ "x"=>$mousex, "y"=>$mousey, "button"=>1, "press"=>$button1 ] or null
function addClick(&$mice, $x, $y, $button, $press){
	$mice['clicks'][] = array("x"=>$x, "y"=>$y, "button"=>$button, "press"=>$press );
}
function pullClick(&$mice){
	return array_shift($mice['clicks']);
}

function processButton(&$mice, $num, $click){
	if( $mice['wait'][$num] and $click ) $click=false; else $mice['wait'][$num]=false;
	if($click!=$mice[$num]) addClick($mice, $mice['x'], $mice['y'], $num, $click);	
	$mice[$num]=$click;
}
function isButtonPressed(&$mice,$button){
	return $mice[$button];
}

function getMouseX(&$mice){
	return $mice['x'];
}
function getMouseY(&$mice){
	return $mice['y'];
}


function readMouseInput(&$mice){
	while($packet=fread($mice['micefd'],3) ){

		// check button states.
		$buttons=ord($packet[0]);

		processButton($mice, 1, ($buttons&1)!=0 );
		processButton($mice, 2, ($buttons&2)!=0 );
		processButton($mice, 3, ($buttons&4)!=0 );

		// get relative mouse coords.
		$mrx=ord($packet[1]);
		if($mrx>127) $mrx|=-256;

		$mry=ord($packet[2]);
		if($mry>127) $mry|=-256;

		// update and clamp mouse coords.
		$mice['x']=clamp($mice['x']+$mrx, 0, $mice['mx']);
		$mice['y']=clamp($mice['y']-$mry, 0, $mice['my']);
	}
}

?>
