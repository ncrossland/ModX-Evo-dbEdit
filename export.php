<?php
/**
 * Export data to CSV for dbEdit Table Editor module.
 *
 * @package dbEdit Table Editor
 * @version 1.0
 * @author Thomas Jakobi
 * @copyright 2011 Thomas Jakobi
 * @license GPL
 *
 */

// Headers already set in manager/index.php so the way with MODX config has to be used to provide file download
ob_start();
require '../../../manager/includes/config.inc.php';

session_name($site_sessionname);
session_start();

if ($_SESSION['mgrValidated'] != 1) {
	die('<h1>ERROR:</h1><p>Please use the MODx Content Manager instead of accessing this file directly.</p>');
}

if (isset($_GET['export'])) {

	// Connect to MODX database.
	$link = mysql_connect($database_server, $database_user, $database_password);
	if (!$link) {
		die('Could not connect: '.mysql_error());
	}
	mysql_select_db(trim($dbase, '`'));

	// Get data records from table.
	$export = mysql_real_escape_string(strip_tags($_GET['export']));
	$where = isset($_SESSION['dbe_where_sql']) ? ' WHERE '.$_SESSION['dbe_where_sql'].' ' : '';

	// Don't export files with MODX table prefix
	if (strstr($export, $table_prefix) != $export) {
		$result = mysql_query('SELECT * FROM '.$export.$where.' ORDER BY id ASC');

		$exportTable = array();
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$exportTable[] = $row;
		}

		// Prepare CSV
		if (count($exportTable)) {
			$keys = array_keys($exportTable[0]);
			$output = implode(',', $keys)."\r\n";
			foreach ($exportTable as $row) {
				foreach ($row as $key => $value) {
					$row[$key] = str_replace('"', '""', $value);
				}
				$output .= '"'.implode('","', $row).'"'."\r\n";
			}
		} else {
			$output = '';
		}

		// Header for file download
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename='.$export.'.csv ');
		header('Content-Description: CSV Export');
		header('Content-Transfer-Encoding: binary ');
		echo $output;
	} else {
		echo 'You are not allowed to export tables with '.$table_prefix.' prefix.';
	}
}
ob_end_flush();
?>