//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.Image = {

 _strings : {
  'eb5d45750c7ab13aa8e6bacc80315a30' :"32M",
  '2859a4ae58ae4e25abdfc530f814e42f' :"Upload an Image or File",
  '1243daf593fa297e07ab03bf06d925af' :"Searching...",
  'ea4788705e6873b424c65e91c2846b19' :"Cancel",
  '8e16a71b3d8217eb80b39b7d8dec4296' :"Image Type",
  'dff0c70e4c11953e4e3ee1cf268fb96d' :"Select image type",
  '91412465ea9169dfd901dd5e7c96dd99' :"Upload",
  'ea72bacd2fdfa818907bb9559e6905a1' :"Upload Image or File"
 },
 _named_strings : {
  'imgtype_name_fieldLabel' : '8e16a71b3d8217eb80b39b7d8dec4296' /* Image Type */ ,
  'imgtype_name_loadingText' : '1243daf593fa297e07ab03bf06d925af' /* Searching... */ ,
  'imgtype_name_emptyText' : 'dff0c70e4c11953e4e3ee1cf268fb96d' /* Select image type */ ,
  'upload_max_filesize_value' : 'eb5d45750c7ab13aa8e6bacc80315a30' /* 32M */ ,
  'imgtype_name_qtip' : 'dff0c70e4c11953e4e3ee1cf268fb96d' /* Select image type */ ,
  'post_max_size_value' : 'eb5d45750c7ab13aa8e6bacc80315a30' /* 32M */ ,
  'imageUpload_fieldLabel' : 'ea72bacd2fdfa818907bb9559e6905a1' /* Upload Image or File */ 
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
    haveProgress : false,
    height : 140,
    modal : true,
    resizable : false,
    shadow : true,
    title : _this._strings['2859a4ae58ae4e25abdfc530f814e42f'] /* Upload an Image or File */,
    uploadCallback : function() {
        _this.dialog.uploadComplete = false;
        _this.form.doAction('submit', {
            params: {
                ts : Math.random()
            }
        });
        
        if (!_this.data.useSSE) {
            _this.dialog.haveProgress = 0; // set to show..
            _this.dialog.uploadProgress.defer(1000, _this.dialog);
        }
        
        _this.form.findField('imageUpload').el.un('change', _this.dialog.uploadCallback);
    },
    uploadComplete : false,
    uploadProgress : function()
    {
        var dlg = this;
       if (!dlg.haveProgress) {
            Roo.MessageBox.progress("Uploading", "Uploading");
        }
        
        if (dlg.haveProgress == 2) {
            // it's been closed elsewhere..
            return;
        }
        if (dlg.uploadComplete) {
            Roo.MessageBox.hide();
            return;
        }
        
        dlg.haveProgress = 1;
    
        var uid = _this.form.findField('UPLOAD_IDENTIFIER').getValue();
        new Pman.Request({
            url : baseURL + '/Core/UploadProgress.php',
            params: {
                id : uid
            },
            method: 'GET',
            success : function(res){
                //console.log(data);
                var data = res.data;
                if (dlg.haveProgress == 2) {
                    // it's been closed elsewhere..
                    return;
                }
                
                if (dlg.uploadComplete) {
                    Roo.MessageBox.hide();
                    return;
                }
                    
                if (data){
                    Roo.MessageBox.updateProgress(data.bytes_uploaded/data.bytes_total,
                        Math.floor((data.bytes_total - data.bytes_uploaded)/1000) + 'k remaining'
                    );
                } else {
                    Roo.MessageBox.updateProgress(1,
                        "Upload Complete - processing"
                    );
                    return;
                }
                dlg.uploadProgress.defer(2000,dlg);
            },
            failure: function(data) {
              //  console.log('fail');
             //   console.log(data);
            }
        })
        
    },
    width : 500,
    listeners : {
     show : function (_self)
      {
          _this.form.findField('imageUpload').el.on('change', _self.uploadCallback);
          _this.form.findField('imageUpload').el.dom.click();
          _this.dialog.hide();
      
          // this does not really work - escape on the borders works..
          // resize to fit.. if we have styled stuff...
          
          
          
          
          var d = this;
          
          var pad =     d.el.getSize().height - (d.header.getSize().height +
              d.footer.getSize().height +        
              d.layout.getRegion('center').getPanel(0).el.getSize().height
              );
          
          var height = (
              pad + 
              d.header.getSize().height +
              d.footer.getSize().height +        
              d.layout.getRegion('center').getPanel(0).el.child('div').getSize().height
          );
          this.resizeTo(d.el.getSize().width, height);
          
          if (this.keylistener) {
              return;
          }
          this.keylistener = this.addKeyListener(27, this.hide, this);
          
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
      text : _this._strings['91412465ea9169dfd901dd5e7c96dd99'] /* Upload */,
      listeners : {
       click : function (_self, e)
        {
            // do some checks?
             
            //_this.dialog.el.mask("Sending");
            _this.dialog.uploadComplete = false;
            _this.form.doAction('submit', {
                params: {
                    ts : Math.random()
                }
            });
            
            _this.dialog.haveProgress = 0; // set to show..
            _this.dialog.uploadProgress.defer(1000, _this.dialog);
        
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
      xns : Roo,
      '|xns' : 'Roo',
      items  : [
       {
        xtype : 'Form',
        fileUpload : true,
        labelWidth : 140,
        method : 'POST',
        style : 'margin:10px;',
        timeout : 300,
        url : baseURL + '/Roo/Images.php',
        listeners : {
         actioncomplete : function(_self,act)
          {
              _this.dialog.uploadComplete = true;
              _this.dialog.haveProgress = 2; 
              Roo.MessageBox.hide(); // force hiding
              //_this.dialog.el.unmask();
               
              if (act.type == 'setdata') { 
                  Roo.log("SET DATA!!!!!!!!!!!!!!!!!");
              
                  _this.form.findField('imgtype').hide();
                  
                  _this.dialog.resizeTo(500, 140);
                  
                  if(_this.data._show_image_type){
                      _this.form.findField('imgtype').show();
                      _this.dialog.resizeTo(500, 170);
                  }
                  
                  this.url = _this.data._url ? _this.data._url : baseURL + '/Roo/Images.php';
                  this.el.dom.action = this.url;
                  this.useSSE = _this.data.useSSE || false;
                  if (typeof(_this.data.timeout) != 'undefined') {
                      this.timeout = _this.data.timeout;
                  }
                  
                  this.findField('UPLOAD_IDENTIFIER').setValue(
                      (new Date() * 1) + '' + Math.random());
                      
                  return;
              }
               
             
              if (act.type == 'load') {
                // should this happen?  
                  _this.data = act.result.data;
                 // _this.loaded();
                  return;
              }
              
              
              if (act.type == 'submit') { // only submitted here if we are 
                  _this.dialog.hide();
                  Roo.log("Upload success");
                  Roo.log(act);
                  //console.log(act);
                  if (_this.callback) {
                      _this.callback.call(this, act.result.data, act.result.extra);
                  }
                  return; 
              }
           
          
              
          },
         actionfailed : function (_self, act)
          {
             
             
              _this.dialog.uploadComplete = true;
             // _this.dialog.el.unmask();
              // error msg???
               _this.dialog.haveProgress = 2; 
              if (act.type == 'submit') {
                  Roo.log("Upload error");
                  Roo.log(act);
                  
                  try {
                      Roo.MessageBox.alert("Error", act.result.errorMsg.split(/\n/).join('<BR/>'));
                  } catch(e) {
                    //  Roo.log(e);
                      Roo.MessageBox.alert("Error", "Saving failed = fix errors and try again");        
                  }
                  return;
              }
              
              // what about load failing..
              Roo.MessageBox.alert("Error", "Error loading details"); 
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
          xtype : 'Hidden',
          name : 'UPLOAD_IDENTIFIER',
          xns : Roo.form,
          '|xns' : 'Roo.form'
         },
         {
          xtype : 'Hidden',
          name : 'post_max_size',
          value : _this._strings['eb5d45750c7ab13aa8e6bacc80315a30'] /* 32M */,
          xns : Roo.form,
          '|xns' : 'Roo.form'
         },
         {
          xtype : 'Hidden',
          name : 'upload_max_filesize',
          value : _this._strings['eb5d45750c7ab13aa8e6bacc80315a30'] /* 32M */,
          xns : Roo.form,
          '|xns' : 'Roo.form'
         },
         {
          xtype : 'TextField',
          allowBlank : false,
          fieldLabel : _this._strings['ea72bacd2fdfa818907bb9559e6905a1'] /* Upload Image or File */,
          inputType : 'file',
          name : 'imageUpload',
          width : 200,
          xns : Roo.form,
          '|xns' : 'Roo.form'
         },
         {
          xtype : 'ComboBox',
          actionMode : 'fieldEl',
          allowBlank : true,
          alwaysQuery : true,
          displayField : 'display_name',
          emptyText : _this._strings['dff0c70e4c11953e4e3ee1cf268fb96d'] /* Select image type */,
          fieldLabel : _this._strings['8e16a71b3d8217eb80b39b7d8dec4296'] /* Image Type */,
          forceSelection : true,
          hiddenName : 'imgtype',
          listWidth : 400,
          loadingText : _this._strings['1243daf593fa297e07ab03bf06d925af'] /* Searching... */,
          minChars : 2,
          name : 'imgtype_name',
          pageSize : 20,
          qtip : _this._strings['dff0c70e4c11953e4e3ee1cf268fb96d'] /* Select image type */,
          queryParam : 'query[search]',
          selectOnFocus : true,
          tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{display_name}</b> {name}</div>',
          triggerAction : 'all',
          typeAhead : true,
          valueField : 'name',
          width : 250,
          xns : Roo.form,
          '|xns' : 'Roo.form',
          store : {
           xtype : 'Store',
           remoteSort : true,
           sortInfo : { direction : 'ASC', field: 'name' },
           listeners : {
            beforeload : function (_self, o){
                 o.params = o.params || {};
                 
                 o.params.etype = 'ImageType';
                 
                 o.params.active = 1;
             }
           },
           xns : Roo.data,
           '|xns' : 'Roo.data',
           proxy : {
            xtype : 'HttpProxy',
            method : 'GET',
            url : baseURL + '/Roo/Core_enum',
            xns : Roo.data,
            '|xns' : 'Roo.data'
           },
           reader : {
            xtype : 'JsonReader',
            fields : [{"name":"id","type":"int"},{"name":"name","type":"string"}],
            id : 'id',
            root : 'data',
            totalProperty : 'total',
            xns : Roo.data,
            '|xns' : 'Roo.data'
           }
          }
         },
         {
          xtype : 'Hidden',
          name : 'ontable',
          xns : Roo.form,
          '|xns' : 'Roo.form'
         },
         {
          xtype : 'Hidden',
          name : 'onid',
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
