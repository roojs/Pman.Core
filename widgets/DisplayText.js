/*
 * Based on:
 * Ext JS Library 1.1.1
 * Copyright(c) 2006-2007, Ext JS, LLC.
 *
 * Originally Released Under LGPL - original licence link has changed is not relivant.
 *
 * Fork - LGPL
 * <script type="text/javascript">
 */
/**
 * @class Roo.form.DisplayText
 * @extends Roo.form.Field
 * Display text field 
 * @constructor
 * Creates a new Display text fiedl
 * @param {Object} config Configuration options
 */
Roo.form.DisplayText = function(config){
    Roo.form.DisplayText.superclass.constructor.call(this, config);
    
};

Roo.extend(Roo.form.DisplayText, Roo.form.Field,  {
     /**
     * @cfg {Number} width  - mostly ignored
     */
    width : 100,
    /**
     * @cfg {Number} height - used to restrict height of image..
     */
    width : 50,
    /**
     * @cfg {String} focusClass The CSS class to use when the checkbox receives focus (defaults to undefined)
     */
    focusClass : undefined,
    /**
     * @cfg {String} fieldClass The default CSS class for the checkbox (defaults to "x-form-field")
     */
    fieldClass: 'x-form-field',
    
    /**
     * @cfg {String/Object} autoCreate A DomHelper element spec, or true for a default element spec (defaults to
     * {tag: "input", type: "checkbox", autocomplete: "off"})
     */
     
    defaultAutoCreate : { tag: 'input', type: 'hidden', autocomplete: 'off'},

    /**
     * @cfg {String} inputValue The value that should go into the generated input element's value attribute
     */
    //
    onResize : function(){
        Roo.form.Field.superclass.onResize.apply(this, arguments);
        
    },

    initEvents : function(){
        // Roo.form.Checkbox.superclass.initEvents.call(this);
        // has no events...
       
    },


    getResizeEl : function(){
        return this.wrap;
    },

    getPositionEl : function(){
        return this.wrap;
    },

    // private
    onRender : function(ct, position){
        Roo.form.DisplayText.superclass.onRender.call(this, ct, position);
        //if(this.inputValue !== undefined){
        
        this.style = this.style || '';
        var style = this.style;
        delete this.style;
        
        Roo.form.DisplayImage.superclass.onRender.call(this, ct, position);
        this.wrap = this.el.wrap({cls: 'x-menu-check-item'});
        this.viewEl = this.wrap.createChild({ tag: 'div'});
        
        if (style) {
            this.viewEl.applyStyles(style);
        }
        this.viewEl.setStyle('padding', '2px');
        if (this.width) {
            this.viewEl.setWidth(this.width);
        }
        if (this.height) {
            this.viewEl.setHeight(this.height);
        }
        this.setValue(this.value);
        

        
        
    },

    // private
    initValue : Ext.emptyFn,

  

	// private
    onClick : function(){
        
    },

    /**
     * Sets the checked state of the checkbox.
     * @param {Boolean/String} checked True, 'true', '1', or 'on' to check the checkbox, any other value will uncheck it.
     */
    setValue : function(v){
        this.value = v;
        var html = this.renderer ?  this.renderer(v) : String.format('{0}', v);

        this.viewEl.dom.innerHTML = html;
        Roo.form.DisplayText.superclass.setValue.call(this, v);

    }
});