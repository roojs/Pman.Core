//<script type="text/javascript">
/**
 * Locking - 
 * 
 * usage:
 * 
 
     new Pman.Lock( {
          table : 'Product',
         id : 123,
         success : function(lock) { ..show dialog etc..... 
          
           ... dostuff..
           ... send _lock=XXX to Roo updated code..
           
            lock.unlock() -- we dont care about the result..
          }
        }
    * 
 * 
 * 
 *  call : 
 * try and lock it..
 * baseURL + /Core/Lock/lock?on_id=...&on_table=...
 * - returns id or an array of who has the locks.
 * 
 * Force an unlock after a warning..
 * baseURL + /Core/Lock/lock?on_id=...&on_table=...&force=1
 * - returns id..
 * 
 * Unlock - call when window is closed..
 * baseURL + /Core/Lock/unlock?on_id=...&on_table=...&force=1
 * - returns jerr or jok
 * 
 */
Pman.Lock = function (cfg) {
    this.cfg = cfg;
    Roo.log('ctor-callLock');
    this.callLock();
}

Roo.apply(Pman.Lock.prototype, {
    cfg : false,
    
    id : false, // the id of the lock..
    
    callLock : function(force)
    {
        Roo.log('callLock');
        force = force || 0;
        var _t = this;
        new Pman.Request({
            url : baseURL + '/Core/Lock/lock',
            params : {
                on_table : this.cfg.table,
                on_id : this.cfg.id,
                force : force
            },
            failure : function() {
                Roo.MessageBox.alert("Error", "Lock Request failed, please try again");
            },
            success : function(res)
            {
                var data = res.data;
               Roo.log(data);
                if (!force && typeof(data) == 'object') {
                    _t.confirmBreak(data);
                    return;
                }
                _t.id = data;
                _t.cfg.success(_t); //dont care about scope..
                
                
            }
        })
    },
    confirmBreak : function (ar)
    {
        
        var msg = "This Record is Locked by the following people, <br/>" + 
            "Do you want to continue, this will prevent these people from saving their changes<br/>";
            
        Roo.each(ar, function(p) {
            msg += '<br/>' + p.name + ' at ' + p.lock_created ;
        });
        var _t = this;
        Roo.MessageBox.confirm("Confirm breaking locks", msg, function(r) {
            if (r != 'yes') {
                return;
            }
            _t.callLock(1);
        });
            
        
        
        
    },
    
    
    unlock : function(id) {
        id = id || this.id;
        new Pman.Request({
            url : baseURL + '/Core/Lock/unlock',
            params : {
                id : id
            },
            failure : function() {
                Roo.MessageBox.alert("Error", "UnLock Request failed, you may get a warning when trying to edit again");
            },
            success : function(data)
            {
                // don nothing
                
            }
        });
    }
    

});
 