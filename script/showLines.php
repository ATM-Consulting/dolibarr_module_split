<?php 

	require('../config.php');
	
	dol_include_once('/core/class/html.form.class.php');
	
	$element = GETPOST('element');
	$id = GETPOST('id');
	
	$langs->load('companies');
	
	$object = new $element($db);
	
	$object->fetch($id);
	
	?>
	<form name="splitform" id="splitform">
		<input type="hidden" name="element" value="<?php echo $element ?>" />
		<input type="hidden" name="id" value="<?php echo $id ?>" />
		<input type="hidden" name="action" value="split" />
	<?php
	
	echo $langs->trans("SelectThirdParty");
	
	$form=new Form($db);
	echo $form->select_company($object->fk_soc, 'socid', '(s.client=1 OR s.client=2 OR s.client=3)')
	
	?>	
	<table width="100%">
	<tr class="liste_titre nodrag nodrop">
		<td>Description</td>
		<td align="right" width="50">TVA</td>
		<td align="right" width="80">P.U. HT</td>
		<td align="right" width="50">Qté</td>
		<td align="right" width="50">Réduc.</td>
		<td align="right" width="50">Total HT</td>
		<td align="right" width="50">MoveThisLines</td>
		
	</tr>
	<?php
	
	$class='';
	
	foreach($object->lines as $k=>$line) {
		
		if($line->fk_product>0) {
			$prod=new Product($db);
			$prod->fetch($line->fk_product);
				
			$label = $prod->getNomUrl(1).' - '.$prod->label;
			if($line->desc) $label.=' - '.$line->desc	;
		}
		else{
			$label = $line->desc;
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
				<td align="center"><?php echo round($line->tva_tx,2) ?>%</td>
				<td align="right"><?php echo price($line->subprice,0,'',1,-1,-1,$conf->currency); ?></td>
				<td align="center"><?php echo $line->qty ?></td>
				<td align="center"><?php echo round($line->remise_percent,2) ?>%</td>
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
	
	
	