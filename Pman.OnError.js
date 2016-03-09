/**
 *
 * Window onerror handler..  reports to our logger.
 *
 */

Pman.OnError = {
    
    init : function()
    {
        this.history = [];
        window.onerror = Pman.OnError.handler;
        
    },
    
    history  : false, // array of previous events...
    
    lock : false,
    
    handler : function(errorMsg, url, lineNumber, column, errorObj)
    {
        Roo.log(this);
        if (this.lock) {
            return false;
        }
        
        // note - some are not passed by all browsers.
        column = column || -1;
        var stack = errorObj ? errorObj.stack : false;
        
        
        
        
        if (!errorObj) {
            var stack = [];
            var f = arguments.callee.caller;
            while (f) {
                stack.push(f.name);
                f = f.caller;
            }
        }
        // 10 events max in 5 minutes
        var last = this.history.length  > 10 ? this.history.shift() : false;
        if (last && last > (new Date()).add( Date.MINUTE, -5)) {
            this.history.unshift(last); // put it back on, and ingore this error.
            return false;
        }
        
        this.history.push(new Date());
        
        
        // rate limit...
        this.lock = true;
        
        
        
        new Pman.Request({
            url : baseURL + '/Core/JsError',
            method  : 'POST',
            params : {
                msg : msg,
                url : url,
                line : line,
                col : col,
                stack : '' + stack // array??? 
            },
            success : this.resetLock,
            failure : this.resetLock
                
            
        });
        return false;
        
    
    
    
    },
    resetLock : function()
    {
        Pman.OnError.lock = false;
    }
    
}
Pman.OnError.init();
