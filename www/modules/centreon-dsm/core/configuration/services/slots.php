<?php
/*
 * Copyright 2005-2015 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

	if (!isset ($oreon)) {
		exit ();
	}

	isset($_GET["pool_id"]) ? $cG = $_GET["pool_id"] : $cG = NULL;
	isset($_POST["pool_id"]) ? $cP = $_POST["pool_id"] : $cP = NULL;
	$cG ? $slot_id = $cG : $slot_id = $cP;

	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

	isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = NULL;
	isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = NULL;
	$cG ? $dupNbr = $cG : $dupNbr = $cP;

	$search = isset($_POST['searchSlot']) ? htmlentities($_POST['searchSlot'], ENT_QUOTES) : NULL;

    /*
	 * Path to the configuration dir
	 */
	$path = "./modules/centreon-dsm/core/configuration/services/";

	/*
	 * PHP functions
	 */
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	switch ($o)	{
		case "a" : require_once($path."formSlot.php"); break; #Add a slot
		case "w" : require_once($path."formSlot.php"); break; #Watch a slot
		case "c" : require_once($path."formSlot.php"); break; #Modify a slot
		case "s" : enablePoolInDB($slot_id); require_once($path."listSlot.php"); break; #Activate a slot
		case "ms" : enablePoolInDB(NULL, isset($select) ? $select : array()); require_once($path."listSlot.php"); break;
		case "u" : disablePoolInDB($slot_id); require_once($path."listSlot.php"); break; #Desactivate a slot
		case "mu" : disablePoolInDB(NULL, isset($select) ? $select : array()); require_once($path."listSlot.php"); break;
		case "m" : multiplePoolInDB(isset($select) ? $select : array(), $dupNbr); require_once($path."listSlot.php"); break; #Duplicate n slots
		case "d" : deletePoolInDB(isset($select) ? $select : array()); require_once($path."listSlot.php"); break; #Delete n slots
		default : require_once($path."listSlot.php"); break;
	}
?>
