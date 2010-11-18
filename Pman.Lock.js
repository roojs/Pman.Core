//<script type="text/javascript">
/**
 * Locking - 
 * 
 * usage:
 * 
   Pman.Lock.lock( {
     table : 'Product',
     id : 123,
     success : function() { ..show dialog etc..... }
    });
   Pman.Lock.unlock( {
        table : 'Product',
        id : 123
    });
    * 
    * new Pman.Lock( {
          table : 'Product',
         id : 123,
         success : function(lock) { ..show dialog etc..... 
         * 
         *  ... dostuff..
         * lock.unlock()
         * }
        }
    * 
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
 