//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Pman.Dialog.Image = {

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
                    // this does not really work - escape on the borders works..
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
            resizable : false,
            title : "Upload an Image or File",
            uploadComplete : false,
            width : 500,
            shadow : true,
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
                                    _this.dialog.haveProgress = 2; 
                                    Roo.MessageBox.hide(); // force hiding
                                    //_this.dialog.el.unmask();
                                     
                                    if (act.type == 'setdata') { 
                                        this.url = _this.data._url ? _this.data._url : baseURL + '/Roo/Images.php';
                                        this.el.dom.action = this.url;
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
                                rendered : function (form)
                                {
                                    _this.form= form;
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
                                }
                            },
                            fileUpload : true,
                            labelWidth : 140,
                            method : 'POST',
                            style : 'margin:10px;',
                            timeout : 300,
                            url : baseURL + '/Roo/Images.php',
                            items : [
                                {
                                    xtype: 'Hidden',
                                    xns: Roo.form,
                                    name : 'UPLOAD_IDENTIFIER'
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
                                },
                                {
                                    xtype: 'TextField',
                                    xns: Roo.form,
                                    fieldLabel : 'Upload Image or File',
                                    inputType : 'file',
                                    name : 'imageUpload',
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
                                    name : 'id'
                                },
                                {
                                    xtype: 'Hidden',
                                    xns: Roo.form,
                                    name : 'imgtype'
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
                    text : "Upload"
                }
            ]
        });
    }
};
