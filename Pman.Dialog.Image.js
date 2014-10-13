//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

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
            center : {
                '|xns' : 'Roo',
                xtype : 'LayoutRegion',
                xns : Roo
            },
            '|xns' : 'Roo',
            modal : true,
            shadow : true,
            collapsible : false,
            title : "Upload an Image or File",
            xtype : 'LayoutDialog',
            uploadComplete : false,
            width : 500,
            xns : Roo,
            closable : false,
            resizable : false,
            haveProgress : false,
            height : 140,
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
            buttons : [
            	 {
            	        '|xns' : 'Roo',
            	        text : "Cancel",
            	        xtype : 'Button',
            	        xns : Roo,
            	        listeners : {
            	        	click : function (_self, e)
            	        	   {
            	        	       _this.dialog.hide();
            	        	   }
            	        }
            	    },
{
            	        '|xns' : 'Roo',
            	        text : "Upload",
            	        xtype : 'Button',
            	        xns : Roo,
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
            	        }
            	    }
            ],
            listeners : {
            	show : function (_self)
            	   {
            	   
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
            items : [
            	{
                    '|xns' : 'Roo',
                    fitToFrame : true,
                    region : 'center',
                    xtype : 'ContentPanel',
                    xns : Roo,
                    items : [
                    	{
                            '|xns' : 'Roo.form',
                            url : baseURL + '/Roo/Images.php',
                            fileUpload : true,
                            method : 'POST',
                            style : 'margin:10px;',
                            xtype : 'Form',
                            labelWidth : 140,
                            timeout : 300,
                            xns : Roo.form,
                            listeners : {
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
                            	actioncomplete : function(_self,act)
                            	   {
                            	       _this.dialog.uploadComplete = true;
                            	       _this.dialog.haveProgress = 2; 
                            	       Roo.MessageBox.hide(); // force hiding
                            	       //_this.dialog.el.unmask();
                            	        
                            	       if (act.type == 'setdata') { 
                            	           this.url = _this.data._url ? _this.data._url : baseURL + '/Roo/Images.php';
                            	           this.el.dom.action = this.url;
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
                            	rendered : function (form)
                            	   {
                            	       _this.form= form;
                            	   }
                            },
                            items : [
                            	{
                                    '|xns' : 'Roo.form',
                                    xtype : 'Hidden',
                                    xns : Roo.form,
                                    name : 'UPLOAD_IDENTIFIER'
                                },
                            	{
                                    '|xns' : 'Roo.form',
                                    xtype : 'Hidden',
                                    value : "32M",
                                    xns : Roo.form,
                                    name : 'post_max_size'
                                },
                            	{
                                    '|xns' : 'Roo.form',
                                    xtype : 'Hidden',
                                    value : "32M",
                                    xns : Roo.form,
                                    name : 'upload_max_filesize'
                                },
                            	{
                                    '|xns' : 'Roo.form',
                                    inputType : 'file',
                                    fieldLabel : 'Upload Image or File',
                                    xtype : 'TextField',
                                    width : 200,
                                    xns : Roo.form,
                                    name : 'imageUpload'
                                },
                            	{
                                    '|xns' : 'Roo.form',
                                    xtype : 'Hidden',
                                    xns : Roo.form,
                                    name : 'ontable'
                                },
                            	{
                                    '|xns' : 'Roo.form',
                                    xtype : 'Hidden',
                                    xns : Roo.form,
                                    name : 'onid'
                                },
                            	{
                                    '|xns' : 'Roo.form',
                                    xtype : 'Hidden',
                                    xns : Roo.form,
                                    name : 'id'
                                },
                            	{
                                    '|xns' : 'Roo.form',
                                    xtype : 'Hidden',
                                    xns : Roo.form,
                                    name : 'imgtype'
                                }
                            ]

                        }
                    ]

                }
            ]

        });
    }
};
