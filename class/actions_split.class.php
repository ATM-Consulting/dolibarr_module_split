<?php
class ActionsSplit
{ 
     /** Overloading the doActions function : replacing the parent's function with the one below 
      *  @param      parameters  meta datas of the hook (context, etc...) 
      *  @param      object             the object you want to process (an invoice if you are in invoice module, a propale in propale's module, etc...) 
      *  @param      action             current action (if set). Generally create or edit or null 
      *  @return       void 
      */
      
    function formObjectOptions($parameters, &$object, &$action, $hookmanager) 
    {  
      	global $langs,$db,$user, $conf, $mc;
		
		$langs->load('split@split');
		
		$contexts = explode(':',$parameters['context']);
		
		if(in_array('ordercard',$contexts) || in_array('propalcard',$contexts) || in_array('invoicecard',$contexts)) {
			
			if(GETPOST('actionSplitDelete') == 'ok') {
				setEventMessage($langs->trans('SplitDeleteOk'));
			}
			else if(GETPOST('actionSplit') == 'ok') {
				setEventMessage($langs->trans('SplitOk'));
			}
			else if(GETPOST('actionSplitCopy') == 'ok') {
				setEventMessage($langs->trans('SplitCopyOk'));
			}
			
        		
        	if ($object->statut == 0  && $user->rights->{$object->element}->creer) {
			
			
				if($object->element=='facture')$idvar = 'facid';
				else $idvar='id';
				
				if((float)DOL_VERSION >= 4.0) $fiche = 'propal/card.php';
				else $fiche = 'propal.php';
					    	
				?><script type="text/javascript">
					$(document).ready(function() {

                        var split_bt = $('<div class="inline-block divButAction"><a id="split_it" href="javascript:;" class="butAction"><?php echo  $langs->trans('SplitIt' )?></a></div>');

                        $('div.fiche div.tabsAction').append(split_bt);

                        split_bt.click(function() {
							$('#pop-split').remove();
							$('body').append('<div id="pop-split"></div>');
							
							$.get('<?php echo dol_buildpath('/split/script/showLines.php',1).'?id='.$object->id.'&element='.$object->element ?>', function(data) {
								$('#pop-split').html(data)
								
								$('#pop-split').dialog({
									title:'<?php echo $langs->transnoentities('SplitThisDocument') ?>'
									,width:'80%'
									,modal: true
									,buttons: [ 
										{ text: "<?php echo $langs->transnoentities('SimplyDelete'); ?>", click: function() { 
												
												$('#splitform input[name=action]').val('delete');
												
												$.post('<?php echo dol_buildpath('/split/script/splitLines.php',1) ?>', $('#splitform').serialize(), function() {
													
													document.location.href="<?php echo dol_buildpath('/comm/'.$fiche.'?id='.$object->id.'&actionSplitDelete=ok',1) ?>";
														
												});
												
												$( this ).dialog( "close" );
														
												 
											} 
										}
										,{ text: "<?php echo $langs->transnoentities('SimplyCopy'); ?>", title: "<?php echo $langs->transnoentities('SimplyCopyTitle'); ?>", click: function() { 
												
												$('#splitform input[name=action]').val('copy');
												
												$.post('<?php echo dol_buildpath('/split/script/splitLines.php',1) ?>', $('#splitform').serialize(), function() {
													
													document.location.href="<?php echo dol_buildpath('/comm/'.$fiche.'?id='.$object->id.'&actionSplitCopy=ok',1) ?>";
														
												});
												
												$( this ).dialog( "close" );
														
												 
											} 
										} 
										
										,{ text: "<?php echo $langs->transnoentities('SplitIt'); ?>", title: "<?php echo $langs->transnoentities('SplitItTitle'); ?>", click: function() { 
												
												$.post('<?php echo dol_buildpath('/split/script/splitLines.php',1) ?>', $('#splitform').serialize(), function() {
													
													document.location.href="<?php echo dol_buildpath('/comm/'.$fiche.'?id='.$object->id.'&actionSplit=ok',1) ?>";
														
												});
												
												$( this ).dialog( "close" );
														
												 
											} 
										}
										
									]
								});
							});
							
						});	
						
					});
					
				</script><?php
			}
		}

		return 0;
	}
}
