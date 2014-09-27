<?php

	require('../config.php');
	
	if(empty($_POST['TMoveLine'])) exit;
	
	$TMoveLine = GETPOST('TMoveLine');
	$element=GETPOST('element');
	
	$object = new $element($db);
	$object->fetch(GETPOST('id'));
	
	$id_new = $object->createFromClone();
	
	$old_object = new $element($db);
	$old_object->fetch(GETPOST('id'));
	
	$new_object = new $element($db);
	$new_object->fetch($id_new);
	
	/*foreach ($TMoveLine as $lineid => $dummy) {
			if($element=='propal') {
				$line = new PropaleLigne;
				$line->fetch($lineid);
				
				$line->fk_propal = $id_new;
				$line->insert();
				
				$line = new PropaleLigne;
				$line->fetch($lineid);
				$line->delete();			
			}
	}*/
	
	
	foreach($new_object->lines as $line) {
                 
         $lineid = empty($line->id) ? $line->rowid : $line->id;
         
         if(!isset($TMoveLine[$lineid])) {
                 $new_object->deleteline($lineid, $user);
         }
    }       
		
	foreach($old_object->lines as $line) {
                 
         $lineid = empty($line->id) ? $line->rowid : $line->id;
         
         if(isset($TMoveLine[$lineid])) {
                 $old_object->deleteline($lineid, $user);
         }
    }       