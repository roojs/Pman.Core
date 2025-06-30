/**
 *
 *  progress caller:
 *
 *  incomming - list of IDs
 *  -- it will call a method with either '1' id or a batch..?
 *
 * usage:
 *   cfg:
 *      items
 *      title
 *      message_prefix
 *      success : on done..
 *      url:
 *      base_params
 *      batch_size (default 1)
 */
 
Pman.Progress = function(cfg)
{
    Roo.apply(this, cfg);
    
    this.items = Array.from(cfg.items); // copy...
    this.items_len = this.items.length;
    
    var _this = this;
    if (this.items.length< 1) {
        return;
    }
    
    if (this.confirm == false) {
        Roo.MessageBox.progress(this.title , this.message_prefix + " 1/" + this.items.length );
        this.doItem();
        return;
    }
     Roo.MessageBox.confirm("Confirm", String.format(this.confirm,  this.items.length ),
        function(btn) {
            if (btn != 'yes') {
                return;
            }
             Roo.MessageBox.progress(_this.title , _this.message_prefix + " 1/" + _this.items.length );
            _this.doItem();
            
        }
    ); 
     
}

Roo.apply(Pman.Progress.prototype, {
    
    url : '',
    batch_size : 1,
    base_params : {},
    
    items : false,
    items_len : 0,
    
    title : '',
    message_prefix : '',
    success : false,
    confirm : false,
    
    doCall : function(id)
    {
 
        var params = Roo.apply({}, this.base_params);
        params[ this.batch_size > 1 ? 'ids' : 'id' ] = id;
        
        new Pman.Request({
            url: this.url,
            method: 'POST',
            params: params,
            success: function( ) {
            
                if (! this.items.length) {
                    Roo.MessageBox.hide();
                    this.success();
                    return;
                }
                
                this.doItem();
            },
            failure: function(act) {
                
 
                var msg = '';
                try {
                    msg = act.errorMsg;
                } catch(e) {
                    msg = "Error disabling";
                }
                
                Roo.MessageBox.alert("Error", msg);
            },
            scope : this
            
        });
    },
    
    doItem : function ()
    {
        Roo.MessageBox.updateProgress(  
            (this.items_len - this.items.length) / this.items_len, 
            this.message_prefix + (this.items_len - this.items.length) + "/" + this.items_len
        );
        var ids = this.batch_size > 1 ? this.items.splice(0, this.batch_size).join(',') : this.items.shift();
        
        
         this.doCall( ids  );
    }
    
    
    
    
})