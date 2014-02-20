//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Pman.Dialog.CoreMailingListMessagePreview = {

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
                    var m = 'Core';
                    
                    if(_this.data.module = 'crm'){
                        M = 'Crm';
                    }
                    
                    _self.layout.getRegion('center').showPanel(0);
                    _this.panel.load({ 
                        url: baseURL + '/' + m + '/MessagePreview', 
                        params  : {
                            _id : _this.data.id
                        },
                        method : 'GET'
                    });
                    _this.hpanel.load({ 
                        url: baseURL + '/' + m + '/MessagePreview', 
                        params  : {
                            _as_html : 1,
                            _id : _this.data.id
                        },
                        method : 'GET'
                    });
                        
                }
            },
            autoScroll : true,
            closable : true,
            height : 800,
            shadow : 'true',
            title : "Email Preview",
            width : 1200,
            items : [
                {
                    xtype: 'ContentPanel',
                    xns: Roo,
                    listeners : {
                        render : function (_self)
                        {
                            _this.panel = _self;
                        }
                    },
                    fitContainer : true,
                    fitToFrame : true,
                    region : 'center',
                    title : "Plain"
                },
                {
                    xtype: 'ContentPanel',
                    xns: Roo,
                    listeners : {
                        render : function (_self)
                        {
                            _this.hpanel = _self;
                        }
                    },
                    fitContainer : true,
                    fitToFrame : true,
                    region : 'center',
                    title : "HTML"
                }
            ],
            center : {
                xtype: 'LayoutRegion',
                xns: Roo,
                autoScroll : true,
                tabPosition : 'top'
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
                    text : "OK"
                }
            ]
        });
    }
};
