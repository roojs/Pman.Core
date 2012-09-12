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
                    xns: Roo
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
