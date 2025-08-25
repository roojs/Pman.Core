//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.PreviewHeaderImport = {

 _strings : {
  '57f21c454b7a90784699cce8315fa4bf' :"1st Row",
  'ec211f7c20af43e742bf2570c3cb84f9' :"Add",
  'b4daba1e7a3f227329f66e17180aebcc' :"Import into Mailing List:",
  '274e4d0b0c96974c2d6e57e250622c1a' :"Example Content",
  'f7aec8fa9a417536bfb549b4bbf83af0' :"Database col",
  '1243daf593fa297e07ab03bf06d925af' :"Searching...",
  'cfcd208495d565ef66e7dff9f98764da' :"0",
  '340c2ee497b85d5954b01c64de7f44f6' :"Select Person ",
  'ea4788705e6873b424c65e91c2846b19' :"Cancel",
  'a1556f1f07081d341ab3e0eae7368cc4' :"Preview Headers from XLS",
  '81a5726cb8da16023374870e4f8282e0' :"Select a List ",
  '35c31ea9e29f774dba060916d184fe7d' :"Your Data",
  'ad3d06d03d94223fa652babc913de686' :"Validate",
  'dcce7ae3bed98022daa78cd837c7ac54' :"Select col"
 },
 _named_strings : {
  'name_qtip' : '340c2ee497b85d5954b01c64de7f44f6' /* Select Person  */ ,
  'name_emptyText' : '81a5726cb8da16023374870e4f8282e0' /* Select a List  */ ,
  'db_col_name_emptyText' : 'dcce7ae3bed98022daa78cd837c7ac54' /* Select col */ ,
  'db_col_name_value' : 'cfcd208495d565ef66e7dff9f98764da' /* 0 */ ,
  'name_loadingText' : '1243daf593fa297e07ab03bf06d925af' /* Searching... */ 
 },

 dialog : false,
 callback:  false,

 show : function(data, cb)
 {
  if (!this.dialog) {
   this.create();
  }

  this.callback = cb;
  this.data = data;
  this.dialog.show.apply(this.dialog,  Array.prototype.slice.call(arguments).slice(2));
  if (this.form) {
   this.form.reset();
   this.form.setValues(data);
   this.form.fireEvent('actioncomplete', this.form,  { type: 'setdata', data: data });
  }

 },

 create : function()
 {
   var _this = this;
   this.dialog = Roo.factory({
    xtype : 'LayoutDialog',
    closable : false,
    collapsible : false,
    height : 500,
    modal : true,
    resizable : true,
    title : _this._strings['a1556f1f07081d341ab3e0eae7368cc4'] /* Preview Headers from XLS */,
    width : 700,
    listeners : {
     show : function (_self)
      {
          if(typeof(_this.data.disableMailingList) != 'undefined' && _this.data.disableMailingList === true) {
              _this.mailing_list.hide();
              _this.mailing_list_text.hide();
              _this.mailing_list_add.hide();
          }
          _this.mailing_list.setValue('');
          
          // sort database cols
          _this.data.dbCols = _this.data.dbCols.sort(function(a, b) {
              // Always keep empty first value at the beginning
              if (a[0] === '') {
                  return -1;
              }
              if (b[0] === '') { 
                  return 1;
              }
              
              // Sort the rest alphabetically by display name (case-insensitive)
              return a[1].toLowerCase().localeCompare(b[1].toLowerCase());
          });
      
          var records = [];
          _this.data.data.headers.forEach(function(h, index) {
              var dbCol = '';
              var dbColName = '';
              // map if header name matches column display name
              _this.data.dbCols.forEach(function(c) {
                  if(h.toUpperCase() == c[1].toUpperCase()) {
                      dbCol = c[0];
                      dbColName = c[1];
                  }
              });
              
              if(typeof(_this.data.map) != 'undefined') {
                  // use provided mapping if available
                  _this.data.dbCols.forEach(function(c) {
                      if(_this.data.map[h] == c[0]) {
                          dbCol = c[0];
                          dbColName = c[1];
                      }
                  });
              }
              
              var exampleContent = '';
              
              // find a non-empty value starting from the second row
              for(var rowIndex = 1; rowIndex < _this.data.data.rows.length; rowIndex++) {
                  if(_this.data.data.rows[rowIndex][index].length > 0) {
                      exampleContent = _this.data.data.rows[rowIndex][index];
                      break;
                  }
              }
              
              records.push(new Roo.data.Record({
                  'header_name' : h,
                  'row_1': _this.data.data.rows.length > 0 ? _this.data.data.rows[0][index] : '',
                  'row_2': exampleContent,
                  'db_col': dbCol,
                  'db_col_name': dbColName
              }));
          });
          
          _this.grid.ds.removeAll();
          records.forEach(function(r) {
              _this.grid.ds.add(r);
          });
          
          var records = [];
          _this.data.dbCols.forEach(function(c){
              records.push(new Roo.data.Record({
                  'col': c[0],
                  'name': c[1],
                  'style': c[2] ? "font-weight:bold;" : ''
              }));
          });
          
          _this.grid.colModel.getColumnByDataIndex('db_col').editor.field.store.removeAll();
          records.forEach(function(r) {
              _this.grid.colModel.getColumnByDataIndex('db_col').editor.field.store.add(r);
          });
      }
    },
    xns : Roo,
    '|xns' : 'Roo',
    center : {
     xtype : 'LayoutRegion',
     xns : Roo,
     '|xns' : 'Roo'
    },
    buttons : [
     {
      xtype : 'Button',
      text : _this._strings['ea4788705e6873b424c65e91c2846b19'] /* Cancel */,
      listeners : {
       click : function (_self, e)
        {
            new Pman.Request({
                method: 'POST',
                url: _this.data.url,
                mask: 'Deleting old uploaded files',
                params: {
                    _delete: _this.data.fileId
                },
                success: function(res) {
                    _this.dialog.hide();
                }
            });
        }
      },
      xns : Roo,
      '|xns' : 'Roo'
     },
     {
      xtype : 'Button',
      text : _this._strings['ad3d06d03d94223fa652babc913de686'] /* Validate */,
      listeners : {
       click : function (_self, e)
        {
            if(typeof(_this.data.disableMailingList) == 'undefined' && _this.mailing_list.getValue() == '') {
                Roo.MessageBox.alert('Error', 'You must select a valid mailing list to import into');
                return;
            }
            
            var nameMap = {};
            Roo.each(_this.data.dbCols, arr => {
                if(arr[0] == '') {
                    return;
                }
                nameMap[arr[0]] = arr[1];
            });
            var missingEmail = true;
            
            var gotDbCols = [];
            var duplicateDbCols = [];
            
            var rec = _this.grid.getDataSource().data.items;
            var map = {};
            var colMap = {};
            Roo.each(rec, function(r,index) {
                map[r.data.header_name] = r.data.db_col;
                colMap[index] = r.data.db_col;
                if(r.data.db_col == 'email') {
                    missingEmail = false;
                }
                if(r.data.db_col.length && gotDbCols.includes(r.data.db_col)) {
                    duplicateDbCols.push(nameMap[r.data.db_col]);
                    return;
                }
                gotDbCols.push(r.data.db_col);
            });
            
            if(duplicateDbCols.length) {
                Roo.MessageBox.alert('Error', 'Duplicate mapping to ' + duplicateDbCols.join(', '));
                return;
            }
            
            if(missingEmail) {
                Roo.MessageBox.alert('Error', 'The mapping to "Email" database column is missing');
                return;
            }
            
            var total = _this.data.data.rows.length;
            var batchValidateStart = 0;
            var batchValidateLimit = 50;
            
            var validateRows = function() {
                new Pman.Request({
                    method: 'POST',
                    url: _this.data.url,
                    params: {
                        fileId: _this.data.fileId,
                        colMap: Roo.encode(colMap),
                        nameMap: Roo.encode(nameMap),
                        _validate: 1,
                        _validate_start: batchValidateStart,
                        _validate_limit: batchValidateLimit
                    },
                    success: function(res) {
                        batchValidateStart += batchValidateLimit;
                        Roo.MessageBox.updateProgress(
                            batchValidateStart / total,
                            batchValidateStart + ' / ' + total + ' rows validated'
                        );
                        if(batchValidateStart >= total) {
                            Roo.MessageBox.hide();
                            _this.dialog.hide();
                            var config = {
                                url: _this.data.url,
                                fileId: _this.data.fileId,
                                data: res.data,
                                dbCols: _this.data.dbCols,
                                validateCols: _this.data.validateCols,
                                colMap: colMap
                            };
                            
                            if(typeof(_this.data.disableMailingList) == 'undefined') {
                                config['mailingListId'] = _this.mailing_list.getValue();
                            }
                            Pman.Dialog.PreviewRowsImport.show(config);
                            return;
                        }
                        validateRows();
                    },
                    failure : function(res)
                    {
                        // show errors
                        Roo.MessageBox.show({
                            title: "Fix these issues, and try uploading again", 
                            multiline: 500,
                            value: res.errorMsg,
                            buttons: {ok: "Upload Again", cancel: "Cancel"},
                            closable: false,
                            fn: function(res) {
                                // close message box
                                if(res == 'cancel') {
                                    return;
                                }
                                
                                // delete old uploaded files
                                new Pman.Request({
                                    method: 'POST',
                                    url: _this.data.url,
                                    mask: 'Deleting old uploaded files',
                                    params: {
                                        _delete: _this.data.fileId
                                    },
                                    success: function(res) {
                                        _this.dialog.hide();
                                        // upload again
                                        Pman.Dialog.Image.show({
                                            _url : _this.data.url
        
                                        }, function (data) {
                                            var config = {
                                                url: _this.data.url,
                                                fileId: data.id,
                                                data: data.data,
                                                dbCols: _this.data.dbCols,
                                                validateCols: _this.data.validateCols,
                                                map: map
                                            };
                                            if(typeof(_this.data.disableMailingList) != 'undefined') {
                                                config.disableMailingList = _this.data.disableMailingList;
                                            }
                                            Pman.Dialog.PreviewHeaderImport.show(config);
                                        });
                                    }
                                });
                                
                            }
                        });
                    }
                });
            }
            
            Roo.MessageBox.progress("Validating Rows", "Starting");
            
            validateRows();
            
        }
      },
      xns : Roo,
      '|xns' : 'Roo'
     }
    ],
    items  : [
     {
      xtype : 'GridPanel',
      background : false,
      fitContainer : true,
      fitToframe : true,
      region : 'center',
      xns : Roo,
      '|xns' : 'Roo',
      grid : {
       xtype : 'EditorGrid',
       autoExpandColumn : 'db_col',
       clicksToEdit : 1,
       listeners : {
        render : function() 
         {
             _this.grid = this;
         }
       },
       xns : Roo.grid,
       '|xns' : 'Roo.grid',
       toolbar : {
        xtype : 'Toolbar',
        xns : Roo,
        '|xns' : 'Roo',
        items  : [
         {
          xtype : 'TextItem',
          text : _this._strings['b4daba1e7a3f227329f66e17180aebcc'] /* Import into Mailing List: */,
          listeners : {
           render : function (_self)
            {
                _this.mailing_list_text = _self;
            }
          },
          xns : Roo.Toolbar,
          '|xns' : 'Roo.Toolbar'
         },
         {
          xtype : 'ComboBox',
          allowBlank : true,
          alwaysQuery : true,
          displayField : 'name',
          editable : false,
          emptyText : _this._strings['81a5726cb8da16023374870e4f8282e0'] /* Select a List  */,
          forceSelection : true,
          listWidth : 250,
          loadingText : _this._strings['1243daf593fa297e07ab03bf06d925af'] /* Searching... */,
          minChars : 2,
          name : 'name',
          pageSize : 20,
          qtip : _this._strings['340c2ee497b85d5954b01c64de7f44f6'] /* Select Person  */,
          queryParam : 'query[name]',
          selectOnFocus : true,
          tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{name}</b></div>',
          triggerAction : 'all',
          typeAhead : true,
          valueField : 'id',
          width : 150,
          listeners : {
           render : function (_self)
            {
              _this.mailing_list = _self;
            }
          },
          xns : Roo.form,
          '|xns' : 'Roo.form',
          store : {
           xtype : 'Store',
           sortInfo : { field : 'name' , direction : 'ASC' },
           listeners : {
            beforeload : function (_self, o)
             {
                 o.params = o.params || {};
                 o.params['filter_criteria_type'] = 'MANUAL';
             }
           },
           xns : Roo.data,
           '|xns' : 'Roo.data',
           proxy : {
            xtype : 'HttpProxy',
            method : 'GET',
            url : baseURL + '/Roo/Crm_mailing_list.php',
            xns : Roo.data,
            '|xns' : 'Roo.data'
           },
           reader : {
            xtype : 'JsonReader',
            fields : [
                {
                    'name': 'id',
                    'type': 'int'
                },
                {
                    'name': 'name',
                    'type': 'string'
                }
            ],
            id : 'id',
            root : 'data',
            totalProperty : 'total',
            xns : Roo.data,
            '|xns' : 'Roo.data'
           }
          }
         },
         {
          xtype : 'Button',
          text : _this._strings['ec211f7c20af43e742bf2570c3cb84f9'] /* Add */,
          listeners : {
           click : function (_self, e)
            {
                Pman.Dialog.CrmMailingList.show(
                    { id : 0,  owner_id : Pman.Login.authUserId, is_import : 1} ,
                    function(res) {
                      _this.mailing_list.setFromData(res);
                   }
                ); 
            },
           render : function (_self)
            {
                _this.mailing_list_add = _self;
            }
          },
          xns : Roo.Toolbar,
          '|xns' : 'Roo.Toolbar'
         },
         {
          xtype : 'Separator',
          xns : Roo.Toolbar,
          '|xns' : 'Roo.Toolbar'
         }
        ]
       },
       dataSource : {
        xtype : 'SimpleStore',
        fields : [
            {name: 'header_name', type: 'string'},
            {name: 'row_1', type: 'string'},
            {name: 'row_2', type: 'string'},
            {name: 'db_col', type: 'string'}
        ],
        xns : Roo.data,
        '|xns' : 'Roo.data'
       },
       colModel : [
        {
         xtype : 'ColumnModel',
         dataIndex : 'header_name',
         header : _this._strings['35c31ea9e29f774dba060916d184fe7d'] /* Your Data */,
         renderer : function(v,x,r)
         {
             return String.format('{0}', v);
         },
         width : 150,
         xns : Roo.grid,
         '|xns' : 'Roo.grid'
        },
        {
         xtype : 'ColumnModel',
         dataIndex : 'row_1',
         header : _this._strings['57f21c454b7a90784699cce8315fa4bf'] /* 1st Row */,
         renderer : function(v,x,r)
         {
             return String.format('{0}', v);
         },
         width : 150,
         xns : Roo.grid,
         '|xns' : 'Roo.grid'
        },
        {
         xtype : 'ColumnModel',
         dataIndex : 'row_2',
         header : _this._strings['274e4d0b0c96974c2d6e57e250622c1a'] /* Example Content */,
         renderer : function(v,x,r)
         {
             return String.format('{0}', v);
         },
         width : 150,
         xns : Roo.grid,
         '|xns' : 'Roo.grid'
        },
        {
         xtype : 'ColumnModel',
         dataIndex : 'db_col',
         header : _this._strings['f7aec8fa9a417536bfb549b4bbf83af0'] /* Database col */,
         renderer : function(v,r,x)
         {
             return String.format('{0}', (v) ? x.data.db_col_name : '');
         },
         width : 150,
         xns : Roo.grid,
         '|xns' : 'Roo.grid',
         editor : {
          xtype : 'GridEditor',
          xns : Roo.grid,
          '|xns' : 'Roo.grid',
          field : {
           xtype : 'ComboBox',
           allowBlank : true,
           displayField : 'name',
           editable : false,
           emptyText : _this._strings['dcce7ae3bed98022daa78cd837c7ac54'] /* Select col */,
           hiddenName : 'db_col',
           mode : 'local',
           name : 'db_col_name',
           selectOnFocus : false,
           tpl : '(function(x) {\n    Roo.log(\'A\');\n    Roo.log(x);\n    return \'<div class=\"x-grid-cell-text x-btn button\">{name}{required}</div>\';\n})()',
           triggerAction : 'all',
           typeAhead : true,
           value : 0,
           valueField : 'col',
           width : 150,
           xns : Roo.form,
           '|xns' : 'Roo.form',
           store : {
            xtype : 'SimpleStore',
            fields : [ {name: 'col', type: 'string'}, { name: 'name', type: 'string'}, {name: 'required', type: 'boolean'}],
            xns : Roo.data,
            '|xns' : 'Roo.data'
           }
          }
         }
        }
       ]
      }
     }
    ]
   });
 }
};
