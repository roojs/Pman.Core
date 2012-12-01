/**
 * include a script -- normaly used for testing..
 *
 *
 */


Pman.Include = function(sp) {
    
    Pman.Include.script(sp, true)
    
     
)

Pman.Include.cached = function(sp) {
    Pman.Include.script(sp, false)
     
)

Pman.Include.script = function(sp,cached) {
    var head = document.getElementsByTagName("head")[0];
 
    
    var script = document.createElement("script");
    script.setAttribute("src",  rootURL + sp    +
              ( cached ? '' :     '?ts=' + Math.random() ));
    script.setAttribute("type", "text/javascript");
    //script.setAttribute("id", trans.scriptId);
    head.appendChild(script);
    
    Roo.get(documen.body).appendChild();
)