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
 *    \file       tab_matrix.php
 *    \ingroup    linesfromproductmatrix
 *    \brief      Page to edit matrix qts on active object [ invoice / order / propal ]
 */

require_once ('config.dolibarr.inc.php');


dol_include_once('/linesfromproductmatrix/class/bloc.class.php');
dol_include_once('/linesfromproductmatrix/lib/linesfromproductmatrix_bloc.lib.php');
dol_include_once('/linesfromproductmatrix/class/blochead.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/order.lib.php';

if (!empty($conf->projet->enabled)) {
	require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
}

$element = GETPOST('element', 'alpha');
$callUserFunction = '';
if ($element == 'propal') {
	require_once DOL_DOCUMENT_ROOT.'/core/lib/propal.lib.php';
	$callUserFunction = $element.'_prepare_head';
}
if ($element == 'commande') {
	require_once DOL_DOCUMENT_ROOT.'/core/lib/order.lib.php';
	$callUserFunction = $element.'_prepare_head';
}
if ($element == 'facture') {
	require_once DOL_DOCUMENT_ROOT.'/core/lib/invoice.lib.php';
	$callUserFunction = $element.'_prepare_head';
}

// Load translation files required by the page
$langs->loadLangs(array("linesfromproductmatrix@linesfromproductmatrix", 'companies', 'bills', 'orders'));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'bloccard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');


// Initialize technical objects
$object = new Bloc($db);
$diroutputmassaction = $conf->linesfromproductmatrix->dir_output . '/temp/massgeneration/' . $user->id;
$hookmanager->initHooks(array('bloccard', 'globalcard')); // Note that conf->hooks_modules contains array


if (empty($action) && empty($id) && empty($ref))
	$action = 'view';

// Load object
include DOL_DOCUMENT_ROOT . '/core/actions_fetchobject.inc.php'; // Must be include, not include_once.

// permission
$permissiontoread = $user->rights->linesfromproductmatrix->bloc->read;


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

	$backurlforlist = dol_buildpath('/linesfromproductmatrix/matrix_config.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel))
				$backtopage = $backurlforlist;
			else $backtopage = dol_buildpath('/linesfromproductmatrix/bloc_card.php', 1) . '?id=' . ($id > 0 ? $id : '__ID__');
		}
	}
}


/*
 * View
 *
 * Put here all code to build page
 */


$title = $langs->trans("LinesFromProductMatrixArea");
$help_url = '';
// print load_fiche_titre pour afficher le titre du contenu de la page courante
$object = linesFromProductMatrixObjectAutoLoad($element, $db);

if ($object->fetch($id, $ref) <= 0)
{
	dol_print_error($db);
	exit;
}

if ($id > 0 || !empty($ref)) {

	$object->fetch_thirdparty();

	llxHeader('', $langs->trans('Order'), 'EN:Customers_Orders|FR:Commandes_Clients|ES:Pedidos de clientes');

	/*
	 * La fonction call_user_func() appelle une fonction de rappel fournie par le premier argument
	 */

	if (function_exists($callUserFunction)){
		$head = call_user_func($callUserFunction, $object);
	}else{
		setEventMessage($langs->trans("CallFunctionNameError") ,'errors');
	}

	// structure de la tabulation pour la fiche active
	dol_fiche_head($head, 'tabmatrix', $langs->trans("CustomerOrder"), -1, 'order');
	$linkback = '<a href="' . DOL_URL_ROOT . '/'.$element.'/list.php?restore_lastsearch_values=1' . (!empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';



	// REF CUSTOMMER  ET TIERS ENTÊTE
	$morehtmlref = '<div class="refidno">';
	// Ref customer
	$morehtmlref .= $form->editfieldkey("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', 0, 1);
	$morehtmlref .= $form->editfieldval("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', null, null, '', 1);
	// Thirdparty
	$morehtmlref .= '<br>' . $langs->trans('ThirdParty') . ' : ' . $object->thirdparty->getNomUrl(1);


	// PROJET ENTÊTE
	if(!empty($conf->projet->enabled)) {
		$langs->load("projects");
		$morehtmlref .= '<br>' . $langs->trans('Project') . ' ';
		if ($action != 'classify') {
			$morehtmlref .= ' : ';
		}
		$morehtmlref .= $form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'none', 0, 0, 0, 1);
	}

	// CONSTRUCTION DE LA BANNIÈRE FAISANT RÉFÉRENCE À L'ENTITÉ
	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


	// <hr>
	print '<div class="fichecenter">';
	print '</div>';






 $TlinesObject = array();


 foreach ($object->lines as $l ){

 	$obj = new stdclass();
 	$obj->qty = $l->qty;
	 $TlinesObjectFPC[$l->fk_product] = $obj;

 }

	// tiny fpc : get id and element from current fpc
 	$fpc_obj = new stdClass();
	$fpc_obj->id = $object->id;
	$fpc_obj->element = $object->element;

	$bloc = new Bloc($db);
	$blocs =  $bloc->fetchAll('ASC','fk_rank');

	print '<div class="matrix-wrap">
				<div class="matrix-container">';
	if ($blocs) {
		foreach ($blocs as $b){
			print $bloc->displayBloc($b,  false,'view', $TlinesObjectFPC , $fpc_obj);
		}
	}
	print '</div></div>';
	dol_fiche_end();
}


if (!empty($conf->use_javascript_ajax)) {
	include DOL_DOCUMENT_ROOT . '/core/tpl/ajaxrow.tpl.php';
}
?>

<?php
// End of page
llxFooter();
$db->close();

