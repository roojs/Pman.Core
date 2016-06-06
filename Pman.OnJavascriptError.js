/**
 *
 * Window onerror handler..  reports to our logger.
 *
 */

Pman.OnJavascriptError = {
    
    init : function()
    {
        this.history = [];
        window.onerror = this.handler.createDelegate(this);
        
    },
    
    history  : false, // array of previous events...
    
    lock : false,
    
    handler : function(msg, url, line, col, errorObj)
    {
        
        if (this.lock) {
            return false;
        }
        
        // note - some are not passed by all browsers.
        col = col || -1;
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
            url : baseURL + '/Core/JavascriptError',
            method  : 'POST',
            params : {
                msg : msg,
                url : url,
                source_url : window.location.toString(),
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
        Pman.OnJavascriptError.lock = false;
    }
    
};
Pman.OnJavascriptError.init();
