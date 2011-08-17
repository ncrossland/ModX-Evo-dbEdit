<?php

/**
 * module save configuration
 *
 * @package dbEdit Table Editor
 * @version 1.0
 * @author Jelle Jager
 * @copyright 2008 Jelle Jager
 * @license GPL
 *
 * //Permissions!!!
 * //Permissions!!!
 */
	//* debug */ print __LINE__.': '.$dba.'<br />';
	include_once('JSON.php');
	$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);

	function findField($fieldName) {
		global $dbFields;
		if(is_array($fieldName)) {
			foreach($fieldName as $name)
				if(!findField($name)) return false;
			return $fieldName;
		}
		return (isset($dbFields[$fieldName]))?$fieldName:false;
	}

	//Need to do some translating from php (display) to mysql types.
	$allowedFieldTypes = array(
		'string'=>'string',
		'int' => 'number',
		'float' => 'number',
		'double' => 'number',
		'blob' => 'string'
	//	'' => '',
	);
	//* debug */ print __LINE__.': <pre>'.print_r($_REQUEST,true) .'</pre><br />';

	unset($dbConfig);
	//NEED TO DO TYPES!! SEE THROUGH CODE for supported types!!!

	//check if we have a valid json
	if( !isset($_POST['json_data']) ) die('No data to process!');
	$dbConfig = $json->decode($_POST['json_data']);
	if( !$dbConfig ) die('No data or invalid configuration data found.!');

	//get fields info from database
	$GLOBALS['dbFields'] = $modx->db->getTableMetaData( $dbConfig['tableName'] );
	global $dbFields;
	if(!$dbFields) die('Could not verify table data.');

	if(!is_array($dbConfig) || !isset($dbConfig['tableName'])) {
		$ok=false;
	} else {
		//do some validation...
		foreach( $dbConfig['fields'] as $fld_name => $props ) {
			//check that field exists in table
			$ok = (isset($dbFields[$fld_name]))?1:0;
			//check that (db) data type is the same (otherwise someone is tampering?)
			$l = strlen($props['dbtype']);
			$ok = ( $ok && $props['dbtype']==substr($dbFields[$fld_name]['Type'],0,$l))?1:0;
			//check that (display) data type is appropriate?
			//HOW?? What datatypes? Especially with bindings!
			//* debug */ print __LINE__.': <pre>'.print_r($ok,true) .'</pre><br />';
			//* debug */ print __LINE__.': <pre>'.print_r($props,true) .'</pre><br />';
			
			//check that default values are appropriate
			if($ok && !empty($props['default']) ) {
				if ($props['type']=='date' || $props['type']=='time') {
					# date/time field, check for invalid default
					$t = strtotime($props['default']);
					$ok = ( ($t!=-1 && $t!=false) ) ?1:0;
				}
				//* debug */ print __LINE__.': <pre>'.print_r($ok,true) .'</pre><br />';
				
				if($ok && in_array(strtoupper($props['type']),$modx->db->dataTypes['numeric'])) {
					# numeric field, check for non-numeric default
					$ok = ( !is_numeric($props['default']) )?0:1;
				}
				//* debug */ print __LINE__.': <pre>'.print_r($ok,true) .'</pre><br />';
			}
			//* debug */ print __LINE__.': <pre>'.print_r($ok,true) .'</pre><br />';
		}
	}

	if($ok) {
		//need to validate all values here!!
		$insertFields['name'] = $modx->db->escape($dbConfig['title']);
		$insertFields['comment'] = $modx->db->escape($dbConfig['description']);
		$insertFields['config'] = $modx->db->escape(serialize($dbConfig));
		//update or new?
	}
	
	if ($dba==109) {
		$ok = $modx->putIntTableRow($insertFields,$dbe_config_table)?1:0;
	} elseif( ($dba==119 || $dba==110) && !empty($db_id) ) {
		$ok = $modx->updIntTableRow($insertFields,$dbe_config_table,"recID=".$db_id)?1:0;
	} else {
		$msg = "Wrong save action encountered!<br />";
	}

	if ($ok) {
		$msg .= "Configuration for \"".$dbConfig['moduleName']."\" saved succesfully.";
		$_SESSION['dbedit_message'] = array('succes',$msg);
		header("location: index.php?a=".$_REQUEST['a']."&id=".$module_id);
		exit;
	} else {
		//* debug */ print __LINE__.': <pre>'.print_r($dbConfig,true) .'</pre><br />';
		$msg .=  "Unable to save configuration. Reason is: <br />".$modx->db->getLastError();
	}

//somehow make this possible???
//$dbConfig['list']['Select'] = "recID,concat(FirstName,\" \",LastName) as FullName,artistCode,Type";
//$dbConfig['list']['Where'] = "";

include($basePath.$mod_path.'/header.inc.php');
?>

<div class="subTitle">
	<span class="right"><em>db</em>Edit Table Editor</span>
</div>
<div class="sectionHeader"><img src='media/images/misc/dot.gif' alt="." />&nbsp;Save Configuration</div><div class="sectionBody">
<?php print $msg; ?>
<ul>
	<li><a href="<?php echo $moduleHomeUrl; ?>">Return to <?php echo $mod_name; ?> module</a></li>
</ul>
</div>
<?php include($basePath.$mod_path.'/footer.inc.php'); ?>