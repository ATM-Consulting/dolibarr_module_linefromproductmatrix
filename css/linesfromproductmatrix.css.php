<?php
/* Copyright (C) 2020 SuperAdmin
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    linesfromproductmatrix/css/linesfromproductmatrix.css.php
 * \ingroup linesfromproductmatrix
 * \brief   CSS file for module LinesFromProductMatrix.
 */

//if (! defined('NOREQUIREUSER')) define('NOREQUIREUSER','1');	// Not disabled because need to load personalized language
//if (! defined('NOREQUIREDB'))   define('NOREQUIREDB','1');	// Not disabled. Language code is found on url.
if (!defined('NOREQUIRESOC'))    define('NOREQUIRESOC', '1');
//if (! defined('NOREQUIRETRAN')) define('NOREQUIRETRAN','1');	// Not disabled because need to do translations
if (!defined('NOCSRFCHECK'))     define('NOCSRFCHECK', 1);
if (!defined('NOTOKENRENEWAL'))  define('NOTOKENRENEWAL', 1);
if (!defined('NOLOGIN'))         define('NOLOGIN', 1); // File must be accessed by logon page so without login
//if (! defined('NOREQUIREMENU'))   define('NOREQUIREMENU',1);  // We need top menu content
if (!defined('NOREQUIREHTML'))   define('NOREQUIREHTML', 1);
if (!defined('NOREQUIREAJAX'))   define('NOREQUIREAJAX', '1');

session_cache_limiter('public');
// false or '' = keep cache instruction added by server
// 'public'  = remove cache instruction added by server
// and if no cache-control added later, a default cache delay (10800) will be added by PHP.

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) { $i--; $j--; }
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/../main.inc.php")) $res = @include substr($tmp, 0, ($i + 1))."/../main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

// Load user to have $user->conf loaded (not done by default here because of NOLOGIN constant defined) and load permission if we need to use them in CSS
/*if (empty($user->id) && ! empty($_SESSION['dol_login']))
{
	$user->fetch('',$_SESSION['dol_login']);
	$user->getrights();
}*/


// Define css type
header('Content-type: text/css');
// Important: Following code is to cache this file to avoid page request by browser at each Dolibarr page access.
// You can use CTRL+F5 to refresh your browser cache.
if (empty($dolibarr_nocache)) header('Cache-Control: max-age=10800, public, must-revalidate');
else header('Cache-Control: no-cache');

?>


input.inputBloc {
	font-size: 1.1em;
	width: 30%;
}

.matrix-wrap {
	display: block;
	margin-top: 50px;
	margin-bottom: 25px;
	margin-left: 10px;
}
.matrix-container {
	display: flex;
	flex-direction: row;
	flex-wrap: wrap;
}
.matrix-head {
	border-bottom: 1px solid #e0e0e0;
	transition: 0.5s ease-in-out;
	padding: 8px;
}
.matrix-head input.inputBloc {
	font-weight: bold;
	min-width: 250px;
}



.matrix-item {
	flex-shrink: 1;
	flex-basis: auto;
	width: auto;
	margin-bottom: 25px;
	margin-right: 15px;
	box-shadow: 1px 1px 3px rgba(0,0,0, 0.5);
	/*border: 1px solid #dedede;*/
}

.matrix-line-delete, .matrix-col-delete{
	opacity: 0;
}
.bloc-table-row:hover .matrix-line-delete,.bloc-table-head:hover .matrix-col-delete{
	opacity: 1;
}

.pictodelete {
	padding-top: 6px;
	padding-right: 10px;
}

.editfielda {
	margin-left: 10px;
	padding-right: 15px;
}

/*Matrice*/
.bloc-table{
	display: table;
	width: 100%;
}

.bloc-table-row {
	display: table-row;
}

.bloc-table-row:hover:not(:first-child) {
	background:  var(--colorbacklinepairhover) !important;
}

.bloc-table-cell, .bloc-table-head {
	border-left: 1px solid #999999;
	border-bottom: 1px solid #999999;
	display: table-cell;
	padding: 5px 5px;
}

.bloc-table-cell:first-child{
	border-left: none;
}



.bloc-table-head {
	background-color: #EEE;
	font-weight:bold;
}

.bloc-label{
	font-weight: bold;
}


.matrix-footer {
	opacity: 0;
}
.matrix-item:hover .matrix-footer {
	opacity: 1;
}
.bloc-table-cell.bloc-table-head {
	text-align: center;
}


.matrix-add-btn:hover{
	cursor: pointer;
	color:  var(--colortextlink) ;
}
.matrix-add-btn{

}

.classfortooltip.inputNumber{
	float: right;
	text-align: right;
	margin-left: 30px;
	width: 50%;

}
.fa-grip-lines.--rotate90neg{
	transform: rotate(-90deg);
}

.input-bloc-header{
	background: rgba(255,255,255,0.1);
	width: calc(100% - 40px);
}
