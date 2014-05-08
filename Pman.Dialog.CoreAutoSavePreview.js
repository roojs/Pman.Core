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
            center : {
                xtype: 'LayoutRegion',
                xns: Roo,
                titlebar : false
            },
            west : {
                xtype: 'LayoutRegion',
                xns: Roo,
                split : true,
                width : 300
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
