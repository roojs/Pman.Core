//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.CoreEnumMerge = {

 _strings : {
  'bf8691517ce00a09186a05cd65863091' :"Select Item to Merge With",
  '298a183cfe4fddedd4bd17abe8aeb685' :"Merge Pulldown Option",
  '03e956f1dca2b4d525df03cb1899cb6f' :"Merge with",
  '1243daf593fa297e07ab03bf06d925af' :"Searching...",
  '68be4837f6c739877233e527a996dd00' :"Merge",
  'ea4788705e6873b424c65e91c2846b19' :"Cancel"
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
  this.dialog.show(this.data._el);
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
    closable : false,
    collapsible : false,
    height : 120,
    modal : true,
    resizable : false,
    title : _this._strings['298a183cfe4fddedd4bd17abe8aeb685'] /* Merge Pulldown Option */,
    width : 400,
    xns : Roo,
    '|xns' : 'Roo',
    xtype : 'LayoutDialog',
    listeners : {
     show : function (_self)
      {
          if (_this.isBuilder) {
              _this.data = { id : 2, comptype : 'SUPPLIER' }
          }
      }
    },
    center : {
     xns : Roo,
     '|xns' : 'Roo',
     xtype : 'LayoutRegion'
    },
    buttons : [
     {
      text : _this._strings['ea4788705e6873b424c65e91c2846b19'] /* Cancel */,
      xns : Roo,
      '|xns' : 'Roo',
      xtype : 'Button',
      listeners : {
       click : function (_self, e)
        {
            _this.dialog.hide();
        }
      }
     },
     {
      text : _this._strings['68be4837f6c739877233e527a996dd00'] /* Merge */,
      xns : Roo,
      '|xns' : 'Roo',
      xtype : 'Button',
      listeners : {
       click : function (_self, e)
        {
             
            _this.form.doAction("submit");
        
        }
      }
     }
    ],
    items  : [
     {
      region : 'center',
      xns : Roo,
      '|xns' : 'Roo',
      xtype : 'ContentPanel',
      items  : [
       {
        method : 'POST',
        style : 'margin: 10px;',
        url : baseURL + '/Roo/Core_enum.php',
        xns : Roo.form,
        '|xns' : 'Roo.form',
        xtype : 'Form',
        listeners : {
         actioncomplete : function (_self, action)
          {
          
             if (action.type =='submit') {
                 
                   _this.dialog.hide();
                 
                  if (_this.callback) {
                     _this.callback.call(_this, _this.form.getValues());
                  }
                  _this.form.reset();
                  return;
              }
              if (action.type == 'setdata') {
                  
                   var title = _this.data.title  || _this.data.etype;
                  _this.dialog.setTitle("Delete selected " + title + " and merge data with");
                  _this.form.findField('merge_id').store.proxy.conn.url = baseURL + '/Roo/' + _this.data.table + '.php';
                  _this.form.findField('merge_id').emptyText = "Select " + title;
                  _this.form.findField('merge_id').reset();
                 return;
              }
              
          },
         actionfailed : function (_self, action)
          {
              _this.dialog.el.unmask();
              Pman.standardActionFailed(_self, action);
          },
         rendered : function (form)
          {
             _this.form = form;
          }
        },
        items  : [
         {
          allowBlank : false,
          alwaysQuery : true,
          displayField : 'name',
          emptyText : _this._strings['bf8691517ce00a09186a05cd65863091'] /* Select Item to Merge With */,
          fieldLabel : _this._strings['03e956f1dca2b4d525df03cb1899cb6f'] /* Merge with */,
          forceSelection : true,
          hiddenName : '_merge_id',
          listWidth : 400,
          loadingText : _this._strings['1243daf593fa297e07ab03bf06d925af'] /* Searching... */,
          minChars : 2,
          name : '_merge_id_name',
          pageSize : 20,
          qtip : _this._strings['bf8691517ce00a09186a05cd65863091'] /* Select Item to Merge With */,
          queryParam : 'query[search_begins]',
          selectOnFocus : true,
          tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{display_name}</b> {name}</div>',
          triggerAction : 'all',
          typeAhead : true,
          valueField : 'id',
          width : 250,
          xns : Roo.form,
          '|xns' : 'Roo.form',
          xtype : 'ComboBox',
          store : {
           remoteSort : true,
           sortInfo : { direction : 'ASC', field: 'name' },
           xns : Roo.data,
           '|xns' : 'Roo.data',
           xtype : 'Store',
           listeners : {
            beforeload : function (_self, o){
                 o.params = o.params || {};
                 
                 o.params['etype'] = _this.data.etype;
                 
                 
                 // set more here
             }
           },
           proxy : {
            method : 'GET',
            url : baseURL + '/Roo/Core_enum',
            xns : Roo.data,
            '|xns' : 'Roo.data',
            xtype : 'HttpProxy'
           },
           reader : {
            fields : [{"name":"id","type":"int"},{"name":"name","type":"string"}],
            id : 'id',
            root : 'data',
            totalProperty : 'total',
            xns : Roo.data,
            '|xns' : 'Roo.data',
            xtype : 'JsonReader'
           }
          }
         },
         {
          name : 'etype',
          xns : Roo.form,
          '|xns' : 'Roo.form',
          xtype : 'Hidden'
         },
         {
          name : 'id',
          xns : Roo.form,
          '|xns' : 'Roo.form',
          xtype : 'Hidden'
         }
        ]
       }
      ]
     }
    ]
   });
 }
};
