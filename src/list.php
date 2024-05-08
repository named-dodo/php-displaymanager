<?php /* Manage a list of all the items. */

function list_create(){
	return [ 'first'=>false ];
}

function &list_findNode(&$list, &$item){
	$node=&$list['first'];
	while($node){
		if($node['item']===$item){
			return ['list'=>&$list, 'current'=>&$node ];
		}
		$node=&$node['next'];
	}
	$false=false;
	return $false;
}


function list_add(&$list, &$item){
	if(!( isset($item) and $item )) return;
	$node = [ 'item'=>&$item, 'list'=>&$list, 'prev'=>false, 'next'=>false ];
	if( $list['first'] ){
		$list['first']['prev']=&$node;
		$node['next']=&$list['first'];
	}
	$list['first']=&$node;
}

function list_iterator(&$list){
	return ['list'=>&$list, 'current'=>false ];
}

function &list_next(&$iterator){
	$false=false;
	if($iterator['current']===false){
		$iterator['current']=&$iterator['list']['first'];
		if(!$iterator['current']) return $false;
		return $iterator['current']['item'];
	}

	if(! $iterator['current']['next'] ) return $false;
	$iterator['current']=&$iterator['current']['next'];
	return $iterator['current']['item'];
}

function &list_prev(&$iterator){
	$false=false;
	if($iterator['current']===false){
		$last=&$iterator['list']['first'];
		while( $last and $last['next'] ) $last=&$last['next'];
		if(! $last) return $false;
		$iterator['current']=&$last;
		return $iterator['current']['item'];
	}

	if(! $iterator['current']['prev'] ) return $false;
	$iterator['current']=&$iterator['current']['prev'];
	return $iterator['current']['item'];
}

function list_remove(&$iterator){
	$false=false;
	$node=&$iterator['current'];
	if( $node['prev'] and $node['next'] ){
		$node['prev']['next']=&$node['next'];
		$node['next']['prev']=&$node['prev'];
	} elseif( $node['prev'] ){
		$node['prev']['next']=&$false;
	} elseif( $node['next'] ){
		$node['next']['prev']=&$false;
		$node['list']['first']=&$node['next'];
	} else {
		$node['list']['first']=&$false;
	}
	unset($node['list']);
}

// move this element to the front of the list. returns false if it already was.
function list_raise(&$iterator){
	$false=false;
	$node=&$iterator['current'];
	if($node['list']['first']===$node) return false;

	//maintain iterator position.
	$temp=[ 'item'=>&$node['item'], 'list'=>&$list, 'prev'=>&$node['prev'], 'next'=>&$node['next'] ];;
	$iterator['current']=&$temp;

	if( $node['prev'] and $node['next'] ){
		$node['prev']['next']=&$node['next'];
		$node['next']['prev']=&$node['prev'];
	} elseif( $node['prev'] ){
		$node['prev']['next']=&$false;
	}

	if( $node['list']['first'] ){
		$node['next']=&$node['list']['first'];
		$node['list']['first']['prev']=&$node;
	}
	$node['prev']=&$false;
	$node['list']['first']=&$node;

	return true;
}



?>
