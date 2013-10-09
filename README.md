dbEdit
================================================================================

Edit arbitrary databases from within the manager for the MODX Evolution content management framework

Installation:
--------------------------------------------------------------------------------
1. Upload the folder *assets/modules/dbedit* in the corresponding folder in your installation.
2. Create a new module. Give it a name such as "dbEdit" (this is what will appear in the menu under "Modules").
3. Copy the contents of dbedit.module.php.txt to the Module Code box
4. Copy the following line into the Module Configuration box on the Configuration tab:

```
&mod_name=Module name;string;dbEdit
&mod_path=Module path;string;assets/modules/dbedit/
&dbedit_date_format=Date Format (use d,D,m,M,F,n,y,Y as in php date() with simple separator);string;d F Y
```

Reload the manager and access the module under the modules tab.

Advanced Settings:
--------------------------------------------------------------------------------

Currently there are only six built-in advanced setting:

###select_sql

Instead of the 'list' checkboxes you can use an SQL statement for the main records list. Field (or alias) names will be used for the column headings with underscores translated to spaces. 

To be able to use the record filter with select_sql there are a couple of 'placeholders' you must use inside the SQL statement:

- {FILTER} will be replaced with the record search terms. This uses the sql HAVING statement.	
- {WHERE} will be replaced with the WHERE clause build from the deleted_field & filter values as set in the table configuration. dbEdit is clever enough to detect if you already have a WHERE and/or HAVING clause in your sql
		
####Examples for select_sql: 

```
SELECT t1.recid, concat(name,' - ',category) as product_title,description FROM `products` as t1 INNER JOIN (categories as t2) ON (t1.cat_id=t2.recid) {WHERE} {FILTER} ORDER BY category ASC, name ASC`
```

```
SELECT recid, product_name as name ,description FROM `products` WHERE cat_id=122 {WHERE} {FILTER}
```

with a filter this would result in

```
SELECT recid, name, description FROM `products` WHERE cat_id=122 AND deleted='0' HAVING name LIKE '%jacket%'
```
		
###hide_add

If set to true, this will hide the "New Record" button. 	

###hide_export

If set to true, this will hide the "CSV Export" button. 	

###hide_delete

If set to true, this will hide the "Delete" button.

###view_only

If set to true, open the Record window in view only mode.

###pdf_export

If set to true, add a PDF export button for each row.
       
You can also create your own advanced settings. These will not have any effect in dbEdit itself without extra coding but I have used them on occasion as extra parameters for snippets.