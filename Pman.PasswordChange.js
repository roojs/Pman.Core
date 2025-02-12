//<script type="text/javascript">
 
 
 
Pman.PasswordChange = {
    
    
     
    dialog : false,
    form : false,
    create: function()
    {
        if (this.dialog) {
            return;
        }
        var _this = this;
        
        
        this.dialog = new Ext.LayoutDialog(Ext.get(document.body).createChild({tag:'div'}),  { 
            autoCreated: true,
            title: "Change Password",
            modal: true,
            width:  500,
            height: 160,
            shadow:true,
            resizable: false,
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
        var dgcloser = function(data) {
            Pman.Preview.tmpEnable();
            
            _this.dialog.hide();
            if (_this.callback) {
                _this.callback.call(this, data ? data : false);
            }
        };
        
        this.dialog.addKeyListener(27, dgcloser,this);
        this.dialog.addButton("Cancel",dgcloser,this);
        this.dialog.addButton("Save", this.save, this);
        
        
        this.layout = this.dialog.getLayout();
        this.layout.beginUpdate();
        
        
        this.form = new Ext.form.Form({
            labelWidth: 220 ,
            
            listeners : {
                actionfailed : function(f, act) {
                    //console.log(act);
                    _this.dialog.el.unmask();
                    // error msg???
                    
                    if (act.failureType == 'client') {
                        Ext.MessageBox.alert("Error", "Please Correct all the errors");
                        return;
                        
                    }
                    
                    if (act.type == 'submit') {
                        
                        Ext.MessageBox.alert("Error", typeof(act.result.errorMsg) == 'string' ?
                            act.result.errorMsg : 
                            "Saving failed = fix errors and try again");
                        return;
                    }
                    
                    // what about load failing..
                    Ext.MessageBox.alert("Error", "Error loading details"); 
                              
                },
                actioncomplete: function(f, act) {
                    _this.dialog.el.unmask();
                    
                    if (act.type == 'submit') { // only submitted here if we are 
                        dgcloser(act.data);
                        return; 
                    }
                    // unmask?? 
                }
            }
        
            
            
             
        });
        //?? will this work...
        this.form.addxtype.apply(this.form,[
            {
                name : 'passwd1',
                fieldLabel : "New Password ",
                value : '',
                allowBlank : false, // must be filled in as we rely on it for login details..
                inputType: 'password',
                xtype : 'SecurePass',
                width : 220,
                imageRoot : rootURL + '/Pman/templates/images'
            },
            {
                
                name : 'passwd2',
                fieldLabel : "New Password (type again to confirm)",
                value : '',
                allowBlank : false, // must be filled in as we rely on it for login details..
                inputType: 'password',
                xtype : 'TextField',
                width : 220
            },
             
            {
                name : 'passwordReset',
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
    show : function(data, callback)
    {
        this.callback= callback;
        this.data = data;
        this.create();
        this.form.reset();
         
        // edit...
        this.form.setValues(data);
        
        Pman.Preview.tmpDisable();
        
        this.dialog.show();
        this.form.findField('passwd1').focus();
        
    },
    save : function()
    {
        var p1 = this.form.findField('passwd1').getValue();
        var p2 = this.form.findField('passwd2').getValue();
        if (!p1.length || !p2.length) {
            Ext.MessageBox.alert("Error", "Enter Passwords in both boxes");
        }
        if (p1 != p2) {
            Ext.MessageBox.alert("Error", "Passwords do not match");
        }
        
        this.form.doAction('submit', {
            url: baseURL + '/Core/Auth/ChangePassword',
            method: 'POST',
            params: {
                changePassword: true,
                ts : Math.random()
            } 
        });
    }
     
      
    
    
}