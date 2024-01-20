<?php /* process incoming X11 connections. */

// TODO EVERYTHING
// We have a connection with the user application, but that's all.
// it's just that there's a LOT of implementation work to get a basic protocol working...


// TODO fix functions. :-)
$x11s_clients=[];

// Initialize the X11 server socket
function X11_init($ip){
	if( !extension_loaded('sockets') )
		if( ! dl("sockets.so") ) exit("The sockets extension is not and could not be loaded.\n");

	if(!isset($ip)) exit('X11 server binding not provided!'."\n");
	
	$sock = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));
	if($sock==false) exit("Error creating X11 socket: ".socket_strerror(socket_last_error())."\n");

	if( !socket_bind($sock, explode(":",$ip)[0],explode(":",$ip)[1]) ) // ip:port format
		exit("Error binding X11 socket: ".socket_strerror(socket_last_error())."\n");

	if( !socket_listen($sock, 1024) )
		exit("Error listening on X11 socket: ".socket_strerror(socket_last_error())."\n");

	socket_set_nonblock($sock);

	return $sock;
}


function X11_accept($serversock){
	GLOBAL $x11s_clients, $vendor_name, $maxw, $maxh;

	$sock=socket_accept($serversock);
	if(!$sock) return false;

	$header=socket_read($sock, 4, PHP_BINARY_READ);
	$bom=ord($header[0]);
	$protmaj=ord($header[2]);
	$protmin=ord($header[3]);

	echo("accept: $protmaj:$protmin, order=$bom\n");
	$response="";
	$response.=chr1(1); // success
	$response.=chr1(0); //unused
	$response.=chr2(11); // major
	$response.=chr2(0); // minor
	$response.=chr2(28+(strlen($vendor_name)/4) ); //3075-12 -3038+8); // length? -12 ints for unused pixmaps, -3038 for screen data
	$response.=chr4(1234); // 
	$response.=chr4(0x06600000); // 
	$response.=chr4(0x001fffff); // 
	$response.=chr4(256); // motbuffsize
	$response.=chr2(strlen($vendor_name)); // 
	$response.=chr2(65535); // max req len
	$response.=chr1(1); // screen num
	$response.=chr1(1); // pixmap f num
	$response.=chr1(0);
	$response.=chr1(0);
	$response.=chr1(32);
	$response.=chr1(32);
	$response.=chr1(8); // min keycode
	$response.=chr1(255); // max keycode
	$response.=chr4(0); // unused (10th int)
	$response.=$vendor_name; // 20 bytes is 5 ints.

	$response.=chr1(32); // pixmap depth
	$response.=chr1(32); // bits-per-pixel
	$response.=chr1(32); // scanline-pad
	$response.=chr1(0); // unused
	$response.=chr4(0); // unused

	$response.=chr4(0x6b3); // root (screen 0)
	$response.=chr4(0x20); // default colormap
	$response.=chr4(0x00FFFFFF); // white pixel
	$response.=chr4(0); // black pixel
	$response.=chr4(0xfa8033); // input mask
	$response.=chr2($maxw); // w
	$response.=chr2($maxh); // h
	$response.=chr2(508*$maxw/1920); // w mm
	$response.=chr2(258*$maxh/1080); // h mm
	$response.=chr2(1); // min inst maps
	$response.=chr2(1); // max inst maps
	$response.=chr4(0x21); // root visual
	$response.=chr1(1); // backing stores
	$response.=chr1(0); // save unders
	$response.=chr1(24); // root depth
	$response.=chr1(1); // allowed depths len

	$response.=chr1(24); // depth
	$response.=chr1(0); // unused
	$response.=chr2(1); //vtype numbers
	$response.=chr4(0); // unused

	$response.=chr4(0x6c); //visid
	$response.=chr1(4); // class
	$response.=chr1(8); // bits per rgb value
	$response.=chr2(256); // colormap entries
	$response.=chr4(0x0000ff00); // red
	$response.=chr4(0x00ff0000); // green
	$response.=chr4(0xff000000); // blue
	$response.=chr4(0);



	echo("sizes=".strlen($response).", calculated:".(int2($response, 6)*4+8)."!\n");
	$result=socket_send($sock, $response, strlen($response), 0);
	if(!$result) echo("something went wrong while sending response...\n");

	socket_set_nonblock($sock);
	$x11s_clients[]=['s'=>$sock ];
	return $sock;
}


function X11_read($sock){
	$header=socket_read($sock, 4, PHP_BINARY_READ);
	if(!$header || strlen($header)<4 ) return false;

	$opcode=ord($header[0]);
	$size=4*int2($header,2)-4;

	if($size<0 || $opcode==0 || ord($header[1])==0 ){ echo("unexpected value in x11 packet!\n"); return false; }

	$body=socket_read($sock, $size, PHP_BINARY_READ);
	if(!$body || strlen($body)!=$size ) exit("body is of incorrect length! (".strlen($body)." should be $size)");

	echo("body=".bin2hex($body)."\n\n");


	return true;
}

?>
