<?php /* Compositing functions for surface updates */

function &com_create(&$windowlist, &$monitor_list){
	$com = ['windows'=>&$windowlist, 'screens'=>[] ];
	
	$x=0;
	$iter=list_iterator($monitor_list);
	while( $monitor=&list_next($iter) ){
		$update=[ 0,0,$monitor['w'],$monitor['h'] ];
		$buffer=img_create( $monitor['w'],$monitor['h'],rgb(255,0,255) );
		$background=img_clone($buffer);
		$position=[ 'x'=>$x,'y'=>0,'w'=>$monitor['w'],'h'=>$monitor['h'] ];
		$com['screens'][] = ['monitor'=>&$monitor, 'position'=>$position, 'updates'=>[ $update ], 'buffer'=>$buffer, 'background'=>$background, 'okay'=>true ];
		$x+=$monitor['w'];
	}
	unset($iter, $monitor);

	return $com;
}

function com_setBackground(&$com, &$background){
	foreach($com['screens'] as &$screen){
		$bg=img_clone($background);
		if(! ( img_getW($bg)==$screen['monitor']['w'] and img_getH($bg)==$screen['monitor']['h'] ) ){
			img_resize($bg, $screen['monitor']['w'], $screen['monitor']['h'] );
		}
		$screen['background']=&$bg;
	}
}

// request surface update for a specific rectangular area of a window.
function com_requestWUpdate(&$com, &$window, $rect){
	foreach($com['screens'] as &$screen){
		$wx=$window['x']-$screen['position']['x']+$rect[0];
		$wy=$window['y']-$screen['position']['y']+$rect[1];
		$wx2=$wx+$rect[2];
		$wy2=$wy+$rect[3];
		
		if( $wx2<0 or $wx>$screen['position']['w'] or $wy2<0 or $wy>$screen['position']['h'] ) continue;
		$screen['updates'][]=[ max($wx,0), max($wy,0), min($wx2,$screen['position']['w']), min($wy2,$screen['position']['h']) ];
	}
}

// request surface update for specific area of a monitor.
// rect is x,y,x2,y2 coordinate quad relative to monitor origin.
function com_requestMUpdate(&$com, &$monitor, $rect){
	foreach($com['screens'] as &$screen){
		if($screen !== $monitor) continue;
		$screen['updates'][]=$rect;
	}
}


// recursively split and copy rectangular areas to update.
// rect is x,y,x2,y2 coordinate quad relative to monitor origin.
function com_splitFragment(&$com, &$screen, $rect){
	$window=false;

	$frag_x=$rect[0];
	$frag_y=$rect[1];
	$frag_x2=$rect[2];
	$frag_y2=$rect[3];

	$iter=list_iterator($com['windows']);
	while( $w=&list_next($iter) ){
		if($w['minimized']) continue;
		$wx=$w['x']-$screen['position']['x'];
		$wy=$w['y']-$screen['position']['y'];
		$wx2=$wx+$w['w']; $wy2=$wy+$w['h'];
		if(!( ($frag_x>=$wx2 or $frag_x2<=$wx) or ($frag_y>=$wy2 or $frag_y2<=$wy)  )){
			$window=&$w;
			break;
		}
	}
	unset($iter, $w);	

	if(!$window){ // fragment does not touch any windows, fill with the wallpaper
		img_copy($screen['buffer'], $frag_x,$frag_y, $screen['background'], $frag_x,$frag_y, $frag_x2,$frag_y2);
		return;
	}

	$wx=$window['x']-$screen['position']['x'];
	$wy=$window['y']-$screen['position']['y'];
	$wx2=$wx+$window['w']; $wy2=$wy+$window['h'];

	// let the window draw it's portion.
	$area=[ max($frag_x,$wx)-$wx, max($frag_y,$wy)-$wy, min($frag_x2,$wx2)-$wx, min($frag_y2,$wy2)-$wy ];
	win_drawSection($window, $screen['buffer'], [$wx,$wy] , $area);
//img_drawLine($screen['buffer'], $wx,$wy, $wx2,$wy2, rgb(255,0,0) );
//img_drawLine($screen['buffer'], $wx,$wy2, $wx2,$wy, rgb(255,0,0) );

	if($frag_y<$wy) // top (x within window bounds)
		com_splitFragment($com, $screen, [ $wx, $frag_y ,$wx2, $wy ]);

	if($frag_y2>$wy2) // bottom (x within window bounds)
		com_splitFragment($com, $screen, [ $wx, $wy2 ,$wx2, $frag_y2 ]);

	if($frag_x<$wx) // left of window
		com_splitFragment($com, $screen, [ $frag_x,$frag_y,$wx,$frag_y2 ]);

	if($frag_x2>$wx2) // right of window
		com_splitFragment($com, $screen, [ $wx2,$frag_y,$frag_x2,$frag_y2 ]);
}


function com_update(&$com){
	foreach($com['screens'] as &$screen){
		foreach($screen['updates'] as $rect){
			com_splitFragment($com, $screen, $rect);
		}
		$screen['updates']=[];
	}
}

function com_drawCursor(&$com, $cursor_type, $color, $x,$y){
	foreach($com['screens'] as &$screen){
		$rx=$x-$screen['position']['x'];
		$ry=$y-$screen['position']['y'];
		
		if( $rx<-6 or $rx>$screen['position']['w']+6 or $ry<-6 or $ry>$screen['position']['h']+6 ) continue;
		img_drawCursor($screen['buffer'], $rx, $ry, $color, $cursor_type);
		com_requestMUpdate($com, $screen, [$rx-6,$ry-6,$rx+6,$ry+6]);	
	}
}

function com_blit(&$com){
	foreach($com['screens'] as &$screen){
		$okay=framebuffer_blit($screen['monitor'],$screen['buffer']);
		if(!$okay){ $screen['okay']=false; }
	}
}

?>
