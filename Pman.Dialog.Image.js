//<script type="text/javascript">

/*
Pman.Dialog.Image = {
    dialog : false,
    form : false,
    create: function()
    {
        if (this.dialog) {
            return;
        }
        var _this = this;
        
        this.dialog = new Ext.LayoutDialog(Ext.get(document.body).createChild({tag:'div'}),  { 
            autoCreated: true,
            title: "Upload Image or  File",
            modal: true,
            width:  500,
            height: 140,
            shadow:true,
            minWidth:200,
            minHeight:180,
            //proxyDrag: true,
            closable: false,
            draggable: false,
            center: {
                autoScroll:false,
                titlebar: false,
               // tabPosition: 'top',
                hideTabs: true,
                closeOnTab: true,
                alwaysShowTabs: false
            }
        });
        this.dialog.addKeyListener(27, this.dialog.hide, this.dialog);
        this.dialog.addButton("Cancel", this.dialog.hide, this.dialog);
       
        this.dialog.addButton("Save", this.save, this);
        this.layout = this.dialog.getLayout();
        this.layout.beginUpdate();
        
         
        this.form = new Ext.form.Form({
            labelWidth: 150 ,
            fileUpload : true,
            listeners : {
                actionfailed : function(f, act) {
                    _this.uploadComplete = true;
                    _this.dialog.el.unmask();
                    // error msg???
                    
                    if (act.type == 'submit') {
                        Ext.MessageBox.alert("Error", "Saving failed = fix errors and try again");
                        return;
                    }
                    
                    // what about load failing..
                    Ext.MessageBox.alert("Error", "Error loading details"); 
                              
                },
                actioncomplete: function(f, act) {
                    _this.uploadComplete = true;
                    _this.dialog.el.unmask();
                     
                   
                    if (act.type == 'load') {
                        
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
                    // unmask?? 
                }
            }
        
            
            
             
        });
        //?? will this work...
        this.form.addxtype.apply(this.form,
            [
                // type filed??
                { name: 'UPLOAD_IDENTIFIER' , xtype: 'Hidden' },
                { 
                        xtype :  'TextField',
                        name : 'imageUpload',
                        fieldLabel : "Upload Image or File",
                        inputType : 'file'
                },
                { name: 'ontable', xtype: 'Hidden' },
                { name: 'onid', xtype: 'Hidden' },
                { name: 'imgtype', xtype: 'Hidden' }, // special value for sorting!!
                { name: 'post_max_size', xtype: 'Hidden' , value :'32M'} ,
                { name: 'upload_max_filesize', xtype: 'Hidden' , value :'32M'} 
                    
                   
                 
        ]

        );
        var ef = this.dialog.getLayout().getEl().createChild({tag: 'div'});
        ef.dom.style.margin = 10;
         
        this.form.render(ef.dom);

        var vp = this.dialog.getLayout().add('center', new Ext.ContentPanel(ef, {
            autoCreate : true,
            //title: 'Org Details',
            //toolbar: this.tb,
            width: 250,
            maxWidth: 250,
            fitToFrame:true
        }));
          

        
        
        this.layout.endUpdate();
    },
    _id : 0,
    
    show: function (data, callback)
    {
        
        this.callback = callback;
         this.create();
        this.form.reset();
        
        this.form.setValues(data);
        this.form.findField('UPLOAD_IDENTIFIER').setValue((new Date() * 1) + '' + Math.random());
        this.dialog.show();
        

    },
     
    save : function()
    {
        this.dialog.el.mask("Sending");
        this.uploadComplete = false;
        this.form.doAction('submit', {
            url: baseURL + '/Roo/Images.html',
            method: 'POST',
            params: {
             //   _id: 0 ,
                ts : Math.random()
            } 
        });
        this.haveProgress = false,
        this.uploadProgress.defer(1000, this);
        
    },
    uploadComplete : false,
    haveProgress: false,
    uploadProgress : function()
    {
        if (!this.haveProgress) {
            Roo.MessageBox.progress("Uploading", "Uploading");
        }
        if (this.uploadComplete) {
            Roo.MessageBox.hide();
            return;
        }
        this.haveProgress = true;
        var _this = this;
        var uid = this.form.findField('UPLOAD_IDENTIFIER').getValue();
        Pman.request({
            url : baseURL + '/Core/UploadProgress.php',
            params: {
                id : uid
            },
            method: 'GET',
            success : function(data){
                //console.log(data);
                if (_this.uploadComplete) {
                    Roo.MessageBox.hide();
                    return;
                }
                    
                if (data){
                    Roo.MessageBox.updateProgress(data.bytes_uploaded/data.bytes_total,
                        Math.floor((data.bytes_total - data.bytes_uploaded)/1000) + 'k remaining'
                    );
                }
                _this.uploadProgress.defer(2000, _this);
            },
            failure: function(data) {
              //  console.log('fail');
             //   console.log(data);
            }
        })
        
        
        
    }
    
    
    
    
         
};
*/