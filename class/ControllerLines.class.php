<?php
class ControllerLines {

	public $db;
	public $OBJECT_COMMANDE = "commande";
	public $OBJECT_PROPAL = "propal";
	public $OBJECT_FACTURE = "facture";
	public $jsonResponse ;
	public $currentElement;
	public $qty;
	public $currentQty;
	public $langs;
	public $fk_fpc_object;
	public $idproduct;
	public $error = 0;
	public $errormysql = -1;
	public $obj;

	public function __construct($db,$langs){
		$this->db = $db;
		$this->jsonResponse = new stdClass();
		$this->langs = $langs;
	}

	/**
	 * @param $qty
	 * @param $currentQty
	 * @param $idproduct
	 * @param $element  /context object Facture / Propale / commande
	 */
	public function init($fk_fpc_object,$qty,$currentQty,$idproduct,$element,$obj){

		$this->qty = $qty;
		$this->currentQty = $currentQty;
		$this->idproduct = $idproduct;
		$this->currentElement = $element;
		$this->fk_fpc_object = $fk_fpc_object;
		$this->obj = $obj;
		$this->checkQty();
		$this->checkProduct();

	}

	public function processInput(){
		global $user, $conf;

		if (!$this->jsonResponse->error) {


			$this->obj->fetch($this->fk_fpc_object);

			$p = new Product($this->db);
			$p->fetch($this->idproduct);

			$PriceSQL = "select price , price_ttc  FROM llx_product_price WHERE fk_product = " . $this->idproduct . ' ORDER BY date_price DESC';

			$price_level = 1;
			if ($this->obj->socid > 0 && !empty($conf->global->PRODUIT_MULTIPRICES)) {
				$thirdpartytemp = new Societe($this->db);
				$thirdpartytemp->fetch($this->obj->socid);
				$price_level = $thirdpartytemp->price_level;
				if ($price_level >= 1)
				{
					$res = new stdClass();
					$res->price = $p->multiprices[$price_level];
					$res->price_ttc = $p->multiprices_ttc[$price_level];
				}
			}

			$updated = false;
			$this->jsonResponse->msg = "product fetched";
			// Itération sur les lignes de Facture/Propal/Commande
			foreach ($this->obj->lines as $l) {
				if ($l->fk_product == $this->idproduct) {

					if (empty($res)) $res = $this->db->getRow($PriceSQL);

					// On cherche à supprimer la ligne active
					if ($this->qty == 0) {
						$this->errormysql = $this->deleteLineOfObject($this->obj, $l->id);
						$this->jsonResponse->msg = "line deleted";
						$updated = true;
						break;

					} else if ($res > 0) {

						// On créé un objet $values contenant toutes les infos nécessaires pour l'update de TOUS les éléments FPC
						$values = $this->prepareValues($l, $this->qty, $res, $p);
						// On update
						$this->errormysql = $this->updateLineInObject($this->obj, $values);
						$this->jsonResponse->msg = "line updated";
						$updated = true;
						break;

					}
				}
			}
			// On ajoute la ligne, si elle n'est pas présente dans le current FPC
			if (!$updated) {

				if (empty($res)) $res = $this->db->getRow($PriceSQL);

				if (empty($res))
				{
					/*if ($p->import_key) // if $res null (produit importé), créer prix de vente à 0
					{*/
						// je crée une ligne de prix pour les produits importés qui n'en ont pas
						$sql = "INSERT INTO ".MAIN_DB_PREFIX."product_price(price_level,date_price, fk_product, fk_user_author, price, price_ttc, price_base_type,tosell, tva_tx, default_vat_code, recuperableonly,";
						$sql .= " localtax1_tx, localtax2_tx, localtax1_type, localtax2_type, price_min,price_min_ttc,price_by_qty,entity,fk_price_expression) ";
						$sql .= " VALUES(1, '".$this->db->idate(dol_now())."',".$p->id.",".$user->id.", 0, 0,'HT',1 ,20 , null, 0,";
						$sql .= " 0, 0, '0', '0', 0, 0, 0,".$conf->entity.", null";
						$sql .= ")";
						$resql = $this->db->query($sql);
						if ($resql)
						{
							$res = new stdClass();
							$res->price = "0.00000000";
							$res->price_ttc = "0.00000000";
						}
//					}
				}

				if ($res > 0) {
					// On créé un objet $values contenant toutes les infos nécessaires pour l'update de TOUS les éléments FPC
					$values = $this->prepareValues($l, $this->qty, $res, $p, true);
					$this->errormysql = $this->addLineInObject($this->obj, $values, $this->obj->element);
					$this->jsonResponse->msg = "line created";
				} else {
					$this->error++;
					$this->jsonResponse->msg = "Error while getting product price";
				}
			}
		}

	}


	public function checkQty(){

		if ($this->qty < 0 || !is_numeric($this->qty)) {
			if(!is_numeric($this->qty)) $this->jsonResponse->error = $this->langs->trans("NotANumber");
			else $this->jsonResponse->error = $this->langs->trans("NegativeNumberError");
			$this->jsonResponse->currentQty = $this->currentQty;
		}
	}

	public function checkProduct(){
		if  (empty($this->idproduct)){
			$this->jsonResponse->error = $this->langs->trans("NoProductError");
		}
	}

	/**
	 * Update une ligne dans un Objet de type FPC
	 * @param          $currentObj
	 * @param stdClass $values
	 * @return int $error  1 = OK /  < 0 = erreur
	 */
	function updateLineInObject (&$currentObj, stdClass $values){

		/** @var Commande $currentObj */
		if ($currentObj->element == $this->OBJECT_COMMANDE) {
			return $currentObj->updateline($values->rowid, $values->desc, $values->pu, $values->qty, $values->remise_percent, $values->txtva);
		}
		/** @var Propal $currentObj */
		if ($currentObj->element == $this->OBJECT_PROPAL) {
			return $currentObj->updateline($values->rowid, $values->pu, $values->qty, $values->remise_percent, $values->txtva);
		}
		/** @var Facture $currentObj */
		if ($currentObj->element == $this->OBJECT_FACTURE)  {
			return $currentObj->updateline($values->rowid, $values->desc, $values->pu, $values->qty, $values->remise_percent, $values->date_start, $values->date_end, $values->txtva);
		}
	}

	/**
	 * Ajouter une ligne dans un Objet de type FPC
	 * @param          $currentObj
	 * @param stdClass $values
	 * @return int $error  1 = OK /  < 0 = erreur
	 */
	function addLineInObject (&$currentObj, stdClass $values, $element){


		if($element == $this->OBJECT_COMMANDE) {
			return $currentObj->addLine(
				$values->desc,
				$values->pu,
				$values->qty,
				$values->txtva,
				'',
				'',
				$values->idproduct,
				'',
				'',
				'',
				$values->price_base_type,
				$values->pu_ttc);
		}
		if($element == $this->OBJECT_FACTURE) {
			return $currentObj->addLine(
				$values->desc,
				$values->pu,
				$values->qty,
				$values->txtva,
				'',
				'',
				$values->idproduct,
				'',
				'',
				'',
				0,
				0,
				'',
				$values->price_base_type,
				$values->pu_ttc);
		}
		if($element == $this->OBJECT_PROPAL) {
			return $currentObj->addLine(
				$values->desc,
				$values->pu,
				$values->qty,
				$values->txtva,
				'',
				'',
				$values->idproduct,
				'',
				$values->price_base_type,
				$values->pu_ttc);
		}

	}

	/**
	 * Supprimer une ligne dans un Objet de type FPC
	 * @param        $currentObj
	 * @param int    $idLine
	 * @return int $error  1 = OK /  < 0 = erreur
	 */
	function deleteLineOfObject (&$currentObj, $idLine){
		global  $user;

		if ($currentObj->element == $this->OBJECT_COMMANDE) {
			/** @var Commande $currentObj */
			return $currentObj->deleteLine($user, $idLine);

		}
		else {
			// fature / propal
			return $currentObj->deleteLine($idLine);
		}
	}

	/**
	 * Création d'objet contenant les valeurs nécessaires au CRU des objets FPC
	 * @param      $currentLine
	 * @param      $qty
	 * @param      $res
	 * @param      $product
	 * @param bool $add
	 * @return stdClass
	 */
	function prepareValues($currentLine, $qty, $res, $product, $add = false) {
		$values = new stdClass();
		$values->idproduct = $product->id ? $product->id : null;
		$values->rowid = $currentLine->id;
		$values->pu = $res->price;
		$values->qty = $qty;
		$values->date_start = null;
		$values->date_end = null;
		if ($add) {
			$values->desc = $product->label;
			$values->remise_percent = $product->remise_percent;
			$values->txtva = $product->tva_tx;
			$values->pu_ttc = $res->price_ttc; // Obligatoire pour que s'affiche le prix HT dans la fiche du FPC
		}
		else {
			$values->desc = $currentLine->desc;
			$values->remise_percent = $currentLine->remise_percent;
			$values->txtva = $currentLine->tva_tx;
		}

		return $values;
	}

}
