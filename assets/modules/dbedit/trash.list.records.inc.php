<?php
/**
 * List contents of trash bin.
 *
 * @package dbEdit Table Editor
 * @version 1.0
 * @author Jelle Jager
 * @copyright 2008 Jelle Jager
 * @license GPL
 *
 */
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

foreach($dbConfig['fields'] as $fldname => $props){
	if($props['use']) $select_fields[] = $fldname;
	if($props['list']){
		$col_fields[] = $fldname;
		$col_headers[] = $props['heading']? $props['heading']:$fldname;
	}
	if($props['linked']) $linked_fields[] = $fldname;

}
	$sql_fields = implode(',',$select_fields);
	$ds = $modx->db->select($sql_fields, $dbConfig['tableName'],$dbConfig['deletedField']."='".$dbConfig['deletedValue']."'",str_replace('=',' ',$dbConfig['sort']));

	include_once $manager_path."includes/controls/datagrid.class.php";

	$col_count = count($col_fields);

	$grd = new DataGrid('',$ds,25); // page size needs to be setting!
	$grd->noRecordMsg = $_lang["no_records_found"];
	$grd->cssClass="grid";
	$grd->columnHeaderClass="gridHeader";
	$grd->itemClass="gridItem";
	$grd->altItemClass="gridAltItem";
	$grd->fields= implode(',',$col_fields).',del';
	$grd->columns = implode(',',$col_headers);
	$grd->colTypes ="template:<input type='checkbox' name='chk[]' value='[+value+]' />&nbsp;[+value+]";
	$grd->colWidths="55".str_repeat(',',$col_count);

	// render grid
	$html = $grd->render();
?>

	<script language="JavaScript" type="text/javascript">
	function cancelEdit(){
		document.location.href = '<?php echo $dbeHomeUrl ?>';
	}

	function restoreRecords(){
		f = $('mutate');
		if(f){
			f.ra.value = "restore";
			f.submit();
		}
	}

	function purgeRecords(){
		if(confirm("Are you sure you want permanently remove these reords?" )==false) return;
		f = $('mutate');
		if(f){
			f.ra.value = "purge";
			f.submit();
		}
	}
	</script>
	<form name="mutate" id="mutate" method="post" action="index.php?id=<?php echo $module_id; ?>">
	<input type="hidden" name="a" value="<?php echo $_REQUEST['a']; ?>">
	<input type="hidden" name="id" value="<?php echo $module_id; ?>">
	<input type="hidden" name="db" value="<?php echo $db_id; ?>">
	<input type="hidden" name="ra" value="">
    
    <h1><?php echo $mod_name; ?></h1>
    <div id="actions">
        <ul class="actionButtons">
            <li id="Button1">
                <a onclick="restoreRecords();">
				<img src="media/style/<?php echo $manager_theme; ?>images/icons/save.png" alt=""> Restore</a>
            </li>
            <li id="Button2">
               	<a onclick="purgeRecords();">
                <img src="media/style/<?php echo $manager_theme; ?>images/icons/delete.gif" align="absmiddle"> Remove Permanently</a>
            </li>
            <li id="Button3">
               	<a onclick="cancelEdit();">
                <img src="media/style/<?php echo $manager_theme; ?>images/icons/cancel.png" align="absmiddle"> Cancel</a>
            </li>
        </ul>
    </div>

	<div class="sectionHeader">Trash bin for <?php echo $dbConfig['title'];?></div><div class="sectionBody">
			<p style="margin-bottom:15px;">Below is a listing of all records from "<?php echo $dbConfig['moduleName']; ?>" that have previously been deleted. You can select records using the checkboxes on their left. Selected records can either be restored or permanently be removed from the database.</p>
	<?php echo $html; ?>
	<input type="submit" name="save" style="display:none;">
	</div>
	</form>