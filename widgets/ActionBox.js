//<script type="text/javascript">


Roo.box = Roo.box || {};

Roo.box.Action = function(cfg) {
    Roo.apply(this, cfg);
    this.midwidth = this.width - 24; 
};

Roo.box.Action.prototype = {
    
    width : 240,
    items : [],
    midwidth : 218,
    title : '',
    zitems: '',
    cls : '',
    render : function(el)
    {
        
        this.initTemplate();
        this.id = Roo.id();
        this.midwidth = this.width - 22;
        this.subwidth = this.midwidth-18;
        Roo.box.Action.tmpl.append(el, this);
        Roo.each(this.items, function(o, i) {
            o.initHandler();
        });
        this.el = Roo.get(this.id);
        this.el.setVisibilityMode(Roo.Element.DISPLAY);
    },
    initTemplate : function()
    {
        if (Roo.box.Action.tmpl) {
            return;
        }
        
        
        Roo.box.Action.tmpl = new Roo.Template(
                    
            '<table id="{id}" style="width: {width}px; border-collapse: collapse;" class="x-action-box {cls}">',
                '<col width="12"/><col width="{midwidth}"/><col width="12"/>',
                '<tbody>',
                    '<tr>',
                        '<td rowspan="2" colspan="2" class="x-action-box-header x-action-box-small-header">',
                            '<div class="x-action-box-properties-header">{title}</div>',
                        '</td>',
                        '<td class="x-action-box-top-right"/>',
                    '</tr>',
                '<tr>',
                    '<td rowspan="2" class="x-action-box-right"/>',
                '</tr>',
                '<tr>',
                    '<td colspan="2" class="x-action-box-body">',
                        '<div style="width: {subwidth}px">{zitems:this.renderItems}</div>',
                    '</td>',
                '</tr>',
                '<tr>',
                    '<td style="width: 12px;" class="x-action-box-bottom-left"></td>',
                    '<td style="width: {midwidth}px;" class="x-action-box-bottom"/>',
                    '<td style="width: 12px;" class="x-action-box-bottom-right"></td>',
                '</tr>',
            '</tbody>',
            '</table>'
        );
        Roo.box.Action.tmpl.renderItems = function (v, allv) {
            return Roo.box.Action.prototype.renderItems(v,allv); // ensure scope of this.. 
        };
        Roo.box.Action.tmpl.compile();
        
    },
    renderItems : function (items, obj) {
        var ret = '';
        //console.log(obj);
        Roo.each(obj.items, function(o, i) {
            obj.items[i] =  Roo.factory(o, Roo.box);
            
            ret += obj.items[i].render(obj);
        });
        //console.log(ret);
        return ret;
    },
    get : function(n) {
        var ret = false;
        Roo.each(this.items, function(o) {
            if (o.name == n) {
                ret = o;
                return false;
            }
        });
        return ret;
    },
    hide: function()
    {
        this.el.hide();
    },
    show: function()
    {
        this.el.show();
    }
    
     
    
};

Roo.box.Link = function(cfg) {
    Roo.apply(this, cfg);
};

Roo.box.Link.prototype = {
    name : false,
    handler : false,
    id : false,
    title : 'empty',
    el: false,
    icon : false,
    render: function(obj) {
        this.id = Roo.id();
        return Roo.DomHelper.markup({
            tag: 'div',
            id : this.id,
            cls : 'x-action-box-action x-action-ico-edit',
            style : 'display: block;' + ( this.icon ? 'background-image: url(' + this.icon + ');' : ''),
            html : String.format('{0}', this.title)
        });
          
    },
    initHandler: function() 
    {
        if (this.id) {
            this.el = Roo.get(this.id);
            this.el.setVisibilityMode(Roo.Element.DISPLAY);
        }
        if (this.el && this.handler) {
            Roo.get(this.id).on('click', this.handler);
        }
         
    },
    
    hide: function() {
        if (this.el) {
            this.el.hide();
        }
    },
    show: function() {
        if (this.el) {
            this.el.show();
        }
    },
    setValue: function(v) {
        if (!this.edid) {
            return;
        }
        this.value = v;
        Roo.get(this.edid).dom.innerHTML = v; // unfiltered..
    }
    
    
};
Roo.box.Hr = function(cfg) {
    Roo.apply(this, cfg);
    
};

Roo.extend(Roo.box.Hr, Roo.box.Link , {
    handler : false,
    id : false,
    render: function(obj) {
        return  '<div  class="x-action-box-hr"></div>'
    }
});

Roo.box.KeyValShort = function(cfg) {
    Roo.apply(this, cfg);
    
};

Roo.extend(Roo.box.KeyValShort, Roo.box.Link , {
    handler : false,
    id : false,
    key: 'key',
    value : 'value',
    render: function(obj) {
        this.id = Roo.id();
        this.edid = Roo.id();
        return  String.format('<div id="{3}" class="x-action-prop-col-div">' + 
                '<span style="font-weight: bolder;">{0}: </span><span id="{2}">{1}</span></div>', 
            this.key, this.value, this.edid,this.id);
    }
}); 




Roo.box.KeyValLong = function(cfg) {
    Roo.apply(this, cfg);
    
};

Roo.extend(Roo.box.KeyValLong, Roo.box.Link , {
    handler : false,
    id : false,
    key: 'key',
    value : 'value',
    render: function(obj) {
        this.id = Roo.id();
        this.edid = Roo.id();
        return  String.format('<span id="{3}"><span style="color: rgb(51, 51, 51); font-weight: bolder;">{0}</span><br/>'+
            '<div id="{2}" style="padding-left: 10px; overflow:hidden;">{1} </div></span>', 
            this.key, this.value, this.edid,this.id).replace(/\n/g, '<BR/>\n');
    }
});

