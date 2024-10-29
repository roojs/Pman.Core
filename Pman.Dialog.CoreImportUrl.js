//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.CoreImportUrl = {

 _strings : {
  'ea4788705e6873b424c65e91c2846b19' :"Cancel",
  'c8c55f55be8cbe3141db7e26ab0a8b4e' :"Import URL",
  'e0aa021e21dddbd6d8cecec71e9cf564' :"OK",
  'e6b391a8d2c4d45902a23a8b6585703d' :"URL"
 },
 _named_strings : {
  'importUrl_fieldLabel' : 'e6b391a8d2c4d45902a23a8b6585703d' /* URL */ 
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
    draggable : false,
    height : 140,
    modal : true,
    resizable : false,
    title : _this._strings['c8c55f55be8cbe3141db7e26ab0a8b4e'] /* Import URL */,
    width : 500,
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
      text : _this._strings['e0aa021e21dddbd6d8cecec71e9cf564'] /* OK */,
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
        method : 'POST',
        style : 'margin:10px;',
        url : baseURL,
        listeners : {
         actioncomplete : function (_self, action)
          {
               if (action.type == 'setdata') {
                  if(_this.data.target){
                      _this.form.url = baseURL + _this.data.target;
                  }
                 // _this.dialog.el.mask("Loading");
                 // if(_this.data.id*1 > 0)
                 //     this.load({ method: 'GET', params: { '_id' : _this.data.id }});
                 
                 return;
              }
              if (action.type == 'load') {
           
                  return;
              }
              if (action.type =='submit') {
              
                  //action.result.data
                  _this.dialog.hide();
              //    Roo.log(_this.callback);
                   if (_this.callback) {
                      _this.callback.call(_this, action.result.data);
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
          xtype : 'TextField',
          fieldLabel : _this._strings['e6b391a8d2c4d45902a23a8b6585703d'] /* URL */,
          name : 'importUrl',
          vtype : 'url',
          width : 250,
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
