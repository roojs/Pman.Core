//<script type="text/javascript">


Pman.Download = {
    
    csvFrame : false,
    
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
        if(Roo.isIE){
            this.csvFrame.src = Roo.SSL_SECURE_URL;
        }
        document.body.appendChild(this.csvFrame);

        if(Roo.isIE){
            document.frames[id].name = id;
        }
        
    },
     
    
    
    download : function(c) {
        
        if (c.newWindow) {
            // as ie seems buggy...
            window.open( c.url + '?' + Roo.urlEncode(c.params || {}), '_blank');
            return;
            
        }
        
        this.createCsvFrame();
        function cb(){
            var r = { responseText : "", responseXML : null };

            var frame = this.csvFrame;

            try { 
                var doc = Roo.isIE ? 
                    frame.contentWindow.document : 
                    (frame.contentDocument || window.frames[this.csvFrame.id].document);
                
                if(doc && doc.body && doc.body.innerHTML.length){
                  //  alert(doc.body.innerHTML);
                    Roo.MessageBox.alert("Error download",doc.body.innerHTML);
                }
                 
            }
            catch(e) {
            }

            Roo.EventManager.removeListener(frame, 'load', cb, this);
 
        }
        Roo.EventManager.on( this.csvFrame, 'load', cb, this);
        this.csvFrame.src = c.url;
    },
};