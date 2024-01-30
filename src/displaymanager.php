<?php /* Entrypoint of the PHP displaymanager. */


$micedevicename="/dev/input/mice"; // <-- the input device for your mouse or mice.
$X11SocketAddress="127.0.0.1:6001"; // <-- the TCP port to listen for incoming X11 connections.
$vendor_name="The X11.PHP Project.";

ini_set('memory_limit', 1024*2 .'M'); // increase memory max to 2G

/* Start loading everything */
include 'tty.php';
include 'utils.php';
include 'dodofont.php';
include 'cursordata.php';
include 'image.php';
include 'framebuffer.php';
include 'mouse.php';
include 'keyboard.php';
include 'window.php';
include 'list.php';
include 'X11server.php';

include 'wc_empty.php';
include 'wc_terminal.php';


tty_disable();

// Getting fb1 working requires:
// - Having multiple graphic cards. (integrated one also counts)
// - Having a display connected to both card outputs.
// - In the BIOS/UEFI, keep the integrated GPU (output) always on.
// - X11/Wayland/distro needs to surrender control of both monitors when switching to TTY1.
//   - Disable in display settings the monitor that doesn't blank or show the TTY.
//   - Write a 0 to /sys/class/graphics/fb1/blank (each time after swithing from X11 to TTY)
$framebuffer=framebuffer_open("/dev/fb0");

$maxw=$framebuffer['w'];
$maxh=$framebuffer['h'];
// You can VNC virtual framebuffers with: "x11vnc -rawfb map:/dev/fb1@1024x720x32"


$keyboard_file=kbd_find();
$kbd=kbd_open($keyboard_file);

$mice = mouse_open($maxw, $maxh, $micedevicename);

// TODO disabled incomplete Xorg server implementation
//$x11s = X11_init($X11SocketAddress);

$wlist = list_create();

$moving=null;
$resizing=null;
$bordercolor=rgb(100,100,100);
$headercolor=rgb(120,120,120);


$background=false;
foreach( glob("./res/background_*.ppm") as $background_file ){
	$background = img_loadPPM($background_file);
	if( $background and img_getW($background)==$maxw and img_getH($background)==$maxh ) break;
}


if( $background and img_getW($background)==$maxw and img_getH($background)==$maxh ){
	img_dim($background, 1);
} else {
	echo("Failed loading ".$maxw."x".$maxh." wallpaper.\r\n");
	$background=img_create($maxw, $maxh, rgb(10,10,20));
}


$thing1=Wcreate(200, 150, 200, 150, "My Window", 'empty');
$thing2=Wcreate(450, 300, 150, 150, "Another Window", 'empty');
list_add($wlist, $thing1);
list_add($wlist, $thing2);
unset($thing1, $thing2);


while(!mouse_isPressed($mice,3)){
	$time = microtime(true);
	usleep(0.01 *1000000);

	mouse_read($mice);

// TODO disabled incomplete Xorg server implementation
//	if(!isset($sock) || !$sock) $sock=X11_accept($x11s);
//	if(isset($sock) && $sock) X11_read($sock);

	$buff=$background;

	// start button and taskbar
	$taskbar_h=25;
	img_fill($buff,0,$maxh-$taskbar_h,$maxw,$maxh,rgb(50,50,50));
	img_fill($buff, $taskbar_h*0	,$maxh-$taskbar_h, $taskbar_h*1 ,$maxh,rgb(100,100,100)); // random empty window

	img_fill($buff, $taskbar_h*1	,$maxh-$taskbar_h, $taskbar_h*2 ,$maxh,rgb(50,100,50)); // builtin terminal
	img_drawString($buff, $taskbar_h+10, $maxh-$taskbar_h, 22, 40, rgb(0,255,0) , "T" );


	// draw windows.
	$iter=list_iterator($wlist);
	while( $window=&list_prev($iter) ){
		WfullDraw($window,$buff);
	}
	unset($iter, $window);

	if($moving){
		$moving['window']["x"]=mouse_getX($mice)+$moving['xoff'];
		$moving['window']["y"]=mouse_getY($mice)+$moving['yoff'];
	}


	if($resizing){
		$win=&$resizing['window'];

		$wx=WgetX($win);
		$wy=WgetY($win);
		$ww=WgetW($win);
		$wh=WgetH($win);

		if($resizing['movement']['x']){
			$wx=mouse_getX($mice)-$resizing['xoff'];
			$ww=$resizing['wa']-mouse_getX($mice);
		} else if($resizing['movement']['w'])
			$ww=mouse_getX($mice)+$resizing['woff'];

		if($resizing['movement']['y']){
			$wy=mouse_getY($mice)-$resizing['yoff'];
			$wh=$resizing['ha']-mouse_getY($mice);
		} else if($resizing['movement']['h'])
			$wh=mouse_getY($mice)+$resizing['hoff'];

		WsetPosition($win, $wx, $wy);
		WsetSize($win, $ww, $wh);
	}

	// pullClick returns an array("x"=>$mousex, "y"=>$mousey, "button"=>1, "press"=>$button1) or null
	while( $click = mouse_pullClick($mice) ){
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
		$iter=list_iterator($wlist);
		while( $window=&list_next($iter) ){

			// returns:  -3=minimize, -2=destroy, -1=outside, 0=inside, 1=titlebar/move, 2=unknown, 3=left, 4=right, 5=top, 6=topleft, 7=topright, 8=bottom, 9=bottomleft, 10=bottomright
			$result=Wclick($window,$click);

			if($result==-1){ continue; }
			$handled=true;

			if($result==-2){
				list_remove($iter);
				Wdestroy($window);
				break;
			}
			if($result==-3){ break; }

			// move window to top if it ain't.
			list_raise($iter);
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
		unset($iter, $window);

		if($handled) continue;

		// start button pressed.
		if($click['press'] and $mx<25 and $my+25>$maxh){
			$colorwindow=Wcreate(rand(10,1200), rand(10,400), rand(50,700), rand(30,600), "Another Window ".rand(1000,9999), 'empty' );
			list_add($wlist, $colorwindow );
			unset($colorwindow);
		}

		// terminal button pressed.
		if($click['press'] and $mx>25 and $mx<50 and $my+25>$maxh){
			$termwindow=Wcreate(rand(100,500), rand(100,200), 500, 400, "Terminal window", 'terminal' );
			list_add($wlist, $termwindow );
			unset($termwindow);
		}

	}

	// find hover icon.
	$cursor=0;
	$iter=list_iterator($wlist);
	while( $window=&list_next($iter) ){
		$result=Whover($window, mouse_getX($mice), mouse_getY($mice) );

		if($result==-1) continue;
		$cursor=$result;
		break;
	}
	unset($iter,$window);


	// testing the keyboard.
	$iter=list_iterator($wlist);
	$window=&list_next($iter);
	while($keypress=kbd_read($kbd)){
		if($window) Wpress($window, $keypress);
	}
	unset($iter, $window);

	if( kbd_isPressed($kbd, kbd_getID("LEFTSHIFT")) )
			img_drawString($buff, 10, 100, 15, 400, rgb(250,25,250) , "You have pressed the left-shift key" );
	if( kbd_isPressed($kbd, kbd_getID("RIGHTSHIFT")) )
			img_drawString($buff, 10, 115, 15, 400, rgb(250,25,250) , "You have pressed the right-shift key" );


	// draw mouse
	$mcolor= ( (mouse_isPressed($mice,1)) ? rgb(200,200,255) : rgb(100,100,255) );
	img_drawCursor($buff, mouse_getX($mice), mouse_getY($mice), $mcolor, $cursor);

	if(mouse_isPressed($mice,2)) img_invertColors($buff);

	$i=2;
	$iter=list_iterator($wlist);
	while( $window=&list_next($iter) ){
		img_drawString($buff, 15, 15*$i++, 12, 800, rgb(250,250,250) , "> ".WtoString($window) );
	}
	unset($iter, $window);

	img_drawString($buff, 10, 10, 15, 40, rgb(250,25,250) , "".(microtime(true)-$time) );
	img_drawString($buff, 80, 10, 15, 400, rgb(250,25,250) , "Click the scroll wheel to exit." );

	framebuffer_blit($framebuffer, $buff);
}

// --- RESTORE TERMINAL TO SAFE DEFAULTS ---
while( mouse_isPressed($mice,3) ) mouse_read($mice);
tty_enable();
tty_flush();
?>
