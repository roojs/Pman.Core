//<script type="text/javascript">
  
Pman.Dialog.Projects = {
    dialog : false,
    form : false,
    create: function()
    {
        if (this.dialog) {
            return;
        }
        
        this.dialog = new Ext.LayoutDialog(Ext.get(document.body).createChild({tag:'div'}),  { 
            autoCreated: true,
            title: "Edit Project",
            modal: true,
            width:  450,
            height: 450,
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
        
        var dg = Pman.Dialog.Projects;
        
        this.form = new Ext.form.Form({
            labelWidth: 100 ,
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
        this.form.addxtype.apply(this.form,[
            
            {
                name : 'code',
                fieldLabel : "Code",
                value : '',
                allowBlank : false,
                qtip : "Enter Project Code",
                xtype : 'TextField',
                width : 100
            },
            {
                name : 'name',
                fieldLabel : "Project Name",
                value : '',
                allowBlank : true,
                qtip : "Enter Project Name",
                xtype : 'TextField',
                width : 300
            },
            {
                
                xtype: 'ComboBox',
                name : 'type_desc',
                selectOnFocus:true,
                qtip : "Project type",
                fieldLabel : "Project type",

                allowBlank : false,
                width: 200,
                
                
                store: new Ext.data.SimpleStore({
                      // load using HTTP
                    fields: [ 'code', 'desc' ],
                    data:  Pman.Dialog.Projects.getTypes()
                }),
                displayField:'desc',
                editable : false,
                valueField : 'code',
                hiddenName:  'type',
                typeAhead: true,
                forceSelection: true,
                mode: 'local',
                triggerAction: 'all' 
               // queryParam: 'query[project]',
               // loadingText: "Searching...",
                //listWidth: 400
               
                 
               
            },
             // CLIENT picklist.
             {
                
                xtype: 'ComboBoxAdder',
                fieldLabel: "Client",
                name : 'client_id_name',
                selectOnFocus:true,
                qtip : "Select Client",
                allowBlank : true,
                width: 277,
                
                store: new Ext.data.Store({
                      // load using HTTP
                    proxy: new Ext.data.HttpProxy({
                        url: baseURL + '/Roo/Companies.html',
                        method: 'GET'
                    }),
                    reader: Pman.Readers.Companies,
                    listeners : {
                        loadexception : Pman.loadException
                    }
                }),
                displayField:'name',
                valueField : 'id',
                hiddenName:  'client_id',
                typeAhead: true,
                forceSelection: true,
                //mode: 'local',
                triggerAction: 'all',
                tpl: new Ext.Template(
                    '<div class="x-grid-cell-text x-btn button">',
                        '<b>{name}</b> {address}',
                    '</div>'
                ),
                queryParam: 'query[name]',
                loadingText: "Searching...",
                listWidth: 400,
               
                minChars: 2,
                pageSize:20,
                 
                listeners : {
                    adderclick : function()
                    {
                        var cb = this;
                        Pman.Dialog.Companies.show( {  id: 0 },  function(data) {
                            cb.setFromData(data);
                        }); 
                    },
                    blur : function(f) {
                        if (!f.el.getValue().length) {
                            this.setFromData({
                                id: 0,
                                name : ""
                            });
                        }
                    }
                }

                  
            },
              // TEAM: picklist
            {
                
                xtype: 'ComboBox',
                fieldLabel: "Team",
                name : 'team_id_name',
                selectOnFocus:true,
                qtip : "Select Team",
                allowBlank : true,
                width: 300,
                
                store: new Ext.data.Store({
                      // load using HTTP
                    proxy: new Ext.data.HttpProxy({
                        url: baseURL + '/Roo/Groups.html',
                        method: 'GET'
                        
                    }),
                    reader: Pman.Readers.Groups,
                    listeners : {
                        beforeload: function(g, o) {
                            o.params = o.params ? o.params : {};
                            o.params.type = 1;
                            o.params['query[group_pulldown]'] = 1;
                            
                        },
                        loadexception : Pman.loadException
                    
                    }
                }),
                displayField:'name',
                valueField : 'id',
                hiddenName:  'team_id',
                typeAhead: true,
                forceSelection: true,
                //mode: 'local',
                triggerAction: 'all' ,
                queryParam: 'query[name]',
                loadingText: "Searching...",
                //listWidth: 400,
               
                minChars: 2,
               // pageSize:20,
                
                listeners : {
                    blur : function(f) {
                        if (!f.el.getValue().length) {
                            this.setFromData({
                                id: 0,
                                name : ""
                            });
                        }
                    }
                }
                
                  
            },
         
            // Office (is related to team leader?!?)
            
            // Files stored:
            
            {
                name : 'file_location',
                fieldLabel : "File Location",
                value : '',
                qtip : "Where are the files stored?",
                allowBlank : true,
                xtype : 'TextField',
                width : 300
            },
            
            // things to go in..
            // Location (Files)
            // Client? - pick from contacts..
            // team? 
            // team leaders???
            // office in charge..
            // email list????? == oru project list..
            
            
            
         
            
            
            {
                name : 'remarks',
                fieldLabel : "Remarks",
                value : '',
                allowBlank : true,
                qtip : "Enter Project Remarks",
                xtype : 'TextArea',
                width : 300,
                height : 100
            },
                 // opened by?
            // opened date.. 
            {
                xtype : 'FieldSet',
                legend: 'Opened',
                style: 'width:393px;padding:0 0 2 10;',
                items : [
                    {
                        name : 'open_date',
                        fieldLabel : "Date",
                        value : '',
                        allowBlank : true,
                        qtip : "Enter Date Opened",
                        xtype : 'DateField',
                        altFormats : 'Y-m-d|d/m/Y',
                        width : 100,
                        format: 'd/m/Y'
                    },
                    
                    
                      
                    {
                        
                        xtype: 'ComboBox',
                        fieldLabel: "By",
                        name : 'open_by_name',
                        selectOnFocus:true,
                        qtip : "Select Person Who opened",
                        allowBlank : true,
                        width: 250,
                        
                        store: new Ext.data.Store({
                              // load using HTTP
                            proxy: new Ext.data.HttpProxy({
                                url: baseURL + '/Roo/Person.html',
                                method: 'GET'
                            }),
                            reader: Pman.Readers.Person,
                            listeners : {
                                beforeload : function(st,o)
                                {
                                    // compnay myst be set..
                                     
                                    o.params.company_id = Pman.Login.authUser.company_id * 1;
                                     
                                     
                                },
                                loadexception : Pman.loadException
                            
                            }
                        }),
                         
                        
                        displayField:'name',
                        valueField : 'id',
                        hiddenName:  'open_by',
                        typeAhead: true,
                        forceSelection: true,
                        doForce : function(){
                            if(this.el.dom.value.length > 0){
                                this.el.dom.value =
                                    this.lastSelectionText === undefined ? "" : this.lastSelectionText;
                                this.applyEmptyText();
                                if (!this.el.dom.value.length) {
                                    this.setFromData({  id: 0, name:  '----'  });
                                }
                            }
                        },

                        //mode: 'local',
                        triggerAction: 'all',
                        tpl: new Ext.Template(
                            '<div class="x-grid-cell-text x-btn button">',
                                '<b>{name}</b> {role}',
                            '</div>'
                        ),
                        queryParam: 'query[name]',
                        loadingText: "Searching...",
                        listWidth: 300,
                       
                        minChars: 2,
                        pageSize:20 
                         
                    }
                ]
                 
            },
            {
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
    show : function(data, callback)
    {
        this.callback= callback;
        this._id = data.id;
        this.data = data;
        this.create();
        this.form.reset();
        this.form.setValues(data);
        if (data.id) {
             
            this.form.findField('client_id').setFromData({
                id: data.client_id,
                name: data.client_id_name
            });
            this.form.findField('team_id').setFromData({
                id: data.team_id,
                name: data.team_id_name
            });
            this.form.findField('open_by').setFromData({
                id: data.open_by,
                name: data.open_by_name
            });
        }
        this.dialog.show();
        

    },
    save : function()
    {
         this.form.doAction('submit', {
            url: baseURL + '/Roo/Projects.html',
            method: 'POST',
            params: {
                _id: this._id ,
                ts : Math.random()
            } 
        });
    },
    getTypes: function()
    {
 
        return [
            [  'U' , "Project (Unconfirmed)" ],
            [  'P' , "Project" ],
            [  'C' , "Project (Closed)" ],
            [  'N' , "Non-Project" ],
            [  'X' , "Non-Project (Closed)" ]
        
        ];
    },
    
    
    
         
};