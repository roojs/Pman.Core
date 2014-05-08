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
                    tableName : 'Images',
                    title : "Images",
                    grid : {
                        xtype: 'Grid',
                        xns: Roo.grid,
                        listeners : {
                            render : function() 
                            {
                                _this.grid = this; 
                                //_this.dialog = Pman.Dialog.FILL_IN
                                if (_this.panel.active) {
                                   this.footer.onClick('first');
                                }
                            },
                            rowdblclick : function (_self, rowIndex, e)
                            {
                            
                            }
                        },
                        autoExpandColumn : 'filename',
                        loadMask : true,
                        dataSource : {
                            xtype: 'Store',
                            xns: Roo.data,
                            remoteSort : true,
                            sortInfo : { field : 'filename', direction: 'ASC' },
                            proxy : {
                                xtype: 'HttpProxy',
                                xns: Roo.data,
                                method : 'GET',
                                url : baseURL + '/Roo/Images.php'
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
