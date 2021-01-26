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

		if(in_array('ordercard',$contexts) || in_array('propalcard',$contexts) || in_array('invoicecard',$contexts)|| in_array('operationordercard',$contexts)) {

			if ($object->statut == 0 && ($user->rights->{$object->element}->creer || $user->rights->{$object->element}->write)) {
				$displayButton = true;
			} else {
				$displayButton = false;
			}

			if(GETPOST('actionSplitDelete') == 'ok') {
				setEventMessage($langs->trans('SplitDeleteOk'));
			}
			else if(GETPOST('actionSplit') == 'ok') {
			    $url = GETPOST('new_url');
			    if (!empty($url)) $url = '- '.$url;
                setEventMessage($langs->trans('SplitOk', $url));
			}
			else if(GETPOST('actionSplitCopy') == 'ok') {
                $url = GETPOST('new_url');
                if (!empty($url)) $url = '- '.$url;
                setEventMessage($langs->trans('SplitCopyOk', $url));
			}
			if($conf->operationorder->enabled && $object->element === 'operationorder') {
				dol_include_once('/operationorder/class/operationorderstatus.class.php');
                $statusLowerRang = new Operationorderstatus($db);
                $res = $statusLowerRang->fetchDefault(0, $conf->entity);
                if ($res<0) {
                	setEventMessage($statusLowerRang->error, 'errors');
				}
				$displayButton = $displayButton || ($statusLowerRang->code === $object->objStatus->code);
				if (!empty($conf->global->OPODER_STATUS_ON_CLONE)) {
					$statusFrom = new Operationorderstatus($db);
					$res = $statusFrom->fetch($conf->global->OPODER_STATUS_ON_CLONE);
					if ($res<0) {
						setEventMessage($statusFrom->error, 'errors');
					}
					$displayButton =  $displayButton || ($statusFrom->code == $object->objStatus->code);
				}
            }
        	if ($displayButton) {

				if($object->element=='facture')$idvar = 'facid';
				else $idvar='id';
                if($object->element == 'propal') {
                    if((float)DOL_VERSION >= 4.0) $fiche = '/comm/propal/card.php';
                    else $fiche = '/comm/propal.php';
                }
                else if($object->element == 'operationorder'){
                    $fiche = '/operationorder/operationorder_card.php';
                }
                else if($object->element == 'commande') {
                    if(floatval(DOL_VERSION) >= 3.7) $fiche = '/commande/card.php';
                    else $fiche = '/commande/fiche.php';
                }
                else if($object->element == 'facture') {
                    if(floatval(DOL_VERSION) >= 6.0) $fiche = '/compta/facture/card.php';
                    else $fiche = '/compta/facture.php';
                }

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

													document.location.href="<?php echo dol_buildpath($fiche.'?id='.$object->id.'&actionSplitDelete=ok',1) ?>";

												});

												$( this ).dialog( "close" );


											}
										}
										,{ text: "<?php echo $langs->transnoentities('SimplyCopy'); ?>", title: "<?php echo $langs->transnoentities('SimplyCopyTitle'); ?>", click: function() {

												$('#splitform input[name=action]').val('copy');

                                                $.ajax({
                                                    url: '<?php echo dol_buildpath('/split/script/splitLines.php', 1); ?>'
                                                    , method: 'POST'
                                                    , data: $('#splitform').serialize()
                                                    , dataType: 'html'
                                                }).done(function (url) {
                                                    document.location.href = "<?php echo dol_buildpath($fiche, 1).'?id='.$object->id; ?>&actionSplitCopy=ok&new_url=" + url;
                                                });

												$( this ).dialog( "close" );


											}
										}

										,{ text: "<?php echo $langs->transnoentities('SplitIt'); ?>", title: "<?php echo $langs->transnoentities('SplitItTitle'); ?>", click: function() {

                                                $.ajax({
                                                    url: '<?php echo dol_buildpath('/split/script/splitLines.php', 1); ?>'
                                                    , method: 'POST'
                                                    , data: $('#splitform').serialize()
                                                    , dataType: 'html'
                                                }).done(function (url) {
                                                    document.location.href = "<?php echo dol_buildpath($fiche, 1).'?id='.$object->id; ?>&actionSplit=ok&new_url=" + url;
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
