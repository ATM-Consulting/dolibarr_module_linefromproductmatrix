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
 *    \file       bloc_card.php
 *        \ingroup    linesfromproductmatrix
 *        \brief      Page to create/edit/view bloc
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

require_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/html.formprojet.class.php';
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
//$lineid   = GETPOST('lineid', 'int');

// Initialize technical objects
$object = new Bloc($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->linesfromproductmatrix->dir_output . '/temp/massgeneration/' . $user->id;
$hookmanager->initHooks(array('bloccard', 'globalcard')); // Note that conf->hooks_modules contains array

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$search_all = trim(GETPOST("search_all", 'alpha'));
$search = array();
foreach ($object->fields as $key => $val) {
	if (GETPOST('search_' . $key, 'alpha'))
		$search[$key] = GETPOST('search_' . $key, 'alpha');
}

if (empty($action) && empty($id) && empty($ref))
	$action = 'view';

// Load object
include DOL_DOCUMENT_ROOT . '/core/actions_fetchobject.inc.php'; // Must be include, not include_once.

$permissiontoadd = $user->rights->linesfromproductmatrix->bloc->write;
$permissiontoread = $user->rights->linesfromproductmatrix->bloc->read;
$permissiontodelete = $user->rights->linesfromproductmatrix->bloc->delete;
$upload_dir = $conf->linesfromproductmatrix->multidir_output[isset($object->entity) ? $object->entity : 1];

// Security check - Protection if external user
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (($object->statut == $object::STATUS_DRAFT) ? 1 : 0);
//$result = restrictedArea($user, 'linesfromproductmatrix', $object->id, '', '', 'fk_soc', 'rowid', $isdraft);

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

	$backurlforlist = dol_buildpath('/linesfromproductmatrix/index.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel))
				$backtopage = $backurlforlist;
			else $backtopage = dol_buildpath('/linesfromproductmatrix/bloc_card.php', 1) . '?id=' . ($id > 0 ? $id : '__ID__');
		}
	}
	$triggermodname = 'LINESFROMPRODUCTMATRIX_BLOC_MODIFY'; // Name of trigger action code to execute when we modify record

	// Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen
	include DOL_DOCUMENT_ROOT . '/core/actions_addupdatedelete.inc.php';

	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT . '/core/actions_dellink.inc.php';

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT . '/core/actions_printing.inc.php';

	// Action to move up and down lines of object
	//include DOL_DOCUMENT_ROOT.'/core/actions_lineupdown.inc.php';

	// Action to build doc
	include DOL_DOCUMENT_ROOT . '/core/actions_builddoc.inc.php';

	if ($action == 'set_thirdparty' && $permissiontoadd) {
		$object->setValueFrom('fk_soc', GETPOST('fk_soc', 'int'), '', '', 'date', '', $user, 'BLOC_MODIFY');
	}
	if ($action == 'classin' && $permissiontoadd) {
		$object->setProject(GETPOST('projectid', 'int'));
	}

	// Actions to send emails
	$triggersendname = 'BLOC_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_BLOC_TO';
	$trackid = 'bloc' . $object->id;
	include DOL_DOCUMENT_ROOT . '/core/actions_sendmails.inc.php';
}


/*
 * View
 *
 * Put here all code to build page
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);

$title = $langs->trans("LinesFromProductMatrixArea");
$help_url = '';
// print load_fiche_titre pour afficher le titre du contenu de la page courante
llxHeader('', $title, $help_url);
print load_fiche_titre($langs->trans("LinesFromProductMatrixArea"),
	'<a class="btnTitle btnTitlePlus" style="background-color:white" href="">
		<span class="fa fa-plus-circle valignmiddle btnTitle-icon"></span>
		<span class="valignmiddle text-plus-circle btnTitle-label hideonsmartphone">Créer un bloc</span>
		</a>', 'linesfromproductmatrix.png@linesfromproductmatrix');

print '<div class="label-form" style="display:none;margin:12px 12px 20px 25px;">
  			<input placeholder="Titre du bloc" type="text" id="inputPlaceholderEx" class="form-control">
  			<button type="submit" class="create-button" style="margin-left: 10px;margin-right: 10px;padding: 5px 10px;border: 1px solid lightgrey;">Valider</button>
  			<button type="submit" class="cancel-button" style="margin-right: 10px;padding: 5px 10px;border: 1px solid lightgrey;">Annuler</button>
		</div>';



$bloc = new Bloc($db);
$blocs =  $bloc->fetchAll('ASC','fk_rank');

print '<div class="matrix-wrap">
			<div class="matrix-container">';
if ($blocs) {
	foreach ($blocs as $b){
		print $bloc->displayBloc($b);
	}

}
print '</div>
	</div>';



if (!empty($conf->use_javascript_ajax)) {
	include DOL_DOCUMENT_ROOT . '/core/tpl/ajaxrow.tpl.php';
}
?>

<!--Example : Adding jquery code-->
<script type="text/javascript" language="javascript"></script>

<?php
// End of page
llxFooter();
$db->close();
