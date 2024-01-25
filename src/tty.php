<?php /* TTY functions. */

// enter terminal into semi-framebuffer mode...
// escape sequences: https://gist.github.com/fnky/458719343aabd01cfb17a3a4f7296797
function tty_disable(){
	system("stty -echo raw"); // disable echo and input buffering.
	//echo("\033[0;0H"); // don't move to origin, it messes with the console on exit.
	echo("\033[?25l\033[8m"); // turn off cursor visibility and text visibility.
}

// reset terminal status.
function tty_enable(){
	system("stty echo -raw"); // enable echo and input buffering
	echo("\033[?25h\033[28m"); // enable cursor and text visibility.
}

// input is cleared to prevent any previous typing to be executed as commands.
function tty_flush(){
	$stdin = fopen( 'php://stdin', 'r' );
	socket_set_blocking($stdin,false);
	while( fgetc( $stdin ) );
	fclose( $stdin );
}

?>
