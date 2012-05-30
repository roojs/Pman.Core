//<script type="text/javascript">


Pman.Dialog.Companies =   new Roo.util.Observable({
    events : {
        'beforerender' : true, // trigger so we can add modules later..
        'show' : true, // trigger on showing form.. - to load additiona data..
        'beforesave' : true
    },
     
    dialog : false,
    form : false,
    callback: false,
    create: function()
    {
        if (this.dialog) {
            return;
        }
        
        this.dialog = new Ext.LayoutDialog(Ext.get(document.body).createChild({tag:'div'}),  { 
            autoCreated: true,
            title: "Edit Companies",
            modal: true,
            width:  750,
            height: 400,
            shadow:true,
            minWidth:200,
            minHeight:180,
            //proxyDrag: true,
            collapsible : false,
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
        
        var _this = this;
        
        this.form = new Ext.form.Form({
            labelWidth: 150 ,
              
            fileUpload : true,
            listeners : {
                actionfailed : function(f, act) {
                    _this.dialog.el.unmask();
                    // error msg???
                    Pman.standardActionFailed(f,act);
                              
                },
                actioncomplete: function(f, act) {
                    _this.dialog.el.unmask();
                    //console.log('load completed'); 
                    // error messages?????
                    
                   
                    if (act.type == 'load') {
                        
                        _this.data = act.result.data;
                        var meth = _this.data.isOwner || !Pman.Login.isOwner() ? 'disable' : 'enable';
                     
                            
                        if (_this.form.findField('comptype')) {
                            _this.form.findField('comptype')[meth]();
                        }
                         
                       // _this.loaded();
                        return;
                    }
                    
                    
                    if (act.type == 'submit') { // only submitted here if we are 
                        _this.dialog.hide();
                       
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
        
        this.form.addxtype.apply(this.form, this.getFormFields());
         this.fireEvent('beforeRender', this );
        
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
    show : function (data, callback)
    {
        this.callback = callback;
        this._id = data.id ? data.id : 0;  // modify if you do not use ID !!!!
        this.create();
        this.data = data;
        this.form.reset();
        if (data._fetch) {
            this.dialog.show();
            this.dialog.el.mask("Loading");
            this.form.doAction('load', {
                url: baseURL + '/Roo/Companies.html',
                method: 'GET',
                params: {
                    _id: this._id ,
                    _ts : Math.random()
                } 
            });
            this.fireEvent('show');
            return;
        } else {
            this.form.setValues(data);
        }
        
        
        
        this.dialog.show();
        
        if (data.isOwner || !Pman.Login.isOwner()) {
            this.dialog.setTitle("Your Company Details");
            if (this.form.findField('comptype')) {
                this.form.findField('comptype').disable();
            }
            
            
            
            
        } else {
            this.dialog.setTitle(data.id ? "Edit Company" : "Add Company");
            if (this.form.findField('comptype')) {
                this.form.findField('comptype').enable();
            }
        }
        this.fireEvent('show');

    },
    
    
    save : function()
    {
        this.form.fileUpload = this.form.findField('imageUpload') ? true : false;
        this.fireEvent('beforesave'); 
        this.form.doAction('submit', {
            url: baseURL + '/Roo/Companies.html',
            method: 'POST',
            params: {
                _id: this._id ,
                ts : Math.random()
            } 
        });
    },
    
    comptypeList : function()
    {
        // should probably be system configurable..
        return [
            
            [ 'CONSULTANT', "Consultant" ],
            [ 'CLIENT'    ,  "Client" ],
            [ 'CONTRACTOR' , "Contractor" ]
          //  [ 'OWNER', "System Owner" ]
         ];
    },
    comptypeListToString: function(v) {
        if (!v.length) {
            return '';
        }
        if (v== "OWNER") {
            return "System Owner";
        }
        var a = this.comptypeList();
        var ret = '';
        Roo.each(a, function( ar) {
            if (ar[0] == v) {
                ret = ar[1];
                return false;
            }
        });
        return ret;
        
        
        
    },
    
    getFormFields : function() {
        return [
            {   
                xtype : 'Column',
                width: 500,
                items: [
                    this.c_code(),
                    this.c_comptype_name(),
                    this.c_name(),
                    this.c_tel(),
                    this.c_fax(),
                    this.c_email(),
                    
                    
                    this.c_address(),
                    this.c_remarks()
                ]
            },
            {   
                xtype : 'Column',
                width: 200,
                labelAlign: 'top',
                items : [
                    this.c_background_color(),
                    this.c_image_edit()
                    //this.c_image_view(),
                    //this.c_image_change(),
                ]
            },
            this.c_isOwner(),
            this.c_id()
        ];
    },
    
    
    c_code : function() {
        return {
                name : 'code',
                fieldLabel : "Company ID (for filing Ref.)",
                value : '',
                allowBlank : false,
                qtip : "Enter code",
                xtype : 'TextField',
                width : 100
            }
    },
    c_comptype_name : function() {
        return {
    			
				fieldLabel : 'Type',
				disabled : Pman.Login.isOwner() ? false : true,
                name : 'comptype_name',
                xtype : 'ComboBox',
                allowBlank : false,
				qtip : 'Select Company type',
                
                width: 200,
                xns : Roo.form,
                
                listWidth : 250,
                
               
                store: {
                    xtype : 'SimpleStore',
                    fields: ['val', 'desc'],
                    data : this.comptypeList()
                },
                displayField:'desc',
                valueField: 'val',
                hiddenName : 'comptype',
                
                typeAhead: false,
                editable: false,
                //mode: 'local',
                triggerAction: 'all',
                emptyText: "Select Type",
                selectOnFocus: true
                
                
           }
    },
    c_name : function() {
        return {
    
                name : 'name',
                fieldLabel : "Company Name",
                value : '',
                allowBlank : true,
                qtip : "Enter Company Name",
                xtype : 'TextField',
                width : 300
                    }
    },
    c_tel : function() {
        return {
    
                name : 'tel',
                fieldLabel : "Phone",
                value : '',
                allowBlank : true,
                qtip : "Enter Phone Number",
                xtype : 'TextField',
                width : 300
                    }
    },
    c_fax : function() {
        return {
    
                name : 'fax',
                fieldLabel : "fax",
                value : '',
                allowBlank : true,
                qtip : "Enter fax Number",
                xtype : 'TextField',
                width : 300
                    }
    },
    c_email : function() {
        return {
    
                name : 'email',
                fieldLabel : "Email",
                value : '',
                allowBlank : true,
                qtip : "Enter Email Address",
                xtype : 'TextField',
                width : 300
                    }
    },
    c_address : function() {
        return {
    
                name : 'address',
                fieldLabel : "Address",
                value : '',
                allowBlank : true,
                qtip : "Enter Address",
                xtype : 'TextArea',
                height : 70,
                width : 300
        }
    },
    c_remarks : function() {
        return {
    
                name : 'remarks',
                fieldLabel : "Remarks",
                value : '',
                allowBlank : true,
                qtip : "Enter remarks",
                xtype : 'TextArea',
                height : 40,
                width : 300
        }
    },
    c_background_color : function() {
        return {
                    xtype: 'ColorField',
                name : 'background_color',
                fieldLabel: "Background Colour"
        }
    },
    c_image_view : function() {
        var _this = this;
        return {
                xtype :  'FieldSetEx',
                name : 'image-view',
                collapseGroup : 'companies-image',
                value: 0,
                labelWidth: 100,
                expanded: true,
                style: 'width:420px;',
                legend : "Logo Image",
                items: [
                    {
                        xtype :  'DisplayImage', // image preview...
                        name : 'logo_id',
                        fieldLabel : 'Logo Image',
                        width: 300,
                        height: 50,
                        renderer : function(v) {
                            return v ?  String.format('<img src="{0}" height="{1}">', 
                                baseURL + '/Images/' + v + '/' + _this.data.logo_id_filename, 
                                Math.min(this.height, _this.data.logo_id_height)) : "No Image Attached";
                            
                        }
                    }
                ]
                
        }
    },
    c_image_edit : function() {
        var _this = this;
        return {
                    name : 'logo_id',
                    fieldLabel : "Logo Image",
                    value : '',
                    allowBlank : true,
                    style: 'border: 1px solid #ccc;',
                    xtype : 'DisplayImage',
                    width : 170,
                    height: 170,
                    addTitle : "Change / Add Image",
                    icon: Roo.rootURL + 'images/default/dd/drop-add.gif',
                    handler : function() {
                        var _t = this;
                         
                        Pman.Dialog.Image.show({
                            onid :_this.data.id,
                            ontable : 'Companies',
                            imgtype : 'LOGO'
                        }, function(data) {
                            if  (data) {
                                _t.setValue(data.id);
                            }
                            
                        });
                    }, 
                    renderer : function(v) {
                        //var vp = v ? v : 'Companies:' + _this.data.id + ':-LOGO';
                        if (!v) {
                            return "No Image Available" + '<BR/>';
                        }
                        return String.format('<img src="{0}" width="150">', 
                                baseURL + '/Images/Thumb/150x150/' + v + '/logo.jpg'
                        );
                    }
            
            }  ;
        
    },
    c_image_change: function() {
        return { 
                xtype :  'FieldSetEx',
                collapseGroup : 'companies-image',
                name : 'image-change',
                value: 0,
                labelWidth: 100,
                expanded: false,
                style: 'width:420px;',
                legend : "Add / Change Image",
                items : [ 
                    {   
                        xtype :  'TextField',
                        name : 'imageUpload',
                        fieldLabel : "Upload Image",
                        inputType : 'file'
                    }
                ]
        }
    },
    c_isOwner : function() {
        return {                 
                name : 'isOwner',
                value : '',
                xtype : 'Hidden'
            }
    },
    c_id : function() {
        return { 
                name : 'id',
                value : '',
                xtype : 'Hidden'
            }
    }
         
});
