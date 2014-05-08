//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Pman.Dialog.CoreEventsPreview = {

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
            width : 600,
            items : [
                {
                    xtype: 'GridPanel',
                    xns: Roo,
                    listeners : {
                        activate : function() {
                            _this.wpanel = this;
                            if (_this.wgrid) {
                                _this.wgrid.footer.onClick('first');
                            }
                        }
                    },
                    background : true,
                    fitContainer : true,
                    fitToframe : true,
                    region : 'west',
                    tableName : 'clipping_journalist',
                    title : "Publication Lists",
                    grid : {
                        xtype: 'Grid',
                        xns: Roo.grid,
                        listeners : {
                            render : function() 
                            {
                                _this.wgrid = this; 
                                //_this.dialog = Pman.Dialog.FILL_IN
                                if (_this.wpanel.active) {
                                   this.footer.onClick('first');
                                }
                            }
                        },
                        autoExpandColumn : 'publication',
                        loadMask : true,
                        dataSource : {
                            xtype: 'Store',
                            xns: Roo.data,
                            listeners : {
                                beforeload : function (_self, o)
                                {
                                    o.params = o.params || {};
                                    o.params._list = 1;
                                }
                            },
                            remoteSort : true,
                            sortInfo : { field : 'publication', direction: 'ASC' },
                            proxy : {
                                xtype: 'HttpProxy',
                                xns: Roo.data,
                                method : 'GET',
                                url : baseURL + '/Roo/Groups.php'
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
                                        'name': 'name',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'type',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'leader',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'leader_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'leader_office_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'leader_name',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'leader_phone',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'leader_fax',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'leader_email',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'leader_company_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'leader_role',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'leader_active',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'leader_remarks',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'leader_passwd',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'leader_owner_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'leader_lang',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'leader_no_reset_sent',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'leader_action_type',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'leader_project_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'leader_deleted_by',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'leader_deleted_dt',
                                        'type': 'date'
                                    },
                                    {
                                        'name': 'leader_firstname',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'leader_lastname',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'leader_name_facebook',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'leader_url_blog',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'leader_url_twitter',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'leader_url_linkedin',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'leader_crm_lead_percentage',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'leader_crm_industry_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'leader_crm_updated_action_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'leader_crm_created_action_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'leader_crm_type_id',
                                        'type': 'int'
                                    }
                                ]
                            }
                        },
                        footer : {
                            xtype: 'PagingToolbar',
                            xns: Roo,
                            displayInfo : true,
                            displayMsg : "Displaying Publication{0} - {1} of {2}",
                            emptyMsg : "Nothing found",
                            pageSize : 25
                        },
                        colModel : [
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                dataIndex : 'publication',
                                header : 'Publication Lists',
                                width : 200,
                                renderer : function(v) { return String.format('{0}', v); }
                            }
                        ]
                    }
                },
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
                    background : true,
                    fitContainer : true,
                    fitToframe : true,
                    region : 'center',
                    tableName : 'clipping_journalist',
                    title : "Journalist",
                    grid : {
                        xtype: 'EditorGrid',
                        xns: Roo.grid,
                        listeners : {
                            render : function() 
                            {
                                _this.grid = this; 
                                if (_this.panel.active) {
                                   this.footer.onClick('first');
                                }
                            }
                        },
                        autoExpandColumn : 'name',
                        clicksToEdit : 1,
                        loadMask : true,
                        dataSource : {
                            xtype: 'Store',
                            xns: Roo.data,
                            listeners : {
                                beforeload : function (_self, o)
                                {
                                    o.params = o.params || {};
                                    if (!_this.data.campaign_id) {
                                        Roo.log('Missing campaign id!?');
                                        return false;
                                    }
                                    o.params.campaign_id = _this.data.campaign_id;
                                }
                            },
                            remoteSort : true,
                            sortInfo : { field : 'name', direction: 'ASC' },
                            proxy : {
                                xtype: 'HttpProxy',
                                xns: Roo.data,
                                method : 'GET',
                                url : baseURL + '/Roo/clipping_journalist.php'
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
                                        'name': 'name',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'publication',
                                        'type': 'string'
                                    }
                                ]
                            }
                        },
                        footer : {
                            xtype: 'PagingToolbar',
                            xns: Roo,
                            displayInfo : true,
                            displayMsg : "Displaying Journalist {0} - {1} of {2}",
                            emptyMsg : "Nothing found",
                            pageSize : 25
                        },
                        colModel : [
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                dataIndex : 'name',
                                header : 'Name',
                                width : 100,
                                renderer : function(v,x,r) 
                                { 
                                    return String.format('{0}', v ? v : ''); 
                                }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                dataIndex : 'publication',
                                header : 'Publication',
                                width : 300,
                                renderer : function(v,x,r) 
                                { 
                                    return String.format('{0}', v ? v : ''); 
                                },
                                editor : {
                                    xtype: 'GridEditor',
                                    xns: Roo.grid
                                }
                            }
                        ]
                    }
                }
            ],
            center : {
                xtype: 'LayoutRegion',
                xns: Roo,
                split : true,
                titlebar : false,
                width : 400
            },
            west : {
                xtype: 'LayoutRegion',
                xns: Roo
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
