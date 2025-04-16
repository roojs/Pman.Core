//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.ViewWebsite = {

 _strings : {
  '1e35fe802ad1aaf4414fd68ad3157675' :"View Website",
  'a60852f204ed8028c1c58808b746d115' :"Ok"
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
    height : 400,
    modal : true,
    resizable : false,
    title : _this._strings['1e35fe802ad1aaf4414fd68ad3157675'] /* View Website */,
    width : 600,
    listeners : {
     show : function (_self)
      {
          var url = false;
          if(typeof(_this.data.id) !== 'undefined') {
              var params = {_download: _this.data.id};
              url = baseURL + '/Roo/Mail_imap_file.php?' + Roo.urlEncode(params || {});
          }
          
          if(typeof(_this.data.data_url) !== 'undefined') {
              url = _this.data.data_url;
          }
          
          if(url === false) {
              Roo.MessageBox.alert("Error", "Missing id or name & mimetype & data_url");
              return;
          }
          
          _this.imageViewPanel.el.setWidth('100%');
          _this.imageViewPanel.el.setHeight('100%');
          _this.imageViewPanel.el.setStyle('textAlign', 'center');
          _this.imageViewPanel.setContent("<img style='width:100%; height:100%; object-fit:contain;' src='" + url + "' />");
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
      text : _this._strings['a60852f204ed8028c1c58808b746d115'] /* Ok */,
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
      region : 'center',
      listeners : {
       render : function (_self)
        {
            _this.websiteViewPanel = this;
        }
      },
      xns : Roo,
      '|xns' : 'Roo'
     }
    ]
   });
 }
};
