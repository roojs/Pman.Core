//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Pman.Tab.CoreEmail = new Roo.XComponent({
    part     :  ["Core","Email"],
    order    : '999-Pman.Tab.CoreEmail',
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
                        tableName : 'core_email',
                        title : "core_email",
                        grid : {
                            xtype: 'Grid',
                            xns: Roo.grid,
                            listeners : {
                                render : function() 
                                {
                                    _this.grid = this; 
                                    _this.dialog = Pman.Dialog.CoreEmail;
                                    if (_this.panel.active) {
                                       _this.grid.footer.onClick('first');
                                    }
                                },
                                rowdblclick : function (_self, rowIndex, e)
                                {
                                    if (!_this.dialog) return;
                                    var data = this.getDataSource().getAt(rowIndex).data;
                                    _this.dialog.show( {id : data.id, module : 'core'} , function() {
                                        _this.grid.footer.onClick('first');
                                    }); 
                                },
                                rowclick : function (_self, rowIndex, e)
                                {
                                   // _this.grid.ds.load({});
                                    _this.viewPanel.view.store.load({});
                                }
                            },
                            autoExpandColumn : 'subject',
                            loadMask : true,
                            sm : {
                                xtype: 'RowSelectionModel',
                                xns: Roo.grid,
                                singleSelect : true
                            },
                            dataSource : {
                                xtype: 'Store',
                                xns: Roo.data,
                                listeners : {
                                    beforeload : function (_self, options)
                                    {
                                        options.params = options.params || {};
                                        
                                        var s = _this.searchBox.getValue();
                                        
                                        if(s.length){
                                            options.params['search[nameortitle]'] = s;
                                        }
                                    
                                    }
                                },
                                remoteSort : true,
                                sortInfo : { field : 'id', direction: 'DESC' },
                                proxy : {
                                    xtype: 'HttpProxy',
                                    xns: Roo.data,
                                    method : 'GET',
                                    url : baseURL + '/Roo/Core_email.php'
                                },
                                reader : {
                                    xtype: 'JsonReader',
                                    xns: Roo.data,
                                    id : 'id',
                                    root : 'data',
                                    totalProperty : 'total',
                                    fields : [
                                        {
                                            "name":"name",
                                            "type":"string"
                                        },
                                        {
                                            "name":"subject",
                                            "type":"string"
                                        }
                                    ]
                                }
                            },
                            footer : {
                                xtype: 'PagingToolbar',
                                xns: Roo,
                                displayInfo : true,
                                displayMsg : "Displaying Message{0} - {1} of {2}",
                                emptyMsg : "Nothing found",
                                pageSize : 25
                            },
                            toolbar : {
                                xtype: 'Toolbar',
                                xns: Roo,
                                items : [
                                    {
                                        xtype: 'TextField',
                                        xns: Roo.form,
                                        listeners : {
                                            specialkey : function (_self, e)
                                            {
                                              _this.grid.footer.onClick('first');
                                            },
                                            render : function (_self)
                                            {
                                                _this.searchBox = _self;
                                            }
                                        }
                                    },
                                    {
                                        xtype: 'Button',
                                        xns: Roo.Toolbar,
                                        listeners : {
                                            click : function (_self, e)
                                            {
                                                _this.grid.footer.onClick('first');
                                            }
                                        },
                                        cls : 'x-btn-icon',
                                        icon : rootURL + '/Pman/templates/images/search.gif'
                                    },
                                    {
                                        xtype: 'Button',
                                        xns: Roo.Toolbar,
                                        listeners : {
                                            click : function (_self, e)
                                            {
                                                _this.searchBox.setValue('');
                                                _this.grid.footer.onClick('first');
                                            }
                                        },
                                        cls : 'x-btn-icon',
                                        icon : rootURL + '/Pman/templates/images/edit-clear.gif'
                                    },
                                    {
                                        xtype: 'Fill',
                                        xns: Roo.Toolbar
                                    },
                                    {
                                        xtype: 'Button',
                                        xns: Roo.Toolbar,
                                        listeners : {
                                            click : function()
                                            {
                                              var sel = _this.grid.selModel.getSelected();
                                              if (!sel) {
                                                    Roo.MessageBox.alert("Error", "Select a message to copy");
                                                    return;
                                                }
                                            new Pman.Request({
                                                url : baseURL + '/Roo/Core_email',
                                                method : 'POST',
                                                params : {
                                                    id : sel.data.id,
                                                    _make_copy : 1
                                                },
                                                success : function() {
                                                    _this.grid.footer.onClick('refresh');
                                                }
                                            });
                                              
                                            }
                                        },
                                        cls : 'x-btn-text-icon',
                                        text : "Copy",
                                        icon : Roo.rootURL + 'images/default/dd/drop-add.gif'
                                    },
                                    {
                                        xtype: 'Button',
                                        xns: Roo.Toolbar,
                                        listeners : {
                                            click : function()
                                            {
                                                if (!_this.dialog) return;
                                                _this.dialog.show( { id : 0, module : 'core' } , function() {
                                                    _this.grid.footer.onClick('first');
                                               }); 
                                            }
                                        },
                                        cls : 'x-btn-text-icon',
                                        text : "Add",
                                        icon : Roo.rootURL + 'images/default/dd/drop-add.gif'
                                    },
                                    {
                                        xtype: 'Separator',
                                        xns: Roo.Toolbar
                                    },
                                    {
                                        xtype: 'Button',
                                        xns: Roo.Toolbar,
                                        listeners : {
                                            click : function()
                                            {
                                                Pman.genericDelete(_this, 'core_mailing_list_message');
                                                
                                            }
                                        },
                                        cls : 'x-btn-text-icon',
                                        text : "Delete",
                                        icon : rootURL + '/Pman/templates/images/trash.gif'
                                    }
                                ]
                            },
                            colModel : [
                                {
                                    xtype: 'ColumnModel',
                                    xns: Roo.grid,
                                    dataIndex : 'name',
                                    header : 'Name',
                                    width : 250,
                                    renderer : function(v) { return String.format('{0}', v); }
                                },
                                {
                                    xtype: 'ColumnModel',
                                    xns: Roo.grid,
                                    dataIndex : 'subject',
                                    header : 'Title',
                                    width : 300,
                                    renderer : function(v) { return String.format('{0}', v); }
                                },
                                {
                                    xtype: 'ColumnModel',
                                    xns: Roo.grid,
                                    dataIndex : 'from_name',
                                    header : 'From Name',
                                    width : 400,
                                    renderer : function(v) { return String.format('{0}', v); }
                                },
                                {
                                    xtype: 'ColumnModel',
                                    xns: Roo.grid,
                                    dataIndex : 'from_email',
                                    header : 'From Email',
                                    width : 400,
                                    renderer : function(v) { return String.format('{0}', v); }
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
                        autoScroll : true,
                        background : false,
                        fitContainer : true,
                        fitToFrame : true,
                        region : 'south',
                        title : "View Message",
                        view : {
                            xtype: 'View',
                            xns: Roo,
                            listeners : {
                                preparedata : function (_self, data, i, rec)
                                {
                                   // Roo.log(data);
                                    //Roo.apply(data, rec.json);
                                }
                            },
                            tpl : new Roo.DomTemplate({url : rootURL+'/Pman/Crm/domtemplates/crm_mail.html'}),
                            store : {
                                xtype: 'Store',
                                xns: Roo.data,
                                listeners : {
                                    beforeload : function (_self, options)
                                    {
                                        options.params = options.params || {};
                                        var p = _this.grid.selModel.getSelected();
                                        if (!p || !p.data.id) {
                                            this.removeAll();
                                            return false;
                                        }
                                       
                                        options.params['id'] = p.data.id;
                                     
                                    },
                                    load : function (_self, records, options)
                                    {
                                        var p = _this.grid.selModel.getSelected();
                                      //  Roo.log(p);
                                        if (!p || !p.data.id) {
                                            this.removeAll();
                                            return false;
                                        }
                                      /*  
                                        new Pman.Request({
                                            url : baseURL + '/Roo/crm_action.php',
                                            method : 'GET',
                                            params : {
                                                person_id : p.data.id,
                                                sort : 'action_dt',
                                                dir : 'DESC'
                                            },
                                            success : function(res) {
                                                if(res.success){
                                                    var el = _this.cpanel.el.select('.crm-history-content').first();
                                                    _this.historyTemplate.overwrite(el, res);
                                                    el.select('.crm-history-log').on('click', Pman.Crm.auditToggle);
                                                    //Roo.log(res);
                                                }
                                            },
                                            failure : function(e) {
                                                //Roo.log(e);
                                              _this.grid.ds.load({});
                                            }
                                            
                                        });
                                        */
                                    
                                    //_this.historyTemplate = new Roo.DomTemplate({url : rootURL+'/Pman/Crm/domtemplates/crm_history.html'})
                                    
                                    /*
                                      new pman request ([
                                      
                                      } successs(data)
                                           el = _this.elemmnt.select('.services')
                                           _this.serviceTemplate.overwite(el, data)
                                      */
                                    }
                                },
                                proxy : {
                                    xtype: 'HttpProxy',
                                    xns: Roo.data,
                                    method : 'GET',
                                    url : baseURL+'/Roo/Core_mailing_list_message.php'
                                },
                                reader : {
                                    xtype: 'JsonReader',
                                    xns: Roo.data,
                                    id : 'id',
                                    root : 'data',
                                    totalProperty : 'total'
                                }
                            }
                        }
                    }
                ],
                center : {
                    xtype: 'LayoutRegion',
                    xns: Roo,
                    autoScroll : false,
                    split : true
                },
                south : {
                    xtype: 'LayoutRegion',
                    xns: Roo,
                    autoScroll : false,
                    height : 300,
                    split : true,
                    titlebar : true
                }
            }
        };
    }
});
