<?php

$sapi_type = php_sapi_name();
$script_file = basename(__FILE__);
$path = dirname(__FILE__) . '/';

global $db;

// Include and load Dolibarr environment variables
$res = 0;
if (!$res && file_exists($path . "master.inc.php")) $res = @include($path . "master.inc.php");
if (!$res && file_exists($path . "../master.inc.php")) $res = @include($path . "../master.inc.php");
if (!$res && file_exists($path . "../../master.inc.php")) $res = @include($path . "../../master.inc.php");
if (!$res && file_exists($path . "../../../master.inc.php")) $res = @include($path . "../../../master.inc.php");
if (!$res) die("Include of master fails");
dol_include_once('discountrules/class/discountrule.class.php');
require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT . '/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

// Load traductions files requiredby by page
$langs->loadLangs(array("linesfromproductmatrix@linesfromproductmatrix", "other", 'main'));

global $db;

$form = new Form($db);
$label = GETPOST('label');
$idBloc = GETPOST('id');
$action = GETPOST('action');
$idMatrix = GETPOST('idMatrix');
$addLineMatrix = GETPOST('addLineMatrix');
$jsonResponse = new stdClass();

// Modify a bloc's label
if (isset($idBloc) && isset($label) && isset($action) && $action == 'updateLabelBloc' ) {
	$sql = "UPDATE ".MAIN_DB_PREFIX."linesfromproductmatrix_bloc SET label = ".'"' .$label. '"'." WHERE rowid = $idBloc";
	$resql = $db->query($sql);
}

// Delete a bloc
if (isset($idMatrix) && isset($action) && $action == 'deleteMatrix' ) {
	$sql = 'DELETE FROM '.MAIN_DB_PREFIX.'linesfromproductmatrix_bloc WHERE rowid = '.$idMatrix;
	$resql = $db->query($sql);
}

// Add a Matrix Line
if (isset($idMatrix) && isset($action) && $action == 'deleteMatrix' ) {
	/*	$sql = 'DELETE FROM '.MAIN_DB_PREFIX.'linesfromproductmatrix_bloc WHERE rowid = '.$idMatrix;
		$resql = $db->query($sql);*/
}

print json_encode($jsonResponse, JSON_PRETTY_PRINT);

$db->close();    // Close $db database opened handler

$activateDebugLog = GETPOST('activatedebug','int');



