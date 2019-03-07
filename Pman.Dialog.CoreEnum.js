//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.CoreEnum = {

 _strings : {
  '518ad9ed87d3ca17e223a91604b464d5' :"Add / Edit Core Enum",
  'cfcd208495d565ef66e7dff9f98764da' :"0",
  'b48968e1c912da07df5e8d6d246291ec' :"Display Name",
  'c4ca4238a0b923820dcc509a6f75849b' :"1",
  'ea4788705e6873b424c65e91c2846b19' :"Cancel",
  '49ee3087348e8d44e1feda1917443987' :"Name",
  '4d3d769b812b6faa6b76e1a8abaece2d' :"Active",
  'e0aa021e21dddbd6d8cecec71e9cf564' :"OK"
 },
 _named_strings : {
  'display_name_fieldLabel' : 'b48968e1c912da07df5e8d6d246291ec' /* Display Name */ ,
  'seqid_value' : 'cfcd208495d565ef66e7dff9f98764da' /* 0 */ ,
  'name_fieldLabel' : '49ee3087348e8d44e1feda1917443987' /* Name */ ,
  'active_fieldLabel' : '4d3d769b812b6faa6b76e1a8abaece2d' /* Active */ ,
  'active_value' : 'c4ca4238a0b923820dcc509a6f75849b' /* 1 */ 
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
    title : _this._strings['518ad9ed87d3ca17e223a91604b464d5'] /* Add / Edit Core Enum */,
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
        url : baseURL + '/Roo/core_enum.php',
        listeners : {
         actioncomplete : function (_self, action)
          {
            if (action.type == 'setdata') {
          
                  if((typeof(_this.data.etype) == 'undefined') || !_this.data.etype.length){
                      Roo.MessageBox.alert('Error', 'Missing etype');
                      _this.dialog.hide();
                      return;
                  }
                  
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
          actionMode : 'fieldEl',
          allowBlank : false,
          fieldLabel : _this._strings['49ee3087348e8d44e1feda1917443987'] /* Name */,
          hidden : true,
          name : 'name',
          width : 200,
          xns : Roo.form,
          '|xns' : 'Roo.form'
         },
         {
          xtype : 'TextField',
          allowBlank : false,
          fieldLabel : _this._strings['b48968e1c912da07df5e8d6d246291ec'] /* Display Name */,
          name : 'display_name',
          width : 200,
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
          xtype : 'Checkbox',
          actionMode : 'fieldEl',
          checked : true,
          fieldLabel : _this._strings['4d3d769b812b6faa6b76e1a8abaece2d'] /* Active */,
          hidden : true,
          inputValue : 1,
          name : 'active',
          value : 1,
          valueOff : 0,
          xns : Roo.form,
          '|xns' : 'Roo.form'
         },
         {
          xtype : 'Hidden',
          name : 'etype',
          xns : Roo.form,
          '|xns' : 'Roo.form'
         },
         {
          xtype : 'Hidden',
          name : 'seqid',
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
