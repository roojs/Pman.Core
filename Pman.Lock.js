//<script type="text/javascript">
/**
 * Locking - 
 * 
 * usage:
 * 
 
    * new Pman.Lock( {
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
        var _t = this;
        Pman.Request({
            url : baseURL + 'Core/Lock.php',
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
                
                _t.cfg.success.call(_t,_t);
                
                
            }
        })
    
    
    },
    unlock : function() {
        
    }
    

}
 