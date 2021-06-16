<?php
	if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL', '1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
	//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK', '1');				// Do not check style html tag into posted data
	if (! defined('NOREQUIREMENU')) define('NOREQUIREMENU', '1');				// If there is no need to load and show top and left menu
	if (! defined('NOREQUIREHTML')) define('NOREQUIREHTML', '1');				// If we don't need to load the html.form.class.php
	if (! defined('NOREQUIREAJAX')) define('NOREQUIREAJAX', '1');       	  	// Do not load ajax.lib.php library
	if (! defined('NOCSRFCHECK'))   define('NOCSRFCHECK', '1');				// Do not check CSRF attack (test on referer + on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on).
	//if (! defined("NOLOGIN"))                  define("NOLOGIN", '1');					// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
	//if (! defined('NOIPCHECK'))                define('NOIPCHECK', '1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
	//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT', 'auto');					// Force lang to a particular value
	//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE', 'aloginmodule');	// Force authentication handler
	//if (! defined("NOREDIRECTBYMAINTOLOGIN"))  define('NOREDIRECTBYMAINTOLOGIN', 1);		// The main.inc.php does not make a redirect if not logged, instead show simple error message
	//if (! defined("FORCECSP"))                 define('FORCECSP', 'none');				// Disable all Content Security Policies
	//if (! defined('CSRFCHECK_WITH_TOKEN'))     define('CSRFCHECK_WITH_TOKEN', '1');		// Force use of CSRF protection with tokens even for GET
	//if (! defined('NOBROWSERNOTIF'))     		 define('NOBROWSERNOTIF', '1');				// Disable browser notification

	require('../config.php');

	if($conf->operationorder->enabled) dol_include_once('/operationorder/class/operationorder.class.php');

	$json = new stdClass();
	$json->result = 0; // 0 nothing, 1 ok, -x errors
	$json->errorMessage = '';
	$json->msg ='';
	$json->log = array();
	$json->newToken = function_exists('newToken')?newToken():$_SESSION['newtoken'];

	$id = GETPOST('id', 'int');
	$element = GETPOST('element');
	$action = GETPOST('action');
	$entity = GETPOST('split_entity', 'int');
	$TMoveLine = GETPOST('TMoveLine', 'array');
	if(empty($TMoveLine)){
		$json->result = -1; // 0 nothing, 1 ok, -x errors
		$json->errorMessage = $langs->trans('EmptyTMoveLine');
		print json_encode($json);
		exit;
	}

    if($element == 'operationorder') $classname = 'OperationOrder';
    else $classname = $element;
	global $id_origin_line;
	$object = new $classname($db);
	$object->fetch($id);

	$old_object = new $element($db);
	$old_object->fetch($id);

	if(empty($entity))$entity=$conf->entity;



	// TODO : gérer les droits car apparement c'est pas du tout géré ...
//	if(empty($user->rights->xxxxx->write))
//	{
//		$json->result = -1;
//		$json->msg = $langs->transnoentities('InsufficientRights');
//	}



	if($action == 'split' || $action=='copy') {

		$json->result = 1; // vu que à la base il n'y a pas de gestion d'erreur sur ce script il faut partir du postula que c'est ok, perso ça me rends fou, mais on pourra le passer à -1 si erreurs plus tard... mais franchement c'est moche...

		$fk_target = GETPOST('fk_element_split', 'int');
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

				$id_origin_line = $line->id;
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

            if ($id_new > 0)
            {
				$json->newObjectId = $id_new;

				$json->log[] = "création $id_new";
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
						$json->log[] = "Suppresion ligne $k $lineid";
						if($object->element != 'operationorder' || ($object->element == 'operationorder' && !array_key_exists($lineid, $TNestedToKeep))) {
							$new_object->deleteline($lineid, $user);
						}
					}
					else{
						$json->log[] = "ok $k $lineid";
					}
				}
			}
		}

		if($entity!=$conf->entity) {
			$db->query("UPDATE ".MAIN_DB_PREFIX.$new_object->table_element." SET entity=".$entity." WHERE rowid=".$new_object->id );
		}

		$linkToDocument = 'ya pas ';
		if (!empty($new_object) && is_object($new_object) && method_exists($new_object, 'getNomUrl')){
			$linkToDocument = $new_object->getNomUrl();
		}

		if (!empty($linkToDocument)) $linkToDocument = '- '.$linkToDocument;

		if ($action == 'split') {
			// coté js il y a une redirection, mais avec CSRF CHECK l'ancienne redirection avec get ne marche plus
			$json->msg = $langs->trans('SplitOk'); // dû a un bug de l'espace avec les set event messages j'ai découpé mais il faut normalement utilisé $langs->trans('SplitOk', $linkToDocument)
			setEventMessage($langs->trans('SplitOk'));
			setEventMessage($linkToDocument);
		} elseif ($action == 'copy') {
			// coté js il y a une redirection, mais avec CSRF CHECK l'ancienne redirection avec get ne marche plus
			$json->msg = $langs->trans('SplitCopyOk'); // dû a un bug de l'espace avec les set event messages j'ai découpé mais il faut normalement utilisé $langs->trans('SplitCopyOk', $linkToDocument)
			setEventMessage($json->msg);
			setEventMessage($linkToDocument);
		}
	}

	if ($action == 'split' || $action == 'delete' )
	{
		$errors = 0;
		foreach($old_object->lines as $k=>$line) {
	         $lineid = empty($line->id) ? $line->rowid : $line->id;
	         if(isset($TMoveLine[$k])) {
	         	$json->log[] = "Suppresion ligne old $lineid";
	                 $resDel = $old_object->deleteline($lineid, $user);
	                 if($resDel<0){
						 $errors++;
					 }
	         }
	    }

		if(empty($errors) && $action == 'delete' ) {
			// coté js il y a une redirection, mais avec CSRF CHECK l'ancienne redirection avec get ne marche plus
			$json->msg = $langs->trans('SplitDeleteOk');
			setEventMessage($json->msg);
		}
	}

	print json_encode($json);
