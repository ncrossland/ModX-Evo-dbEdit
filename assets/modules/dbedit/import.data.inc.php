<?php
/**
 * Import data processor
 * handles simple cvs data (to some extend)
 *
 * @package dbEdit Table Editor
 * @version 1.0
 * @author Jelle Jager
 * @copyright 2008 Jelle Jager
 * @license GPL
 *
 * @todo create code for purge table before import
 * @todo move $tpl outside file for multilanguage use
 */
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
/*
if(!dbeHasPermission('import_table_data',$modx)){
	$modx->webAlert('You do not have permissions for this operation',"$moduleHomeUrl");
	exit;
}
//*/
	if($_SERVER['REQUEST_METHOD']=='POST'){
#need to get $tpl out of this file	so it can be edited for language separatedly
		$tpl = <<<EOD
		
		<h1><?php echo $mod_name; ?></h1>
		<div id="actions">
			<ul class="actionButtons">
				<li id="Button1">
					<a onclick="window.location.href = '{$moduleHomeUrl}';">
					<img src="media/style/{$manager_theme}images/icons/cancel.png" />&nbsp;Return</a>
				</li>
				<li id="Button2">
					<a onclick="document.location.href='index.php?a=112&id=<?php echo $module_id; ?>&dba=107';">
					<img src="media/style/<?php echo $manager_theme; ?>images/icons/add.png"  /> Add New Table</a>
				</li>
			</ul>
		</div>

<div class="sectionHeader">{$_lang['dbe_import_data']} &raquo; [+moduleName+]</div><div class="sectionBody">
		<p>[+importCount+] records succesfully imported from a total of [+totalRecords+] records found.</p>
		<p>[+errCount+] records were skipped due to errors. For a listing of errors see below:</p>
		<p><textarea rows="20" style="width:99%;" name="errorData">[+errorMessages+]</textarea></p>
	</div>
EOD;

		if(trim($_REQUEST['importData'])==''){
			$dbMessage = "<p style=\"color:red;font-weight:bold;\">".$_lang['dbe_import_nodata']."</p>";
			include('import.form.inc.php');
			exit;
		}

		$placeholders['moduleName'] = $dbConfig['title'];

		//parse the imported data
		$importData = explode( "\n" ,$_REQUEST['importData']);
		if($_REQUEST['hasHeaderRow']) $headerRow = array_shift($importData);

		//skip any fields that are set to 0 (by flipping the array twice)
		$fieldOrder = array_flip($_REQUEST['frmFields']);
		unset($fieldOrder[0]);
		$fieldOrder = array_flip($fieldOrder);

		$tableName = $dbConfig['tableName'];
		//get fields info from database
		$dbFieldProps = $modx->db->getTableMetaData($tableName);

		//preset some counter vars
		$recCount = count($importData);
		$importCount = $emptyCount = $errCount = 0;

	//process each line
	foreach($importData as $lineNum => $line){

		if(trim($line)==''){
			$emptyCount++;
			continue;
		}
		$fieldData = explode(",",$line);
		array_unshift($fieldData,"");
//add dummy value to adjust column index
		$fieldString = '';
		$valueString = '';
		foreach($fieldOrder as $fld => $key){
			$value = $fieldData[$key];
			$fieldString .= ($fieldString)?',':'';
			$valueString .= ($valueString)?',':'';
			unset($err);
			//get basic mysql field type
			preg_match('/^[a-z]*/i',$dbFieldProps[$fld]['Type'],$matches);
			$dbFldType = strtoupper($matches[0]);
			//need quotes for string values
			$qt = ( in_array($dbFldType,$modx->db->dataTypes['string']) )?"'":"";
			if( in_array($dbFldType,$modx->db->dataTypes['numeric']) && !is_numeric($value) ){
				$err = "line {$lineNum}: '{$fld}' (key {$key}): '{$value}'  is not numeric (perhaps the record contains a comma in the description?)"."\n".$line."\n\n";//mergePLcontent($_lang['dbe_import_non_numeric'], array('line'=>$lineNum,'field'=>$fld,'key'=>$key))."\n";
				break;
			}elseif( in_array($dbFldType,$modx->db->dataTypes['date']) ){
				//We're not concerned if a date is actually valid as long as it can be stored in DB as a date
				//so if $value is numeric we'll store it though the date might be bogus
				if(!is_numeric($value) && ($value = strtotime($value)) === -1 ){
					$err = "line {$lineNum}: '{$fld}' (key {$key}) is not a valid date." ."\n".$line."\n\n"; mergePLcontent($_lang['dbe_import_non_date'], array('line'=>$lineNum,'field'=>$fld,'key'=>$key)) . "\n";
					break;
				}else{
					$value = $modx->db->prepareDate($value,$dbFieldType);
				}
			}
			//add quotes if needed and escape value
			$value = $qt.$modx->db->escape(trim($value)).$qt;
			//add to sql vars
			$fieldString .= $fld;
			$valueString .= $value;

		}

		if(isset($err)){
			$errors .= $err;
			$errCount++;
			unset($err);
			continue;
		}

		//try to save record
		$sql = "{$update_string} INTO $tableName (" . $fieldString . ") VALUES(" . $valueString . ")";
        if (empty ($modx->db->conn) || !is_resource($modx->db->conn)) {
            $modx->db->connect();
        }
        if (!$result = $modx->db->query($sql)) {
            $errors .= "record # $lineNum: " . $modx->db->getLastError() . "\n";
			$errCount++;
        } else {
            $importCount++;
        }
	}

	//report results
	$placeholders['homeHref']=$moduleHomeUrl;
	$placeholders['importCount']=$importCount;
	$placeholders['errCount'] = ($errCount)?$errCount:'No';
	$placeholders['errorMessages'] = $errors;
	$placeholders['totalRecords'] = ($recCount - $emptyCount);
	print mergePLcontent($tpl,$placeholders);

}else{
	// nothing posted - show form
	include('import.form.inc.php');
}
?>
