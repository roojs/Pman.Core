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
                                    id : 'poitem_id',
                                    root : 'data',
                                    totalProperty : 'total',
                                    fields : [
                                        {
                                            'name': 'poitem_id',
                                            'type': 'int'
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
                                emptyMsg : "No Item found",
                                pageSize : 100,
                                updateSummary : function() {
                                
                                    var f = this;
                                    new Pman.Request({
                                        url : baseURL + '/Xtuple/Roo/Poitem',
                                        method : 'GET',
                                        params : {
                                            _roo_office : _this.data.office ? _this.data.office : baseURL.split('/').pop().substr(0,2),
                                            _totals : 1,
                                            poitem_pohead_id : _this.form.findField('pohead_id').getValue()
                                        },
                                        success : function(d) {
                                            Roo.log(d);
                                            f.displayEl.update(String.format(
                                                "{0} items | Total : {1} {2}",
                                                d.data[0].count_item,
                                                _this.form.findField('pohead_curr_id').el.dom.value,
                                                d.data[0].totals
                                            ));
                                                
                                        }
                                    });
                                
                                }
                            },
                            colModel : [
                                {
                                    xtype: 'ColumnModel',
                                    xns: Roo.grid,
                                    align : 'right',
                                    dataIndex : 'poitem_linenumber',
                                    header : 'Line#',
                                    width : 50,
                                    renderer : function(v) { return String.format('{0}', v); }
                                },
                                {
                                    xtype: 'ColumnModel',
                                    xns: Roo.grid,
                                    dataIndex : 'item_number',
                                    header : 'Item code',
                                    width : 100,
                                    renderer : function(v) { return String.format('{0}', v); },
                                    editor : {
                                        xtype: 'GridEditor',
                                        xns: Roo.grid,
                                        field : {
                                            xtype: 'ComboBox',
                                            xns: Roo.form,
                                            listeners : {
                                                beforeselect : function (combo, record, index)
                                                {
                                                  var ar = _this.grid.activeEditor.record;
                                                  
                                                  (function() { 
                                                      ar.set('item_descrip1', record.data.itemsite_item_id_item_descrip1);
                                                      ar.set('poitem_itemsite_id', record.data.itemsite_id);
                                                  }).defer(100);
                                                  
                                                }
                                            },
                                            allowBlank : false,
                                            displayField : 'itemsite_item_id_item_number',
                                            editable : true,
                                            emptyText : "Select item",
                                            forceSelection : true,
                                            hiddenName : 'itemsite_item_id_item_number',
                                            listWidth : 400,
                                            loadingText : "Searching...",
                                            minChars : 2,
                                            name : 'item_number',
                                            pageSize : 20,
                                            qtip : "Select item",
                                            queryParam : 'query[number]',
                                            selectOnFocus : true,
                                            tpl : '<div class="x-grid-cell-text x-btn button"><b>{itemsite_item_id_item_number}</b> - {itemsite_item_id_item_descrip1} </div>',
                                            triggerAction : 'all',
                                            typeAhead : false,
                                            valueField : 'item_number',
                                            store : {
                                                xtype: 'Store',
                                                xns: Roo.data,
                                                listeners : {
                                                    beforeload : function (_self, o){
                                                        o.params = o.params || {}; 
                                                        o.params.itemsite_posupply = 1;
                                                    }
                                                },
                                                remoteSort : true,
                                                sortInfo : { direction : 'ASC', field: 'item_number' },
                                                proxy : {
                                                    xtype: 'HttpProxy',
                                                    xns: Roo.data,
                                                    method : 'GET',
                                                    url : baseURL + '/Roo/itemsite.php'
                                                },
                                                reader : {
                                                    xtype: 'JsonReader',
                                                    xns: Roo.data,
                                                    id : 'itemsite_id',
                                                    root : 'data',
                                                    totalProperty : 'total',
                                                    fields : [{"name":"item_id","type":"int"},"item_number"]
                                                }
                                            }
                                        }
                                    }
                                },
                                {
                                    xtype: 'ColumnModel',
                                    xns: Roo.grid,
                                    dataIndex : 'item_descrip1',
                                    header : 'Item description',
                                    width : 150,
                                    renderer : function(v) { return String.format('{0}', v); }
                                },
                                {
                                    xtype: 'ColumnModel',
                                    xns: Roo.grid,
                                    dataIndex : 'poitem_duedate',
                                    header : 'Due date',
                                    width : 100,
                                    renderer : function(v) { return String.format('{0}', v ? v.format('Y-m-d') : ''); }
                                },
                                {
                                    xtype: 'ColumnModel',
                                    xns: Roo.grid,
                                    align : 'right',
                                    dataIndex : 'poitem_qty_ordered',
                                    header : 'Ordered',
                                    width : 75,
                                    renderer : function(v) { return String.format('{0}', v); },
                                    editor : {
                                        xtype: 'GridEditor',
                                        xns: Roo.grid,
                                        field : {
                                            xtype: 'NumberField',
                                            xns: Roo.form,
                                            allowDecimals : false,
                                            decimalPrecision : 0,
                                            minValue : 1,
                                            style : 'text-align:right'
                                        }
                                    }
                                },
                                {
                                    xtype: 'ColumnModel',
                                    xns: Roo.grid,
                                    align : 'right',
                                    dataIndex : 'poitem_qty_received',
                                    header : 'Received',
                                    width : 75,
                                    renderer : function(v,x,r) { 
                                        return String.format(
                                            r.data.poitem_qty_ordered != (v-r.data.poitem_qty_returned) ? '<span style="color:red">{0}</span>':  '{0}',
                                             v - r.data.poitem_qty_returned);
                                    }
                                },
                                {
                                    xtype: 'ColumnModel',
                                    xns: Roo.grid,
                                    align : 'right',
                                    dataIndex : 'poitem_qty_vouchered',
                                    header : 'Vouchered',
                                    width : 65,
                                    renderer : function(v,x,r) { 
                                        return String.format(
                                            r.data.poitem_qty_ordered != v ? '<span style="color:red">{0}</span>':  '{0}',
                                             v);
                                    }
                                },
                                {
                                    xtype: 'ColumnModel',
                                    xns: Roo.grid,
                                    align : 'right',
                                    dataIndex : 'poitem_unitprice',
                                    header : 'Unit price',
                                    width : 100,
                                    renderer : function(v) { return String.format('{0}', (v || v == 0) ? parseFloat(v).toFixed(3) : ''); },
                                    editor : {
                                        xtype: 'GridEditor',
                                        xns: Roo.grid,
                                        field : {
                                            xtype: 'NumberField',
                                            xns: Roo.form,
                                            allowBlank : false,
                                            allowDecimals : false,
                                            decimalPrecision : 0,
                                            minValue : 1,
                                            style : 'text-align:right'
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
