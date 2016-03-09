/**
 *
 * Window onerror handler..  reports to our logger.
 *
 */

Pman.OnError = {
    
    init : function()
    {
        window.onerror = this.handler;
    },
    
    lock : false,
    
    handler : function(errorMsg, url, lineNumber, column, errorObj)
    {
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
            success : function()
            {
                Pman.OnError.lock  = false;
            }
        })
        
        
    
    
    
    }
}