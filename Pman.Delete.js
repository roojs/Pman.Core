/*
 * Generic Delete function (moved from Pman.js)
 *
 * with progressbar version.
 */


Pman.Delete = {
    
    
    selections : function(tab)
    {
        var r = [];
 
            
        var s = tab.grid.getSelectionModel().getSelections();
        if (!s.length)  {
            Roo.MessageBox.alert("Error", "Select at least one Row to delete" );
            return '';
        }
        var reader = tab.grid.reader || tab.grid.ds.reader;
        for(var i = 0; i < s.length; i++) {
            r.push(reader.getId(s[i].json));
        }
        return r;
    },
    
    // previous version - no progress..
    
    simple : function(tab, tbl)
    {
        
        var r = this.selections(tab);
        if (!r.length) {
            return;
        }
        
        
        Roo.MessageBox.confirm("Confirm", "Are you sure you want to delete that?",
            function(btn) {
                if (btn != 'yes') {
                    return;
                }
                tab.grid.getView().mainWrap.mask("Deleting");
                Pman.Delete.simpleCall(tab, tbl, r, function(response) {
                    tab.grid.getView().mainWrap.unmask();
                    Pman.Delete.completed(tab);
                });
                
            }
            
        );
     },
    
    simpleCall : function(tab, tbl, r, resp)
    {
            // what about the toolbar??
        
        new Pman.Request({
            url: baseURL + '/Roo/'+tbl,
            method: 'POST',
            params: {
                _delete : r.join(',')
            },
            success: resp,
            failure: function(act) {
                
                Roo.log(act);
                var msg = '';
                try {
                    msg = act.errorMsg;
                } catch(e) {
                    msg = "Error deleting";
                }
                tab.grid.getView().mainWrap.unmask();
                Roo.MessageBox.alert("Error",  msg);
            }
            
        });
    },
    
    progress : function(tab, tbl) {
        
        var r = this.selections(tab);
        if (!r.length) {
            return;
        }
        
        
        Roo.MessageBox.confirm("Confirm", "Are you sure you want to delete that?",
            function(btn) {
                if (btn != 'yes') {
                    return;
                }
                Roo.MessageBox.progress("Delete Records", "Deleting 1/" + r.length );
                
                
                var p = 0;
                
                var delItem = function ()
                {
                    Roo.MessageBox.updateProgress(  (p+1)/r.length, "Deleting " + (1+p) + "/" + r.length );
                    Pman.Delete.simpleCall(tab, tbl, [ r[p] ], function(response) {
                        
                        p++;
                        if (p == r.length) {
                            Roo.MessageBox.hide();
                            Pman.Delete.completed(tab);
                            return;
                        }
                        
                        delItem();
                    });
                }
                delItem();
               
                
            }
            
        );
    },
    completed : function(tab)
    {
        
        if ( tab.paging ) {
            tab.paging.onClick('refresh');   
        } else if (tab.grid.footer && tab.grid.footer.onClick) {
            // new xtype built grids
            tab.grid.footer.onClick('refresh');   
        } else if (tab.refresh) {
            tab.refresh(); // this might cause problems as panels have a refresh method?
        } else {
            tab.grid.getDataSource().load();
        }
    
    }
}