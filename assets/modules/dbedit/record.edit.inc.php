<?php

/**
 * <?php
/**
 * Edit record script for dbEdit Table Editor module.
 *
 * @package dbEdit Table Editor
 * @version 1.0
 * @author Jelle Jager
 * @copyright 2008 Jelle Jager
 * @license GPL
 *
 * @todo -c Implement .Add Language vars
 * @todo -c Implement .Permission checking
 * @todo -c Implement .Review javascript code
 */
?>
<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
	global $base_url;
	$image_manager_script = "
					<script type=\"text/javascript\">
							var lastImageCtrl;
							var lastSrcImage;
							function OpenServerBrowser(url, width, height ) {
								var iLeft = (screen.width  - width) / 2 ;
								var iTop  = (screen.height - height) / 2 ;

								var sOptions = 'toolbar=no,status=no,resizable=yes,dependent=yes' ;
								sOptions += ',width=' + width + ',height=' + height ;
								sOptions += ',left=' + iLeft +',top=' + iTop;

								window.open( url, 'FCKBrowseWindow', sOptions ) ;
							}

							function BrowseServerImage(ctrl,imgSrc) {
								lastImageCtrl = ctrl;
								lastSrcImage = imgSrc;
								var w = screen.width * 0.7;
								var h = screen.height * 0.7;
								OpenServerBrowser('".$base_url."manager/media/browser/mcpuk/browser.html?Type=images&Connector=".$base_url."manager/media/browser/mcpuk/connectors/php/connector.php&ServerPath=".$base_url. "', w, h);
							}

							//callback function for image browser
							function SetUrl(url, width, height, alt){
								if(!lastImageCtrl) return;
								var c = document.mutate[lastImageCtrl];
								if(c) c.value = url;
								updateImgSource(lastSrcImage,lastImageCtrl);
								lastImageCtrl = '';
							}

							function updateImgSource(ImageCtrl,valueCtrl){
								var v = document.mutate[valueCtrl];
								if(v){
									p = v.value.lastIndexOf('/')+1;
									var path = v.value.substring(0,p);
									var filename = v.value.substring(p);
									 url = '" .$base_url."' + path + '.thumb_'+filename;
								}else return;

								var i = document.images[ImageCtrl];
								if(i){
									i.src = url;
									i.style.display='block';
								}
							}


							function clearField(ctrl){
								var v = document.mutate[ctrl];
								if(v) v.value='';
							}
							function clearImage(ctrl){
								var v = document.mutate['fld_'+ctrl];
								if(v) v.value='';
								var i = document.images['img_'+ctrl];
								if(i) i.src='".$base_url."assets/modules/dbedit/images/no_image.gif';
								//i.style.display='none';
							}

					</script>\n";


	$table_meta_data = $modx->db->getTableMetaData( $dbConfig['tableName'] );

	$fields = $dbConfig['fields'];

	//get data for editing
	//which fields
	foreach($dbConfig['fields'] as $fld => $props){
		if($props['use']){
			$sql_fields .= $c . $fld;
			$c=",";
		}
	}

	if($ra=="edit"){ //only get details if action is edit
		//add quotes if rn is not a number
		if(!is_numeric($rn)) $rn = "'".$rn."'";
		$rsc = $modx->db->select($sql_fields, $dbConfig['tableName'],$dbConfig['keyField']."=$rn");
		if($modx->db->getRecordCount($rsc)==1){
			$row = $modx->db->getRow($rsc);
		}else{
			print $modx->db->getRecordCount($rsc) . "records were found.";
		}
	}else{
		foreach(array_keys($fields) as $k ){ $row[$k]=""; }//initialize empty $row var
		unset ($rn); //precaution don't want to confudge anything
	}

	//build form
	$tableRows = $hiddenFields = ""; //initialize vars
	foreach($fields as $fld => $props){
		if( !$props['use'] && !$props['list'] ) continue; //skip fields set as not used

		$field_meta_data = $table_meta_data[$fld];
		//hide key field if it's auto-increment
		if( $props['isKey'] && $field_meta_data['Extra']=='auto_increment' ){
			$hiddenFields .= "<input type=\"hidden\" name=\"fld_".$fld."\" value=\"".$row[$fld]."\">";
			continue;
		}

		//hide the "deleted" field if any.
		if( $fld==$dbConfig['deletedField'] ){
			$hiddenFields .= "<input type=\"hidden\" name=\"fld_".$fld."\" value=\"".$row[$fld]."\">";
			continue;
		}

//need to make sure that test excludes values that are merely being updated!
//can do this in query by excluding keyfield with old (existing) value....
		$jsEvent = ($field_meta_data['Key']=='UN' || $field_meta_data['Key']=='primary_key' )?
			" onChange=\"js_checkUnique('$fld','".$row[$fld]."',this)\"":"";

		$tableRows .= "<tr valign=\"top\"><th scope=\"row\">".$props['heading']."</th><td>";

		//disable field if it's auto-increment
		if( strstr($field_meta_data['Extra'],'auto_increment') ){
			$hiddenFields .= "<input type=\"hidden\" name=\"fld_".$fld."\" value=\"".$row[$fld]."\">";
			$tableRows .= "<input type=\"text\" name=\"fld_{$fld}_disabled\" size=\"15\" value =\"".$row[$fld]."\" disabled=\"disabled\" />";
			continue;
		}

		//check for any bindings
		unset($values);
		$type = $props['type'];
		if(strstr($type,"@")){
			list($type,$extra)=explode("@",$type,2);
			list($cmd,$extra) = explode(' ',$extra,2);
			//get values
			switch($cmd){
				case 'SELECT':
					$ds = $modx->db->query( 'SELECT ' . $modx->db->escape($extra) );
					if($ds){
						$cols = $modx->db->numFields($ds); //no dbapi equivalent
						while( $rw = $modx->db->getRow($ds,'num') ){
							if($cols==1) $values[$rw[0]] = $rw[0]; //overwrites duplicates
							elseif($cols==3){
								$values[$rw[0]][$rw[1]] = $rw[2];
							}
							else $values[$rw[0]]=$rw[1];//overwrites duplicate keys (only showing last value)
						}
					}
					if($cols==3) ksort($values,SORT_LOCALE_STRING);
					break;
				case 'VALUES':
					$onecol = (strpos($extra,'==')==false);
					$extra = explode('||',$extra);
					foreach($extra as $v){
						if($onecol) $k=$v; else list($k,$v) = explode('==',$v);
						$values[$k] = $v;
					}
					break;
			}
			unset($extra);
		}

		$limit = "";
		$size =20;
		//What to display for each type
		/*types can be any of the following:
		  string,text,textarea,RichText,integer,float(number),currency,date,time,select,
		  multiselect,radio,checkbox,image,file,url,email
		  Additionally the following 2 bindings are supported for select, multiselect, radio, checkbox:
		  @SELECT (eg
			   type="select@SELECT value,caption,option_group from `cities` SORT option_group ASC";
			   (3rd column value will be used as option-groups)
				type="radio@SELECT value,caption from `cities` WHERE country = 'Australia";
		  (if three columns are used the 3rd column will be used as optiongroup)
		  @VALUES
			  type="radio@VALUES 2||3||4||5||6||7||8"; will be used for both caption and value
			  type="select@VALUES Three==3||Two==2||One==1";

		  With bindings you must ensure the data type of the values corresponds to the mysql data type.
        */
	if (!(isset($dbConfig['settings']['view_only']) && $dbConfig['settings']['view_only'])) {
        switch(trim(strtolower($type))){
			case "textarea":
				$tableRows .= "<textarea cols=\"60\" rows=\"10\" name=\"fld_".$fld."\">".$row[$fld]."</textarea>";
				break;
			case "multiselect":
				$multi_select = " multiple=\"multiple\"";
				$brackets = '[]';
				$row[$fld] = explode(',',$row[$fld]);
			case "select":
				$tableRows .= "<select name=\"fld_{$fld}{$brackets}\"{$multi_select}{$jsEvent}>\n";
				if(is_array(current($values))){
					reset($values);
					foreach($values as $group => $option){
						$tableRows .= "<optgroup label=\"$group\">\n";
						foreach($option as $k => $v){
							if( is_array($row[$fld]) ){
								$selected = (in_array($v,$row[$fld]))?" selected=\"selected\"":"";
							}else
								$selected = ($v==$row[$fld])?" selected=\"selected\"":"";

							$tableRows .= "<option value=\"{$v}\"$selected>{$k}</option>\n";
						}
						$tableRows .= "</optgroup>\n";
					}
				}else{
					foreach($values as $k=>$v){
						if( is_array($row[$fld]) ){
							$selected = (in_array($v,$row[$fld]))?" selected=\"selected\"":"";
						}else
							$selected = ($v==$row[$fld])?" selected=\"selected\"":"";

						$tableRows .= "<option value=\"".$v."\"$selected>".$k."</option>\n";
					}
				}
				$tableRows .= "</select>\n";
				break;
			case "checkbox":
				foreach($values as $k=>$v){
					$selected = ($v==$row[$fld])?" checked=\"checked\"":"";
					$tableRows .= "<input type=\"checkbox\" name=\"fld_{$fld}[]\" value=\"{$v}\"$selected>&nbsp;{$k}\n";
				}
				break;
			case "radio":
				foreach($values as $k=>$v){
					$selected = ($v==$row[$fld])?" checked=\"checked\"":"";
					$tableRows .= "<input type=\"radio\" name=\"fld_{$fld}\" value=\"{$v}\"$selected>&nbsp;{$k}\n";
				}
				break;
			case "password":
				$tableRows .= "<input type=\"password\" name=\"fld_$fld\" size=\"$size\" Limit=\"$limit\" value =\"\"$jsEvent>&nbsp;<span class=\"comment\">Stored passwords are encrypted and cannot be viewed. You can however enter a new password if required<span>";
				break;
			case "option":
				$values = explode(";",$extra);
				$tableRows .= "<select name=\"fld_".$fld."\"$jsEvent>\n";
				foreach($values as $val){
					list($k,$v) = explode("=",$val);
					$selected = ($v==$row[$fld])?" selected=\"selected\"":"";
					$tableRows .= "<option value=\"".$v."\"$selected>".$k."</option>\n";
				}
				$tableRows .= "</select>\n";
				break;
			case "image":	// handles image fields using htmlarea image manager
				global $_lang;
				global $ImageManagerLoaded;
				if (!$ImageManagerLoaded){
					$tableRows .= $image_manager_script;
					$ImageManagerLoaded  = true;
				}
				$img_src = (!empty($row[$fld])) ? $base_url.dirname($row[$fld]).'/.thumb_'.basename($row[$fld]) : $base_url.'assets/modules/dbedit/images/no_image.gif';
				//$display_css = ($img_src)?'display:block;':'display:none;';
				$display_css = 'display:block;';
				$tableRows .='
				<div style="clear:left;">
					<img id="img_'.$fld .'" src="'.$img_src.'" style="'.$display_css.'border:1px solid #ccc;margin:2px;float:left;" />
					<input style="display:inline" type="text" id="fld_'.$fld.'" size="25" name="fld_'.$fld.'"  value="'.$row[$fld] .'" '.$field_style.' onChange="updateImgSource(\'img_'.$fld.'\',\'fld_'.$fld.'\');" /><br />
					<input type="button" value="'.$_lang['insert'].'" onclick="BrowseServerImage(\'fld_'.$fld.'\',\'img_'.$fld.'\')" /><input type="button" value="clear" onclick="clearImage(\''.$fld.'\')" />
				</div>';
				break;
			case 'date':
				if($row[$fld]=='0000-00-00')
					$row[$fld] = $dt_val ='';
				else{
					$dt_arr = explode('-',$row[$fld]);
					$dt_val = date($dbedit_date_format,strtotime("{$dt_arr[1]}/{$dt_arr[2]}/{$dt_arr[0]}"));
				}
				$tableRows .= "<input  type=\"text\" id=\"date_{$fld}\" name=\"fld_{$fld}\"  size=\"{$size}\" value =\"{$dt_val}\"{$jsEvent}/>";
				$date_script .="cal_{$fld} = new Calendar({ date_{$fld}: '{$dbedit_date_format}' }, { classes: ['alternate'], navigation: 2 });\n";
				break;
			case "readonly":
				$tableRows .= "<input{$class} type=\"hidden\" name=\"fld_$fld\" value =\"".$row[$fld]."\" />".$row[$fld];
				break;		
			case "string":
					//$size = min($field_meta_data['len'],76);
					$limit = $props['maxlength']? " maxlength=\"".$props['maxlength']."\"":'';			
			default:
				$tableRows .= "<input{$class} type=\"text\" name=\"fld_$fld\" size=\"$size\"$limit value =\"".$row[$fld]."\"$jsEvent />";
				break;
		}
		if($jsEvent) $tableRows .= "<span style=\"color:red;font-weight:bold;\" id=\"response_$fld\"></span>\n";
		unset($jsEvent);
	} else { // view only
		$tableRows .= "<div>" . $row[$fld] . "</div>";
	}
        $tableRows .= "</td>\n</tr>\n";
    }//foreach

?>
<script language="JavaScript">
function deleteRecord() {
	uri = "index.php?id=" + document.mutate.id.value+ "&a=" +document.mutate.a.value+ "&db=" +document.mutate.db.value  + "&ra=delete&rn="+document.mutate.rn.value;
	if(confirm("Are you sure you want to <?php echo (isset($dbConfig['deletedField']))?"move this record to the trash bin?":"permanently delete this record?"; ?>")==true) {
		document.location.href=uri
	}
}
function cancelEdit(){
	document.location.href = "index.php?id=" + document.mutate.id.value + "&a=" +document.mutate.a.value + "&db=" +document.mutate.db.value
}
</script>
<?php

if($date_script){
?>
<script  type="text/javascript" src="<?php echo $site_url; ?>/assets/modules/dbedit/calendar/calendar.js" type="text/javascript"></script>
	<script  type="text/javascript">
	window.addEvent('domready', function() {
		<?php echo $date_script; ?>
	});
</script>
<link rel="stylesheet" type="text/css" href="<?php echo $site_url; ?>assets/modules/dbedit/calendar/calendar.css" />
<?php
}
?>
<form name="mutate" method="post" action="index.php?id=<?php echo $module_id; ?>">
<input type="hidden" name="a" value="<?php echo $_REQUEST['a']; ?>">
<input type="hidden" name="id" value="<?php echo $module_id; ?>">
<input type="hidden" name="rn" value="<?php echo $rn; ?>">
<input type="hidden" name="ra" value="update">
<input type="hidden" name="db" value="<?php echo $db_id; ?>">

<h1><?php echo $mod_name; ?></h1>
<div id="actions">
	<ul class="actionButtons">
		<?php if (!(isset($dbConfig['settings']['view_only']) && $dbConfig['settings']['view_only'])) { ?>

			<li id="Button1">
				<a onclick="documentDirty=false; document.mutate.save.click();">
					<img src="media/style/<?php echo $manager_theme; ?>images/icons/save.png" align="absmiddle">&nbsp;Save</a>
				<span class="plus"> + </span>
				<select id="stay" name="stay">
					<option id="stay1" value="1" <?php echo $_REQUEST['stay'] == '1' ? ' selected=""' : '' ?> ><?php echo $_lang['stay_new'] ?></option>
					<option id="stay2" value="2" <?php echo $_REQUEST['stay'] == '2' ? ' selected="selected"' : '' ?> ><?php echo $_lang['stay'] ?></option>
					<option id="stay3" value=""  <?php echo $_REQUEST['stay'] == '' ? ' selected=""' : '' ?>  ><?php echo $_lang['close'] ?></option>
				</select>
			</li>
			<li id="Button2">
				<a onclick="deleteRecord();">
					<img src="media/style/<?php echo $manager_theme; ?>images/icons/delete.gif" align="absmiddle">&nbsp;Delete</a>
			</li>
		<?php } ?>

		<li id="Button3">
			<a onclick="cancelEdit();">
			<img src="media/style/<?php echo $manager_theme; ?>images/icons/cancel.png" align="absmiddle">&nbsp;Cancel</a>
		</li>
	</ul>
</div>
<br />
<div class="sectionHeader"><?php if (!(isset($dbConfig['settings']['view_only']) && $dbConfig['settings']['view_only'])) { ?>Edit<?php } else { ?>View<?php } ?> Record</div>
<div class="sectionBody">
<?php
	if( isset($_SESSION['dbedit_message'])){
		print "<div class=\"dbe-message-{$_SESSION['dbedit_message'][0]}\">".$_SESSION['dbedit_message'][1].'</div>';
	}
	unset($_SESSION['dbedit_message']);
?>

<style>
.db-edit-table th{ width:150px; text-align:left; font-size:12px;}
.db-edit-table td{ text-align:left; font-size:12px;}
.db-edit-table input{font-size:12px;}
.db-edit-table th, .db-edit-table td{ border-bottom:1px solid #ccf; padding: 2px; }
</style>
<table  class="db-edit-table">
<?php echo $tableRows; ?>
<?php echo $hiddenFields; ?>
</table>
<input type="submit" name="save" style="display:none;">
</div>
</form>