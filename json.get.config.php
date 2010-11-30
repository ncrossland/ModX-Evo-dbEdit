<?php
/**
 * JSON Ajax processor
 * Returns debEdit table configuration in JSON format
 * (at least it will when I get to it)
 *
 * @package dbEdit Table Editor
 * @version 1.0
 * @author Jelle Jager
 * @copyright 2008 Jelle Jager
 * @license GPL
 *
 */
	include_once('JSON.php');
	$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);

	if( !isset($dbConfig) || empty($dbConfig) ){
		$dbConfig = array();
		$dbConfig['tableName'] = $modx->db->escape($_REQUEST['tbl']);
		$dbConfig['fields']=array();
		$dbConfig['settings']=array();
	}else{
//*for development only*/ if($_GET['test']=='php') print __LINE__.': <pre>'.print_r( $dbConfig,true) .'</pre>';
		//print  $json->encode($dbConfig);
		//exit;
	}
/*for development only*/ if($_REQUEST['test']=='php') print __LINE__.': <pre>'.print_r( $dbConfig,true) .'</pre>';

	$fields = $modx->db->getTableMetaData($dbConfig['tableName']);
//* debug */ if($_REQUEST['test']=='php') print __LINE__.' FIELDS : <pre>'.print_r($fields,true) .'</pre><br />';
	if($fields){
		foreach( $fields as $fld => &$props){
			$props = array_change_key_case($props,CASE_LOWER);

			$props['heading']= ucwords (str_replace('_',' ',$fld));
			//store 1st primary_key && auto_increment fields
			if( !$auto && $props['extra']=='auto_increment' ) $auto=$fld;
			if( !$primary && $props['key']=='PRI' ) $primary=$fld;
			if( !$unique && $props['key']=='UNI' ) $unique_key=$fld;


			$props['dbtype'] = $props['type']; //copy mysql type
			list($type,$len) = explode('(',substr($props['type'],0,-1));
			$props['maxlength'] = ( strtoupper($type)=="VARCHAR" )? $len : '';
			$props['type'] = (in_array(strtoupper($type),$modx->db->dataTypes['numeric']))?'number':
				((in_array(strtoupper($type),$modx->db->dataTypes['date']))?'date':'string');

			if(!isset($props['use'])) $props['use']=1;

			unset($props['null'],$props['key'],$props['extra']);
		}
		//set the key field to the first primary_key, auto_increment or unique_key field if possible
		$key_field = ($primary) ? $primary : ( ($auto) ? $auto : $unique );
		$fields[$key_field]['isKey'] = 1;
		$fields[$key_field]['list'] = 1;
		if( !$dbConfig['keyField']  )$dbConfig['keyField'] = $key_field;



/* debug */ if($_REQUEST['test']=='php') print __LINE__.': <pre>'.print_r($newConfig,true) .'</pre><br />';
		$dbConfig['fields'] = $dbConfig['fields'] + (array)$fields;
		$response = $dbConfig;
	}else
		$response = array('error'=>1,'msg'=>'Could not retrieve configuration details for this database table.');

	/*for development only*/
	if($_REQUEST['test']=='php'){
		print __LINE__.': <pre>'.print_r($response,true) .'</pre><br />';

	}else{
		include_once('JSON.php');
		$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		print  $json->encode($response);
	}
	exit;
?>