<?php
/**
 * Save record processor.
 *
 * @package dbEdit Table Editor
 * @version 1.0
 * @author Jelle Jager
 * @copyright 2008 Jelle Jager
 * @license GPL
 *
 */
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

//* debug */ print __LINE__.',POST: <pre>'.print_r($_POST,true) .'</pre><br />';

	$tblInfo = $modx->db->getTableMetaData( $dbConfig['tableName'] );
	$fields = $dbConfig['fields'];
//* debug */ print __LINE__.': <pre>'.print_r($tblInfo,true) .'</pre><br />';

	foreach($fields as $fld => $props){
		if(!isset($_POST["fld_".$fld]) ) continue;
		list($db_type,$extra) = explode('(',strtoupper($tblInfo[$fld]['Type']));
//* debug */ print __LINE__.': '.substr($extra,0,-1).'<br />';
		//make sure values are of right type and escape if needed
		//needs to be changed into proper validation...
		if($db_type=='set' || $db_type=='enum'){
			$ok_values = explode(',',substr($extra,0,-1));
//* debug */ print __LINE__.': <pre>'.print_r($ok_values,true) .'</pre><br />';
			if(!is_array($_POST["fld_".$fld])) $_POST["fld_".$fld] = array($_POST["fld_".$fld]);
			foreach($_POST["fld_".$fld] as $v){
				if(!in_array("'".$v."'",$ok_values)){
					$error[$fld] = "'{$v}' is not a valid value for '".$fields[$fld]['heading']."'";
					break 2;
				}
			}
		}

		if(is_array($_POST["fld_".$fld])) $_POST["fld_".$fld] = implode(',',$_POST["fld_".$fld]);
//* debug */ print __LINE__.',db_type: '.$db_type.'<br />';
//* debug */ print __LINE__.',mysql types: '.implode(', ',$mysql_types['date']).'<br />';
		if(in_array($db_type,$mysql_types['integer']))
			$newValues[$fld] = intval($_POST["fld_".$fld]);
		elseif(in_array($db_type,$mysql_types['float']))
			$newValues[$fld] = floatval($_POST["fld_".$fld]);
		elseif( in_array($db_type,$mysql_types['date']) ){
				//get date format and reshuffle so that we can use strtotime
				if( preg_match('/[\W_]+/',$dbedit_date_format,$match))
					$sep = $match[0];
				$d = explode($match[0],$dbedit_date_format);
				$dt_value = explode($match[0],$_POST["fld_".$fld]);
				foreach($d as $k=>$f){
					$dy = (strstr('d,j',$f))? $dt_value[$k]:$dy; //d, D, j, l, N, w, S, F, m, M, n, Y, y
					$mth = (strstr('m,M,F,n',$f))? $dt_value[$k]:$mth;
					$y = ($f=='Y' || $f=='y')? $dt_value[$k]:$Y;
				}
				$t = strtotime("$mth $dy $y");
//* debug */ print __LINE__.': '."$mth $dy $y".'<br />';
				$newValues[$fld] = ($t!= false && $t!=-1)? date('Y-m-d',$t):'0000-00-00';
//* debug */ print __LINE__.': '.$newValues[$fld].'<br />';
			}
		else
			$newValues[$fld] = $modx->db->escape($_POST["fld_".$fld]);
	}
//* debug */ die();
	if(isset($error)){
		/* debug */ print __LINE__.': <pre>'.print_r($error,true) .'</pre><br />';die();
	}

	//enforce  reset of deleted field
	if( isset($dbConfig['deletedField']) )
		 $newValues[$dbConfig['deletedField']]=$dbConfig['enabledValue'];
//* debug */ print __LINE__.': <pre>'.print_r($newValues,true) .'</pre><br />';
//die();
	if( isset($rn) && $rn ){
		//add quotes if rn is not a number
		if(!is_numeric($rn)) $rn = "'".$modx->db->escape($rn)."'";
		if( !$modx->db->update($newValues,$dbConfig['tableName'],$dbConfig['keyField']."=".$rn) ){
			$msg = "<h4>Could not save edited record!</h4> <p>Database replied:<br />";
			$msg .= $modx->db->getLastError()."<p>";
			$ok = false;
		}else{
			$msg .= "<p>Record succesfully updated.</p>";
			$ok = true;
		}
	}else{ //insert new
		//expand this to check all fields for autoincrement??
		if(strstr($tableInfo[$dbConfig['keyField']]['flags'],'auto_increment'))
			$newValues[$dbConfig['keyField']]= Null;

		if( !$modx->db->insert($newValues,$dbConfig['tableName']) ){
			$msg = "<h4>Error Saving New Record!</h4> <p>Database replied:<br />";
			$msg .= $modx->db->getLastError()."<p>";
		}else{
			$ok = true;
			$msg .= "<p>Succesfully added new record</p>";
		}
	}

	$url = $uri = "{$dbeHomeUrl}&db={$db_id}";
	if($_POST['stay']>0)  $uri .= "&ra=edit"; //add another
	if($_POST['stay']==2) $uri .= "&rn=".$rn;//continue editing

	if($ok){
		clearCache();
		$_SESSION['dbedit_message'] = array('succes',$msg);
		header("location: {$uri}");
		exit;
	}

	include($basePath.$mod_path.'/header.inc.php');
?>

<h1><?php echo $mod_name; ?></h1>

<div class="sectionHeader"> Save Record</div>
<div class="sectionBody">
<?php echo $msg; ?>
<p>&nbsp;</p>
<ul>
	<li><a href="{$url}">Return to <?php echo $dbConfig['title']; ?></a></li>
</ul>
</div>
<?php include($basePath.$mod_path.'/footer.inc.php'); ?>