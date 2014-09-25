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
      	global $langs,$db,$user, $conf;
		
		$langs->load('split@split');
		
		$contexts = explode(':',$parameters['context']);
		
		if(in_array('ordercard',$contexts) || in_array('propalcard',$contexts) || in_array('invoicecard',$contexts)) {
        		
        	if ($object->statut == 0  && $user->rights->{$object->element}->creer) {
			
			
				if($object->element=='facture')$idvar = 'facid';
				else $idvar='id';
				
				
				if($action=='split') {
					
					// Todo parse line and change propal id (direct sql ?) 
					
				}
				
				    	
				?><script type="text/javascript">
					$(document).ready(function() {
						
						$('div.fiche div.tabsAction').append('<div class="inline-block divButAction"><a id="split_it" href="javascript:;" class="butAction"><?php echo  $langs->trans('SplitIt' )?></a></div>');

						$('#split_it').click(function() {
							$('#pop-split').remove();
							$('body').append('<div id="pop-split"></div>');
							
							$.get('<?php echo dol_buildpath('/split/script/showLines.php',1).'?id='.$object->id.'&element='.$object->element ?>', function(data) {
								$('#pop-split').html(data)
								
								$('#pop-split').dialog({
									title:'<?php echo $langs->trans('SplitThisDocument') ?>'
									,width:'90%'
									,buttons: [ 
										{ text: "<?php echo $langs->trans('Split'); ?>", click: function() { 
												
												$.post('<?php echo dol_buildpath('/split/script/splitLines.php',1) ?>', $('#splitform').serialize(), function() {
													
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
