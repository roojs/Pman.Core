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
                            Roo.log(2);
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
                                Roo.log('1');
                                   this.footer.onClick('first');
                                }
                            }
                        },
                        autoExpandColumn : 'event_when',
                        loadMask : true,
                        sm : {
                            xtype: 'RowSelectionModel',
                            xns: Roo.grid,
                            listeners : {
                                afterselectionchange : function (_self)
                                {
                                    
                                    if (!this.getSelected()) {
                                        _this.viewPanel.setContent("Nothing Selected");
                                        return;
                                    }
                                    
                                    _this.viewPanel.setContent("data");
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
                                    Roo.log(_this.data);
                                    o.params = o.parmas || {};
                                    o.params.action = 'AUTOSAVE';
                                    
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
                            displayInfo : true,
                            pageSize : 25
                        },
                        colModel : [
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                dataIndex : 'event_when',
                                header : 'Date',
                                width : 100,
                                renderer : function(v) { return v ? v.dateFormat('d/m/Y H:i') : ''; }
                            }
                        ]
                    }
                },
                {
                    xtype: 'ContentPanel',
                    xns: Roo,
                    listeners : {
                        render : function (_self)
                        {
                            _this.viewPanel = _self;
                        }
                    },
                    background : false,
                    fitContainer : true,
                    fitToFrame : true,
                    region : 'center'
                }
            ],
            center : {
                xtype: 'LayoutRegion',
                xns: Roo
            },
            west : {
                xtype: 'LayoutRegion',
                xns: Roo,
                split : true,
                width : 200
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
