//<script type="text/javascript">
/**
 * @class Ext.form.Checkbox
 * @extends Ext.form.Field
 * Single checkbox field.  Can be used as a direct replacement for traditional checkbox fields.
 * @constructor
 * Creates a new Checkbox
 * @param {Object} config Configuration options
 */
Ext.form.StarField = function(config){
    Ext.form.StarField.superclass.constructor.call(this, config);
     
};

Ext.extend(Ext.form.StarField, Ext.form.Field,  {
     
     /**
     * @cfg {String} focusClass The CSS class to use when the checkbox receives focus (defaults to undefined)
     */
    
    /**
     * @cfg {String} focusClass The CSS class to use when the checkbox receives focus (defaults to undefined)
     */
    focusClass : undefined,
    /**
     * @cfg {String} fieldClass The default CSS class for the checkbox (defaults to "x-form-field")
     */
    fieldClass: 'x-form-field',
    /**
     * @cfg {Boolean} checked True if the the checkbox should render already checked (defaults to false)
     */
    checked: false,
    /**
     * @cfg {String/Object} autoCreate A DomHelper element spec, or true for a default element spec (defaults to
     * {tag: "input", type: "checkbox", autocomplete: "off"})
     */
   // defaultAutoCreate : { tag: 'div' },
     defaultAutoCreate : { tag: 'input', type: 'hidden', autocomplete: 'off'},
    /**
     * @cfg {String} addTitle Text to include for adding a title.
     */
     addTitle : false,
    //
    onResize : function(){
        Ext.form.Field.superclass.onResize.apply(this, arguments);
        
    },

    initEvents : function(){
        // Ext.form.Checkbox.superclass.initEvents.call(this);
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
        
        this.style = this.style || '';
        var style = this.style;
        var _this= this;
        delete this.style;
        
        Ext.form.StarField.superclass.onRender.call(this, ct, position);
        this.wrap = this.el.wrap({cls: 'x-menu-check-item '});
        this.viewEl = this.wrap.createChild({ tag: 'img',  src: Roo.BLANK_IMAGE_URL });
        
        
        this.viewEl.applyStyles('width:60px;height:15px;margin:3px;margin-left:1px;');
        this.viewEl.applyStyles('background-image: url(' + rootURL +'/Pman/templates/images/fivestars.gif);' );
        
        
        //if(this.inputValue !== undefined){
        //this.setValue(this.value);
        this.viewEl.on('mouseup',  function(e) { 
            var xy = _this.viewEl.getXY();
            var offset = Math.floor( (e.xy[0] - xy[0]) / 12.0) +1;
            _this.setValue(offset);
        });
        this.viewEl.on('mousemove',  function(e) { 
            var xy = _this.viewEl.getXY();
            var offset = Math.floor( (e.xy[0] - xy[0]) / 12.0) +1;
            _this.viewEl.setStyle('background-color', '#eee');
            _this.viewEl.setStyle('background-position', '-' + (60 - (offset * 12)) + ' 0');
        });
        this.viewEl.on('mouseout',  function(e) { 
            _this.setValue(_this.value);
           
        });
        
    },

    // private
    initValue : Ext.emptyFn,

    /**
     * Returns the checked state of the checkbox.
     * @return {Boolean} True if checked, else false
     */
    
    /**
     * Sets the value of the item. 
     * @param {Boolean/String} checked True, 'true', '1', or 'on' to check the checkbox, any other value will uncheck it.
     */
    setValue : function(v){
        
        v = parseInt(v);
        
        if (isNaN(v)) {
            v = 0;
        }
        v = v < 0 ? 0 : v;
        v = v > 5 ? 5 : v;
        
        this.value = v;
        this.viewEl.setStyle('background-color', '#fff');
        this.viewEl.setStyle('background-position', '-' + (60 - (v * 12)) + ' 0');
        
        
        Roo.form.StarField.superclass.setValue.call(this, v);
    }
    
     
    
    
});