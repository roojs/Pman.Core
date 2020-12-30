//<script type="text/javascript">

/**
* 
* All Our standard form fields
* 
* should move this really..
* 
*/

Pman.Std = {
    project_id : function (cfg) {
        cfg = cfg || {};
        cfg.storeListeners = cfg.storeListeners || {};
        return  Roo.apply({
                
            width: 200,
            fieldLabel: "Project",
            name : 'project_id_code',
            hiddenName:  'project_id',
            
            allowBlank : false,
            selectOnFocus:true,
            qtip : "Select Project",
            
            
            
            xtype: 'ComboBox',
            
            store: {
                xtype : 'Store',
                  // load using HTTP
                proxy: {
                    xtype : 'HttpProxy',
                    url: baseURL + '/Roo/core_project',
                    method: 'GET'
                },
                reader: new Roo.data.JsonReader({}, []), //Pman.Readers.Projects,
                listeners : Roo.apply(
                    {
                        loadexception : Pman.loadException
                    }, 
                    cfg.storeListeners
                ),
                remoteSort : true,
                sortInfo: {
                    field: 'code', direction: 'ASC'
                }
            },
            displayField:'code',
            valueField : 'id',
            
            typeAhead: true,
            forceSelection: true,
            //mode: 'local',
            triggerAction: 'all',
            tpl: new Ext.Template(
                '<div class="x-grid-cell-text x-btn button">',
                    '<b>{code}</b> {name}',
                '</div>'
            ),
            queryParam: 'query[project_search]',
            loadingText: "Searching...",
            listWidth: 400,
           
            minChars: 2,
            pageSize:20 
             
        }, cfg);
   },
   
    
   company_id : function(cfg) { // really picks names...
        cfg = cfg || {};
        cfg.storeListeners = cfg.storeListeners || {};
        // we may want to set up cfg listners default here???
        cfg.listeners = cfg.listeners || {};
           
        return Roo.apply({
                // things we might want to change...
                
                name : 'addressto_name',
                displayField:'name',
                
                fieldLabel : "Sent To",
                 qtip : "Enter Sent To",
                width: 290,
                
                
                value : '',
                xtype: 'ComboBoxAdder',
                selectOnFocus:true,
                allowBlank : false,
                
               
                store: {
                      // load using HTTP
                    xtype: 'Store',
                    proxy: {
                        xtype : 'HttpProxy',
                        url: baseURL + '/Roo/core_company',
                        method: 'GET'
                    },
                    reader: Pman.Readers.Companies,
                    
                    listeners : Roo.apply(
                        {
                            loadexception : Pman.loadException
                        }, 
                        cfg.storeListeners
                    ),
                    remoteSort : true,
                    sortInfo: {
                        field: 'name', direction: 'ASC'
                    }
                    
                },
              
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
                pageSize:20 
                
            }, cfg);
    },
    
    doctype_name: function(cfg) {
        cfg = cfg || {};
        return Roo.apply({
                // things that might need chnaging
                name : 'doctype_name',
                width : 290,
                fieldLabel : "Type",
                allowBlank : false,
                
                // less likely
                qtip : "Select Document Type",
                
                value : '',
                // very unlinkly
                xtype : 'ComboBox',   
                store: {
                    // load using HTTP
                    xtype: 'Store',
                    proxy: {
                        xtype: 'HttpProxy',
                        url: baseURL + '/Roo/Document_Types.html',
                        method: 'GET'
                    },
                    
                    reader: Pman.Readers.Document_Types,
                    listeners : {
                        beforeload: function(t, o) {
                            //console.log(o.params);
                            o.params.limit = 9999;
                        },
                        loadexception : Pman.loadException
                
                    },
                    remoteSort: true,
                    sortInfo: {
                        field: 'name', direction: 'ASC'
                    }
                },
                displayField:'name',
                
                typeAhead: false,
                editable: false,
                //mode: 'local',
                triggerAction: 'all',
                //emptyText:'Select a state...',
                selectOnFocus:true 
            }, cfg);
    },
    
    
    address_list_adder : function(cfg) {
        cfg = cfg || {};
        cfg.storeListeners = cfg.storeListeners || {};
        return Roo.apply({
                
                name : 'send_to',
                fieldLabel : "To",
                idField : 'email',
                
                 renderer : function(d) {
                    return String.format('{0}', 
                        d.name.length ? d.name : d.email
                    );
                },
                
                
                xtype: 'ComboBoxLister',
                displayField:'name',
                value : '',
               
                qtip : "Select an address to add.",
                selectOnFocus:true,
                allowBlank : true,
                width: 150,
                
                
                store: {
                    xtype : 'Store',
                      // load using HTTP
                    proxy: {
                        xtype : 'HttpProxy',
                        url: baseURL + '/Roo/core_person',
                        method: 'GET'
                    },
                    reader: Pman.Readers.Companies,
                    listeners : cfg.storeListeners, 
                    remoteSort : true,
                    sortInfo: {
                        field: 'name', direction: 'ASC'
                    }
                },
               
                
                typeAhead: true,
                forceSelection: true,
                //mode: 'local',
                triggerAction: 'all',
                tpl: new Ext.Template(
                    '<div class="x-grid-cell-text x-btn button">',
                        '<b>{name}</b> {email}',
                    '</div>'
                ),
                queryParam: 'query[name]',
                loadingText: "Searching...",
                listWidth: 400,
               
                minChars: 2,
                pageSize:20,
                setList : function(ar) {
                    var _this = this;
                    Roo.each(ar, function(a) {
                        _this.addItem(a);
                    });
                },
                toList : function() {
                    var ret = [];
                    this.items.each(function(a) {
                        ret.push(a.data);
                    });
                    return ret;
                }
                
                 
            }, cfg);
    },
    
    
    our_office_id : function(cfg) 
    {
        cfg = cfg || {};
        cfg.listeners = cfg.listeners  || {};
        return Roo.apply({
            xtype: 'ComboBoxAdder',
            fieldLabel: "Office / Department",
            
            hiddenName:  'office_id',
            name : 'office_id_name',
            
            qtip : "Select Office",
            width: 300,
            allowBlank : true,
            triggerAction: 'all',
            
            
            typeAhead: true,
            forceSelection: true,
            selectOnFocus:true,
            
            displayField:'name',
            valueField : 'id',
            
            store:  {
                xtype : 'Store',
                  // load using HTTP
                proxy: {
                    xtype : 'HttpProxy',
                    url: baseURL + '/Roo/Office.html',
                    method: 'GET'
                },
                reader: Pman.Readers.Office,
                listeners : Roo.apply({
                    loadexception : Pman.loadException
                    }, cfg.storeListeners
                ),
                remoteSort : true,
                sortInfo: {
                    field: 'name', direction: 'ASC'
                }
            },
            listeners : Roo.apply({
                adderclick : function()
                {
                     
                    var ncfg = {
                        company_id : Pman.Login.authUser.company_id * 1,
                        company_id_name:  Pman.Login.authUser.company_id_name,
                        address: '',
                        phone: '',
                        fax: '',
                        email: ''
                    };

                    
                    Pman.Preview.tmpDisable();
                    
                    Pman.Dialog.Office.show(ncfg, function(data) {
                        _this.setFromData(data);
                        Pman.Preview.tmpEnable();
                    }); 
                } 
            }, cfg.listeners),
           
            //mode: 'local',
            
            tpl: new Ext.Template(
                '<div class="x-grid-cell-text x-btn button">',
                    '<b>{name}</b> {address}',
                '</div>'
            ),
            queryParam: 'query[name]',
            loadingText: "Searching...",
            listWidth: 400,
           
            minChars: 2,
            pageSize:20 
             
             
             
        }, cfg);
    },
    
    /**
     * Depreciated - use Pman.I18n directly
     * 
     */

    country: function(cfg) { return Pman.I18n.country(cfg); }, 
    language: function(cfg) { return Pman.I18n.language(cfg); }, 
       
    languageList : function(cfg) { return Pman.I18n.languageList(cfg); },
    countryList : function(cfg) { return Pman.I18n.countryList(cfg); }
     
        
};