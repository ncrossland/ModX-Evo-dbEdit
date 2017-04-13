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
	var dbObject;
	var request = false;
	var processAjaxResult = function(v) {
			if(v.error || !v.tableName){
				msg = (v.msg)?v.msg:'An undefined error occurred while retrieving table info.';
				var el = new Element('span', {
					'html': msg,
					'styles':{'display':'block', 'color':'red'}
					});
				el.inject($('error-reponse'));
			}else{
				dbObjectClass.implement(v); //sneaky way to populate object
				dbObject = new dbObjectClass();
				//dbObject.debugPrint();
				dbObject.makeForm();
			}
			$('wait-for-me').getFirst('img').dispose();
			$('wait-for-me').getFirst('span').removeClass('waiting').set({'html':'Fill in the table name to expose its properties below.'});
		};

/**
 *
 * @access public
 * @return void
 **/
function ajaxLoadConfig(el){
	tablename= (el)?el.getProperty('value'):false;
	if(dbObject) dbObject.reset();
	if(!DBEDIT_DB_ID && !DBEDIT_TABLE_NAME && !tablename) return;
	if(DBEDIT_DB_ID) q = '&db='+DBEDIT_DB_ID;
	else q = (tablename) ? '&tbl='+tablename : '&tbl='+DBEDIT_TABLE_NAME;

	img = new Element('img',{'src':'../assets/modules/dbedit/images/loader.gif','align':'absmiddle'});
	$('wait-for-me').getFirst('span').set({'text':'Loading Configuration','class':'waiting'});

	img.inject($('wait-for-me'),'top');
	var request = new Request.JSON({
		secure:true,
		url: DBEDIT_MODULE_URL+'&dba=118'+q,
		onComplete: function(jsonObj) {
			processAjaxResult(jsonObj);
		}
	}).get();
}

function addSetting(c,sn,sv){ //source,setting_name,setting_value
	r = new Element('tr');
	tn = new Element('td'); tv = new Element('td');
	tb = new Element('td');
	n = new Element('input',{'name':'prop_settingsname[]','type':'text','size':'15','maxlength':'50','class':'setting-name'}).inject(tn);
	if(sn) n.set('value',sn);
	v = new Element('input',{'name':'prop_settingsvalue[]','type':'text','size':'35','maxlength':'512','class':'setting-value'}).inject(tv);
	if(sv) v.set('value',sv);
	b1 = new Element('input',{'value':'+','type':'button','onClick':'addSetting(this)'}).inject(tb);
	b2 = new Element('input',{'value':'-','type':'button','onClick':'removeSetting(this)'}).inject(tb);
	tn.inject(r); tv.inject(r); tb.inject(r);
	if(c) r.inject(c.getParent('tr'),'after');
	else r.inject($('row_settings'));
}

function removeSetting(r){
	if(!r) $('row_settings').getLast('tr').dispose();
	else r.getParent('tr').dispose();
}

dbObjectClass = new Class({
	initialize: function(name){
		if(name) this.moduleName = name;
	},
	setType:function(fld,h){ this.fields[fld].type = h.value; },
	setSize: function(fld, h){ this.fields[fld].size = h.value; },
	setDefault: function(fld,h){ this.fields[fld].defaultValue = h.value; },
	setHeading:function(fld,h){ this.fields[fld].heading = h.value; },
	makeForm:function(){
		td = new Element('td');
		tr = new Element('tr');
		tbl = $('fieldsArray').empty();//new Element('table',{'id':'fields-table'});
		var ch = new Element('input',{'type':'checkbox'});
		var txt = new Element('input',{'type':'input','styles':{'width':'75px'}});
		var opt = new Element('option');
		$('trash_field').empty();
		o=opt.clone().set({'value':'','text':'none'}).inject($('trash_field'),'top');
		var i=0;
		$each(this.fields, function(itm,fld){
			c=ch.clone();t=td.clone();r=tr.clone();
			c.set({'name':'enabledFields['+i+']','value':fld,'onClick':'dbObject.toggleUseField(this)'});
			if(itm.use) c.set({'checked':'checked'});
			c.inject(t);
			t.inject(r);
			t1=td.clone().set({'text':fld}).inject(r);
			t2 = td.clone();
			c1=ch.clone().set({'name':'listFields['+i+']','value':fld,'onClick':'dbObject.toggleListField(this)'});
			if(itm.list) c1.set({'checked':'checked'});
			c1.inject(t2);
			t2.inject(r);
			xf = txt.clone();  t4=td.clone();
			xf.set({'name':'fieldTypes['+i+']','value':itm.type,'onChange':'dbObject.setType(\''+fld+'\',this)'});
			xf.inject(t4); t4.inject(r);
			xh = txt.clone();  t5=td.clone();
			xh.set({'name':'fieldHeadings['+i+']','value':itm.heading,'onChange':"dbObject.setHeading('"+fld+"',this)"});
			xh.inject(t5); t5.inject(r);
			xv = txt.clone().set({'name':'fieldDefaults['+i+']','value':itm.defaultValue,'onChange':"dbObject.setDefault('"+fld+"',this)"});
			t6=td.clone(); xv.inject(t6);
			t6.inject(r);
			r.inject(tbl);
			//add field to trash selectbox
			if(!itm.isKey){
				o = opt.clone(); o.set({'value':fld,'text':fld});
				if(this.dbObject.deletedField==fld) {
					o.set('selected','selected');
					$('trash_deleteVal').set( {'value':this.dbObject.deletedValue} );
					$('trash_undeleteVal').set( {'value':this.dbObject.enabledValue} );
					$('trash_props').removeClass('hide');
				}
				o.inject($('trash_field'));
				t6=td.clone().inject(r);
			}else{
				//fill in key field
				t6=td.clone();
				c3=ch.clone().set({'name':'keyField','value':fld,'disabled':'disabled','checked':'checked'}).inject(t6);
				$('prop_keyfield').set({'value':fld,'disabled':'disabled'});
			}
			t6.inject(r);
			i++;
		});
		$('prop_title').set('value',this.title);
		$('prop_description').set('value',this.description);
		$('prop_tablename').set('value',this.tableName);
		$('prop_sort').set('value',this.sort);
		$('prop_filter').set('value',this.filter);
		if(DBEDIT_DB_ID)$('prop_tablename').set('disabled','disabled');
		$('dbeTableProperties').removeClass('disabled').addClass('enabled');
		$('fieldsMsg').addClass('disabled');
		//settings
		j=0;
		$each(this.settings, function(itm,idx){
			if(!j){ //existing input tag
				in_nm = $('row_settings').getElement('input.setting-name').set('value',idx);
				in_val = $('row_settings').getElement('input.setting-value').set('value',itm);
				j++;
			}else{
				addSetting(null,idx,itm);
			}

		});
	},
	reset: function(){
		delete this.fields;
		delete this.settings;
		delete this.tableName;
		delete this.keyField;
		$('dbeTableProperties').removeClass('enabled').addClass('disabled');
		$('fieldsMsg').removeClass('disabled');
		nm = $('row_settings').getElements('input.setting-name');
		vl = $('row_settings').getElements('input.setting-value');
		lb = nm.length;
		$each(nm, function(itm,idx){
			if(idx>0){
				removeSetting(itm);
			}else{
				itm.value = '';
				vl[idx].value='';
			}
		});

	},
	toggleListField: function(fld){
		f=this.fields[fld.value];
		if(f) f.list=(f.list)?0:1;
	},
	toggleLinkField: function(fld){
		f=this.fields[fld.value];
		if(f) f.linked=(f.linked)?0:1;
	},
	toggleUseField: function(fld){
		f=this.fields[fld.value];
		if(f) f.use=(f.use)?0:1;
	},
	submitData: function(){
		this.title = $('prop_title').value;
		this.description = $('prop_description').value;
		this.moduleName = this.title;
		if(this.tableName.lenght==0 ){ alert('nothing to save'); return false; }
        if(this.title.lenght < 2 ){ alert('Please provide a caption for this configuration.');return false;}
        //grab extra fields
		frm = $('form_mutate');
		//trashField
		trash = $('trash_field').value;
		if(trash.length!=0){
			this.deletedField = trash;
			this.deletedValue = frm.trash_deleteVal.value;
			this.enabledValue = frm.trash_undeleteVal.value
		}else{
			frm.trash_deleteVal.value='';
			frm.trash_undeleteVal.value='';
		}
		//grab extra settings from form
		names = $('row_settings').getElements('input.setting-name');
		vals = $('row_settings').getElements('input.setting-value');
		x = new Hash();
		$each(names, function(itm,idx){
			x.set(itm.value,vals[idx].value);
		});
		this.settings = x;
		//sorting
		if($('prop_sort').value.length>0) this.sort = $('prop_sort').value;
		//filter
		if($('prop_filter').value.length>0) this.filter = $('prop_filter').value;
		json = JSON.encode(this);
		$('json_data').set('value',json);
		$('prop_dbid').set('value',DBEDIT_DB_ID);
		$('form_mutate').submit();
		//submitRequest(json);

	},
	debugPrint:function(){
		if($('debug-window')){
			$('debug-window').empty();
		}else{
			var el = new Element('div', {'id':'debug-window','styles':{'background':'white','border':'2px solid black'}});
			el.inject(document.body,'top');
		}
		var html ='';
		pr = new Element('p',{'html':'Title: '+$('prop_title').value}).inject($('debug-window'));
		pr = new Element('p',{'html':'Description: '+$('prop_description').value}).inject($('debug-window'));
		pr = new Element('p',{'html':'FIELDS'}).inject($('debug-window'));
		$each(this.fields,function(fld,name){
			html += name + " = ";
			html += fld.heading + ", ";
			html += fld.type + ", ";
			//html += 'db type: ' + fld.dbtype + ", ";
			if(fld.defaultValue) html += 'default: ' + fld.defaultValue + ", ";
			use = (fld.use)?fld.use:0;
			html += 'Use:' + use + ", ";
			list = (fld.list)?fld.list:0;
			html += 'List:' +list + ", ";
			linked = (fld.linked)?fld.linked:0;
			html += 'Link:' +linked + ", ";
			if(fld.isKey) html += 'is Key' + "";
			pr = new Element('p');
			pr.set({'html':html}).inject($('debug-window'));
			html='';
		});
	}
});

/**
 * function to show output of json.get.config server request while developing
 * Will be removed!
 **/
function setHref(lnk,q){
	url = DBEDIT_MODULE_URL +"&dba=118";
	DBEDIT_TABLE_NAME = (dbObject.tableName)?dbObject.tableName:DBEDIT_TABLE_NAME;
	if(!DBEDIT_TABLE_NAME && !DBEDIT_DB_ID) return false;
	url+= (DBEDIT_DB_ID)?'&db='+DBEDIT_DB_ID:'';
	url+= (DBEDIT_TABLE_NAME) ? '&tbl='+DBEDIT_TABLE_NAME : '';
	url+= q;
	lnk.set({'href':url});
}

function trashChange(fld){
	f = $('trash_props');
	if(!f){ return; }
	if(fld.value.length==0){
		f.addClass('hide');
	}else{
		f.removeClass('hide');
	}
}


