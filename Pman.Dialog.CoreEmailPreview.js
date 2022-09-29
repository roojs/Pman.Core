//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.CoreEmailPreview = {

 _strings : {
  '4cd8413207629a963225f4314b53adcd' :"Plain",
  '4c4ad5fca2e7a3f74dbb1ced00381aa4' :"HTML",
  '006c82ffdd63692a84a259c4f8732842' :"Email Preview",
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
    autoScroll : true,
    closable : true,
    height : 800,
    shadow : true,
    title : _this._strings['006c82ffdd63692a84a259c4f8732842'] /* Email Preview */,
    width : 1200,
    listeners : {
     show : function (_self)
      {
          
          _self.layout.getRegion('center').showPanel(0);
          _this.panel.load({ 
              url: baseURL + '/Core/MessagePreview', 
              params  : {
                  _id : _this.data.id,
                  _table : _this.data.module,
                  ontable : _this.data.ontable || '',
                  onid : _this.data.onid || '',
                  evtype : _this.data.evtype  || ''
              },
              method : 'GET'
          });
          _this.hpanel.load({ 
              url: baseURL + '/Core/MessagePreview', 
              params  : {
                  _as_html : 1,
                  _id : _this.data.id,
                  _table : _this.data.module,
                  ontable : _this.data.ontable || '',
                  onid : _this.data.onid  || '',
                  evtype : _this.data.evtype || ''
              },
              method : 'GET'
          });
              
      }
    },
    xns : Roo,
    '|xns' : 'Roo',
    center : {
     xtype : 'LayoutRegion',
     autoScroll : true,
     tabPosition : 'top',
     xns : Roo,
     '|xns' : 'Roo'
    },
    buttons : [
     {
      xtype : 'Button',
      text : _this._strings['e0aa021e21dddbd6d8cecec71e9cf564'] /* OK */,
      listeners : {
       click : function (_self, e)
        {
            _this.dialog.hide();
        }
      },
      xns : Roo,
      '|xns' : 'Roo'
     }
    ],
    items  : [
     {
      xtype : 'ContentPanel',
      fitContainer : true,
      fitToFrame : true,
      region : 'center',
      title : _this._strings['4c4ad5fca2e7a3f74dbb1ced00381aa4'] /* HTML */,
      listeners : {
       render : function (_self)
        {
            _this.hpanel = _self;
        }
      },
      xns : Roo,
      '|xns' : 'Roo'
     },
     {
      xtype : 'ContentPanel',
      fitContainer : true,
      fitToFrame : true,
      region : 'center',
      title : _this._strings['4cd8413207629a963225f4314b53adcd'] /* Plain */,
      listeners : {
       render : function (_self)
        {
            _this.panel = _self;
        }
      },
      xns : Roo,
      '|xns' : 'Roo'
     }
    ]
   });
 }
};
