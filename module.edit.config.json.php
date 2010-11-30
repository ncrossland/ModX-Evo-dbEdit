<?php
 /**
 * New version of edit table configuration
 *
 * @package dbEdit Table Editor
 * @version 1.0
 * @author Jelle Jager
 * @copyright 2008 Jelle Jager
 * @license GPL
 *
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html charset="utf-8" />
	<title>MODx dbEdit</title>
	<link rel="stylesheet" type="text/css" href="<?php echo $site_url; ?>manager/media/style/<?php echo $manager_theme ? "$manager_theme":""; ?>style.css" />
	<?php include(dirname(__FILE__).'/styles.inc.php'); ?>
	<script type="text/javascript" src="<?php echo $site_url; ?>/assets/modules/dbedit/js/mootools_1_2.js"></script>
	<script type="text/javascript" src="<?php echo $site_url; ?>/assets/modules/dbedit/js/edit.config.json.js" ></script>
	<script type="text/javascript">
	var DBEDIT_DB_ID = <?php echo $db_id?$db_id:"''"; ?>;
	var DBEDIT_TABLE_NAME = '<?php echo $modx->db->escape($_REQUEST["tbl"]); ?>';
	var DBEDIT_MODULE_URL = '<?php echo $moduleHomeUrl; ?>';
	var MODULE_ID = <?php echo $module_id; ?>;
	window.addEvent('domready', function() {
<?php
if($debug){
print "
		$('getJson').addEvent('click', function(e) {
			e.stop();
			ajaxLoadConfig();
		});
";
}
?>
		if(DBEDIT_DB_ID) ajaxLoadConfig();
	});
	</script>
</head>
<body>
<style type="text/css">
table tr{ vertical-align:top; }
table.disabled{ display:none;}
table.enabled{ display:block;background:#fff; }
span.waiting{ color:#4D6029; font-size:80%;}
.hide{display:none;}
.show{display:block;}
.showinline{display:inline;}
</style>



<h1><?php echo $mod_name; ?></h1>
<div id="actions">
	<ul class="actionButtons">
		<li id="Button1">
			<a onClick="if(dbObject){dbObject.submitData()};return false;"><img src="media/style/<?php echo $manager_theme; ?>images/icons/save.png" align="absmiddle">&nbsp;Save</a>
		</li>
		<li id="Button2">
			<a href="<?php echo $moduleHomeUrl; ?>"><img src="media/style/<?php echo $manager_theme; ?>images/icons/cancel.png" align="absmiddle">&nbsp;Cancel</a>
		</li>
	</ul>
</div>

<div class="sectionHeader">Edit Table Configuration</div>
<div class="sectionBody">
<?php if($debug)
print <<<EOFD
<p>
	<a href="#" onClick="dbObject.debugPrint()">show</a>
	&nbsp;|&nbsp;<a id="getJson" href="#">load</a>
	&nbsp;|&nbsp;<a target="debugwindow" onClick="setHref(this,'');"href="#">Show json string</a>
	&nbsp;|&nbsp;<a target="debugwindow" onClick="setHref(this,'&test=php');" href="#">Show php object</a>
</p>
EOFD;
?>
<div id="error-reponse"></div>
<!-- FORM -->
<p><img src='media/style/<?php echo $manager_theme; ?>images/icons/dbedit.gif' align="absmiddle" alt="dBedit" title="" width='32' height='32' />&nbsp; Here you can add a new configuration to manage a database table. The database table must already exists and be accessible by ModX. </p>
<br />
<form name="mutate" id="form_mutate" method="POST" action="index.php?a=112&dba=<?php echo $dba==107?'109':'110'; ?>&id=<?php echo $module_id; ?>">
<input type="hidden" name="json_data" id="json_data" value="" />
<input type="hidden" name="db" id="prop_dbid" value="" />
<?php //* debug */ print __LINE__.': <pre>'.print_r($dbConfig,true) .'</pre><br />'; ?>
<table border='0' cellspacing='0' cellpadding='3'>
<tr><td colspan="2"><div class='split'></div></td></tr>
<tr>
<td scope="row">Table Name</td>
<td><div id="wait-for-me"><span>Select a the table to expose it's properties below.</span></div>
<?php
if( $dba==107 ){
//new table
	$names = listTables();
	if($names){
	$tags = '<select name="fld_tableName" id="prop_tablename" onchange="ajaxLoadConfig(this)">
	<option value=""></option>';
	foreach($names as $table)
		$tags .= '<option value="'.$table.'">'.$table.'</option>';

	$tags .= '</select>';
	}
}else  $tags = '<input type="text" name="fld_tableName" id="prop_tablename" size="30" onchange="ajaxLoadConfig(this)" value="" />';
?>
<?php echo $tags; ?>

<input type="button" name="btRefresh" value="refresh" accesskey="r" onclick="ajaxLoadConfig($('prop_tablename'))" /> <span  class='comment'>(refresh will reset all property fields)</span></td>
<td></td>
</tr>
<tr>
	<td width="200">&nbsp;</td>
	<td class='comment'>Name of the database table. Make sure the table already exists in the current database!<br /><span class="warning">The module will not allow you to select tables with the MODX prefix! (as set at time of MODX install)</span></td>
</tr>
<tr><td colspan="2"><div class='split'></div></td></tr>
<tr>
<td scope="row">Caption</td>
<td><input type="text" name="fld_moduleName" id="prop_title" size="50" maxlength="75" value="" />
</td>
<td></td>
</tr>
<tr>
	<td width="200">&nbsp;</td>
	<td class='comment'>A name to identify this Database Table</td>
</tr>
<tr><td colspan="2"><div class='split'></div></td></tr>

<tr>
<td scope="row">Description</td>
<td><input type="text" name="fld_tableComment" id="prop_description" size="50" maxlength="254" value="" />
</td>
<td></td>
</tr>
<tr>
	<td width="200">&nbsp;</td>
	<td class='comment'>Description of this Database Table</td>
</tr>
<tr><td colspan="2"><div class='split'></div></td></tr>


</table>

<table border='0' cellspacing='0' cellpadding='3' id="dbeTableProperties" class="disabled">
<tr>
<td scope="row">Key Field</td>
<td>
<input type="text" name="fld_keyField" id="prop_keyfield" size="50" maxlength="75" value="" /></td>
<td></td>
</tr>
<tr>
	<td width="200">&nbsp;</td>
	<td class='comment'>Normally this is set automatically. If no key is found dbEdit will not be able to work with this table. Please see the documentation for more.</td>
</tr>
<tr><td colspan="2"><div class='split'></div></td></tr>

<tr valign="top">
<td>Field Properties</td>
<td>
<div id="fieldsMsg" style="display:block;">Field properties will be displayed here once the table is found</div>
<table id="fieldsTable">
<tr><th>Use</th><th>Fld name</th><th>List</th><th>Format</th><th>Heading</th><th>Default</th><th>key</th></tr>
<tbody id="fieldsArray"></tbody>
</table>
</td>
<td></td>
</tr>
<tr>
	<td width="200">&nbsp;</td>
	<td class='comment'>List of field properties.</td>
</tr>
<tr><td colspan="2"><div class='split'></div></td></tr>

<tr>
<td scope="row">Trash Indicator Field</td>
<td><select name="trash_field" id="trash_field" onChange="trashChange(this);"></select><div id="trash_props" class="hide"> Deleted Value: <input type="text" name="trash_deleteVal" size="10" maxlength="20" value="" /> Undeleted Value: <input type="text" name="trash_undeleteVal" size="10" maxlength="20" value="" /></div>
</td>
<td></td>
</tr>
<tr>
	<td width="200">&nbsp;</td>
	<td class='comment'>If you want to utilize the Trash Bin functionality you will need a field in your database table that acts as a "trash" or "deleted" indicator.  </td>
</tr>
<tr><td colspan="2"><div class='split'></div></td></tr>

<td scope="row">Sorting</td>
<td><input type="text" name="fld_sortParams" id="prop_sort" size="50" maxlength="75" value="" /></td>
<td></td>
</tr>
<tr>
	<td width="200">&nbsp;</td>
	<td class='comment'>Comma separated list of fields and sorting direction. (ex. FullName=Asc,FirstName=Desc )</td>
</tr>
<tr><td colspan="2"><div class='split'></div></td></tr>
<tr>
<td scope="row">Filter Records on:</td>
<td><input type="text" name="fld_listWhere" id="prop_filter" size="50" maxlength="75" value="" /></td>
<td></td>
</tr>
<tr>
	<td width="200">&nbsp;</td>
	<td class='comment'>Show only records according to filter criteria.</td>
</tr>
<tr><td colspan="2"><div class='split'></div></td></tr>

<tr>
<td scope="row">Advanced Settings</td>
<td>
	<table id="row_settings">
		<tr>
			<th>Name</th>
			<th>Value</th>
			<th>&nbsp;</th>
		</tr>
		<tr>
			<td><input class='setting-name' type="text" name="prop_settingsname[]" size="15" maxlength="50" value="" /></td>
			<td><input class='setting-value' type="text" name="prop_settingsvalue[]" size="35" maxlength="512" value="" /></td>
			<td><input type="button" onClick="addSetting();" value="+" /></td>
		</tr>
	</table>
</td>
</tr>
<tr>
	<td width="200">&nbsp;</td>
	<td class='comment'>Here you can add advanced settings. See documentation for an explanation of using advanced settings.</td>
</tr>
<tr><td colspan="2"><div class='split'></div></td></tr>
</table>
<input type="submit" name="save" value="save" style="display:none;">
</form>
</div>
</div>

