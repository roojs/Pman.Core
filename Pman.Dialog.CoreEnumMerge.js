//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.CoreEnumMerge = {

 _strings : {
  '8324cdec05065c4bd7d8c5effdf43edf' :"Delete this",
  '298a183cfe4fddedd4bd17abe8aeb685' :"Merge Pulldown Option",
  'bf8691517ce00a09186a05cd65863091' :"Select Item to Merge With",
  '1243daf593fa297e07ab03bf06d925af' :"Searching...",
  '68be4837f6c739877233e527a996dd00' :"Merge",
  '266459bee8ed1ca2e0464899e1ef0994' :"And replace with",
  'ea4788705e6873b424c65e91c2846b19' :"Cancel"
 },
 _named_strings : {
  '_merge_id_name_emptyText' : 'bf8691517ce00a09186a05cd65863091' /* Select Item to Merge With */ ,
  '_merge_id_name_loadingText' : '1243daf593fa297e07ab03bf06d925af' /* Searching... */ ,
  '_merge_id_name_qtip' : 'bf8691517ce00a09186a05cd65863091' /* Select Item to Merge With */ ,
  '_merge_id_name_fieldLabel' : '266459bee8ed1ca2e0464899e1ef0994' /* And replace with */ ,
  '_names_fieldLabel' : '8324cdec05065c4bd7d8c5effdf43edf' /* Delete this */ 
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
    height : 160,
    modal : true,
    resizable : false,
    title : _this._strings['298a183cfe4fddedd4bd17abe8aeb685'] /* Merge Pulldown Option */,
    width : 450,
    listeners : {
     show : function (_self)
      {
          if (_this.isBuilder) {
              _this.data = { id : 2, comptype : 'SUPPLIER' }
          }
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
      text : _this._strings['68be4837f6c739877233e527a996dd00'] /* Merge */,
      listeners : {
       click : function (_self, e)
        {
             
            _this.form.doAction("submit");
        
        }
      },
      xns : Roo,
      '|xns' : 'Roo'
     }
    ],
    items  : [
     {
      xtype : 'ContentPanel',
      region : 'center',
      xns : Roo,
      '|xns' : 'Roo',
      items  : [
       {
        xtype : 'Form',
        labelWidth : 120,
        method : 'POST',
        style : 'margin: 10px;',
        url : baseURL + '/Roo/Core_enum.php',
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
                  
          
                  if(typeof(_this.data._ids) == "undefined"){
                      this.load({ method: 'GET', params: { '_id' : _this.data.id }});  
                      return;
                  }
                  _this.form.findField('_names').setValue(_this.data._names);
                  _this.form.findField('_ids').setValue(_this.data._ids);
                  return;
              }
              
          },
         actionfailed : function (_self, action)
          {
           
              Pman.standardActionFailed(_self, action);
          },
         rendered : function (form)
          {
             _this.form = form;
          }
        },
        xns : Roo.form,
        '|xns' : 'Roo.form',
        items  : [
         {
          xtype : 'TextField',
          fieldLabel : _this._strings['8324cdec05065c4bd7d8c5effdf43edf'] /* Delete this */,
          name : '_names',
          readOnly : true,
          width : 250,
          xns : Roo.form,
          '|xns' : 'Roo.form'
         },
         {
          xtype : 'ComboBox',
          allowBlank : false,
          alwaysQuery : true,
          displayField : 'name',
          emptyText : _this._strings['bf8691517ce00a09186a05cd65863091'] /* Select Item to Merge With */,
          fieldLabel : _this._strings['266459bee8ed1ca2e0464899e1ef0994'] /* And replace with */,
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
          store : {
           xtype : 'Store',
           remoteSort : true,
           sortInfo : { direction : 'ASC', field: 'name' },
           listeners : {
            beforeload : function (_self, o){
                 o.params = o.params || {};
                 
                 o.params['etype'] = _this.data.etype;
                 
                 var ids = _this.form.findField('_ids').getValue();
                 if (ids.length) {
                     var xids = ids.split(',');
                     for(var i =0;i < xids.length; i++) {
                         o.params['!id[' + i + ']'] = xids[i];
                     }
                 } else {
                     o.params['!id'] = _this.form.findField('id').getValue();
                 } 
                 // set more here
             }
           },
           xns : Roo.data,
           '|xns' : 'Roo.data',
           proxy : {
            xtype : 'HttpProxy',
            method : 'GET',
            url : baseURL + '/Roo/Core_enum',
            xns : Roo.data,
            '|xns' : 'Roo.data'
           },
           reader : {
            xtype : 'JsonReader',
            fields : [{"name":"id","type":"int"},{"name":"name","type":"string"}],
            id : 'id',
            root : 'data',
            totalProperty : 'total',
            xns : Roo.data,
            '|xns' : 'Roo.data'
           }
          }
         },
         {
          xtype : 'Hidden',
          name : 'etype',
          xns : Roo.form,
          '|xns' : 'Roo.form'
         },
         {
          xtype : 'Hidden',
          name : '_ids',
          xns : Roo.form,
          '|xns' : 'Roo.form'
         },
         {
          xtype : 'Hidden',
          name : 'id',
          xns : Roo.form,
          '|xns' : 'Roo.form'
         }
        ]
       }
      ]
     }
    ]
   });
 }
};
