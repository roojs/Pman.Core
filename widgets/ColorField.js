//<script type="text/javascript">


/**
* Color field
*/


Roo.form.ColorField = function(config){
    Roo.form.ColorField.superclass.constructor.call(this, config);
 
   
};
Roo.extend(Roo.form.ColorField, Roo.form.TriggerField , {
     
    defaultAutoCreate : {tag: 'input', type: 'text', size: '6',   autocomplete: 'off'},
    validateValue : function(value){
        this.setBgColor(value);
        return true;
    },
    menuListeners : {
        select: function(m, d){
            this.setValue(d);
        },
        show : function(){ 
            this.onFocus();
        },
        hide : function(){
            this.focus.defer(10, this);
            var ml = this.menuListeners;
            this.menu.un('select', ml.select, this);
            this.menu.un('show', ml.show, this);
            this.menu.un('hide', ml.hide, this);
        }
    },
    onTriggerClick : function(){
        if(this.disabled){
            return;
        }
        if(this.menu == null){
            this.menu = new Roo.menu.ColorMenu();
        }
         
        this.menu.on(Roo.apply({}, this.menuListeners, {
            scope:this
        }));
        //this.menu.picker.setValue(this.getValue() || new Date());
        this.menu.show(this.el, 'tl-bl?');
    }, 
    setValue: function(d) {
        d = (typeof(d) != 'undefined') && d.length ? d : 'FFFFFF';
        
        Roo.form.ColorField.superclass.setValue.call(this, d);
        this.setBgColor(d);
    },
    
    setBgColor : function(d) {
        var d = (typeof(d) != 'undefined') && d.length ? d : 'FFFFFF';
        this.el.dom.style.background ='#' + d;
    }
});