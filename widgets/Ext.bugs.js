// <script type="text/javascript">
 /* this knocks about 3000ms off the startup - total is 13000 originally. */
 
 
  
 (function(){
	var propCache = {},
		camelRe = /(-[a-z])/gi,
		classReCache = {},
		view = document.defaultView,
		propFloat = Ext.isIE ? 'styleFloat' : 'cssFloat',
		opacityRe = /alpha\(opacity=(.*)\)/i;
    Ext.propCache = propCache; 
    
	function camelFn (a) {
		return a.charAt(1).toUpperCase();
	};
	function chkCache(prop){
		return propCache[prop] ||
			(propCache[prop] = prop == 'float' ? 
                propFloat : prop.replace(camelRe, camelFn));
	};
	Ext.override(Ext.Element, {
		getStyle : function(){
			return view && view.getComputedStyle ?
				function(prop){
                    // ie!!!.
					var el = this.dom, v, cs;
					if(el == document) { return null; }
					prop = chkCache(prop);
                    
					return (v = el.style[prop]) ? v :
						   (cs = view.getComputedStyle(el, '')) ?
                            cs[prop] : null;
				} :
				function(prop){
					var el = this.dom, m, cs;
					if(el == document) return null;
					if (prop == 'opacity') {
						if (el.style.filter.match) {
							if(m = el.style.filter.match(opacityRe)){
								var fv = parseFloat(m[1]);
								if(!isNaN(fv)){
									return fv ? fv / 100 : 0;
								}
							}
						}
						return 1;
					}
					prop = chkCache(prop);
					return el.style[prop] || ((cs = el.currentStyle) ? cs[prop] : null);
				};
		}(),
		setStyle : function(prop, value){
			var tmp,
				style,
				camel;
			if (typeof(prop) != 'object') {
				tmp = {};
				tmp[prop] = value;
				prop = tmp;
			}
			for (style in prop) {
				value = prop[style];
				style == 'opacity' ?
					this.setOpacity(value) :
					this.dom.style[chkCache(style)] = value;
			}
			return this;
		}
	})
})();


// added to roo...
Ext.override(Ext.dd.StatusProxy, {
	update : function(html){
		if(typeof html == 'string'){
			this.ghost.update(html);
		}else{
			this.ghost.update('');
			html.style.margin = '0';
			this.ghost.dom.appendChild(html);
		}
		var el = this.ghost.dom.firstChild;
		if(el){
			Ext.fly(el).setStyle('float', 'none');
		}
	}
});

// added to roo....
Ext.override(Ext.grid.ColumnModel, {
    
    
    getIndexByDataIndex : function(x){ // fixme - find refs and remove them!
        return this.findColumnIndex(x);
    }
});
 
Ext.override(Ext.grid.GridView, { 
  
    updateColumns : function(){ // this was added before I worked out the fix to the cols.
        this.grid.stopEditing();
        var cm = this.grid.colModel, colIds = this.getColumnIds();
        //var totalWidth = cm.getTotalWidth();
        var pos = 0;
        var ci = '';
        var w = 0;
        for(var i = 0, len = cm.getColumnCount(); i < len; i++){
            //if(cm.isHidden(i)) continue;
            w = cm.getColumnWidth(i);
            ci = Roo.isSafari ? colIds[i].toLowerCase() : colIds[i];
          //  console.log('UPDATE COLS: " +this.colSelector+colIds[i] + '=>' + (w - this.borderWidth)+ "px");
            this.css.updateRule(this.colSelector+ci, 'width', (w - this.borderWidth) + 'px');
            
            this.css.updateRule(this.hdSelector+ci, 'width', (w - this.borderWidth) + 'px');
        }
        this.updateSplitters();
       // this.forceLayout.defer(100,this);
    } 



});
 
 
 /*
String.format =  function(format) {
    var args = Array.prototype.slice.call(arguments, 1);
    return format.replace(/\{(\d+)\}/g, function(m, i) {
       
        var e = document.createTextNode( args[i]  );
        var ew = document.createElement('a');
        ew.appendChild(e);
        return ew.innerHTML;
    });
};
*/
Ext.grid.ColumnModel.defaultRenderer = function(value){
    if (typeof value == 'undefined') {
        return '&#160;';
    }
	if (typeof value == 'string' && value.length < 1){
	    return '&#160;';
	}
    //console.log(value);
    return String.format('{0}',value);   
};
 
// as 'findbyId in roo...
Ext.form.Form.prototype.stackFind = function(id)
{
    return this.findbyId(id);
     
};

// This is in Roo???
/*
Ext.form.Hidden = function(config){
    Ext.form.Hidden.superclass.constructor.call(this, config);
};

Ext.extend(Ext.form.Hidden, Ext.form.TextField, {
    fieldLabel: '',
    inputType: 'hidden',
    width: 50,
    allowBlank: true,
    labelSeparator: '',
     
    hidden: true,
    itemCls : 'x-form-item-display-none'


});


 Why is this here.. it does not work very well...
Ext.form.DateField.prototype.getValue = function(fmt) {
    var r = this.parseDate(Ext.form.DateField.superclass.getValue.call(this)) || '';
    if (typeof(fmt) == 'undefined') {
        return r;
    }
    if (typeof(r) == 'string') {
        return '';
    }
    return r.format(fmt);
};
*/
//<script type="text/Javascript">

/**
 * @class Ext.form.FormButton
 * @extends Ext.Button
 * Single checkbox field.  Can be used as a direct replacement for traditional checkbox fields.
 * @constructor
 * Creates a new Checkbox
 * @param {Object} config Configuration options
 */
 
Ext.form.FormButton = function(config){
    this.config = config;
    Ext.form.FormButton.superclass.constructor.call(this, config);
    
    
};
Ext.extend(Ext.form.FormButton, Ext.form.Field, {
    config : false,
    labelSeparator: '',
    defaultAutoCreate : { tag: 'input', type: 'hidden'},
    onRender : function(ct, position){
        Roo.form.Checkbox.superclass.onRender.call(this, ct, position);
        // remove label..
        var p = Ext.get(this.container.findParentNode('div'));
        var ch = p.child('label');
        if (ch) {
            ch.remove();
        }
        
        // add our button..
        var bc = this.container.createChild({ tag : 'div' });
          
        new Ext.Button(bc, this.config);
        
    }
    
    
});

Roo.Toolbar.prototype.hide = function()
{
    this.el.child('div').setVisibilityMode(Roo.Element.DISPLAY);
    this.el.child('div').hide();
};
Roo.Toolbar.prototype.show = function()
{
    this.el.child('div').show();
};

/* what's this for??? - I think to add x-toolbar-td - is it needed???*/
Roo.Toolbar.prototype.nextBlock = function(){
  
    var td = document.createElement('td');
    this.tr.appendChild(td);
    td.className = 'x-toolbar-td';
    return td;
    
};
