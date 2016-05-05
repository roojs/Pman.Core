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
    doctype: 'pdf' 
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
* @cfg {String} doctype (optional) download PDF to new window
* @cfg {Boolean} limit (optional) limit for grid downloads.
 
 * @cfg {String} csvCols  - use '*' to override grid coluns
 * @cfg {String} csvTitles - use '*' to override grid coluns

 
 
* @cfg {Function} success (optional) MAY fire on download completed (fails on attachments)..
* @cfg {Number} timeout (optional) in milliseconds before it gives up (default 30000 = 30s)
* @cfg {Roo.grid.Grid} grid (optional) if you want to just download a grid, (without renderers..)
* 
*/

Pman.Download = function(cfg)
{
    
    this.params = {};
    
    Roo.apply(this, cfg);
     
    if (this.grid) {
        
        this.buildFromGrid();
        Roo.log(this);
    }
    
    
    if (this.newWindow && this.method == 'GET') {
        // as ie seems buggy...
        window.open( this.url + '?' + Roo.urlEncode(this.params || {}), '_blank');
        return ; 
        
    }
   
    
    
    this.submit = false;
    this.createCsvFrame();
    
    var requested = 0;
     
    Roo.EventManager.on( this.csvFrame, 'load', this.onLoad, this);
    
    
    //--- simple method..
    this.method = this.method || 'GET';
    
    if (this.method == 'GET' && !this.params) {
        (function() {
            this.submit = true;
            this.csvFrame.src = cfg.url;
            //this.cleanup.defer(cfg.timeout || 30000,this);
        }).defer(100, this);
        
       
        return;
    }
    
    
    Roo.log("creating form?");
    
    var b = Roo.get(document.body);
    this.form = b.createChild({
        tag: 'form',
        method : this.method,
        action : this.url,
        target : this.newWindow ? '_new' : this.csvFrame.id,
        enctype : 'multipart/form-data'
    });
//    
//    if(this.doctype == 'pdf'){
//        this.pdfEmbed = b.createChild({
//            tag: 'embed',
//            src : this.url,
//            pluginspage : 'http://www.adobe.com/products/acrobat/readstep2.html',
//            alt: this.doctype
//        });
//    }
 
    Roo.log(this.params);
    for(var i in this.params) {
        
        var el = this.form.createChild( {
            ns : 'html',
            tag : 'input',
            
            type: 'hidden',
            name : i,
            value : this.params[i]
        });
        
        
    }
    var test = Roo.urlDecode(Roo.Ajax.serializeForm(this.form.dom));
    Roo.log(test);return;
    
    (function() {
        this.submit = true;
        this.form.dom.submit();
        this.cleanup.defer(this.timeout || 30000,this);
    }).defer(100, this);
    
     
 
}

Roo.apply(Pman.Download.prototype, {
    
    /**
     * @type {HTMLIframe} the iframe to download into.
     */
     
    csvFrame : false,
    
    // private
    form : false,
    
    limit : 9999,
    
    newWindow : false,
    
    method : 'GET',
    
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
        Roo.log('onload?');
        if (!this.submit) {
            return false;
        }
        return false;
      
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
        // we can not actually do anything with the frame... as it may actually still be downloading..
        return true;
    
        this.cleanup();
        
        // this will never fire.. see 
        // http://www.atalasoft.com/cs/blogs/jake/archive/2009/08/18/events-to-expect-when-dynamically-loading-iframes-in-javascript-take-2-thanks-firefox-3-5.aspx
        if (this.success && success) {
            
            this.success();
        }
        return false;
        

    },
    
    // private - clean up download elements.
    cleanup :function()
    {
        Roo.log('cleanup?');
        if (this.form) {
            this.form.remove();
            this.form= false;
        
        }
        
        if (this.csvFrame) {
            Roo.EventManager.removeListener(this.csvFrame, 'load', this.onLoad, this);
            Roo.get(this.csvFrame).remove();
            this.csvFrame= false;
        }
         
    },
    
    buildFromGrid : function()
    {
        // get the params from beforeLoad
        var ds = this.grid.ds;
        ds.fireEvent('beforeload', ds, {
            params : this.params
            
        });
        
         if(ds.sortInfo && ds.remoteSort){
            var pn = ds.paramNames;
            this.params[pn["sort"]] = ds.sortInfo.field;
            this.params[pn["dir"]] = ds.sortInfo.direction;
        }
        if (ds.multiSort) {
            var pn = ds.paramNames;
            this.params[pn["multisort"]] = Roo.encode( { sort : ds.sortToggle, order: ds.sortOrder });
        }
        
        
        
        this.url = this.grid.ds.proxy.conn.url;
        this.method = this.method || this.grid.ds.proxy.conn.method ;
        var t = this;
        // work out the cols
        
        if (this.csvCols) {
            t.params.csvCols = this.csvCols;
            t.params.csvTitles = this.csvTitles;
        } else {
            
            Roo.each(this.grid.cm.config, function(c,i) {
                t.params['csvCols['+i+']'] = c.dataIndex;
                t.params['csvTitles['+i+']'] = c.header;
                
            });
        }
        if (this.grid.loadMask) {
            this.grid.loadMask.onLoad();
        }
        this.params.limit = this.limit;
        
        // do it as a post, as args can get long..
        
        this.method = this.method || 'POST';
        if (this.method  == 'POST') {
            this.params._get = 1;
        }
    }
     
});
