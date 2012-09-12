//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Pman.Dialog.CoreCompanies = {

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
            autoCreate : 'true',
            closable : false,
            collapsible : false,
            draggable : false,
            height : 400,
            modal : true,
            shadow : 'true',
            title : "Edit Companies",
            width : 750,
            items : [
                {
                    xtype: 'ContentPanel',
                    xns: Roo,
                    items : [
                        {
                            xtype: 'Form',
                            xns: Roo.form,
                            listeners : {
                                actionfailed : function(f, act) {
                                    _this.dialog.el.unmask();
                                    // error msg???
                                    Pman.standardActionFailed(f,act);
                                              
                                },
                                actioncomplete : function(f, act) {
                                    _this.dialog.el.unmask();
                                    //console.log('load completed'); 
                                    // error messages?????
                                    
                                   
                                    if (act.type == 'load') {
                                        
                                        _this.data = act.result.data;
                                        var meth = _this.data.isOwner || !Pman.Login.isOwner() ? 'disable' : 'enable';
                                     
                                            
                                        if (_this.form.findField('comptype')) {
                                            _this.form.findField('comptype')[meth]();
                                        }
                                         
                                       // _this.loaded();
                                        return;
                                    }
                                    
                                    
                                    if (act.type == 'submit') { // only submitted here if we are 
                                        _this.dialog.hide();
                                       
                                        if (_this.callback) {
                                            _this.callback.call(this, act.result.data);
                                        }
                                        return; 
                                    }
                                    // unmask?? 
                                }
                            },
                            fileUpload : true,
                            labelWidth : 150
                        }
                    ]
                }
            ],
            center : {
                xtype: 'LayoutRegion',
                xns: Roo,
                alwaysShowTabs : false,
                autoScroll : false,
                closeOnTab : true,
                hideTabs : true,
                titlebar : false
            },
            buttons : [
                {
                    xtype: 'Button',
                    xns: Roo,
                    text : "Cancel"
                },
                {
                    xtype: 'Button',
                    xns: Roo,
                    text : "Save"
                }
            ]
        });
    }
};
