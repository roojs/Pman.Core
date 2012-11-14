//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Pman.Dialog.CoreNotifyRecur = {

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
                    _this.grid.ds.load({});
                }
            },
            height : 550,
            modal : true,
            resizable : false,
            title : "Modify Recurrent Notifications",
            width : 800,
            items : [
                {
                    xtype: 'GridPanel',
                    xns: Roo,
                    listeners : {
                        activate : function() {
                         _this.panel = this;
                            if (_this.grid) {
                        //        _this.grid.footer.onClick('first');
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
                                //   this.footer.onClick('first');
                                }
                            },
                            rowdblclick : function (_self, rowIndex, e)
                            {
                                if (!_this.dialog) return;
                                _this.dialog.show( this.getDataSource().getAt(rowIndex).data, function() {
                                    _this.grid.footer.onClick('first');
                                }); 
                            },
                            afteredit : function (e)
                            {
                               e.record.commit();
                            }
                        },
                        autoExpandColumn : 'freq_day',
                        clicksToEdit : 1,
                        loadMask : true,
                        dataSource : {
                            xtype: 'Store',
                            xns: Roo.data,
                            listeners : {
                                update : function (_self, record, operation)
                                {
                                    //Roo.log(operation);
                                    if (operation != 'commit') {
                                        return;
                                    }
                                    var p = Roo.apply({}, record.data);
                                    p.dtstart = record.data.dtstart.format('Y-m-d');
                                    p.dtend = record.data.dtend.format('Y-m-d');    
                                    
                                    
                                    new Pman.Request({
                                        url : baseURL + '/Roo/Core_notify_recur',
                                        method :'POST',
                                        params : p,
                                        success : function(data)
                                        {
                                            //Roo.log(data);
                                            record.set('id', data.data.id);
                                        },
                                        failure : function() {
                                            Roo.MessageBox.alert("Error", "There was a problem saving");
                                        }
                                    });
                                       
                                    
                                    
                                },
                                beforeload : function (_self, o)
                                {
                                    if (!_this.data) {
                                        return false;
                                    }
                                    o.params =  Roo.apply(o.params || {}, {
                                        person_id : _this.data.person_id,
                                        onid : _this.data.onid,
                                        ontable : _this.data.ontable,
                                        method : _this.data.method
                                    });
                                        
                                }
                            },
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
                                                dtstart : new Date(),
                                                dtend : Date.parseDate('2050-01-01', 'Y-m-d'),
                                                tz : 'Asia/Hong_Kong',
                                                onid : _this.data.onid,
                                                ontable : _this.data.ontable,
                                                method : _this.data.method, // default...
                                                
                                                method_id : _this.data.method_id, // default...
                                                method_id_display_name : _this.data.method_id_display_name, // default...        
                                                
                                                last_event_id : 0,
                                                freq_day_name : '',
                                                freq_hour_name : '',
                                                freq_name : ''
                                                
                                            
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
                                    listeners : {
                                        click : function()
                                        {
                                             _this.grid.stopEditing();
                                             var s = _this.grid.selModel.getSelectedCell();
                                             if (!s) {
                                                Roo.MessageBox.alert("Error", "Select row");
                                                return;
                                            }
                                            
                                            new Pman.Request({
                                                url : baseURL + '/Roo/core_notify_recur',
                                                method : 'POST',
                                                params : {
                                                    _delete : _this.grid.ds.getAt(s[0]).data.id
                                                }, 
                                                success : function() {
                                                    _this.grid.ds.load({});
                                                },
                                                failure : function() {
                                                    Roo.MessageBox.alert("Error", "Deleting failed - try reloading");
                                                }
                                           });
                                            
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
                                dataIndex : 'method_id',
                                header : 'Type',
                                width : 75,
                                renderer : function(v,x,r) {
                                     return String.format('{0}', r.data.method_id_display_name); 
                                },
                                editor : {
                                    xtype: 'GridEditor',
                                    xns: Roo.grid,
                                    field : {
                                        xtype: 'ComboBox',
                                        xns: Roo.form,
                                        allowBlank : 'false',
                                        displayField : 'display_name',
                                        editable : false,
                                        emptyText : "Select Type",
                                        fieldLabel : 'core_enum',
                                        forceSelection : true,
                                        hiddenName : 'method_id',
                                        listWidth : 400,
                                        loadingText : "Searching...",
                                        name : 'method_id_display_name',
                                        pageSize : 20,
                                        qtip : "Select core_enum",
                                        selectOnFocus : true,
                                        tpl : '<div class="x-grid-cell-text x-btn button"><b>{display_name}</b> </div>',
                                        triggerAction : 'all',
                                        typeAhead : true,
                                        valueField : 'id',
                                        width : 300,
                                        store : {
                                            xtype: 'Store',
                                            xns: Roo.data,
                                            remoteSort : true,
                                            sortInfo : { direction : 'ASC', field: 'id' },
                                            listeners : {
                                                beforeload : function (_self, o){
                                                    o.params = o.params || {};
                                                    // set more here
                                                }
                                            },
                                            proxy : {
                                                xtype: 'HttpProxy',
                                                xns: Roo.data,
                                                method : 'GET',
                                                url : baseURL + '/Roo/core_enum.php'
                                            },
                                            reader : {
                                                xtype: 'JsonReader',
                                                xns: Roo.data,
                                                id : 'id',
                                                root : 'data',
                                                totalProperty : 'total',
                                                fields : [{"name":"id","type":"int"},{"name":"etype","type":"string"}]
                                            }
                                        }
                                    }
                                }
                            },
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
                                dataIndex : 'freq_day',
                                header : 'on day(s)',
                                width : 100,
                                renderer : function(v,x,r) { 
                                    
                                    if (v.length) {
                                     
                                        var cm = _this.grid.colModel;
                                       
                                        var ci = cm.getColumnByDataIndex(this.name);
                                       
                                         var tv = [];
                                        var vals = Roo.decode(v);
                                        Roo.each(vals, function(k) {
                                            var r = this.findRecord(this.valueField, k);
                                            if(r){
                                                tv.push(r.data[this.displayField]);
                                            }else if(this.valueNotFoundText !== undefined){
                                                tv.push( this.valueNotFoundText );
                                            }
                                        },ci.editor.field);
                                
                                        r.data[this.name + '_name'] = tv.join(', ');
                                        return String.format('{0}',tv.join(', '));
                                
                                        
                                    
                                    }
                                    r.data[this.name + '_name'] = '';
                                    return String.format('{0}', r.data.freq_day_name || v); 
                                    
                                },
                                editor : {
                                    xtype: 'GridEditor',
                                    xns: Roo.grid,
                                    field : {
                                        xtype: 'ComboCheck',
                                        xns: Roo.form,
                                        allowBlank : false,
                                        displayField : 'title',
                                        editable : false,
                                        fieldLabel : 'Country',
                                        hiddenName : 'freq_day',
                                        listWidth : 300,
                                        mode : 'local',
                                        name : 'freq_day_name',
                                        pageSize : 40,
                                        triggerAction : 'all',
                                        valueField : 'code',
                                        store : {
                                            xtype: 'SimpleStore',
                                            xns: Roo.data,
                                            data : (function() { 
                                                var ret = [];
                                                Roo.each(Date.dayNames, function(d) {
                                                    ret.push([ d.substring(0,3).toUpperCase(), d ]);
                                                });
                                                return ret;
                                            })(),
                                            fields : ['code', 'title'],
                                            sortInfo : { field : 'title', direction: 'ASC' }
                                        }
                                    }
                                }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                dataIndex : 'freq_hour',
                                header : 'at Hour(s)',
                                width : 250,
                                renderer : function(v,x,r) { 
                                    
                                 
                                    if (v.length) {
                                     
                                        var cm = _this.grid.colModel;
                                       
                                        var ci = cm.getColumnByDataIndex(this.name);
                                       
                                         var tv = [];
                                        var vals = Roo.decode(v);
                                        Roo.each(vals, function(k) {
                                            var r = this.findRecord(this.valueField, k);
                                            if(r){
                                                tv.push(r.data[this.displayField]);
                                            }else if(this.valueNotFoundText !== undefined){
                                                tv.push( this.valueNotFoundText );
                                            }
                                        },ci.editor.field);
                                
                                         r.data[this.name + '_name'] = tv.join(', ');
                                        return String.format('{0}',tv.join(', '));
                                
                                        
                                    
                                    }
                                        r.data[this.name + '_name'] = '';
                                    return String.format('{0}', r.data.freq_hour_name || v); 
                                    
                                },
                                editor : {
                                    xtype: 'GridEditor',
                                    xns: Roo.grid,
                                    field : {
                                        xtype: 'ComboCheck',
                                        xns: Roo.form,
                                        allowBlank : false,
                                        displayField : 'title',
                                        editable : false,
                                        fieldLabel : 'Country',
                                        hiddenName : 'freq_hour',
                                        listWidth : 300,
                                        mode : 'local',
                                        name : 'freq_hour_name',
                                        pageSize : 40,
                                        triggerAction : 'all',
                                        valueField : 'code',
                                        store : {
                                            xtype: 'SimpleStore',
                                            xns: Roo.data,
                                            data : (function() { 
                                                var ret = [];
                                                for (var i = 5; i < 25; i++) {
                                                    var h = i < 10 ? ('0' + i) : i;
                                                    var mer = i < 12 || i > 23 ? 'am' : 'pm';
                                                    var dh = i < 13 ? i : i-12;
                                                    
                                                    ret.push([ h+':00', dh+':00' + mer ]);
                                                    ret.push([ h+':30', dh+':30' + mer ]);        
                                                }
                                                return ret;
                                            })(),
                                            fields : ['code', 'title'],
                                            sortInfo : { field : 'title', direction: 'ASC' }
                                        }
                                    }
                                }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                dataIndex : 'tz',
                                header : 'Timezone',
                                width : 100,
                                renderer : function(v) { return String.format('{0}', v); },
                                editor : {
                                    xtype: 'GridEditor',
                                    xns: Roo.grid,
                                    field : {
                                        xtype: 'ComboBox',
                                        xns: Roo.form,
                                        allowBlank : 'false',
                                        displayField : 'tz',
                                        editable : true,
                                        emptyText : "Select timezone",
                                        fieldLabel : 'core_enum',
                                        forceSelection : true,
                                        listWidth : 400,
                                        loadingText : "Searching...",
                                        minChars : 2,
                                        name : 'tz',
                                        pageSize : 999,
                                        qtip : "Select timezone",
                                        queryParam : 'q',
                                        selectOnFocus : true,
                                        tpl : '<div class="x-grid-cell-text x-btn button"><b>{tz}</b> </div>',
                                        triggerAction : 'all',
                                        typeAhead : true,
                                        width : 300,
                                        store : {
                                            xtype: 'Store',
                                            xns: Roo.data,
                                            listeners : {
                                                beforeload : function (_self, o){
                                                    o.params = o.params || {};
                                                    // set more here
                                                }
                                            },
                                            remoteSort : true,
                                            sortInfo : { direction : 'ASC', field: 'tz' },
                                            proxy : {
                                                xtype: 'HttpProxy',
                                                xns: Roo.data,
                                                method : 'GET',
                                                url : baseURL + '/Core/I18n/Timezone.php'
                                            },
                                            reader : {
                                                xtype: 'JsonReader',
                                                xns: Roo.data,
                                                id : 'id',
                                                root : 'data',
                                                totalProperty : 'total',
                                                fields : [{"name":"tz","type":"string"}]
                                            }
                                        }
                                    }
                                }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                dataIndex : 'last_event_id',
                                header : 'Last Sent',
                                width : 75,
                                renderer : function(v) { return String.format('{0}', v ? v : 'never'); }
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
                    listeners : {
                        click : function (_self, e)
                        {
                            _this.dialog.hide();
                        }
                    },
                    text : "Done"
                }
            ]
        });
    }
};
