<?php /* Basic process management. */

function pr_init($program){

	$descriptorspec = [ 0=>["pipe","r"], 1=>["pipe","w"], 2=>["pipe","w"] ];

	$proc = proc_open($program, $descriptorspec, $pipes);
	if(!$proc){
		echo("Failed opening $program!\n");
		return false;
	}

	stream_set_blocking($pipes[1], 0);
	stream_set_blocking($pipes[2], 0);

	return ['proc'=>&$proc, 'stdin'=>&$pipes[0], 'stdout'=>&$pipes[1],'stderr'=>&$pipes[2] ];
}	

function pr_STDIN(&$term, $line){
	if(! is_resource($term['stdin']) ) return;
	fwrite($term['stdin'], $line);
}
function pr_STDOUT(&$term){
	if(! is_resource($term['stdout']) ) return false;
	return fgets($term['stdout']);
}
function pr_STDERR(&$term){
	if(! is_resource($term['stderr']) ) return false;
	return fgets($term['stderr']);
}

function pr_isRunning(&$term){
	return is_resource($term['proc']);
}

function pr_close(&$term){
	if(is_resource($term['stdout'])) fclose($term['stdout']);
	if(is_resource($term['stderr'])) fclose($term['stderr']);
	if(is_resource($term['proc'])) proc_terminate($term['proc']);
}



?>
