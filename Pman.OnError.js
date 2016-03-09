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
        
        
        new Pman.Request({
            url : baseURL + '/Core/JsError',
            method  : 'POST',
            params : {
                msg : msg,
                url : url,
                line : line,
                col : col,
                stack : '' + stack // array??? 
            }
        })
        
        
    
    
    
    }
}