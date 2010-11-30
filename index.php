<?php
/**
 * Entry file for dbEdit Table Editor
 *
 * @package dbEdit Table Editor
 * @version 1.0
 * @author Jelle Jager
 * @copyright 2008 Jelle Jager
 * @license GPL
 *
 */
//* debug */ print __LINE__.': <pre>'.print_r($modx->config,true) .'</pre><br />';die();
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
$manager_theme = $modx->config['manager_theme'].'/';

	global $manager_language,
			$_lang,
			$modx_charset,
			$create_table_sql,
			$dbe_config_table,
			$module_id,
			$db_id,
			$dba;

	$mysql_types = array(
	'integer' => array ('INT','INTEGER','TINYINT','BOOLEAN','SMALLINT','MEDIUMINT','BIGINT','BIT'),
	'float' => array ('DECIMAL','DEC','NUMERIC','FLOAT','DOUBLE PRECISION','REAL'),
	'string' => array ('CHAR','VARCHAR','BINARY','VARBINARY','TINYBLOB','BLOB','MEDIUMBLOB','LONGBLOB','TINYTEXT','TEXT','MEDIUMTEXT','LONGTEXT','ENUM','SET'),
	'date' => array ('DATE','DATETIME','TIMESTAMP','TIME','YEAR')
	);
	//* debug */ print __LINE__.': <pre>'.print_r($_REQUEST,true) .'</pre><br />';
	$site_url=$modx->config['site_url'];
	$dbe_config_table = "dbedit_configs";
	$module_id = (int)$_REQUEST['id'];
	$db_id = isset($_REQUEST['db'])? (int)$_REQUEST['db'] : null;

	//add language stuff
	include_once "lang/english.dbe.inc.php";
	if($manager_language!="english" && !empty($manager_language)) {
		include_once "lang/".$manager_language.".dbe.inc.php";
	}

	$dbConfig='';

	if( !createDbEditTable() ){
		include($basePath.$mod_path.'header.inc.php');
		print "<div>\n";
		print "<p><span  style=\"color:#900;:font-weight:bold;\">Could not create the module configurations table.</span><br />
		Perhaps the script does not have the proper permissions. Please create the following table manually and run the module again.</p>";
		print "<p>$create_table_sql</p>\n";
		print "</div>";
		include($basePath.$mod_path.'footer.inc.php');
		exit;
	}

	//get request variables
	//get dbConfig if any
	if(isset($db_id))
		$dbConfig = getStoredConfig($db_id);

	if(isset($_REQUEST['rn'])){
		$rn = trim($_REQUEST['rn']);
		//$rn could well be a string...
		if(!is_numeric($rn)) $rn=$modx->db->escape(html_entity_decode($rn));
	}
	$ra = (isset($_REQUEST['ra']))?trim($_REQUEST['ra']):"";

	//set a few return urls
	$base_url = $modx->config['base_url'];
	$manager_path = $modx->config['base_path'].'manager/';
	$moduleHomeUrl = $base_url."manager/index.php?a=".$_REQUEST['a']."&id=".$module_id;
	$dbeHomeUrl = $moduleHomeUrl."&db=" . $db_id;

	$dba = isset($_REQUEST['dba'])?(int)$_REQUEST['dba']:0;
	switch($dba){
		case 113: //ajax request for table info
			include("ajax.table.info.inc.php");
			exit;
			break;
		case 114: //ajax request for duplicate values DOES NOT WORK YET
			include("ajax.check.field.inc.php");
			exit;
			break;
		case 118:
			include("json.get.config.php");
			exit;
			break;
		case 107: //add new table
		case 108: //edit table config
			include("module.edit.config.json.php");
			exit;
			break;
		case 109: //save new configuration
		case 110: //update existing configuration
			include("module.save.configuration.inc.php");
			exit;
			break;
		case 111: //delete config
			include("module.delete.configuration.php");
			exit;
			break;
		case 112:
			include($basePath.$mod_path.'header.inc.php');
			include ('import.data.inc.php');
			include($basePath.$mod_path.'footer.inc.php');

			exit;
			break;
		default:
			if( is_array($dbConfig) ){
				//Should make these into numbers as well
					if($rn && !$ra){$ra = "edit"; }
					$include_header = 1;
					switch($ra){
						case "insert":
						case "edit":
							$include_file = "record.edit.inc.php";
							break;
						case "update":
							$include_header=0;
							$include_file = "record.save.inc.php";
							break;
						case "delete":
							$include_file = "record.delete.inc.php";
							break;
						case "purge":
							$include_file = "trash.purge.records.inc.php";
							break;
						case "restore":
							$include_header=0;
							$include_file = "trash.restore.records.inc.php";
							break;
						case "opentrash":
							$include_file = "trash.list.records.inc.php";
							break;
						case "dbinfo":
							$include_file = "table.info.static.action.php";
							break;
						case "import":
							$include_file = "import.data.inc.php";
							break;
						case "debugconfig":
							$include_file = "debug.print.table.php";
							break;
						default:
							$include_file = "records.list.records.inc.php";
					}
					if($include_header) include($basePath.$mod_path.'header.inc.php');
					include($include_file);
					if($include_header)include($basePath.$mod_path.'footer.inc.php');
				exit;
			}else{
				//nothing else happening - show list of editable db tables
				include($basePath.$mod_path.'header.inc.php');
				include('module.list.tables.inc.php');
				include($basePath.$mod_path.'footer.inc.php');
				//*/
			}
		}
	exit;

/****************************************
* List of functions
****************************************/

	//get table info from config or mysql table
	function getTableInfo(&$modx,$dbConfig,$expand=false){
		if(empty($modx->db->conn)||!is_resource($modx->db->conn)) { $modx->db->connect(); }
		$dbname = str_replace("`",'',$modx->db->config['dbase']); //remove backticks if any
		//check if table exists
		if( !tableExists($dbConfig['tableName']) ) return null;
		$flds = mysql_list_fields($dbname, $dbConfig['tableName'], $modx->db->conn);
		$columns = mysql_num_fields($flds);
		for ($i = 0; $i < $columns; $i++) {
			$fldName = mysql_field_name($flds, $i);
			$tblInfo[$fldName]['dbtype'] = mysql_field_type($flds, $i);
	   	$tblInfo[$fldName]['type'] = ( isset($dbConfig['fieldTypes'][$fldName]) )?$dbConfig['fieldTypes'][$fldName]:$tblInfo[$fldName]['dbtype'];
			$tblInfo[$fldName]['len'] = mysql_field_len($flds, $i);
			$flags = mysql_field_flags($flds, $i);
			$tblInfo[$fldName]['flags'] = $expand? explode(' ',$flags):$flags;
		}
		return $tblInfo;
	}

	function getStoredConfig($db_id){
		global $modx,$dbe_config_table;
		if($rsc = $modx->db->select("*",$modx->db->config['table_prefix'] .
		 $dbe_config_table,"recID=".$db_id) ){
			$row = $modx->db->getRow($rsc);
			$dbConfig = unserialize(stripslashes($row['config']));
			if($dbConfig){
				if(!isset($dbConfig['moduleComments']))
					$dbConfig['moduleComments'] = $row['comment'];
				$dbConfig['title'] = $row['name'];
				return $dbConfig;
			}
		}
		return false;
	}

	function listTables(){
		global $modx;
		$dbase = str_replace('`','',$modx->db->config['dbase']);
		$mysql_version = explode('.',mysql_get_client_info());
		$prefix = $modx->db->config['table_prefix'];
		$l = strlen($prefix);
		//create list of tables without the modx prefix.
		$sql  = "SHOW TABLES FROM `{$dbase}`";
		if($mysql_version[0]>4){
			if($l) $sql .= " WHERE Tables_in_{$dbase} NOT LIKE '".$prefix."%'";
		}
		$ds = $modx->db->query($sql);
		if($ds){
			while($row = $modx->db->getRow($ds,'num')){
				if(substr($row[0],0,$l)!=$prefix) $table_names[] = $row[0];
			}
			return $table_names;
		}
		return false;
	}

	function printSelectField($arr,$selectValue=false,$id="field[]",$includeStartField=1,$sep=',',$name=''){
		if(!is_array($arr)){
			$a = explode($sep,$arr);
			$arr = array_combine($a,$a);
		}
		$name = $name?$name:$id;
		print "\n<select name=\"$nmae\" id=\"$id\">\n";
		if($includeStartField){ print "\t<option value=\"\"></option>\n";}
		foreach($arr as $key => $val){
			$selected = ($key==$selectValue)?" selected=\"selected\"":"";
			print "\t<option value=\"$key\"$selected>$val</option>\n";
		}
		print "</select>\n";
	}


	/**
	 * checkTypes()
	 * Checks type of database field
	 * Not yet implemented..
	 *
	 * @todo -c Implement . everything basically
	 * @param mixed $type
	 * @return
	 **/
	function checkTypes($type){
		if(empty($type)) return false;
		return $type;
	}



	/**
	 * mergePlaceholderContent()
	 * Basically a copy of modx->mergePlaceholderContent with an added parameter
	 * I wasn't sure if you could use setPlaceHolder and mergePlaceholderContent
	 * without any detrimental effects so I pretty much duplicated it here.
	 *
	 * @param mixed $content - template with placeholders
	 * @param array mixed $placeholders
	 * @return string
	 **/
	function mergePLcontent($content,$placeholders) {
		$replace = array();
    $matches = array();
    if (preg_match_all('~\[\+(.*?)\+\]~', $content, $matches)) {
      $cnt = count($matches[1]);
      for($i=0; $i<$cnt; $i++) {
        $v = '';
        $key= $matches[1][$i];
        if (is_array($placeholders) && array_key_exists($key, $placeholders))
          $v = $placeholders[$key];
        if($v==='') unset($matches[0][$i]); // here we'll leave empty placeholders for last.
        else $replace[$i] = $v;
      }
      $content = str_replace($matches[0], $replace, $content);
    }
    return $content;
  }

	$modxPermissions = array(
		'browse_tables' => 'run_module',
		'edit_table_link' => 'edit_module',
		'create_table_link' => 'create_module',
		'delete_table_link' => 'delete_module',
		'import_table_data'=>'edit_module'
	);

	/**
	 * dbeHasPermission()
	 * Wrapper for modx->hasPermission - custom permissions with fallback...
	 * If the customPermissions plugin is installed and they are set (either 0 0r 1) for
	 * the current user it will use those. Otherwise it will check the standard modx
	 * permissions in the $modxPermissions table. For now this means that someone can have
	 * access if they have sufficient module acces and have not had their custom permissions
	 * set one way or the other.
	 *
	 * @param string $permission
	 * @param reference $modx
	 * @return boolean true or false
	 **/
	function dbeHasPermission($permission,&$modx){
		global $modxPermissions;
		$permission = isset($_SESSION['mgrPermissions'][$permission])?$permission:$modxPermissions[$permission];
		return $modx->hasPermission($permission);
	}

	//checks if a table exists in database
	function tableExists($tableName,$dbase=''){
		global $modx;
		if(empty($tableName)) return false;
		if(!$dbase) $dbase = $modx->db->config['dbase'];
		$sql = "SHOW TABLES FROM {$dbase} LIKE '{$tableName}'";

		$table_ok = $modx->db->getValue($sql)?true:false;
		return $table_ok;
	}

	function createDbEditTable(){
		global $modx, $create_table_sql, $dbe_config_table;
		//check if our dbEdit table exists and try to create it if not.
		$sql = "Show Tables from {$modx->db->config['dbase']} LIKE '{$modx->db->config['table_prefix']}{$dbe_config_table}'";
		$table_ok = $modx->db->getValue($sql)?true:false;
		//try to create table
		if(!$table_ok){
			$sql = "CREATE TABLE `".$modx->db->config['table_prefix'].$dbe_config_table."` (`recID` TINYINT (3) UNSIGNED AUTO_INCREMENT, `name` VARCHAR (128) NOT NULL, `comment` VARCHAR (255) NOT NULL, `config` TEXT NOT NULL, PRIMARY KEY(`recID`), UNIQUE(`name`))";

			if( $modx->db->query($sql) )
				$table_ok = true;
		}

		return $table_ok;
	}
	
	
	// Clears the cache when values are updated
	// by cipa
	// http://modxcms.com/forums/index.php/topic,32720.msg273472.html#msg273472
	function clearCache() {
	    include_once "processors/cache_sync.class.processor.php";
	    $sync = new synccache();
	    $sync->setCachepath("../assets/cache/");
	    $sync->setReport(false);
	    $sync->emptyCache();
	}
?>