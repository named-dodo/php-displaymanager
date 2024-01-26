<?php /* image manipulation functions */

// rgba format for images. (Note: alpha doesn't seem to work?)
function rgb($r, $g, $b, $a=0){	return chr($b).chr($g).chr($r).chr($a);}

define("IMG_DEFAULT_COLOR", rgb(250,0,250) );


// create an image with $color for background.
function img_create($w, $h, $color){
	return array( 'w'=>$w, 'h'=>$h, 'd'=>str_repeat($color, $w*$h) );
}

function img_getW(&$img){ return $img['w']; }
function img_getH(&$img){ return $img['h']; }
function &img_getD(&$img){ return $img['d']; }
function img_copyD(&$img){ return $img['d']; }


function img_clear(&$img, $color){
	$img['d']=str_repeat($color , $img['w']*$img['h'] );
}


function img_setPixel(&$img, int $x, int $y, $color){
	$d=&$img['d'];
	$ptr=4*($x+$y*$img['w']);
	$d[$ptr+0]=$color[0];
	$d[$ptr+1]=$color[1];
	$d[$ptr+2]=$color[2];
	$d[$ptr+3]=$color[3];
}

// fill a selected area with color
function img_fill(&$img, int $x1, int $y1, int $x2, int $y2, $color){
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

// resize horizontally
function img_resize_w(&$img, int $nw, $color=IMG_DEFAULT_COLOR ){
	$ow=&$img['w'];

	if($ow==$nw) return;

	$lines=str_split($img['d'],4*$ow);
	$nb="";
	if($ow>$nw){
		$l=4*$nw;
		foreach($lines as $line)
			$nb.=substr($line,0,$l);
	}else{
		$l=str_repeat($color, $nw-$ow);
		foreach($lines as $line)
			$nb.=$line.$l;

	}
	$img['d']=$nb;
	$img['w']=$nw;
}

// resize vertically
function img_resize_h(&$img, int $nh, $color=IMG_DEFAULT_COLOR ){
	$oh=$img['h'];
	if($oh==$nh) return;
	if($nh<$oh)
		$img['d']=substr($img['d'],0,4*$img['w']*$nh);
	else
		$img['d']=$img['d'] . str_repeat($color , $img['w']*($nh-$oh) );
	$img['h']=$nh;
}

// resize image
function img_resize(&$img, int $nw, int $nh, $color=IMG_DEFAULT_COLOR){
	$ow=$img['w'];
	$oh=$img['h'];
	if( ($nw==$ow && $nh==$oh)||$nw<0||$nh<0 ) return;

	if($nw==$ow){ img_resize_h($img,$nh, $color); return; }
	if($nh==$oh){ img_resize_w($img,$nw, $color); return; }

	if($nh>$oh){
		img_resize_w($img,$nw, $color);
		img_resize_h($img,$nh, $color);
	}else{
		img_resize_h($img,$nh, $color);
		img_resize_w($img,$nw, $color);
	}
}

// fully draw an src image onto a dest image.
function img_paint(&$dest, int $x, int $y, &$src){
	$dout=&$dest['d'];
	$din =&$src['d'];

	$dw=$dest['w'];
	$dh=$dest['h'];
	$sw=$src['w'];
	$sh=$src['h'];
	if($sh<1 or $sw <1){ return; }

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

function img_drawhline(&$img, int $y, int $x1, int $x2, $color){
	$maxw=$img['w'];
	$maxh=$img['h'];

	if($y<0 or $y>=$maxh) return;

	if($x1>$x2){ $z=$x1; $x1=$x2; $x2=$z; }
	if($x1<0) $x1=0;
	if($x1>=$maxw) return;
	if($x2>=$maxw) $x2=$maxw-1;
	if($x2<0) return;

	for($x=$x1;$x<=$x2;$x++){
		img_setPixel($img, $x,$y,$color);
	}
}

function img_drawvline(&$img, int $x, int $y1, int $y2, $color){
	$maxw=$img['w'];
	$maxh=$img['h'];

	if($x<0 or $x>=$maxw) return;

	if($y1>$y2){ $z=$y1; $y1=$y2; $y2=$z; }
	if($y1<0) $y1=0;
	if($y1>=$maxh) return;
	if($y2>=$maxh) $y2=$maxh-1;
	if($y2<0) return;

	for($y=$y1;$y<=$y2;$y++){
		img_setPixel($img, $x,$y,$color);
	}
}

// draw a line between point 1 and 2.
function img_drawLine(&$img, $x1, $y1, $x2, $y2, $color){
	$maxw=$img['w'];
	$maxh=$img['h'];

	$rx=$x2-$x1;
	$ry=$y2-$y1;

	if($rx==0){
		img_drawvline($img, (int)$x1, (int)$y1, (int)$y2, $color);
		return;
	}
	if($ry==0){
		img_drawhline($img, (int)$y1, (int)$x1, (int)$x2, $color);
		return;
	}

	$steps=sqrt( ($rx*$rx) + ($ry*$ry) );
	if($steps<1){
		if($x1>=0 && $x1<$maxw && $y1>=0 && $y1<$maxh )
			img_setPixel($img, $x1, $y1, $color);
		return;
	}
	$rx/=$steps;
	$ry/=$steps;

	for($i=0;$i<$steps+0.01;$i+=1){
		$x=intval(round($x1+ $rx*$i));
		$y=intval(round($y1+ $ry*$i));

		if($x>=0 && $x<$maxw && $y>=0 && $y<$maxh )
			img_setPixel($img, $x, $y, $color);
	}
}

// draw string at x y with h height and mw as max width.
function img_drawString(&$img, $x, $y, $h, $mw, $color ,$text){
	$xoff=0;
	foreach(str_split(strtolower($text)) as $char){
		$lines=dodofont_getCharLines($char);

		foreach($lines as $line){
			$x1=intval(round($line[0]*$h/2));
			$y1=intval(round($line[1]*$h/2));
			$x2=intval(round($line[2]*$h/2));
			$y2=intval(round($line[3]*$h/2));

			img_drawLine($img, $x+$xoff+$x1, $y+$y1, $x+$xoff+$x2, $y+$y2, $color);
		}

		$xoff+=$h/2+2;
		if($xoff>$mw) return;
	}
}

// draw cursor at x y
//cursor= 0 if normal, 1 if clickable, 2 if grabbable, resize: 3=left, 4=right, 5=top, 6=topleft, 7=topright, 8=bottom, 9=bottomleft, 10=bottomright
function img_drawCursor(&$img, $x, $y, $color ,$cursor){
	$lines=getCursorData($cursor);
	$size=5;
	foreach($lines as $line){
		$x1=intval(round($line[0][0]*$size));
		$y1=intval(round($line[0][1]*$size));
		$x2=intval(round($line[1][0]*$size));
		$y2=intval(round($line[1][1]*$size));

		img_fill($img, $x-1+$x1 ,$y-1+$y1 ,$x+$x2 ,$y+$y2 ,$color);
	}
}

//TODO more image copying? (transparancy?)




?>
