//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.CorePrintPreview = {

 _strings : {
  '57b1416ba37ead15b87058a4d1314307' :"Preview Print",
  'ad513e4f467bdbd8b7e6a7ed511f7fa3' :"Close Window",
  '13dba24862cf9128167a59100e154c8d' :"Print"
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
    height : 300,
    modal : true,
    resizable : false,
    title : _this._strings['57b1416ba37ead15b87058a4d1314307'] /* Preview Print */,
    width : 500,
    listeners : {
     show : function (_self)
                       {
          if (!_this.dialog) { 
              _this.dialog = _self; 
          }
          _self.getLayout().beginUpdate();
          _self.moveTo(35,35);
          _self.resizeTo(window.innerWidth - 70,window.innerHeight - 70);
          _self.getLayout().endUpdate();
          
      
      
          if (!_this.frm) {
              var el =     _self.getLayout().getRegion('center').getPanel(0).getEl();
              _this.frm = el.createChild({ tag: 'iframe', src : 'about:blank'});
              _this.frm.dom.style.border = '0px';
              _this.frm.dom.style.overflow = 'auto';
          }
       
          if (!_this.frm) {    // in ... builder 
              return;
          }
      
          var sz = _self.getLayout().getRegion('center').bodyEl.getSize();
          _this.frm.dom.width = sz.width;
          _this.frm.dom.height = sz.height;
          
          
          
          if (!_this.data) {
          // for testing.. should not happen!
           //   _this.frm.dom.src=  baseURL + '/Cash/Print/1.html';
             return;
          }
          
      
          if (typeof(_this.data.head) != 'undefined') {
              _this.frm.dom.contentWindow.document.head.innerHTML = _this.data.head;
          }
          
          if (typeof(_this.data.body) != 'undefined') {
              _this.frm.dom.contentWindow.document.body.innerHTML = _this.data.body;
          }
          if (typeof(_this.data.title) != 'undefined') {            
              _self.setTitle(_this.data.title);
              _this.frm.dom.contentWindow.document.title = _this.data.title;
          }
          
          
       }
    },
    xns : Roo,
    '|xns' : 'Roo',
    center : {
     xtype : 'LayoutRegion',
     autoScroll : false,
     titlebar : false,
     xns : Roo,
     '|xns' : 'Roo'
    },
    buttons : [
     {
      xtype : 'Button',
      text : _this._strings['13dba24862cf9128167a59100e154c8d'] /* Print */,
      listeners : {
       click : function (_self, e)
        {
           if (!_this.frm) {
               return;
           }
          _this.frm.dom.contentWindow.print();
           
        
           
           
        }
      },
      xns : Roo,
      '|xns' : 'Roo'
     },
     {
      xtype : 'Button',
      text : _this._strings['ad513e4f467bdbd8b7e6a7ed511f7fa3'] /* Close Window */,
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
      autoScroll : false,
      fitContainer : false,
      fitToFrame : false,
      region : 'center',
      listeners : {
       activate : function (_self)
        {
            
        }
      },
      xns : Roo,
      '|xns' : 'Roo'
     }
    ]
   });
 }
};
