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
 *
 * Library javascript to enable Browser notifications
 */

if (!defined('NOREQUIREUSER'))  define('NOREQUIREUSER', '1');
if (!defined('NOREQUIREDB'))    define('NOREQUIREDB','1');
if (!defined('NOREQUIRESOC'))   define('NOREQUIRESOC', '1');
//if (!defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1');
//if (!defined('NOCSRFCHECK'))    define('NOCSRFCHECK', 1);
if (!defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', 1);
if (!defined('NOLOGIN'))        define('NOLOGIN', 1);
if (!defined('NOREQUIREMENU'))  define('NOREQUIREMENU', 1);
if (!defined('NOREQUIREHTML'))  define('NOREQUIREHTML', 1);
if (!defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX','1');


/**
 * \file    js/linesfromproductmatrix.js.php
 * \ingroup linesfromproductmatrix
 * \brief   JavaScript file for module linesfromproductmatrix.
 */

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php");
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include(substr($tmp, 0, ($i+1))."/main.inc.php");
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/../main.inc.php")) $res=@include(substr($tmp, 0, ($i+1))."/../main.inc.php");
// Try main.inc.php using relative path
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

// Define js type
header('Content-Type: application/javascript');
// Important: Following code is to cache this file to avoid page request by browser at each Dolibarr page access.
// You can use CTRL+F5 to refresh your browser cache.
if (empty($dolibarr_nocache)) header('Cache-Control: max-age=3600, public, must-revalidate');
else header('Cache-Control: no-cache');


// Load traductions files requiredby by page
$langs->loadLangs(array("linesfromproductmatrix@linesfromproductmatrix","other"));
?>



/* Javascript library of module linesfromproductmatrix */
$(document).ready(function() {

	$(document).on("click", ".fa-pencil-alt, .inputBloc", function () {
		if ($(this).is("input")) {
			var input = $(this);
			var check = $(this).next("a").children("span.fa-check");
			var pencil = $(this).next("a").children("span.fa-pencil-alt");
		} else if ($(this).is("span")) {
			var pencil = $(this);
			var check = $(this).next("span");
			var input = $(this).closest("input");
		}
		check.css("display", "inline-block");
		pencil.css("display", "none");
	});

	// START CREATE BLOC EVENTS
	$(document).on("keypress", "#inputPlaceholderEx", function (e) {
		if (e.which == 13) {
			createABloc();
		}
	});

	$(document).on("click", "#add-block-btn", createABloc);
	// END OF CREATE BLOC EVENTS


	// DELETE A BLOC AND HIS CHILDREN
	/**
	 * DELETE BLOCK
	 *
	 */
	$(document).on("click", ".pictodelete", deleteConfirmation);


	// DELETE A BLOCHEAD
	/**
	 * DELETE HEADLINE OR HEADCOL
	 *
	 */
	$(document).on("click", ".fas.fa-trash.deleteHead", function () {
		var $out = '<?php print $langs->trans("deleteHeadConfirm") ?>';
		var idBloc = $(this).parent().data("blocid");
		var currentType = $(this).parent().data("type");
		var currentBloc = $(this).parent().parent().parent().parent().parent();
		var currentHead = $(this).parent().data("id");


		$("#deleteHead-confirm").dialog({
			resizable: true,
			height: "auto",
			width: 400,
			modal: true,
			buttons: {
				"Supprimer": function () {
					$(this).dialog("close");
					$.ajax({
						url: "<?php print dol_buildpath('linesfromproductmatrix/scripts/interface.php', 1)?>",
						method: "POST",
						dataType: "json",  // format de réponse attendu
						data: {
							reloadBlocView : true,
							id: idBloc,
							currentHead: currentHead,
							currentType: currentType,
							action: 'deleteHead',
						},
						success: function (data) {
							if(!data.error) {
								currentBloc.html(data.currentDisplayedBloc);
								matrixSetMessage($out);
							}else {
								matrixSetMessage(data.error, "error");
								console.log(data.error);
							}
						},
						error: function (err) {
							matrixSetMessage(err.responseText, "error");
						}
					})
				},
				"Annuler": function () {
					$(this).dialog("close");
				}
			}

		});
	});



	/**
	 * Ajout colonne / ligne dans la matrice active
	 */
	$(document).on("click", ".matrix-add-btn", function () {
		var currentBloc = $(this).parent().parent();
		var $out = '<?php print $langs->trans("addHeadConfirm") ?>'
		var idBloc = $(this).data("id");
		var blocheadType = $(this).data("type");
		$.ajax({
			url: "<?php print dol_buildpath('linesfromproductmatrix/scripts/interface.php', 1)?>",
			method: "POST",
			dataType: "json",  // format de réponse attendu
			data: {
				reloadBlocView : true,
				id: idBloc,
				action: 'addHeaderMatrix',
				blocheadType: blocheadType
			},
			success: function (data) {
				if(!data.error) {
					currentBloc.html(data.currentDisplayedBloc);
					matrixSetMessage($out);
				}else {
					matrixSetMessage(data.error, "error");
				}
			},
			error: function (err) {
				matrixSetMessage(err.responseText, "error");
			}
		})
	});


	/**
	 * Modification  label entête Block
	 */
	$(document).on("change", ".inputBloc", function () {
		var labelBloc = $(this).val(); // On récupère la valeur de l\'input
		var idBloc = $(this).data("id");  // On récupère l\'id de l\'input
		var self = $(this);
		var parentBlocTitle = $(this).closest("div");
		$.ajax({
			url: "<?php print dol_buildpath('linesfromproductmatrix/scripts/interface.php', 1)?>",
			method: "POST",
			dataType: "json",  // format de réponse attendu
			data: {
				id: idBloc,
				action: 'updateLabelBloc',
				label: labelBloc
			},
			success: function (data) {
				if(!data.error) {
					parentBlocTitle.css("background-color", "green");
					setTimeout(function () {
						parentBlocTitle.css("background-color", "white");
					}, 700)
					var pencilToShow = self.next().children(".fa-pencil-alt");
					var check = self.next().children(".fa-check");

					check.toggle(0);
					pencilToShow.toggle(0);
				}else {
					matrixSetMessage(data.error, "error");
				}
			},
			error: function (err) {
				matrixSetMessage(err.responseText, "error");
			}
		})
	});


	/**
	 * Modification des entêtes label HEADERS
	 */
	$(document).on("change", ".input-bloc-header", function () {
		let idBlocHead = $(this).data("idhead");  // On récupère l\'id de l\'input
		let self = $(this);
		var label = $(this).val();
		var currentBlocCell = self.parent();
		var currentValue = $(this).attr("data-currentValue");


		if(label.length == 0){
			self.val(currentValue);
			matrixSetMessage('<?php print $langs->trans("emptyLabelHeaderError") ?>', "error");

		}else {

			var parentBlocTitle = $(this).closest("div");

			parentBlocTitle.css("background-color", "green");

			let data =
				{
					idhead: idBlocHead,
					label: label,
					action: "updatelabelHeader"
				}
			$.ajax({
				url: "<?php print dol_buildpath('linesfromproductmatrix/scripts/interface.php', 1)?>",
				method: "POST",
				dataType: "json",
				data: data,
				success: function (data) {
					if (!data.error) {
						parentBlocTitle.css("background-color", "green");
						setTimeout(function () {
							parentBlocTitle.css("background-color", "white");
						}, 700)
					} else {
						matrixSetMessage(data.error, "error");
					}
				},
				error: function (err) {
					parentBlocTitle.css("background-color", "red");
					matrixSetMessage(err.responseText, "error");
				}
			})
		}
	});

	/**
	 * Modification des PRODUITS
	 */
	$(document).on("change", ".inputproductmatric", function () {
		var element  = $(this).parent().children('input');
		let idproduct  = element.val();
		let bhc = $(this).data("blocheadercolid");
		let bhr = $(this).data("blocheaderrowid");
		let bid = $(this).data("blocid");
		let self = $(this);

		let data =
			{
				id: bid,
				blocheadercolid: bhc,
				blocheaderrowid: bhr,
				idproduct: idproduct,
				action: "updateselect"
			}

		$.ajax({
			url: "<?php print dol_buildpath('linesfromproductmatrix/scripts/interface.php', 1)?>",
			method: "POST",
			dataType: "json",
			data: data,
			success: function (data) {
				if(!data.error) {
					self.css("background-color", "green");
					self.blur();
					setTimeout(function () {
						self.css("background-color", '#fff');
					}, 800);
				}else {
					matrixSetMessage(data.error, "error");
				}
			},
			error: function (err) {
				matrixSetMessage(err.responseText, "error");
			}
		})
	});


	/** **
	 *
	 */
	$(document).on("change", ".inputNumber", function () {




		let idproduct  = $(this).attr('data-fk-product');
		let fk_fpc_object = $(this).attr('data-fk-fpc-object');
		let fpc_element = $(this).attr('data-fpc-element');
		let currentQty= $(this).attr('data-currentQty');
		var  self = $(this);
		let qty = $(this).val();
		let data =
			{
				fk_fpc_object :fk_fpc_object,
				fpc_element : fpc_element,
				idproduct: idproduct,
				qty :qty,
				currentQty : currentQty,
				action: "updateQtyProduct"
			}

		$.ajax({
			url: "<?php print dol_buildpath('linesfromproductmatrix/scripts/interface.php', 1)?>",
			method: "POST",
			dataType: "json",
			data: data,
			success: function (data) {
				console.log(data);
				if(!data.error) {

					if (self.val() == 0) {self.val('');}

					self.css("background-color", "green");
					setTimeout(function () {
						self.css("background-color", '#fff');
					}, 800);

				}else {
					matrixSetMessage(data.error, "error");

					// on entre < 0 sur une cellule avec produit avec qty préexistante
					if (data.currentQty){
						self.val(data.currentQty);
					}else{ // on entre < 0 sur une cellule avec ou sans produit sans qty préexistante
						self.val('');
					}


				}
			},
			error: function (err) {
				matrixSetMessage(err.responseText, "error");
			}
		})
	});


	/**
	 *  Box de confirmation avant suppression
	 *
	 */
	function deleteConfirmation() {
		var $out = '<?php print $langs->trans("deleteBlocConfirm") ?>';
		var idMatrix = $(this).data("id");
		var currentBloc = $(this).closest("div.matrix-item");

		$("#dialog-confirm").dialog({
			resizable: true,
			height: "auto",
			width: 400,
			modal: true,
			buttons: {
				"Supprimer": function () {
					$(this).dialog("close");
					$.ajax({
						url: "<?php print dol_buildpath('linesfromproductmatrix/scripts/interface.php', 1)?>",
						method: "POST",
						dataType: "json",  // format de réponse attendu
						data: {
							idMatrix: idMatrix,
							action: 'deleteMatrix',
						},
						success: function (data) {
							if(!data.error) {
								currentBloc.hide();
								matrixSetMessage($out);
							}else {
								matrixSetMessage(data.error, "error");
							}
						},
						error: function (err) {
							matrixSetMessage(err.responseText, "error");
						}
					})
				},
				"Annuler": function () {
					$(this).dialog("close");
				}
			}

		});
	}

	/**
	 *  création bloc
	 *
	 */
	function createABloc() {
		var $out = '<?php print $langs->trans("saveBlocConfirm") ?>';
		var label = $("#inputPlaceholderEx").val();
		$.ajax({
			url: "<?php print dol_buildpath('linesfromproductmatrix/scripts/interface.php', 1)?>",
			method: "POST",
			dataType: "json",  // format de réponse attendu
			data: {
				label: label,
				action: 'createBloc',
			},
			success: function (data) {
				if(!data.error) {
					$("#inputPlaceholderEx").val("");
					$(".matrix-container").append(data.data);
					matrixSetMessage($out);
				}else {
					matrixSetMessage(data.error, "error");
				}
			},
			error: function (err) {
				matrixSetMessage(err.responseText, "error");
			}
		});
	}

	function matrixSetMessage($out, $type = "success") {
		$.jnotify($out, $type, 3000);
	}

});

