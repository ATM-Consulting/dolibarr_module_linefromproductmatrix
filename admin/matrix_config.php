<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *    \file       matrix_config.php
 *    \ingroup    linesfromproductmatrix
 *    \brief      Page to create/edit/view bloc
 */


// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"]))
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php"))
	$res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php"))
	$res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php"))
	$res = @include "../main.inc.php";
if (!$res && file_exists("../../main.inc.php"))
	$res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php"))
	$res = @include "../../../main.inc.php";
if (!$res)
	die("Include of main fails");


dol_include_once('/linesfromproductmatrix/class/bloc.class.php');
dol_include_once('/linesfromproductmatrix/lib/linesfromproductmatrix_bloc.lib.php');
dol_include_once('/linesfromproductmatrix/class/blochead.class.php');


// Load translation files required by the page
$langs->loadLangs(array("linesfromproductmatrix@linesfromproductmatrix", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'bloccard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');

// Initialize technical objects
$object = new Bloc($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->linesfromproductmatrix->dir_output . '/temp/massgeneration/' . $user->id;
$hookmanager->initHooks(array('lfpmconfig')); // Note that conf->hooks_modules contains array



if (empty($action) && empty($id) && empty($ref))
	$action = 'view';

// Load object
include DOL_DOCUMENT_ROOT . '/core/actions_fetchobject.inc.php'; // Must be include, not include_once.

$permissiontoadd = $user->rights->linesfromproductmatrix->bloc->write;
$permissiontoread = $user->rights->linesfromproductmatrix->bloc->read;
$permissiontodelete = $user->rights->linesfromproductmatrix->bloc->delete;
$upload_dir = $conf->linesfromproductmatrix->multidir_output[isset($object->entity) ? $object->entity : 1];


if (!$permissiontoread)
	accessforbidden();

/*
 * Actions
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessage($object->error, "errors");
};

if (empty($reshook)) {
	$error = 0;
}


/*
 * View
 *
 * Put here all code to build page
 */
$title = $langs->trans("LinesFromProductMatrixArea");
$help_url = '';
// print load_fiche_titre pour afficher le titre du contenu de la page courante
llxHeader('', $title, $help_url);


$btnAdd = '<a class="btnTitle btnTitlePlus" style="background-color:white" href="">
		<span class="fa fa-plus-circle valignmiddle btnTitle-icon"></span>
		<span class="valignmiddle text-plus-circle btnTitle-label hideonsmartphone">Créer un bloc</span>
		</a>';

print load_fiche_titre($langs->trans("LinesFromProductMatrixArea"), $btnAdd, 'object_linesfromproductmatrix@linesfromproductmatrix');


print '<fieldset id="add-block-wrapper" style="display:none;">
			<legend>'.$langs->trans('AddAMatrixBlock').'</legend>
			<div class="label-form">
				<input placeholder="Titre du bloc" type="text" id="inputPlaceholderEx" class="form-control">
				<button id="add-block-btn" type="submit" class="button --create-button" >'.$langs->trans("Validate").'</button>
				<button id="add-block-cancel-btn" type="submit" class="button --cancel-button" >'.$langs->trans("Cancel").'</button>
			</div>
  		</fieldset>';



$bloc = new Bloc($db);
$blocs =  $bloc->fetchAll('ASC','fk_rank');

print '<div class="matrix-wrap">
			<div class="matrix-container">';
if ($blocs) {
	foreach ($blocs as $b){
		print $bloc->displayBloc($b, false, 'config');
	}

}
print '</div>
	</div>';



if (!empty($conf->use_javascript_ajax)) {
	include DOL_DOCUMENT_ROOT . '/core/tpl/ajaxrow.tpl.php';
}


// End of page
llxFooter();
$db->close();
