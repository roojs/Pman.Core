//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Pman.Tab.CoreOAuthClient = new Roo.XComponent({
    part     :  ["Core","OAuthClient"],
    order    : '001-Pman.Tab.CoreOAuthClient',
    region   : 'center',
    parent   : false,
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
            title : "Email Template",
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
                                beforeedit : function (e)
                                {
                                    var r = e.record.data.poitem_qty_received * 1;
                                    
                                    if(r > 0){
                                        Roo.MessageBox.alert("Error", "This item has been receipted");
                                        return false;
                                    }
                                    
                                    var status = _this.form.findField('pohead_status').getValue();
                                    
                                    if(status == 'C'){
                                        Roo.MessageBox.alert("Error", "This PO has been closed");
                                        return false;
                                    }
                                    
                                    
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
                                                var status = _this.form.findField('pohead_status').getValue();
                                                
                                                if(status == 'C'){
                                                    Roo.MessageBox.alert("Error", "This PO has been closed");
                                                    return;
                                                }
                                                
                                                var ct  =    _this.grid.ds.getCount();
                                                
                                                var last = ct ? _this.grid.ds.getAt(ct-1).data.poitem_linenumber * 1 + 1 : 1;
                                                
                                                var dt = _this.form.findField('pohead_orderdate').getValue();
                                                
                                                var nr = _this.grid.ds.reader.newRow({
                                                    poitem_id : 0,
                                                    poitem_linenumber : last,
                                                    item_number : '',
                                                    item_descrip1 : '',
                                                    poitem_duedate : dt,
                                                    poitem_qty_ordered : 1,
                                                    poitem_unitprice : 0
                                                });
                                                
                                                _this.grid.stopEditing();
                                                _this.grid.ds.insert(_this.grid.ds.getCount(), nr); 
                                                _this.grid.startEditing(_this.grid.ds.getCount()-1, 1);
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
                                                var status = _this.form.findField('pohead_status').getValue();
                                                
                                                if(status == 'C'){
                                                    Roo.MessageBox.alert("Error", "This PO has been closed");
                                                    return;
                                                }
                                                
                                                var cs = _this.grid.getSelectionModel().getSelectedCell();
                                                if (!cs) {
                                                    Roo.MessageBox.alert("Error", "Select a cell");
                                                    return;
                                                }
                                                _this.grid.stopEditing();
                                                var r = _this.grid.ds.getAt(cs[0]);
                                                
                                                if(r.data.poitem_qty_received * 1 > 0){
                                                    Roo.MessageBox.alert("Error", "This item has been receipted");
                                                    return;
                                                }
                                                
                                                
                                                _this.grid.ds.remove(r);
                                               
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
                                            xns: Roo.form
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
                                            xns: Roo.form
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
