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
    this.attemptLock();
}

Roo.apply(Pman.Lock.prototype, {
    
    attemptLock : function()
    {
        var _t = this
        Pman.Request({
            url : baseURL + 'Core/Lock/lock',
            params : {
                on_table : cfg.table,
                on_id : cfg.id
            },
            failure : function() {
                Roo.MessageBox.alert("Error", "Lock Request failed, please try again");
            },
            success : function(data)
            {
                Roo.log(data);
                
                if (typeof(data) == 'object') {
                    _t.confirmBreak(data);
                }
                
                _t.cfg.success(_t); //dont care about scope..
                
                
            }
        })
    },
    confirmBreak : function (ar)
    {
        
        var msg = '';
        Roo.each(ar, function(p) {
            
           }
        
        
        
    }
    
    
    unlock : function() {
        Pman.Request({
            url : baseURL + 'Core/Lock/unlock',
            params : {
                id : this.lock_id,
                on_id : cfg.id
            },
            failure : function() {
                Roo.MessageBox.alert("Error", "Lock Request failed, please try again");
            },
            success : function(data)
            {
                Roo.log(data);
                
                if (typeof(data) == 'object') {
                    _t.confirmBreak(data);
                }
                
                _t.cfg.success(_t); //dont care about scope..
                
                
            }
        })
    }
    

});
 