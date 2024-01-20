<?php /* Manage a list of all the windows. */

$wlist_idcounter=0;
$wlist_windows=[];
$wlist_order=[];

function addWindow($w){
	GLOBAL $wlist_idcounter, $wlist_windows, $wlist_order;
	$windowid=$wlist_idcounter++;

	$w['wid']=$windowid;
	$wlist_windows[$windowid]=&$w;
	$wlist_order[  $windowid]=$windowid;
}

function deleteWindow($w){
	GLOBAL $wlist_windows, $wlist_order;

	$windowid=$w['wid'];
	unset($wlist_windows[$windowid]);
	unset($wlist_order[$windowid]);
	
	WDestroy($w);
}

function raiseWindow($w){
	GLOBAL $wlist_idcounter, $wlist_order;
	$wlist_order[$w['wid']]=$wlist_idcounter++;
}

function createIterator($reverse=false){
	GLOBAL $wlist_idcounter, $wlist_order;

	if($reverse) $n=$wlist_idcounter+1; else $n=-1;
	return [ 'r'=>$reverse, 'n'=>$n ];
}

function &nextWindow(&$iterator){
	GLOBAL $wlist_windows, $wlist_order;
	$target=$iterator['n'];

	foreach($wlist_order as $k => $v ){
		if(!$iterator['r']){
			if($v>$target && (!isset($curr) || $v<$wlist_order[$curr]) ){ $curr=$k; }
		}else{
			if($v<$target && (!isset($curr) || $v>$wlist_order[$curr]) ){ $curr=$k; }
		}
	}

	$null=null;
	if(!isset($curr)) return $null;
	$iterator['n']=$wlist_order[$curr];
	return $wlist_windows[$curr];
}

?>
