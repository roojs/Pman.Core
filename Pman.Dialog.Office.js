//<script type="text/javascript">

  
Pman.Dialog.Office = {
    dialog : false,
    form : false,
    create: function()
    {
        if (this.dialog) {
            return;
        }
        
        this.dialog = new Roo.LayoutDialog(Roo.get(document.body).createChild({tag:'div'}),  { 
            autoCreated: true,
            title: "Edit Office / Department / Sub Company",
            modal: true,
            width:  650,
            height: 400,
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
        
        var dg = Pman.Dialog.Office;
        
        this.form = new Roo.form.Form({
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
                'name' : 'id',
                'value' : '',
                'xtype' : 'Hidden'
                
            },{
                'name' : 'company_id',
              
                'value' : '',
                'xtype' : 'Hidden'
            },{
                'name' : 'company_id_name',
                'fieldLabel' : "Company",
                'value' : '',
                'xtype' : 'TextField',
                readOnly : true,
                
                'width' : 300
            },
            {
                'name' : 'name',
                'fieldLabel' : "Office / Department / Sub Comp. Name",
                'value' : '',
                'allowBlank' : false,
                'qtip' : "Enter name",
                'xtype' : 'TextField',
                'width' : 300
            },{
                'name' : 'address',
                'fieldLabel' : "Address",
                'value' : '',
                 
                'qtip' : "Enter address",
                'xtype' : 'TextArea',
                'height' : 100,
                'width' : 300
            },{
                'name' : 'phone',
                'fieldLabel' : "Phone",
                'value' : '',
                
                'qtip' : "Enter phone",
                'xtype' : 'TextField',
                'width' : 300
            },{
                'name' : 'fax',
                'fieldLabel' : "fax",
                'value' : '',
                
                'qtip' : "Enter fax",
                'xtype' : 'TextField',
                'width' : 300
            },{
                'name' : 'email',
                'fieldLabel' : "Email",
                'value' : '',
             
                'qtip' : "Enter email",
                'xtype' : 'TextField',
                'width' : 300
            }
        ]);
        var ef = this.dialog.getLayout().getEl().createChild({tag: 'div'});
        ef.dom.style.margin = 10;
         
        this.form.render(ef.dom);

        this.dialog.getLayout().add('center', new Roo.ContentPanel(ef, {
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
    show : function(data, callback)
    {
        this.callback= callback;
        this._id = data.id;
        this.create();
        this.form.reset();
        
        
        this.form.setValues(data);
        this.dialog.show();
        this.form.findField('name').focus();
        

    },
    save : function()
    {
         this.form.doAction('submit', {
            url: baseURL + '/Roo/core_office',
            method: 'POST',
            params: {
                _id: this._id ,
                ts : Math.random()
            } 
        });
    } 
    
    
         
};
