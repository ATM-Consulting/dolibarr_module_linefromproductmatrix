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

// Load traductions files requiredby by page
$langs->loadLangs(array("linesfromproductmatrix@linesfromproductmatrix", "other", 'main'));

$label = GETPOST('label');
$idBloc = GETPOST('id');
$jsonResponse = new stdClass();

if (isset($idBloc) && isset($label)) {
	$sql = "UPDATE ".MAIN_DB_PREFIX."linesfromproductmatrix_bloc SET label = ".'"' .$label. '"'." WHERE rowid = $idBloc";
	$resql = $db->query($sql);

}

print json_encode($jsonResponse, JSON_PRETTY_PRINT);

$db->close();    // Close $db database opened handler

$activateDebugLog = GETPOST('activatedebug','int');



