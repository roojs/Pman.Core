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
            height : 500,
            modal : true,
            resizable : false,
            title : "Modify Recurrent Notifications",
            width : 700,
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
                    fitToFrame : true,
                    region : 'center',
                    tableName : 'core_notify_recur',
                    title : "core_notify_recur",
                    grid : {
                        xtype: 'EditorGrid',
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
                                if (!_this.dialog) return;
                                _this.dialog.show( this.getDataSource().getAt(rowIndex).data, function() {
                                    _this.grid.footer.onClick('first');
                                }); 
                            }
                        },
                        autoExpandColumn : 'freq',
                        clicksToEdit : 1,
                        loadMask : true,
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
                                id : 'id',
                                root : 'data',
                                totalProperty : 'total',
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
                                    listeners : {
                                        click : function()
                                        {
                                            var grid = _this.grid;
                                            var r = grid.getDataSource().reader.newRow({
                                            // defaults..
                                                person_id : _this.data.person_id,
                                                dtstart : new Date(0),
                                                dtend : Date.parseDate('2050-01-01', 'Y-m-d'),
                                                tz : 'Asia/Hong Kong',
                                                onid : _this.data.onid,
                                                ontable : _this.data.ontable,
                                                method : _this.data.method,
                                                last_event_id : 0,
                                                
                                            
                                            });
                                            grid.stopEditing();
                                            grid.getDataSource().insert(0, r); 
                                            grid.startEditing(0, 2); 
                                        
                                        }
                                    },
                                    cls : 'x-btn-text-icon',
                                    text : "Add",
                                    icon : Roo.rootURL + 'images/default/dd/drop-add.gif'
                                },
                                {
                                    xtype: 'Fill',
                                    xns: Roo.Toolbar
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
                                dataIndex : 'dtstart',
                                header : 'From',
                                width : 75,
                                renderer : function(v) { return String.format('{0}', v ? v.format('d/M/Y') : ''); },
                                editor : {
                                    xtype: 'GridEditor',
                                    xns: Roo.grid,
                                    field : {
                                        xtype: 'DateField',
                                        xns: Roo.form
                                    }
                                }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                dataIndex : 'dtend',
                                header : 'Until',
                                width : 75,
                                renderer : function(v) { return String.format('{0}', v ? v.format('d/M/Y') : ''); },
                                editor : {
                                    xtype: 'GridEditor',
                                    xns: Roo.grid,
                                    field : {
                                        xtype: 'DateField',
                                        xns: Roo.form
                                    }
                                }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                dataIndex : 'freq',
                                header : 'Frequency',
                                width : 100,
                                renderer : function(v,x,r) { 
                                
                                    Roo.log(this);
                                    var cm = _this.grid.colModel;
                                    var ix = cm.findColumnIndex('freq');
                                    var ce = cm.getCellEditor(ix)
                                    var matches = ce.field.store.query('code',v);
                                    if (!matches.length) {
                                        return '';
                                    }
                                    return String.format('{0}', matches.first().data.title);
                                 },
                                editor : {
                                    xtype: 'GridEditor',
                                    xns: Roo.grid,
                                    field : {
                                        xtype: 'ComboBox',
                                        xns: Roo.form,
                                        allowBlank : false,
                                        displayField : 'title',
                                        editable : false,
                                        fieldLabel : 'Country',
                                        hiddenName : 'freq',
                                        listWidth : 200,
                                        mode : 'local',
                                        name : 'freq_name',
                                        tpl : '<div class="x-grid-cell-text x-btn button"><b>{title}</b> </div>',
                                        triggerAction : 'all',
                                        valueField : 'code',
                                        width : 200,
                                        store : {
                                            xtype: 'SimpleStore',
                                            xns: Roo.data,
                                            data : [ 
                                                [ 'HOURLY' , 'Hourly at' ] ,
                                                   [ 'DAILY' , 'Daily at'] ,
                                                    [ 'WEEKLY' , 'Weekly at'] ,
                                                     [ 'Montly' , 'Montly at'] 
                                            ],
                                            fields : ['code', 'title'],
                                            sortInfo : { field : 'title', direction: 'ASC' }
                                        }
                                    }
                                }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                dataIndex : 'freq_day',
                                header : 'on day(s)',
                                width : 100,
                                renderer : function(v) { return String.format('{0}', v); },
                                editor : {
                                    xtype: 'GridEditor',
                                    xns: Roo.grid
                                }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                dataIndex : 'freq_hour',
                                header : 'at Hour(s)',
                                width : 100,
                                renderer : function(v) { return String.format('{0}', v); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                dataIndex : 'tz',
                                header : 'Timezone',
                                width : 75,
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
                                dataIndex : 'last_applied_dt',
                                header : 'Message Last sent',
                                width : 75,
                                renderer : function(v) { return String.format('{0}', v ? v.format('d/M/Y') : ''); }
                            }
                        ]
                    }
                }
            ],
            center : {
                xtype: 'LayoutRegion',
                xns: Roo,
                autoScroll : true,
                loadOnce : true
            },
            buttons : [
                {
                    xtype: 'Button',
                    xns: Roo,
                    text : "Done"
                }
            ]
        });
    }
};
