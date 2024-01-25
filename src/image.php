<?php /* image manipulation functions */

// rgba format for images. (Note: alpha doesn't seem to work?)
function color($r, $g, $b, $a=0){	return chr($b).chr($g).chr($r).chr($a);}


// create an image with $color for background.
function createImage($w, $h, $color){
	return array( 'w'=>$w, 'h'=>$h, 'd'=>str_repeat($color, $w*$h) );
}

function getW(&$img){ return $img['w']; }
function getH(&$img){ return $img['h']; }
function &getD(&$img){ return $img['d']; }
function copyD(&$img){ return $img['d']; }


function clear(&$img, $color){
	$img['d']=str_repeat($color , $img['w']*$img['h'] );
}


function setpixel(&$img, $x, $y, $color){
	$d=&$img['d'];
	$ptr=4*($x+$y*$img['w']);
	$d[$ptr+0]=$color[0];
	$d[$ptr+1]=$color[1];
	$d[$ptr+2]=$color[2];
	$d[$ptr+3]=$color[3];
}

// fill a selected area with color
function fill(&$img, $x1, $y1, $x2, $y2, $color){
	$buff=&$img['d'];
	$maxw=$img['w'];
	$maxh=$img['h'];

	if($x1>$x2){ $z=$x1; $x1=$x2; $x2=$z; }
	if($y1>$y2){ $z=$y1; $y1=$y2; $y2=$z; }

	if($x1<0) $x1=0;
	if($x2>=$maxw) $x2=$maxw-1;
	if($y1<0) $y1=0;
	if($y2>=$maxh) $y2=$maxh-1;


	for($y=$y1;$y<=$y2;$y++){
		$offset=$y*$maxw;
		for($x=$x1;$x<=$x2;$x++){

			$ptr=4*($x+$offset);
			$buff[$ptr+0]=$color[0];
			$buff[$ptr+1]=$color[1];
			$buff[$ptr+2]=$color[2];
			$buff[$ptr+3]=$color[3];
		}
	}
}

function resize_w(&$img, $nw){
	$ow=&$img['w'];

	if($ow==$nw) return;

	$lines=str_split($img['d'],4*$ow);
	$nb="";
	if($ow>$nw){
		$l=4*$nw;
		foreach($lines as $line)
			$nb.=substr($line,0,$l);
	}else{
		$l=str_repeat(color(250,0,250) , $nw-$ow);
		foreach($lines as $line)
			$nb.=$line.$l;

	}
	$img['d']=$nb;
	$img['w']=$nw;
}

function resize_h(&$img, $nh){
	$oh=$img['h'];
	if($oh==$nh) return;
	if($nh<$oh)
		$img['d']=substr($img['d'],0,4*$img['w']*$nh);
	else
		$img['d']=$img['d'] . str_repeat(color(250,0,250) , $img['w']*($nh-$oh) );
	$img['h']=$nh;
}


function resize(&$img, $nw, $nh){
	$ow=$img['w'];
	$oh=$img['h'];
	if( ($nw==$ow && $nh==$oh)||$nw<0||$nh<0 ) return;

	if($nw==$ow){ resize_h($img,$nh); return; }
	if($nh==$oh){ resize_w($img,$nw); return; }

	if($nh>$oh){
		resize_w($img,$nw);
		resize_h($img,$nh);
	}else{
		resize_h($img,$nh);
		resize_w($img,$nw);
	}
}


function paint(&$dest, $x, $y, &$src){
	$dout=&$dest['d'];
	$din =&$src['d'];

	$dw=$dest['w'];
	$dh=$dest['h'];
	$sw=$src['w'];
	$sh=$src['h'];

	for($i=0;$i<$sh;$i++){
		if($i+$y<0) continue;
		if($i+$y>=$dh) return;

		for($j=0;$j<$sw;$j++){
			if($j+$x<0) continue;
			if($j+$x>=$dw) break;
			
			$srcindex=4*( $j+$i*$sw );
			$dstindex=4*( ($y+$i)*$dw +$x+$j );

			$dout[$dstindex+0]=$din[$srcindex+0];
			$dout[$dstindex+1]=$din[$srcindex+1];
			$dout[$dstindex+2]=$din[$srcindex+2];
			$dout[$dstindex+3]=$din[$srcindex+3];
		}
	}
}

function drawhline(&$img, $y, $x1, $x2, $color){
	$maxw=$img['w'];
	$maxh=$img['h'];

	if($x1>$x2){ $z=$x1; $x1=$x2; $x2=$z; }

	if($x1<0) $x1=0;
	if($x2>=$maxw) $x2=$maxw-1;
	if($y<0 or $y>=$maxh) return;

	for($x=$x1;$x<=$x2;$x++){
		setpixel($img, $x,$y,$color);
	}
}

function drawvline(&$img, $x, $y1, $y2, $color){
	$maxw=$img['w'];
	$maxh=$img['h'];

	if($y1>$y2){ $z=$y1; $y1=$y2; $y2=$z; }

	if($x<0 or $x>=$maxw) return;
	if($y1<0) $y1=0;
	if($y2>=$maxh) $y2=$maxh-1;

	for($y=$y1;$y<=$y2;$y++){
		setpixel($img, $x,$y,$color);
	}
}

// draw a line between point 1 and 2.
function drawLine(&$img, $x1, $y1, $x2, $y2, $color){
	$maxw=$img['w'];
	$maxh=$img['h'];

	$rx=$x2-$x1;
	$ry=$y2-$y1;

	$steps=sqrt( ($rx*$rx) + ($ry*$ry) );
	if($steps<1){
		if($x1>=0 && $x1<$maxw && $y1>=0 && $y1<$maxh )
			setpixel($img, $x1, $y1, $color);
		return;
	}
	$rx/=$steps;
	$ry/=$steps;

	for($i=0;$i<$steps+0.01;$i+=1){
		$x=intval(round($x1+ $rx*$i));
		$y=intval(round($y1+ $ry*$i));

		if($x>=0 && $x<$maxw && $y>=0 && $y<$maxh )
			setpixel($img, $x, $y, $color);
	}
}

// draw string at x y with h height and mw as max width.
function drawString(&$img, $x, $y, $h, $mw, $color ,$text){
	$xoff=0;
	foreach(str_split(strtolower($text)) as $char){
		$lines=dodofont_getCharLines($char);

		foreach($lines as $line){
			$x1=intval(round($line[0]*$h/2));
			$y1=intval(round($line[1]*$h/2));
			$x2=intval(round($line[2]*$h/2));
			$y2=intval(round($line[3]*$h/2));

			drawLine($img, $x+$xoff+$x1, $y+$y1, $x+$xoff+$x2, $y+$y2, $color);
		}

		$xoff+=$h/2+2;
		if($xoff>$mw) return;
	}
}

// draw cursor at x y
//cursor= 0 if normal, 1 if clickable, 2 if grabbable, resize: 3=left, 4=right, 5=top, 6=topleft, 7=topright, 8=bottom, 9=bottomleft, 10=bottomright
function drawCursor(&$img, $x, $y, $color ,$cursor){
	$lines=getCursorData($cursor);
	$size=5;
	foreach($lines as $line){
		$x1=intval(round($line[0][0]*$size));
		$y1=intval(round($line[0][1]*$size));
		$x2=intval(round($line[1][0]*$size));
		$y2=intval(round($line[1][1]*$size));

		fill($img, $x-1+$x1 ,$y-1+$y1 ,$x+$x2 ,$y+$y2 ,$color);
	}
}

//TODO more image copying? (transparancy?)




?>
