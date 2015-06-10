//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.CoreEnum = {

 _strings : {
  '518ad9ed87d3ca17e223a91604b464d5' :"Add / Edit Core Enum",
  'cfcd208495d565ef66e7dff9f98764da' :"0",
  'c4ca4238a0b923820dcc509a6f75849b' :"1",
  'ea4788705e6873b424c65e91c2846b19' :"Cancel",
  'e0aa021e21dddbd6d8cecec71e9cf564' :"OK"
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
    center : {
     '|xns' : 'Roo',
     titlebar : false,
     xns : Roo,
     xtype : 'LayoutRegion'
    },
    '|xns' : 'Roo',
    background : true,
    closable : false,
    collapsible : false,
    height : 150,
    modal : true,
    resizable : false,
    title : _this._strings['518ad9ed87d3ca17e223a91604b464d5'],
    width : 400,
    xns : Roo,
    xtype : 'LayoutDialog',
    buttons : [
      {
       '|xns' : 'Roo',
       text : _this._strings['ea4788705e6873b424c65e91c2846b19'],
       xns : Roo,
       xtype : 'Button',
       listeners : {
        click : function() {
             _this.form.reset();
             _this.dialog.hide();
         }
       }
      },
{
       '|xns' : 'Roo',
       text : _this._strings['e0aa021e21dddbd6d8cecec71e9cf564'],
       xns : Roo,
       xtype : 'Button',
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
       }
      }
    ],
    listeners : {
     show : function (_self)
      {
          
      }
    },
    items : [
     {
      '|xns' : 'Roo',
      background : true,
      fitToFrame : true,
      region : 'center',
      xns : Roo,
      xtype : 'ContentPanel',
      items : [
       {
        '|xns' : 'Roo.form',
        method : 'POST',
        style : 'margin: 5px',
        url : baseURL + '/Roo/core_enum.php',
        xns : Roo.form,
        xtype : 'Form',
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
        items : [
         {
          '|xns' : 'Roo.form',
          actionMode : 'fieldEl',
          allowBlank : false,
          fieldLabel : 'Name',
          hidden : true,
          name : 'name',
          width : 200,
          xns : Roo.form,
          xtype : 'TextField'
         },
         {
          '|xns' : 'Roo.form',
          allowBlank : false,
          fieldLabel : 'Display Name',
          name : 'display_name',
          width : 200,
          xns : Roo.form,
          xtype : 'TextField',
          listeners : {
           keyup : function (_self, e)
            {
                _this.form.findField('name').setValue(this.getValue().replace(/[^a-z0-9]/ig, '').toUpperCase());
                
            }
          }
         },
         {
          '|xns' : 'Roo.form',
          actionMode : 'fieldEl',
          checked : true,
          fieldLabel : 'Active',
          hidden : true,
          inputValue : 1,
          name : 'active',
          value : 1,
          valueOff : 0,
          xns : Roo.form,
          xtype : 'Checkbox'
         },
         {
          '|xns' : 'Roo.form',
          name : 'etype',
          xns : Roo.form,
          xtype : 'Hidden'
         },
         {
          '|xns' : 'Roo.form',
          name : 'seqid',
          value : 0,
          xns : Roo.form,
          xtype : 'Hidden'
         },
         {
          '|xns' : 'Roo.form',
          name : 'seqid',
          value : 0,
          xns : Roo.form,
          xtype : 'Hidden'
         },
         {
          '|xns' : 'Roo.form',
          name : 'id',
          xns : Roo.form,
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
