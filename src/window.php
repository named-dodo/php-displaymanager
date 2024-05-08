<?php /* Manage a window */

function &Wcreate($x,$y,$w,$h,$title, $wc_name, $wc_data=null){
	$w = ['x'=>$x, 'y'=>$y, 'w'=>$w, 'h'=>$h, 'title'=>$title,
					'minimized'=>false, 'maximized'=>false, 'decorated'=>true,
					'buffer'=>img_create($w-4,$h-25,rgb(0,0,0)), 'wc_name'=>$wc_name  ];
	deco_update($w);

	// register functions for window client.
	foreach( ['init','destroy','tick','keypress','hover','click'] as $func_action){
		$function_name='wc_'.$func_action.'_'.$wc_name;
		if(!function_exists($function_name)){
			echo("Window Client function $function_name does not exist!");
			return false;
		}
		$w['wc_'.$func_action]=$function_name;
	}

	$w['wc']=$w['wc_init']($w, $wc_data);
	if(!$w['wc']) return false;

	return $w;
}


function Wdestroy(&$w){
	$w['wc_destroy']($w); //TODO proper destruction
}

function Wtick(&$w){
	$w['wc_tick']($w);
}

function Wpress(&$w,$keypress){
	$w['wc_keypress']($w, $keypress);
}


function WgetX(&$w){ return $w['x']; }
function WgetY(&$w){ return $w['y']; }

function WsetPosition(&$w,$x,$y){ $w['x']=$x; $w['y']=$y; }

function WgetW(&$w){ return $w['w']; }
function WgetH(&$w){ return $w['h']; }
function WgetRect(&$w){ return [0,0,$w['w'],$w['h']];}
function WsetSize(&$w,$nw,$nh){
	if($nw<40) $nw=40;
	if($nh<25) $nh=25;
	img_setSize($w['buffer'],$nw-4,$nh-25, rgb(0,0,0));
	$w['w']=$nw; $w['h']=$nh;
	deco_update($w);
}

function WsetMinimized(&$w,$bool){ $w['minimized']=$bool; }

function WMaximize(&$w,$x,$y,$wn,$nh){} //TODO later
function WUnMaximize(&$w){} //TODO later

function WsetDecorated(&$w,$bool){
	$w['decorated']=$bool;
	if($bool) deco_update($w);
	// TODO change everything for when the window is undecorated...
}


// $toff is x/y offset of target surface, $rect is relative to window origin.
function win_drawSection(&$window, &$target, $toff, $rect){
	if($window['minimized']) return;

	if($window['decorated']){
		deco_repaint($window, $target,$toff[0],$toff[1], $rect[0], $rect[1], $rect[2], $rect[3]);
		img_copy($target, $toff[0]+$rect[0], $toff[1]+$rect[1], $window['buffer'], $rect[0]-2, $rect[1]-23, $rect[2]-2, $rect[3]-23 );

	}else{
		img_copy($target, $toff[0]+$rect[0], $toff[1]+$rect[1], $window['buffer'], $rect[0], $rect[1], $rect[2], $rect[3] );
	}

}


// returns:  -3=minimize, -2=destroy, -1=outside, 0=inside, 1=titlebar/move, 2=unknown, 3=left, 4=right, 5=top, 6=topleft, 7=topright, 8=bottom, 9=bottomleft, 10=bottomright
function Wclick(&$w,&$click){
		$mx=$click['x'];
		$my=$click['y'];
		$wx=$w['x'];
		$wy=$w['y'];
		$ww=$w['w'];
		$wh=$w['h'];			

		if($w['minimized']) return -1; // return if minimized or outside resize borders.
		if( $mx<$wx-5 || $mx>$wx+$ww+5 || $my<$wy-5 || $my>$wy+$wh+5 ) return -1;

		// clicked inside application?
		if( $mx>$wx and $mx<$wx+$ww and $my>=$wy+20 and $my<$wy+$wh ){
			$passed_click=$click;
			$passed_click['x']=$passed_click['x']-1;
			$passed_click['y']=$passed_click['y']-20;
			$w['wc_click']($w, $passed_click);
			return 0;
		}

		// clicked somewhere, ignore everything except first button release.		
		if( $click["button"]!=1 ) return 0;

		// window title bar clicked
		if( ($mx>$wx and $mx<$wx+$ww and $my>$wy and $my<$wy+20) ){

			if($mx>$wx+$ww-20){
				if( $click['press'] ) return 0;
				Wdestroy($w);
				return -2;
			}
			if($mx>$wx+$ww-40){
				if( $click['press'] ) return 0;
				WsetMinimized($w, true);
				return -3;
			}

			return 1;
		}

	// rezising...
	$resize=0;
	if( $mx<=$wx+5 ) $resize+=1;
	if( $mx>=$wx+$ww-5 )$resize+=2;

	if( $my<=$wy+5 ) $resize+=3;
	if( $my>=$wy+$wh-5 )$resize+=6;

	return $resize+2; //2+ 0=unknown, 1=left, 2=right, 3=top, 4=topleft, 5=topright, 6=bottom, 7=bottomleft, 8=bottomright
}

// returns -1 if outside, 0 if normal, 1 if clickable, 2 if grabbable, resize: 3=left, 4=right, 5=top, 6=topleft, 7=topright, 8=bottom, 9=bottomleft, 10=bottomright
function Whover(&$w,$mx, $my){
		$wx=$w['x'];
		$wy=$w['y'];
		$ww=$w['w'];
		$wh=$w['h'];			

		if($w['minimized']) return -1; // return if minimized or outside resize borders.
		if( $mx<$wx-5 || $mx>$wx+$ww+5 || $my<$wy-5 || $my>$wy+$wh+5 ) return -1;

		// inside application content?
		if( $mx>$wx and $mx<$wx+$ww and $my>=$wy+20 and $my<$wy+$wh ){
			return $w['wc_hover']($w, $mx-1,$my-20);
		}

		// window title bar
		if( ($mx>$wx and $mx<$wx+$ww and $my>$wy and $my<$wy+20) ){

			if($mx>$wx+$ww-20) return 1;
			if($mx>$wx+$ww-40) return 1;

			return 2;
		}

	// rezising...
	$resize=2;
	if( $mx<=$wx+5 ) $resize+=1;
	if( $mx>=$wx+$ww-5 )$resize+=2;

	if( $my<=$wy+5 ) $resize+=3;
	if( $my>=$wy+$wh-5 )$resize+=6;

	return $resize; // 3=left, 4=right, 5=top, 6=topleft, 7=topright, 8=bottom, 9=bottomleft, 10=bottomright
}




function WtoString(&$w){
	$wmin=($w['minimized'])?'true':'false';
	return "title={$w['title']}, x={$w['x']}, y={$w['y']} ,w={$w['w']} ,h={$w['h']} ,min=$wmin";
}

?>
