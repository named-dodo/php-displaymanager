<?php /* Entrypoint of the PHP displaymanager. */

/* Do not forget to adjust these settings below... */
$maxw = 1920 ; // <-- Set the width  of your monitor in pixels here.
$maxh = 1080 ; // <-- Set the heigth of your monitor in pixels here.



$framebuffer = "/dev/fb0" ; // <-- the framebuffer device for your monitor.
$micedevicename="/dev/input/mice"; // <-- the input device for your mouse or mice.
$X11SocketAddress="127.0.0.1:6001"; // <-- the TCP port to listen for incoming X11 connections.
$vendor_name="The X11.PHP Project.";

ini_set('memory_limit', 1024*2 .'M'); // increase memory max to 2G

/* Start loading everything */
include 'utils.php';
include 'dodofont.php';
include 'cursordata.php';
include 'image.php';
include 'framebuffer.php';
include 'mouse.php';
include 'keyboard.php';
include 'window.php';
include 'windowlist.php';
include 'X11server.php';


disable_terminal();
$bg=createFrameBuffer( color(10,10,20));
$mice = openMouse($maxw, $maxh, $micedevicename);

// TODO disabled incomplete Xorg server implementation
//$x11s = X11_init($X11SocketAddress);


$moving=null;
$resizing=null;
$bordercolor=color(100,100,100);
$headercolor=color(120,120,120);

addWindow( Wcreate(200, 150, 200, 150, "My Window") );
addWindow( Wcreate(400, 300, 150, 150, "Another Window") );




while(!isButtonPressed($mice,3)){
	$time = microtime(true);
	usleep(0.01 *1000000);

	readMouseInput($mice);

// TODO disabled incomplete Xorg server implementation
//	if(!isset($sock) || !$sock) $sock=X11_accept($x11s);
//	if(isset($sock) && $sock) X11_read($sock);

	$buff=$bg;

	// start button and taskbar
	$taskbar_h=25;
	fill($buff,0,$maxh-$taskbar_h,$taskbar_h,$maxh,color(100,100,100));
	fill($buff,$taskbar_h,$maxh-$taskbar_h,$maxw,$maxh,color(50,50,50));

	// draw windows.
	$iter=createIterator(false);
	while( $window=&nextWindow($iter) ){
		WfullDraw($window,$buff);
	}
	unset($window);

	if($moving){
		$moving['window']["x"]=getMouseX($mice)+$moving['xoff'];
		$moving['window']["y"]=getMouseY($mice)+$moving['yoff'];
	}


	if($resizing){
		$win=&$resizing['window'];

		$wx=WgetX($win);
		$wy=WgetY($win);
		$ww=WgetW($win);
		$wh=WgetH($win);

		if($resizing['movement']['x']){
			$wx=getMouseX($mice)-$resizing['xoff'];
			$ww=$resizing['wa']-getMouseX($mice);
		} else if($resizing['movement']['w'])
			$ww=getMouseX($mice)+$resizing['woff'];

		if($resizing['movement']['y']){
			$wy=getMouseY($mice)-$resizing['yoff'];
			$wh=$resizing['ha']-getMouseY($mice);
		} else if($resizing['movement']['h'])
			$wh=getMouseY($mice)+$resizing['hoff'];

		WsetPosition($win, $wx, $wy);
		WsetSize($win, $ww, $wh);

	}

	// pullClick returns an array("x"=>$mousex, "y"=>$mousey, "button"=>1, "press"=>$button1) or null
	while( $click = pullClick($mice) ){
		if($click["button"]!=1) continue; //ignore left and middle click for now.
		
		if($moving and !$click['press']){ // stopped moving?
			$moving=null;
			continue;
		}

		if($resizing and !$click['press']){ // stopped moving?
			$resizing=null;
			continue;
		}

		$handled=false;
		$mx=$click['x'];
		$my=$click['y'];		
		
		// which window was clicked?
		$window=null;
		$iter=createIterator(true);
		while( 	$window=&nextWindow($iter) ){
			// returns:  -3=minimize, -2=destroy, -1=outside, 0=inside, 1=titlebar/move, 2=unknown, 3=left, 4=right, 5=top, 6=topleft, 7=topright, 8=bottom, 9=bottomleft, 10=bottomright
			$result=Wclick($window,$click);

			if($result==-1){ continue; }
			$handled=true;

			if($result==-2){ deleteWindow($window); break; }
			if($result==-3){ break; }

			// move window to top if it ain't.
			raiseWindow($window);
			WfullDraw($window,$buff);

			if($result==0){ break; }
	
			if($result==1){ // start dragging
				$moving=array( 'window'=>&$window, 'xoff'=>-$mx+$window['x'], 'yoff'=>-$my+$window['y'] );
				break;
			}

			// handle resizing.
			if( $result>2 && $result<=10 ){ // start dragging
				$resizing=array( 'window'=>&$window, 'woff'=>-$mx+$window['w'], 'hoff'=>-$my+$window['h'],
					'xoff'=>-$mx+$window['x']+2*($mx-$window['x']), 'yoff'=>-$my+$window['y']+2*($my-$window['y']), 'wa'=>$window['w']+$mx, 'ha'=>$window['h']+$my,
					'movement'=>[ 'w'=>($result!=5 && $result!=8), 'h'=>($result!=3 && $result!=4), 'x'=>($result==3 || $result==6 || $result==9), 'y'=>($result==5 || $result==6 || $result==7) ] );
				break;
			}
			break;
		}
		unset($window);

		if($handled) continue;

		// start button pressed.
		if($click['press'] and $mx<25 and $my+25>$maxh){
			addWindow( Wcreate(rand(10,1200), rand(10,800), rand(50,900), rand(30,800), "Another Window") );
		}

	}

	// find hover icon.
	$cursor=0;
	$window=null;
	$iter=createIterator(true);
	while( $window=&nextWindow($iter) ){

		$result=Whover($window, getMouseX($mice), getMouseY($mice) );

		if($result==-1)	continue;
		$cursor=$result;
		break;
	}
	unset($window);

	// draw mouse
	$mcolor= ( (isButtonPressed($mice,1)) ? color(200,200,255) : color(100,100,255) );
	drawCursor($buff, getMouseX($mice), getMouseY($mice), $mcolor, $cursor);

	drawWindowsInfo($buff);
	drawString($buff, 10, 10, 15, 40, color(250,25,250) , "".(microtime(true)-$time) );

	blit($buff);
}

// --- RESTORE TERMINAL TO SAFE DEFAULTS ---
while( isButtonPressed($mice,3) ) readMouseInput($mice);
enable_terminal();
?>
