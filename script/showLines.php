<?php
	if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', '1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
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

	require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
	require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';
	require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
	require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
	dol_include_once('/split/lib/split.lib.php');

	$element = GETPOST('element', 'alphanohtml');
	$id = GETPOST('id', 'int');

	$langs->load('split@split');
	$langs->load('companies');

	// TODO make it work with orders and invoices by using fetchObjectByElement function and fixing algo
	$classname = $element;
	$object = new $classname($db);

	$object->fetch($id);

	$token = function_exists('newToken')?newToken():$_SESSION['newtoken'];

	?>
	<form name="splitform" id="splitform">
		<input type="hidden" name="element" value="<?php echo $element ?>" />
		<input type="hidden" name="id" value="<?php echo $id ?>" />
		<input type="hidden" name="action" value="split" />
		<input type="hidden" name="modulefrom" value="splitpropal" />
		<input type="hidden" name="token" value="<?php echo $token ?>" />
	<?php

	echo $langs->trans("SelectThirdParty");

	$form=new Form($db);
	if ((float) DOL_VERSION < 18.0) {
		echo $form->select_company($object->socid, 'socid', '(s.client=1 OR s.client=2 OR s.client=3)');
	}else{
		echo $form->select_company($object->socid, 'socid', '( (s.client:=:1) OR (s.client:=:2)  OR (s.client:=:3) )');
	}


	if(!empty($mc)) {
		echo ' - '. $langs->trans('EntityTo').' : '. $mc->select_entities($conf->entity, 'split_entity');
	}

	echo ' - <b>'.$langs->trans('Or').'</b> - '. $langs->trans('Target').' : '.getHtmlSelectElements($conf->entity, array($object->id), $element);

	?>
	<br><br>
	<table width="100%" class="noborder">
	<tr class="liste_titre nodrag nodrop">
		<td>Description</td>
		<td align="right" width="50">TVA</td>
		<td align="right" width="80">P.U. HT</td>
		<td align="right" width="50">Qté</td>
		<td align="right" width="50">Réduc.</td>
		<td align="right" width="80">Total HT</td>
		<td align="center" width="50"><?php echo $langs->trans('MoveThisLines') ?></td>

	</tr>
	<?php

	$class='';
	foreach($object->lines as $k=>$line) {
		if($line->fk_product>0) {
			$prod=new Product($db);
			$prod->fetch($line->fk_product);

			$text = $prod->getNomUrl(1).' - '.$prod->label;
			$desc = dol_htmlentitiesbr($line->desc);
			$label = $form->textwithtooltip($text,$desc,3);
		}
		else{
			$label = !empty($line->desc) ? $line->desc : $line->label;
		}


		$lineid = empty($line->id) ? $line->rowid : $line->id;

		$class=($class=='impair') ? 'pair':'impair';

		if($line->product_type==9) {
			?>
			<tr class="<?php echo $class; ?>">
				<td colspan="6" style="font-weight: bold;"><?php echo $label ?></td>
				<td align="center"><input type="checkbox" name="TMoveLine[<?php echo $k; ?>]" value="<?php echo $lineid ?>" /></td>
			</tr>
			<?php

		}
		else{
			?>
			<tr class="<?php echo $class; ?>">
				<td><?php echo $label ?></td>
				<td align="right"><?php echo round($line->tva_tx,2) ?>%</td>
				<td align="right"><?php echo price(empty($line->subprice)?$line->price:$line->subprice,0,'',1,-1,-1,$conf->currency); ?></td>
				<td align="right"><?php echo $line->qty ?></td>
				<td align="right"><?php echo round($line->remise_percent,2) ?>%</td>
				<td align="right"><?php echo price($line->total_ht,0,'',1,-1,-1,$conf->currency); ?></td>
				<td align="center"><input type="checkbox" name="TMoveLine[<?php echo $k; ?>]" value="<?php echo $lineid ?>" /></td>
			</tr>
			<?php

		}



	}

	?>
	</table>
	</form>
