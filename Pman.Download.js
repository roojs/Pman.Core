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
     
*/
Pman.Download = function(cfg)
{
 
    if (cfg.newWindow) {
            // as ie seems buggy...
        window.open( cfg.url + '?' + Roo.urlEncode(cfg.params || {}), '_blank');
        return ; 
        
    }
    
    this.createCsvFrame();
    
    var requested = 0;
    function cb()
    {
       // requested++; // second request is real one..
       // if (requested < 2) {
       //     return;
        //}
        Roo.log("loaded?");
        var r = { responseText : "", responseXML : null };
        
        var frame = this.csvFrame;
        
        try { 
            var doc = Roo.isIE ? 
                frame.contentWindow.document : 
                (frame.contentDocument || window.frames[Pman.Download.csvFrame.id].document);
            
            if(doc && doc.body && doc.body.innerHTML.length){
              //  alert(doc.body.innerHTML);
                Roo.MessageBox.alert("Error download",doc.body.innerHTML);
            }
             
        }
        catch(e) {
            Roo.log(e.toString());
            Roo.log(e);
        }
        
        if (this.form)
        {
            this.form.remove();
            this.form= false;
        }
        Roo.EventManager.removeListener(frame, 'load', cb, this);
        if (cfg.success) {
            cfg.success();
        }
        //Roo.get(frame).remove();
        

    }
    Roo.EventManager.on( this.csvFrame, 'load', cb, this);
    cfg.method = cfg.method || 'GET';
    
    if (cfg.method == 'GET') {
        
         this.csvFrame.src = cfg.url;
        return;
    }
    Roo.log("creating form?");
    
    var b = Roo.get(document.body);
    this.form = b.createChild({
        tag: 'form',
        method : 'POST',
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
 
    
    this.form.dom.submit.defer(100,this.form.dom);
     
 
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
        
    }
     
     
});