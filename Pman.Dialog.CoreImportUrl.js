//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Pman.Dialog.CoreImportUrl = {

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
            draggable : false,
            height : 140,
            modal : true,
            resizable : false,
            title : "Import URL",
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
                                actioncomplete : function (_self, action)
                                {
                                     if (action.type == 'setdata') {
                                       // _this.dialog.el.mask("Loading");
                                       // if(_this.data.id*1 > 0)
                                       //     this.load({ method: 'GET', params: { '_id' : _this.data.id }});
                                       
                                       return;
                                    }
                                    if (action.type == 'load') {
                                 
                                        return;
                                    }
                                    if (action.type =='submit') {
                                    
                                        //action.result.data
                                        _this.dialog.hide();
                                    //    Roo.log(_this.callback);
                                         if (_this.callback) {
                                            _this.callback.call(_this, action.result.data);
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
                            url : baseURL,
                            items : [
                                {
                                    xtype: 'TextField',
                                    xns: Roo.form,
                                    fieldLabel : 'URL',
                                    name : 'importUrl',
                                    vtype : 'url',
                                    width : 250
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
                            _this.form.doAction("submit");
                        }
                    },
                    text : "OK"
                }
            ]
        });
    }
};
