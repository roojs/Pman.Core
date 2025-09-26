//<script type="text/javascript">

/**
 * 
 * generic person list - used by perms. and staff lists.
 *
 * -- this is probably depreciated now..
 * 
 */



Pman.Tab.PersonList = function(config)
{
    Roo.apply(this, config);
};

Pman.Tab.PersonList.prototype = {
    
    //--- things that should be set!!!!
    id : '',  // should be set to something!
    type : 0, // means!! = 0 = Groups (perms) 1= teams - loose grouping..
    title : false,
    hiddenColumns: false,  // lsit of cols to hide..
    itemDisplayName : false, /// eg "Staff Employees / Contacts etc."
    permName : 'Core.Person', // or 'Core.Staff'
    getLeftSelections : function() { return []; },
    hideDelete : false,
    
    // beforeload handler... -- override on extended versions..
    beforeload: function() {
        //console.log(o.params);
        // teams!?!
        alert('person list not configured');
        return false;
        /*
        var tms = _this.getLeftSelections();
        
        if (tms.length) {
            o.params['query[in_group]'] = tms[0].data.id;
        }
        o.params['query[name]'] = this.searchBox.getValue();
        o.params['query[type]'] = this.type; // group type..
        o.params['query[person_internal_only_all]'] = 1;
        o.params['query[person_inactive]'] = this.showInActive ? 0  : 1;
        */
        
    },
    
    columns : function()
    {
        alert('person list not configured');
        return false;
        /*return [
            this.c_name(),
            this.c_office_id_name(),
            this.c_role(),
            this.c_phone(),
            this.c_fax(),
            this.c_email(),
            this.c_active()
        ];
        */
    },
    
    dialog: function () {
        alert('person list not configured');
        return false;
       // return Pman.Dialog.PersonStaff;
    },
    bulkAdd : function() {
        //return Pman.Dialog.PersonBulkAdd
        return false;
    },
    newDefaults : function() {
        alert('person list not configured');
        return false;
        /*return {
            
            id : 0,
            company_id : Pman.Login.authUser.company_id,
            company_id_name : Pman.Login.authUser.company_id_name,
            company_id_address : Pman.Login.authUser.company_id_address,
            company_id_tel : Pman.Login.authUser.company_id_tel,
            company_id_fax : Pman.Login.authUser.company_id_fax
        };
        */
    },
         
    
    
    /// --- end extendable bits...
    
    
    parentLayout : false,
    showInActive : 0,  // toggle var for hiding and showing active staff..
    grid : false,
    panel : false,
    toolbar : false,
    paging:  false,
    tab: false,
    
    
    refreshWestPanel : function() /// used wher???
    {
        var actpan = this.parentLayout.getRegion('west').getActivePanel();
        if (actpan && actpan.controller) {
            actpan.controller.paging.onClick('refresh');
            return;
        }
        // depreciated..    
    
        if (!actpan || !actpan.id) {
            return;
        }
        Pman.Tab[actpan.id].refresh();
    },
    
    refresh: function(){
        if (!this.paging) {
            this.delayedCreate();
        }
        this.paging.onClick('refresh');
    },
    
    loadFirst: function(){
        if (!this.paging) {
            this.delayedCreate();
        }
        this.paging.onClick('first');
    },  
    
    
    
    add : function(parentLayout, region) {
        
        var _this = this;
        if (this.tab) {
            parentLayout.getRegion(region).showPanel(this.panel);
            return;
        }
        this.parentLayout = parentLayout;
        
        this.layout = new Roo.BorderLayout(
            parentLayout.getEl().createChild({tag:'div'}),
            {
               
                center: {
                    autoScroll:false,
                    hideTabs: true
                }
            }
        );



        this.tab = parentLayout.add(region,
            new Roo.NestedLayoutPanel(
                this.layout, {
                    title: this.title,
                    background: true,
                    controller : this
        }));

        this.tab.on('activate', function() {
            _this.delayedCreate();
           // _this.paging.onClick('refresh');
        });
    },
    delayedCreate : function () 
     
    {
        var _this = this;
        if (this.grid) {
            return;
        }
        
        var refreshPager = function() {
            _this.refresh();
        };
        this.layout.beginUpdate();
        
        var frm = this.layout.getRegion('center').getEl().createChild({tag:'div'});
        //this.grid = new Roo.grid.EditorGrid(frm,  {
        this.grid = new Roo.grid.Grid(frm,  {
                ddGroup: 'groupDD',
                //enableDrag: true,
                enableDrag: true,
                id: this.id + '-grid',
                ds:   new Roo.data.Store({
                    // load using HTTP
                    proxy: new Roo.data.HttpProxy({
                        url: baseURL + '/Roo/core_person',
                        method: 'GET'
                    }),
                    reader: new Roo.data.JsonReader({}, []),
                    remoteSort: true,
                    listeners : {
                        
                        beforeload: function(t, o) {
                            //console.log(o.params);
                            // teams!?!
                            return _this.beforeload(t,o);
                             
                            
                        },
                        loadexception : Pman.loadException,
                        update : function (_self, record, operation)
                        {
                            if (operation != 'commit') {
                                return;
                            }
                            // only used to change active status.
                            
                            new Pman.Request({
                                url : baseURL + '/Roo/core_person',
                                method :'POST',
                                params : {
                                    id : record.data.id,
                                    active: record.data.active
                                    
                                },
                                success : function() {
                                    // do nothing
                                },
                                failure : function() 
                                {
                                    Roo.MessageBox.alert("Error", "saving failed", function() {
                                        _this.grid.footer.onClick('first');
                                    });
                                }
                            });
                                
                            
                            
                        }
                    
                    },
                    sortInfo: {
                        field: 'name', direction: 'ASC'
                    }
                }),
                cm: new Roo.grid.ColumnModel(
                    this.columns()
                ),
                autoExpandColumn:  'name', // fixme!!!!
                clicksToEdit : 1,
                
                loadMask: true,

                listeners : {
                    rowdblclick : function(g, ri) {
                        var s = g.getDataSource().getAt(ri).data;
                        if (_this.dialog() && Pman.hasPerm(_this.permName, 'E')) {
                            _this.dialog().show(s,refreshPager);
                        }
                        
                        
                    },
                    cellclick : function (_self, rowIndex, columnIndex)
                    {   
                        var di = this.colModel.getDataIndex(columnIndex);
                        if (di != 'active') {
                            return;
                        }
                        
                        var rec = _this.grid.ds.getAt(rowIndex);
                        
                        rec.set('active', rec.data.active ? 0 : 1);
                        rec.commit();
                        
                        // only hide if viewing active members... 
                        if (!_this.showInActive) {
                            var el = Roo.select('.x-grid-row-selected').item(3);
                            el.addClass('x-grid-row-fadeout');
                            el.on('transitionend',function(){
                                _this.grid.ds.remove(rec);
                            },this,{single:true});
                        }
                        
                    }
                    
                }
                 
                 
        });
        this.panel  = this.layout.add('center',  new Roo.GridPanel(this.grid , {
                fitToframe: true,
                fitContainer: true,
                //background : false,
                id: this.id, 
                title: this.title || "Staff", 
                controller : this 
            })
        );
        this.grid.render();
        
        if (this.hiddenColumns) {
            var cm = this.grid.getColumnModel();
            Roo.each(this.hiddenColumns, function(c) {
                cm.setHidden(cm.getIndexByDataIndex(c), true);
            });
        }
        

        
        var gridFoot = this.grid.getView().getFooterPanel(true);
        this.paging = new Roo.PagingToolbar(gridFoot, this.grid.getDataSource(), {
            pageSize: 25,
            displayInfo: true,
            displayMsg: "Displaying " + (this.itemDisplayName || "Staff") + " {0} - {1} of {2}",
            emptyMsg: "No " + (this.itemDisplayName || "Staff") + " found"
        });
        var grid = this.grid;
 
    
        this.toolbar = new Roo.Toolbar(this.grid.getView().getHeaderPanel(true));
        
        var tb = this.toolbar;
        
        
        if (this.parentLayout.getRegion('west') && this.parentLayout.getRegion('west').panels.length) {
                
            this.paging.add( 
                '<b><i><font color="red">'+ 
                    (this.type ?
                        "Drag person to add or remove from group" :
                        "Drag person to add or remove from team"
                    ) +
                '</font></i></b>'
            );
        }
        
        
        //if (this.permName == 'Core.Staff') {
                
            this.paging.add( '-',
                {
                    text: "Show old staff",
                    pressed: false,
                    enableToggle: true,
                    toggleHandler: function(btn,pressed) {
                        _this.showInActive = (pressed ? 1 : 0);
                        btn.setText(pressed ? "Hide old staff": "Show old staff" );
                        refreshPager();
                    }
                }, 
                
               
                '-'
            );
        //}
        
     
        this.searchBox = new Roo.form.TextField({
            name: 'search',
            width:135,
            listeners : {
                specialkey : function(f,e)
                {
                    
                    if (e.getKey() == 13) {
                        
                        refreshPager();
                    } 
                   
                
                }
            }
         
        });
        var dg = _this.dialog();
        tb.add(
            {
                text: "Add",
                cls: 'x-btn-text-icon',
                icon: Roo.rootURL + 'images/default/dd/drop-add.gif',
                hidden :  !dg || (_this.newDefaults() === false) || !Pman.hasPerm(this.permName, 'A'),  
                handler : function(){
                    dg.show(  _this.newDefaults(), refreshPager );  
                }
            }, 
             { ///... for contacts stuff...
                text: "Bulk Add",
                cls: 'x-btn-text-icon',
                icon: Roo.rootURL + 'images/default/dd/drop-add.gif',
                hidden : !this.bulkAdd() || !Pman.hasPerm(this.permName, 'A'),    
                handler : function(){
                    
                   // Pman.Dialog.PersonBulkAdd.show( {  id : 0 }, refreshPager ); 
                   _this.bulkAdd().show( {  id : 0 }, refreshPager ); 
                }
            },

            {
                text: "Edit",
                cls: 'x-btn-text-icon',
                icon: Roo.rootURL + 'images/default/tree/leaf.gif',
                hidden : !dg || !Pman.hasPerm(this.permName, 'E'),    
                handler : function(){
                    var s = grid.getSelectionModel().getSelections();
                    if (!s.length || (s.length > 1))  {
                        Roo.MessageBox.alert("Error", s.length ? "Select only one Row" : "Select a Row");
                        return;
                    }
                    dg.show( s[0].data,refreshPager);
                 }
            }, 
         /*   {
                text: "Toogle Active",
                cls: 'x-btn-text-icon',
                icon:   rootURL + '/Pman/templates/images/trash.gif',
                hidden : (this.permName != 'Core.Staff') || !Pman.hasPerm(this.permName, 'E'),   // SPECIFIC TO STAFF!!!!!!
                handler : function(){
                 
                    var s = grid.getSelectionModel().getSelections();
                    if (!s.length  )  {
                        Roo.MessageBox.alert("Error",  "Select People Row");
                        return;
                    }
                    var r = [];
                    for(var i = 0; i < s.length; i++) {
                        r.push(s[i].data.id);
                    }
                
                
                
                    grid.getView().mainWrap.mask("Sending");

                    
                    Roo.Ajax.request({
                        url: baseURL + '/Roo/core_person',
                        method: 'GET',
                        params: {
                            _toggleActive : r.join(',')
                        },
                        success: function(resp) {
                            var res = Pman.processResponse(resp);
                            grid.getView().mainWrap.unmask();
                            if (!res.success) {
                                Roo.MessageBox.alert("Error", res.errorMsg ? res.errorMsg  : "Error Sending");
                                return;
                            }
                            refreshPager();
                            
                        },
                        failure: function(act) {
                            grid.getView().mainWrap.unmask();
                            Roo.MessageBox.alert("Error", "Error Sending");
                        }
                        
                    });
                }
                
            }, 
            */
            {
                text: "Delete",
                cls: 'x-btn-text-icon',
                hidden : !Pman.hasPerm(_this.permName, 'D'),    
                icon: rootURL + '/Pman/templates/images/trash.gif',
                handler : function(){
                    //Pman.genericDelete(_this, 'Person'); 
                    
                    var rec = _this.grid.ds.getAt(_this.grid.selModel.last);
                    
                    var rec_id = rec.id;
                    
                    if (rec_id * 1 < 1) {
                        Roo.MessageBox.alert("Error", "Select row to delete");
                        return;
                    }

                    Roo.MessageBox.confirm(
                        "Confirm", 
                        "Confirm Deletion of selected row (some rows can not be deleted if they are referenced elsewhere", 
                        function(res) {
                            if(res != 'yes') {
                                return;
                            }
                            new Pman.Request({
                                method : 'POST',
                                url : baseURL + '/Roo/core_person',
                                params : {
                                    _delete  : rec_id
                                },
                                success : function() {
                                    _this.paging.onClick('refresh');
                                    //_this.grid.footer.onClick('refresh');
                                }
                            });
                        }
                    );


                    

                }
            } ,

           
            '-',
            'Search: ',
             
            this.searchBox,
        
            {
                
               
                icon: rootURL + '/Pman/templates/images/search.gif', // icons can also be specified inline
                cls: 'x-btn-icon',
                qtip: "Search",
                handler : function () { 
                    _this.grid.getSelectionModel().clearSelections();

                    refreshPager();
                }
            },   
             {
                
               
                icon: rootURL + '/Pman/templates/images/edit-clear.gif', // icons can also be specified inline
                cls: 'x-btn-icon',
                qtip: "Reset Search",
                handler : function () {
                    _this.searchBox.setValue('');
                    _this.grid.getSelectionModel().clearSelections();
                    
                    refreshPager();
                }
            },
            '-',
             {
               
                xtype : 'Button',
                xns : Roo.Toolbar,
               
                text: "Switch to Selected User",
                hidden : _this.permName != 'Core.Staff' || !Pman.hasPerm('Core.Staff', 'E'),
                listeners : {
                    click : function () { 
                        var s = grid.getSelectionModel().getSelections();
                        if (s.length != 1)  {
                            Roo.MessageBox.alert("Error",  "Select a Person");
                            return;
                        }
                        var has_crypto = document.location.protocol == 'https:' || (
                            document.location.protocol == 'http:'  && document.location.hostname == 'localhost'    
                        );
                        if (Pman.Login.window_id === false && has_crypto) {
                            // persitant in windows..
                            Pman.Login.window_id = window.sessionStorage.getItem('windowid');
                            if (!Pman.Login.window_id) {
                                Pman.Login.window_id = crypto.randomUUID();
                                window.sessionStorage.setItem('windowid', Pman.Login.window_id);               
                            }
                        }

                        new Pman.Request({
                            url : baseURL+ '/Core/Auth/Switch',
                            params  :{
                                user_id : s[0].data.id,
                                window_id: Pman.Login.window_id
                            },
                            
                            method : 'POST',
                            success : function() {
                                document.location = baseURL + '?ts=' + Math.random();
                                
                            }
                        });
                        
                        
                    }
                }
            
                     
            },
            '->',
            
              {
               
                xtype : 'Button',
                xns : Roo.Toolbar,
               
                text: "Bulk Change Passwords",
                hidden : _this.permName != 'Core.Staff' || !Pman.hasPerm('Core.Staff', 'E'),
                listeners : {
                    click : function () {
                        Pman.Dialog.AdminBulkPassword.show({}, function() { 
                          refreshPager();
                        });
                        
                    }
                }
            
                     
            }

        );
        
            
        //this.toolbar = tb;
        // add stuff to toolbar?
        //this.innerLayout.endUpdate();
         this.layout.endUpdate();

        
        
    },
    /*
    show: function (parentLayout, region)
    {
        this.add(parentLayout, region);
        this.grid.getDataSource().load({
            params: {
                start:0, 
                limit:25
            }
        });

    },
    */
    
    c_project_id_code : function(cfg) {
        cfg = cfg || {};
        return Roo.apply({               
            header : "Project",
            dataIndex : 'project_id_code',
            sortable : false,
            width : 70,
            renderer : function(v,x,r) {
                return String.format('<span qtip="{0}">{1}</span>', 
                    r.data.action_type,
                    v);
            }
        },cfg);
    },

    
    
    
    c_name : function(cfg) {
        cfg = cfg || {};
        return Roo.apply({
         //   id : (this.id + '-name').toLowerCase(),
            header : "Name",
            dataIndex : 'name',
            sortable : true,
            renderer : function(v,p,r) { 
                if(r.data.active != 1){
                    return String.format('<div style="text-decoration:line-through">{0}</div>', v); 
                }
                return String.format('{0}', v); 
            }
          //  width : 150  
        }, cfg);
    },
    
    c_group_membership : function(cfg) {
        cfg = cfg || {};
        return Roo.apply({
         //   id : (this.id + '-name').toLowerCase(),
            header : "Group Membership",
            dataIndex : 'member_of',
            sortable : false,
            renderer : function(v,p,r) {
                if(r.data.active != 1){
                    return String.format('<div style="text-decoration:line-through">{0}</div>', v).split("\n").join("<br/>"); 
                }
                return String.format('{0}', v).split("\n").join("<br/>"); 
            },
            width : 150  
        }, cfg);
    },
    
     c_company_id_comptype : function(cfg) {
        cfg = cfg || {};
        return Roo.apply({
            header : "Company Type",
            dataIndex : 'company_id_comptype',
            sortable : true,
            width : 70
        }, cfg);
    },
    
    c_company_id_name : function(cfg) {
        cfg = cfg || {};
        return Roo.apply({
            header : "Company / Office",
            dataIndex : 'company_id_name',
            sortable : true,
            width : 150,
            renderer: function(v,x,r) {
                return String.format('{0}{1}{2}', 
                    v,
                    r.data.office_id ? ' / ' : '',
                    r.data.office_id_name);
            }

        }, cfg);
    },
    
    c_office_id_name : function(cfg) {
        cfg = cfg || {};
        return Roo.apply({
            header : "Office / Dept.",
            dataIndex : 'office_id_name',
            sortable : true,
            width : 150  
        }, cfg);
        
    },
    c_role : function(cfg) {
        cfg = cfg || {};
        return Roo.apply({
            header : "Role / Position",
            dataIndex : 'role',
            sortable : true,
            width : 100
        }, cfg);
        
    },
    c_phone : function(cfg) {
        cfg = cfg || {};
        return Roo.apply({
            header : "Phone",
            dataIndex : 'phone',
            sortable : true,
            width : 70
        }, cfg);
        
    },
    c_fax : function(cfg) {
        cfg = cfg || {};
        return Roo.apply({
            header : "Fax",
            dataIndex : 'fax',
            sortable : true,
            width : 70
        }, cfg);
        
    },
    c_email : function(cfg) {
        cfg = cfg || {};
        return Roo.apply({
            header : "Email",
            dataIndex : 'email',
            sortable : true,
            width : 150,
            renderer : function (v) {
                return (v.length && v.indexOf('@') > 0 ) ? 
                    String.format('<a href="mailto:{0}">{0}</a>',v) : v;
            }
        }, cfg);
        
    },
    c_active : function(cfg) {
        cfg = cfg || {};
        return Roo.apply({
            header : "Active",
            dataIndex : 'active',
            sortable : true,
            width : 50,
            renderer : function(v) {
                // work out what the column is..
                
                var state = v> 0 ?  '-checked' : '';

                return '<img class="x-grid-check-icon' + state + '" src="' + Roo.BLANK_IMAGE_URL + '"/>';
                
                
            }

        }, cfg);
        
    }
     
    
    
};
// need two version of this 
// (one can be used as edit + ProjectDirectory ADD)
// - the other one needs selection combos's for company / office


