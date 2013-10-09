<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
 /**
 * Import data form for dbEdit Table Editor module.
 *
 * @package dbEdit Table Editor
 * @version 1.0
 * @author Jelle Jager
 * @copyright 2008 Jelle Jager
 * @license GPL
 *
 */
//* debug */ print basename(__FILE__).' ('.__LINE__.'): <pre>'.print_r($dbConfig,true) .'</pre><br />';
?>

<form name="mutate" method="post" action="index.php">
<input type="hidden" name="a" value="<?php echo $_REQUEST['a']; ?>">
<input type="hidden" name="id" value="<?php echo $module_id; ?>">
<input type="hidden" name="dba" value="<?php echo $dba; ?>">
<input type="hidden" name="ra" value="import">
<input type="hidden" name="db" value="<?php echo $db_id; ?>">
<input type="hidden" name="variablesmodified" value="">


<h1><?php echo $mod_name; ?></h1>
<div id="actions">
	<ul class="actionButtons">
		<li id="Button1">
			<a onclick="documentDirty=false; document.mutate.save.click();">
			<img src="media/style/<?php echo $manager_theme; ?>images/icons/cancel.png" alt="">&nbsp;Import</a>
		</li>
		<li id="Button2">
			<a onclick="window.location.href = '<?php echo $moduleHomeUrl ?>';">
			<img src="media/style/<?php echo $manager_theme; ?>images/icons/cancel.png" />&nbsp;Cancel</a>
		</li>
	</ul>
</div>


<div class="sectionHeader"><?php echo $_lang['dbe_import_data']; ?></div><div class="sectionBody">
<?php
	if( isset($_SESSION['dbedit_message'])){
		print "<div class=\"dbe-message-{$_SESSION['dbedit_message'][0]}\">".$_SESSION['dbedit_message'][1].'</div>';
	}
	unset($_SESSION['dbedit_message']);
	?>

<p><?php echo $_lang['dbe_import_intro']; ?></p>
<?php /*
<p>
<?php echo $_lang['dbe_import_type']; ?>
	<input type="radio" name="type" value="SQL" disabled="disabled" /> SQL
	<input type="radio" name="type" value="CSV" checked="checked" /> CSV
</p>
//*/
?>
<p><?php echo $lang['dbe_import_fieldlist_help']; ?></p>

<table width="99%">
<tr align="left"><th style="border-bottom:1px solid #999;padding-bottom:4px;"><?php echo $_lang['dbe_import_settings']; ?></th><th style="border-bottom:1px solid #999;padding-bottom:4px;"><?php echo $_lang['dbe_import_data']; ?></th></tr>
<tr valign="top">
<td width="30%">
<p>
	<input type="checkbox" name="hasHeaderRow" value="yes" /> <strong><?php echo $_lang['dbe_import_hasheader']; ?>
	<!-- Column Number of Key Field: <input type="text" name="KeyFieldColumn" value="1" size="3" maxlength="2" /> -->
</p>
<?php //* ?>
<p>
	<label><strong><?php echo $_lang['dbe_import_insert_caption']; ?></strong></label><br />
	<input type="radio" name="insertString" value="insert" checked="checked" />&nbsp;<?php echo $_lang['dbe_import_insert']; ?>
	<input type="radio" name="insertString" value="replace" />&nbsp;<?php echo $_lang['dbe_import_replace']; ?><br />
		<span style="font-size:85%;"><?php echo $_lang['dbe_import_insert_help']; ?></span></p>
<p><?php echo $_lang['dbe_import_fieldlist']; ?></p>
<?php //*/ ?>
<div id="fieldList">
<?php
	$i=0;
	foreach($dbConfig['fields'] as $fldName => $fld){
		//if(!$fld['isKey']){
			$headings[$i] = $fld['heading'];
			$fields[$i] = $fldName;
			$i++;
		//}
	}

	for($i=0;$i<count($fields);$i++)
		print "<input type=\"text\" id=\"fld_$i\" name=\"frmFields[$fields[$i]]\" value=\"".($i)."\" size=\"2\" /> $headings[$i]<br />";
		//print "<input type=\"text\" id=\"fld_$i\" name=\"frmFields[$fields[$i]]\" value=\"".($i)."\" size=\"2\" onchange=\"renumberFields(this)\" /> $headings[$i]<br />";

?>
</div>

</td>
<td>
	<p>
	<textarea style="width:99%;height:300px;" rows="15" name="importData"></textarea>
	</p>
	<input STYLE="display:none;" type="submit" name="save" value="Save" />
</p>
</td>
</tr>
</table>
</div>
</form>
