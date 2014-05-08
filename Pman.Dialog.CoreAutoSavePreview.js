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
                            Roo.log(2);
                                _this.grid.footer.onClick('first');
                            }
                        }
                    },
                    background : false,
                    fitContainer : true,
                    fitToframe : true,
                    region : 'west',
                    tableName : 'Events',
                    title : "Events",
                    grid : {
                        xtype: 'Grid',
                        xns: Roo.grid,
                        listeners : {
                            render : function() { 
                                _this.grid = this; 
                                //_this.dialog = Pman.Dialog.FILL_IN
                                if (_this.panel.active) {
                                Roo.log('1');
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
                                    
                                    if (!this.getSelected()) {
                                        _this.viewPanel.setContent("Nothing Selected");
                                        return;
                                    }
                                    
                                    _this.viewPanel.setContent("data");
                                }
                            },
                            singleSelect : true
                        },
                        footer : {
                            xtype: 'PagingToolbar',
                            xns: Roo,
                            pageSize : 25,
                            displayInfo : true,
                            displayMsg : "Displaying Images{0} - {1} of {2}",
                            emptyMsg : "No Images found"
                        },
                        dataSource : {
                            xtype: 'Store',
                            xns: Roo.data,
                            listeners : {
                                beforeload : function (_self, o)
                                {
                                    Roo.log(_this.data);
                                    o.params = o.parmas || {};
                                    o.params.action = 'AUTOSAVE';
                                    
                                }
                            },
                            remoteSort : true,
                            sortInfo : { field: 'event_when', direction: 'DESC'},
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
                                        'name': 'person_name',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'event_when',
                                        'type': 'date',
                                        'dateFormat': 'Y-m-d'
                                    },
                                    {
                                        'name': 'action',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'ipaddr',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'on_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'on_table',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'person_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'remarks',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'person_id_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'person_id_office_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'person_id_name',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'person_id_phone',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'person_id_fax',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'person_id_email',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'person_id_company_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'person_id_role',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'person_id_active',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'person_id_remarks',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'person_id_passwd',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'person_id_owner_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'person_id_lang',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'person_id_no_reset_sent',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'person_id_action_type',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'person_id_project_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'person_id_deleted_by',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'person_id_deleted_dt',
                                        'type': 'date'
                                    }
                                ]
                            },
                            proxy : {
                                xtype: 'HttpProxy',
                                xns: Roo.data,
                                method : 'GET',
                                url : baseURL + '/Roo/Events.php'
                            }
                        },
                        colModel : [
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                dataIndex : 'event_when',
                                header : 'Date',
                                width : 100,
                                renderer : function(v) { return v ? v.dateFormat('d/m/Y H:i') : ''; }
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
                    region : 'west',
                    tableName : 'Images',
                    title : "Images",
                    grid : {
                        xtype: 'Grid',
                        xns: Roo.grid,
                        autoExpandColumn : 'filename',
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
                                totalProperty : 'total',
                                root : 'data',
                                id : 'id',
                                fields : [
                                    {
                                        'name': 'id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'filename',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'ontable',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'onid',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'mimetype',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'width',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'height',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'filesize',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'displayorder',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'language',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'parent_image_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'created',
                                        'type': 'date',
                                        'dateFormat': 'Y-m-d'
                                    },
                                    {
                                        'name': 'imgtype',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'linkurl',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'descript',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'title',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'parent_image_id_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'parent_image_id_filename',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'parent_image_id_ontable',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'parent_image_id_onid',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'parent_image_id_mimetype',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'parent_image_id_width',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'parent_image_id_height',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'parent_image_id_filesize',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'parent_image_id_displayorder',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'parent_image_id_language',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'parent_image_id_parent_image_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'parent_image_id_created',
                                        'type': 'date'
                                    },
                                    {
                                        'name': 'parent_image_id_imgtype',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'parent_image_id_linkurl',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'parent_image_id_descript',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'parent_image_id_title',
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
                            displayMsg : "Displaying Images{0} - {1} of {2}",
                            emptyMsg : "No Images found"
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
                                             Pman.genericDelete(_this, 'Images'); 
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
                                header : 'Filename',
                                width : 200,
                                dataIndex : 'filename',
                                renderer : function(v) { return String.format('{0}', v); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Ontable',
                                width : 200,
                                dataIndex : 'ontable',
                                renderer : function(v) { return String.format('{0}', v); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Onid',
                                width : 75,
                                dataIndex : 'onid',
                                renderer : function(v) { return String.format('{0}', v); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Mimetype',
                                width : 200,
                                dataIndex : 'mimetype',
                                renderer : function(v) { return String.format('{0}', v); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Width',
                                width : 75,
                                dataIndex : 'width',
                                renderer : function(v) { return String.format('{0}', v); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Height',
                                width : 75,
                                dataIndex : 'height',
                                renderer : function(v) { return String.format('{0}', v); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Filesize',
                                width : 75,
                                dataIndex : 'filesize',
                                renderer : function(v) { return String.format('{0}', v); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Displayorder',
                                width : 75,
                                dataIndex : 'displayorder',
                                renderer : function(v) { return String.format('{0}', v); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Language',
                                width : 200,
                                dataIndex : 'language',
                                renderer : function(v) { return String.format('{0}', v); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Parent image',
                                width : 75,
                                dataIndex : 'parent_image_id',
                                renderer : function(v) { return String.format('{0}', v); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Created',
                                width : 75,
                                dataIndex : 'created',
                                renderer : function(v) { return String.format('{0}', v ? v.format('d/M/Y') : ''); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Imgtype',
                                width : 200,
                                dataIndex : 'imgtype',
                                renderer : function(v) { return String.format('{0}', v); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Linkurl',
                                width : 200,
                                dataIndex : 'linkurl',
                                renderer : function(v) { return String.format('{0}', v); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Descript',
                                width : 200,
                                dataIndex : 'descript',
                                renderer : function(v) { return String.format('{0}', v); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Title',
                                width : 200,
                                dataIndex : 'title',
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
