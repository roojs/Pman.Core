//<script type="text/javascript">

/**
 * 
 *  Generic Page handler - implement this to start your app..
 * 
 * 
 * 
 * eg.
 *  MyProject = new Roo.Document({
        events : {
            'load' : true // your events..
        },
        listeners : {
            'ready' : function() {
                // fired on Ext.onReady()
            }
        }
 * 
 */
Roo.Document = function(cfg) {
     
    this.addEvents({ 
        'ready' : true
    });
    Roo.util.Observable.call(this,cfg);
    var _this = this;
    Roo.onReady(function() {
        _this.fireEvent('ready');
    },null,false);
    
}
Roo.extend(Roo.Document, Roo.util.Observable, {
    
});