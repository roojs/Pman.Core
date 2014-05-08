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
            listeners : {
                show : function (_self)
                {
                    if(typeof(_this.data) != 'undefined'){
                        _this.grid.footer.onClick('first');
                    }
                    
                }
            },
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
                    tableName : 'Images',
                    title : "Images",
                    grid : {
                        xtype: 'Grid',
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
                        autoExpandColumn : 'event_when',
                        loadMask : true,
                        sm : {
                            xtype: 'RowSelectionModel',
                            xns: Roo.grid,
                            listeners : {
                                afterselectionchange : function (_self)
                                {
                                    var selected = this.getSelected();
                                    
                                    _this.viewPanel.load( { url : baseURL + "/Roo/Events", method : 'GET' }, {_id : selected.data.id, _retrieve_source : 1}, function(oElement, bSuccess, oResponse){
                                        
                                        _this.source = '';
                                        
                                        var res = Roo.decode(oResponse.responseText);
                                        
                                        if(!bSuccess || !res.success){
                                            _this.viewPanel.setContent("Load data failed?!");
                                        }
                                        
                                        if(typeof(res.data) === 'string'){
                                            _this.viewPanel.setContent(res.data);
                                            return;
                                        }
                                        
                                        if(!_this.data.successFn){
                                            Roo.MessageBox.alert('Error', 'Please setup the successFn');
                                            return;
                                        }
                                        
                                        _this.source = _this.data.successFn(res);
                                
                                        _this.viewPanel.setContent(_this.source);
                                        
                                    });
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
                                    o.params = o.params || {};
                                    
                                    if(typeof(_this.data) == 'undefined'){
                                        this.removeAll();
                                        return false;
                                    }
                                
                                    var d = Roo.apply({}, _this.data);
                                    delete d.successFn;
                                
                                    Roo.apply(o.params, d);
                                    
                                }
                            },
                            remoteSort : true,
                            sortInfo : { field : 'event_when', direction: 'DESC' },
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
                                        'name': 'event_when',
                                        'type': 'string'
                                    }
                                ]
                            }
                        },
                        footer : {
                            xtype: 'PagingToolbar',
                            xns: Roo,
                            displayInfo : false,
                            pageSize : 25
                        },
                        colModel : [
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                dataIndex : 'event_when',
                                header : 'Date',
                                width : 100,
                                renderer : function(v) { return String.format('{0}', v ? v.format('Y-m-d H:i:s') : ''); }
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
                            
                            if (_this.callback) {
                                _this.callback.call(this, _this.source);
                            }
                        }
                    },
                    text : "OK"
                }
            ]
        });
    }
};
