<?php
/**
 * Html header file
 *
 * @package dbEdit Table Editor
 * @version 1.0
 * @author Jelle Jager
 * @copyright 2008 Jelle Jager
 * @license GPL
 *
 */
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php echo $modx->config['manager_direction'] == 'rtl' ? 'dir="rtl"' : '';?> lang="<?php echo $modx->config['manager_lang_attribute'];?>" xml:lang="<?php echo $modx->config['manager_lang_attribute'];?>">
<head>
	<title>MODx - dbEdit Module - <?php echo $mod_name; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $modx_charset; ?>" />
	<link rel="stylesheet" type="text/css" href="media/style/<?php echo $manager_theme; ?>style.css" />
	<?php include(dirname(__FILE__).'/styles.inc.php'); ?>
	<script  type="text/javascript" src="<?php echo $site_url; ?>assets/modules/dbedit/js/mootools_1_2.js" type="text/javascript"></script>
	<script src="media/script/mootools/moodx.js" type="text/javascript"></script>

</head>
<body>
