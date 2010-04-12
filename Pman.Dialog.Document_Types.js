//<script type="text/javascript">

Pman.Dialog.Document_Types = {
    dialog : false,
    form : false,
    create: function()
    {
        if (this.dialog) {
            return;
        }
        
        this.dialog = new Ext.LayoutDialog(Ext.get(document.body).createChild({tag:'div'}),  { 
            autoCreated: true,
            title: "Edit Document Type",
            modal: true,
            width:  650,
            height: 250,
            shadow:true,
            minWidth:200,
            minHeight:180,
            //proxyDrag: true,
            closable: false,
            draggable: false,
            center: {
                autoScroll:false,
                titlebar: false,
               // tabPosition: 'top',
                hideTabs: true,
                closeOnTab: true,
                alwaysShowTabs: false
            }
        });
        this.dialog.addKeyListener(27, this.dialog.hide, this.dialog);
        this.dialog.addButton("Cancel", this.dialog.hide, this.dialog);
       
        this.dialog.addButton("Save", this.save, this);
        this.layout = this.dialog.getLayout();
        this.layout.beginUpdate();
        
        var dg = Pman.Dialog.Document_Types;
        
        this.form = new Ext.form.Form({
            labelWidth: 250 ,
            listeners : {
                actionfailed : function(f, act) {
                    dg.dialog.el.unmask();
                    // error msg???
                    
                    Pman.standardActionFailed(f,act);
                              
                },
                actioncomplete: function(f, act) {
                    dg.dialog.el.unmask();
                    //console.log('load completed'); 
                    // error messages?????
                    
                   
                    if (act.type == 'load') {
                        
                        dg.data = act.result.data;
                       // dg.loaded();
                        return;
                    }
                    
                    
                    if (act.type == 'submit') { // only submitted here if we are 
                        dg.dialog.hide();
                        if (dg.callback) {
                            dg.callback.call(this, act.result.data);
                        }
                        return; 
                    }
                    // unmask?? 
                }
            }
        
            
            
             
        });
        //?? will this work...
        this.form.addxtype.apply(this.form,[{
                name : 'code',
                fieldLabel : "Code",
                value : '',
                allowBlank : false,
                qtip : "Enter code",
                xtype : 'TextField',
                width : 100
            },{
                name : 'name',
                fieldLabel : "Document Type",
                value : '',
                allowBlank : true,
                qtip : "Enter Document Type",
                xtype : 'TextField',
                width : 300
            },{
                name : 'remarks',
                fieldLabel : "Remarks",
                value : '',
                allowBlank : true,
                qtip : "Enter remarks",
                xtype : 'TextArea',
                height : 100,
                width : 300
            },{
                name : 'id',
                value : '',
                xtype : 'Hidden'
                
           
            }
        ]);
        var ef = this.dialog.getLayout().getEl().createChild({tag: 'div'});
        ef.dom.style.margin = 10;
         
        this.form.render(ef.dom);

        var vp = this.dialog.getLayout().add('center', new Ext.ContentPanel(ef, {
            autoCreate : true,
            //title: 'Org Details',
            //toolbar: this.tb,
            width: 250,
            maxWidth: 250,
            fitToFrame:true
        }));
          

        
        
        this.layout.endUpdate();
    },
    _id : 0,
    show: function (data, callback)
    {
        
        this.callback = callback;
        this._id = data.id ? data.id : 0;  // modify if you do not use ID !!!!
        this.create();
        this.form.reset();
        
        this.form.setValues(data);
        
        this.dialog.show();
        

    },
     
    save : function()
    {
         this.form.doAction('submit', {
            url: baseURL + '/Roo/Document_Types.html',
            method: 'POST',
            params: {
                _id: this._id ,
                ts : Math.random()
            } 
        });
    }
    
    
    
    
         
};