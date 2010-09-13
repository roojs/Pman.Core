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
    success : function(data) {
        
    },
    failure : function () {
         
    }
});

</code></pre>
* 
* @constructor
* @param {Object} cfg   Configuration object.
* @cfg {String} url     Location to download from.
* @cfg {String} method     GET or POST (default GET), POST will create a form, and post that into the hidden frame.
* @cfg  {Object/String/Function} (Optional) An object containing properties which are used as parameters to the
*       request, a url encoded string or a function to call to get either.
*    
*/

Pman.Request = function(config){
    
    Pman.Request.superclass.constructor.call(this, config);
    config.failure  = typeof(config.failure) == 'undefined' ? this.failure : config.failure;
    this.request(config);
}

Roo.extend(Pman.Request, Roo.data.Connection, {
    
    
});