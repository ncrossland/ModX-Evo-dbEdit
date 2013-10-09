<?php
/**
 * Export data to PDF for dbEdit Table Editor module.
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

if (isset($_GET['export']) && isset($_GET['row'])) {
	$rowId = intval($_GET['row']);

	// Connect to MODX database.
	$link = mysql_connect($database_server, $database_user, $database_password);
	if (!$link) {
		die('Could not connect: ' . mysql_error());
	}

	// Get fieldnames
	mysql_select_db(trim($dbase, '`'));
	$db_id = intval($_GET['export']);
	$result = mysql_query('SELECT * FROM ' . $table_prefix . 'dbedit_configs WHERE recID=' . $db_id);
	$dbConfig = mysql_fetch_array($result, MYSQL_ASSOC);

	if (!$dbConfig) {
		die('<h1>ERROR:</h1><p>Table does not exist!</p>');
	}

	$dbConfig['config'] = unserialize($dbConfig['config']);
	//die('<pre>' . print_r($dbConfig, true));

	mysql_select_db(trim($dbase, '`'));

	// Get data records from table.
	$export = mysql_real_escape_string(strip_tags($_GET['export']));
	$where = ' WHERE id=' . $rowId . ' ';

	// Don't export files with MODX table prefix (removed)
	if (TRUE) {
		$result = mysql_query('SELECT * FROM ' . $dbConfig['config']['tableName'] . $where);
		$exportTable = array();
		$row = mysql_fetch_array($result, MYSQL_ASSOC);

		// build pdf
		$rows = ''; //initialize vars
		foreach ($row as $key => $value) {
			if ($key == 'id' || (isset($dbConfig['config']['deletedField']) && $key == $dbConfig['config']['deletedField'])) {
				continue; //skip fields
			}
			if ($dbConfig['config']['fields'][$key]['use']) {
				$rows .= '<p><b>' . $dbConfig['config']['fields'][$key]['heading'] . ':</b><br>' . str_replace("\n", '<br>', $value) . '</p>';
			}
		}
		if (isset($dbConfig['config']['settings']['pdf_export_below'])) {
			$rows .= '<p>' . $dbConfig['config']['settings']['pdf_export_below'] . '</p>';
		}
		require 'pdf/dbedit_pdf.class.inc.php';
		$pdf = new dbedit_PDF('P', 'mm', 'A4');
		$pdf->AddPage();
		$pdf->AddText($rows);
		$output = $pdf->Output('', 'S');
		$pdf->Close();

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="' . $dbConfig['name'] . ' Message ' . $rowId . '.pdf"');
		header('Content-Transfer-Encoding: binary ');
		echo $output;
	}
}
ob_end_flush();
?>