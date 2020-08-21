<?php

$sapi_type = php_sapi_name();
$script_file = basename(__FILE__);
$path = dirname(__FILE__) . '/';

global $db, $user;

// Include and load Dolibarr environment variables
$res = 0;

// LES USERS sont chargés avec main.inc. pas avec master.inc !!!
$res = @include ("../../main.inc.php"); // For root directory
if (! $res)
	$res = @include ("../../../main.inc.php"); // For "custom" directory
if (!$res) die("Include of master fails");

require_once DOL_DOCUMENT_ROOT . '/custom/linesfromproductmatrix/class/matrix.class.php';
require_once DOL_DOCUMENT_ROOT . '/custom/linesfromproductmatrix/class/bloc.class.php';
require_once DOL_DOCUMENT_ROOT . '/custom/linesfromproductmatrix/class/blochead.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
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
$currentHead = GETPOST('currentHead');
$currentType = GETPOST('currentType');
$reloadBlocView = GETPOST('reloadBlocView');
$errormysql = -1;
$jsonResponse = new stdClass();


// Modify a bloc's label
if (isset($idBloc) && isset($label) && isset($action) && $action == 'updateLabelBloc' ) {

    $b = new Bloc($db);
    $b->fetch($idBloc);
    $b->label = $label;
    $b->update($user);

}

// Create a bloc
if (isset($action) && $action == 'createBloc' ) {
	$s = 'SELECT COUNT(*) AS c FROM '.MAIN_DB_PREFIX.'linesfromproductmatrix_bloc ';
	//var_dump($s);
	$res = $db->query($s);
	//var_dump($res);
	$r = $db->fetch_row($res);

	$allowed = true;
	if(!empty($conf->global->PLM_MAX_COL))	$allowed  = (intval($r[0]) < intval($conf->global->PLM_MAX_BLOC));

	if ($allowed){
		$b = new Bloc($db);
		$b->label = $label;

		$res  = $b->create($user);
		if ($res < 0){
			$jsonResponse->error = $langs->trans("errorCreateBloc");

		}else{
			$error = 0;
			$bh = new BlocHead($db);
			$bh->fk_bloc = $b->id;
			$bh->date_creation = date('Y-m-d H:m:s');
			$bh->fk_user_creat = 1;
			$bh->fk_rank = 1;
			$bh->type = 1;
			$res = $bh->create($user);
			if ($res == $errormysql) {
				$jsonResponse->error =$langs->trans("errorCreateBlocHeadRow");
			}else{
				$bh = new BlocHead($db);
				$bh->fk_bloc = $b->id;
				$bh->date_creation = date('Y-m-d H:m:s');
				$bh->fk_user_creat = 1;
				$bh->fk_rank = 1;
				$bh->type = 0;
				$res = $bh->create($user);
				if ($res == $errormysql ){
					$error++;
					$jsonResponse->error =$langs->trans("errorCreateBlocHeadCol");
				}
			}

		}

		$out = $b->displayBloc($b, false, 'config');

		$jsonResponse->data = $out;

	}else{
		$jsonResponse->error =  $langs->trans("MaxBlocError");
	}


}

// Delete a bloc
if (isset($idMatrix) && isset($action) && $action == 'deleteMatrix' ) {

    // must handling cascade delete blockhead and matrix before deleting block !!!
	$sql = 'select rowid FROM '.MAIN_DB_PREFIX.'linesfromproductmatrix_blochead WHERE fk_bloc = '.$idMatrix;
	$resql = $db->query($sql);


	while ($obj = $db->fetch_object($resql)){
		$bh = new BlocHead($db);
		// peupler
		$bh->fetch($obj->rowid);
		$res = $bh->delete($user);
		if ($res == $errormysql){
			$jsonResponse->error = $langs->trans("errorDeleteBlocHead");
			break;
		}
	}

	$sql = 'select rowid FROM '.MAIN_DB_PREFIX.'linesfromproductmatrix_matrix WHERE fk_bloc = '.$idMatrix;
	$resql = $db->query($sql);
	while ($obj = $db->fetch_object($resql)){
		$bh = new Matrix($db);
		// peupler
		$bh->fetch($obj->rowid);
		$res = $bh->delete($user);
		if ($res == $errormysql){
			$jsonResponse->error = $langs->trans("errorDeleteBlocHead");
			break;
		}
	}

    $b = new Bloc($db);
    $b->id = $idMatrix;

    $res = $b->delete($user);
	if ($res == $errormysql){
		$jsonResponse->error = $langs->trans("errorDeleteBloc");
	}


}

// Delete a Head (Line OR Col) AND Matrix LINKS
if (isset($currentHead) && isset($action) && $action == 'deleteHead' ) {
	if ($currentType == 0) {
		$nameCOL = 'fk_blochead_column';
	}else{
		$nameCOL = 'fk_blochead_row';
	}
	$sql = 'SELECT * FROM '.MAIN_DB_PREFIX.'linesfromproductmatrix_matrix WHERE '.$nameCOL.'  = '.$currentHead;
	$resql = $db->query($sql);

	while ($obj = $db->fetch_object($resql)) {
		$bh = new Matrix($db);
		// peupler / HYDRATER !!!!! = FETCH
		$bh->fetch($obj->rowid);
		$res = $bh->delete($user);
		if ($res == $errormysql){
			$jsonResponse->error = $langs->trans("errorDeleteMatrix");
			break;
		}
	}

	$bh = new BlocHead($db);
	$bh->fetch($currentHead);
	$res= $bh->delete($user);
	if ($res == $errormysql){
		$jsonResponse->error = $langs->trans("errorDeleteBlocHead");
	}

	$bloc = new Bloc($db);
	$bloc->fetch($idBloc);
	$jsonResponse->currentDisplayedBloc = $bloc->displayBloc($bloc,$reloadBlocView ? $reloadBlocView : false, 'config');


}



// Add a Matrix Line or Col
if (isset($idBloc) && isset($action) && $action == 'addHeaderMatrix' ) {
	// On sélectionne le fk_rank MAX



	$allowed = true;

	if (intval($type) == 0){

		$s = 'SELECT COUNT(*) AS c FROM '.MAIN_DB_PREFIX.'linesfromproductmatrix_blochead WHERE type = 0 AND fk_bloc = '.$idBloc;
		//var_dump($s);
		$res = $db->query($s);
		//var_dump($res);
		$r = $db->fetch_row($res);
		if(!empty($conf->global->PLM_MAX_COL))	$allowed  = (intval($r[0]) < intval($conf->global->PLM_MAX_COL))	;
	}


 if ( intval($type) == 1){

	 $sql = 'SELECT MAX(fk_rank) AS m FROM '.MAIN_DB_PREFIX.'linesfromproductmatrix_blochead WHERE type = 1 AND fk_bloc = '.$idBloc;
	 // Méthode historique
	 $resql = $db->query($sql);
	 $result = $db->fetch_row($resql);
	 $fk_rank_increment = ++$result[0] ;  // On incrémente le fk_rank


	 // On insert une ligne avec le bon type  et les infos relatives au bloc (fk_bloc) et on lui passe un fk_rank à "fk_rank maximum + 1"
	 $h = new BlocHead($db);
	 $h->fk_bloc = $idBloc;
	 $h->date_creation = date('Y-m-d H:m:s');
	 $h->fk_user_creat = 1;
	 $h->fk_rank = $fk_rank_increment;
	 $h->type = intval($type);
	 $res =  $h->create($user);

	 if ($res == $errormysql){
		 $jsonResponse->error =  $langs->trans("errorCreateBlocHead");
	 }


	 $bloc = new Bloc($db);
	 $bloc->fetch($idBloc);
	 $jsonResponse->currentDisplayedBloc = $bloc->displayBloc($bloc, $reloadBlocView ? $reloadBlocView : false,'config');
// COL
 }else if ($allowed){
	 $sql = 'SELECT MAX(fk_rank) AS m FROM '.MAIN_DB_PREFIX.'linesfromproductmatrix_blochead WHERE type = 1 AND fk_bloc = '.$idBloc;
	 // Méthode historique
	 $resql = $db->query($sql);
	 $result = $db->fetch_row($resql);
	 $fk_rank_increment = ++$result[0] ;  // On incrémente le fk_rank


	 // On insert une ligne avec le bon type  et les infos relatives au bloc (fk_bloc) et on lui passe un fk_rank à "fk_rank maximum + 1"
	 $h = new BlocHead($db);
	 $h->fk_bloc = $idBloc;
	 $h->date_creation = date('Y-m-d H:m:s');
	 $h->fk_user_creat = 1;
	 $h->fk_rank = $fk_rank_increment;
	 $h->type = intval($type);
	 $res =  $h->create($user);

	 if ($res == $errormysql){
		 $jsonResponse->error =  $langs->trans("errorCreateBlocHead");
	 }


	 $bloc = new Bloc($db);
	 $bloc->fetch($idBloc);
	 $jsonResponse->currentDisplayedBloc = $bloc->displayBloc($bloc, $reloadBlocView ? $reloadBlocView : false,'config');
// COL
 } else{
		$jsonResponse->error =  $langs->trans("MaxColError");
	}


}



// MODIFICATION LABEL HEADERS
if (isset($idHead) && isset($label) && isset($action) && $action == 'updatelabelHeader' ) {

    $h = new BlocHead($db);
    $h->fetch($idHead);
    $h->label = $label;
    $res = $h->update($user);
	if ($res == $errormysql){
		$jsonResponse->error = $langs->trans("errorUpdateBlocHead");
	}
}


//***  CRUD SELECT PRODUCT   ***//
if (isset($idBloc) && isset($label) && isset($action) && $action == 'updateselect' ) {

	// BLOCHEAD COL AND BLOCHEAD ROW AND BLOC ID
	$m = new Matrix($db);
	$res = $m->db->getRow("SELECT rowid  FROM ".MAIN_DB_PREFIX."linesfromproductmatrix_matrix"." WHERE fk_bloc = $idBloc AND fk_blochead_column = $headerColId  AND fk_blochead_row = $headerRowId");


    $m->fk_bloc = $idBloc;
    $m->fk_blochead_column = $headerColId;
    $m->fk_blochead_row = $headerRowId;
    $m->fk_product =$idproduct;
    $m->id = !is_null($res->rowid) ? $res->rowid : '';

    if (is_null($res->rowid)){
        $res = $m->create($user);
		if ($res == $errormysql){
			$jsonResponse->error = $langs->trans("errorUpdateProduct");
		}
    }else if (!is_null($res->rowid) && !empty($idproduct) ){
        $res = $m->update($user);
		if ($res == $errormysql){
			$jsonResponse->error = $langs->trans("errorUpdateProduct");
		}

    }else{
        $res = $m->delete($user);
		if ($res == $errormysql){
			$jsonResponse->error = $langs->trans("errorDeleteProduct");
		}
    }

}


$db->close();    // Close $db database opened handler
$activateDebugLog = GETPOST('activatedebug','int');
print json_encode($jsonResponse, JSON_PRETTY_PRINT);


function addColRow($db,$type,$user,$errormysql,$jsonResponse,$reloadBlocView,$langs){
	$sql = 'SELECT MAX(fk_rank) AS m FROM '.MAIN_DB_PREFIX.'linesfromproductmatrix_blochead WHERE type = 1 AND fk_bloc = '.$idBloc;
	// Méthode historique
	$resql = $db->query($sql);
	$result = $db->fetch_row($resql);
	$fk_rank_increment = ++$result[0] ;  // On incrémente le fk_rank


	// On insert une ligne avec le bon type  et les infos relatives au bloc (fk_bloc) et on lui passe un fk_rank à "fk_rank maximum + 1"
	$h = new BlocHead($db);
	$h->fk_bloc = $idBloc;
	$h->date_creation = date('Y-m-d H:m:s');
	$h->fk_user_creat = 1;
	$h->fk_rank = $fk_rank_increment;
	$h->type = intval($type);
	$res =  $h->create($user);

	if ($res == $errormysql){
		$jsonResponse->error =  $langs->trans("errorCreateBlocHead");
	}


	$bloc = new Bloc($db);
	$bloc->fetch($idBloc);

	return $jsonResponse->currentDisplayedBloc = $bloc->displayBloc($bloc, $reloadBlocView ? $reloadBlocView : false,'config');
}

