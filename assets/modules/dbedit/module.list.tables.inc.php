<?php
 /**
 * List tables managed by dbEdit
 *
 * @package dbEdit Table Editor
 * @version 1.0
 * @author Jelle Jager
 * @copyright 2008 Jelle Jager
 * @license GPL
 *
 */
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
$theme = $manager_theme ? "$manager_theme":"";

$base_path = $modx->config['base_path'];
// context menu
include $base_path."manager/includes/controls/contextmenu.php";
$cm = new ContextMenu("cntxm", 150);
$cm->addItem("Browse","js:menuAction(1)","media/style/{$theme}images/icons/save.png",(!$modx->hasPermission('exec_module') ? 1:0));
$cm->addSeparator();
$cm->addItem($_lang["edit"],"js:menuAction(2)","media/style/{$theme}images/icons/logging.gif",(!$modx->hasPermission('edit_module') ? 1:0));
$cm->addItem("Import Data","js:menuAction(3)","media/style/{$theme}images/icons/newdoc.gif",(!$modx->hasPermission('new_module') ? 1:0));
$cm->addItem("Remove", "js:menuAction(4)","media/style/{$theme}images/icons/delete.gif",(!$modx->hasPermission('delete_module') ? 1:0));
echo $cm->render();
?>
<script>
	function getCntxMenu(id) {
		return $(id);
	}
	function hideCntxMenu(id){
		var cm = getCntxMenu(id);
		cm.style.visibility = 'hidden';
	}
</script>
<script language="JavaScript" type="text/javascript">
	var THIS_MOD_ID = <?php echo $module_id; ?>;
	var selectedItem;
	var contextm = <?php echo $cm->getClientScriptObject(); ?>;

	function showContentMenu(id,e){
		selectedItem=id;
		contextm.style.left = (e.pageX || (e.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft)))<?php echo $modx->config['manager_direction']=='rtl' ? '-190' : '';?>+"px"; //offset menu if RTL is selected
		contextm.style.top = (e.pageY || (e.clientY + (document.documentElement.scrollTop || document.body.scrollTop)))+"px";
		contextm.style.visibility = "visible";
		e.cancelBubble=true;
		return false;
    }
    function menuAction(a) {
		var db = selectedItem;
		switch(a) {
			case 1:		// run module
				//dontShowWorker = true; //prevent worker from being displayed
				window.location.href = 'index.php?a=112&id='+THIS_MOD_ID+"&db="+db;
				break;
			case 2:		// edit
				window.location.href='index.php?a=112&dba=108&id='+THIS_MOD_ID+"&db="+db;
				break;
			case 3:		// import data
				uri = 'index.php?a=112&dba=112&id='+THIS_MOD_ID+"&db="+db;
				//window.location.href=uri;
				//alert(uri);
				window.location.href=uri;
				break;
			case 4:		// delete
				if( confirm("Are you sure you want to remove this table?\n (This does NOT delete any data from your database. \nIt only removes the configuration from this module.)")==true) {
					window.location.href='index.php?a=112&dba=111&id='+THIS_MOD_ID+"&db="+db;
				}
				break;
		}
	}

	document.addEvent('click', function(){
		contextm.style.visibility = "hidden";
	});
</script>
<h1><?php echo $mod_name; ?></h1>
<div id="actions">
	<ul class="actionButtons">
		<li id="Button1">
			<a onclick="document.location.href='index.php?a=2';">
			<img src="media/style/<?php echo $manager_theme; ?>images/icons/cancel.png" alt=""> Close <?php echo $mod_name; ?></a>
		</li>
		<li id="Button2">
			<a onclick="document.location.href='index.php?a=112&id=<?php echo $module_id; ?>&dba=107';">
			<img src="media/style/<?php echo $manager_theme; ?>images/icons/add.png"  /> Add New Table</a>
		</li>
	</ul>
</div>
<div class="sectionBody">
<?php
	if( isset($_SESSION['dbedit_message'])){
		print "<div class=\"dbe-message-{$_SESSION['dbedit_message'][0]}\">".$_SESSION['dbedit_message'][1].'</div>';
	}
	unset($_SESSION['dbedit_message']);
	?>
	<!-- load modules -->
	<p><img src='media/style/<?php echo $manager_theme; ?>images/icons/resources.gif' alt="." width="32" height="32" align="left" />&nbsp;Select a data table to manage by clicking on the name of the table. For more options click on the icon in the grid.</p><p>&nbsp;</p>
	<br />
	<div>
<?php
	$Qstring = "index.php?a=".$_REQUEST['a']."&id=".$module_id;

	$sql = "SELECT recID,name,comment, config " .
			"FROM ".$modx->getFullTableName($dbe_config_table)." ".
			(!empty($sqlQuery) ? " WHERE (name LIKE '%$sqlQuery%') OR (comment LIKE '%$sqlQuery%')":"")." ".
			"ORDER BY name";
    $ds = $modx->db->query($sql);
	include_once $base_path."manager/includes/controls/datagrid.class.php";
	$grd = new DataGrid('',$ds,15); // set page size to 0 t show all items
	$grd->noRecordMsg = $_lang["no_records_found"];
	$grd->cssClass="grid";
	$grd->columnHeaderClass="gridHeader";
	$grd->itemClass="gridItem";
	$grd->altItemClass="gridAltItem";
	$grd->fields="name,comments";
	$grd->columns=$_lang["icon"]." ,".$_lang["name"]." ,".$_lang["description"];
	$grd->colWidths="34,,";
	$grd->colAligns="center,,";
	$grd->colTypes="template:<a class='gridRowIcon' href='#' onclick='return showContentMenu([+recID+],event);' title='".$_lang["click_to_context"]."'><img src='".$base_url.$mod_path."images/db.gif' width='32' height='32' /></a>||template:<a href='".$Qstring."&db=[+recID+]' title='Click to browse table'>[+name+]</a>||template:<em>[+comment+]</em>";
	//if($listmode=='1') $grd->pageSize=0;
	//if($_REQUEST['op']=='reset') $grd->pageNumber = 1;
	// render grid
	echo $grd->render();
?>
</div>
</div>