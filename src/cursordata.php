<?php /* Cursor image data for the PHP displaymanager. */

function getCursorData($n){
	$tl=[-1,-1]; $tm=[ 0,-1]; $tr=[ 1,-1];
	$ml=[-1, 0]; $mm=[ 0, 0]; $mr=[ 1, 0];
	$bl=[-1, 1]; $bm=[ 0, 1]; $br=[ 1, 1];
	switch($n){
		case 0: return [ [$ml,$mr],[$tm,$bm] ]; // normal cursor
		case 1: return [ [$ml,$mr],[$tm,$bm],[$tl,$tl],[$tr,$tr],[$bl,$bl],[$br,$br] ]; // click cursor
		case 2: return [ [$ml,$bl],[$tm,$mm],[$mr,$br] ]; // grab  cursor
		case 3: return [ [$tl,$bl],[$ml,$mr] ]; // resize left
		case 4: return [ [$tr,$br],[$ml,$mr] ]; // resize right
		case 5: return [ [$tl,$tr],[$tm,$bm] ]; // resize top
		case 6: return [ [$tl,$tr],[$tl,$bl] ]; // resize topleft
		case 7: return [ [$tl,$tr],[$tr,$br] ]; // resize topright
		case 8: return [ [$bl,$br],[$tm,$bm] ]; // resize bottom
		case 9: return [ [$bl,$br],[$tl,$bl] ]; // resize bottomleft
		case 10:return [ [$bl,$br],[$tr,$br] ]; // resize bottomright
		default:return [ [$tl,$tl],[$tr,$tr],[$bl,$bl],[$br,$br] ]; // :: for edge cases.
	}
}
?>
