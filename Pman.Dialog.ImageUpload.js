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
            closable : false,
            collapsible : false,
            height : 140,
            modal : true,
            resizable : true,
            shadow : true,
            title : "Upload an Image or File",
            width : 500,
            items : [
                {
                    xtype: 'ContentPanel',
                    xns: Roo,
                    region : 'center',
                    items : [
                        {
                            xtype: 'Form',
                            xns: Roo.form,
                            listeners : {
                                actioncomplete : function(_self,act)
                                {
                                       _this.uploadComplete = true;
                                        _this.dialog.el.unmask();
                                         
                                       
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
                                    _this.uploadComplete = true;
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
                            _this.uploadComplete = false;
                            _this.form.doAction('submit', {
                                url: baseURL + '/Roo/Images.html',
                                method: 'POST',
                                params: {
                                 //   _id: 0 ,
                                    ts : Math.random()
                                } 
                            });
                            _this.haveProgress = false,
                            _this.uploadProgress.defer(1000, this);
                        
                        }
                    },
                    text : "Post"
                }
            ]
        });
    }
};
