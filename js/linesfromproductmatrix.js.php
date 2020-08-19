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


	/**
	 * Création des blocs en renseignant le label
	 */
	// START OF CREATE A BLOC EVENTS
	$(document).on("click", ".btnTitle", function (e) {
		e.preventDefault();
		var textarea = $("#inputPlaceholderEx");
		$(".label-form").show();
		textarea.select();
	});

	$(document).on("keypress", "#inputPlaceholderEx", function (e) {
		if (e.which == 13) {
			createABloc();
		}
	});

	$(document).on("click", ".create-button", createABloc);
	// END OF CREATE BLOC EVENTS


	// DELETE A BLOC AND HIS CHILDREN
	/**
	 * DELETE BLOCK
	 *
	 */
	$(document).on("click", ".pictodelete", deleteConfirmation);


	// DELETE A LINE
	/**
	 * DELETE LINE
	 *
	 */
	$(document).on("click", ".fas.fa-trash.deleteHead", function () {
		var $out = "Suppression réalisée avec succès";
		var currentHead = $(this).parent().data("id");
		var currentType = $(this).parent().data("type");
		if (currentType == 1) {var currentDiv = $(this).parent().parent().parent();}

		$("#deleteHead-confirm").dialog({
			resizable: true,
			height: "auto",
			width: 400,
			modal: true,
			buttons: {
				"Supprimer": function () {
					$(this).dialog("close");
					$.ajax({
						url: "scripts/interface.php",
						method: "POST",
						dataType: "json",  // format de réponse attendu
						data: {
							currentHead: currentHead,
							currentType: currentType,
							action: 'deleteHead',
						}
					})
						.done(function () {
							currentDiv.hide();
							matrixSetMessage($out);
						});
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
	$(document).on("click", ".fa-grip-lines", function () {
		var $currentBloc = $(this);
		//var $currentTable = $currentBloc.parent().prev());
		var idBloc = $(this).data("id");
		var blocheadType = $(this).data("type");
		$.ajax({
			url: "scripts/interface.php",
			method: "POST",
			dataType: "json",  // format de réponse attendu
			data: {
				id: idBloc,
				action: 'addHeaderMatrix',
				blocheadType: blocheadType
			}
		})
			.done(function () {
				location.reload();
			});
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
			url: "scripts/interface.php",
			method: "POST",
			dataType: "json",  // format de réponse attendu
			data: {
				id: idBloc,
				action: 'updateLabelBloc',
				label: labelBloc
			}
		})
			.done(function () {

				parentBlocTitle.css("background-color", "green");
				setTimeout(function () {
					parentBlocTitle.css("background-color", "white");
				}, 700)
				var pencilToShow = self.next().children(".fa-pencil-alt");
				var check = self.next().children(".fa-check");

				check.toggle(0);
				pencilToShow.toggle(0);

			});
	});


	/**
	 * Modification des entêtes label HEADERS
	 */
	$(document).on("change", ".inputBlocHeader", function () {

		let idBlocHead = $(this).data("idhead");  // On récupère l\'id de l\'input
		let self = $(this);
		let label = $(this).val();
		var parentBlocTitle = $(this).closest("div");

		parentBlocTitle.css("background-color", "green");

		let data =
			{
				idhead: idBlocHead,
				label: label,
				action: "updatelabelHeader"
			}
		$.ajax({
			url: "scripts/interface.php",
			method: "POST",
			dataType: "json",
			data: data
		})
			.done(function () {
				parentBlocTitle.css("background-color", "green");
				setTimeout(function () {
					parentBlocTitle.css("background-color", "white");
				}, 700)
			});
	});

	/**
	 * Modification des PRODUITS
	 */
	$(document).on("change", "select", function () {

		let bhc = $(this).data("blocheadercolid");
		let bhr = $(this).data("blocheaderrowid");
		let bid = $(this).data("blocid");
		let idproduct = $(this).val();
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
			url: "scripts/interface.php",
			method: "POST",
			dataType: "json",
			data: data
		})
			.done(function () {
				self.css("background-color", "green");
				setTimeout(function () {
					self.css("background-color", '#fff');
				}, 800);
			});


	});

	$(document).on("change", ".inputproductmatric", function () {
		alert($(this).data("idproduct"));
		let bhc = $(this).data("blocheadercolid");
		let bhr = $(this).data("blocheaderrowid");
		let idProduct = $(this).data("idproduct");
		let bid = $(this).data("blocid");
		let self = $(this);
		console.log(bhc + '--' + bhr + '--' + bid + '--' + idProduct)
		let data =
			{
				id: bid,
				blocheadercolid: bhc,
				blocheaderrowid: bhr,
				idproduct: idProduct,
				action: "InputproductMatric"
			}

		$.ajax({
			url: "scripts/interface.php",
			method: "POST",
			dataType: "json",
			data: data
		})
			.done(function () {
				self.css("background-color", "green");
				setTimeout(function () {
					self.css("background-color", '#fff');
				}, 800);
			});


	});




	/**
	 *  delete confirmation
	 */
	function deleteConfirmation() {
		var $out = "Le bloc a bien été supprimé";
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
						url: "scripts/interface.php",
						method: "POST",
						dataType: "json",  // format de réponse attendu
						data: {
							idMatrix: idMatrix,
							action: 'deleteMatrix',
						}
					})
						.done(function () {
							currentBloc.hide();
							matrixSetMessage($out);
						});
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
		var $out = "Le bloc a bien été enregistré";
		var label = $("#inputPlaceholderEx").val();
		$.ajax({
			url: "scripts/interface.php",

			method: "POST",

			dataType: "json",  // format de réponse attendu

			data: {
				label: label,
				action: 'createBloc',
			},

			success : function(data) {
				if (!data.error){
							$(".label-form").hide();
							$(".matrix-container").append(data.data);
							matrixSetMessage($out);
						}else{
							matrixSetMessage(data.error,'error')
						}
			},
			error :function() {},
		})
		//.done(function (data) {
		//	if (!data.error){
		//		$(".label-form").hide();
		//		$(".matrix-container").append(data.data);
		//		matrixSetMessage($out);
		//	}else{
		//		matrixSetMessage(data.error,'error')
		//	}
		//
		//});

	}

	function matrixSetMessage($out,$type ='success') {
		$.jnotify($out,$type, 2000);
	}

});

