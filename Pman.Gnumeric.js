//<script type="text/javascript">

/**
 * Pman Gnumeric.
 * 
 * -> load up a remote xml file of a gnumeric document.
 * 
 * -> convert into a usable data structure
 * 
 * -> ?? apply templated values ??
 * -> allow modification of fields
 * 
 * -> render to screen.
 * 
 * -> send for conversion to XLS (via ssconvert)
 * 
 * usage:
 * 
   
    new Pman.Gnumeric( {
      url: rootURL + '/xxx/yyy/templates/reports/myreport.xml',
      data: { ..... },
      listeners : {
          load : function()
          {
          
               x.applyData({ ... }); // key value data looks for {value} in strings and replaces it..
               
               x.set('A3', 'test');
               
               mypanel.update(x.toHTML());
               
               x.download()       
               
           }
      }
    });
    
   
 
 
 * 
 * FIXME: - sheet - we currently use first sheet only..
 * 
 * 
 */

Pman.Gnumeric = function (cfg)
{
    cfg.data = cfg.data || {};
    
    
    
    
    
    
    this.addEvents({
        /**
	     * @event load
	     * Fires when source document has been loaded
	     * @param {Pman.Gnumerci} this
	     */
	    "load" : true
    }); 
    
    Roo.util.Observable.call(this,cfg);
    
    this.defaultCell = {
        valueType : 0,
        valueFormat : '',
        value : '',
        colspan: 1,
        rowspan: 1
          
    };
     
    this.load();
    
    this.applyData(cfg.data);
    
    
    
}
Roo.extend(Pman.Gnumeric.prototype, Roo.Observable, {
    
    /**
     * @cfg {String} url the source of the Gnumeric document.
     */
    url : false,
      /**
     * @cfg {Object} data overlay data for spreadsheet - from constructor.
     */
    data : false,
     
    /**
     * @prop {XmlDocument} doc the gnumeric xml document
     */
    doc : false,
    
    /**
     * @prop {XmlNode} sheet the 'Sheet' element 
     */
    sheet : false,
    /**
     * @prop {Object} grid the map[row][col] = cellData 
     */
    grid : false,
    /**
     * @prop {Object} colInfo - list of column sizes
     */
    colInfo : false,
    /**
     * @prop {Object} rowInfo - list of row sizes
     */
    rowInfo : false,
    
    /**
     * @prop {Number} cmax - maximum number of columns
     */
    cmax: false,
    /**
     * @prop {Object} rmax - maximum number of rows
     */
    rmax : false,
    /**
     * load:
     * run the connection, parse document and fire load event..
     * 
    */
    
    load : function(url)
    {
        url = url || this.url;
        if (!url) {
            return;
        }
        _t = this;
        var c = new Roo.data.Connection();
        c.request({
            url: url,
            method:  'GET',
            success : function(resp, opts) {
                _t.doc = resp.responseXML;
                _t.parseDoc();
                _t.parseStyles();
                _t.overlayStyles();
                _t.fireEvent('load', _t);
            },
            failure : function()
            {
                Roo.MessageBox.alert("Error", "Failed to Load Template for Spreadsheet");
            }
        });
        

    },
    
    
    
    /**
     * toRC:
     * convert 'A1' style position to row/column reference
     * 
     * @arg {String} k cell name
     * @return {Object}  as { r: {Number} , c: {Number}  }
     */
    
    toRC : function(k)
    {
          var c = k.charCodeAt(0)-64;
        var n = k.substring(1);
        if (k.charCodeAt(1) > 64) {
            c *=26;
            c+=k.charCodeAt(1)-64;
            n = k.substring(2);
        }
        return { c:c -1 ,r: (n*1)-1 }
    },
      /**
     * rangeToRC:
     * convert 'A1:B1' style position to array of row/column references
     * 
     * @arg {String} k cell range
     * @return {Array}  as [ { r: {Number} , c: {Number}  }. { r: {Number} , c: {Number}  } ]
     */
    rangeToRC : function(s) {
        var ar = s.split(':');
        return [ this.toRC(ar[0]) , this.toRC(ar[1])]
    },
    
    
    
   
    
    /**
     * parseDoc:
     * convert XML document into cells and other data..
     * 
     */
    parseDoc : function() 
    {
        var _t = this;
        this.grid = {}
        this.rmax = 1;
        this.cmax = 1;
        
        this.sheet = _this.doc.getElementsByTagName('Sheet')[0];
        var cells = this.sheet.getElementsByTagName('Cell');

        
        
        Roo.each(cells, function(c) {
           // Roo.log(c);
            var row = c.getAttribute('Row') * 1;
            var col = c.getAttribute('Col') * 1;
            _t.cmax = Math.max(col+1, _t.cmax);
            _t.rmax = Math.max(row+1, _t.rmax);
            var vt = c.getAttribute('ValueType');
            var vf = c.getAttribute('ValueFormat');
            var val = c.textContent;
            
            if (typeof(_t.grid[row]) == 'undefined') {
                _t.grid[row] ={};
            }
            _t.grid[row][col] = Roo.applyIf({
                valueType : vt,
                valueFormat : vf,
                value : val,
                dom: c
            }, _t.defaultCell);
        });
       
        for (var r = 0; r < _t.rmax;r++) {
            if (typeof(this.grid[r]) == 'undefined') {
              this.grid[r] ={};
            }
            for (var c = 0; c < cmax;c++) {
                if (typeof(this.grid[r][c]) == 'undefined') {
                    continue;
                }
                //this.print( "[" + r + "]["+c+"]=" + grid[r][c].value +'<br/>');
            }
        }
        
        var merge = this.sheet.getElementsByTagName('Merge');

        Roo.each(merge, function(c) {
            var rc = _t.rangeToRC(c.textContent);
            //Roo.log(JSON.stringify(rc))
            if (typeof(_t.grid[rc[0].r][rc[0].c]) == 'undefined') {
                _t.grid[rc[0].r][rc[0].c] =  Roo.apply({}, _t.defaultCell);
            }
                
            _t.grid[rc[0].r][rc[0].c].colspan = (rc[1].c - rc[0].c) + 1;
            _t.grid[rc[0].r][rc[0].c].rowspan = (rc[1].r - rc[0].r) + 1;
            for(var r = (rc[0].r); r < (rc[1].r+1); r++) {
               for(var c = rc[0].c; c < (rc[1].c+1); c++) {
                    //Roo.log('adding alias : ' + r+','+c);
                   _t.grid[r][c] = _t.grid[rc[0].r][rc[0].c];
               }
           }
            
            
        });
        // read colinfo..
        var ci = this.sheet.getElementsByTagName('ColInfo');
        this.colInfo = {};
        
        Roo.each(ci, function(c) {
            var count = c.getAttribute('Count') || 1;
            var s =  c.getAttribute('No')*1;
            for(var i =0; i < count; i++) {
                _t.colInfo[s+i] = Math.floor(c.getAttribute('Unit')*1);
            }
        });
        
        
        ci = this.sheet.getElementsByTagName('RowInfo');
        
        this.rowInfo = {};
        Roo.each(ci, function(c) {
            var count = c.getAttribute('Count') || 1;
            var s =  c.getAttribute('No')*1;
            for(var i =0; i < count; i++) {
                _t.rowInfo[s+i] = Math.floor(c.getAttribute('Unit')*1);
            }
        });
    
        
        
     
        
    },
     /**
     * overlayStyles:
     * put the style info onto the cell data.
     * 
     */
    overlayStyles : function ()
    {
           // apply styles.
        var _t = this;
        Roo.each(this.styles, function(s) {
       
            for (var r = s.r; r < s.r1;r++) {
                if (typeof(_t.grid[r]) == 'undefined') {
                   continue;
                }
                for (var c = s.c; c < s.c1;c++) {
                   if (c > _t.cmax) continue;
    
                    if (typeof(_t.grid[r][c]) == 'undefined') _t.grid[r][c] = Roo.apply({}, _t.defaultCell);
                    var g=_t.grid[r][c];
                    if (typeof(g.cls) =='undefined') g.cls = [];
                    if (g.cls.indexOf(s.name)  > -1) continue;
                    g.cls.push(s.name);
                }
            }
        });
    },
     /**
     * parseStyles: 
     *  read the style information
     * generates a stylesheet for the current file
     * this should be disposed of really.....
     * 
     */
    parseStyles : function() {
                
        var srs = this.sheet.getElementsByTagName('StyleRegion');
        var _t  = this;
        var ent = {};
        
        var map =  {
            HAlign : function(ent,v) { 
                ent['text-align'] = { '1' : 'left', '8': 'center', '4' : 'right'}[v] || 'left';
            },
            VAlign : function(ent,v) { 
                ent['vertical-align'] = { '1' : 'top', '4': 'middel', '8' : 'bottom'}[v]  || 'top'
            },
            Fore : function(ent,v) { 
                var col=[];
                Roo.each(v.split(':'), function(c) { col.push(Math.round(parseInt(c,16)/256)); })
                ent['color'] = 'rgb(' + col.join(',') + ')';
            },
            Back : function(ent,v) { 
                var col=[];
                Roo.each(v.split(':'), function(c) { col.push(Math.round(parseInt(c,16)/256)); })
                ent['background-color'] = 'rgb(' + col.join(',') + ')';
            },
            FontUnit : function(ent,v) { 
                ent['font-size'] = v + 'px';
            },
            FontBold : function(ent,v) { 
                if (v*1 < 1) return;
                ent['font-weight'] = 'bold';
            },
            FontItalic : function(ent,v) { 
                if (v*0 < 1) return;
                //ent['font-weight'] = 'bold';
            },
            FontName : function(ent,v) { 
                ent['font-family'] = v;
            },
            BorderStyle : function(ent,v) { 
                var vv  = v.split('-');
                ent['border-'+vv[0]+'-style'] = 'solid';
                ent['border-'+vv[0]+'-width'] = vv[1]+'px';
            },
            BorderColor : function(ent,v) { 
                var vv  = v.split('-');
                var col=[];
                Roo.each(vv[1].split(':'), function(c) { col.push(Math.round(parseInt(c,16)/256)); })
                ent['border-'+vv[0]+'-color'] = 'rgb(' + col.join(',') + ')';
            }
        }
        function add(e, k, v) {
            //Roo.log(k,v);
            e.gstyle[k] = v;
            if (typeof(map[k]) == 'undefined') {
                return;
            }
            map[k](e.style,v);    
        }
        var css = {};
        var styles = [];
        var sid= Roo.id();
        
        
        Roo.each(srs, function(sr,n)
        {
            ent = {
                c : sr.getAttribute('startCol') *1,
                r : sr.getAttribute('startRow')*1,
                c1 : (sr.getAttribute('endCol')*1) +1,
                r1 : (sr.getAttribute('endRow')*1) +1,
                style : {},
                gstyle : {},
                name : sid +'-gstyle-' + n
                
            };
    
            Roo.each(sr.getElementsByTagName('Style')[0].attributes, function(e) { 
                add(ent, e.name, e.value);
            });
            if (sr.getElementsByTagName('Font').length) {
                Roo.each(sr.getElementsByTagName('Font')[0].attributes, function(e) { 
                     add(ent, 'Font'+e.name, e.value);
    
                });
                add(ent, 'FontName', sr.getElementsByTagName('Font')[0].textContent);
    
            }
            if (sr.getElementsByTagName('StyleBorder').length) {
                Roo.each(sr.getElementsByTagName('StyleBorder')[0].childNodes, function(e) {
                    if (!e.tagName) {
                        return;
                    }
                    Roo.each(e.attributes, function(ea) { 
                        add(ent, 'Border'+ea.name, e.tagName.split(':')[1].toLowerCase() + '-' + ea.value);
                    });
                })
                    
            }
            styles.push(ent);
            css['.'+ent.name] = ent.style;
        });
        
        this.styles = styles;
        
        
        Roo.util.CSS.createStyleSheet(css, sid);
    },

    /* ---------------------------------------  AFTER LOAD METHODS... ----------------------- */
    /**
     * set: 
     * Set the value of a cell..
     * @param {String} cell name of cell, eg. C10
     * @param {Value} value to put in cell..
     * 
     * Cells should exist at present, we do not make them up...
     */
     
    
    set : function(cell, v) {
        
        var cs= typeof(cell == 'string') ? this.toRC(cell) : cell;
        Roo.log(    this.grid[cs.r][cs.c]);
        this.grid[cs.r][cs.c].value=  v;
        // need to generate clell if it doe
        if (typeof(this.grid[cs.r][cs.c].dom) == 'undefined') {
            Roo.log('no default content for cell:' + cell);
            return;
        }
        this.grid[cs.r][cs.c].dom.textContent=  v;
    },
    /**
     * applyData: 
     * Set the value of a cell..
     * @param {String} cell name of cell, eg. C10
     * @param {Value} value to put in cell..
     * 
     * Cells should exist at present, we do not make them up...
     */
     
    applyData : function(data)
    {
        for (var r = 0; r < this.rmax;r++) {
            if (typeof(_this.grid[r]) == 'undefined') continue;
            for (var c = 0; c < this.cmax;c++) {  
                if (typeof(_this.grid[r][c]) == 'undefined') {
                    continue;
                }
                if (!_this.grid[r][c].value.length 
                        || !_this.grid[r][c].value.match(/\{/)) {
                    continue;
                }
                
                var x = new Roo.Template({ html: _this.grid[r][c].value });
                try {
                    this.set({ r: r, c: c}, x.applyTemplate(data));
                } catch (e) {
                    // continue?
                }
                
            }
        }
            
    },
    
    
     /**
     * toHTML: 
     * Convert spreadsheet into a HTML table.
     */
            
    toHTML :function()
    {
         var _t = this;
        function calcWidth(sc, span)
        {
            var n =0;
            for(var i =sc; i< sc+span;i++) {
                n+=_t.colInfo[i];
            }   
            return n;
        }
        
        var grid = this.grid;
        // lets do a basic dump..
        var out = '<table style="table-layout:fixed;" cellpadding="0" cellspacing="0">';
        for (var r = 0; r < this.rmax;r++) {
            out += '<tr style="height:'+this.rowInfo[r]+'px;">';
            for (var c = 0; c < this.cmax;c++) {
                var g = (typeof(grid[r][c]) == 'undefined') ? defaultCell  : grid[r][c];
                if (typeof(g.cls) =='undefined') g.cls = [];
                var w= calcWidth(c,g.colspan);
                out+=String.format('<td colspan="{0}" rowspan="{1}"  class="{4}"><div style="{3}">{2}</div></td>', 
                    g.colspan, g.rowspan, g.value,
                    'overflow:hidden;' + 
                    'width:'+w+'px;' +
                   
                    'text-overflow:ellipsis;' +
                    'white-space:nowrap;',
                     g.cls.join(' ')
    
    
                );
                c+=(g.colspan-1);
            }
            out += '</tr>';
        }
        //Roo.log(out);
        return out+'</table>';
        
        
        
    }

});