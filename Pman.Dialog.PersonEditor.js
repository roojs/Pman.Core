//<script type="text/javascript">

Pman.Dialog.PersonEditor = function(config)
{
   
    Roo.apply(this, config);
    
};

Pman.Dialog.PersonEditor.prototype = {
    
    itemList : false, // list of itemTypes used on form.
    dialogConfig : false, // 
    type : '',
    
    
    itemTypes : false, // set in contructor
    
    dialog : false,
    form : false,
    layout : false,
    
    callback: false, 
    _id : false,
    data : false,
    
    
    create : function()
    {
        if (this.dialog) {
            return;
        }
        var _this = this;
        this.dialog = new Roo.LayoutDialog(Roo.get(document.body).createChild({tag:'div'}),  
            Roo.apply({ 
                autoCreated: true,
                title: 'Edit Contact Details',
                modal: true,
                width:  530,
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
            },this.cr)
        );
        
        this.dialog.addKeyListener(27, this.dialog.hide, this.dialog);
        if (this.itemList.indexOf('save_send') > -1 ) {
            this.dialog.addButton("Send Introduction Mail", this.saveSend, this);
        }
        
        this.dialog.addButton("Cancel", this.dialog.hide, this.dialog);
        
        
        
        this.dialog.addButton("Save", this.save, this);
        this.layout = this.dialog.getLayout();
        this.layout.beginUpdate();
        
         
        this.form = new Roo.form.Form({
            labelWidth: 120,
            listeners : {
                actionfailed : function(f, act) {
                    _this.dialog.el.unmask();
                    // error msg???
                    Pman.standardActionFailed(f,act);
                              
                },
                actioncomplete: function(f, act) {
                    _this.dialog.el.unmask();
                    if (act.type == 'load') {
                        
                        _this.data = act.result.data;
                    }
                    
                                
                    if  ((act.type == 'load') || (act.type == 'setdata')) {
                        var data = _this.data;
                        // we dont have  a form where company name is sent in - and is editable..
                        //this.form.findField('office_id')
                        if(!data.countries && _this.form.findField('countries')){
                            _this.form.findField('countries').setValue();// set empty array by default...
                        }
                        
                        if (_this.form.findField('company_id') && _this.form.findField('company_id').setFromData) {
                            _this.form.findField('company_id').setFromData( data.company_id ? {
                                id : data.company_id,
                                name : data.company_id_name,
                                remarks:  data.company_id_remarks,
                                address:  data.company_id_address,
                                tel:  data.company_id_tel,
                                fax:  data.company_id_fax,
                                email:  data.company_id_email
                            } : { id: 0, name : ''  });
                        }
                        
                        
                        if (_this.form.findField('office_id') && _this.form.findField('office_id').setFromData) {
                            // set up the office details.. new, edit, staff
                            
                            _this.form.findField('office_id').setFromData(data.office_id ? { 
                                id: data.office_id,
                                name:  data.office_id_name,
                                remarks:  data.office_id_remarks,
                                address:  data.office_id_address,
                                tel:  data.office_id_tel,
                                fax:  data.office_id_fax,
                                email:  data.office_id_email
                                // should we add in company_name etc. ????
                                
                            } :  {  id: 0, name:  ''  });
                            
                        }
                        if (_this.form.findField('project_id')) {
                            _this.form.findbyId('project_id_fs').setExpand(data.project_id ? true: false);
                            
                            
                            _this.form.findField('project_id').setFromData(data.project_id ?{
                               id : data.project_id,
                               code : data.project_id_code
                              } : { id: 0, code :'' } );
                       }
                        
                        
                        if (this.type == 'staff') {
                            _this.form.findField('passwd1').allowBlank = false;
                            _this.form.findField('passwd2').allowBlank = false;
                            if (data.id > 0) {
                                _this.form.findField('passwd1').allowBlank = true;
                                _this.form.findField('passwd2').allowBlank = true;
                            }

                        }
                        
                        
                        return;
                    } 
                    
                    if (act.type == 'submit') { // only submitted here if we are 
                        _this.dialog.hide();
                        
                        
                        
                        if (_this.callback) {
                            _this.callback.call(this, act.result.data);
                        }
                        if (_this.sendAfterSave) {
                            act.result.data.rawPasswd = _this.form.findField('passwd1').getValue();
                            _this.sendIntro([ act.result.data ], "Sending Welcome Message");
                        }
                        
                        return; 
                    }
                    // unmask?? 
                }
            }
         
             
        });
        this.loadItemTypes();
        Roo.each(this.itemList, function(il) {
            if (typeof(il) != 'object') {
                // no permission for Core offices.. - can not show department...
                if (il == 'office_id_name' && !Pman.hasPerm('Core.Offices','S')) {
                    return true;
                }
                
                _this.form.addxtype(_this.itemTypes[il]);
                return true;
            }
            _this.form.addxtype(Roo.apply(il, _this.itemTypes[il.name]));
            return true;
            
        });
        var ef = this.dialog.getLayout().getEl().createChild({tag: 'div'});
        ef.dom.style.margin = 10;
         
        this.form.render(ef.dom);

        var vp = this.dialog.getLayout().add('center', new Roo.ContentPanel(ef, {
            autoCreate : true,
            //title: 'Org Details',
            //toolbar: this.tb,
            width: 250,
            maxWidth: 250,
            fitToFrame:true
        }));
          

        
        
        this.layout.endUpdate();
 
    },
 
    
    
    loadItemTypes : function() 
    {
        var _this = this;
        this.itemTypes =   {
            company_id_name_ro : {
                    name : 'company_id_name',
                    fieldLabel : "Company",
                    value : '',
                    xtype : 'TextField',
                    readOnly : true,
                    width : 300
            },
            
            company_id_name : {
                
                xtype: 'ComboBoxAdder',
                fieldLabel: "Company",
                name : 'company_id_name',
                selectOnFocus:true,
                qtip : "Select Company",
                allowBlank : false,
                width: 300,
                
                store: {
                    xtype : 'Store',
                      // load using HTTP
                    proxy:{
                        xtype:  'HttpProxy',
                        url: baseURL + '/Roo/core_company',
                        method: 'GET'
                    },
                    reader: new Roo.data.JsonReader({}, []), //Pman.Readers.Companies,
                    listeners : {
                        beforeload : function(st,o)
                        {
                        
                            o.params['!comptype'] = 'OWNER';
                        },
                        loadexception : Pman.loadException
                    
                    },
                    sortInfo: {
                        field: 'name', direction: 'ASC'
                    }
                },
                displayField:'name',
                valueField : 'id',
                hiddenName:  'company_id',
                typeAhead: true,
                forceSelection: true,
                //mode: 'local',
                triggerAction: 'all',
                tpl: new Roo.Template(
                    '<div class="x-grid-cell-text x-btn button">',
                        '<b>{name}</b> {address}',
                    '</div>'
                ),
                queryParam: 'search[name_starts]',
                loadingText: "Searching...",
                listWidth: 400,
               
                minChars: 2,
                pageSize:20,
                listeners : {
                    adderclick : function()
                    {
                        var cb = this;
                        Pman.Dialog.CoreCompanies.show( {  id: 0 },  function(data) {
                            cb.setFromData(data);
                        }); 
                    }
                }
               
                 
                 
                 
            },
            office_id_name_ro : {
                    name : 'office_id_name',
                    fieldLabel : "Office",
                    value : '',
                    xtype : 'TextField',
                    readOnly : true,
                    width : 300
            },
            
            office_id_name : {
                
                xtype: 'ComboBoxAdder',
                fieldLabel: "Office / Department",
                name : 'office_id_name',
                selectOnFocus:true,
                qtip : "Select Office",
                allowBlank : true,
                width: 300,
                
                store:  {
                    xtype : 'Store',
                      // load using HTTP
                    proxy: {
                        xtype : 'HttpProxy',
                        url: baseURL + '/Roo/Core_office.html',
                        method: 'GET'
                    },
                    reader: new Roo.data.JsonReader({}, []), //Pman.Readers.Office,
                    listeners : {
                        beforeload : function(st,o)
                        {
                            // compnay myst be set..
                            var coid = _this.form.findField('company_id').getValue();
                            o.params.company_id = coid;
                        },
                        loadexception : Pman.loadException
                    
                    },
                    sortInfo: {
                        field: 'name', direction: 'ASC'
                    }
                },
                listeners : {
                    adderclick : function()
                    {
                        var cb = this;
                        
                        // for new - we have
                        var cfg = false;
                        var data = false;
                        if (_this.type == 'new') {
                            data = _this.form.findField('company_id').lastData;
                            if (!data.id ) {
                                Roo.MessageBox.alert("Error", "Select An Company First");
                                return false
                            }
                            
                            cfg = {
                                company_id : data.id ,
                                company_id_name: data.name,
                                address: data.address,
                                phone: data.tel,
                                fax: data.fax,
                                email: data.email
                            };

                        } else { // all other tyeps have the data in the caller data array.
                            data  = _this.data;
                            cfg = {
                                company_id : data.company_id,
                                company_id_name: data.company_id_name,
                                address: data.company_id_address,
                                phone: data.company_id_tel,
                                fax: data.company_id_fax,
                                email: data.company_id_email
                            }
                        }
                        
                         
                        
                        
                        Pman.Dialog.Office.show(cfg, function(data) {
                                cb.setFromData(data);
                        }); 
                    },
                    beforequery : function (qe) {
                        var coid = _this.form.findField('company_id').getValue();
                        if (coid < 1 ) {
                            Roo.MessageBox.alert("Error", "Select An Company First");
                            return false;
                        }
                    }
                },
                displayField:'name',
                valueField : 'id',
                hiddenName:  'office_id',
                typeAhead: true,
                forceSelection: true,
                //mode: 'local',
                triggerAction: 'all',
                tpl: new Roo.Template(
                    '<div class="x-grid-cell-text x-btn button">',
                        '<b>{name}</b> {address}',
                    '</div>'
                ),
                queryParam: 'query[name]',
                loadingText: "Searching...",
                listWidth: 400,
               
                minChars: 2,
                pageSize:20 
                 
                 
            },
            name : {
                name : 'name',
                fieldLabel : "Contact Name",
                value : '',
                allowBlank : false,
                qtip : "Enter name",
                xtype : 'TextField',
                width : 300
            },
            role : {
                name : 'role',
                fieldLabel : "Role / Position",
                value : '',
                allowBlank : true,
                qtip : "Enter Role / Position",
                xtype : 'TextField',
                width : 300
            },

            phone : {
                name : 'phone',
                fieldLabel : "Phone",
                value : '',
                allowBlank : true,
                qtip : "Enter phone Number",
                xtype : 'TextField',
                width : 300
            },
            fax : {
              
                name : 'fax',
                fieldLabel : "Fax",
                value : '',
                allowBlank : true,
                qtip : "Enter fax",
                xtype : 'TextField',
                width : 300
            },
            email : {
                name : 'email',
                fieldLabel : "Email",
                value : '',
                allowBlank : true,
                qtip : "Enter email",
                xtype : 'TextField',
                width : 300
            },
            bulklist: {
                name : 'bulklist',
                fieldLabel : "Email address (one per line)",
                value : '',
                allowBlank : false,
                qtip : "Enter email addresse",
                xtype : 'TextArea',
                width : 300,
                height:  200
            },
            
            
            email_req : {
                name : 'email',
                fieldLabel : "Email",
                value : '',
                allowBlank : false,
                qtip : "Enter email",
                xtype : 'TextField',
                width : 300
            },
            countries : {
                xtype: 'ComboBoxArray',
                xns: Roo.form,
                fieldLabel : 'Country',
                hiddenName : 'countries',
                name : 'countries_name',
                width : 300,
                combo : {
                    xtype: 'ComboBox',
                    xns: Roo.form,
                    allowBlank : true,
                    alwaysQuery : true,
                    displayField : 'title',
                    editable : false,
                    emptyText : "Select Country",
                    forceSelection : true,
                    idField : 'id',
                    triggerAction : 'all',
                    typeAhead : true,
                    valueField : 'code',
                    width : 280,
                    store : {
                        xtype: 'SimpleStore',
                        xns: Roo.data,
                        data : (function() {
                            return Pman.I18n.simpleStoreData('c');
                        })(),
                        fields : [  'code', 'title' ]
                    }
                }
            },
            passwd1 : {
                name : 'passwd1',
                fieldLabel : "New Password ",
                value : '',
                allowBlank : true, // must be filled in as we rely on it for login details..
                inputType: 'password', // << if comment out this input type, it should be ok
                xtype : 'SecurePass',
                width : 220,
                imageRoot : rootURL + '/Pman/templates/images'
            },
            passwd2 : {
                
                name : 'passwd2',
                fieldLabel : "Password (type again to confirm)",
                value : '',
                allowBlank : true, // must be filled in as we rely on it for login details..
                inputType: 'password', // << if comment out this input type, it should be ok
                xtype : 'TextField',
                width : 220
            },
            secure_password : {
                name : 'secure_password',
                fieldLabel : "Secure passwords",
                inputValue : 1,
                valueOff : 0,
                checked : true,
                xtype : 'Checkbox',
                xns : Roo.form,
                width : 220,
                listeners : {
                    check : function (_self, checked) {
                        this.form.findField('passwd1').insecure = false;
                        
                        if(!checked){
                            this.form.findField('passwd1').insecure = true;
                        }
                    }
                }
            },
            project_id_fs : {
                xtype : 'FieldSetEx',
                name: 'project_id_fs',
                value: 0,
                labelWidth: 100,
                expanded: false,
                style: 'width:420px;',
                legend : "Always File Messages from this Person in Project",
                items : [
                    Pman.Std.project_id({
                        width: 300,
                        fieldLabel : "Project",
                        allowBlank : true
                    }),
                    {
                      xtype: 'ComboBox',
                        name : 'action_type_str',
                        selectOnFocus:true,
                        qtip : "Action Type",
                        fieldLabel : "Action Required",

                        allowBlank : true,
                        width: 50,
                        
                        
                        store: new Roo.data.SimpleStore({
                              // load using HTTP
                            fields: [ 'code', 'desc' ],
                            data:  [[ 'ACTION_REQUIRED', "Yes"] , [ 'NOTIFY', "No"] ]
                        }),
                        displayField:'desc',
                        editable : false,
                        valueField : 'code',
                        hiddenName:  'action_type',
                        value : 'ACTION_REQUIRED',
                        forceSelection: true,
                        mode: 'local',
                        triggerAction: 'all' 
                       // queryParam: 'query[project]',
                       // loadingText: "Searching...",
                        //listWidth: 400
                       
                         
                       
                    }
                ]
            },
            
            id : { name : 'id', value : '', xtype : 'Hidden' },
            save_send : { name : '_save_send', value : 0, xtype : 'Hidden' },
            active : { name : 'active', value : 1, xtype : 'Hidden' },
            company_id : { name : 'company_id', value : '', xtype : 'Hidden' },
            company_id_email : { name : 'company_id_email', value : '', xtype : 'Hidden' },
            company_id_address : { name : 'company_id_address', value : '', xtype : 'Hidden' },
            company_id_tel : { name : 'company_id_tel', value : '', xtype : 'Hidden' },
            company_id_fax : { name : 'company_id_fax', value : '', xtype : 'Hidden' },
            project_id_addto : { name : 'project_id_addto', value : '', xtype : 'Hidden' }
        };
    
    }, //end getItemTypes
    
    saveSend : function(bt, e)
    {
        this.save(bt,e, 1);
    },
    sendAfterSave : 0,
    save : function(bt, e, andsend)
    {
        // ensure a company has been selected..
        this.sendAfterSave  = andsend || 0;
        
        if (this.form.findField('bulklist')) {
            this.saveBulk();
            return;
            
        }
        if (this.form.findField('company_id') && !this.form.findField('company_id').getValue()) {
            Roo.MessageBox.alert("Error", "Select a Company");
            return;
        }
        
        if (this.form.findField('passwd1')) {
            
            var p1 = this.form.findField('passwd1').getValue();
            var p2 = this.form.findField('passwd2').getValue();
            
            if (this.sendAfterSave && !p1.length) {
                Roo.MessageBox.alert("Error", "You must create a password to send introduction mail");
                return;
            }
            
            if (Pman.Login.authUser.id < 0 && !p1.length) {
                Roo.MessageBox.alert("Error", "You must create a password for the admin account");
                return;
            }
            
            
            if (p1.length || p2.length) {
                if (p1 != p2) {
                    Roo.MessageBox.alert("Error", "Passwords do not match");
                    return;
                }
            }
            
        
        }
        // ensure it's blank!
        if (this.form.findField('project_id')) {
            if (!this.form.findbyId('project_id_fs').expanded) {
                this.form.findField('project_id').setFromData({
                    id : 0,
                    code : ''
                });
            }
        }
        this.dialog.el.mask("Sending");
        this.form.doAction('submit', {
            url: baseURL + '/Roo/core_person',
            method: 'POST',
            params: {
                _id: this._id ,
                ts : Math.random()
            } 
        });
    },

    
     
    show: function (data, callback)
    {
        
        this.callback = callback;
        this._id = data.id;
        this.data = data;
        this.create();
        this.form.reset();
        if ( this._id) {
            this.dialog.show();
            this.dialog.el.mask("Loading");
            this.form.doAction('load', {
                url: baseURL + '/Roo/core_person',
                method: 'GET',
                params: {
                    _id: this._id ,
                    _ts : Math.random()
                } 
            });
           // this.fireEvent('show');
            return;
        }
        //} else {
        this.form.setValues(data);
        //}
        this.form.fireEvent('actioncomplete', this.form,{
            type : 'setdata',
            data: data
        });
         
        this.dialog.show();
        // no need to load...

    },
    
    saveBulk: function() {
        // similar action to SendIntro
        // we build a fake list of data..
        if (!this.form.findField('company_id').getValue()) {
            Roo.MessageBox.alert("Error", "Select the Company Name");
            return;
        }
        // prompt..
        var adr = [];
        var _this = this;
        
        Roo.MessageBox.confirm("Send Welcome", "Send Welcome Messages and Generate Passwords?",
            function(yn) {
                var pw = 1;
                //console.log(yn);
                if (yn != 'yes') {
                    pw = 0;
                }
                Roo.each(_this.form.findField('bulklist').getValue().split("\n"), function(v) {
                    if (!v.length || !v.replace(new RegExp(' ', 'g'), '').length) {
                        return;
                    }
                    adr.push({
                        id:  0,
                        email : v,
                        company_id : _this.form.findField('company_id').getValue(),
                        office_id  : _this.form.findField('office_id').getValue(),
                        active : 1,
                        _create : 1,
                        _createPasswd : pw
                        
                    })
                });
                if (!adr.length) {
                    Roo.MessageBox.alert("Error", "No addresses found");
                    return;
                }
                _this.dialog.hide();
                _this.sendIntro(adr, "Creating Account / Sending Welcome", _this.callback)
            }
        );
        
        
        
        
      
      
    },
    
    
    sendIntro  : function(ar, msg, callback) {
        // can hanlde multiple items -- will be usefull for later usage
        // when we do list of multiple users..
        var i =0;
        
        Roo.MessageBox.show({
           title: "Please wait...",
           msg: msg,
           width:350,
           progress:true,
           closable:false
        });
        
        //this.sendData = ar; console.log(ar);
        var _this = this;
        var wis = function () 
        {
            if (i == ar.length) {
                Roo.MessageBox.hide();
                Roo.MessageBox.alert("Done", "Done - " + msg);
                if (callback) {
                    callback.call(this, false);
                }
                return;
            }
            Roo.MessageBox.updateProgress( 
                (i+1)/ar.length,  msg + " : " + ar[i].email
            );
            
             
            var c = ar[i];
            i++;
            new Pman.Request({
                url : baseURL+'/Core/SendIntro.html',
                method : 'POST',
                params: c,
                success : function(resp, opts) {
                    wis();
                },
                failure: function()
                {
                    Roo.MessageBox.show({
                       title: "Please wait...",
                       msg: msg,
                       width:350,
                       progress:true,
                       closable:false
                    });
                    // error condition!?!?
                    wis();
                }
                
            });
            
        };
        wis();
        
        
        
    }
         
};