//<script type="text/javascript">
/**
 * @class Ext.form.Checkbox
 * @extends Ext.form.Field
 * Single checkbox field.  Can be used as a direct replacement for traditional checkbox fields.
 * @constructor
 * Creates a new Checkbox
 * @param {Object} config Configuration options
 */
Ext.form.FormGrid = function(config){
    Ext.form.FormGrid.superclass.constructor.call(this, config);
     
};

Ext.extend(Ext.form.FormGrid, Ext.form.Field,  {
    /**
     * @cfg {Number} width  - mostly ignored
     */
    width : 100,
    /**
     * @cfg {Number} height - used to restrict height of image..
     */
    width : 50,
     /**
     * @cfg {String} grid The embeded grid...
     */
    grid : false, 
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
        delete this.style;
        
        Ext.form.DisplayImage.superclass.onRender.call(this, ct, position);
        this.wrap = this.el.wrap({cls: 'x-menu-check-item '}); // not sure why ive done thsi...
        this.viewEl = this.wrap.createChild({ tag: 'div' });
        if (style) {
            this.viewEl.applyStyles(style);
        }
        if (this.width) {
            this.viewEl.setWidth(this.width);
        }
        if (this.height) {
            this.viewEl.setHeight(this.height);
        }
        //if(this.inputValue !== undefined){
        //this.setValue(this.value);
        
        
        this.grid = new Roo.grid.EditorGrid(this.viewEl, this.grid);
        /*
        var layout = Roo.BorderLayout.create({
            center: {
                margins:{left:3,top:3,right:3,bottom:3},
                panels: [new Roo.GridPanel(grid)]
            }
        }, 'grid-panel');
        */

        this.grid.render();

        
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
        //this.value = v;
        
        Roo.form.FormGrid.superclass.setValue.call(this, v);
    }
    
     
    
    
});