//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.CoreViewWebsite = {

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
    closable : true,
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
          
          if(typeof(_this.data.url) !== 'undefined') {
              url = _this.data.url;
          }
          
          if(url === false) {
              Roo.MessageBox.alert("Error", "Missing url");
              return;
          }
          
          _this.dialog.setTitle('View URL: ' + url);
          
          var vw = Roo.lib.Dom.getViewWidth();
          var vh = Roo.lib.Dom.getViewHeight();
          _this.dialog.resizeTo(vw * 0.9, vh * 0.9);
          _this.dialog.moveTo(vw * 0.05, vh * 0.05);
          
          var size = _this.dialog.layout.getRegion('center').el.getSize();
          
          // different origin
          if (!url.startsWith('/')) {
            fetch(baseURL + '/', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({"url": url})
            })
            .then(function(res) {
                return res.text();
            })
            .then(function(html) {
                _this.websiteViewPanel.setContent(
                    '<div>' + html + '</div>'
                );
            });
            return;
          }
          
          _this.websiteViewPanel.setContent(
              '<iframe ' + 
              'style="border: 0px;width:' + size.width +'px;height:' + size.height + 'px" ' +
              'src="' + url + '"/>'
          );
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
