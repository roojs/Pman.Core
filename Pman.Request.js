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
* @cfg  {Function} success  called with ( JSON decoded data of the data.. )
* @cfg {Boolean} showFailureDialog (true|false) default true
*/

Pman.Request = function(config){
    
    Pman.Request.superclass.constructor.call(this, config);
    
    if (this.mask) {
        this.maskEl = this.maskEl || Roo.get(document.body);
        Roo.get(this.maskEl).mask(this.mask);
        
    }
    this.request(config);

}

Roo.extend(Pman.Request, Roo.data.Connection, {
    // private
    showFailureDialog : true,
    
    processResponse : function(response) {
        // convert the Roo Connection response into JSON data.
        
        var res;
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
            res = { success : false,  errorMsg : response.responseText || Roo.encode(response), errors : true };
        }
        //Roo.log(response.responseText);
        if (!res.success && !res.errorMsg) {
            res.errorMsg = Roo.encode(response);
        }
        return res;
    },
    
    handleResponse : function(response)
    {
        this.transId = false;
        var options = response.argument.options;
        response.argument = options ? options.argument : null;
        this.fireEvent("requestcomplete", this, response, options);
        
        if (this.mask && this.maskEl) {
            Roo.get(this.maskEl).unmask(true);
        }
        var res = this.processResponse(response);
                
        if (!res.success && !res.done) { // error!
            if (options.failure) {
                // failure is handled... - do not show error..
                Roo.callback(options.failure, options.scope, [res, options]);
                return;
            }
            Roo.MessageBox.hide(); // hide any existing messages..
            Roo.MessageBox.alert("Error", res && res.errorMsg ?  res.errorMsg : "Error Sending data");
            return;
        }
        Roo.callback(options.success, options.scope, [res, options]);
        
    },
    
    handleFailure : function(response, e){
        this.transId = false;
        var options = response.argument.options;
        response.argument = options ? options.argument : null;
        this.fireEvent("requestexception", this, response, options, e);
        var res = Roo.callback(options.failure, options.scope, [response, options]);
        if (this.mask && this.maskEl) {
            Roo.get(this.maskEl).unmask(true);
        }
        if(!this.showFailureDialog){
            return;
        }
        if (res !== true) {
            var decode = this.processResponse(response);
            Roo.log(decode);
            // this pops up even if we have a method to handle failure..
            if (Roo.MessageBox.isVisible() ) {
                if (!options.failure) {
                    alert(decode && decode.errorMsg ?  decode.errorMsg : "Error Sending data - return true from failure to remove message");
                }
                
                return;
            }
            
            Roo.MessageBox.alert("Error", decode && decode.errorMsg ?  decode.errorMsg : "Error Sending data");
            
        }
    }
});
