<?php /* TTY functions. */

// enter terminal into semi-framebuffer mode...
// (physical Linux Mint distro works, virtualbox ubuntu doesn't... needs investigation?)
// escape sequences: https://gist.github.com/fnky/458719343aabd01cfb17a3a4f7296797
function disable_terminal(){
	system("stty -echo"); // disable echo
	//echo("\033[0;0H"); // don't move to origin, it messes with the console on exit.
	echo("\033[?25l\033[8m"); // turn off cursor visibility and text visibility.
}

// reset terminal status and clear remaining input.
// input is cleared to prevent any previous typing to be executed as commands.
function enable_terminal(){
	system("stty echo"); // enable echo
	echo("\033[?25h\033[28m"); // enable cursor and text visibility.
	$stdin = fopen( 'php://stdin', 'r' ); // clear text buffer
	socket_set_blocking($stdin,false);
	while( fgets( $stdin ) );
	fclose( $stdin );
}

?>
