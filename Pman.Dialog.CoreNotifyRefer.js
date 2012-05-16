//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Pman.Dialog.CoreNotifyRefer = {

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
            items : [
                {
                    xtype: 'GridPanel',
                    xns: Roo,
                    title : "core_notify_recur",
                    fitToframe : true,
                    fitContainer : true,
                    tableName : 'core_notify_recur',
                    background : true,
                    region : 'center',
                    listeners : {
                        activate : function() {
                            _this.panel = this;
                            if (_this.grid) {
                                _this.grid.footer.onClick('first');
                            }
                        }
                    },
                    grid : {
                        xtype: 'Grid',
                        xns: Roo.grid,
                        autoExpandColumn : 'freq',
                        loadMask : true,
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
                                if (!_this.dialog) return;
                                _this.dialog.show( this.getDataSource().getAt(rowIndex).data, function() {
                                    _this.grid.footer.onClick('first');
                                }); 
                            }
                        },
                        dataSource : {
                            xtype: 'Store',
                            xns: Roo.data,
                            remoteSort : true,
                            sortInfo : { field : 'freq', direction: 'ASC' },
                            proxy : {
                                xtype: 'HttpProxy',
                                xns: Roo.data,
                                method : 'GET',
                                url : baseURL + '/Roo/core_notify_recur.php'
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
                                        'name': 'person_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'dtstart',
                                        'type': 'date',
                                        'dateFormat': 'Y-m-d'
                                    },
                                    {
                                        'name': 'dtend',
                                        'type': 'date',
                                        'dateFormat': 'Y-m-d'
                                    },
                                    {
                                        'name': 'tz',
                                        'type': 'float'
                                    },
                                    {
                                        'name': 'last_applied_dt',
                                        'type': 'date',
                                        'dateFormat': 'Y-m-d'
                                    },
                                    {
                                        'name': 'freq',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'freq_day',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'freq_hour',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'last_event_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'method',
                                        'type': 'string'
                                    }
                                ]
                            }
                        },
                        footer : {
                            xtype: 'PagingToolbar',
                            xns: Roo,
                            pageSize : 25,
                            displayInfo : true,
                            displayMsg : "Displaying core_notify_recur{0} - {1} of {2}",
                            emptyMsg : "No core_notify_recur found"
                        },
                        toolbar : {
                            xtype: 'Toolbar',
                            xns: Roo,
                            items : [
                                {
                                    xtype: 'Button',
                                    xns: Roo.Toolbar,
                                    text : "Add",
                                    cls : 'x-btn-text-icon',
                                    icon : Roo.rootURL + 'images/default/dd/drop-add.gif',
                                    listeners : {
                                        click : function()
                                        {
                                            if (!_this.dialog) return;
                                            _this.dialog.show( { id : 0 } , function() {
                                                _this.grid.footer.onClick('first');
                                           }); 
                                        }
                                    }
                                },
                                {
                                    xtype: 'Button',
                                    xns: Roo.Toolbar,
                                    text : "Edit",
                                    cls : 'x-btn-text-icon',
                                    icon : Roo.rootURL + 'images/default/tree/leaf.gif',
                                    listeners : {
                                        click : function()
                                        {
                                            var s = _this.grid.getSelectionModel().getSelections();
                                            if (!s.length || (s.length > 1))  {
                                                Roo.MessageBox.alert("Error", s.length ? "Select only one Row" : "Select a Row");
                                                return;
                                            }
                                            if (!_this.dialog) return;
                                            _this.dialog.show(s[0].data, function() {
                                                _this.grid.footer.onClick('first');
                                            }); 
                                            
                                        }
                                    }
                                },
                                {
                                    xtype: 'Button',
                                    xns: Roo.Toolbar,
                                    text : "Delete",
                                    cls : 'x-btn-text-icon',
                                    icon : rootURL + '/Pman/templates/images/trash.gif',
                                    listeners : {
                                        click : function()
                                        {
                                             Pman.genericDelete(_this, 'core_notify_recur'); 
                                        }
                                    }
                                }
                            ]
                        },
                        colModel : [
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Id',
                                width : 75,
                                dataIndex : 'id',
                                renderer : function(v) { return String.format('{0}', v); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Person',
                                width : 75,
                                dataIndex : 'person_id',
                                renderer : function(v) { return String.format('{0}', v); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Dtstart',
                                width : 75,
                                dataIndex : 'dtstart',
                                renderer : function(v) { return String.format('{0}', v ? v.format('d/M/Y') : ''); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Dtend',
                                width : 75,
                                dataIndex : 'dtend',
                                renderer : function(v) { return String.format('{0}', v ? v.format('d/M/Y') : ''); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Tz',
                                width : 75,
                                dataIndex : 'tz',
                                renderer : function(v) { return String.format('{0}', v); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Last applied dt',
                                width : 75,
                                dataIndex : 'last_applied_dt',
                                renderer : function(v) { return String.format('{0}', v ? v.format('d/M/Y') : ''); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Freq',
                                width : 200,
                                dataIndex : 'freq',
                                renderer : function(v) { return String.format('{0}', v); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Freq day',
                                width : 200,
                                dataIndex : 'freq_day',
                                renderer : function(v) { return String.format('{0}', v); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Freq hour',
                                width : 200,
                                dataIndex : 'freq_hour',
                                renderer : function(v) { return String.format('{0}', v); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Last event',
                                width : 75,
                                dataIndex : 'last_event_id',
                                renderer : function(v) { return String.format('{0}', v); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Method',
                                width : 200,
                                dataIndex : 'method',
                                renderer : function(v) { return String.format('{0}', v); }
                            }
                        ]
                    }
                }
            ]
        });
    }
};
