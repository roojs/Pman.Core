/**
 * include a script -- normaly used for testing..
 *
 *
 */


Pman.Include = function(sp) {
    var head = document.getElementsByTagName("head")[0];
 
    
    var script = document.createElement("script");
    script.setAttribute("src",  rootURL + sp    +'?ts=' + Math.random() );
    script.setAttribute("type", "text/javascript");
    //script.setAttribute("id", trans.scriptId);
    head.appendChild(script);
    
    Roo.get(documen.body).appendChild()
     
)

Pman.Include.cached = function(sp) {
    var tag = '<script type="text/javascript"  src="' +
        rootURL + sp     
    Roo.get(documen.body).appendChild()
     
)