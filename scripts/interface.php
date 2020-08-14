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
require_once DOL_DOCUMENT_ROOT . '/custom/linesfromproductmatrix/class/matrix.class.php';
// Load traductions files requiredby by page
$langs->loadLangs(array("linesfromproductmatrix@linesfromproductmatrix", "other", 'main'));





//---------------------------------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------------

$label = GETPOST('label');
$idHead = GETPOST('idhead');
$idBloc = GETPOST('id');
$idproduct = GETPOST('idproduct');
$action = GETPOST('action');
$headerColId = GETPOST('blocheadercolid');
$headerRowId = GETPOST('blocheaderrowid');
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


// MODIFICATION LABEL HEADERS
if (isset($idHead) && isset($label) && isset($action) && $action == 'updatelabelHeader' ) {
	$sql = "UPDATE ".MAIN_DB_PREFIX."linesfromproductmatrix_blochead SET label = ".'"' .$label. '"'." WHERE rowid = $idHead";
	$resql = $db->query($sql);
}


//***  UPDATE SELECT PRODUCT   ***//
if (isset($idBloc) && isset($label) && isset($action) && $action == 'updateselect' ) {

	// BLOCHEAD COL AND BLOCHEAD ROW AND BLOC ID
	$m = new Matrix($db);
	$res = $m->db->getRow("SELECT COUNT(*) as c FROM ".MAIN_DB_PREFIX."linesfromproductmatrix_matrix"." WHERE fk_bloc = $idBloc AND fk_blochead_column = $headerColId  AND fk_blochead_row = $headerRowId");

	// resultset exist in db
	if ($res->c > 0){
		$sql = "UPDATE ".MAIN_DB_PREFIX."linesfromproductmatrix_matrix SET FK_PRODUCT = ". $idproduct . " WHERE fk_bloc = $idBloc AND fk_blochead_column = $headerColId  AND fk_blochead_row = $headerRowId";
		$resql = $db->query($sql);
	}else{
		// create matrix for this cell
	}

}


$db->close();    // Close $db database opened handler

$activateDebugLog = GETPOST('activatedebug','int');

print json_encode($jsonResponse, JSON_PRETTY_PRINT);


