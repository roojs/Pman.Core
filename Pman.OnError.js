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
        errorObj = errorObj || false;
        // arguments.callee.caller
        
        
        
    
    
    
    }