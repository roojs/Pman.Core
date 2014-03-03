//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Pman.Dialog.CoreEnum = {

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
                    
                }
            },
            background : true,
            closable : false,
            collapsible : false,
            height : 150,
            modal : true,
            resizable : false,
            title : "Add / Edit Core Enum",
            width : 400,
            items : [
                {
                    xtype: 'ContentPanel',
                    xns: Roo,
                    background : true,
                    fitToFrame : true,
                    region : 'center',
                    items : [
                        {
                            xtype: 'Form',
                            xns: Roo.form,
                            listeners : {
                                actioncomplete : function (_self, action)
                                {
                                  if (action.type == 'setdata') {
                                
                                        if((typeof(_this.data.etype) == 'undefined') || !_this.data.etype.length){
                                            Roo.MessageBox.alert('Error', 'Missing etype');
                                            _this.dialog.hide();
                                            return;
                                        }
                                  
                                        if(_this.data.id){
                                            _this.dialog.el.mask("Loading");
                                            this.load({ method: 'GET', params: { '_id' : _this.data.id }}); 
                                        }
                                       
                                       return;
                                    }
                                    if (action.type == 'load') {
                                        _this.dialog.el.unmask();
                                        return;
                                    }
                                    if (action.type == 'submit' ) {
                                        _this.dialog.el.unmask();
                                        _this.dialog.hide();
                                
                                        if (_this.callback) {
                                           _this.callback.call(_this, action.result.data);
                                        }
                                        _this.form.reset();
                                    }
                                },
                                rendered : function (form)
                                {
                                   _this.form = form;
                                }
                            },
                            method : 'POST',
                            style : 'margin: 5px',
                            url : baseURL + '/Roo/core_enum.php',
                            items : [
                                {
                                    xtype: 'TextField',
                                    xns: Roo.form,
                                    allowBlank : false,
                                    fieldLabel : 'Name',
                                    name : 'name',
                                    width : 200
                                },
                                {
                                    xtype: 'TextField',
                                    xns: Roo.form,
                                    allowBlank : false,
                                    fieldLabel : 'Display Name',
                                    name : 'display_name',
                                    width : 200
                                },
                                {
                                    xtype: 'Checkbox',
                                    xns: Roo.form,
                                    fieldLabel : 'Active',
                                    inputValue : 1,
                                    name : 'active',
                                    value : 0,
                                    valueOff : 0
                                },
                                {
                                    xtype: 'Hidden',
                                    xns: Roo.form,
                                    name : 'etype'
                                },
                                {
                                    xtype: 'Hidden',
                                    xns: Roo.form,
                                    name : 'seqid',
                                    value : 0
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
                xns: Roo,
                titlebar : false
            },
            buttons : [
                {
                    xtype: 'Button',
                    xns: Roo,
                    listeners : {
                        click : function() {
                            _this.form.reset();
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
                        
                            var name =     _this.form.findField('name').getValue();
                            name = name.toUpperCase().replace(/[^A-Z]+/g, '');
                            if (!name.length) {
                                Roo.MessageBox.alert("Error","Please fill in a valid name");
                                return;
                            }
                            _this.form.findField('name').setValue(name);
                         
                            _this.form.doAction('submit');
                            
                        }
                    },
                    text : "OK"
                }
            ]
        });
    }
};
