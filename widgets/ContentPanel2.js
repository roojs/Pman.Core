//<script type="text/javascript">


// I'm not sure if this is the best way to do this..


Roo.ContentPanel2 = function(el, config, content){
     Roo.ContentPanel2.superclass.constructor.call(this,el, config, content);
     
}
Roo.extend(Roo.ContentPanel2, Roo.ContentPanel, {
    adjustForComponents : function(width, height){
        if(this.resizeEl != this.el){
            width -= this.el.getFrameWidth('lr');
            height -= this.el.getFrameWidth('tb');
        }
        if(this.toolbar){
            var te = this.toolbar.getEl();
            height -= te.getHeight();
            te.setWidth(width);
        }
        if(this.toolbar2){
            var te = this.toolbar2.getEl();
            height -= te.getHeight();
            te.setWidth(width);
        }
        if(this.adjustments){
            width += this.adjustments[0];
            height += this.adjustments[1];
        }
        return {'width': width, 'height': height};
    }
});
        