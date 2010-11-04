//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Pman.Dialog.ImageUpload = {

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
            closable : false,
            collapsible : false,
            height : 140,
            modal : true,
            resizable : true,
            shadow : true,
            title : "Upload an Image or File",
            width : 500,
            items : [
                {
                    xtype: 'ContentPanel',
                    xns: Roo,
                    region : 'center',
                    items : [
                        {
                            xtype: 'Form',
                            xns: Roo.form,
                            listeners : {
                                actioncomplete : function(_self,action)
                                {
                                    if (action.type == 'setdata') {
                                       //_this.dialog.el.mask("Loading");
                                       //this.load({ method: 'GET', params: { '_id' : _this.data.id }});
                                       return;
                                    }
                                    if (action.type == 'load') {
                                        _this.dialog.el.unmask();
                                        return;
                                    }
                                    if (action.type =='submit') {
                                    
                                        _this.dialog.el.unmask();
                                        _this.dialog.hide();
                                    
                                         if (_this.callback) {
                                            _this.callback.call(_this, _this.form.getValues());
                                         }
                                         _this.form.reset();
                                         return;
                                    }
                                },
                                rendered : function (form)
                                {
                                    _this.form= form;
                                }
                            },
                            method : 'POST',
                            style : 'margin:10px;',
                            url : baseURL + '/Roo/Images.php',
                            items : [
                                {
                                    xtype: 'TextField',
                                    xns: Roo.form,
                                    name : 'filename',
                                    width : 200
                                },
                                {
                                    xtype: 'TextField',
                                    xns: Roo.form,
                                    fieldLabel : 'Ontable',
                                    name : 'ontable',
                                    width : 200
                                },
                                {
                                    xtype: 'TextField',
                                    xns: Roo.form,
                                    fieldLabel : 'Onid',
                                    name : 'onid',
                                    width : 75
                                },
                                {
                                    xtype: 'TextField',
                                    xns: Roo.form,
                                    fieldLabel : 'Mimetype',
                                    name : 'mimetype',
                                    width : 200
                                },
                                {
                                    xtype: 'TextField',
                                    xns: Roo.form,
                                    fieldLabel : 'Width',
                                    name : 'width',
                                    width : 75
                                },
                                {
                                    xtype: 'TextField',
                                    xns: Roo.form,
                                    fieldLabel : 'Height',
                                    name : 'height',
                                    width : 75
                                },
                                {
                                    xtype: 'TextField',
                                    xns: Roo.form,
                                    fieldLabel : 'Filesize',
                                    name : 'filesize',
                                    width : 75
                                },
                                {
                                    xtype: 'TextField',
                                    xns: Roo.form,
                                    fieldLabel : 'Displayorder',
                                    name : 'displayorder',
                                    width : 75
                                },
                                {
                                    xtype: 'TextField',
                                    xns: Roo.form,
                                    fieldLabel : 'Language',
                                    name : 'language',
                                    width : 200
                                },
                                {
                                    xtype: 'ComboBox',
                                    xns: Roo.form,
                                    allowBlank : 'false',
                                    editable : 'false',
                                    emptyText : "Select Images",
                                    forceSelection : true,
                                    listWidth : 400,
                                    loadingText : "Searching...",
                                    minChars : 2,
                                    pageSize : 20,
                                    qtip : "Select Images",
                                    selectOnFocus : true,
                                    triggerAction : 'all',
                                    typeAhead : true,
                                    width : 300,
                                    tpl : '<div class="x-grid-cell-text x-btn button"><b>{filename}</b> </div>',
                                    queryParam : 'query[filename]',
                                    fieldLabel : 'Parent image',
                                    valueField : 'id',
                                    displayField : 'filename',
                                    hiddenName : 'parent_image_id',
                                    name : 'parent_image_id_filename',
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
                                            url : baseURL + '/Roo/Images.php'
                                        },
                                        reader : {
                                            xtype: 'JsonReader',
                                            xns: Roo.data,
                                            id : 'id',
                                            root : 'data',
                                            totalProperty : 'total',
                                            fields : [{"name":"id","type":"int"},{"name":"filename","type":"string"}]
                                        }
                                    }
                                },
                                {
                                    xtype: 'DateField',
                                    xns: Roo.form,
                                    fieldLabel : 'Created',
                                    name : 'created',
                                    width : 75
                                },
                                {
                                    xtype: 'TextField',
                                    xns: Roo.form,
                                    fieldLabel : 'Imgtype',
                                    name : 'imgtype',
                                    width : 200
                                },
                                {
                                    xtype: 'TextField',
                                    xns: Roo.form,
                                    fieldLabel : 'Linkurl',
                                    name : 'linkurl',
                                    width : 200
                                },
                                {
                                    xtype: 'TextArea',
                                    xns: Roo.form,
                                    fieldLabel : 'Descript',
                                    name : 'descript',
                                    width : 200,
                                    height : 100
                                },
                                {
                                    xtype: 'TextField',
                                    xns: Roo.form,
                                    fieldLabel : 'Title',
                                    name : 'title',
                                    width : 200
                                },
                                {
                                    xtype: 'Hidden',
                                    xns: Roo.form,
                                    name : 'id'
                                }
                            ]
                        }
                    ]
                }
            ],
            center : {
                xtype: 'LayoutRegion',
                xns: Roo
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
                    text : "Cancel"
                },
                {
                    xtype: 'Button',
                    xns: Roo,
                    listeners : {
                        click : function (_self, e)
                        {
                            // do some checks?
                             
                            _this.dialog.el.mask("Sending");
                            _this.uploadComplete = false;
                            _this.form.doAction('submit', {
                                url: baseURL + '/Roo/Images.html',
                                method: 'POST',
                                params: {
                                 //   _id: 0 ,
                                    ts : Math.random()
                                } 
                            });
                            _this.haveProgress = false,
                            _this.uploadProgress.defer(1000, this);
                        
                        }
                    },
                    text : "Post"
                }
            ]
        });
    }
};
