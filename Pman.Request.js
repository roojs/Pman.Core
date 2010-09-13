//<script type="text/javascript">
/**
* @class Pman.Request
* Handles generic requests  - an extension of Roo.data.Connection that 
* shows error messages, and parses results..
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
* @cfg {Boolean} newWindow (optional) download to new window
     
*/