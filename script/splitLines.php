<?php

	require('../config.php');
	
	if(empty($_POST['TMoveLine'])) exit;
	
	$TMoveLine = GETPOST('TMoveLine');
	$element=GETPOST('element');
	
	$object = new $element($db);
	$object->fetch(GETPOST('id'));
	
	$id_new = $object->createFromClone();
	
	$new_object = new $element($db);
	$new_object->fetch($id_new);
	
	$old_object = new $element($db);
	$old_object->fetch(GETPOST('id'));
	
	

	
	foreach($old_object->lines as $line) {
		
		$lineid = empty($line->id) ? $line->rowid : $line->id;
		
		if(isset($TMoveLine[$lineid])) {
			$old_object->deleteline($lineid, $user);
			
			if($element=='propal') $new_object->addline($line->desc, $line->subprice,$line->qty,0,0,0,0,0,'HT',0,0,$line->product_type,-1);
		
		}
		
	}	
	