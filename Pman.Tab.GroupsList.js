
//<script type="text/javascript">

// generic groups listing tab


Pman.Tab.GroupsList = function(config) {
    Ext.apply(this, config);
};

Pman.Tab.GroupsList.prototype = {
    id : false,
    grid : false,
    panel : false,
    getDialog : false,
    title : false,
    type : 0,
    disabled : false,
    add : function(parentLayout, region) {
        
        var _this = this;
        
        var refreshPager = function() {
            _this.refresh();
        }
        
        if (this.panel) {
            parentLayout.getRegion(region).showPanel(this.panel);
            return;
        }
          
        var refreshCenterPanel = function()
        {
            var actpan = parentLayout.getRegion('center').getActivePanel();
            if (actpan && actpan.controller) {
                actpan.controller.refresh();
                return;
            }
            // depreciated..
            var agid = actpan.id;
            if (!agid) {
                return;
            }
            Pman.Tab[agid].refresh();
        }
        
        var frm = parentLayout.getRegion(region).getEl().createChild({tag:'div'});
        //this.grid = new Ext.grid.EditorGrid(frm,  {
        this.grid = new Ext.grid.Grid(frm,  {
                id: _this.id + '-groups',
                
                //enableDragDrop:true,
                enableDrop:true,
                ddGroup: 'groupDD',
                
                //dropConfig: {
                //    appendOnly : true,
                //    ddGroup: 'groupDD' 
                //},
                ds:   new Ext.data.Store({
                    // load using HTTP
                    
                    
                    proxy: new Ext.data.HttpProxy({
                        url: baseURL + '/Roo/Groups.html',
                        method: 'GET'
                    }),
                    remoteSort: true,
                    reader: Pman.Readers.Groups,
                    sortInfo: {
                        field: 'name', direction: 'ASC'
                    },
                    listeners : {
                        
                        beforeload: function(t, o) {
                            //console.log(o.params);
                            if (!o.params) {
                                o.params = {};
                            }
                            o.params.type = _this.type;
                            
                            
                        },
                        load : function()
                        {
                            var sm = _this.grid.getSelectionModel();
                            if (!sm.getSelections().length) {
                                sm.selectFirstRow();
                            }
                            refreshCenterPanel();
                          //  Pman.Tab.Documents_In.delayedCreate();
                          //  Pman.Tab.Documents_Out.delayedCreate();
                        },
                        loadexception : Pman.loadException
                    
                    }
                }),
                sm: new Ext.grid.RowSelectionModel({ singleSelect: true }),
                cm: new Ext.grid.ColumnModel(
                    [{
                        id : _this.id + '-name',
                        header : "Name",
                        dataIndex : 'name',
                        sortable : true,
                        width : 100,
                        renderer : function(v,x,r) {
                            if (r.data.id == -1) {
                                return '<b>' + "Not in a Group" + '</b>';
                            }
                            if ((r.data.id == 0) && (_this.type == 0)) {
                                return '<b>' + "All Staff (Default Permissions)" + '</b>';
                            }
                            if ((r.data.id == 0) && (_this.type == 2)) {
                                return '<b>' + "Everybody" + '</b>';
                            }
                            if (r.data.id == 0) {
                                return '<b>' + "All Staff" + '</b>';
                            }
                            if (v == 'Administrators') {
                                return '<b>' + "Adminstrators" + '</b>';
                            }
                            if (r.data.leader) {
                                return v + ' (' + r.data.leader_name + ')';
                            }
                            
                            return v;
                            /*
                            switch (v) {
                                case 'Default':
                                    return '<b>' + "All Staff (Default Perms.)" + '</b>';
                                case 'Administrators':
                                    return '<b>' + "Administrators" + '</b>';
                                default: 
                                    return v;
                            }
                            */
                            
                       }
                    }]
                ),
                autoExpandColumn: _this.id + '-name' , // fixme!!!!
                clicksToEdit : 1,
                
                loadMask: true,
                listeners : {
                    rowclick: function(g, ri, e)
                    {
                        refreshCenterPanel();
                    } 
                }
                 
        });
        // add selection changed...
        
        this.panel  = parentLayout.add(region,  new Ext.GridPanel(this.grid ,
            { fitToframe: true,fitContainer: true, title: _this.title, id : _this.id, background: true})
        );
        this.grid.render();
        
        
        new Ext.dd.DropTarget(_this.grid.getView().mainBody, {  
            ddGroup : 'groupDD',  
            copy       : true,

            notifyOver : function(dd, e, data){  
                var t = Roo.lib.Event.getTarget(e); 
                var ri = _this.grid.view.findRowIndex(t);
                var rid  = false;
                if (ri !== false) {
                    rid = _this.grid.getDataSource().getAt(ri).data;
                }
                
                var s = _this.grid.getSelectionModel().getSelections();
                
                var isFromGroup = s.length ? s[0].data.id > 0 : false;
                
                var isToGroup = rid && rid.id > 0;
                
                if (isFromGroup && isToGroup) {
                    return this.dropNotAllowed; 
                }
                if (!isFromGroup && !isToGroup) {
                    return this.dropNotAllowed; 
                }
                if (isFromGroup && !isToGroup) {
                    return 'x-dd-drop-ok-sub'; 
                } 
                //if (!isFromGroup && isToGroup) {
                    return 'x-dd-drop-ok-add'; 
                //}
                
                  
            },  
            notifyDrop : function(dd, e, data){  
                
                var t = Roo.lib.Event.getTarget(e); 
                var ri = _this.grid.view.findRowIndex(t);
                var rid  = false;
                if (ri !== false) {
                    rid = _this.grid.getDataSource().getAt(ri).data;
                }
                var s = _this.grid.getSelectionModel().getSelections();
                  
                //console.log(data);
                var isFromGroup = s.length ? s[0].data.id > 0 : false;
                
                var isToGroup = rid && rid.id > 0;
                
                if (isFromGroup && isToGroup) {
                    return false;
                }
                if (!isFromGroup && !isToGroup) {
                    return false;
                }
                var action = 'add';
                if (isFromGroup && !isToGroup) {
                    action = 'sub';
                    //return 'x-dd-drop-ok-sub'; 
                }
                // build a list of selections.
                var sels = [];
                for (var i=0; i < data.selections.length; i++) {
                    sels.push(data.selections[i].data.id);
                }
                
                new Pman.Request({
                    url: baseURL + '/Core/GroupMembers.php',
                    params: {
                        action : action,
                        group_id: action =='add' ? rid.id : s[0].data.id,
                        type: _this.type,
                        user_ids : sels.join(',')
                        
                    },  
                    method: 'POST',  
                    success : function(data) {
                        refreshPager();
                    }, 
                    
                    failure: function() {
                        //Ext.get(document.body).unmask();
                        //if (cb) {
                        //    cb.call(false);
                        //}
                         
                    }
                });
                
                
                
                //if (!isFromGroup && isToGroup) {
                    //return 'x-dd-drop-ok-add'; 
                return true;
                //}
                
                  
            }
        });  
        
        /*
        var gridFoot = this.grid.getView().getFooterPanel(true);
        
        this.paging = new Ext.PagingToolbar(gridFoot, this.grid.getDataSource(), {
            pageSize: 25,
            displayInfo: true,
            displayMsg: '',
            emptyMsg: ''
        });
        */
        var grid = this.grid;
 
        var gridHead = this.grid.getView().getHeaderPanel(true);
        this.toolbar = new Ext.Toolbar(gridHead);
          
 
        var _dialog= this.getDialog();
        this.toolbar.add({
            
            text: "Manage Groups",
            cls: 'x-btn-text-icon',
            icon: Ext.rootURL + 'images/default/tree/leaf.gif',
            menu : {
                items : [
                    
                    {
                        text: "Add",
                         cls: 'x-btn-text-icon',
                        icon: Ext.rootURL + 'images/default/dd/drop-add.gif',
                        hidden : !Pman.hasPerm('Core.Groups', 'A'),
                        handler : function(){
                            _dialog.show( { id : 0, type: _this.type }, refreshPager ); 
                        }
                    }, 
                    {
                        text: "Edit",
                        cls: 'x-btn-text-icon',
                        icon: Ext.rootURL + 'images/default/tree/leaf.gif',
                        hidden : !Pman.hasPerm('Core.Groups', 'E'),
                        handler : function() {
                            var s = grid.getSelectionModel().getSelections();
                            if (!s.length || (s.length > 1))  {
                                Ext.MessageBox.alert("Error", s.length ? "Select only one Row" : "Select a Row");
                                return;
                            }
                            if ((s[0].data.name == 'Administrators') ||(s[0].data.name == 'Default')) {
                                Ext.MessageBox.alert("Error", "You can not rename that group");
                                return;
                            }
                            if (s[0].data.id < 1) {
                                Ext.MessageBox.alert("Error", "You can not rename that group");
                                return;
                            }
                            _dialog.show(s[0].data, refreshPager); 
                        }
                    },  
                    
                    {
                        text: "Delete",
                         cls: 'x-btn-text-icon',
                        icon: rootURL + '/Pman/templates/images/trash.gif',
                        hidden : !Pman.hasPerm('Core.Groups', 'D'),
                        handler : function(){
                            var s = grid.getSelectionModel().getSelections();
                              
                            for(var i = 0; i < s.length; i++) {
                                
                                if ((s[i].data.id < 1) || (s[i].data.name == 'Administrators')) {
                                    Ext.MessageBox.alert("Error", "You can not delete that group");
                                    return;
                                }
                            }
                            
                            
                            Pman.genericDelete(_this, 'Groups'); 
                        } 
                    } , '-',
                      {
                        text: "Reload",
                         cls: 'x-btn-text-icon',
                           icon: rootURL + '/Pman/templates/images/view-refresh.gif',
                        handler : function(){
                            refreshPager();
                        }
                    }
                ]
            }
                    
        });
        this.panel.on('activate', function() {
           // refreshPager();
        });
            
        //this.toolbar = tb;
        // add stuff to toolbar?
        //this.innerLayout.endUpdate();
        
        
        
    },
    refresh: function()
    {
        this.grid.getDataSource().reload();   
    } /*,
   // - is this used anymore? 
   
    show: function (parentLayout, region)
    {
        this.add(parentLayout, region);
        this.grid.getDataSource().load({
            params: {
                type: _this.type
            }
        });

    }
    */
};
