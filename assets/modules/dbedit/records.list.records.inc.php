<?php
/**
 * List records for dbEdit module.
 *
 * @package dbEdit Table Editor
 * @version 1.0
 * @author Jelle Jager
 * @copyright 2008 Jelle Jager
 * @license GPL
 *
 * @todo Add Language vars
 * @todo General code cleanup
 */
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

	if(!isset($dbConfig) || empty($dbConfig['fields'])){
		exit("<p>Error: No table information!</p>");
	}

	//initialize some vars
	$key_field = $dbConfig['keyField'];
	$where_sql = $search_sql = '';

	//prepare status message to user
	if( isset($_SESSION['dbedit_message'])){
		$status_message = "<div class=\"dbe-message-{$_SESSION['dbedit_message'][0]}\">".$_SESSION['dbedit_message'][1].'</div>';
	}
	unset($_SESSION['dbedit_message']);

	//prepare 'deleted' filter
	if( !empty($dbConfig['deletedField']) )
		$where_sql = $dbConfig['deletedField']."='".$dbConfig['enabledValue']."'";

	//add filter form configuration
	if(isset($dbConfig['filter']) && !empty($dbConfig['filter']) )
		$where_sql .= (!empty($where_sql)) ? ' AND '. $dbConfig['filter'] : $dbConfig['filter'];

	//clear search filter session if requested
	if( ($_REQUEST['dbe_fld']!='' && $_REQUEST['dbe_filter']=='') || $_REQUEST['cf']==1)
		unset($_SESSION['dbe_search'][$db_id]);
	//get stored search filter data
	if(!empty($_SESSION['dbe_search'][$db_id])){
		$_REQUEST['dbe_fld'] = $_REQUEST['dbe_fld'] ? $_REQUEST['dbe_fld']:$_SESSION['dbe_search'][$db_id][0];
		$_REQUEST['dbe_filter'] = $_REQUEST['dbe_filter'] ? $_REQUEST['dbe_filter']:$_SESSION['dbe_search'][$db_id][1];
	}

	//prepare search filter
	if(!empty($_REQUEST['dbe_fld']) && !empty($_REQUEST['dbe_filter'])){
		$_SESSION['dbe_search'][$db_id] = array($_REQUEST['dbe_fld'],$_REQUEST['dbe_filter']);
		$search_sql .= $modx->db->escape(trim($_REQUEST['dbe_fld'])) . " LIKE '%" . $modx->db->escape(trim($_REQUEST['dbe_filter'])) . "%'";
		$filter_message = "<span class='dbe-active-filter'>Filter active! Not all records may be visible. <a href=\"{$dbeHomeUrl}&cf=1\">clear filter</a></span>";
	}


	//Use custom select sql
	if(!empty($dbConfig['settings']['select_sql'])){
		//ensure the WHERE and HAVING keywords are inserted when necessary
		if(!empty($where_sql))
			$where_sql = ((!strpos($dbConfig['settings']['select_sql'],' WHERE '))?' WHERE ':' AND ').$where_sql;
		if( !empty($search_sql) )
			$search_sql = ((!strpos($dbConfig['settings']['select_sql'],' HAVING '))?' HAVING ':' AND ').$search_sql;
		//add filter and search to query
		$sql = str_replace(array('{WHERE}','{FILTER}'),array($where_sql,$search_sql),$dbConfig['settings']['select_sql']);
		//get db resource
		$ds = $modx->db->query($sql);

		//prepare datagrid properties
		if( $rw = $modx->db->getRow($ds) ){
			$col_fields=array_keys($rw);
			unset($col_headers);
			foreach($col_fields as $cval) {
				$col_headers[]= ucwords(str_replace('_',' ',$cval));
				$select_fields[$cval] = ucwords(str_replace('_',' ',$cval));
			}
			@mysqli_data_seek($ds,0); //reset to first row
		}
	}else{ //Build resource from dbedit configuration
		//prepare datagrid properties
		foreach($dbConfig['fields'] as $fldname => $props){
			if($props['use']) $select_fields[$fldname] = $props['heading']? $props['heading']:$fldname;
			if($props['list']){
				$col_fields[] = $fldname;
				$col_headers[] = $props['heading']? $props['heading']:$fldname;
			}
		}
		//combine filters into one statement
		if(!empty($search_sql)) $where_sql .= (!empty($where_sql)?' AND ':'') . $search_sql;
		//Make sure the keyfield is included in the query (otherwise links won't work)
		$sql_fields = ((!in_array($dbConfig['keyField'],$col_fields))? $dbConfig['keyField'] .', ' :'') . implode(',',$col_fields);
		//get db resource
		$ds = $modx->db->select($sql_fields, $dbConfig['tableName'] ,$where_sql,str_replace('=',' ',$dbConfig['sort']));
	}
	$_SESSION['dbe_where_sql'] = $where_sql;

	//setup the datagrid
	include_once $manager_path."includes/controls/datagrid.class.php";

	$col_count = count($col_fields);

	$grd = new DataGrid('',$ds,$modx->config['number_of_results']);
	$grd->noRecordMsg       = $_lang["no_records_found"];
	$grd->cssClass          = "grid";
	$grd->columnHeaderClass = "gridHeader";
	$grd->itemClass         = "gridItem";
	$grd->altItemClass      = "gridAltItem";
	$grd->fields            = implode(',',$col_fields).',del';
	$grd->columns           = implode(',',$col_headers).',Delete';
	$grd->colWidths         = ((in_array($dbConfig['keyField'],$col_fields))?"30":'').str_repeat(',',$col_count).'30';
	$grd->colAligns         = str_repeat(',',$col_count).'center';
	if ((isset($dbConfig['settings']['pdf_export']) && $dbConfig['settings']['pdf_export'])) {
		$grd->columns .= ',PDF';
	}
	if (!(isset($dbConfig['settings']['hide_delete']) && $dbConfig['settings']['hide_delete'])) {
		$grd->columns .= ',Delete';
	}
	//compute templates/types
	for($f=0;$f<$col_count;$f++)
			$col_types[] = "template:<a style='display:block;' href=\"".$dbeHomeUrl."&rn=[+".$key_field."+]\" title=\"Click to view record\">[+value+]</a>";

	if ((isset($dbConfig['settings']['pdf_export']) && $dbConfig['settings']['pdf_export'])) {
		$col_types[] = "template:<a href=\"/assets/modules/dbedit/exportpdf.php?export=" . $db_id . "&row=[+" . $key_field . "+]	\"><img src=\"media/style/{$manager_theme}images/tree/application_pdf.png\"  align=\"absmiddle\" alt=\"PDF export\" /></a>";
	}
	if (!(isset($dbConfig['settings']['hide_delete']) && $dbConfig['settings']['hide_delete'])) {
		$col_types[] = "template:<a href=\"#\" onClick=\"deleteRecord('[+" . $key_field . "+]')\"><img src=\"media/style/{$manager_theme}images/icons/delete.png\"  align=\"absmiddle\" alt=\"delete\" /></a>";
	}
    $grd->colTypes = implode('||',$col_types);

	$grid_html = $grd->render();


$js_script = <<<EOS
<script type="text/javascript">
function deleteRecord(id){
	uri = '{$dbeHomeUrl}&ra=delete&rn='+id;
	if(confirm("Are you sure you want to delete record '" + id + "'")==true){
		window.location.href = uri;
	}
}
function filterGrid(){
	fld = $('fldname').value;
	if(!fld) return;
	val = $('dbe_search').value;
	uri = '{$dbeHomeUrl}&dbe_fld='+fld+'&dbe_filter='+val;
	window.location.href = uri;
}
</script>
EOS;
print $js_script;
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

<div class="sectionHeader"><?php echo $dbConfig['title'];?></div>
<div class="sectionBody">
		<p style="margin-bottom:15px;">Here you can <?php if (isset($dbConfig['settings']['hide_add']) && $dbConfig['settings']['hide_add']) { echo ' '; } else { ?>add new records or <?php } ?>select records to be edited.</p>
		<?php echo ( isset($status_message) ) ? $status_message:''; ?>
		<?php echo ( isset($filter_message) ) ? $filter_message:''; ?>
		<div class="searchbar"  style="margin-bottom:3px;">
		<table border="0" style="width:100%">
			<tr valign="middle">
			<td><?php if (isset($dbConfig['settings']['hide_add']) && $dbConfig['settings']['hide_add']) { echo '&nbsp;'; } else { ?> <a class="searchtoolbarbtn" href="<?php echo $dbeHomeUrl; ?>&ra=insert"><img src="media/style/<?php echo $manager_theme; ?>images/icons/add.png"  align="absmiddle" /> New Record</a> <?php } ?>
			<?php if (isset($dbConfig['settings']['hide_export']) && $dbConfig['settings']['hide_export']) { echo '&nbsp;'; } else { ?> <a class="searchtoolbarbtn" href="<?php echo $base_url.$mod_path; ?>export.php?export=<?php echo $dbConfig['tableName'];?>"><img src="media/style/<?php echo $manager_theme; ?>images/icons/ed_save.gif"  align="absmiddle" /> CSV Export</a> <?php } ?></td>
			<td nowrap="nowrap">

				<table border="0" style="float:right;"><tr>
				<td>Filter:</td>
				<td><?php echo printSelectField( $select_fields,$_REQUEST['dbe_fld'],'fldname' ); ?></td>
				<td>&nbsp;on&nbsp;</td>
				<td><input class="searchtext" name="search" id="dbe_search" type="text" size="15" value="<?php echo $_REQUEST['dbe_filter']; ?>" /></td>
				<td ><a href="#" class="searchbutton" title="Search" onclick="filterGrid();return false;">Go</a></td>
<?php 	if(isset($dbConfig['deletedField']) ){ ?>
				<td><a class="searchtoolbarbtn" href="<?php echo $dbeHomeUrl; ?>&ra=opentrash"><img src="media/style/<?php echo $manager_theme; ?>images/icons/trash.png" width="16" height="16" align="absmiddle" /> Trash Bin</a></td>
<?php } ?>
				</tr>
				</table>

			</td>
			</tr>
		</table>
		</div>


<?php echo $grid_html; ?>
</div>