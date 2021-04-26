//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.CoreColumnConfig = {

 _strings : {
  'cfcd208495d565ef66e7dff9f98764da' :"0",
  'ea4788705e6873b424c65e91c2846b19' :"Cancel",
  'c40cab5f875bb6c270d800eff77a4af0' :"Save Column Configuration",
  'b5a7adde1af5c87d7fd797b6245c2a39' :"Description",
  'e0aa021e21dddbd6d8cecec71e9cf564' :"OK"
 },
 _named_strings : {
  'description_fieldLabel' : 'b5a7adde1af5c87d7fd797b6245c2a39' /* Description */ ,
  'name_value' : 'cfcd208495d565ef66e7dff9f98764da' /* 0 */ 
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
    background : true,
    closable : false,
    collapsible : false,
    height : 150,
    modal : true,
    resizable : false,
    title : _this._strings['c40cab5f875bb6c270d800eff77a4af0'] /* Save Column Configuration */,
    width : 400,
    listeners : {
     show : function (_self)
      {
          
      }
    },
    xns : Roo,
    '|xns' : 'Roo',
    center : {
     xtype : 'LayoutRegion',
     titlebar : false,
     xns : Roo,
     '|xns' : 'Roo'
    },
    buttons : [
     {
      xtype : 'Button',
      text : _this._strings['ea4788705e6873b424c65e91c2846b19'] /* Cancel */,
      listeners : {
       click : function() {
            _this.form.reset();
            _this.dialog.hide();
        }
      },
      xns : Roo,
      '|xns' : 'Roo'
     },
     {
      xtype : 'Button',
      text : _this._strings['e0aa021e21dddbd6d8cecec71e9cf564'] /* OK */,
      listeners : {
       click : function() {
        
            var name =     _this.form.findField('name').getValue();
            name = name.toUpperCase().replace(/[^A-Z]+/g, '');
            if (!name.length) {
                Roo.MessageBox.alert("Error","Please fill in a valid name");
                return;
            }
            _this.form.findField('name').setValue(name);
         
            _this.form.doAction('submit');
            
        }
      },
      xns : Roo,
      '|xns' : 'Roo'
     }
    ],
    items  : [
     {
      xtype : 'ContentPanel',
      background : true,
      fitToFrame : true,
      region : 'center',
      xns : Roo,
      '|xns' : 'Roo',
      items  : [
       {
        xtype : 'Form',
        method : 'POST',
        style : 'margin: 5px',
        url : baseURL + '/Roo/core_setting',
        listeners : {
         actioncomplete : function (_self, action)
          {
            if (action.type == 'setdata') {
          
                  
                  
                  if(typeof(_this.data.title) != 'undefined' && _this.data.title.length){
                      _this.dialog.setTitle(_this.data.title);
                  }
            
                  if(_this.data.id){
                      _this.dialog.el.mask("Loading");
                      this.load({ method: 'GET', params: { '_id' : _this.data.id }}); 
                  }
                 
                 return;
              }
              if (action.type == 'load') {
                  _this.dialog.el.unmask();
                  return;
              }
              if (action.type == 'submit' ) {
                  _this.dialog.el.unmask();
                  _this.dialog.hide();
          
                  if (_this.callback) {
                     _this.callback.call(_this, action.result.data);
                  }
                  _this.form.reset();
              }
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
          allowBlank : false,
          fieldLabel : _this._strings['b5a7adde1af5c87d7fd797b6245c2a39'] /* Description */,
          name : 'description',
          width : 250,
          listeners : {
           keyup : function (_self, e)
            {
                _this.form.findField('name').setValue(this.getValue().replace(/[^a-z0-9]/ig, '').toUpperCase());
                
            }
          },
          xns : Roo.form,
          '|xns' : 'Roo.form'
         },
         {
          xtype : 'Hidden',
          name : 'module',
          xns : Roo.form,
          '|xns' : 'Roo.form'
         },
         {
          xtype : 'Hidden',
          name : 'val',
          xns : Roo.form,
          '|xns' : 'Roo.form'
         },
         {
          xtype : 'Hidden',
          name : 'name',
          value : 0,
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
