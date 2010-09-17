//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Pman.Dialog.CorePersonContact = {

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
            height : 630,
            resizable : false,
            title : "Edit / Create Person",
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
                                actioncomplete : function(_self,action)
                                {
                                    if (action.type == 'setdata') {
                                       //_this.dialog.el.mask("Loading");
                                       //this.load({ method: 'GET', params: { '_id' : _this.data.id }});
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
                                            _this.callback.call(_this, _this.form.getValues());
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
                            method : 'POST',
                            style : 'margin:10px;',
                            url : baseURL + '/Roo/Person.php',
                            items : [
                                {
                                    xtype: 'ComboBox',
                                    xns: Roo.form,
                                    allowBlank : 'false',
                                    editable : 'false',
                                    emptyText : "Select Companies",
                                    forceSelection : true,
                                    listWidth : 400,
                                    loadingText : "Searching...",
                                    minChars : 2,
                                    pageSize : 20,
                                    qtip : "Select Companies",
                                    selectOnFocus : true,
                                    triggerAction : 'all',
                                    typeAhead : true,
                                    width : 300,
                                    tpl : '<div class="x-grid-cell-text x-btn button"><b>{code}</b> </div>',
                                    queryParam : 'query[code]',
                                    fieldLabel : 'Company',
                                    valueField : 'id',
                                    displayField : 'code',
                                    hiddenName : 'company_id',
                                    name : 'company_id_code',
                                    store : {
                                        xtype: 'Store',
                                        xns: Roo.data,
                                        remoteSort : true,
                                        sortInfo : { direction : 'ASC', field: 'id' },
                                        listeners : {
                                            beforeload : function (_self, o){
                                                o.params = o.params || {};
                                                // set more here
                                            }
                                        },
                                        proxy : {
                                            xtype: 'HttpProxy',
                                            xns: Roo.data,
                                            method : 'GET',
                                            url : baseURL + '/Roo/Companies.php'
                                        },
                                        reader : {
                                            xtype: 'JsonReader',
                                            xns: Roo.data,
                                            id : 'id',
                                            root : 'data',
                                            totalProperty : 'total',
                                            fields : [{"name":"id","type":"int"},{"name":"code","type":"string"}]
                                        }
                                    }
                                },
                                {
                                    xtype: 'ComboBox',
                                    xns: Roo.form,
                                    allowBlank : 'false',
                                    editable : 'false',
                                    emptyText : "Select Office",
                                    forceSelection : true,
                                    listWidth : 400,
                                    loadingText : "Searching...",
                                    minChars : 2,
                                    pageSize : 20,
                                    qtip : "Select Office",
                                    selectOnFocus : true,
                                    triggerAction : 'all',
                                    typeAhead : true,
                                    width : 300,
                                    tpl : '<div class="x-grid-cell-text x-btn button"><b>{name}</b> </div>',
                                    queryParam : 'query[name]',
                                    fieldLabel : 'Office',
                                    valueField : 'id',
                                    displayField : 'name',
                                    hiddenName : 'office_id',
                                    name : 'office_id_name',
                                    store : {
                                        xtype: 'Store',
                                        xns: Roo.data,
                                        remoteSort : true,
                                        sortInfo : { direction : 'ASC', field: 'id' },
                                        listeners : {
                                            beforeload : function (_self, o){
                                                o.params = o.params || {};
                                                // set more here
                                            }
                                        },
                                        proxy : {
                                            xtype: 'HttpProxy',
                                            xns: Roo.data,
                                            method : 'GET',
                                            url : baseURL + '/Roo/Office.php'
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
                                    fieldLabel : 'Name',
                                    name : 'name',
                                    width : 300
                                },
                                {
                                    xtype: 'TextField',
                                    xns: Roo.form,
                                    fieldLabel : 'Role',
                                    name : 'role',
                                    width : 200
                                },
                                {
                                    xtype: 'TextField',
                                    xns: Roo.form,
                                    fieldLabel : 'Phone',
                                    name : 'phone',
                                    width : 200
                                },
                                {
                                    xtype: 'TextField',
                                    xns: Roo.form,
                                    fieldLabel : 'Fax',
                                    name : 'fax',
                                    width : 200
                                },
                                {
                                    xtype: 'TextField',
                                    xns: Roo.form,
                                    fieldLabel : 'Email',
                                    name : 'email',
                                    width : 200
                                },
                                {
                                    xtype: 'ComboBox',
                                    xns: Roo.form,
                                    allowBlank : 'false',
                                    editable : 'false',
                                    emptyText : "Select Projects",
                                    forceSelection : true,
                                    listWidth : 400,
                                    loadingText : "Searching...",
                                    minChars : 2,
                                    pageSize : 20,
                                    qtip : "Select Projects",
                                    selectOnFocus : true,
                                    triggerAction : 'all',
                                    typeAhead : true,
                                    width : 300,
                                    tpl : '<div class="x-grid-cell-text x-btn button"><b>{name}</b> </div>',
                                    queryParam : 'query[name]',
                                    fieldLabel : 'Project',
                                    valueField : 'id',
                                    displayField : 'name',
                                    hiddenName : 'project_id',
                                    name : 'project_id_name',
                                    store : {
                                        xtype: 'Store',
                                        xns: Roo.data,
                                        remoteSort : true,
                                        sortInfo : { direction : 'ASC', field: 'id' },
                                        listeners : {
                                            beforeload : function (_self, o){
                                                o.params = o.params || {};
                                                // set more here
                                            }
                                        },
                                        proxy : {
                                            xtype: 'HttpProxy',
                                            xns: Roo.data,
                                            method : 'GET',
                                            url : baseURL + '/Roo/Projects.php'
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
                                    fieldLabel : 'Deleted by',
                                    name : 'deleted_by',
                                    width : 75
                                },
                                {
                                    xtype: 'DateField',
                                    xns: Roo.form,
                                    fieldLabel : 'Deleted dt',
                                    name : 'deleted_dt',
                                    width : 75
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
