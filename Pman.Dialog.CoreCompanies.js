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
            center : {
                alwaysShowTabs : false,
                autoScroll : false,
                xtype : 'LayoutRegion',
                hideTabs : true,
                xns : Roo,
                closeOnTab : true,
                titlebar : false
            },
            modal : true,
            shadow : true,
            collapsible : false,
            title : "Add / Edit Organization",
            xtype : 'LayoutDialog',
            autoCreate : true,
            width : 750,
            xns : Roo,
            closable : false,
            height : 400,
            draggable : false,
            buttons : [
            	 {
            	        text : "Cancel",
            	        xtype : 'Button',
            	        xns : Roo,
            	        listeners : {
            	        	click : function (_self, e)
            	        	   {
            	        	       _this.dialog.hide();
            	        	   }
            	        }
            	    },
{
            	        text : "Save",
            	        xtype : 'Button',
            	        xns : Roo,
            	        listeners : {
            	        	click : function (_self, e)
            	        	   {
            	        	       // do some checks?
            	        	        
            	        	       
            	        	       _this.dialog.el.mask("Saving");
            	        	       _this.form.doAction("submit");
            	        	   
            	        	   }
            	        }
            	    }
            ],
            items : [
            	{
                    region : 'center',
                    fitToFrame : true,
                    xtype : 'ContentPanel',
                    autoCreate : true,
                    xns : Roo,
                    items : [
                    	{
                            url : baseURL + '/Roo/Companies.php',
                            fileUpload : true,
                            xtype : 'Form',
                            labelWidth : 160,
                            xns : Roo.form,
                            listeners : {
                            	actionfailed : function(f, act) {
                            	       _this.dialog.el.unmask();
                            	       // error msg???
                            	       Pman.standardActionFailed(f,act);
                            	                 
                            	   },
                            	rendered : function (form)
                            	   {
                            	       _this.form = form;
                            	   },
                            	actioncomplete : function(f, act) {
                            	       _this.dialog.el.unmask();
                            	       //console.log('load completed'); 
                            	       // error messages?????
                            	       if(act.type == 'setdata'){
                            	           this.load({ method: 'GET', params: { '_id' : _this.data.id }});
                            	           return;
                            	       }
                            	      
                            	       if (act.type == 'load') {
                            	           _this.data = act.result.data;
                            	           var meth = _this.data.comptype == 'OWNER' ? 'disable' : 'enable';
                            	        
                            	               
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
                            items : [
                            	{
                                    xtype : 'Column',
                                    xns : Roo.form,
                                    width : 500,
                                    items : [
                                    	{
                                            fieldLabel : 'Company ID (for filing Ref.)',
                                            xtype : 'TextField',
                                            allowBlank : true,
                                            width : 100,
                                            xns : Roo.form,
                                            name : 'code',
                                            qtip : "Enter code"
                                        },
                                    	{
                                            store : {
                                                proxy : {
                                                    url : baseURL + '/Roo/core_enum.php',
                                                    method : 'GET',
                                                    xtype : 'HttpProxy',
                                                    xns : Roo.data
                                                },
                                                reader : {
                                                    id : 'id',
                                                    root : 'data',
                                                    xtype : 'JsonReader',
                                                    fields : [{"name":"id","type":"int"},{"name":"name","type":"string"}],
                                                    xns : Roo.data,
                                                    totalProperty : 'total'
                                                },
                                                xtype : 'Store',
                                                remoteSort : true,
                                                sortInfo : { direction : 'ASC', field: 'id' },
                                                xns : Roo.data,
                                                listeners : {
                                                	beforeload : function (_self, o){
                                                	       o.params = o.params || {};
                                                	       // set more here
                                                	       //o.params['query[empty_etype]'] = 1;
                                                	       o.params.etype = 'COMPTYPE';
                                                	   }
                                                },
                                                items : [

                                                ]

                                            },
                                            alwaysQuery : true,
                                            listWidth : 250,
                                            triggerAction : 'all',
                                            fieldLabel : 'Type',
                                            forceSelection : true,
                                            selectOnFocus : true,
                                            pageSize : 20,
                                            emptyText : "Select Type",
                                            displayField : 'display_name',
                                            hiddenName : 'comptype',
                                            minChars : 2,
                                            valueField : 'name',
                                            xtype : 'ComboBox',
                                            typeAhead : false,
                                            width : 200,
                                            xns : Roo.form,
                                            name : 'comptype_display_name',
                                            qtip : "Select type",
                                            queryParam : 'query[name]',
                                            tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{name}</b> : {display_name}</div>',
                                            loadingText : "Searching...",
                                            listeners : {
                                            	render : function (_self)
                                            	   {
                                            	       _this.etypeCombo = _self;
                                            	   }
                                            },
                                            items : [

                                            ]

                                        },
                                    	{
                                            fieldLabel : 'Company Name',
                                            xtype : 'TextField',
                                            allowBlank : true,
                                            width : 300,
                                            xns : Roo.form,
                                            name : 'name',
                                            qtip : "Enter Company Name"
                                        },
                                    	{
                                            fieldLabel : 'Phone',
                                            xtype : 'TextField',
                                            allowBlank : true,
                                            width : 300,
                                            xns : Roo.form,
                                            name : 'tel',
                                            qtip : "Enter Phone Number"
                                        },
                                    	{
                                            fieldLabel : 'Fax',
                                            xtype : 'TextField',
                                            allowBlank : true,
                                            width : 300,
                                            xns : Roo.form,
                                            name : 'fax',
                                            qtip : "Enter Fax Number"
                                        },
                                    	{
                                            fieldLabel : 'Email',
                                            xtype : 'TextField',
                                            allowBlank : true,
                                            width : 300,
                                            xns : Roo.form,
                                            name : 'email',
                                            qtip : "Enter Email Address"
                                        },
                                    	{
                                            fieldLabel : 'Url',
                                            xtype : 'TextField',
                                            allowBlank : true,
                                            width : 300,
                                            xns : Roo.form,
                                            name : 'url',
                                            qtip : "Enter Url"
                                        },
                                    	{
                                            fieldLabel : 'Address',
                                            xtype : 'TextField',
                                            allowBlank : true,
                                            width : 300,
                                            xns : Roo.form,
                                            name : 'address',
                                            qtip : "Enter Address"
                                        },
                                    	{
                                            fieldLabel : 'Remarks',
                                            xtype : 'TextArea',
                                            allowBlank : true,
                                            width : 300,
                                            xns : Roo.form,
                                            height : 120,
                                            name : 'remarks',
                                            qtip : "Enter remarks"
                                        }
                                    ]

                                },
                            	{
                                    labelAlign : 'top',
                                    xtype : 'Column',
                                    width : 200,
                                    xns : Roo.form,
                                    items : [
                                    	{
                                            fieldLabel : 'Background Colour',
                                            xtype : 'ColorField',
                                            xns : Roo.form,
                                            name : 'background_color'
                                        },
                                    	{
                                            fieldLabel : 'Logo Image',
                                            style : 'border: 1px solid #ccc;',
                                            xtype : 'DisplayField',
                                            valueRenderer : function(v) {
                                                //var vp = v ? v : 'Companies:' + _this.data.id + ':-LOGO';
                                                if (!v) {
                                                    return "No Image Available" + '<BR/>';
                                                }
                                                return String.format('<a target="_new" href="{1}"><img src="{0}" width="150"></a>', 
                                                        baseURL + '/Images/Thumb/150x150/' + v + '/logo.jpg',
                                                        baseURL + '/Images/'+v+'/logo.jpg'           
                                                );
                                            },
                                            icon : 'rootURL + \'images/default/dd/drop-add.gif\'',
                                            width : 170,
                                            xns : Roo.form,
                                            height : 170,
                                            name : 'logo_id'
                                        },
                                    	{
                                            text : "Add Image",
                                            xtype : 'Button',
                                            xns : Roo,
                                            listeners : {
                                            	click : function (_self, e)
                                            	   {
                                            	       var _t = _this.form.findField('logo_id');
                                            	                            
                                            	       Pman.Dialog.Image.show({
                                            	           onid :_this.data.id,
                                            	           ontable : 'Companies',
                                            	           imgtype : 'LOGO'
                                            	       }, function(data) {
                                            	           if  (data) {
                                            	               _t.setValue(data.id);
                                            	           }
                                            	           
                                            	       });
                                            	   }
                                            }
                                        }
                                    ]

                                },
                            	{
                                    xtype : 'Hidden',
                                    xns : Roo.form,
                                    name : 'id'
                                }
                            ]

                        }
                    ]

                }
            ]

        });
    }
};
