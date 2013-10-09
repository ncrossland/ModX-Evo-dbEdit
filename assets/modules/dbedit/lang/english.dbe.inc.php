<?php
/**
 * Filename:       includes/lang/english.inc.php
 * Function:       Default English language file for dbEdit module.
 * Encoding:       ISO-Latin-1
 * Author:         Jelle Jager
 * Date:           2006/7/14
 * Version:        1.
 * MODx version:   0.9.2
*/

// NOTE: Make sure there's no conflict with modx main language file

# Import
$_lang['dbe_import_not_numeric'] = 'record # [+line+]: [+field+] (column:[+key+]) is not numeric.';
$_lang['dbe_import_non_date'] = 'record # [+line+]: [+field+] (column:[+key+]) is not a valid date.';
$_lang['dbe_import_title'] = 'Import Data for ';
$_lang['dbe_import'] = 'Import';
$_lang['dbe_import_data'] = 'Import Data';
$_lang['dbe_import_intro'] = 'Here you can import data into your database table. Only comma separated values (CSV) are supported. ';
$_lang['dbe_import_fieldlist_help'] = 'You can use the field list below to match database field names with csv column numbers. If you have less columns in your CSV data then the fields listed you should skip the missing fields by setting them to \'0\'. Be carefull not to have duplicate numbers!';
$_lang['dbe_import_settings'] = 'Import Settings';
$_lang['dbe_import_hasheader'] = '1st row is header row</strong><br /><em style="font-size:85%;">(Currently not used to match field names)</em>';
$_lang['dbe_import_purge'] = '<strong>Purge table before import</strong><br /><em style="font-size:85%;">(not yet implemented)</em>';
$_lang['dbe_import_fieldlist'] = '<strong>Field List</strong>';
$_lang['dbe_import_type'] = 'Import type: <em style="font-size:85%;">(This setting is currently ignored.)</em>';
$_lang['dbe_import_nodata'] = 'Did not receive any data to import. Form has been reset!';
$_lang['dbe_import_insert'] = 'Insert';
$_lang['dbe_import_replace'] = 'Replace';
$_lang['dbe_import_insert_caption'] = 'Use SQL INSERT or REPLACE to import fields?';
$_lang['dbe_nodatabase'] = 'Could not read database tables.';
$_lang['dbe_import_insert_help'] = '(Replace will overwrite existing records with the same unique key)';
/*
$_lang['dbe_'] = '';
$_lang['dbe_'] = '';
$_lang['dbe_'] = '';
$_lang['dbe_'] = '';
$_lang['dbe_'] = '';

$_lang["edit"] = "Edit";
$_lang["save"] = "Save";
$_lang["delete"] = "Delete";


* //*/
?>