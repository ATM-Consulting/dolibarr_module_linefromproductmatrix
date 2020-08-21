<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2020 SuperAdmin
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    linesfromproductmatrix/admin/setup.php
 * \ingroup linesfromproductmatrix
 * \brief   LinesFromProductMatrix setup page.
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) { $i--; $j--; }
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) $res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php";
require_once '../lib/linesfromproductmatrix.lib.php';
//require_once "../class/myclass.class.php";

// Translations
$langs->loadLangs(array("admin", "linesfromproductmatrix@linesfromproductmatrix"));

// Access control
if (!$user->admin) accessforbidden();

// Parameters
$action = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');

$value = GETPOST('value', 'alpha');

$error = 0;
$setupnotempty = 0;


/*
 * Actions
 */

if ((float) DOL_VERSION >= 6)
{
	include DOL_DOCUMENT_ROOT.'/core/actions_setmoduleoptions.inc.php';
}



/*
 * View
 */

$form = new Form($db);

$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);

$page_name = "LinesFromProductMatrixSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'object_linesfromproductmatrix@linesfromproductmatrix');

// Configuration header
$head = linesfromproductmatrixAdminPrepareHead();
dol_fiche_head($head, 'settings', '', -1, "linesfromproductmatrix@linesfromproductmatrix");

// Setup page goes here
echo '<span class="opacitymedium">'.$langs->trans("LinesFromProductMatrixSetupPage").'</span><br><br>';




print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';


print '<div class="div-table-responsive-no-min">'; // You can use div-table-responsive-no-min if you dont need reserved height for your table
print '<table class="noborder centpercent">';

print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameter").'</td>';
print '<td align="center" width="60">'.$langs->trans("Value").'</td>';
print '<td width="80">&nbsp;</td>';
print "</tr>\n";

$metas = array(
	'type' => 'number',
	'step' => '1',
	'min' => 0,
	'max' => 100,
	'placeholder' => '0'
);
_printInputFormPart('PLM_MAX_BLOC', $langs->trans('PLM_MAX_BLOC'), '', $metas);

$metas = array(
	'type' => 'number',
	'step' => '1',
	'min' => 0,
	'max' => 100,
	'placeholder' => '0'
);
_printInputFormPart('PLM_MAX_COL', $langs->trans('PLM_MAX_COL'), '', $metas);


print '</table>';
print '</div>';

print '<br>';

_updateBtn();

print '</form>';




// Page end
dol_fiche_end();

llxFooter();
$db->close();



/**
 * Print an update button
 *
 * @return void
 */
function _updateBtn()
{
	global $langs;
	print '<div class="center">';
	print '<input type="submit" class="button" value="'.$langs->trans("Save").'">';
	print '</div>';
}

/**
 * Print a On/Off button
 *
 * @param string $confkey the conf key
 * @param bool $title Title of conf
 * @param string $desc Description
 *
 * @param string $help
 * @return void
 */
function _printOnOff($confkey, $title = false, $desc = '', $help = '')
{
	global $langs, $db;
	$form=new Form($db);

	print '<tr class="oddeven">';
	print '<td>';

	if(empty($help) && !empty($langs->tab_translate[$confkey . '_HELP'])){
		$help = $confkey . '_HELP';
	}

	if (!empty($help)) {
		print $form->textwithtooltip(($title?$title:$langs->trans($confkey)), $langs->trans($help), 2, 1, img_help(1, ''));
	} else {
		print $title?$title:$langs->trans($confkey);
	}


	if (!empty($desc)) {
		print '<br><small>'.$langs->trans($desc).'</small>';
	}
	print '</td>';
	print '<td class="center" width="20">&nbsp;</td>';
	print '<td class="right" width="300">';
	print ajax_constantonoff($confkey);
	print '</td></tr>';
}


/**
 * Print a form part
 *
 * @param string $confkey the conf key
 * @param bool   $title   Title of conf
 * @param string $desc    Description of
 * @param array  $metas   html meta
 * @param string $type    type of input textarea or input
 * @param bool   $help    help description
 *
 * @return void
 */
function _printInputFormPart($confkey, $title = false, $desc = '', $metas = array(), $type = 'input', $help = false)
{
	global $langs, $conf, $db, $inputCount;

	$inputCount = empty($inputCount)?1:($inputCount+1);
	$form=new Form($db);

	$defaultMetas = array(
		'name' => 'value'.$inputCount
	);

	if ($type!='textarea') {
		$defaultMetas['type']   = 'text';
		$defaultMetas['value']  = $conf->global->{$confkey};
	}


	$metas = array_merge($defaultMetas, $metas);
	$metascompil = '';
	foreach ($metas as $key => $values) {
		$metascompil .= ' '.$key.'="'.$values.'" ';
	}

	print '<tr class="oddeven">';
	print '<td>';

	if(empty($help) && !empty($langs->tab_translate[$confkey . '_HELP'])){
		$help = $confkey . '_HELP';
	}

	if (!empty($help)) {
		print $form->textwithtooltip(($title?$title:$langs->trans($confkey)), $langs->trans($help), 2, 1, img_help(1, ''));
	} else {
		print $title?$title:$langs->trans($confkey);
	}

	if (!empty($desc)) {
		print '<br><small>'.$langs->trans($desc).'</small>';
	}

	print '</td>';
	print '<td class="center" width="20">&nbsp;</td>';
	print '<td class="right" width="300">';
	print '<input type="hidden" name="param'.$inputCount.'" value="'.$confkey.'">';

	print '<input type="hidden" name="action" value="setModuleOptions">';
	if ($type=='textarea') {
		print '<textarea '.$metascompil.'  >'.dol_htmlentities($conf->global->{$confkey}).'</textarea>';
	} else {
		print '<input '.$metascompil.'  />';
	}
	print '</td></tr>';
}
