//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Pman.Dialog.ImageUpload = {

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
            xtype: 'LayoutDialog',
            xns: Roo,
            listeners : {
                show : function (_self)
                {
                    if (this.keylistener) {
                        return;
                    }
                    this.keylistener = this.addKeyListener(27, this.hide, this);
                }
            },
            closable : false,
            collapsible : false,
            haveProgress : false,
            height : 140,
            modal : true,
            resizable : true,
            title : "Upload an Image or File",
            uploadProgress : false,
            width : 500,
            shadow : true,
            uploadProgres : function()
            {
                var dlg = this;
               if (!dlg.haveProgress) {
                    Roo.MessageBox.progress("Uploading", "Uploading");
                }
                if (dlg.uploadComplete) {
                    Roo.MessageBox.hide();
                    return;
                }
                dlg.haveProgress = true;
            
                var uid = _this.form.findField('UPLOAD_IDENTIFIER').getValue();
                Pman.request({
                    url : baseURL + '/Core/UploadProgress.php',
                    params: {
                        id : uid
                    },
                    method: 'GET',
                    success : function(data){
                        //console.log(data);
                        if (dlg.uploadComplete) {
                            Roo.MessageBox.hide();
                            return;
                        }
                            
                        if (data){
                            Roo.MessageBox.updateProgress(data.bytes_uploaded/data.bytes_total,
                                Math.floor((data.bytes_total - data.bytes_uploaded)/1000) + 'k remaining'
                            );
                        }
                        dlg.uploadProgress.defer(2000,dlg);
                    },
                    failure: function(data) {
                      //  console.log('fail');
                     //   console.log(data);
                    }
                })
                
            },
            items : [
                {
                    xtype: 'ContentPanel',
                    xns: Roo,
                    fitToFrame : true,
                    region : 'center',
                    items : [
                        {
                            xtype: 'Form',
                            xns: Roo.form,
                            listeners : {
                                actioncomplete : function(_self,act)
                                {
                                       _this.dialog.uploadComplete = true;
                                        _this.dialog.el.unmask();
                                         
                                          if (act.type == 'setdata') { 
                                         
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
                                            //console.log(act);
                                            if (_this.callback) {
                                                _this.callback.call(this, act.result.data);
                                            }
                                            return; 
                                        }
                                },
                                rendered : function (form)
                                {
                                    _this.form= form;
                                },
                                actionfailed : function (_self, act)
                                {
                                    _this.dialog.uploadComplete = true;
                                    _this.dialog.el.unmask();
                                    // error msg???
                                    
                                    if (act.type == 'submit') {
                                        Ext.MessageBox.alert("Error", "Saving failed = fix errors and try again");
                                        return;
                                    }
                                    
                                    // what about load failing..
                                    Ext.MessageBox.alert("Error", "Error loading details"); 
                                }
                            },
                            labelWidth : 140,
                            method : 'POST',
                            style : 'margin:10px;',
                            url : baseURL + '/Roo/Images.php',
                            items : [
                                {
                                    xtype: 'Hidden',
                                    xns: Roo.form,
                                    name : 'UPLOAD_IDENTIFIER'
                                },
                                {
                                    xtype: 'TextField',
                                    xns: Roo.form,
                                    fieldLabel : 'Upload Image or File',
                                    inputType : 'file',
                                    name : 'image',
                                    width : 200
                                },
                                {
                                    xtype: 'Hidden',
                                    xns: Roo.form,
                                    name : 'ontable'
                                },
                                {
                                    xtype: 'Hidden',
                                    xns: Roo.form,
                                    name : 'onid'
                                },
                                {
                                    xtype: 'Hidden',
                                    xns: Roo.form,
                                    name : 'imgtype'
                                },
                                {
                                    xtype: 'Hidden',
                                    xns: Roo.form,
                                    name : 'post_max_size',
                                    value : "32M"
                                },
                                {
                                    xtype: 'Hidden',
                                    xns: Roo.form,
                                    name : 'upload_max_filesize',
                                    value : "32M"
                                }
                            ]
                        }
                    ]
                }
            ],
            center : {
                xtype: 'LayoutRegion',
                xns: Roo
            },
            buttons : [
                {
                    xtype: 'Button',
                    xns: Roo,
                    listeners : {
                        click : function (_self, e)
                        {
                            _this.dialog.hide();
                        }
                    },
                    text : "Cancel"
                },
                {
                    xtype: 'Button',
                    xns: Roo,
                    listeners : {
                        click : function (_self, e)
                        {
                            // do some checks?
                             
                            _this.dialog.el.mask("Sending");
                            _this.dialog.uploadComplete = false;
                            _this.form.doAction('submit', {
                                url: baseURL + '/Roo/Images.html',
                                method: 'POST',
                                params: {
                                 //   _id: 0 ,
                                    ts : Math.random()
                                } 
                            });
                            _this.dialog.haveProgress = false,
                            _this.dialog.uploadProgress.defer(1000, _this.dialog);
                        
                        }
                    },
                    text : "Post"
                }
            ]
        });
    }
};
