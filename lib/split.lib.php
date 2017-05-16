<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2013 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file		lib/split.lib.php
 *	\ingroup	split
 *	\brief		This file is an example module library
 *				Put some comments here
 */

function splitAdminPrepareHead()
{
    global $langs, $conf;

    $langs->load("split@split");

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/split/admin/split_setup.php", 1);
    $head[$h][1] = $langs->trans("Settings");
    $head[$h][2] = 'settings';
    $h++;
    $head[$h][0] = dol_buildpath("/split/admin/about.php", 1);
    $head[$h][1] = $langs->trans("About");
    $head[$h][2] = 'about';
    $h++;

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    //$this->tabs = array(
    //	'entity:+tabname:Title:@split:/split/mypage.php?id=__ID__'
    //); // to add new tab
    //$this->tabs = array(
    //	'entity:-tabname:Title:@split:/split/mypage.php?id=__ID__'
    //); // to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'split');

    return $head;
}


function getHtmlSelectPropals($entity, $TExcludeId=array())
{
	global $db,$form,$conf;
	
	require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';
	
	$TPropal = array(0 => '');
	
	$sql = 'SELECT p.rowid, p.ref, p.total_ht, s.nom, s.code_client, '.((float) DOL_VERSION >= 5.0 ? 'p.multicurrency_code' : "'$conf->currency'").' as currency_code FROM '.MAIN_DB_PREFIX.'propal p';
	$sql.= ' INNER JOIN '.MAIN_DB_PREFIX.'societe s ON (p.fk_soc = s.rowid)';
	$sql.= ' WHERE p.entity = '.$entity;
	$sql.= ' AND p.fk_statut = '.(property_exists('Propal', 'STATUS_DRAFT') ? Propal::STATUS_DRAFT : 0);
	if (!empty($TExcludeId)) $sql.= ' AND p.rowid NOT IN ('.implode(',', $TExcludeId).')';
	$sql.= ' ORDER BY p.ref';
	
	dol_syslog('Lib module SPLIT for action "getHtmlSelectPropals" launched by ' . __FILE__ . ' [SQL]= '.$sql, LOG_DEBUG);
	$resql = $db->query($sql);
	if ($resql)
	{
		while ($row = $db->fetch_object($resql))
		{
			$TPropal[$row->rowid] = $row->ref.' - '.price($row->total_ht, 0, $langs, 1, -1, -1, $row->currency_code).' - '.$row->nom.' ('.$row->code_client.')';
		}
	}
	else
	{
		dol_print_error($db);
	}
	
	return $form->selectarray('fk_propal_split', $TPropal, '', 0, 0, 0, '', 0, 0, 0, '', '', 1);
}