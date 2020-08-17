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

    $b = new Bloc($db);
    $b->fetch($idBloc);
    $b->label = $label;
    $b->update($user);

}

// Create a bloc
if (isset($action) && $action == 'createBloc' ) {
	$b = new Bloc($db);
	$b->label = $label;
	$b->create($user);

	$bh = new BlocHead($db);
	$bh->fk_bloc = $b->id;
	$bh->date_creation = date('Y-m-d H:m:s');
	$bh->fk_user_creat = 1;
	$bh->fk_rank = 1;
	$bh->type = 1;
//	$bh->label = "";
	$bh->create($user);

	$bh = new BlocHead($db);
	$bh->fk_bloc = $b->id;
	$bh->date_creation = date('Y-m-d H:m:s');
	$bh->fk_user_creat = 1;
	$bh->fk_rank = 1;
//	$bh->label = "";
	$bh->type = 0;
	$bh->create($user);

	$out = '';
	$out = '<div class="matrix-item">
				<div class="matrix-head">
					<input id="bloc-label-' . $b->id . '" class="inputBloc" onfocus="this.select();" style="text-decoration:none; background: none;" type="text" size="6" name="bloclabel" data-id="' . $b->id . '" value="' . $b->label . '">
						<a class="editfielda reposition" data-id="' . $b->id . '" href="#bloc-label-' . $b->id . '">
							<span id="' . $b->id . '" data-id="' . $b->id . '" class="fas fa-pencil-alt" title="Modifier"></span>
							<span id="' . $b->id . '" data-id="' . $b->id . '" class="fa fa-check" style="color:lightgrey; display: none" ></span>
						</a>
						<a id="matrix-delete-' . $b->id . '">
							<span data-id="'.$b->id.'" class="fas fa-trash pictodelete pull-right" style="" title="Supprimer"></span>
						</a>
				</div>';

	$out .= '<div id="dialog-confirm" style="display:none" title="Confirmation de suppression">
				<p><span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>Êtes-vous sûr(e) ? Ce bloc sera supprimé ainsi que toutes les données qui lui sont associées</p>
			 </div>';

	// Part to display a delete success notification
	$out .= '<div id="notification" style="display:none;float:left;margin:12px 12px 20px 0;">
				<span class="dismiss"><a title="Masquer ce message">x</a></span>
			</div>';

	// Part to display a blloc create success notification
	$out .= '<div id="create-notification" style="display:none;float:left;margin:12px 12px 20px 0;">
				<span class="dismiss"><a title="Masquer ce message">x</a></span>
			</div>';

	$b->fetchMatrix($b);
	$out .= $b->display();
	$out .= '<div class="matrix-footer">
					<a data-type="1" data-id="'.$b->id.'" class="fas fa-grip-lines matrix-add --line"> Ajouter une ligne</a>
					<a data-type="0" data-id="'.$b->id.'" class="fas fa-grip-lines matrix-add --line"> Ajouter une Colonne</a>
				</div>
			 </div>';

	$jsonResponse->data = $out;

}

// Delete a bloc
if (isset($idMatrix) && isset($action) && $action == 'deleteMatrix' ) {

    // must handling cascade delete blockhead and matric before deleting block !!!
	$sql = 'select rowid FROM '.MAIN_DB_PREFIX.'linesfromproductmatrix_blochead WHERE fk_bloc = '.$idMatrix;
	$resql = $db->query($sql);

	while ($obj = $db->fetch_object($resql)){
		$bh = new BlocHead($db);
		// peupler
		$bh->fetch($obj->rowid);
		$bh->delete($user);
	}

	$sql = 'select rowid FROM '.MAIN_DB_PREFIX.'linesfromproductmatrix_matrix WHERE fk_bloc = '.$idMatrix;
	$resql = $db->query($sql);
	while ($obj = $db->fetch_object($resql)){
		$bh = new Matrix($db);
		// peupler
		$bh->fetch($obj->rowid);
		$bh->delete($user);
	}

    $b = new Bloc($db);
    $b->id = $idMatrix;
    $b->delete($user);


}

// Add a Matrix Line or Col
if (isset($idBloc) && isset($action) && $action == 'addHeaderMatrix' ) {
	// On sélectionne le fk_rank MAX

	$sql = 'SELECT MAX(fk_rank) AS m FROM '.MAIN_DB_PREFIX.'linesfromproductmatrix_blochead WHERE type = 1 AND fk_bloc = '.$idBloc;
	// Méthode historique
	$resql = $db->query($sql);
	$result = $db->fetch_row($resql);  // $resql = $db->getRow($sql)   c'est la même chose en méthode simplifiée
	$fk_rank_increment = ++$result[0] ;  // On incrémente le fk_rank



	// On insert une ligne avec le bon type  et les infos relatives au bloc (fk_bloc) et on lui passe un fk_rank à "fk_rank maximum + 1"
    $h = new BlocHead($db);
    $h->fk_bloc = $idBloc;
    $h->date_creation = date('Y-m-d H:m:s');
    $h->fk_user_creat = 1;
    $h->fk_rank = $fk_rank_increment;
    $h->type = intval($type);
    $h->create($user);

}


// MODIFICATION LABEL HEADERS
if (isset($idHead) && isset($label) && isset($action) && $action == 'updatelabelHeader' ) {

    $h = new BlocHead($db);
    $h->fetch($idHead);
    $h->label = $label;
    $h->update($user);

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
        $object = $m->create($user);
    }else if (!is_null($res->rowid) && !empty($idproduct) ){
        $m->update($user);
    }else{
        $m->delete($user);
    }

}


$db->close();    // Close $db database opened handler
$activateDebugLog = GETPOST('activatedebug','int');
print json_encode($jsonResponse, JSON_PRETTY_PRINT);


