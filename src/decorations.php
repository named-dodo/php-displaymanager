<?php /* Manage window decorations */

$Wbordercolor=rgb(100,100,100);
$Wheadercolor=rgb(120,120,120);
//$Wbordercolor=rgb(24,100,100);
//$Wheadercolor=rgb(120,120,240);


function &deco_getCloseButton(){
	$img=img_create(18,18, rgb(120,0,0) );
	img_drawLine($img, 2, 2, 15, 15, rgb(255,50,50) );
	img_drawLine($img, 15, 2, 2, 15, rgb(255,50,50) );
	return $img;
}
function &deco_getMinifyButton(){
	$img=img_create(18,18, rgb(100,100,100) );
	img_drawLine($img, 2, 15, 15, 15, rgb(200,200,200) );
	return $img;
}


function deco_update(&$window){
	GLOBAL $Wbordercolor, $Wheadercolor;
	$w=$window['w']-4; // -4 for vertical borders
	$img=img_create($w,24, $Wheadercolor); // w=0..w-1, h=0..22

	img_drawhline($img,  0, 0, $w-1, $Wbordercolor );
	img_drawhline($img,  1, 0, $w-1, $Wbordercolor );
	img_drawhline($img, 22, 0, $w-1, $Wbordercolor );

	img_paint($img, $w-19,3, deco_getCloseButton());
	img_paint($img, $w-38,3, deco_getMinifyButton());

	// window title
	img_drawString($img, 5, 4, 12, $w-52, rgb(0,0,0) , $window['title']);

	$window['header']=&$img;
}

/*
Draw a select area (x1/y1 to x2/y2) of a window to a target image.
Offset the drawing location with xoff/yoff.
A full window draw would be WupdateSurface($w, $target, $w['x'],$w['y'],0,0,$w['w'],$w['h']);
*/
function deco_repaint(&$window, &$target,$xoff,$yoff,$x1,$y1,$x2,$y2){
	GLOBAL $Wbordercolor, $Wheadercolor;
	if( $window['minimized'] ) return;

	$w=$window['w'];
	$h=$window['h'];

	
	if($x1<=1 and $x2>=1) img_drawvline($target, $xoff+0,    $yoff+$y1, $yoff+$y2-1, $Wbordercolor ); // left border
	if($x1<=2 and $x2>=2) img_drawvline($target, $xoff+1,    $yoff+$y1, $yoff+$y2-1, $Wbordercolor );

  if($x1<=$w-1 and $x2>=$w-1)	img_drawvline($target, $xoff+$w-1, $yoff+$y1, $yoff+$y2-1, $Wbordercolor ); // right border
	if($x1<=$w-2 and $x2>=$w-2)	img_drawvline($target, $xoff+$w-2, $yoff+$y1, $yoff+$y2-1, $Wbordercolor );

	
	if($y1<=$h-1 and $y2>=$h-1)	img_drawhline($target, $yoff+$h-1, $xoff+$x1, $xoff+$x2-1, $Wbordercolor ); // bottom border
	if($y1<=$h-2 and $y2>=$h-2)	img_drawhline($target, $yoff+$h-2, $xoff+$x1, $xoff+$x2-1, $Wbordercolor );

	$boundX=img_findMinBounds($x1,$x2,2,$w-2);
	$boundY=img_findMinBounds($y1,$y2,0,23);
	if($boundX and $boundY){
		img_copy($target, $xoff+$x1, $yoff+$y1, $window['header'], $x1-2, $y1, $x2-2, $y2);
	}

}


?>
