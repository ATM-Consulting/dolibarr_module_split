<?php 

	require('../config.php');
	
	require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
	dol_include_once('/split/lib/split.lib.php');
	
	$element = GETPOST('element');
	$id = GETPOST('id');
	
	$langs->load('split@split');
	$langs->load('companies');
	
	$object = new $element($db);
	
	$object->fetch($id);
	
	?>
	<form name="splitform" id="splitform">
		<input type="hidden" name="element" value="<?php echo $element ?>" />
		<input type="hidden" name="id" value="<?php echo $id ?>" />
		<input type="hidden" name="action" value="split" />
		<input type="hidden" name="modulefrom" value="splitpropal" />
	<?php
	
	echo $langs->trans("SelectThirdParty");
	
	$form=new Form($db);
	echo $form->select_company($object->socid, 'socid', '(s.client=1 OR s.client=2 OR s.client=3)');
	
	
	if(!empty($mc)) {
		echo ' - '. $langs->trans('EntityTo').' : '. $mc->select_entities($conf->entity, 'split_entity');	
	}
	
	echo ' - <b>'.$langs->trans('Or').'</b> - '. $langs->trans('PropalTarget').' : '.getHtmlSelectPropals($conf->entity, array($object->id));
			
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
				<td align="right"><?php echo price($line->subprice,0,'',1,-1,-1,$conf->currency); ?></td>
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
	<?php
	
	
