<?php

	require('../config.php');
	if($conf->operationorder->enabled) dol_include_once('/operationorder/class/operationorder.class.php');
	if(empty($_POST['TMoveLine'])) exit;
	
	$TMoveLine = GETPOST('TMoveLine');
	$element=GETPOST('element');
	$action = GETPOST('action');
    if($element == 'operationorder') $classname = 'OperationOrder';
    else $classname = $element;
	
	$object = new $classname($db);
	$object->fetch(GETPOST('id'));
	
	$old_object = new $element($db);
	$old_object->fetch(GETPOST('id'));
	
	$entity = GETPOST('split_entity');
	if(empty($entity))$entity=$conf->entity;
	
	if($action == 'split' || $action=='copy') {
		
		$fk_target = GETPOST('fk_element_split');
		if ($fk_target > 0)
		{

			$new_object = new $classname($db);
			$new_object->fetch($fk_target);

			// copie des coefs de la propal source si la propal de destination en est dépourvu
			if($conf->nomenclature->enabled && in_array($element, array('propal', 'commande'))) {
				dol_include_once('/nomenclature/class/nomenclature.class.php');
				$PDOdb = new TPDOdb;

				if (!empty($TMoveLine)) // on ne copie les coefs que si y a des lignes à copier...
				{
					$coef = new TNomenclatureCoefObject;
					$TCoef = $coef->loadCoefObject($PDOdb, $old_object, $element, $old_object->id);
					if (!empty($TCoef))
					{
						$sql = 'SELECT rowid FROM '.MAIN_DB_PREFIX.'nomenclature_coef_object
							WHERE fk_object = '.(int)$new_object->id.'
							AND type_object = "'.$element.'"';

						$PDOdb->Execute($sql);
						$TRes = $PDOdb->Get_All();

						if (empty($TRes)) // la nomenclature cible n'a pas de coef, on copie les coef de la source
						{
							foreach ($TCoef as $label => $coef)
							{
								$coef->fk_object = $new_object->id;
								$coef->{OBJETSTD_MASTERKEY} = 0; // le champ id est toujours def
								$coef->{OBJETSTD_DATECREATE}=time(); // ces champs dates aussi
								$coef->{OBJETSTD_DATEUPDATE}=time();
//								var_dump($new_object->id, $coef); exit;
								$coef->save($PDOdb);
							}
						}
					}
				}

			}

			foreach ($TMoveLine as $k => $line)
			{
				$line = $old_object->lines[$k];

				
                if($object->element == 'operationorder') {
                    $newLineId = $new_object->addline($line->desc, $line->qty, $line->price, $line->fk_warehouse, $line->pc, $line->time_planned, $line->time_spent, $line->fk_product, $line->info_bits, $line->date_start, $line->date_end, $line->type, $line->rang, $line->special_code, $line->fk_parent_line, $line->label, $line->array_options, $line->origin, $line->origin_id);
                    $new_object->recurciveAddChildLines($newLineId, $line->fk_product, $line->qty);
                }
                else $newLineId = $new_object->addline($line->desc, $line->subprice, $line->qty, $line->tva_tx, $line->localtax1_tx, $line->localtax2_tx, $line->fk_product, $line->remise_percent, 'HT', 0, 0, $line->product_type, -1, $line->special_code, 0, 0, $line->pa_ht, $line->label, $line->date_start, $line->date_end, $line->array_options, $line->fk_unit, '', 0, 0, $line->fk_remise_except); 
                    
				if($conf->nomenclature->enabled && in_array($element, array('propal', 'commande'))) {
				    // nomenclature de la ligne source
                    $n = new TNomenclature;
                    $n->loadByObjectId($PDOdb, $line->id, $element, true, $line->fk_product, $line->qty, $old_object->id);

                    if($n->rowid == 0 && (count($n->TNomenclatureDet) + count($n->TNomenclatureWorkstation)) > 0) {
                        // Le cas d'une nomenclature non chargée : ça ne sert à rien de copier la Nomenclature...
                        continue;
                    }

                    // copie de la nomenclature ligne vers la ligne de destination
                    $newN = new TNomenclature;
                    $newN->loadByObjectId($PDOdb, $line->id, $element, true, $line->fk_product, $line->qty, $old_object->id);
					$newN->reinit();
					$newN->object_type = $element;
					$newN->fk_object = $newLineId;

					$newN->save($PDOdb);

                }
			}
		}
		else
		{
            if($object->element == 'operationorder') $id_new = $object->cloneObject($user);
            else {
                if((float)DOL_VERSION >= 10.0) $id_new = $object->createFromClone($user, (int)GETPOST('socid'));
                else $id_new = $object->createFromClone((int)GETPOST('socid'));
            }
		//	print "création $id_new<br>";
			$new_object = new $classname($db);
			$new_object->fetch($id_new);
		//	var_dump($TMoveLine,$new_object->lines);
            if($object->element == 'operationorder') {
                $TNestedToKeep = array();
                foreach($new_object->lines as $k=>$line) {
                    if(isset($TMoveLine[$k])) {
                        $TNestedToKeep += $line->fetch_all_children_lines(0, true, true);
                        $TNestedToKeep[$line->id] = $line;
                    }
                }
            }
			foreach($new_object->lines as $k=>$line) {

				$lineid = empty($line->id) ? $line->rowid : $line->id;

				if(!isset($TMoveLine[$k])) {
		 //       	print "Suppresion ligne $k $lineid<br>";
                    if($object->element != 'operationorder' || ($object->element == 'operationorder' && !array_key_exists($lineid, $TNestedToKeep))) {
                        $new_object->deleteline($lineid, $user);
                    }
				}
				else{
		   //	 	print "ok $k $lineid<br>";
				}
			}
		}  		
		
		if($entity!=$conf->entity) {
			
			$db->query("UPDATE ".MAIN_DB_PREFIX.$new_object->table_element." SET entity=".$entity." WHERE rowid=".$new_object->id );
			
		}
		
	}
	
	
	if($action == 'split' || $action == 'delete' ) {	
		foreach($old_object->lines as $k=>$line) {
	                 
	         $lineid = empty($line->id) ? $line->rowid : $line->id;
	         if(isset($TMoveLine[$k])) {
//	         	print "Suppresion ligne old $lineid";
	                 $old_object->deleteline($lineid, $user);
	         }
	    }       
	}

	if ($action !== 'delete' && method_exists($new_object, 'getNomUrl'))
    {
        print urlencode($new_object->getNomUrl());
    }
