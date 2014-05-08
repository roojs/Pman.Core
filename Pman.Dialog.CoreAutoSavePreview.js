//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Pman.Dialog.CoreAutoSavePreview = {

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
            background : false,
            closable : false,
            collapsible : false,
            height : 500,
            modal : true,
            resizable : false,
            title : "Saved Version",
            width : 800,
            items : [
                {
                    xtype: 'GridPanel',
                    xns: Roo,
                    listeners : {
                        activate : function() {
                            _this.panel = this;
                            if (_this.grid) {
                                _this.grid.footer.onClick('first');
                            }
                        }
                    },
                    background : false,
                    fitContainer : true,
                    fitToframe : true,
                    region : 'west',
                    tableName : 'Events',
                    title : "Events",
                    grid : {
                        xtype: 'Grid',
                        xns: Roo.grid,
                        listeners : {
                            render : function() { 
                                _this.grid = this; 
                                //_this.dialog = Pman.Dialog.FILL_IN
                                if (_this.panel.active) {
                                   this.footer.onClick('first');
                                }
                            }
                        },
                        autoExpandColumn : 'remarks',
                        loadMask : true,
                        sm : {
                            xtype: 'RowSelectionModel',
                            xns: Roo.grid,
                            listeners : {
                                afterselectionchange : function (_self)
                                {
                                    // load detail log in _this.viewPanel;
                                    if (!this.getSelected()) {
                                        this.viewPanel.setContent("Nothing Selected");
                                        return;
                                    }
                                    var id = this.getSelected().data.id;
                                    _this.viewPanel.load( { url : baseURL + "/Admin/EventView/" + id + ".html" });
                                    
                                }
                            },
                            singleSelect : true
                        },
                        dataSource : {
                            xtype: 'Store',
                            xns: Roo.data,
                            listeners : {
                                beforeload : function (_self, o)
                                {
                                    if (!Pman.buildCompleted) {
                                        return false;
                                    }
                                   
                                   
                                   if (! _this.personSel) {
                                    return false;
                                    }
                                    o.params = o.params || {};
                                    o.params.person_id = _this.personSel.getValue();
                                    var act = _this.actionSel.getValue();
                                    if (act.length) {
                                        o.params.action = act;
                                    }
                                    var tbl = _this.affectSel.getValue();
                                    if (tbl.length) {
                                        o.params.on_table = tbl;
                                    }
                                    act = _this.dateFrom.getValue();
                                    if (act.format) {
                                        o.params['query[from]'] = act.format('Y-m-d');
                                    }
                                    act = _this.dateTo.getValue();
                                    if (act.format) {
                                        o.params['query[to]'] = act.format('Y-m-d');
                                    }
                                 
                                    
                                    /*
                                    act = _this.groupedCombo.getValue();
                                    o.params['query[grouped]'] = act;
                                    if (o.params['query[grouped]'] == 'gr') {
                                    
                                        if (!tbl.length) {
                                            Roo.MessageBox.alert("Error", "Select a table to group results on");
                                            return false;
                                        }
                                //        o.params['_columns']  = 
                                        o.params['_distinct'] = 'on_id';
                                        
                                        
                                    }
                                    
                                    */
                                
                                    
                                }
                            },
                            remoteSort : true,
                            sortInfo : { field: 'event_when', direction: 'DESC'},
                            reader : {
                                xtype: 'JsonReader',
                                xns: Roo.data,
                                totalProperty : 'total',
                                root : 'data',
                                id : 'id',
                                fields : [
                                    {
                                        'name': 'id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'person_name',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'event_when',
                                        'type': 'date',
                                        'dateFormat': 'Y-m-d'
                                    },
                                    {
                                        'name': 'action',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'ipaddr',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'on_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'on_table',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'person_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'remarks',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'person_id_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'person_id_office_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'person_id_name',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'person_id_phone',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'person_id_fax',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'person_id_email',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'person_id_company_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'person_id_role',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'person_id_active',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'person_id_remarks',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'person_id_passwd',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'person_id_owner_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'person_id_lang',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'person_id_no_reset_sent',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'person_id_action_type',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'person_id_project_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'person_id_deleted_by',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'person_id_deleted_dt',
                                        'type': 'date'
                                    }
                                ]
                            },
                            proxy : {
                                xtype: 'HttpProxy',
                                xns: Roo.data,
                                method : 'GET',
                                url : baseURL + '/Roo/Events.php'
                            }
                        },
                        footer : {
                            xtype: 'PagingToolbar',
                            xns: Roo,
                            pageSize : 25,
                            displayInfo : true,
                            displayMsg : "Displaying Events  {0} - {1} of {2}",
                            emptyMsg : "No Events found",
                            items : [
                                {
                                    xtype: 'Button',
                                    xns: Roo.Toolbar,
                                    listeners : {
                                        click : function (_self, e)
                                        {
                                            
                                            var params = {
                                                'sort' : 'event_when',
                                                'dir' : 'DESC',
                                                'start' : 0,
                                                'limit' : 900,
                                                person_id : _this.personSel.getValue(), 
                                                'csvTitles[0]' : 'When',   'csvCols[0]' : 'event_when', 
                                                'csvTitles[1]' : 'Staff',   'csvCols[1]' : 'person_id_name', 
                                                'csvTitles[2]' : 'Action',   'csvCols[2]' : 'action', 
                                                'csvTitles[3]' : 'Record ID',   'csvCols[3]' : 'on_id'       ,
                                                'csvTitles[4]' : 'Record Type',   'csvCols[4]' : 'on_table',
                                                'csvTitles[5]' : 'Remarks',   'csvCols[5]' : 'remarks'        
                                            }
                                                
                                            params.person_id = _this.personSel.getValue();
                                            var act = _this.actionSel.getValue();
                                            if (act.length) {
                                                params.action = act;
                                            }
                                            var tbl = _this.affectSel.getValue();
                                            if (tbl.length) {
                                                params.on_table = tbl;
                                            }
                                            act = _this.dateFrom.getValue();
                                            if (act.format) {
                                                params['query[from]'] = act.format('Y-m-d');
                                            }
                                            act = _this.dateTo.getValue();
                                            if (act.format) {
                                                params['query[to]'] = act.format('Y-m-d');
                                            }
                                            params.limit = 9999;
                                            
                                        
                                            new Pman.download({
                                                url : baseURL + '/Roo/Events.php',
                                                params : params ,
                                                newWindow: true
                                                
                                            });
                                        }
                                    },
                                    text : "Download to Excel"
                                }
                            ]
                        },
                        toolbar : {
                            xtype: 'Toolbar',
                            xns: Roo,
                            items : [
                                {
                                    xtype: 'TextItem',
                                    xns: Roo.Toolbar,
                                    text : "Show"
                                },
                                {
                                    xtype: 'ComboBox',
                                    xns: Roo.form,
                                    listeners : {
                                        select : function (combo, record, index)
                                        {
                                          _this.grid.footer.onClick('first');
                                          
                                        },
                                        render : function (_self)
                                        {
                                          _this.personSel = _self;
                                        }
                                    },
                                    allowBlank : true,
                                    displayField : 'name',
                                    editable : true,
                                    emptyText : "Select Person ",
                                    fieldLabel : 'Person ',
                                    forceSelection : true,
                                    listWidth : 600,
                                    loadingText : "Searching...",
                                    minChars : 2,
                                    name : 'person_id_name',
                                    pageSize : 20,
                                    qtip : "Select Person ",
                                    queryParam : 'query[name]',
                                    selectOnFocus : true,
                                    tpl : '<div class="x-grid-cell-text x-btn button"><b>{name}</b> ({company_id_name}) &lt;{email}&gt; </div>',
                                    triggerAction : 'all',
                                    typeAhead : true,
                                    valueField : 'id',
                                    width : 150,
                                    store : {
                                        xtype: 'Store',
                                        xns: Roo.data,
                                        listeners : {
                                            beforeload : function (_self, o)
                                            {
                                                o.params = o.params || {};
                                                o.params.company_id = Pman.Login.authUser.company_id;
                                            }
                                        },
                                        remoteSort : true,
                                        sortInfo : { field : 'name' , direction : 'ASC' },
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
                                            fields : [
                                                {
                                                    'name': 'id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'office_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'name',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'phone',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'fax',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'email',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'company_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'role',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'active',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'remarks',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'passwd',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'owner_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'lang',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'no_reset_sent',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'action_type',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'project_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'deleted_by',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'deleted_dt',
                                                    'type': 'date',
                                                    'dateFormat': 'Y-m-d'
                                                },
                                                {
                                                    'name': 'office_id_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'office_id_company_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'office_id_name',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'office_id_address',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'office_id_phone',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'office_id_fax',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'office_id_email',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'office_id_role',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'company_id_code',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'company_id_name',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'company_id_remarks',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'company_id_owner_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'company_id_address',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'company_id_tel',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'company_id_fax',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'company_id_email',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'company_id_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'company_id_isOwner',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'company_id_logo_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'company_id_background_color',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'company_id_comptype',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'company_id_url',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'company_id_main_office_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'company_id_created_by',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'company_id_created_dt',
                                                    'type': 'date'
                                                },
                                                {
                                                    'name': 'company_id_updated_by',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'company_id_updated_dt',
                                                    'type': 'date'
                                                },
                                                {
                                                    'name': 'company_id_passwd',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'company_id_dispatch_port',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'company_id_province',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'company_id_country',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'project_id_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'project_id_name',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'project_id_remarks',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'project_id_owner_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'project_id_code',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'project_id_active',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'project_id_type',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'project_id_client_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'project_id_team_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'project_id_file_location',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'project_id_open_date',
                                                    'type': 'date'
                                                },
                                                {
                                                    'name': 'project_id_open_by',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'project_id_close_date',
                                                    'type': 'date'
                                                },
                                                {
                                                    'name': 'project_id_countries',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'project_id_languages',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'project_id_agency_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'owner_id_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'owner_id_office_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'owner_id_name',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'owner_id_phone',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'owner_id_fax',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'owner_id_email',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'owner_id_company_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'owner_id_role',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'owner_id_active',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'owner_id_remarks',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'owner_id_passwd',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'owner_id_owner_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'owner_id_lang',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'owner_id_no_reset_sent',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'owner_id_action_type',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'owner_id_project_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'owner_id_deleted_by',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'owner_id_deleted_dt',
                                                    'type': 'date'
                                                }
                                            ]
                                        }
                                    }
                                },
                                {
                                    xtype: 'ComboBox',
                                    xns: Roo.form,
                                    listeners : {
                                        select : function (combo, record, index)
                                        {
                                          _this.grid.footer.onClick('first');
                                        },
                                        render : function (_self)
                                        {
                                          _this.actionSel = _self;
                                        }
                                    },
                                    allowBlank : true,
                                    displayField : 'action',
                                    editable : true,
                                    emptyText : "Select Action",
                                    forceSelection : true,
                                    listWidth : 300,
                                    loadingText : "Searching...",
                                    minChars : 2,
                                    name : 'action',
                                    pageSize : 20,
                                    qtip : "Select Action",
                                    queryParam : 'query[action]',
                                    selectOnFocus : true,
                                    tpl : '<div class="x-grid-cell-text x-btn button"><b>{action}</b> </div>',
                                    triggerAction : 'all',
                                    typeAhead : true,
                                    valueField : 'action',
                                    width : 150,
                                    store : {
                                        xtype: 'Store',
                                        xns: Roo.data,
                                        listeners : {
                                            beforeload : function (_self, o)
                                            {
                                                o.params = o.params || {};
                                                // staff can see all logs, other companies can only see their own.
                                                if (Pman.Login.authUser.company_id_comptype != 'OWNER') {
                                                    o.params.company_id = Pman.Login.authUser.company_id;
                                                }
                                                o.params._distinct = 'action';
                                                o.params._columns ='action';
                                            }
                                        },
                                        remoteSort : true,
                                        sortInfo : { field : 'action' , direction : 'ASC' },
                                        proxy : {
                                            xtype: 'HttpProxy',
                                            xns: Roo.data,
                                            method : 'GET',
                                            url : baseURL + '/Roo/Events.php'
                                        },
                                        reader : {
                                            xtype: 'JsonReader',
                                            xns: Roo.data,
                                            id : 'id',
                                            root : 'data',
                                            totalProperty : 'total',
                                            fields : [
                                                {
                                                    'name': 'id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'person_name',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'event_when',
                                                    'type': 'date',
                                                    'dateFormat': 'Y-m-d'
                                                },
                                                {
                                                    'name': 'action',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'ipaddr',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'on_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'on_table',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'person_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'remarks',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'person_id_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'person_id_office_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'person_id_name',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'person_id_phone',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'person_id_fax',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'person_id_email',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'person_id_company_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'person_id_role',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'person_id_active',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'person_id_remarks',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'person_id_passwd',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'person_id_owner_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'person_id_lang',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'person_id_no_reset_sent',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'person_id_action_type',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'person_id_project_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'person_id_deleted_by',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'person_id_deleted_dt',
                                                    'type': 'date'
                                                }
                                            ]
                                        }
                                    }
                                },
                                {
                                    xtype: 'ComboBox',
                                    xns: Roo.form,
                                    listeners : {
                                        select : function (combo, record, index)
                                        {
                                          _this.grid.footer.onClick('first');
                                        },
                                        render : function (_self)
                                        {
                                          _this.affectSel = _self;
                                        }
                                    },
                                    allowBlank : true,
                                    displayField : 'on_table',
                                    editable : true,
                                    emptyText : "Select Affects",
                                    forceSelection : true,
                                    listWidth : 300,
                                    loadingText : "Searching...",
                                    minChars : 2,
                                    name : 'on_table',
                                    pageSize : 20,
                                    qtip : "Select Action",
                                    queryParam : 'query[on_table]',
                                    selectOnFocus : true,
                                    tpl : '<div class="x-grid-cell-text x-btn button"><b>{on_table}</b> </div>',
                                    triggerAction : 'all',
                                    typeAhead : true,
                                    valueField : 'action',
                                    width : 150,
                                    store : {
                                        xtype: 'Store',
                                        xns: Roo.data,
                                        listeners : {
                                            beforeload : function (_self, o)
                                            {
                                                o.params = o.params || {};
                                                // staff can see all logs, other companies can only see their own.
                                                if (Pman.Login.authUser.company_id_comptype != 'OWNER') {
                                                    o.params.company_id = Pman.Login.authUser.company_id;
                                                }
                                                o.params._distinct = 'on_table';
                                                o.params._columns ='on_table';
                                            }
                                        },
                                        remoteSort : true,
                                        sortInfo : { field : 'on_table' , direction : 'ASC' },
                                        proxy : {
                                            xtype: 'HttpProxy',
                                            xns: Roo.data,
                                            method : 'GET',
                                            url : baseURL + '/Roo/Events.php'
                                        },
                                        reader : {
                                            xtype: 'JsonReader',
                                            xns: Roo.data,
                                            totalProperty : 'total',
                                            root : 'data',
                                            id : 'id',
                                            fields : [
                                                {
                                                    'name': 'id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'person_name',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'event_when',
                                                    'type': 'date',
                                                    'dateFormat': 'Y-m-d'
                                                },
                                                {
                                                    'name': 'action',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'ipaddr',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'on_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'on_table',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'person_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'remarks',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'person_id_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'person_id_office_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'person_id_name',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'person_id_phone',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'person_id_fax',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'person_id_email',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'person_id_company_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'person_id_role',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'person_id_active',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'person_id_remarks',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'person_id_passwd',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'person_id_owner_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'person_id_lang',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'person_id_no_reset_sent',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'person_id_action_type',
                                                    'type': 'string'
                                                },
                                                {
                                                    'name': 'person_id_project_id',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'person_id_deleted_by',
                                                    'type': 'int'
                                                },
                                                {
                                                    'name': 'person_id_deleted_dt',
                                                    'type': 'date'
                                                }
                                            ]
                                        }
                                    }
                                },
                                {
                                    xtype: 'TextItem',
                                    xns: Roo.Toolbar,
                                    text : "Date Range"
                                },
                                {
                                    xtype: 'DateField',
                                    xns: Roo.form,
                                    listeners : {
                                        render : function (_self)
                                        {
                                          _this.dateFrom = _self;
                                        },
                                        change : function (_self, newValue, oldValue)
                                        {
                                          _this.grid.footer.onClick('first');
                                        }
                                    },
                                    format : 'Y-m-d'
                                },
                                {
                                    xtype: 'DateField',
                                    xns: Roo.form,
                                    listeners : {
                                        render : function (_self)
                                        {
                                          _this.dateTo = _self;
                                        },
                                        change : function (_self, newValue, oldValue)
                                        {
                                          _this.grid.footer.onClick('first');
                                        }
                                    },
                                    format : 'Y-m-d'
                                }
                            ]
                        },
                        colModel : [
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                dataIndex : 'event_when',
                                header : 'When',
                                width : 100,
                                renderer : function(v) { return v ? v.dateFormat('d/m/Y H:i') : ''; }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                dataIndex : 'id',
                                header : '#id',
                                width : 100
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                dataIndex : 'person_id_name',
                                header : 'Staff',
                                width : 150,
                                renderer : function(v,x,r) { 
                                    
                                    var efield = typeof(_this.email_field) == 'undefined' ? 'person_id_email' : _this.email_field;
                                    var nfield = typeof(_this.name_field) == 'undefined' ? 'person_id_name' : _this.name_field;    
                                    
                                    Roo.log([r, efield, nfield]);
                                    
                                    var email = r.json[efield]; // ? r.data.person_id_email : r.data.person_id_email;
                                    var name  = r.json[nfield]; // ? r.data.person_id_name : r.data.person_id_name;    
                                    return String.format('{0} &lt;<a href="mailto:{1}">{1}</a>&gt;', 
                                        name, email); 
                                
                                
                                }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Action',
                                width : 100,
                                dataIndex : 'action',
                                renderer : function(v) { return String.format('{0}', v); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'IP',
                                width : 100,
                                dataIndex : 'ipaddr',
                                renderer : function(v) { return String.format('{0}', v); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                dataIndex : 'on_table',
                                header : 'Affected',
                                width : 100,
                                renderer : function(v, x, r) {                     
                                            return v ? String.format('{0}({1})', v,  r.data.on_id)  : '';  
                                   }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                dataIndex : 'remarks',
                                header : 'Remarks',
                                width : 300,
                                renderer : function(v) { return String.format('<span qtip="{1}">{0}</span>', v, Roo.util.Format.htmlEncode(v)); }
                            }
                        ]
                    }
                }
            ],
            center : {
                xtype: 'LayoutRegion',
                xns: Roo,
                titlebar : false
            },
            west : {
                xtype: 'LayoutRegion',
                xns: Roo,
                split : true,
                width : 300
            },
            buttons : [
                {
                    xtype: 'Button',
                    xns: Roo,
                    listeners : {
                        click : function() {
                            _this.dialog.hide();
                        }
                    },
                    text : "Cancel"
                },
                {
                    xtype: 'Button',
                    xns: Roo,
                    listeners : {
                        click : function() {
                            _this.dialog.hide();
                        }
                    },
                    text : "OK"
                }
            ]
        });
    }
};
