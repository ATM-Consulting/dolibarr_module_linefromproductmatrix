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
require_once DOL_DOCUMENT_ROOT . '/custom/linesfromproductmatrix/class/bloc.class.php';
require_once DOL_DOCUMENT_ROOT . '/custom/linesfromproductmatrix/class/blochead.class.php';

// Load traductions files requiredby by page
$langs->loadLangs(array("linesfromproductmatrix@linesfromproductmatrix", "other", 'main'));





//---------------------------------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------------

$label = GETPOST('label');
$idHead = GETPOST('idhead');
$idBloc = GETPOST('id');
$type = GETPOST('blocheadType');
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
if (isset($idBloc) && isset($action) && $action == 'addLineMatrix' ) {
	// On sélectionne le fk_rank MAX
	$sql = 'SELECT MAX(fk_rank) AS m FROM '.MAIN_DB_PREFIX.'linesfromproductmatrix_blochead WHERE type = 1 AND fk_bloc = '.$idBloc;
	// Méthode historique
	$resql = $db->query($sql);
	$result = $db->fetch_row($resql);  // $resql = $db->getRow($sql)   c'est la même chose en méthode simplifiée
	$fk_rank_increment = ++$result[0] ;  // On incrémente le fk_rank


	// On insert une ligne avec le bon type (1) et les infos relatives au bloc (fk_bloc) et on lui passe un fk_rank à "fk_rank maximum + 1"
	$sql2 = 'INSERT INTO '.MAIN_DB_PREFIX.'linesfromproductmatrix_blochead (fk_bloc, date_creation, fk_user_creat, type, fk_rank) VALUES ('.$idBloc.', datetime NOT NULL, 1, 1, '.$fk_rank_increment.')';
	$resql2 = $db->query($sql2);

	$lastBlocheadId = $db->last_insert_id(MAIN_DB_PREFIX.'linesfromproductmatrix_blochead');
	var_dump($lastBlocheadId);

	$b = new Bloc($db);
	$bloc =  $b->getBloc($idBloc);
	$b->fetchMatrix($b);

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


