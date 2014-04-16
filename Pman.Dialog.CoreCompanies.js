//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Pman.Dialog.CoreCompanies = {

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
            autoCreate : 'true',
            closable : false,
            collapsible : false,
            draggable : false,
            height : 400,
            modal : true,
            shadow : 'true',
            title : "Add / Edit Organization",
            width : 750,
            items : [
                {
                    xtype: 'ContentPanel',
                    xns: Roo,
                    autoCreate : 'true',
                    fitToFrame : true,
                    region : 'center',
                    items : [
                        {
                            xtype: 'Form',
                            xns: Roo.form,
                            listeners : {
                                actionfailed : function(f, act) {
                                    _this.dialog.el.unmask();
                                    // error msg???
                                    Pman.standardActionFailed(f,act);
                                              
                                },
                                actioncomplete : function(f, act) {
                                    _this.dialog.el.unmask();
                                    //console.log('load completed'); 
                                    // error messages?????
                                    if(act.type == 'setdata'){
                                        this.load({ method: 'GET', params: { '_id' : _this.data.id }});
                                        return;
                                    }
                                   
                                    if (act.type == 'load') {
                                        _this.data = act.result.data;
                                        var meth = _this.data.comptype == 'OWNER' ? 'disable' : 'enable';
                                     
                                            
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
                                },
                                rendered : function (form)
                                {
                                    _this.form = form;
                                }
                            },
                            fileUpload : true,
                            labelWidth : 160,
                            url : baseURL + '/Roo/Companies.php',
                            items : [
                                {
                                    xtype: 'Column',
                                    xns: Roo.form,
                                    width : 500,
                                    items : [
                                        {
                                            xtype: 'TextField',
                                            xns: Roo.form,
                                            allowBlank : true,
                                            fieldLabel : 'Company ID (for filing Ref.)',
                                            name : 'code',
                                            qtip : "Enter code",
                                            width : 100
                                        },
                                        {
                                            xtype: 'ComboBox',
                                            xns: Roo.form,
                                            listeners : {
                                                render : function (_self)
                                                {
                                                    _this.etypeCombo = _self;
                                                }
                                            },
                                            alwaysQuery : true,
                                            displayField : 'display_name',
                                            emptyText : "Select Type",
                                            fieldLabel : 'Type',
                                            forceSelection : true,
                                            hiddenName : 'comptype_id',
                                            listWidth : 250,
                                            loadingText : "Searching...",
                                            minChars : 2,
                                            name : 'comptype_display',
                                            pageSize : 20,
                                            qtip : "Select type",
                                            queryParam : 'query[name]',
                                            selectOnFocus : true,
                                            tpl : '<div class="x-grid-cell-text x-btn button"><b>{name}</b> : {display_name}</div>',
                                            triggerAction : 'all',
                                            typeAhead : false,
                                            valueField : 'name',
                                            width : 200,
                                            store : {
                                                xtype: 'Store',
                                                xns: Roo.data,
                                                listeners : {
                                                    beforeload : function (_self, o){
                                                        o.params = o.params || {};
                                                        // set more here
                                                        //o.params['query[empty_etype]'] = 1;
                                                        o.params.etype = 'COMPTYPE';
                                                    }
                                                },
                                                remoteSort : true,
                                                sortInfo : { direction : 'ASC', field: 'id' },
                                                proxy : {
                                                    xtype: 'HttpProxy',
                                                    xns: Roo.data,
                                                    method : 'GET',
                                                    url : baseURL + '/Roo/core_enum.php'
                                                },
                                                reader : {
                                                    xtype: 'JsonReader',
                                                    xns: Roo.data,
                                                    id : 'id',
                                                    root : 'data',
                                                    totalProperty : 'total',
                                                    fields : [{"name":"id","type":"int"},{"name":"name","type":"string"}]
                                                }
                                            }
                                        },
                                        {
                                            xtype: 'TextField',
                                            xns: Roo.form,
                                            allowBlank : true,
                                            fieldLabel : 'Company Name',
                                            name : 'name',
                                            qtip : "Enter Company Name",
                                            width : 300
                                        },
                                        {
                                            xtype: 'TextField',
                                            xns: Roo.form,
                                            allowBlank : true,
                                            fieldLabel : 'Phone',
                                            name : 'tel',
                                            qtip : "Enter Phone Number",
                                            width : 300
                                        },
                                        {
                                            xtype: 'TextField',
                                            xns: Roo.form,
                                            allowBlank : true,
                                            fieldLabel : 'Fax',
                                            name : 'fax',
                                            qtip : "Enter Fax Number",
                                            width : 300
                                        },
                                        {
                                            xtype: 'TextField',
                                            xns: Roo.form,
                                            allowBlank : true,
                                            fieldLabel : 'Email',
                                            name : 'email',
                                            qtip : "Enter Email Address",
                                            width : 300
                                        },
                                        {
                                            xtype: 'TextField',
                                            xns: Roo.form,
                                            allowBlank : true,
                                            fieldLabel : 'Address',
                                            name : 'address',
                                            qtip : "Enter Address",
                                            width : 300
                                        },
                                        {
                                            xtype: 'TextArea',
                                            xns: Roo.form,
                                            allowBlank : true,
                                            fieldLabel : 'Remarks',
                                            height : 120,
                                            name : 'remarks',
                                            qtip : "Enter remarks",
                                            width : 300
                                        }
                                    ]
                                },
                                {
                                    xtype: 'Column',
                                    xns: Roo.form,
                                    labelAlign : 'top',
                                    width : 200,
                                    items : [
                                        {
                                            xtype: 'ColorField',
                                            xns: Roo.form,
                                            fieldLabel : 'Background Colour',
                                            name : 'background_color'
                                        },
                                        {
                                            xtype: 'DisplayField',
                                            xns: Roo.form,
                                            fieldLabel : 'Logo Image',
                                            height : 170,
                                            icon : 'rootURL + \'images/default/dd/drop-add.gif\'',
                                            name : 'logo_id',
                                            style : 'border: 1px solid #ccc;',
                                            width : 170,
                                            valueRenderer : function(v) {
                                                //var vp = v ? v : 'Companies:' + _this.data.id + ':-LOGO';
                                                if (!v) {
                                                    return "No Image Available" + '<BR/>';
                                                }
                                                return String.format('<img src="{0}" width="150">', 
                                                        baseURL + '/Images/Thumb/150x150/' + v + '/logo.jpg'
                                                );
                                            }
                                        },
                                        {
                                            xtype: 'Button',
                                            xns: Roo,
                                            listeners : {
                                                click : function (_self, e)
                                                {
                                                    var _t = _this.form.findField('logo_id');
                                                                         
                                                    Pman.Dialog.Image.show({
                                                        onid :_this.data.id,
                                                        ontable : 'Companies',
                                                        imgtype : 'LOGO'
                                                    }, function(data) {
                                                        if  (data) {
                                                            _t.setValue(data.id);
                                                        }
                                                        
                                                    });
                                                }
                                            },
                                            text : "Add Image"
                                        }
                                    ]
                                },
                                {
                                    xtype: 'Hidden',
                                    xns: Roo.form,
                                    name : 'id'
                                }
                            ]
                        }
                    ]
                }
            ],
            center : {
                xtype: 'LayoutRegion',
                xns: Roo,
                alwaysShowTabs : false,
                autoScroll : false,
                closeOnTab : true,
                hideTabs : true,
                titlebar : false
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
                             
                            
                            _this.dialog.el.mask("Saving");
                            _this.form.doAction("submit");
                        
                        }
                    },
                    text : "Save"
                }
            ]
        });
    }
};
