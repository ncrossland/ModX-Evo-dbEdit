<?php
/**
 * Filename:       includes/lang/english.inc.php
 * Function:       Default English language file.
 * Encoding:       ISO-Latin-1
 * Author:         The MODx Project Team (originally by Alex Butter)
 * Date:           2005/10/25
 * Version:        1.6
 * MODx version:   0.9.1
*/

// NOTE: New language keys should added at the bottom of this page
//			Make sure there's no conflict with main language file

# Import
$_lang['dbe_import_not_numeric'] = 'record # [+line+]: [+field+] (column:[+key+]) is not numeric.';
$_lang['dbe_import_non_date'] = 'record # [+line+]: [+field+] (column:[+key+]) is not a valid date.';
$_lang['dbe_import_title'] = 'Import Data for ';
$_lang['dbe_import'] = 'Import';
$_lang['dbe_import_data'] = 'Import Data';
$_lang['dbe_import_intro'] = 'Here you can import data into your database table. Currently only comma separated values (CSV) are supported. Only unique rows are imported. (i.e. If a row already exists it will not be overwritten)';
$_lang['dbe_import_fieldlist_help'] = 'You can use the field list below to match database field names with csv column numbers. If you have less columns in your CSV data then the fields listed you should skip the missing fields by setting them to \'0\'. Be carefull not to have duplicate numbers!';
$_lang['dbe_import_settings'] = 'Import Settings';
$_lang['dbe_import_hasheader'] = '1st row is header row</strong><br /><em style="font-size:85%;">(Currently not used to match field names)</em>';
$_lang['dbe_import_purge'] = '<strong>Purge table before import</strong><br /><em style="font-size:85%;">(not yet implemented)</em>';
$_lang['dbe_import_fieldlist'] = '<strong>Field List</strong>';
$_lang['dbe_import_type'] = 'Import type: <em style="font-size:85%;">(This setting is currently ignored.)</em>';
$_lang['dbe_import_nodata'] = 'Did not receive any data to import. Form has been reset!';


/*
$_lang["dbe_"] = "";
$_lang["dbe_"] = "";
$_lang["dbe_"] = "";
$_lang["dbe_"] = "";
$_lang["dbe_"] = "";
$_lang["dbe_"] = "";
$_lang["dbe_"] = "";
$_lang["dbe_"] = "";
$_lang["dbe_"] = "";

* //*/
?>