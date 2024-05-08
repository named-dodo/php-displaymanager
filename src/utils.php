<?php /* Some helper functions. */

function clamp($val, $min, $max){
	if($val<$min) return $min;
	if($val>$max) return $max;
	return $val;
}

// LITTLE ENDIAN conversion.
function chr1($num){ return chr($num); }
function chr2($num){ return chr($num).chr($num>>8); }
function chr4($num){ return chr($num>>0).chr($num>>8).chr($num>>16).chr($num>>24); }
function int1($arr, $offset=0){ return ord($arr[$offset]); }
function int2($arr, $offset=0){ return ord($arr[$offset]) | ord($arr[$offset+1])<<8 ; }
function int4($arr, $offset=0){ return ord($arr[$offset])<<0 | ord($arr[$offset+1])<<8 | ord($arr[$offset+2])<<16 | ord($arr[$offset+3])<<24; }

// signed int conversion
function sint($arr, $size=1, $offset=0){
	$n=0;
	if( ord($arr[$offset+$size-1])>127 ) $n=-1;
	for($i=$size-1;$i>=0;$i--)
		$n=$n<<8 | ord($arr[$offset+$i]);
	return $n;
}

function debug($message){
	
	echo($message."\r\n");
}

?>
