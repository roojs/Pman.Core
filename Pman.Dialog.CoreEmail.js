//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.CoreEmail = {

 _strings : {
  '308f2757bfc9ce92fb00ff93fdffd279' :"Images / Attachments",
  'dc0de523c25be298ba751c63c694109e' :"Responsive Email (1)",
  'ea30b40c3caf28acb29198d20d243e54' :"Images / Attachments >>",
  '4c2a8fe7eaf24721cc7a9f0175115bd4' :"Message",
  '2f26e35d61be90501e099089dc533638' :"Select Images",
  'ec211f7c20af43e742bf2570c3cb84f9' :"Add",
  '72d6d7a1885885bb55a565fd1070581a' :"Import",
  '884df8e413319ff51a3f5f528606238a' :"Use template",
  '396ecabf0cd1f9503e591418851ef406' :"Edit / Create Message",
  '31bb2f6e9b8fb11cbb7fb63c6025223f' :"Select Template",
  '1bd18d39370b7f26c1c5e18067b74c6f' :"Html File",
  '1243daf593fa297e07ab03bf06d925af' :"Searching...",
  'b78a3223503896721cca1303f776159b' :"Title",
  '31fde7b05ac8952dacf4af8a704074ec' :"Preview",
  '5b8ef4e762c00a15a41cfc26dc3ef99c' :"Send me a test copy",
  'ea4788705e6873b424c65e91c2846b19' :"Cancel",
  '962b90039a542a29cedd51d87a9f28a1' :"Html Editor",
  '5feb9bf3c03b32635135006cbacb9542' :"Insert Field",
  '2393ad754ba179442d85e415d1d5167c' :"Displayorder",
  'b9c49611cfda3259a2b837b39489e650' :"Add Image",
  'e44b145bd8b49b06e0ad2ced1ad56466' :"Plain Text",
  'f2a6c498fb90ee345d997f888fce3b18' :"Delete",
  '1351017ac6423911223bc19a8cb7c653' :"Filename",
  'c9cc8cce247e49bae79f15173ce97354' :"Save",
  'e6b391a8d2c4d45902a23a8b6585703d' :"URL",
  'e9968623956c15023d54335ea3699855' :"Convert Html to Text"
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
     tabPosition : 'top',
     xns : Roo,
     xtype : 'LayoutRegion'
    },
    east : {
     '|xns' : 'Roo',
     hidden : true,
     split : true,
     title : _this._strings['308f2757bfc9ce92fb00ff93fdffd279'],
     titlebar : true,
     width : 500,
     xns : Roo,
     xtype : 'LayoutRegion'
    },
    '|xns' : 'Roo',
    closable : true,
    collapsible : false,
    height : 500,
    modal : true,
    resizable : true,
    title : _this._strings['396ecabf0cd1f9503e591418851ef406'],
    width : 800,
    xns : Roo,
    xtype : 'LayoutDialog',
    buttons : [
      {
       '|xns' : 'Roo',
       text : _this._strings['31fde7b05ac8952dacf4af8a704074ec'],
       xns : Roo,
       xtype : 'Button',
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
       }
      },
{
       '|xns' : 'Roo',
       text : _this._strings['5b8ef4e762c00a15a41cfc26dc3ef99c'],
       xns : Roo,
       xtype : 'Button',
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
       }
      },
{
       '|xns' : 'Roo',
       text : _this._strings['ea4788705e6873b424c65e91c2846b19'],
       xns : Roo,
       xtype : 'Button',
       listeners : {
        click : function (_self, e)
         {
             _this.dialog.hide();
         }
       }
      },
{
       '|xns' : 'Roo',
       text : _this._strings['c9cc8cce247e49bae79f15173ce97354'],
       xns : Roo,
       xtype : 'Button',
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
       }
      }
    ],
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
    items : [
     {
      toolbar : {
       '|xns' : 'Roo',
       xns : Roo,
       xtype : 'Toolbar',
       items : [
        {
         menu : {
          '|xns' : 'Roo.menu',
          xns : Roo.menu,
          xtype : 'Menu',
          items : [
           {
            '|xns' : 'Roo.menu',
            text : _this._strings['e6b391a8d2c4d45902a23a8b6585703d'],
            xns : Roo.menu,
            xtype : 'Item',
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
            }
           },
           {
            '|xns' : 'Roo.menu',
            text : _this._strings['1bd18d39370b7f26c1c5e18067b74c6f'],
            xns : Roo.menu,
            xtype : 'Item',
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
            }
           }
          ]

         },
         '|xns' : 'Roo.Toolbar',
         text : _this._strings['72d6d7a1885885bb55a565fd1070581a'],
         xns : Roo.Toolbar,
         xtype : 'Button',
         items : [

         ]

        },
        {
         menu : {
          '|xns' : 'Roo.menu',
          xns : Roo.menu,
          xtype : 'Menu',
          items : [
           {
            '|xns' : 'Roo.menu',
            text : _this._strings['dc0de523c25be298ba751c63c694109e'],
            xns : Roo.menu,
            xtype : 'Item',
            listeners : {
             click : function (_self, e)
              {
              
                  var l = document.location;
                  new Pman.Request({
              
                      url : baseURL + '/Core/ImportMailMessage.php',
              
                      method: 'POST',
                      mask : "Loading",
                      params : {
                            importUrl : l.protocol +'//' + l.host +   rootURL + '/Pman/Crm/mail_templates/responsive1.html',
                     },
                      success : function (res) {
              
                       _this.form.findField('bodytext').setValue(res.data);
                      }
                
                  });
              }
            }
           }
          ]

         },
         '|xns' : 'Roo.Toolbar',
         text : _this._strings['884df8e413319ff51a3f5f528606238a'],
         xns : Roo.Toolbar,
         xtype : 'Button',
         items : [

         ]

        },
        {
         store : {
          proxy : {
           '|xns' : 'Roo.data',
           method : 'GET',
           url : baseURL + '/Core/MailTemplateList.php',
           xns : Roo.data,
           xtype : 'HttpProxy'
          },
          reader : {
           '|xns' : 'Roo.data',
           fields : [{"name":"file","type":"string"},{"name":"content","type":"string"}],
           id : 'name',
           root : 'data',
           totalProperty : 'total',
           xns : Roo.data,
           xtype : 'JsonReader'
          },
          '|xns' : 'Roo.data',
          remoteSort : true,
          sortInfo : { direction : 'DESC', field: 'file' },
          xns : Roo.data,
          xtype : 'Store',
          listeners : {
           beforeload : function (_self, o){
                o.params = o.params || {};
                // set more here
               
            }
          },
          items : [

          ]

         },
         '|xns' : 'Roo.form',
         allowBlank : true,
         alwaysQuery : true,
         displayField : 'file',
         editable : false,
         emptyText : _this._strings['31bb2f6e9b8fb11cbb7fb63c6025223f'],
         fieldLabel : 'Template',
         forceSelection : true,
         hiddenName : 'template',
         listWidth : 400,
         loadingText : _this._strings['1243daf593fa297e07ab03bf06d925af'],
         minChars : 2,
         name : 'template',
         pageSize : 20,
         qtip : _this._strings['31bb2f6e9b8fb11cbb7fb63c6025223f'],
         selectOnFocus : true,
         tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{file}</b> </div>',
         triggerAction : 'all',
         typeAhead : true,
         valueField : 'file',
         width : 200,
         xns : Roo.form,
         xtype : 'ComboBox',
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
         items : [

         ]

        },
        {
         '|xns' : 'Roo.Toolbar',
         xns : Roo.Toolbar,
         xtype : 'Fill'
        },
        {
         '|xns' : 'Roo.Toolbar',
         text : _this._strings['ea30b40c3caf28acb29198d20d243e54'],
         xns : Roo.Toolbar,
         xtype : 'Button',
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
         }
        }
       ]

      },
      layout : {
       center : {
        '|xns' : 'Roo',
        autoScroll : true,
        xns : Roo,
        xtype : 'LayoutRegion'
       },
       '|xns' : 'Roo',
       xns : Roo,
       xtype : 'BorderLayout',
       items : [
        {
         '|xns' : 'Roo',
         autoScroll : false,
         background : false,
         fitContainer : true,
         fitToFrame : true,
         region : 'center',
         title : _this._strings['4c2a8fe7eaf24721cc7a9f0175115bd4'],
         xns : Roo,
         xtype : 'ContentPanel',
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
         items : [
          {
           '|xns' : 'Roo.form',
           labelAlign : 'right',
           labelWidth : 120,
           method : 'POST',
           preValidate : function(done_callback) {
               
               Roo.MessageBox.progress("Uploading Images", "Uploading");
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
                   
                   n = i.getAttribute('src').match(/^http(.*)/)
                  
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
           xns : Roo.form,
           xtype : 'Form',
           listeners : {
            actioncomplete : function(_self,action)
             {
                
                 if (action.type == 'setdata') {
                 
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
           items : [
            {
             '|xns' : 'Roo.form',
             xns : Roo.form,
             xtype : 'Row',
             items : [
              {
               '|xns' : 'Roo.form',
               allowBlank : false,
               fieldLabel : 'Mailout Name',
               name : 'name',
               width : 400,
               xns : Roo.form,
               xtype : 'TextField'
              }
             ]

            },
            {
             '|xns' : 'Roo.form',
             xns : Roo.form,
             xtype : 'Row',
             items : [
              {
               '|xns' : 'Roo.form',
               allowBlank : false,
               fieldLabel : 'From',
               name : 'from_name',
               width : 300,
               xns : Roo.form,
               xtype : 'TextField'
              },
              {
               '|xns' : 'Roo.form',
               allowBlank : false,
               fieldLabel : 'Email address',
               name : 'from_email',
               width : 300,
               xns : Roo.form,
               xtype : 'TextField'
              }
             ]

            },
            {
             '|xns' : 'Roo.form',
             allowBlank : false,
             fieldLabel : 'Subject',
             name : 'subject',
             width : 600,
             xns : Roo.form,
             xtype : 'TextField'
            },
            {
             '|xns' : 'Roo.form',
             hideLabels : true,
             xns : Roo.form,
             xtype : 'Row',
             items : [
              {
               '|xns' : 'Roo.form',
               hideLabels : true,
               legend : _this._strings['962b90039a542a29cedd51d87a9f28a1'],
               style : 'text-align:center;',
               xns : Roo.form,
               xtype : 'FieldSet',
               items : [
                {
                 '|xns' : 'Roo.form',
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
                     'right',
                  ],
                 height : 250,
                 name : 'bodytext',
                 resizable : 's',
                 xns : Roo.form,
                 xtype : 'HtmlEditor',
                 toolbars : [
                   {
                    '|xns' : 'Roo.form.HtmlEditor',
                    xns : Roo.form.HtmlEditor,
                    xtype : 'ToolbarContext'
                   },
{
                    '|xns' : 'Roo.form.HtmlEditor',
                    xns : Roo.form.HtmlEditor,
                    xtype : 'ToolbarStandard',
                    btns : [
                      {
                       store : {
                        proxy : {
                         '|xns' : 'Roo.data',
                         method : 'GET',
                         url : baseURL + '/Roo/Images.php',
                         xns : Roo.data,
                         xtype : 'HttpProxy'
                        },
                        reader : {
                         '|xns' : 'Roo.data',
                         fields : [{"name":"id","type":"int"},{"name":"filename","type":"string"},{"name":"url_thumb","type":"string"}],
                         id : 'id',
                         root : 'data',
                         totalProperty : 'total',
                         xns : Roo.data,
                         xtype : 'JsonReader'
                        },
                        '|xns' : 'Roo.data',
                        remoteSort : true,
                        sortInfo : { direction : 'ASC', field: 'id' },
                        xns : Roo.data,
                        xtype : 'Store',
                        listeners : {
                         beforeload : function (_self, o){
                              o.params = o.params || {};
                          
                              var id = _this.form.findField('id').getValue() * 1;    
                              if (!id) {
                                  Roo.MessageBox.alert("Error", "Save email template first");
                                  return false;
                              }
                              o.params.onid = id
                              o.params.ontable = (_this.data.module) ? _this.data.module : 'crm_mailing_list_message';
                              
                             // o.params.imgtype = 'PressRelease';
                              //o.params['query[imagesize]'] = '150x150';
                              // set more here
                          }
                        },
                        items : [

                        ]

                       },
                       '|xns' : 'Roo.form',
                       alwaysQuery : true,
                       displayField : 'name',
                       editable : false,
                       emptyText : _this._strings['b9c49611cfda3259a2b837b39489e650'],
                       fieldLabel : 'Images',
                       forceSelection : true,
                       listWidth : 400,
                       loadingText : _this._strings['1243daf593fa297e07ab03bf06d925af'],
                       minChars : 2,
                       pageSize : 20,
                       qtip : _this._strings['2f26e35d61be90501e099089dc533638'],
                       selectOnFocus : true,
                       tpl : '<div class=\"x-grid-cell-text x-btn button\"><img src=\"{public_baseURL}/Core/Images/Thumb/150x150/{id}.jpg\" height=\"150\" width=\"150\"><b>{filename}</b> </div>',
                       triggerAction : 'all',
                       typeAhead : true,
                       valueField : 'id',
                       width : 100,
                       xns : Roo.form,
                       xtype : 'ComboBox',
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
                             editor.insertAtCursor(
                                     String.format('<img src="{0}/Images/{1}/{2}#image-{1}">',
                                     baseURL,  record.data.id, record.data.filename
                                     )
                              );
                         
                             
                          }
                       },
                       items : [

                       ]

                      },
{
                       store : {
                        '|xns' : 'Roo.data',
                        data : [ 
                            [ '{person.firstname}', "First Name"],
                            [ '{person.lastname}' , "Last Name"],
                            [ '{person.name}', "Full Name"],
                            [ '#unsubscribe', "Unsubscribe"]
                        ],
                        fields : [  'type', 'name'],
                        xns : Roo.data,
                        xtype : 'SimpleStore'
                       },
                       '|xns' : 'Roo.form',
                       alwaysQuery : true,
                       displayField : 'name',
                       editable : false,
                       emptyText : _this._strings['5feb9bf3c03b32635135006cbacb9542'],
                       fieldLabel : 'Field',
                       forceSelection : true,
                       listWidth : 400,
                       loadingText : _this._strings['1243daf593fa297e07ab03bf06d925af'],
                       minChars : 2,
                       pageSize : 20,
                       qtip : _this._strings['5feb9bf3c03b32635135006cbacb9542'],
                       selectOnFocus : true,
                       tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{name}</b> </div>',
                       triggerAction : 'all',
                       typeAhead : true,
                       valueField : 'type',
                       width : 100,
                       xns : Roo.form,
                       xtype : 'ComboBox',
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
                       items : [

                       ]

                      }
                    ],
                    items : [

                    ]

                   }
                 ],
                 items : [

                 ]

                }
               ]

              }
             ]

            },
            {
             '|xns' : 'Roo.form',
             hideLabels : true,
             xns : Roo.form,
             xtype : 'Row',
             items : [
              {
               '|xns' : 'Roo',
               text : _this._strings['e9968623956c15023d54335ea3699855'],
               xns : Roo,
               xtype : 'Button',
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
               }
              }
             ]

            },
            {
             '|xns' : 'Roo.form',
             hideLabels : true,
             xns : Roo.form,
             xtype : 'Row',
             items : [
              {
               '|xns' : 'Roo.form',
               hideLabels : true,
               legend : _this._strings['e44b145bd8b49b06e0ad2ced1ad56466'],
               style : 'text-align:center;',
               xns : Roo.form,
               xtype : 'FieldSet',
               items : [
                {
                 '|xns' : 'Roo.form',
                 height : 50,
                 name : 'plaintext',
                 xns : Roo.form,
                 xtype : 'TextArea'
                }
               ]

              }
             ]

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

      },
      '|xns' : 'Roo',
      autoScroll : false,
      fitContainer : true,
      fitToFrame : true,
      region : 'center',
      xns : Roo,
      xtype : 'NestedLayoutPanel',
      items : [

      ]

     },
     {
      grid : {
       dataSource : {
        proxy : {
         '|xns' : 'Roo.data',
         method : 'GET',
         url : baseURL + '/Roo/Images.php',
         xns : Roo.data,
         xtype : 'HttpProxy'
        },
        reader : {
         '|xns' : 'Roo.data',
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
         xtype : 'JsonReader'
        },
        '|xns' : 'Roo.data',
        remoteSort : true,
        sortInfo : { field : 'filename', direction: 'ASC' },
        xns : Roo.data,
        xtype : 'Store',
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
        items : [

        ]

       },
       toolbar : {
        '|xns' : 'Roo',
        xns : Roo,
        xtype : 'Toolbar',
        items : [
         {
          '|xns' : 'Roo.Toolbar',
          cls : 'x-btn-text-icon',
          icon : Roo.rootURL + 'images/default/dd/drop-add.gif',
          text : _this._strings['ec211f7c20af43e742bf2570c3cb84f9'],
          xns : Roo.Toolbar,
          xtype : 'Button',
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
          }
         },
         {
          '|xns' : 'Roo.Toolbar',
          cls : 'x-btn-text-icon',
          icon : rootURL + '/Pman/templates/images/trash.gif',
          text : _this._strings['f2a6c498fb90ee345d997f888fce3b18'],
          xns : Roo.Toolbar,
          xtype : 'Button',
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
          }
         }
        ]

       },
       '|xns' : 'Roo.grid',
       autoExpandColumn : 'filename',
       loadMask : true,
       xns : Roo.grid,
       xtype : 'Grid',
       colModel : [
         {
          '|xns' : 'Roo.grid',
          dataIndex : 'filename',
          header : _this._strings['1351017ac6423911223bc19a8cb7c653'],
          renderer : function(v,x,r)
          {
             return '<img src="' + baseURL + '/Images/' + r.data.id + '/' + r.data.filename + '" width="' + r.data.width + '" height="' + r.data.height + '" />';
          },
          width : 300,
          xns : Roo.grid,
          xtype : 'ColumnModel'
         },
{
          '|xns' : 'Roo.grid',
          dataIndex : 'displayorder',
          header : _this._strings['2393ad754ba179442d85e415d1d5167c'],
          renderer : function(v) { return String.format('{0}', v); },
          width : 75,
          xns : Roo.grid,
          xtype : 'ColumnModel'
         },
{
          '|xns' : 'Roo.grid',
          dataIndex : 'title',
          header : _this._strings['b78a3223503896721cca1303f776159b'],
          renderer : function(v) { return String.format('{0}', v); },
          width : 75,
          xns : Roo.grid,
          xtype : 'ColumnModel'
         }
       ],
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
       items : [

       ]

      },
      '|xns' : 'Roo',
      autoScroll : false,
      background : false,
      fitContainer : true,
      fitToframe : true,
      region : 'east',
      tableName : 'Images',
      title : _this._strings['308f2757bfc9ce92fb00ff93fdffd279'],
      xns : Roo,
      xtype : 'GridPanel',
      listeners : {
       activate : function() {
            _this.ipanel = this;
            if (_this.igrid) {
               _this.igrid.ds.load({});
            }
        }
      },
      items : [

      ]

     }
    ]

   });
 }
};
