//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.CoreEmail = {

 _strings : {
  'e44b145bd8b49b06e0ad2ced1ad56466' :"Plain Text",
  '2f26e35d61be90501e099089dc533638' :"Select Images",
  'f2a6c498fb90ee345d997f888fce3b18' :"Delete",
  'b357b524e740bc85b9790a0712d84a30' :"Email address",
  '962b90039a542a29cedd51d87a9f28a1' :"Html Editor",
  '72d6d7a1885885bb55a565fd1070581a' :"Import",
  'ea30b40c3caf28acb29198d20d243e54' :"Images / Attachments >>",
  '31fde7b05ac8952dacf4af8a704074ec' :"Preview",
  'b337c8a67244afb6551ee1f8f9717676' :"Test Class <BR/> (for system reference only)",
  '884df8e413319ff51a3f5f528606238a' :"Use template",
  'e6b391a8d2c4d45902a23a8b6585703d' :"URL",
  '2393ad754ba179442d85e415d1d5167c' :"Displayorder",
  '6f16a5f8ff5d75ab84c018adacdfcbb7' :"Field",
  'ec211f7c20af43e742bf2570c3cb84f9' :"Add",
  'e9968623956c15023d54335ea3699855' :"Convert Html to Text",
  '1243daf593fa297e07ab03bf06d925af' :"Searching...",
  '5b8ef4e762c00a15a41cfc26dc3ef99c' :"Send me a test copy",
  'c7892ebbb139886662c6f2fc8c450710' :"Subject",
  'dc0de523c25be298ba751c63c694109e' :"Responsive Email (1)",
  '396ecabf0cd1f9503e591418851ef406' :"Edit / Create Message",
  'b9c49611cfda3259a2b837b39489e650' :"Add Image",
  'ea4788705e6873b424c65e91c2846b19' :"Cancel",
  '68b00d723d37122f64da8d9939f836f0' :"BCC Group",
  'c4ca4238a0b923820dcc509a6f75849b' :"1",
  'bd88a20b53a47f7b5704a83a15ff5506' :"Saved Version",
  'b20a8b77b05d53b4e695738731400c85' :"Mailout Name",
  '1bd18d39370b7f26c1c5e18067b74c6f' :"Html File",
  '2c466a2c159463f1d9ef5a7b57b52827' :"Select BCC Group",
  '5da618e8e4b89c66fe86e32cdafde142' :"From",
  '31bb2f6e9b8fb11cbb7fb63c6025223f' :"Select Template",
  'b78a3223503896721cca1303f776159b' :"Title",
  '278c491bdd8a53618c149c4ac790da34' :"Template",
  '1351017ac6423911223bc19a8cb7c653' :"Filename",
  '308f2757bfc9ce92fb00ff93fdffd279' :"Images / Attachments",
  'c9cc8cce247e49bae79f15173ce97354' :"Save",
  '5feb9bf3c03b32635135006cbacb9542' :"Insert Field",
  '4c2a8fe7eaf24721cc7a9f0175115bd4' :"Message",
  'fff0d600f8a0b5e19e88bfb821dd1157' :"Images"
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
    closable : true,
    collapsible : false,
    height : 500,
    modal : true,
    resizable : true,
    title : _this._strings['396ecabf0cd1f9503e591418851ef406'] /* Edit / Create Message */,
    width : 800,
    listeners : {
     show : function (_self)
      {
          
          _self.layout.getRegion('center').showPanel(0);
          var w = Roo.lib.Dom.getViewWidth();
          var h = Roo.lib.Dom.getViewHeight();        this.resizeTo(w-50, h-50);
          this.center();    
          var ew = Math.max(250, w-320);
          var eh = Math.max(250, h-350) ;
          var e = _this.dialog.layout.getRegion('east');
          if (e.visible) {
              e.hide();
          }
          
          var el = _self.getEl();
          var elw = el.dom.clientWidth;
          
          var bdtext = _this.form.findField('bodytext');
          var ptext = _this.form.findField('plaintext');
          if(bdtext.resizeEl){
              bdtext.width = elw-100;
              bdtext.resizeEl.resizeTo.defer(110, bdtext.resizeEl,[ bdtext.width, bdtext.height  ] );
              ptext.setSize(bdtext.width , bdtext.height);
          }
          
      }
    },
    xns : Roo,
    '|xns' : 'Roo',
    center : {
     xtype : 'LayoutRegion',
     tabPosition : 'top',
     xns : Roo,
     '|xns' : 'Roo'
    },
    east : {
     xtype : 'LayoutRegion',
     hidden : true,
     split : true,
     title : _this._strings['308f2757bfc9ce92fb00ff93fdffd279'] /* Images / Attachments */,
     titlebar : true,
     width : 500,
     xns : Roo,
     '|xns' : 'Roo'
    },
    buttons : [
     {
      xtype : 'Button',
      text : _this._strings['31fde7b05ac8952dacf4af8a704074ec'] /* Preview */,
      listeners : {
       click : function (_self, e)
        {
            //_this.dialog.hide();
            Roo.log(_this.data.module);
            Pman.Dialog.CoreEmailPreview.show({ id : _this.form.findField('id').getValue(), module : _this.data.module });
        },
       render : function (_self)
        {
            _this.preview_btn = _self;
        }
      },
      xns : Roo,
      '|xns' : 'Roo'
     },
     {
      xtype : 'Button',
      text : _this._strings['5b8ef4e762c00a15a41cfc26dc3ef99c'] /* Send me a test copy */,
      listeners : {
       click : function (_self, e)
        {
            //_this.dialog.hide();
            
            var id = _this.form.findField('id').getValue();
            
            if(id*1 < 1){
                Roo.MessageBox.alert('Error', 'Please save the message frist!');
                return;
            }
           
            new Pman.Request({
                url : baseURL + '/Core/MessagePreview',
                method : 'POST',
                mask: 'Sending',
                params : {
                    _id : id,
                    _table : _this.data.module
                }, 
                success : function(res) { 
                    if(res.data == 'SUCCESS'){
                        Roo.MessageBox.alert("Email Sent", 'The report was sent to your email (HTML format).');
                    }
                }
            });
        },
       render : function (_self)
        {
            _this.html_preview = _self;
        }
      },
      xns : Roo,
      '|xns' : 'Roo'
     },
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
            _this.form.preValidate(function(res) {
                if (!res) {
                    return; //failed.
                }
                 _this.form.doAction("submit");
            });
        
        }
      },
      xns : Roo,
      '|xns' : 'Roo'
     }
    ],
    items  : [
     {
      xtype : 'NestedLayoutPanel',
      autoScroll : false,
      fitContainer : true,
      fitToFrame : true,
      region : 'center',
      xns : Roo,
      '|xns' : 'Roo',
      toolbar : {
       xtype : 'Toolbar',
       xns : Roo,
       '|xns' : 'Roo',
       items  : [
        {
         xtype : 'Button',
         text : _this._strings['72d6d7a1885885bb55a565fd1070581a'] /* Import */,
         xns : Roo.Toolbar,
         '|xns' : 'Roo.Toolbar',
         menu : {
          xtype : 'Menu',
          xns : Roo.menu,
          '|xns' : 'Roo.menu',
          items  : [
           {
            xtype : 'Item',
            text : _this._strings['e6b391a8d2c4d45902a23a8b6585703d'] /* URL */,
            listeners : {
             click : function (_self, e)
              {
                  Pman.Dialog.CoreImportUrl.show({
                      target : '/Core/ImportMailMessage.php'
                  }, function(data) {
                      if  (data) {
                        //  Roo.log(data);
                          _this.form.findField('bodytext').setValue(data);
                      }
                  });
              }
            },
            xns : Roo.menu,
            '|xns' : 'Roo.menu'
           },
           {
            xtype : 'Item',
            text : _this._strings['1bd18d39370b7f26c1c5e18067b74c6f'] /* Html File */,
            listeners : {
             click : function (_self, e)
              {
                  Pman.Dialog.Image.show({
                      _url : baseURL + '/Core/ImportMailMessage.php'
                  }, function(data) {
                      if  (data) {
                          _this.form.findField('bodytext').setValue(data);
                      }
                  });
              }
            },
            xns : Roo.menu,
            '|xns' : 'Roo.menu'
           }
          ]
         }
        },
        {
         xtype : 'Button',
         text : _this._strings['884df8e413319ff51a3f5f528606238a'] /* Use template */,
         xns : Roo.Toolbar,
         '|xns' : 'Roo.Toolbar',
         menu : {
          xtype : 'Menu',
          xns : Roo.menu,
          '|xns' : 'Roo.menu',
          items  : [
           {
            xtype : 'Item',
            text : _this._strings['dc0de523c25be298ba751c63c694109e'] /* Responsive Email (1) */,
            listeners : {
             click : function (_self, e)
              {
              
                  var l = document.location;
                  new Pman.Request({
              
                      url : baseURL + '/Core/ImportMailMessage.php',
              
                      method: 'POST',
                      mask : "Loading",
                      params : {
                            importUrl : l.protocol +'//' + l.host +   rootURL + '/Pman/Crm/mail_templates/responsive1.html'
                     },
                      success : function (res) {
              
                       _this.form.findField('bodytext').setValue(res.data);
                      }
                
                  });
              }
            },
            xns : Roo.menu,
            '|xns' : 'Roo.menu'
           }
          ]
         }
        },
        {
         xtype : 'ComboBox',
         allowBlank : true,
         alwaysQuery : true,
         displayField : 'file',
         editable : false,
         emptyText : _this._strings['31bb2f6e9b8fb11cbb7fb63c6025223f'] /* Select Template */,
         fieldLabel : _this._strings['278c491bdd8a53618c149c4ac790da34'] /* Template */,
         forceSelection : true,
         hiddenName : 'template',
         listWidth : 400,
         loadingText : _this._strings['1243daf593fa297e07ab03bf06d925af'] /* Searching... */,
         minChars : 2,
         name : 'template',
         pageSize : 20,
         qtip : _this._strings['31bb2f6e9b8fb11cbb7fb63c6025223f'] /* Select Template */,
         selectOnFocus : true,
         tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{file}</b> </div>',
         triggerAction : 'all',
         typeAhead : true,
         valueField : 'file',
         width : 200,
         listeners : {
          select : function (combo, record, index)
           {
              
           /*
               (function() { 
                   combo.setValue('');
               }).defer(100);
           */    
               if(!record){
                   return;
               }
               _this.form.findField('bodytext').setValue(record.data.content);
           
           }
         },
         xns : Roo.form,
         '|xns' : 'Roo.form',
         store : {
          xtype : 'Store',
          remoteSort : true,
          sortInfo : { direction : 'DESC', field: 'file' },
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
           url : baseURL + '/Core/MailTemplateList.php',
           xns : Roo.data,
           '|xns' : 'Roo.data'
          },
          reader : {
           xtype : 'JsonReader',
           fields : [{"name":"file","type":"string"},{"name":"content","type":"string"}],
           id : 'name',
           root : 'data',
           totalProperty : 'total',
           xns : Roo.data,
           '|xns' : 'Roo.data'
          }
         }
        },
        {
         xtype : 'Fill',
         xns : Roo.Toolbar,
         '|xns' : 'Roo.Toolbar'
        },
        {
         xtype : 'Button',
         text : _this._strings['ea30b40c3caf28acb29198d20d243e54'] /* Images / Attachments >> */,
         listeners : {
          click : function (_self, e)
           {
               var el = _this.dialog.layout.getRegion('east');
               if (el.visible) {
                   el.hide();
               } else {
                   el.show();
                   el.showPanel(0);
               }
               
           }
         },
         xns : Roo.Toolbar,
         '|xns' : 'Roo.Toolbar'
        }
       ]
      },
      layout : {
       xtype : 'BorderLayout',
       xns : Roo,
       '|xns' : 'Roo',
       center : {
        xtype : 'LayoutRegion',
        autoScroll : true,
        xns : Roo,
        '|xns' : 'Roo'
       },
       items  : [
        {
         xtype : 'ContentPanel',
         autoScroll : false,
         background : false,
         fitContainer : true,
         fitToFrame : true,
         region : 'center',
         title : _this._strings['4c2a8fe7eaf24721cc7a9f0175115bd4'] /* Message */,
         listeners : {
          render : function (_self, width, height)
           {
               
                 Roo.log("RESIZE, " + width + ',' + height);
               
               var ew = Math.max(250, width-50);
               var eh = Math.max(250,height-50) ;
               
              
           
           },
          resize : function (_self, width, height)
           {
              var ew = Math.max(250, width-50);
               var eh = Math.max(250,height-50) ;
               
               if (!_this.form) {
                   return;
               }
               var bdtext = _this.form.findField('bodytext');
               var ptext = _this.form.findField('plaintext');
               if(bdtext.resizeEl){
                   bdtext.width = ew-50;
                   bdtext.resizeEl.resizeTo.defer(110, bdtext.resizeEl,[ bdtext.width, bdtext.height  ] );
                   ptext.setSize(bdtext.width , bdtext.height);
               }
           
           }
         },
         xns : Roo,
         '|xns' : 'Roo',
         items  : [
          {
           xtype : 'Form',
           labelAlign : 'right',
           labelWidth : 120,
           method : 'POST',
           preValidate : function(done_callback) {
               
               Roo.MessageBox.progress("Uploading Images", "Uploading");
               
               if(!_this.form.findField('bodytext').editorcore.sourceEditMode){
                   _this.form.findField('bodytext').syncValue();
               }else{
                   _this.form.findField('bodytext').pushValue();
               }
               
               var html = _this.form.findField('bodytext').getValue();
               
               var s = Roo.get(_this.form.findField('bodytext').editorcore.doc.documentElement);
               
               var ontable = (_this.data.module) ? _this.data.module : 'crm_mailing_list_message';
               
               var nodes = [];
               s.select('img[src]').each(function(i) {
                   nodes.push(i.dom);
               });
               var total = nodes.length;
               var mkimg = function() {
               
                   if (!nodes.length) {
                         Roo.MessageBox.hide();
                         _this.form.findField('bodytext').syncValue();
                         done_callback(true);
                      //    _this.form.doAction("submit");
                         return;
                   }
                   var i = nodes.pop(); 
                   
                   var n = i.getAttribute('src').match(/(baseURL|server_baseurl)/);
                   
                   if(n){
                       mkimg();
                       return;
                   }
                   
                   n = i.getAttribute('src').match(/^http(.*)/);
                  
                   if(!n ){
                       mkimg();
                       return;
                   }
                   
                   new Pman.Request({
                       url : baseURL + '/Roo/Images.php',
                       method : 'POST',
                       params : {
                           onid : _this.form.findField('id').getValue(),
                           ontable : ontable ,
                           _remote_upload : i.src
                       },
                       success : function(res){
                           if(res.success == true){      
                               i.setAttribute('src', res.data);
                               Roo.MessageBox.updateProgress( (total - nodes.length) / total , "Done " + (total - nodes.length) + '/' + total);
                           }
                           mkimg();
                       }
                   });
                  
               }
               mkimg();
           },
           style : 'margin:10px',
           url : baseURL + '/Roo/crm_mailing_list_message.php',
           listeners : {
            actioncomplete : function(_self,action)
             {
                
                 if (action.type == 'setdata') {
                 
                     setInterval(_this.form.findField('bodytext').autosave, 5000);
                     
                     _this.data.module = _this.data.module || 'crm_mailing_list_message';
                     
                     _this.form.url = baseURL + '/Roo/' + _this.data.module;
                     
                     _this.html_preview.hide();
                     _this.preview_btn.hide();
                         
                     if(_this.data.id*1 > 0){
                         _this.dialog.el.mask("Loading");
                         this.load({ method: 'GET', params: { '_id' : _this.data.id }});
                         _this.html_preview.show();
                         _this.preview_btn.show();
                         
                     } else {
                         _this.form.setValues({
                             'from_name' : Pman.Login.authUser.name,
                             'from_email' : Pman.Login.authUser.email
                         });
                     }
                    return;
                 }
                 if (action.type == 'load') {
                     _this.dialog.el.unmask();
                     
                     _this.form.findField('bodytext').originalValue = _this.form.findField('bodytext').getValue();
                     
                     return;
                 }
                 if (action.type =='submit') {
                 
                     _this.dialog.el.unmask();
                     _this.dialog.hide();
                 
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
             xtype : 'Row',
             xns : Roo.form,
             '|xns' : 'Roo.form',
             items  : [
              {
               xtype : 'TextField',
               allowBlank : false,
               fieldLabel : _this._strings['b20a8b77b05d53b4e695738731400c85'] /* Mailout Name */,
               name : 'name',
               width : 400,
               xns : Roo.form,
               '|xns' : 'Roo.form'
              },
              {
               xtype : 'Column',
               hideLabels : true,
               xns : Roo.form,
               '|xns' : 'Roo.form',
               items  : [
                {
                 xtype : 'Checkbox',
                 boxLabel : 'Enabled - will be sent out',
                 checked : true,
                 name : 'active',
                 value : 1,
                 valueOff : 0,
                 listeners : {
                  check : function (_self, checked)
                   {
                       var boxLabel = 'Enabled - will be sent out';
                       
                       if(!checked){
                           boxLabel = 'Disabled - will NOT be sent out';
                       }
                       
                       this.setBoxLabel(boxLabel);
                   }
                 },
                 xns : Roo.form,
                 '|xns' : 'Roo.form'
                }
               ]
              }
             ]
            },
            {
             xtype : 'Row',
             xns : Roo.form,
             '|xns' : 'Roo.form',
             items  : [
              {
               xtype : 'TextField',
               allowBlank : false,
               fieldLabel : _this._strings['5da618e8e4b89c66fe86e32cdafde142'] /* From */,
               name : 'from_name',
               width : 300,
               xns : Roo.form,
               '|xns' : 'Roo.form'
              },
              {
               xtype : 'TextField',
               allowBlank : false,
               fieldLabel : _this._strings['b357b524e740bc85b9790a0712d84a30'] /* Email address */,
               name : 'from_email',
               width : 300,
               xns : Roo.form,
               '|xns' : 'Roo.form'
              },
              {
               xtype : 'ComboBox',
               allowBlank : true,
               alwaysQuery : true,
               displayField : 'name',
               editable : false,
               emptyText : _this._strings['2c466a2c159463f1d9ef5a7b57b52827'] /* Select BCC Group */,
               fieldLabel : _this._strings['68b00d723d37122f64da8d9939f836f0'] /* BCC Group */,
               forceSelection : true,
               hiddenName : 'bcc_group',
               loadingText : _this._strings['1243daf593fa297e07ab03bf06d925af'] /* Searching... */,
               minChars : 2,
               name : 'bcc_group_name',
               pageSize : 25,
               qtip : _this._strings['2c466a2c159463f1d9ef5a7b57b52827'] /* Select BCC Group */,
               selectOnFocus : true,
               tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{name}</b> </div>',
               triggerAction : 'all',
               typeAhead : true,
               valueField : 'id',
               width : 300,
               xns : Roo.form,
               '|xns' : 'Roo.form',
               store : {
                xtype : 'Store',
                remoteSort : true,
                sortInfo : { direction : 'ASC', field: 'name' },
                listeners : {
                 beforeload : function (_self, o){
                      o.params = o.params || {};
                      
                      o.params._direct_return = 1;
                  }
                },
                xns : Roo.data,
                '|xns' : 'Roo.data',
                proxy : {
                 xtype : 'HttpProxy',
                 method : 'GET',
                 url : baseURL + '/Roo/Groups',
                 xns : Roo.data,
                 '|xns' : 'Roo.data'
                },
                reader : {
                 xtype : 'JsonReader',
                 fields : [{"name":"name","type":"string"},{"name":"id","type":"int"}],
                 id : 'name',
                 root : 'data',
                 totalProperty : 'total',
                 xns : Roo.data,
                 '|xns' : 'Roo.data'
                }
               }
              }
             ]
            },
            {
             xtype : 'TextField',
             allowBlank : false,
             fieldLabel : _this._strings['c7892ebbb139886662c6f2fc8c450710'] /* Subject */,
             name : 'subject',
             width : 600,
             xns : Roo.form,
             '|xns' : 'Roo.form'
            },
            {
             xtype : 'Row',
             hideLabels : true,
             xns : Roo.form,
             '|xns' : 'Roo.form',
             items  : [
              {
               xtype : 'FieldSet',
               hideLabels : true,
               legend : _this._strings['962b90039a542a29cedd51d87a9f28a1'] /* Html Editor */,
               style : 'text-align:center;',
               xns : Roo.form,
               '|xns' : 'Roo.form',
               items  : [
                {
                 xtype : 'HtmlEditor',
                 autosave : function() {
                     
                     var body = _this.form.findField('bodytext');
                     
                     if(!body.wrap.isVisible(true) || body.getValue() == '' || !body.isDirty()){
                         Roo.log('body not dirty');
                         return;
                     }
                     
                     Roo.log('body dirty, auto save!');
                     
                     body.fireEvent('autosave', body);
                    
                 },
                 clearUp : false,
                 cwhite : [ 
                     'margin',
                     'padding',
                     'text-align',
                     'background',
                     'height',
                     'width',
                     'background-color',
                     'font-size',
                     'line-height',
                     'color',
                     'outline',
                     'text-decoration',
                     'position',
                     'clear',
                     'overflow',
                     'margin-top',
                     'border-bottom',
                     'top',
                     'list-style',
                     'margin-left',
                     'border',
                     'float' ,
                     'margin-right',
                     'padding-top',
                     'min-height',
                     'left',
                     'padding-left',
                     'font-weight',
                     'font-family',
                     'display',
                     'margin-bottom',
                     'padding-bottom',
                     'vertical-align',
                     'cursor',
                     'z-index',
                     'right'
                  ],
                 height : 250,
                 name : 'bodytext',
                 resizable : 's',
                 listeners : {
                  autosave : function (_self)
                   {
                       Roo.log('autosave');
                       
                       var id = _this.form.findField('id').getValue() * 1;
                       
                       /*
                       if(!_self.editorcore.sourceEditMode){
                           _self.syncValue();
                       }else{
                           _self.pushValue();
                       }
                       */
                       
                       new Pman.Request({
                           url : baseURL + '/Roo/Events.php',
                           method :'POST',
                           params : {
                               id : 0,
                               action : 'AUTOSAVE',
                               on_id : (id > 0) ? id : 0,
                               on_table : 'crm_mailing_list_message',
                               remarks : 'BODY',
                               source: _self.getValue()
                           },
                           success : function() {
                               _self.originalValue = _self.getValue();
                               
                           },
                           failure : function() 
                           {
                               Roo.log('body autosave failed?!');
                           }
                       });
                       
                   },
                  savedpreview : function (_self)
                   {
                       var id = _this.form.findField('id').getValue() * 1;
                       
                       var successFn = function(res){
                           return res.data.POST.source;
                       };
                       
                       var params = {
                           action : 'AUTOSAVE',
                           remarks : 'BODY',
                           on_id : (id < 1) ? 0 : id,
                           on_table : 'crm_mailing_list_message',
                           successFn : successFn
                       };
                       
                       
                       Pman.Dialog.CoreAutoSavePreview.show(params, function(res){
                           _self.setValue(res);
                           _self.originalValue = res;
                       });
                   }
                 },
                 xns : Roo.form,
                 '|xns' : 'Roo.form',
                 toolbars : [
                  {
                   xtype : 'ToolbarContext',
                   xns : Roo.form.HtmlEditor,
                   '|xns' : 'Roo.form.HtmlEditor'
                  },
                  {
                   xtype : 'ToolbarStandard',
                   xns : Roo.form.HtmlEditor,
                   '|xns' : 'Roo.form.HtmlEditor',
                   btns : [
                    {
                     xtype : 'ComboBox',
                     alwaysQuery : true,
                     displayField : 'name',
                     editable : false,
                     emptyText : _this._strings['b9c49611cfda3259a2b837b39489e650'] /* Add Image */,
                     fieldLabel : _this._strings['fff0d600f8a0b5e19e88bfb821dd1157'] /* Images */,
                     forceSelection : true,
                     listWidth : 400,
                     loadingText : _this._strings['1243daf593fa297e07ab03bf06d925af'] /* Searching... */,
                     minChars : 2,
                     pageSize : 20,
                     qtip : _this._strings['2f26e35d61be90501e099089dc533638'] /* Select Images */,
                     selectOnFocus : true,
                     tpl : '<div class=\"x-grid-cell-text x-btn button\"><img src=\"{public_baseURL}/Core/Images/Thumb/150x150/{id}.jpg\" height=\"150\" width=\"150\"><b>{filename}</b> </div>',
                     triggerAction : 'all',
                     typeAhead : true,
                     valueField : 'id',
                     width : 100,
                     listeners : {
                      beforequery : function (combo, query, forceAll, cancel, e)
                       {
                           var id = _this.form.findField('id').getValue() * 1;    
                           if (!id) {
                               Roo.MessageBox.alert("Error", "Save message first");
                               return false;
                           }
                       },
                      render : function (_self)
                       {
                           _this.extendimgselect = _self;
                       },
                      select : function (combo, record, index)
                       {
                           Roo.log(record);
                           (function() { 
                               combo.setValue('');
                           }).defer(100);
                           var editor = _this.form.findField('bodytext').editorcore;
                           
                           var curnode = editor.getSelectedNode();
                           if (curnode && curnode.tagName == 'IMG') {
                               curnode.src= String.format('{0}/Images/{1}/{2}#image-{1}',
                                       baseURL,  record.data.id, record.data.filename
                                   );
                                   // note -forces an update... hopefully...
                               editor.owner.fireEvent('editorevent', editor, false);
                           } else {
                           
                               editor.insertAtCursor(
                                   String.format('<img src="{0}/Images/{1}/{2}#image-{1}">',
                                   baseURL,  record.data.id, record.data.filename
                                   )
                               );
                       
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
                        
                            var id = _this.form.findField('id').getValue() * 1;    
                            if (!id) {
                                Roo.MessageBox.alert("Error", "Save email template first");
                                return false;
                            }
                            o.params.onid = id;
                            o.params.ontable = (_this.data.module) ? _this.data.module : 'crm_mailing_list_message';
                            
                           // o.params.imgtype = 'PressRelease';
                            //o.params['query[imagesize]'] = '150x150';
                            // set more here
                        }
                      },
                      xns : Roo.data,
                      '|xns' : 'Roo.data',
                      proxy : {
                       xtype : 'HttpProxy',
                       method : 'GET',
                       url : baseURL + '/Roo/Images.php',
                       xns : Roo.data,
                       '|xns' : 'Roo.data'
                      },
                      reader : {
                       xtype : 'JsonReader',
                       fields : [{"name":"id","type":"int"},{"name":"filename","type":"string"},{"name":"url_thumb","type":"string"}],
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
                     alwaysQuery : true,
                     displayField : 'name',
                     editable : false,
                     emptyText : _this._strings['5feb9bf3c03b32635135006cbacb9542'] /* Insert Field */,
                     fieldLabel : _this._strings['6f16a5f8ff5d75ab84c018adacdfcbb7'] /* Field */,
                     forceSelection : true,
                     listWidth : 400,
                     loadingText : _this._strings['1243daf593fa297e07ab03bf06d925af'] /* Searching... */,
                     minChars : 2,
                     pageSize : 20,
                     qtip : _this._strings['5feb9bf3c03b32635135006cbacb9542'] /* Insert Field */,
                     selectOnFocus : true,
                     tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{name}</b> </div>',
                     triggerAction : 'all',
                     typeAhead : true,
                     valueField : 'type',
                     width : 100,
                     listeners : {
                      render : function (_self)
                       {
                           _this.unsubscribeselect = _self;
                       },
                      select : function (combo, record, index)
                       {
                           Roo.log(record);
                           (function() { 
                               combo.setValue('');
                           }).defer(100);
                           var editor = _this.form.findField('bodytext').editorcore;
                           
                           if(record.data.name == 'Unsubscribe'){
                               editor.insertAtCursor(
                                   String.format('<a href="{0}">{1}</a>',
                                       record.data.type,  record.data.name
                                   )
                               );
                               return;     
                           }
                           
                           editor.insertAtCursor(
                               String.format('{0}',
                                   record.data.type
                               )
                           );
                           
                        }
                     },
                     xns : Roo.form,
                     '|xns' : 'Roo.form',
                     store : {
                      xtype : 'SimpleStore',
                      data : [ 
                          [ '{person.firstname}', "First Name"],
                          [ '{person.lastname}' , "Last Name"],
                          [ '{person.name}', "Full Name"],
                          [ '#unsubscribe', "Unsubscribe"]
                      ],
                      fields : [  'type', 'name'],
                      xns : Roo.data,
                      '|xns' : 'Roo.data'
                     }
                    },
                    {
                     xtype : 'Separator',
                     xns : Roo.Toolbar,
                     '|xns' : 'Roo.Toolbar'
                    },
                    {
                     xtype : 'Button',
                     cls : 'x-init-enable',
                     text : _this._strings['bd88a20b53a47f7b5704a83a15ff5506'] /* Saved Version */,
                     listeners : {
                      click : function (_self, e)
                       {
                           this.scope.owner.fireEvent('savedpreview', this.scope.owner);
                           
                       }
                     },
                     xns : Roo.Toolbar,
                     '|xns' : 'Roo.Toolbar'
                    }
                   ]
                  }
                 ]
                }
               ]
              }
             ]
            },
            {
             xtype : 'Row',
             hideLabels : true,
             xns : Roo.form,
             '|xns' : 'Roo.form',
             items  : [
              {
               xtype : 'Button',
               text : _this._strings['e9968623956c15023d54335ea3699855'] /* Convert Html to Text */,
               listeners : {
                click : function (_self, e)
                 {
                     var h = _this.form.findField('bodytext').getValue();
                     var p = _this.form.findField('plaintext');
                     
                     new Pman.Request({
                         url : baseURL + '/Core/ImportMailMessage.php',
                         method : 'POST',
                         params : {
                           bodytext : h,
                           _convertToPlain : true,
                           _check_unsubscribe : true
                         }, 
                         success : function(res) {
                             if(res.success == true){
                                p.setValue(res.data);
                             }
                         }
                     });  
                     
                 }
               },
               xns : Roo,
               '|xns' : 'Roo'
              }
             ]
            },
            {
             xtype : 'Row',
             hideLabels : true,
             xns : Roo.form,
             '|xns' : 'Roo.form',
             items  : [
              {
               xtype : 'FieldSet',
               hideLabels : true,
               legend : _this._strings['e44b145bd8b49b06e0ad2ced1ad56466'] /* Plain Text */,
               style : 'text-align:center;',
               xns : Roo.form,
               '|xns' : 'Roo.form',
               items  : [
                {
                 xtype : 'TextArea',
                 height : 50,
                 name : 'plaintext',
                 xns : Roo.form,
                 '|xns' : 'Roo.form'
                }
               ]
              }
             ]
            },
            {
             xtype : 'Row',
             xns : Roo.form,
             '|xns' : 'Roo.form',
             items  : [
              {
               xtype : 'TextField',
               allowBlank : true,
               fieldLabel : _this._strings['b337c8a67244afb6551ee1f8f9717676'] /* Test Class <BR/> (for system reference only) */,
               name : 'test_class',
               readOnly : true,
               width : 300,
               xns : Roo.form,
               '|xns' : 'Roo.form'
              }
             ]
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
      }
     },
     {
      xtype : 'GridPanel',
      autoScroll : false,
      background : false,
      fitContainer : true,
      fitToframe : true,
      region : 'east',
      tableName : 'Images',
      title : _this._strings['308f2757bfc9ce92fb00ff93fdffd279'] /* Images / Attachments */,
      listeners : {
       activate : function() {
            _this.ipanel = this;
            if (_this.igrid) {
               _this.igrid.ds.load({});
            }
        }
      },
      xns : Roo,
      '|xns' : 'Roo',
      grid : {
       xtype : 'Grid',
       autoExpandColumn : 'filename',
       loadMask : true,
       listeners : {
        render : function() 
         {
             _this.igrid = this; 
             //_this.dialog = Pman.Dialog.FILL_IN
             if (_this.ipanel.active) {
            //    _this.igrid.ds.load({});
             }
         }
       },
       xns : Roo.grid,
       '|xns' : 'Roo.grid',
       toolbar : {
        xtype : 'Toolbar',
        xns : Roo,
        '|xns' : 'Roo',
        items  : [
         {
          xtype : 'Button',
          cls : 'x-btn-text-icon',
          icon : Roo.rootURL + 'images/default/dd/drop-add.gif',
          text : _this._strings['ec211f7c20af43e742bf2570c3cb84f9'] /* Add */,
          listeners : {
           click : function()
            {
                var id = _this.form.findField('id').getValue();
                
                if(id*1 < 1){
                    Roo.MessageBox.alert('Error', 'Please save the email template first');
                    return;
                }
                
                var ontable = (_this.data.module) ? _this.data.module : 'crm_mailing_list_message';
                
                Pman.Dialog.Image.show( { id : 0, onid: id, ontable: ontable }, function() {
                    _this.igrid.getDataSource().load({});
                }); 
            }
          },
          xns : Roo.Toolbar,
          '|xns' : 'Roo.Toolbar'
         },
         {
          xtype : 'Button',
          cls : 'x-btn-text-icon',
          icon : rootURL + '/Pman/templates/images/trash.gif',
          text : _this._strings['f2a6c498fb90ee345d997f888fce3b18'] /* Delete */,
          listeners : {
           click : function()
            {
                var s = _this.igrid.getSelectionModel().getSelected();
                if (!s || isNaN(s.id *1)) {
                    Roo.MessageBox.alert("Error", "Select a image"); 
                    return;
                }
                Roo.MessageBox.confirm("Confirm", "Are sure you want to delete this image?", function (v){
                    if (v != 'yes') {
                        return;
                    }
                    
                    new Pman.Request({
                        url : baseURL + '/Roo/Images.php',
                        method: 'POST',
                        params : {
                            _delete : s.id
                        },
                        success : function()
                        {
                            Roo.log('Got Success!!');
                           _this.igrid.ds.load({});
                        }
                    });
                });
            }
          },
          xns : Roo.Toolbar,
          '|xns' : 'Roo.Toolbar'
         }
        ]
       },
       dataSource : {
        xtype : 'Store',
        remoteSort : true,
        sortInfo : { field : 'filename', direction: 'ASC' },
        listeners : {
         beforeload : function (_self, options)
          {
              options.params = options.params || {};
              if (typeof(_this.data) == 'undefined') {
                  return false;
              }
              if(_this.data.id * 1 >= 0)
              {
                  options.params.onid = _this.data.id;
          
                  options.params.ontable = (_this.data.module) ? _this.data.module : 'crm_mailing_list_message';
              }
          }
        },
        xns : Roo.data,
        '|xns' : 'Roo.data',
        proxy : {
         xtype : 'HttpProxy',
         method : 'GET',
         url : baseURL + '/Roo/Images.php',
         xns : Roo.data,
         '|xns' : 'Roo.data'
        },
        reader : {
         xtype : 'JsonReader',
         fields : [
             {
                 'name': 'id',
                 'type': 'int'
             },
             {
                 'name': 'filename',
                 'type': 'string'
             },
             {
                 'name': 'ontable',
                 'type': 'string'
             },
             {
                 'name': 'onid',
                 'type': 'int'
             },
             {
                 'name': 'mimetype',
                 'type': 'string'
             },
             {
                 'name': 'width',
                 'type': 'int'
             },
             {
                 'name': 'height',
                 'type': 'int'
             },
             {
                 'name': 'filesize',
                 'type': 'int'
             },
             {
                 'name': 'displayorder',
                 'type': 'int'
             },
             {
                 'name': 'language',
                 'type': 'string'
             },
             {
                 'name': 'parent_image_id',
                 'type': 'int'
             },
             {
                 'name': 'created',
                 'type': 'date',
                 'dateFormat': 'Y-m-d'
             },
             {
                 'name': 'imgtype',
                 'type': 'string'
             },
             {
                 'name': 'linkurl',
                 'type': 'string'
             },
             {
                 'name': 'descript',
                 'type': 'string'
             },
             {
                 'name': 'title',
                 'type': 'string'
             },
             {
                 'name': 'parent_image_id_id',
                 'type': 'int'
             },
             {
                 'name': 'parent_image_id_filename',
                 'type': 'string'
             },
             {
                 'name': 'parent_image_id_ontable',
                 'type': 'string'
             },
             {
                 'name': 'parent_image_id_onid',
                 'type': 'int'
             },
             {
                 'name': 'parent_image_id_mimetype',
                 'type': 'string'
             },
             {
                 'name': 'parent_image_id_width',
                 'type': 'int'
             },
             {
                 'name': 'parent_image_id_height',
                 'type': 'int'
             },
             {
                 'name': 'parent_image_id_filesize',
                 'type': 'int'
             },
             {
                 'name': 'parent_image_id_displayorder',
                 'type': 'int'
             },
             {
                 'name': 'parent_image_id_language',
                 'type': 'string'
             },
             {
                 'name': 'parent_image_id_parent_image_id',
                 'type': 'int'
             },
             {
                 'name': 'parent_image_id_created',
                 'type': 'date'
             },
             {
                 'name': 'parent_image_id_imgtype',
                 'type': 'string'
             },
             {
                 'name': 'parent_image_id_linkurl',
                 'type': 'string'
             },
             {
                 'name': 'parent_image_id_descript',
                 'type': 'string'
             },
             {
                 'name': 'parent_image_id_title',
                 'type': 'string'
             }
         ],
         id : 'id',
         root : 'data',
         totalProperty : 'total',
         xns : Roo.data,
         '|xns' : 'Roo.data'
        }
       },
       colModel : [
        {
         xtype : 'ColumnModel',
         dataIndex : 'filename',
         header : _this._strings['1351017ac6423911223bc19a8cb7c653'] /* Filename */,
         renderer : function(v,x,r)
         {
             var width = r.data.width;
             var height = r.data.height;
             
             if(width > 50){
                 height = Math.round(height * 50 / width);
                 width = 50;
             }
             
            return '<img src="' + baseURL + '/Images/' + r.data.id + '/' + r.data.filename + '" width="' + width + '" height="' + height + '" />';
         },
         width : 300,
         xns : Roo.grid,
         '|xns' : 'Roo.grid'
        },
        {
         xtype : 'ColumnModel',
         dataIndex : 'displayorder',
         header : _this._strings['2393ad754ba179442d85e415d1d5167c'] /* Displayorder */,
         renderer : function(v) { return String.format('{0}', v); },
         width : 75,
         xns : Roo.grid,
         '|xns' : 'Roo.grid'
        },
        {
         xtype : 'ColumnModel',
         dataIndex : 'title',
         header : _this._strings['b78a3223503896721cca1303f776159b'] /* Title */,
         renderer : function(v) { return String.format('{0}', v); },
         width : 75,
         xns : Roo.grid,
         '|xns' : 'Roo.grid'
        }
       ]
      }
     }
    ]
   });
 }
};
