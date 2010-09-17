
//<script type="text/javascript">
/**
 * 
 * old combobox adder.. 
 * this is all incorporated into the new combobox, however the API is different..
 * 
 * to make this work on the new combo, you just implement a 'add' handler.
 */

 
 

Ext.form.ComboBoxAdder = function(config){
    
    if (typeof(config.listeners.adderclick) != 'undefined') {
        config.listeners.add = config.listeners.adderclick;
    }
   Ext.form.ComboBoxAdder.superclass.constructor.call(this, config);  
}
 
Ext.extend(Ext.form.ComboBoxAdder, Ext.form.ComboBox);



Ext.form.TextFieldAdder = function(config){
    
    Ext.form.TextFieldAdder.superclass.constructor.call(this, config);
    this.on('select', function(cb, rec, ix) {
        cb.lastData = rec.data;
    });
    this.addEvents({
        'adderclick' : true
    });
}
 
Ext.extend(Ext.form.TextFieldAdder, Ext.form.TextField, { 
    lastData : false,
    //onAddClick: function() { },
    
    onRender : function(ct, position) 
    {
        Ext.form.TextFieldAdder.superclass.onRender.call(this, ct, position); 
         this.wrap = this.el.wrap({cls: 'x-form-field-wrap'});
        this.adder = this.wrap.createChild(
            {tag: 'img', src: Ext.BLANK_IMAGE_URL, cls: 'x-form-textfield-adder'});  
        var _t = this;
        this.adder.on('click', function(e) {
            _t.fireEvent('adderclick', this, e);
        }, _t);
    }
    
});


Ext.form.TextFieldAdderMinus = function(config){
    
    Ext.form.TextFieldAdder.superclass.constructor.call(this, config);
    this.on('select', function(cb, rec, ix) {
        cb.lastData = rec.data;
    });
    this.addEvents({
        'adderclick' : true,
        'minusclick' : true
    });
}
 
Ext.extend(Ext.form.TextFieldAdderMinus, Ext.form.TextField, { 
    lastData : false,
    //onAddClick: function() { },
    
    onRender : function(ct, position) 
    {
        Ext.form.TextFieldAdder.superclass.onRender.call(this, ct, position); 
         this.wrap = this.el.wrap({cls: 'x-form-field-wrap'});
        this.adder = this.wrap.createChild(
            {tag: 'img', src: Ext.BLANK_IMAGE_URL, width: 16, cls: 'x-form-textfield-adder'});  
        this.minus = this.wrap.createChild(
            {tag: 'img', src: Ext.BLANK_IMAGE_URL, width: 16, cls: 'x-form-textfield-minus'});  
        var _t = this;
        this.adder.on('click', function(e) {
            _t.fireEvent('adderclick', this, e);
        }, _t);
        this.minus.on('click', function(e) {
            _t.fireEvent('minusclick', this, e);
        }, _t);
    }
    
});

