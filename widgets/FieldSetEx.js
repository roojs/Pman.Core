//<script type="text/javascript">


Ext.form.FieldSetEx = function(config){
    Ext.form.FieldSetEx.superclass.constructor.call(this, config);
   /* if (!this.id) {
        this.id = Ext.id();
    }*/
    this.addEvents({
        expand: true
    });
};

Ext.extend(Ext.form.FieldSetEx, Ext.form.FieldSet, {
    collapseGroup : false,
    expanded: true,
    outer: false,
    fs : false,
    name : false,
    hiddenEl: false,
    defaultAutoCreate :  {tag: 'fieldset', cn: { tag:'legend' }},
    defaultBorder  : '',

    onRender : function(ct, position)
    {
       
        if(this.el){ 
            this.el = Roo.get(this.el);
        }else {
            // wrap the fieldset up so wee can  mask the contents..
        
            var cfg = this.getAutoCreate();
            this.fs = ct.createChild(cfg, position);
            if(this.style){
                this.fs.applyStyles(this.style);
            }
            // add the hidden value 
            if (this.name) {
                this.hiddenEl = this.fs.createChild({
                        tag: 'input' , 
                        type: 'hidden', 
                        name : this.name,
                        value : '',
                        cls: 'x-form-item-display-none'
                });
            }
            
            this.el = this.fs.createChild({tag: 'div' });
            this.el.setVisibilityMode(Ext.Element.DISPLAY);
        }
        
        
        
        Ext.form.FieldSet.superclass.onRender.call(this, ct, position);
        
        if(this.legend){
            this.setLegend(this.legend);
        }
        if (this.innerHTML) {
             this.el.createChild({ tag: 'div', cls: 'x-form-item' , html : this.innerHTML });
         
        }

        var l = this.fs.child('legend');
        l.setStyle(
            'background',
            'transparent url('+ Ext.rootURL + '/images/default/menu/chk-sprite.gif)  no-repeat scroll 0 0'
        );
        
        l.setStyle('padding', '0 5 0 20');
        l.setStyle('cursor', 'pointer');
        l.on('click', function() {
            this.setExpand(!this.expanded);
            
            // fire event..
        }, this);
        
        var t = this;
        // not sure why but it always shows up visiable...
        setTimeout(function(){
            t.expand(t.expanded);
        }, 10);
       
    },
    setExpand : function(state) // expand/collapse this, and reflect on others...
    {
        this.expanded = state;
        this.expand(state);
        this.collapseOthers(state);
        this.fireEvent('expand', this, state);
    },
    expand : function(state)
    {
        this.expanded = state;
        var l = this.fs.child('legend');
        l.setStyle('background-position', state ?  '0 -16px': '0 0');
        //l.setStyle(
        //    'background','transparent url('+ Ext.rootURL + 'images/default/menu/' +
        //        (state ? '' : 'un') + 'checked.gif) no-repeat scroll 0 0'
        //);
        this.fs.setStyle('padding', state && !this.defaultBorder.length ? '10 10 5' : '0 10');
        this.fs.setStyle('border', state ? this.defaultBorder : 'none');
        var d = this.fs.query('div');
        if (this.hiddenEl) {
            this.hiddenEl.dom.value = state * 1;
        }
        this.el.setVisible(state);
        //Ext.each(d, function(e) {
        //    Ext.get(e).setVisibilityMode(Ext.Element.DISPLAY);
        //    Ext.get(e).setVisible(state);
        //});
        
        
    },
    
    collapseOthers:  function(state)
    {
        if (!this.form) { // needs context
            return;
        }
        var _this = this;
        Ext.each(this.form.allItems, function(f){
            
            
            if (f.collapseGroup &&  f.collapseGroup == _this.collapseGroup && _this.name != f.name) {
                // toggle it!!!
                f.expand(!state);
                if (!state) {
                    return false; // no more... (if we unexpand the only the first get's expanded..
                }
            }
        });
    },
    
    
    setLegend : function(text){
        if(this.rendered){
            var l = this.fs.child('legend');
            l.update(text);
             
        }
    }
});