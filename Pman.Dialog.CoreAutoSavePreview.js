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
                            emptyMsg : "No Events found"
                        },
                        colModel : [
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                dataIndex : 'event_when',
                                header : 'Date',
                                width : 100,
                                renderer : function(v) { return v ? v.dateFormat('d/m/Y H:i') : ''; }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                dataIndex : 'on_table',
                                header : 'Affected',
                                width : 100,
                                renderer : function(v) {                     
                                    return String.format('{0})', v ? v : '');  
                                }
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
