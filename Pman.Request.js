//<script type="text/javascript">
/**
* @class Pman.Request
* Handles generic requests  - an extension of Roo.data.Connection that runs the request
* on construction. shows error messages, and parses results.
* Usage:
<pre><code>
var t = new Pman.Request({
    url: baseURL + '/Images/Download/0/myfile.jpg',
    params: { .... },
    success : function(res) {
        Roo.log(res.data);
        Roo.log(res.total);
        ....
    } 
});

</code></pre>
* 
* @constructor
* @param {Object} cfg   Configuration object.
* @cfg {String} url     Location to download from.
* @cfg {String} method     GET or POST (default GET), POST will create a form, and post that into the hidden frame.
* @cfg  {Object/String/Function} params (Optional) An object containing properties which are used as parameters to the
*       request, a url encoded string or a function to call to get either.
* @cfg  {Function} success  called with ( JSON decoded data of the data.. )
*/

Pman.Request = function(config){
    
    Pman.Request.superclass.constructor.call(this, config);
    this.request(config);
}

Roo.extend(Pman.Request, Roo.data.Connection, {
    
    processResponse : function(response) {
        
        var res = '';
        try {
            res = Roo.decode(response.responseText);
            // oops...
            if (typeof(res) != 'object') {
                res = { success : false, errorMsg : res, errors : true };
            }
            if (typeof(res.success) == 'undefined') {
                res.success = false;
            }
            
        } catch(e) {
            res = { success : false,  errorMsg : response.responseText, errors : true };
        }
        return res;
    },
    
    handleResponse : function(response){
       this.transId = false;
       var options = response.argument.options;
       response.argument = options ? options.argument : null;
       this.fireEvent("requestcomplete", this, response, options);
       
        var res = this.processResponse(response);
                
        if (!res.success) { // error!
            if (options.failure) {
                // failure is handled... - do not show error..
                if (true === Roo.callback(options.failure, options.scope, [res, options])) {
                    return;
                }
            }
            Roo.MessageBox.hide(); // hide any existing messages..
            Roo.MessageBox.alert("Error", res.errorMsg ? res.errorMsg : "Error Sending");
            return;
        }
        Roo.callback(options.success, options.scope, [res, options]);
        
    },
    handleFailure : function(response, e){
        this.transId = false;
        var options = response.argument.options;
        response.argument = options ? options.argument : null;
        this.fireEvent("requestexception", this, response, options, e);
        Roo.callback(options.failure, options.scope, [response, options]);
        Roo.callback(options.callback, options.scope, [options, false, response]);
        if (!options.failure) {
            Roo.MessageBox.hide(); // hide any existing messages..
            Roo.MessageBox.alert("Error", res.errorMsg ? res.errorMsg : "Error Sending");
        }
    }
});