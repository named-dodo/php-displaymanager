<?php /* Manage a built-in terminal window */


function wc_init_terminal(&$w, $data){
	img_clear( $w['buffer'] , rgb(0,32,0) );

	$term=pr_init('bash');
	if(!$term) return false;

	return [ 'w'=>&$w, 'string'=>'', 'lines'=>[], 'term'=>&$term ];
} // returned data is stored in $w['wc']


function wc_destroy_terminal(&$w){
	pr_close($w['wc']['term']);
}

function wc_tick_terminal(&$w){
	$hadmore=false;
	do {
		$hasmore=false;

		if($out=pr_STDOUT($w['wc']['term'])){
			$hasmore=true;
			$w['wc']['lines'][]=['color'=>rgb(0,100,0),'text'=>rtrim($out)];
		}

		if($err=pr_STDERR($w['wc']['term'])){
			$hasmore=true;
			$w['wc']['lines'][]=['color'=>rgb(100,0,0),'text'=>rtrim($err)];
		}

		if($hasmore) $hadmore=true;
	} while($hasmore);
	
	if($hadmore)
		wcf_terminal_drawOutput($w);

	if(!pr_isRunning($w['wc']['term'])){
		Wdestroy($w);
	}
}

function wc_hover_terminal(&$w, $mx, $my){}
function wc_click_terminal(&$w, $click){}


function wc_keypress_terminal(&$w,$keypress){
	$char=kbd_getChar($keypress);
	if($char and $char!="\n"){
		if($char=="\t") $char='    ';
		$w['wc']['string']=$w['wc']['string'].$char;
		wcf_terminal_drawLine($w);
		return;
	}

	if( kbd_isKey($keypress, 'BACKSPACE') and ! kbd_isUp($keypress) ){
		$line=$w['wc']['string'];
		$w['wc']['string']=substr($line,0,-1);
		
		wcf_terminal_drawLine($w);
		return;
	}

	if( kbd_isKey($keypress, 'ENTER') and ! kbd_isUp($keypress) ){
		$line=$w['wc']['string'];
		$w['wc']['string']="";

		wcf_terminal_drawLine($w);
		if(empty($line)){
			wcf_terminal_drawOutput($w);			
			return;
		}

		$w['wc']['lines'][]=['color'=>rgb(0,160,0),'text'=>$line];
		wcf_terminal_drawOutput($w);

		pr_STDIN($w['wc']['term'], $line."\n");
		return;
	}
}

function wcf_terminal_drawLine(&$w){
	img_fill($w['buffer'], 0,$w['buffer']['h']-15, $w['buffer']['w'],$w['buffer']['h'], rgb(0,48,0) );
	img_drawString($w['buffer'], 5, $w['buffer']['h']-15, 15,9999, rgb(0,100,0), substr($w['wc']['string'],-32) );
}

function wcf_terminal_drawOutput(&$w){
	img_fill($w['buffer'], 0,0, $w['buffer']['w'],$w['buffer']['h']-15, rgb(0,32,0) );

	$maxlines=($w['buffer']['h']-16)/16;
	while(count($w['wc']['lines'])>$maxlines)
		array_shift($w['wc']['lines']);

	$y=5;
	foreach( $w['wc']['lines'] as $line){
			img_drawString($w['buffer'], 5, $y, 15,9999, $line['color'], $line['text'] );
			$y+=16;
	}
}


?>
