//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Pman.Dialog.CoreProject = {

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
            height : 450,
            resizable : false,
            title : "Edit / Create Projects",
            width : 470,
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
                            url : baseURL + '/Roo/Projects.php',
                            items : [
                                {
                                    xtype: 'TextField',
                                    xns: Roo.form,
                                    fieldLabel : 'Code',
                                    name : 'code',
                                    width : 150
                                },
                                {
                                    xtype: 'TextField',
                                    xns: Roo.form,
                                    fieldLabel : 'Project Name',
                                    name : 'name',
                                    qtip : "Enter Project Name",
                                    width : 300
                                },
                                {
                                    xtype: 'ComboBox',
                                    xns: Roo.form,
                                    allowBlank : false,
                                    displayField : 'desc',
                                    editable : false,
                                    emptyText : "Select Project Type",
                                    fieldLabel : 'Project Type',
                                    forceSelection : true,
                                    hiddenName : 'type',
                                    listWidth : 400,
                                    loadingText : "Searching...",
                                    minChars : 2,
                                    name : 'type_desc',
                                    pageSize : 20,
                                    qtip : "Select Project Type",
                                    queryParam : 'query[name]',
                                    selectOnFocus : true,
                                    tpl : '<div class="x-grid-cell-text x-btn button"><b>{desc}</b> </div>',
                                    triggerAction : 'all',
                                    typeAhead : true,
                                    valueField : 'code',
                                    width : 200,
                                    store : {
                                        xtype: 'SimpleStore',
                                        xns: Roo.data,
                                        data : [ [  'U' , "Project (Unconfirmed)" ],
                                        [  'P' , "Project" ],
                                        [  'C' , "Project (Closed)" ],
                                        [  'N' , "Non-Project" ],
                                        [  'X' , "Non-Project (Closed)" ]
                                        ],
                                        fields : [ 'code', 'desc' ]
                                    }
                                },
                                {
                                    xtype: 'ComboBox',
                                    xns: Roo.form,
                                    listeners : {
                                        add : function (combo)
                                        {
                                         Pman.Dialog.Companies.show( {  id: 0 },  function(data) {
                                                    _this.form.setValues({ 
                                                        client_id : data.id,
                                                        client_id_name : data.name
                                                    });
                                                }); 
                                        }
                                    },
                                    allowBlank : 'false',
                                    displayField : 'name',
                                    editable : 'false',
                                    emptyText : "Select Company",
                                    fieldLabel : 'Client',
                                    forceSelection : true,
                                    hiddenName : 'client_id',
                                    listWidth : 400,
                                    loadingText : "Searching...",
                                    minChars : 2,
                                    name : 'client_id_name',
                                    pageSize : 20,
                                    qtip : "Select Companies",
                                    queryParam : 'query[name]',
                                    selectOnFocus : true,
                                    tpl : '<div class="x-grid-cell-text x-btn button"><b>{name}</b> </div>',
                                    triggerAction : 'all',
                                    typeAhead : true,
                                    valueField : 'id',
                                    width : 300,
                                    store : {
                                        xtype: 'Store',
                                        xns: Roo.data,
                                        listeners : {
                                            beforeload : function (_self, o){
                                                o.params = o.params || {};
                                               
                                                o.params.type = 1;
                                                o.params['query[group_pulldown]'] = 1;
                                            }
                                        },
                                        remoteSort : true,
                                        sortInfo : { direction : 'ASC', field: 'name' },
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
                                    displayField : 'name',
                                    editable : 'false',
                                    emptyText : "Select Team",
                                    fieldLabel : 'Team',
                                    forceSelection : true,
                                    hiddenName : 'team_id',
                                    listWidth : 400,
                                    loadingText : "Searching...",
                                    minChars : 2,
                                    name : 'team_id_name',
                                    pageSize : 20,
                                    qtip : "Select Team",
                                    queryParam : 'query[name]',
                                    selectOnFocus : true,
                                    tpl : '<div class="x-grid-cell-text x-btn button"><b>{name}</b> </div>',
                                    triggerAction : 'all',
                                    typeAhead : true,
                                    valueField : 'id',
                                    width : 300,
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
                                            url : baseURL + '/Roo/Groups.php'
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
                                    fieldLabel : 'File location',
                                    name : 'file_location',
                                    width : 300
                                },
                                {
                                    xtype: 'TextArea',
                                    xns: Roo.form,
                                    fieldLabel : 'Remarks',
                                    height : 100,
                                    name : 'remarks',
                                    width : 300
                                },
                                {
                                    xtype: 'FieldSet',
                                    xns: Roo.form,
                                    legend : "Opened",
                                    items : [
                                        {
                                            xtype: 'DateField',
                                            xns: Roo.form,
                                            altFormats : 'Y-m-d|d/m/Y',
                                            fieldLabel : 'Open date',
                                            format : 'd/m/Y',
                                            name : 'open_date',
                                            width : 100
                                        },
                                        {
                                            xtype: 'ComboBox',
                                            xns: Roo.form,
                                            allowBlank : 'false',
                                            editable : 'false',
                                            emptyText : "Select Person",
                                            forceSelection : true,
                                            listWidth : 400,
                                            loadingText : "Searching...",
                                            minChars : 2,
                                            pageSize : 20,
                                            qtip : "Select Person",
                                            selectOnFocus : true,
                                            triggerAction : 'all',
                                            typeAhead : true,
                                            width : 300,
                                            tpl : '<div class="x-grid-cell-text x-btn button"><b>{name}</b> </div>',
                                            queryParam : 'query[name]',
                                            fieldLabel : 'Open by',
                                            valueField : 'id',
                                            displayField : 'name',
                                            hiddenName : 'open_by',
                                            name : 'open_by_name',
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
                                                    url : baseURL + '/Roo/Person.php'
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
