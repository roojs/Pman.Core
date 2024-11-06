//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.XLSImport = {

 _strings : {
  '57f21c454b7a90784699cce8315fa4bf' :"1st Row",
  'f77f8c0e4a05a384a886554d76cbd6b1' :"Import XLS",
  '72d6d7a1885885bb55a565fd1070581a' :"Import",
  'f7aec8fa9a417536bfb549b4bbf83af0' :"Database col",
  'cfcd208495d565ef66e7dff9f98764da' :"0",
  'ea4788705e6873b424c65e91c2846b19' :"Cancel",
  '5578591ead1e76e5eca8723f992950e1' :"2st Row",
  '35c31ea9e29f774dba060916d184fe7d' :"Your Data",
  'dcce7ae3bed98022daa78cd837c7ac54' :"Select col"
 },
 _named_strings : {
  'db_col_name_emptyText' : 'dcce7ae3bed98022daa78cd837c7ac54' /* Select col */ ,
  'db_col_name_value' : 'cfcd208495d565ef66e7dff9f98764da' /* 0 */ 
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
    title : _this._strings['f77f8c0e4a05a384a886554d76cbd6b1'] /* Import XLS */,
    width : 700,
    listeners : {
     show : function (_self)
      {
          var records = [];
          _this.data.data.headers.forEach(function(h, index) {
              var dbCol = '';
              var dbColName = '';
              _this.data.dbCols.forEach(function(c) {
                  if(h.toUpperCase() == c[1].toUpperCase()) {
                      dbCol = c[0];
                      dbColName = c[1];
                  }
              });
              
              if(typeof(_this.data.map) != 'undefined') {
                  
              }
              
              records.push(new Roo.data.Record({
                  'header_name' : h,
                  'row_1': _this.data.data.rows.length > 0 ? _this.data.data.rows[0][h] : '',
                  'row_2': _this.data.data.rows.length > 1 ? _this.data.data.rows[1][h] : '',
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
                  'name': c[1]
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
            _this.dialog.hide();
        }
      },
      xns : Roo,
      '|xns' : 'Roo'
     },
     {
      xtype : 'Button',
      text : _this._strings['72d6d7a1885885bb55a565fd1070581a'] /* Import */,
      listeners : {
       click : function (_self, e)
        {
            var rec = _this.grid.getDataSource().data.items;
            var map = {};
            
            Roo.each(rec, function(r) {
                map[r.data.header_name] = r.data.db_col;
            });
            
            new Pman.Request({
                method: 'POST',
                url: _this.data.url,
                mask: 'Validating',
                params: {
                    fileId: _this.data.fileId,
                    colMap: Roo.encode(map),
                    _validate: 1
                },
                success: function(res) {
                    Roo.log('SUCCESS');
                },
                failure : function(res)
                {
                    Roo.MessageBox.show({
                        title: "Fix these issues, and try uploading again", 
                        multiline: 500,
                        value: res.errorMsg,
                        buttons: {ok: "Upload Again", cancel: "Cancel"},
                        closable: false,
                        fn: function(res) {
                            console.log(res);
                            if(res == 'cancel') {
                                _this.dialog.hide();
                                return;
                            }
                            
                            Pman.Dialog.Image.show({
                                _url : baseURL + '/PressRelease/Import/Journalist'
        
                            }, function (data) {
                                _this.data.fileId = data.id;
                                _this.data.data = data.data;
                                _this.data.map = map;
                                _this.dialog.fireEvent('show', _this.dialog);
                            });
                            
                        }
                    });
                }
            });
            /*
            
            new Pman.Request({
                method : 'POST',
                url : baseURL + '/Crm/Import/ImportAddress',
                mask : 'Uploading',
                timeout: 60000,
                params : { 
                    _id : _this.data._id,
                    _import: 1,
                    mailing_list_id : _this.mailing_list.getValue(),
                    data: Roo.encode(data)
                },
                success : function(res){
         
                    _this.dialog.hide();
                },
                failure : function(res)
                {
                    //Roo.log(res);
                    Roo.MessageBox.show( {
                        title: "Fix these issues, and try uploading again", 
                        prompt : true,
                        multiline : 500,
                        value : res.errorMsg,
                        buttons : Roo.MessageBox.OK 
                    });
                      
        
                    
                    return true;
                
                }
            });
            //_this.form.doAction("submit");
            */
        
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
       sm : {
        xtype : 'CellSelectionModel',
        xns : Roo.grid,
        '|xns' : 'Roo.grid'
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
         header : _this._strings['5578591ead1e76e5eca8723f992950e1'] /* 2st Row */,
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
           tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{name}</b> </div>',
           triggerAction : 'all',
           typeAhead : true,
           value : 0,
           valueField : 'col',
           width : 150,
           xns : Roo.form,
           '|xns' : 'Roo.form',
           store : {
            xtype : 'SimpleStore',
            fields : [ {name: 'col', type: 'string'}, { name: 'name', type: 'string'} ],
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
