<?php
 /**
 * Delete table configuration processor
 *
 * @package dbEdit Table Editor
 * @version 1.0
 * @author Jelle Jager
 * @copyright 2008 Jelle Jager
 * @license GPL
 *
 */
	if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
	//use permissions as for modules
	if(!$modx->hasPermission('delete_module')) {
		$e->setError(3);
		$e->dumpError();
	}
	if($dbConfig){
			//try to delete configuration
			$table = $dbe_config_table;
			$where = "recID=$db_id";
			if( $modx->db->delete($modx->getFullTableName($dbe_config_table),$where) ){
				$msg = "Configuration for table \"{$dbConfig['moduleName']}\" succesfully removed.";
				$ok=true;
			}else{
				$msg = "<p>Unable to remove table configuration for " . $dbConfig['title']. "</p>";
				$ok==false;
			}
	}else{
		$msg = "<p>No table configuration specified!</p>";
		$ok==false;
	}
	if($ok){
		$_SESSION['dbedit_message'] = array('succes',$msg);
		header("location: $moduleHomeUrl");
		exit;
	}

	include($basePath.$mod_path.'/header.inc.php');
?>

<h1><?php echo $mod_name; ?></h1>
<div id="actions">
	<ul class="actionButtons">
		<li id="Button1">
			<a onclick="document.location.href='<?php echo $moduleHomeUrl; ?>';">
			<img src="media/style/<?php echo $manager_theme; ?>images/icons/cancel.png" alt="return"> Return</a>
		</li>
	</ul>
</div>

<div class="sectionHeader"><img src='media/style/<?php echo $manager_theme; ?>images/misc/dot.gif' alt="." />Delete Table Configuration</div>
<div class="sectionBody">
<?php echo $msg; ?>
<ul>
	<li><a href="index.php?a=<?php echo $_REQUEST['a']; ?>&id=<?php echo $module_id; ?>">Return to dbEdit module</a></li>
</ul>
</div>
<?php
		include($basePath.$mod_path.'/footer.inc.php');
?>