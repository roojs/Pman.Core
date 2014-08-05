//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Pman.Tab.CoreOAuthClient = new Roo.XComponent({
    part     :  ["Core","OAuthClient"],
    order    : '900-Pman.Tab.CoreOAuthClient',
    region   : 'center',
    parent   : 'Pman.Tab.Admin',
    name     : "unnamed module",
    disabled : false, 
    permname : '', 
    _tree : function()
    {
        var _this = this;
        var MODULE = this;
        return {
            xtype: 'NestedLayoutPanel',
            xns: Roo,
            region : 'center',
            title : "Oauth2 Clients",
            layout : {
                xtype: 'BorderLayout',
                xns: Roo,
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
                        background : true,
                        fitContainer : true,
                        fitToframe : true,
                        region : 'center',
                        tableName : 'core_oauth_clients',
                        title : "Oauth2 Clients",
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
                                },
                                afteredit : function (e)
                                {
                                    if(e.originalValue == e.value || !e.value.length){
                                        return false;
                                    }
                                    
                                    Roo.log('commit it');
                                    e.record.commit();
                                }
                            },
                            autoExpandColumn : 'redirect_uri',
                            clicksToEdit : 1,
                            loadMask : true,
                            dataSource : {
                                xtype: 'Store',
                                xns: Roo.data,
                                listeners : {
                                    beforeload : function (_self, o){
                                        o.params = o.params || {};
                                    
                                    },
                                    update : function (_self, record, operation)
                                    {
                                        if (operation != Roo.data.Record.COMMIT) {
                                            return;
                                        }
                                    
                                        if (!record.data.client_id.length || !record.data.client_secret.length) {
                                            return;
                                        }
                                        
                                        new Pman.Request({
                                            url : baseURL + '/Roo/Core_oauth_clients',
                                            method : 'POST',
                                            params : {
                                                id : record.data.id,
                                                client_id : record.data.client_id,
                                                client_secret : record.data.client_secret,
                                                redirect_uri : record.data.redirect_uri
                                            },
                                            success : function(res) {
                                                _this.grid.footer.onClick('refresh');
                                            }
                                        });
                                        
                                    }
                                },
                                remoteSort : true,
                                sortInfo : { field : 'client_id', direction: 'ASC' },
                                proxy : {
                                    xtype: 'HttpProxy',
                                    xns: Roo.data,
                                    method : 'GET',
                                    url : baseURL + '/Roo/Core_oauth_clients'
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
                                            'name': 'client_id',
                                            'type': 'string'
                                        },
                                        {
                                            'name': 'client_secret',
                                            'type': 'string'
                                        },
                                        {
                                            'name': 'redirect_uri',
                                            'type': 'string'
                                        }
                                    ]
                                }
                            },
                            toolbar : {
                                xtype: 'Toolbar',
                                xns: Roo,
                                items : [
                                    {
                                        xtype: 'Button',
                                        xns: Roo.Toolbar,
                                        listeners : {
                                            click : function()
                                            {
                                                
                                                var nr = _this.grid.ds.reader.newRow({
                                                    id : 0,
                                                    client_id : '',
                                                    client_secret : '',
                                                    redirect_uri : ''
                                                });
                                                
                                                _this.grid.stopEditing();
                                                _this.grid.ds.insert(_this.grid.ds.getCount(), nr); 
                                                _this.grid.startEditing(_this.grid.ds.getCount()-1, 0);
                                            }
                                        },
                                        cls : 'x-btn-text-icon',
                                        text : "Add",
                                        icon : Roo.rootURL + 'images/default/dd/drop-add.gif'
                                    },
                                    {
                                        xtype: 'Button',
                                        xns: Roo.Toolbar,
                                        listeners : {
                                            click : function ()
                                            {   
                                                var cs = _this.grid.getSelectionModel().getSelectedCell();
                                                if (!cs) {
                                                    Roo.MessageBox.alert("Error", "Select a cell");
                                                    return;
                                                }
                                                _this.grid.stopEditing();
                                             
                                                var r = _this.grid.ds.getAt(cs[0]);
                                                
                                                Roo.MessageBox.confirm("Confirm", "Are you sure you want to delete this client?", function (v){
                                                    if (v != 'yes') {
                                                        return;
                                                    }
                                                    
                                                    new Pman.Request({
                                                        url : baseURL + '/Roo/Core_oauth_clients',
                                                        method : 'POST',
                                                        params : {
                                                            _delete : r.data.id
                                                        },
                                                        success : function(res) {
                                                            _this.grid.footer.onClick('refresh');
                                                        }
                                                    });
                                                });
                                            }
                                        },
                                        cls : 'x-btn-text-icon',
                                        text : "Remove",
                                        icon : rootURL + '/Pman/templates/images/trash.gif'
                                    }
                                ]
                            },
                            footer : {
                                xtype: 'PagingToolbar',
                                xns: Roo,
                                displayInfo : true,
                                emptyMsg : "No Clients found",
                                pageSize : 25
                            },
                            colModel : [
                                {
                                    xtype: 'ColumnModel',
                                    xns: Roo.grid,
                                    dataIndex : 'client_id',
                                    header : 'Client ID',
                                    width : 150,
                                    renderer : function(v) { 
                                        return String.format('{0}', v ? v : '');
                                    },
                                    editor : {
                                        xtype: 'GridEditor',
                                        xns: Roo.grid,
                                        field : {
                                            xtype: 'TextField',
                                            xns: Roo.form,
                                            allowBlank : false
                                        }
                                    }
                                },
                                {
                                    xtype: 'ColumnModel',
                                    xns: Roo.grid,
                                    dataIndex : 'client_secret',
                                    header : 'Client Secret',
                                    width : 150,
                                    renderer : function(v) { 
                                        return String.format('{0}', v ? v : '');
                                    },
                                    editor : {
                                        xtype: 'GridEditor',
                                        xns: Roo.grid,
                                        field : {
                                            xtype: 'TextField',
                                            xns: Roo.form,
                                            allowBlank : false
                                        }
                                    }
                                },
                                {
                                    xtype: 'ColumnModel',
                                    xns: Roo.grid,
                                    dataIndex : 'redirect_uri',
                                    header : 'Redirect URI',
                                    width : 150,
                                    renderer : function(v) { 
                                        return String.format('{0}', v ? v : '');
                                    },
                                    editor : {
                                        xtype: 'GridEditor',
                                        xns: Roo.grid,
                                        field : {
                                            xtype: 'TextField',
                                            xns: Roo.form
                                        }
                                    }
                                }
                            ]
                        }
                    }
                ],
                center : {
                    xtype: 'LayoutRegion',
                    xns: Roo,
                    autoScroll : false,
                    split : true
                }
            }
        };
    }
});
