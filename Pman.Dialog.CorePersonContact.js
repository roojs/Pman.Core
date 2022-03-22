//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.CorePersonContact = {

 _strings : {
  '1c76cbfe21c6f44c1d1e59d54f3e4420' :"Company",
  'ce8ae9da5b7cd6c3df2929543a9af92d' :"Email",
  'c8972faa3b9e1c7250db23c57c85aa23' :"Edit / Create Contact Details",
  '1243daf593fa297e07ab03bf06d925af' :"Searching...",
  'bcc254b55c4a1babdf1dcb82c207506b' :"Phone",
  'ea4788705e6873b424c65e91c2846b19' :"Cancel",
  '9675747b5ab12d05f18518761e68a533' :"Select Companies",
  'df814135652a5a308fea15bff37ea284' :"Office",
  'c373dd4bd4ba0b5d3e0c7522c5629880' :"Select Office",
  '49ee3087348e8d44e1feda1917443987' :"Name",
  'bbbabdbe1b262f75d99d62880b953be1' :"Role",
  'c9cc8cce247e49bae79f15173ce97354' :"Save",
  '9810aa2b9f44401be4bf73188ef2b67d' :"Fax"
 },
 _named_strings : {
  'company_id_code_loadingText' : '1243daf593fa297e07ab03bf06d925af' /* Searching... */ ,
  'role_fieldLabel' : 'bbbabdbe1b262f75d99d62880b953be1' /* Role */ ,
  'fax_fieldLabel' : '9810aa2b9f44401be4bf73188ef2b67d' /* Fax */ ,
  'office_id_name_emptyText' : 'c373dd4bd4ba0b5d3e0c7522c5629880' /* Select Office */ ,
  'name_fieldLabel' : '49ee3087348e8d44e1feda1917443987' /* Name */ ,
  'phone_fieldLabel' : 'bcc254b55c4a1babdf1dcb82c207506b' /* Phone */ ,
  'office_id_name_fieldLabel' : 'df814135652a5a308fea15bff37ea284' /* Office */ ,
  'office_id_name_loadingText' : '1243daf593fa297e07ab03bf06d925af' /* Searching... */ ,
  'company_id_code_qtip' : '9675747b5ab12d05f18518761e68a533' /* Select Companies */ ,
  'office_id_name_qtip' : 'c373dd4bd4ba0b5d3e0c7522c5629880' /* Select Office */ ,
  'email_fieldLabel' : 'ce8ae9da5b7cd6c3df2929543a9af92d' /* Email */ ,
  'company_id_code_emptyText' : '9675747b5ab12d05f18518761e68a533' /* Select Companies */ ,
  'company_id_code_fieldLabel' : '1c76cbfe21c6f44c1d1e59d54f3e4420' /* Company */ 
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
    xtype : 'LayoutDialog',
    closable : false,
    collapsible : false,
    height : 290,
    resizable : false,
    title : _this._strings['c8972faa3b9e1c7250db23c57c85aa23'] /* Edit / Create Contact Details */,
    width : 450,
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
      text : _this._strings['c9cc8cce247e49bae79f15173ce97354'] /* Save */,
      listeners : {
       click : function (_self, e)
        {
            // do some checks?
             
            
            _this.dialog.el.mask("Saving");
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
        method : 'POST',
        style : 'margin:10px;',
        url : baseURL + '/Roo/core_person',
        listeners : {
         actioncomplete : function(_self,action)
          {
              if (action.type == 'setdata') {
                 //_this.dialog.el.mask("Loading");
                 //this.load({ method: 'GET', params: { '_id' : _this.data.id }});
                 return;
              }
              if (action.type == 'load') {
                  _this.dialog.el.unmask();
                  return;
              }
              if (action.type =='submit') {
              
                  _this.dialog.el.unmask();
                  _this.dialog.hide();
              
                   if (_this.callback) {
                      _this.callback.call(_this, _this.form.getValues());
                   }
                   _this.form.reset();
                   return;
              }
          },
         rendered : function (form)
          {
              _this.form= form;
          }
        },
        xns : Roo.form,
        '|xns' : 'Roo.form',
        items  : [
         {
          xtype : 'ComboBox',
          allowBlank : false,
          displayField : 'code',
          editable : false,
          emptyText : _this._strings['9675747b5ab12d05f18518761e68a533'] /* Select Companies */,
          fieldLabel : _this._strings['1c76cbfe21c6f44c1d1e59d54f3e4420'] /* Company */,
          forceSelection : true,
          hiddenName : 'company_id',
          listWidth : 400,
          loadingText : _this._strings['1243daf593fa297e07ab03bf06d925af'] /* Searching... */,
          minChars : 2,
          name : 'company_id_code',
          pageSize : 20,
          qtip : _this._strings['9675747b5ab12d05f18518761e68a533'] /* Select Companies */,
          queryParam : 'query[code]',
          selectOnFocus : true,
          tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{code}</b> </div>',
          triggerAction : 'all',
          typeAhead : true,
          valueField : 'id',
          width : 300,
          listeners : {
           add : function (combo)
            {
            
                Pman.Dialog.Companies.show( {  id: 0 },  function(data) {
                        _this.form.setValues({
                                company_id_name : data.name,
                                company_id : data.id
                        });
                }); 
            }
          },
          xns : Roo.form,
          '|xns' : 'Roo.form',
          store : {
           xtype : 'Store',
           remoteSort : true,
           sortInfo : { direction : 'ASC', field: 'id' },
           listeners : {
            beforeload : function (_self, o){
                 o.params = o.params || {};
                 // set more here
             }
           },
           xns : Roo.data,
           '|xns' : 'Roo.data',
           proxy : {
            xtype : 'HttpProxy',
            method : 'GET',
            url : baseURL + '/Roo/core_company.php',
            xns : Roo.data,
            '|xns' : 'Roo.data'
           },
           reader : {
            xtype : 'JsonReader',
            fields : [{"name":"id","type":"int"},{"name":"code","type":"string"}],
            id : 'id',
            root : 'data',
            totalProperty : 'total',
            xns : Roo.data,
            '|xns' : 'Roo.data'
           }
          }
         },
         {
          xtype : 'ComboBox',
          allowBlank : false,
          displayField : 'name',
          editable : false,
          emptyText : _this._strings['c373dd4bd4ba0b5d3e0c7522c5629880'] /* Select Office */,
          fieldLabel : _this._strings['df814135652a5a308fea15bff37ea284'] /* Office */,
          forceSelection : true,
          hiddenName : 'office_id',
          listWidth : 400,
          loadingText : _this._strings['1243daf593fa297e07ab03bf06d925af'] /* Searching... */,
          minChars : 2,
          name : 'office_id_name',
          pageSize : 20,
          qtip : _this._strings['c373dd4bd4ba0b5d3e0c7522c5629880'] /* Select Office */,
          queryParam : 'query[name]',
          selectOnFocus : true,
          tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{name}</b> </div>',
          triggerAction : 'all',
          typeAhead : true,
          valueField : 'id',
          width : 300,
          listeners : {
           add : function (combo)
            {
                var coid = _this.form.findField('company_id').getValue();
                if (!coid ) {
                
                     Ext.MessageBox.alert("Error", "Select An Company First");
                    return false;
            
                }
                Pman.Dialog.Office.show(cfg, function(data) {
                            _this.form.setValues({
                                office_id_name : data.name,
                                office_id : data.id
                        });
                    }); 
                
                
            },
           beforequery : function (combo, query, forceAll, cancel, e)
            {
                    var coid = _this.form.findField('company_id').getValue();
                    if (coid < 1 ) {
                        Ext.MessageBox.alert("Error", "Select An Company First");
                        return false;
                    }
            }
          },
          xns : Roo.form,
          '|xns' : 'Roo.form',
          store : {
           xtype : 'Store',
           remoteSort : true,
           sortInfo : { direction : 'ASC', field: 'id' },
           listeners : {
            beforeload : function (_self, o){
                 o.params = o.params || {};
                 var coid = _this.form.findField('company_id').getValue();
                 o.params.company_id = coid;
             }
           },
           xns : Roo.data,
           '|xns' : 'Roo.data',
           proxy : {
            xtype : 'HttpProxy',
            method : 'GET',
            url : baseURL + '/Roo/Office.php',
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
          xtype : 'TextField',
          allowBlank : true,
          fieldLabel : _this._strings['49ee3087348e8d44e1feda1917443987'] /* Name */,
          name : 'name',
          width : 300,
          xns : Roo.form,
          '|xns' : 'Roo.form'
         },
         {
          xtype : 'TextField',
          fieldLabel : _this._strings['bbbabdbe1b262f75d99d62880b953be1'] /* Role */,
          name : 'role',
          width : 200,
          xns : Roo.form,
          '|xns' : 'Roo.form'
         },
         {
          xtype : 'TextField',
          fieldLabel : _this._strings['bcc254b55c4a1babdf1dcb82c207506b'] /* Phone */,
          name : 'phone',
          width : 200,
          xns : Roo.form,
          '|xns' : 'Roo.form'
         },
         {
          xtype : 'TextField',
          fieldLabel : _this._strings['9810aa2b9f44401be4bf73188ef2b67d'] /* Fax */,
          name : 'fax',
          width : 200,
          xns : Roo.form,
          '|xns' : 'Roo.form'
         },
         {
          xtype : 'TextField',
          allowBlank : false,
          fieldLabel : _this._strings['ce8ae9da5b7cd6c3df2929543a9af92d'] /* Email */,
          name : 'email',
          width : 200,
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
