/**
 * dbEdit
 * 
 * Edit arbitrary databases from within MODX Evo Manager
 * 
 * @category	module
 * @version 	1.0
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@properties &mod_name=Module name;string;dbEdit &mod_path=Module path;string;assets/modules/dbedit/ &dbedit_date_format=Date Format (use d,D,m,M,F,n,y,Y as in php date() with simple separator);string;d F Y
 * @internal	@guid f81de9ccfe3c0521e608b597371146ec
 * @internal	@shareparams
 * @internal	@dependencies
 * @internal	@modx_category Manager and Admin
 * @internal    @installset base, sample
 */
$basePath = MODX_BASE_PATH;

// If we cant't find the module files...
if (!file_exists($basePath . $mod_path . '/index.php')) {
	// Log an error
	$error_message = '<strong>Module not found!</strong></p><p>Edit the module, click the Configuration tab and change the Module Path to point to the module.</p>';
	$modx->Event->alert($error_message);
	$modx->logEvent(0, 3, $error_message, 'dbEdit');
} else {
	// Execute the module
	include($basePath . $mod_path . '/index.php');
}