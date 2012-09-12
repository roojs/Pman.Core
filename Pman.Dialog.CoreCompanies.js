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
            title : "Edit Companies",
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
                                },
                                rendered : function (form)
                                {
                                    _this.form = form;
                                }
                            },
                            fileUpload : true,
                            labelWidth : 160,
                            items : [
                                {
                                    xtype: 'Column',
                                    xns: Roo.form,
                                    width : 500,
                                    items : [
                                        {
                                            xtype: 'TextField',
                                            xns: Roo.form,
                                            allowBlank : false,
                                            fieldLabel : 'Company ID (for filing Ref.)',
                                            name : 'code',
                                            qtip : "Enter code",
                                            width : 100
                                        },
                                        {
                                            xtype: 'ComboBox',
                                            xns: Roo.form,
                                            allowBlank : false,
                                            displayField : 'desc',
                                            editable : false,
                                            emptyText : "Select Type",
                                            fieldLabel : 'Type',
                                            hiddenName : 'comptype',
                                            listWidth : 250,
                                            name : 'comptype_name',
                                            qtip : "Select Company type",
                                            selectOnFocus : true,
                                            triggerAction : 'all',
                                            typeAhead : false,
                                            valueField : 'val',
                                            width : 200,
                                            store : {
                                                xtype: 'SimpleStore',
                                                xns: Roo.data,
                                                data : '[ \'CONSULTANT\', "Consultant" ],[ \'CLIENT\'    ,  "Client" ],[ \'CONTRACTOR\' , "Contractor" ]',
                                                fields : '[\'val\', \'desc\']'
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
                                            height : 40,
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
                                            xtype: 'TextField',
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
                                            width : 170
                                        }
                                    ]
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
                    text : "Cancel"
                },
                {
                    xtype: 'Button',
                    xns: Roo,
                    text : "Save"
                }
            ]
        });
    }
};
