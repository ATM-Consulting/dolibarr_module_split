<?php

	require('../config.php');
	
	if(empty($_POST['TMoveLine'])) exit;
	
	$TMoveLine = GETPOST('TMoveLine');
	$element=GETPOST('element');
	$action = GETPOST('action');
	
	
	$object = new $element($db);
	$object->fetch(GETPOST('id'));
	
	$old_object = new $element($db);
	$old_object->fetch(GETPOST('id'));
	
	if($action == 'split' || $action=='copy') {
		$id_new = $object->createFromClone((int)GETPOST('socid'));
	//	print "création $id_new<br>";
		$new_object = new $element($db);
		$new_object->fetch($id_new);
	//	var_dump($TMoveLine,$new_object->lines);	
		
		foreach($new_object->lines as $k=>$line) {
	                 
	         $lineid = empty($line->id) ? $line->rowid : $line->id;
	         
	         if(!isset($TMoveLine[$k])) {
	  //       	print "Suppresion ligne $k $lineid<br>";
	                 $new_object->deleteline($lineid, $user);
	         }
			 else{
		//	 	print "ok $k $lineid<br>";
			 }
	    }       		
	}
	
	
	if($action == 'split' || $action == 'delete' ) {	
		foreach($old_object->lines as $k=>$line) {
	                 
	         $lineid = empty($line->id) ? $line->rowid : $line->id;
	         
	         if(isset($TMoveLine[$k])) {
	         	print "Suppresion ligne old $lineid";
	                 $old_object->deleteline($lineid, $user);
	         }
	    }       
	}