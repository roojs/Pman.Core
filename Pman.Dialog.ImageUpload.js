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
