//<script type="text/javascript">

/**
 * 
 * singleton preview frame / object..
 * 
 * -- has two objects
 * -- frame and obj
 * frame = handles doc's that can appear in frames
 * pdf = is for firefox - displaying PDF's and others????
 * 
 */

Pman.Preview = {
    
    frame  : false,
    pdf : false,
    imgDiv : false,
    active: false, // frame of pdf
    init : function()
    {

        if (this.frame) { // already exists.
            return; 
        }
        
         
        //var frm = this.layout.getEl().createChild({
        //        tag:'iframe', 
        //        src: 'about:blank'});
        if (Roo.isGecko) {
            this.pdf = Ext.DomHelper.append(
                document.body,
                {
                    id : 'pdf-view',
                    tag: 'object', 
                    type : 'application/pdf',
                    data : 'about:blank',
                    width : 200,
                    height :200,
                    style : 'position:absolute;top:-1000;left:-1000; z-index:-100',
                    cn : [ 
                        {
                            tag: 'param',
                            name : 'src',
                            value : 'about:blank'
                        }
                    ]
                },
                false
            );
             
        }
        this.imgDiv =  Ext.DomHelper.append(
                document.body,
                { 
                    tag:'div', 
                    style : 'position:absolute;top:-1000;left:-1000; z-index:-100;' + 
                        'overflow-x:hidden;overflow-y:scroll;width:200px; height: 200px;' 
                }
        );
            
        this.frame =  Ext.DomHelper.append(
                document.body,
                { 
                    tag:'iframe', 
                    src: 'about:blank',
                    style : 'position:absolute;top:-1000;left:-1000; z-index:-100;' + 
                        'width:1px; height: 1px;'
                }
        );
        
    },
    config : false,
    
    
    onResize : function() 
    {
        if (this.active ) {
            this.active.style.zIndex = this.activeCfg ? this.activeCfg.zIndex : -100;
        }
        
        //var pz = this.config.previewRegion.panelSize;
        //if (!pz) {
        //    return;
        //   }
        //if (pz.width < 10) {
        //    return;
        //}
        if (!this.config.previewRegion) {
            return;
        }
        
        
        
        var pos = this.config.previewRegion.el.getBox();
        if (pos.width < 10) {
            return;
        }
        
        
        //var top = pos.y + (pos.height - pz.height);
        if (!this.active) {
            return;
        }
        
        this.active.setAttribute( 'width',pos.width);             
        this.active.setAttribute( 'height',pos.height); 
        //if (!Ext.isIE) {
            this.active.style.width = pos.width + 'px';
            this.active.style.height = pos.height + 'px';
        //}
        
        this.active.style.top = pos.y + 'px';
        this.active.style.left = pos.x + 'px';
        
        /*
        this.active.setAttribute( 'width',pz.width);             
        this.active.setAttribute( 'height',pz.height); 
        //if (!Ext.isIE) {
            this.active.style.width = pz.width + 'px';
            this.active.style.height = pz.height + 'px';
        //}
        
        this.active.style.top = top + 'px';
        this.active.style.left = pos.x + 'px';
        */
    },
        
     
    
    unlink: function ()
    {
        // remove the listeners on that object...
        if (!this.config) {
            return;
        }
        if (this.config.dialog ) {
            this.config.dialog.un('hide', this.onHide, this);
        }
         if (this.config.tab) {
            this.config.tab.un('deactivate', this.onHide, this);
        }
        this.config.previewRegion.un('resized', this.onResize, this);
       // this.config.previewRegion.getSplitBar().un('beforeresize', this.disable, this);
        this.config = false;
    },
    
    link: function(config)
    {
        // add the listener to the ownerDiv..
       
        this.init();
        if (this.config) {
            this.unlink();
        }
        this.config = config;
        if (this.config.dialog) {
            this.config.dialog.on('hide', this.onHide, this);
        }
        if (this.config.tab) {
            this.config.tab.on('deactivate', this.onHide, this);
        }
        
        this.config.previewRegion.on('resized', this.onResize, this);
        //this.config.previewRegion.getSplitBar().on('beforeresize', this.disable, this);
    },
    
     
    removeActive: function() 
    {
        if (!this.active) {
            return;
        }
        this.active.style.left='-1000px';
        this.active.style.top='-1000px';
        this.active.style.width='200px';
        this.active.style.top='200px';
        this.active.style.zIndex= -100;
        this.active = false;
    },
    
    onHide: function () {
        if (!this.active) {
            return;
        }
        this.removeActive();
        this.unlink();
    },
    
    showPdf : function()
    {
        this.removeActive();
        this.activeCfg.url = this.activeCfg.pdfurl;
        this.activeCfg.mimetype = 'application/pdf';
        this.load(this.activeCfg);
    },
    
    /**
     * 
     * config args
     * url: load url
     * mimetype : load mimetype (needs more explaination)
     * pdfurl : 
     * width : (for image)
     * height:  (for image)
     * 
     */
    load : function(cfg)
    {
        
        this.activeCfg = false;
        if (typeof(cfg) != 'object') {
            alert('Preview Load only accepts object with url/mimetype/zIndex as loader');
            return;
        }
        this.activeCfg = Roo.apply({},cfg);
        var    url = this.activeCfg.url;
        var    mimetype = this.activeCfg.mimetype;
        
        this.removeActive();
        //this.enable();
        
        switch (mimetype) {
            
            case 'image/jpeg': // preview!!!!
                // we need to show this in a floating div...
                //var ps  = 100; //this.config.previewRegion.panelSize;
                var ps = this.config.previewRegion.el.getBox();
                
                this.imgDiv.innerHTML = '<img src="' + url +'"' + 
                    ' width="'+ (ps.width-15) + '"' +
                    ' qtip="'+ "Click to view PDF" + '"' +
                    ' ext:width="100"' +
                      ' onclick="Pman.Preview.showPdf();"/>';
                this.active = this.imgDiv;
                this.enable(this.activeCfg.zIndex);
                return;
                break;
            
            
            case 'application/pdf':
            case 'application/msword':
            case 'application/vnd.oasis.opendocument.text':
           
            case 'application/vnd.ms-excel':
            case 'application/vnd.oasis.opendocument.spreadsheet':
            case 'application/vnd.dwg':
            case 'application/acad':
            case 'application/x-acad':
            case 'application/autocad_dwg':
            case 'image/x-dwg':
            case 'application/dwg':
            case 'application/x-dwg':
            case 'application/x-autocad':
            case 'image/vnd.dwg':
            case 'drawing/dwg':
                
                if (!url.match(/\.pdf$/)) {
                    url += '.pdf';
                }
                
                if (!Roo.isGecko) {
                  
                    this.frame.src = url;
                    this.active = this.frame;
                    this.enable(this.activeCfg.zIndex);
                    return;
                }
                this.pdf.setAttribute( 'data',  url);
               // this.pdf.childNodes[0].setAttribute( 'src',  url);
                this.active = this.pdf;
                this.enable(this.activeCfg.zIndex);
                return;
                
            default:
                this.frame.src =   url; ///'about:blank';
                this.active = this.frame;
                this.enable(this.activeCfg.zIndex);
                return;
                
            
        }
         
        
    },
    disable: function()
    {
        
        if (!this.active) {
            return;
        }
        this.disabled = true;
        this.active.style.zIndex = -100;
         if (!Roo.isGecko) {
            this.frame.src= 'about:blank';
         }
    },
    enable: function(zIndex)
    {
        if (!this.active) {
            return;
        }
        this.disabled = false;
        
        this.active.style.zIndex = zIndex ? zIndex : 10000;
         if (!Roo.isGecko && this.activeCfg) {
            this.frame.src= this.activeCfg.url;
         }
        
        this.onResize();
    },
    tmpStatus : false,
    tmpDisable : function()
    {
        if (this.disabled) {
            this.tmpStatus = false;
            return;
        }
        this.tmpStatus = true;
        this.disable();
    },
    tmpEnable: function()
    {
        if (!this.tmpStatus) {
            return;
        }
        this.tmpStatus = false;
        this.enable();
    }
    
    
}