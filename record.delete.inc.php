<?php
 /**
 * Delete or disable records.
 *
 *
 * @package dbEdit Table Editor
 * @version 1.0
 * @author Jelle Jager
 * @copyright 2008 Jelle Jager
 * @license GPL
 *
 */
if( isset($dbConfig['deletedField']) ){

	unset($newValues);
	$newValues[$dbConfig['deletedField']] = (isset($dbConfig['deletedValue']))?$dbConfig['deletedValue']:1;
	//add quotes if rn is not a number
	if(!is_numeric($rn)) $rn = "'".$rn."'";
	if( !$modx->db->update( $newValues,$dbConfig['tableName'],$dbConfig['keyField']."=".$rn) ){
		$msg = "<h4>An Error occurred!</h4>";
		$msg = "<p>Unable to move record to the trash bin. If this error occurs repeatedly please contact the support team or check your table configuration.</p>";
		$ok = false;
	}else{
		$msg = "The record has been moved to the trash bin.";
		$ok = true;
	}
}else{//delete permanently
	//add quotes if rn is not a number
	if(!is_numeric($rn)) $rn = "'".$rn."'";
	if( !$modx->db->delete( $dbConfig['tableName'],$dbConfig['keyField']."=".$rn) ){
		$msg = "<h4>An Error occurred</h4>";
		$msg .= "<p>Unable to delete record. If this error occurs repeatedly please contact the support team or check your table configuration.</p>";
		$ok=false;
	}else{
		$msg = "The record was permanently deleted.";
		$ok = true;
	}
}
if($ok){
	clearCache();
	$_SESSION['dbedit_message'] = array('succes',$msg);
	header("location: index.php?a=".$_REQUEST['a']."&id=".$module_id.'&db='.$db_id);
	exit;
}
?>
<h1><?php echo $mod_name; ?></h1>

<div class="sectionHeader"><img src='media/images/misc/dot.gif' alt="." />&nbsp;Delete Records</div><div class="sectionBody">
<?php print $msg; ?>
<ul>
	<li><a href="index.php?a=<?php echo $_REQUEST['a']; ?>&id=<?php echo $module_id; ?>&db=<?php echo $db_id; ?>&ra=opentrash">View the Trash bin</a></li>
	<li><a href="index.php?a=<?php echo $_REQUEST['a']; ?>&id=<?php echo $module_id; ?>&db=<?php echo $db_id; ?>">Return to Manage Database</a></li>
</ul>

</div>
