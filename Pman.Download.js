//<script type="text/javascript">
/**
* @class Pman.Download
* Handles file downloads in a hidden frame, or new window.
* Usage:
<pre><code>
var t = new Pman.Download({
    url: baseURL + '/Images/Download/0/myfile.jpg',
    newWindow : false,
    params: { .... },
    success : function() {
        Roo.MessageBox.alert("File has downloaded");
    }
});

</code></pre>
* 
* @constructor
* @param {Object} cfg   Configuration object.
* @cfg {String} url     Location to download from.
* @cfg {String} method     GET or POST (default GET), POST will create a form, and post that into the hidden frame.
* @cfg {Boolean} newWindow (optional) download to new window
* @cfg {Function} success (optional) MAY fire on download completed (fails on attachments)..
* @cfg {Number} timeout (optional) in milliseconds before it gives up (default 30000 = 30s)
     
*/
Pman.Download = function(cfg)
{
 
    
    
    if (cfg.newWindow) {
            // as ie seems buggy...
        window.open( cfg.url + '?' + Roo.urlEncode(cfg.params || {}), '_blank');
        return ; 
        
    }
    Roo.apply(this, cfg);
    
    var submit = false;
    this.createCsvFrame();
    
    var requested = 0;
     
    Roo.EventManager.on( this.csvFrame, 'load', this.onLoad, this);
    
    
    //--- simple method..
    cfg.method = cfg.method || 'GET';
    
    if (cfg.method == 'GET' && !cfg.params) {
        (function() {
            submit = true;
            this.csvFrame.src = cfg.url;
            this.cleanup.defer(cfg.timeout || 30000,this);
        }).defer(100, this);
        
       
        return;
    }
    
    Roo.log("creating form?");
    
    var b = Roo.get(document.body);
    this.form = b.createChild({
        tag: 'form',
        method : cfg.method,
        action : cfg.url,
        target : this.csvFrame.id,
        enctype : 'multipart/form-data'


        
    });
 
    for(var i in cfg.params) {
        
        var el = this.form.createChild( {
            ns : 'html',
            tag : 'input',
            
            type: 'hidden',
            name : i,
            value : cfg.params[i]
        });
        
        
    }
    
    (function() {
        submit = true;
        this.form.dom.submit();
        this.cleanup.defer(cfg.timeout || 30000,this);
    }).defer(100, this);
    
     
 
}

Roo.apply(Pman.Download.prototype, {
    
    /**
     * @type {HTMLIframe} the iframe to download into.
     */
     
    csvFrame : false,
    
    // private
    form : false,
    
    // private..
    createCsvFrame: function()
    {
        
        if (this.csvFrame) {
            document.body.removeChild(this.csvFrame);
        }
            
        var id = Roo.id();
        this.csvFrame = document.createElement('iframe');
        this.csvFrame.id = id;
        this.csvFrame.name = id;
        this.csvFrame.className = 'x-hidden';
        //if(Roo.isIE){
            this.csvFrame.src = Roo.SSL_SECURE_URL;
        //}
        document.body.appendChild(this.csvFrame);

        if(Roo.isIE){
            document.frames[id].name = id;
        }
        
    },
    
    onLoad : function()
    {
       // requested++; // second request is real one..
       // if (requested < 2) {
       //     return;
        //} // n
        if (!this.submit) {
            return;
        }
        
      
        var frame = this.csvFrame;
        var success  = true; 
        try { 
            var doc = Roo.isIE ? 
                frame.contentWindow.document : 
                (frame.contentDocument || window.frames[Pman.Download.csvFrame.id].document);
            
            
            if(doc && doc.body && doc.body.innerHTML.length){
              //  alert(doc.body.innerHTML);
                  
                Roo.MessageBox.alert("Download Error", doc.body.innerHTML);
                success  = false;
                 
                
            }
            
            Roo.log(doc.body.innerHTML);
             
        }
        catch(e) {
            Roo.log(e.toString());
            Roo.log(e);
        }
        
        this.cleanup();
        
        // this will never fire.. see 
        // http://www.atalasoft.com/cs/blogs/jake/archive/2009/08/18/events-to-expect-when-dynamically-loading-iframes-in-javascript-take-2-thanks-firefox-3-5.aspx
        if (this.success && success) {
            
            this.success();
        }
       
        

    },
    
    // private - clean up download elements.
    cleanup :function()
    {
        if (this.form) {
            this.form.remove();
            this.form= false;
        
        }
        
        if (this.csvFrame) {
            Roo.EventManager.removeListener(this.csvFrame, 'load', this.onLoad, this);
            Roo.get(this.csvFrame).remove();
            this.csvFrame= false;
        }
         
    }
     
     
});