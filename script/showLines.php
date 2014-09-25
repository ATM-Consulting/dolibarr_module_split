<?php 

	require('../config.php');
	
	$element = GETPOST('element');
	$id = GETPOST('id');
	
	
	$object = new $element($db);
	
	$object->fetch($id);
	
	?>
	<form name="splitform" id="splitform">
		<input type="hidden" name="element" value="<?php echo $element ?>" />
		<input type="hidden" name="id" value="<?php echo $id ?>" />
		<input type="hidden" name="action" value="split" />
		
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
	
	foreach($object->lines as $line) {
		
		$prod=new Product($db);
		$prod->fetch($line->fk_product);
		
		?>
		<tr>
			<td><?php echo $prod->getNomUrl(1).' '.$line->desc ?></td>
			<td><?php echo $line->qty ?></td>
			<td><?php echo price($line->total_ht,0,'',1,-1,-1,$conf->currency); ?></td>
			<td><input type="checkbox" name="TMoveLine[<?php echo $line->id ?>]" value="1" /></td>
		</tr>
		<?php
		
		
	}
	
	?>
	</table>
	</form>
	<?php
	
	
	