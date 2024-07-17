<?php
/* Copyright (C) 2018 John BOTELLA
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    class/actions_musiqueservice.class.php
 * \ingroup trainingsession
 * \brief   Example hook overload.
 *
 * Put detailed description here.
 */

/**
 * Class Actionstrainingsession
 */
class ActionsLinesfromproductmatrix
{
	/**
	 * @var DoliDB Database handler.
	 */
	public $db;
	/**
	 * @var string Error
	 */
	public $error = '';
	/**
	 * @var array Errors
	 */
	public $errors = array();


	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;


	/**
	 * Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	public function printObjectLine($parameters, &$object, &$action, $hookmanager){
		var_dump('here');
	}

	/**
	 * @param $parameters
	 * @param $object
	 * @param $action
	 * @param $hookmanager
	 */
	public function formObjectOptions($parameters, &$object, &$action, $hookmanager){}
	/**
	 *
	 * @param $parameters
	 * @param $object
	 * @param $action
	 * @param $hookmanager
	 */
	public function doActions($parameters, &$object, &$action, $hookmanager){

		global $conf,$langs;
		if ($action == 'delete'){}

		if ($action == 'confirm_delete'){}
		if ($action == 'confirm_delete_childs'){
			$action="confirm_delete";
		}


	}

	/**
	 * Overloading the addMoreActionsButtons function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager)
	{
		global $conf,$user,$langs;

		$context = explode(":",$parameters['currentcontext']);

		if (in_array('productcard',$context)){

			// confirmation de supression des childs session du product actif
			if ($action == 'ask_delete_modmusiquechild') {
				$form = new Form($this->db);

				print $form->formconfirm($_SERVER["PHP_SELF"]
					. '?id=' . $object->id, $langs->trans('doublecheck_delete')
					, $langs->trans('CheckConfirmDeleteProduct', $object->ref), 'confirm_delete_childs'
					, 'yes', 'action-delete', 350, 300);


			}




			// regular action add
			$param = array("attr" => array(
				"title" => "Test",
				'class' => 'classfortooltip'
			)
			);
			$metrique = substr($object->duration,-1) == 'i' ? 'minutes' : substr($object->duration,-1);
			print dolGetButtonAction($langs->trans('btnSessionCreate'),
				'<i class="fa fa-plus" aria-hidden="true"></i> '.$langs->trans('btnSessionCreate'),
				'default',
				dol_buildpath('/custom/musiqueservice/trainingsession_card.php?action=create&idmenu=381&mainmenu=musiqueservice&leftmenu='."&fk_product=" .$object->id .$this->addDatesFieldsWithDuration($object), 1),
				'button-modsport-creation',
				$user->hasRight('musiqueservice', 'trainingsession', 'write'), $param );
		}
	}


	public function completeTabsHead($parameters, &$object, &$action, $hookmanager) {
		$context = explode(":",$parameters['currentcontext']);
		var_dump($context);
		if (in_array('productcard',$context)){}
	}

	/**
	 * return a formatted uri params  date_debut and date_fin with the product date duration
	 * @param $object   product /service
	 * @return string   formatted params
	 */
	private function addDatesFieldsWithDuration(&$object){
		$metrique = substr($object->duration,-1) == 'i' ? 'minutes' : substr($object->duration,-1);
		return  "&date_debut=".date("Y-m-d H:i:s").
			"&date_fin=".date('Y-m-d H:i:s', strtotime('+'.substr($object->duration,0, strlen($object->duration -1)).' '.$metrique));
	}

}

