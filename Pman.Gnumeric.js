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
   
   x = new Pman.Gnumeric( {
      url: rootURL + '/xxx/yyy/templates/reports/myreport.xml',
      data: { ..... }
    });
    
   x.applyData({ ... }); // key value data looks for {value} in strings and replaces it..
   
   x.set('A3', 'test');
   
   mypanel.update(x.toHTML());
   
   x.download()
 
 
 * 
 * FIXME: - sheet - we currently use first sheet only..
 * 
 * 
 */

Pman.Gnumeric = function (cfg)
{
    cfg.data = cfg.data || {};
    
    
    
    
    Roo.apply(this,cfg);
    
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
Roo.apply(Pman.Gnumeric.prototype, {
    
    /**
     * @cfg {String} url the source of the Gnumeric document.
     */
    url : '',
      /**
     * @cfg {Object} data overlay data for spreadsheet - from constructor.
     */
    data : false,
     
    /**
     * @prop {XmlDocument} doc the gnumeric xml document
     */
    doc : false,
    
    /**
     * @prop {XmlDocument} doc the gnumeric xml document
     */
    sheet : false,
    
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
      
        
        var sheet = _this.doc.getElementsByTagName('Sheet')[0];
        var cells = sheet.getElementsByTagName('Cell');
        var grid ={};
        var rmax = 1;
        var cmax = 1;
        
        Roo.each(cells, function(c) {
           // Roo.log(c);
            var row = c.getAttribute('Row') * 1;
            var col = c.getAttribute('Col') * 1;
            cmax = Math.max(col+1, cmax);
            rmax = Math.max(row+1, rmax);
            var vt = c.getAttribute('ValueType');
            var vf = c.getAttribute('ValueFormat');
            var val = c.textContent;
            if (typeof(grid[row]) == 'undefined') {
                grid[row] ={};
            }
            grid[row][col] = Roo.applyIf({
                valueType : vt,
                valueFormat : vf,
                value : val,
                dom: c
            }, defaultCell);
        });
       
        for (var r = 0; r < rmax;r++) {
            if (typeof(grid[r]) == 'undefined') {
              grid[r] ={};
            }
            for (var c = 0; c < cmax;c++) {
                if (typeof(grid[r][c]) == 'undefined') {
                    continue;
                }
                //this.print( "[" + r + "]["+c+"]=" + grid[r][c].value +'<br/>');
            }
        }
        
         var merge = sheet.getElementsByTagName('Merge');
         var t= this;
         Roo.each(merge, function(c) {
            var rc = t.rangeToRC(c.textContent);
            //Roo.log(JSON.stringify(rc))
            if (typeof(grid[rc[0].r][rc[0].c]) == 'undefined') {
                grid[rc[0].r][rc[0].c] =  Roo.apply({}, defaultCell);
            }
                
            grid[rc[0].r][rc[0].c].colspan = (rc[1].c - rc[0].c) + 1;
            grid[rc[0].r][rc[0].c].rowspan = (rc[1].r - rc[0].r) + 1;
            for(var r = (rc[0].r); r < (rc[1].r+1); r++) {
               for(var c = rc[0].c; c < (rc[1].c+1); c++) {
    //            Roo.log('adding alias : ' + r+','+c);
                   grid[r][c] = grid[rc[0].r][rc[0].c];
               }
           }
            
            
        });
        // read colinfo..
         var ci = sheet.getElementsByTagName('ColInfo');
         var colInfo = {};
         Roo.each(ci, function(c) {
            var count =c.getAttribute('Count') || 1;
            var s =  c.getAttribute('No')*1;
            for(var i =0; i < count; i++) {
                colInfo[s+i] = Math.floor(c.getAttribute('Unit')*1);
            }
        });
         ci = sheet.getElementsByTagName('RowInfo');
    
         var rowInfo = {};
         Roo.each(ci, function(c) {
            var count =c.getAttribute('Count') || 1;
            var s =  c.getAttribute('No')*1;
            for(var i =0; i < count; i++) {
                rowInfo[s+i] = Math.floor(c.getAttribute('Unit')*1);
            }
        });
    
        
        this.parseStyles();
        
        // apply styles.
        Roo.each(this.styles, function(s) {
       
            for (var r = s.r; r < s.r1;r++) {
                if (typeof(grid[r]) == 'undefined') {
                   continue;
                }
                for (var c = s.c; c < s.c1;c++) {
                   if (c > cmax) continue;
    
                    if (typeof(grid[r][c]) == 'undefined') grid[r][c] = Roo.apply({}, defaultCell);
                    var g=grid[r][c];
                    if (typeof(g.cls) =='undefined') g.cls = [];
                    if (g.cls.indexOf(s.name)  > -1) continue;
                    g.cls.push(s.name);
                }
            }
        });
        this.grid=  grid;
        this.cmax = cmax;
        this.rmax = rmax;
        this.colInfo = colInfo;
        this.rowInfo = rowInfo;
        
    },


    set : function(cell, v) {
        
        var cs= this.toRC(cell);
        Roo.log(    this.grid[cs.r][cs.c]);
        this.grid[cs.r][cs.c].value=  v;
        // need to generate clell if it doe
        if (typeof(this.grid[cs.r][cs.c].dom) == 'undefined') {
            Roo.log('no default content for cell:' + cell);
            return;
        }
        this.grid[cs.r][cs.c].dom.textContent=  v;
    },
           
            parseStyles : function() {
                var sheet = this.xml.getElementsByTagName('Sheet')[0];
                var srs = sheet.getElementsByTagName('StyleRegion');
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
            print : function(str) {
                var p = this.layout.getRegion('center').getPanel(0);
                var o = p.el.dom.innerHTML;
                p.setContent(o + str, false)
                
            },
            renderXML : function() {
                 this.parseDoc();
                 
                 
                 this.cset('G3', 'TEST')
                 
                 
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
                this.print(out+'</table>');
                
                
                
            },