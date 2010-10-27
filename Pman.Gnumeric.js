//<script type="text/javascript">
/**
* @class Pman Gnumeric.
*-> load up a remote xml file of a gnumeric document.
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
* Usage:
<pre><code>

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
    

</code></pre>
* 
* @constructor
* @param {Object} cfg   Configuration object.
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
        c : 0,
        r : 0,
        valueType : 0,
        valueFormat : '',
        value : '',
        colspan: 1,
        rowspan: 1
          
    };
     
    this.load();
    
   
    
    
}
Roo.extend(Pman.Gnumeric, Roo.util.Observable, {
    
    /**
     * @cfg {String} url the source of the Gnumeric document.
     */
    url : false,
      /**
     * @cfg {Object} data overlay data for spreadsheet - from constructor.
     */
    data : false,
     /**
     * @cfg {String} downloadURL where GnumerictoExcel.php is...
     */
     
    downloadURL : false,
    
    /**
     * @type {XmlDocument} doc the gnumeric xml document
     */
    doc : false,
    
    /**
     * @type {XmlNode} sheet the 'Sheet' element 
     */
    sheet : false,
    
    /**
     * @type {XmlNode} sheet the 'Cells' element 
     */    
    cellholder : false,
    /**
     * @type {Object} grid the map[row][col] = cellData 
     */
    grid : false,
    /**
     * @type {Object} colInfo - list of column sizes
     */
    colInfo : false,
    /**
     * @type {Object} rowInfo - list of row sizes
     */
    rowInfo : false,
    
    /**
     * @type {Number} cmax - maximum number of columns
     */
    cmax: false,
    /**
     * @type {Object} rmax - maximum number of rows
     */
    rmax : false,
       /**
     * @type {String} stylesheetID id of stylesheet created to render spreadsheat
     */
    stylesheetID : false,
    /**
     * load:
     * run the connection, parse document and fire load event..
     * can be run multiple times with new data..
     * 
    */
    
    load : function(url)
    {
        this.url = url || this.url;
        if (!this.url) {
            return;
        }
        // reset stufff..
        this.doc = false;
        this.sheet = false;
        this.grid = false;
        this.colInfo = false;
        this.rowInfo = false;
        this.cmax = false;
        this.rmax = false;
        
        if (this.stylesheetID) {
            
            Roo.util.CSS.removeStyleSheet(this.stylesheetID);
            this.stylesheetID = false;
            
        }
        
        _t = this;
        var c = new Roo.data.Connection();
        c.request({
            url: this.url,
            method:  'GET',
            success : function(resp, opts) {
                _t.response = resp;
                _t.doc = resp.responseXML;
                _t.parseDoc();
                _t.parseStyles();
                _t.overlayStyles();
                _t.applyData();
    
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
        
        this.sheet = _t.doc.getElementsByTagNameNS('*','Sheet')[0];
        
        
        this.cellholder = this.sheet.getElementsByTagNameNS('*','Cells')[0];
        var cells = this.sheet.getElementsByTagNameNS('*','Cell');

        
        
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
                dom: c,
                r: row,
                c: col
            }, _t.defaultCell);
        });
       
        for (var r = 0; r < this.rmax;r++) {
            if (typeof(this.grid[r]) == 'undefined') {
              this.grid[r] ={};
            }
            for (var c = 0; c < this.cmax;c++) {
                if (typeof(this.grid[r][c]) == 'undefined') {
                    continue;
                }
                //this.print( "[" + r + "]["+c+"]=" + grid[r][c].value +'<br/>');
            }
        }
        
        var merge = this.sheet.getElementsByTagNameNS('*','Merge');

        Roo.each(merge, function(c) {
            var rc = _t.rangeToRC(c.textContent);
            //Roo.log(JSON.stringify(rc))
            if (typeof(_t.grid[rc[0].r][rc[0].c]) == 'undefined') {
                _t.grid[rc[0].r][rc[0].c] =  Roo.applyIf({ r : rc[0].r, c : rc[0].c }, _t.defaultCell);
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
        var ci = this.sheet.getElementsByTagNameNS('*','ColInfo');
        this.colInfo = {};
        
        Roo.each(ci, function(c) {
            var count = c.getAttribute('Count') || 1;
            var s =  c.getAttribute('No')*1;
            for(var i =0; i < count; i++) {
                _t.colInfo[s+i] = Math.floor(c.getAttribute('Unit')*1);
            }
        });
        
        
        ci = this.sheet.getElementsByTagNameNS('*','RowInfo');
        
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
    
                    if (typeof(_t.grid[r][c]) == 'undefined') _t.grid[r][c] = Roo.applyIf({ r: r , c : c }, _t.defaultCell);
                    var g=_t.grid[r][c];
                    if (typeof(g.cls) =='undefined') {
                        g.cls = [];
                        g.styles = [];
                    }
                    if (g.cls.indexOf(s.name)  > -1) continue;
                    g.cls.push(s.name);
                    g.styles.push(s.dom);
                    
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
                
        var srs = this.sheet.getElementsByTagNameNS('*','StyleRegion');
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
                style : {},  // key val of style for HTML..
                gstyle : {}, // key val of attributes used..
                name : sid +'-gstyle-' + n,
                dom : sr
                
            };
    
            Roo.each(sr.getElementsByTagNameNS('*','Style')[0].attributes, function(e) { 
                add(ent, e.name, e.value);
            });
            if (sr.getElementsByTagNameNS('*','Font').length) {
                Roo.each(sr.getElementsByTagNameNS('*','Font')[0].attributes, function(e) { 
                     add(ent, 'Font'+e.name, e.value);
    
                });
                add(ent, 'FontName', sr.getElementsByTagNameNS('*','Font')[0].textContent);
    
            }
            if (sr.getElementsByTagNameNS('*','StyleBorder').length) {
                Roo.each(sr.getElementsByTagNameNS('*','StyleBorder')[0].childNodes, function(e) {
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
        
        this.stylesheetID = sid;
        Roo.util.CSS.createStyleSheet(css, sid);
    },

    /* ---------------------------------------  AFTER LOAD METHODS... ----------------------- */
    /**
     * set: 
     * Set the value of a cell..
     * @param {String} cell name of cell, eg. C10 or { c: 1, r :1 }
         
     * @param {Value} value to put in cell..
     * @param {ValueType} type of value
     * @param {ValueFormat} value format of cell
     * 
     * Cells should exist at present, we do not make them up...
     */
     
    
    set : function(cell, v, vt, vf) {
        
        var cs= typeof(cell) == 'string' ? this.toRC(cell) : cell;
        //Roo.log(    this.grid[cs.r][cs.c]);
        // need to generate clell if it doe
        if (typeof(this.grid[cs.r]) == 'undefined') {
            Roo.log('no row:' + cell);
            this.grid[cs.r] = []; // create a row..
            //return;
        }
        if (typeof(this.grid[cs.r][cs.c]) == 'undefined') {
            Roo.log('cell not defined:' + cell);
            this.createCell(cs.r,cs.c);
        }
        if (typeof(this.grid[cs.r][cs.c].dom) == 'undefined') {
            Roo.log('no default content for cell:' + cell);
            return;
        }
        this.grid[cs.r][cs.c].value=  v;
        this.grid[cs.r][cs.c].dom.textContent=  v;
        if (typeof(vt != 'undefined') && vt) {
            this.grid[cs.r][cs.c].valueType = vt;
            this.grid[cs.r][cs.c].dom.setAttribute('ValueType', vt);
        }
        if (typeof(vf != 'undefined') && vf) {
            this.grid[cs.r][cs.c].valueFormat = vf;
            this.grid[cs.r][cs.c].dom.setAttribute('ValueFormat', vf);
        }
        
    },
    
    
    copyRow : function(src, dest) {
        if (dest == src) {
            return;
        }
       // Roo.log('create Row' + dest);
        if (typeof(this.grid[dest]) == 'undefined') {
            this.grid[dest] = {}
        }
        
           
        for (var c = 0; c < this.cmax; c++) {

            this.copyCell({ r: src, c: c } , { r: dest, c: c});
            
        }
        this.rmax = Math.max(this.rmax, dest +1);
        
    },
    
    createCell: function(r,c)
    {
        //<gnm:Cell Row="6" Col="5" ValueType="60">Updated</gnm:Cell>    
        var nc = this.doc.createElementNS('http://www.gnumeric.org/v10.dtd', 'gnm:Cell');
        this.cellholder.appendChild(nc);
        var lb = this.doc.createTextNode("\n");// add a line break..
        this.cellholder.appendChild(lb);
        
        nc.setAttribute('Row', r);
        nc.setAttribute('Col', c);
        nc.setAttribute('ValueType', '60');
        nc.textContent = '';
        
        this.grid[r][c] = Roo.applyIf({
            valueType : '60',
            valueFormat : '',
            value : '',
            dom: nc,
            r: r,
            c: c
            }, _t.defaultCell);
        
        return nc;

    },
    
    
    copyCell : function(src, dest)
    {
        var old = this.grid[src.r][src.c];
        // is it an alias...
        if ((old.c != src.c)  || (old.r != src.r)) {
            // only really works on horizonatal merges..
            
            this.grid[dest.r][dest.c] = this.grid[desc.r][old.c]; // let's hope it exists.
            return;
        }
        
        
        var nc = Roo.apply({}, this.grid[src.r][src.c]);
        
        nc.value = '';
        if (typeof(old.dom) == 'undefined') {
            Roo.log("No cell to copy for " + Roo.encode(src));
            return;
        }
        this.grid[dest.r][dest.c] = nc;
        nc.dom = old.dom.cloneNode(true);
        nc.dom.setAttribute('Row', dest.r);
        nc.dom.setAttribute('Cell', dest.c);
        nc.dom.textContent = '';
        old.dom.parentNode.appendChild(nc.dom);
        if (!old.styles || !old.styles.length) {
            return;
        }
        //Roo.log("DEST");
        //Roo.log(dest);
        //Roo.log("STYLES");
        //  .styles...
        Roo.each(old.styles, function(s) {
            // try and extend existing styles..
            var er = s.getAttribute('endRow') * 1;
            var ec = s.getAttribute('endCol') * 1;
            //Roo.log(s);
            if (dest.r == er) {
                s.setAttribute('endRow', dest.r + 1);
            }
            if (dest.c == ec) {
                s.setAttribute('endCol', dest.c + 1);
            }
            /*var ns = s.cloneNode(true);
            s.parentNode.appendChild(ns);
            ns.setAttribute('startCol', dest.c);
            ns.setAttribute('startRow', dest.r);
            ns.setAttribute('endCol', dest.c + 1);
            ns.setAttribute('endRow', dest.r +1);
            */
        });
        
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
        
        data = data || this.data;
        for (var r = 0; r < this.rmax;r++) {
            if (typeof(this.grid[r]) == 'undefined') continue;
            for (var c = 0; c < this.cmax;c++) {  
                if (typeof(this.grid[r][c]) == 'undefined') {
                    continue;
                }
                if (!this.grid[r][c].value.length 
                        || !this.grid[r][c].value.match(/\{/)) {
                    continue;
                }
                
                var x = new Roo.Template({ html: this.grid[r][c].value });
                try {
                    var res = x.applyTemplate(data);
                    //Roo.log("set " + r  + "," + c + ":"+res)
                    this.set({ r: r, c: c}, x.applyTemplate(data));
                } catch (e) {
                 //   Roo.log(e.toString());
                  //  Roo.log(e);
                    // continue?
                }
                
            }
        }
            
    },
     // now the rows..
    importTable : function (datagrid)
    {
        if (!datagrid) {
            Roo.log("Error table not found!?");
            return;
        }
        function cleanHTML(str) {
            
             var ret = str;
            ret = ret.replace(/&nbsp;/g,'.');
            ret = ret.replace(/\n/g,'.');
            ret = ret.replace(/\r/g,'.');
            var i;
            while (-1 != (i = ret.indexOf(unescape('%A0')))) {
                ret = ret.substring(0,i) + ' ' + ret.substring(i+1,str.length);
            }
            return ret;
        }

        
        // <cell col="A" row="1">Test< / cell>
        // <cell col="B" row="2" type="Number" format="test1">30< / cell>
        var rowOffsets = [];
        var rows = datagrid.getElementsByTagName('tr');
        //alert(rows.length);
        
        for(var row=0;row<rows.length;row++) {
            //var style = document.defaultView.getComputedStyle(rows[row], "");
            
            //if (rows[row].getAttribute('xls:height')) {
            //    this.setRowHeight(row+y_offset, 0 + rows[row].getAttribute('xls:height'));
            //} else {
            //    this.setRowHeight(row+y_offset, 0 + style.height.replace(/[^0-9.]+/g,''));
           // }
            
            var coloffset = 0;
            if (rowOffsets[row]) {
                coloffset += rowOffsets[row];
            }
            var cols = rows[row].getElementsByTagName('td');
            
            
            for(var col=0;col < cols.length; col++) {
                
                
                var colat = col + coloffset;
                /*
                if (cols[col].getAttribute('colspan') && (cols[col].getAttribute('colspan') > 1)) {
                    
                    
                    this.mergeRegion(
                        colat,
                        row +y_offset,
                        colat + (cols[col].getAttribute('colspan') - 1), 
                        row+y_offset + (
                                (cols[col].getAttribute('rowspan') > 1) ?
                                    (cols[col].getAttribute('rowspan') - 1) : 0
                                )
                    );
                    
                    
                    
                    coloffset += (cols[col].getAttribute('colspan') - 1);
                }
               
                if (cols[col].getAttribute('rowspan') && (cols[col].getAttribute('rowspan') > 1)) {
                    // this should really do a merge, but it's pretty damn complex...
                    //this.mergeRegion(colat,row +y_offset,colat + (cols[col].getAttribute('colspan') - 1), row+y_offset);
                    var rroff = cols[col].getAttribute('colspan')  ? (cols[col].getAttribute('colspan') -0): 1;
                    var rr = 0;
                    for (rr = 0; rr < cols[col].getAttribute('rowspan');rr++) {
                        rowOffsets[rr + row] = col + rroff;
                    }
                    
                }
                 */
               
                /*
                var style = this.newStyle();
                if (style.setFrom(cols[col])) {
                    style.add(
                        colat+x_offset,
                        row+y_offset,
                        
                        colat+x_offset + ((cols[col].getAttribute('colspan') > 1) ?
                                    (cols[col].getAttribute('colspan') - 1) : 0),
                        row+y_offset  + ((cols[col].getAttribute('rowspan') > 1) ?
                                    (cols[col].getAttribute('rowspan') - 1) : 0) 
                    );
                }
                
                 */
                // skip blank cells
                if (!cols[col].childNodes.length) {
                    continue;
                }
                
                
                
                
                var vt = '60';
                var vf = false;
                
                switch(cols[col].getAttribute('xls:type')) {
                    case 'int':
                        vt = 30; // int!!!!
                        break;
                        
                    case 'float':
                        vt = 40; // float!!!!
                        if (cols[col].getAttribute('xls:floatformat')) {
                                vf = cols[col].getAttribute('xls:floatformat');
                        }
                        break;
                        
                    case 'date':
                        vt = 30;
                        //ValueFormat="d/m/yyyy" 38635  
                        var vf = 'd/m/yyy';
                        if (cols[col].getAttribute('xls:dateformat')) {
                            vf= cols[col].getAttribute('xls:dateformat');
                        }
                        
                       
                        
                        break;
                    
                    default:
                       
                        break;
                }
                /*
                if (cols[col].getAttribute('xls:src')) {
                    //alert(cols[col].childNodes[0].width);
                    if (this.writeImage(
                        row+y_offset, 
                        colat+x_offset+coloffset, 
                        cols[col].getAttribute('xls:src'), 
                        cols[col].childNodes[0].width, 
                        cols[col].childNodes[0].height
                        )) {
                       
                    }
                    continue;
                }
                */
                 
                if (!cols[col].childNodes[0].nodeValue) {
                    continue;
                }
                if (!cols[col].childNodes[0].nodeValue.replace(/^\s*|\s*$/g,"").length) {
                    continue;
                }
                // strip me.!
                var cell_value_text = cleanHtml(cols[col].childNodes[0].nodeValue);
       
                if (cols[col].getAttribute('xls:percent')) {
                    cell_value_text = '' + ((cell_value_text * 1) / 100);
                }

                if (cell_value_text.length && (vt = 30)) {
                    var bits = cell_value_text.split(/-/);
                    var cur = new Date(bits[0],bits[1]-1,bits[2]);
                    cell_value_text = '' + Math.round((cur.getTime() - Date.UTC(1899,11,30)) / (24 * 60 * 60 * 1000));
                }

                
                
                if (cols[col].getAttribute('xls:formula')) {
                    var s = cols[col].getAttribute('xls:formula');
                    cell.removeAttribute('ValueType');
                    cell_value_text = s.replace(/#row#/g,(row + y_offset + 1));
                }
                this.set({ r: row, c : col}, cell_value_text, vt, vf);
                
                
                
                
                
            }
        }
    }
    

    
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
        
        
        
    },
    /**
     * download:
     * @param {String} name  filename to downlaod (without xls)
     * @param {String} callback  (optional) - callback to call after callback is complete.
     */
    download : function(name,callback)
    {
        name = name || "Missing_download_filename";
        
        if (this.downloadURL && this.downloadURL.charAt(this.downloadURL .length-1) != '/') {
            this.downloadURL += '/';
        }
        
        var ser = new XMLSerializer();
        var x = new Pman.Download({
            method: 'POST',
            params : {
               xml : ser.serializeToString(this.doc),
               format : 'xls', //xml
               debug : 0
               
            },
            url : (this.downloadURL || (baseURL + '/GnumericToExcel/')) + name + '.xls',
            success : function() {
                Roo.MessageBox.alert("Alert", "File should have downloaded now");
                if (callback) {
                    callback();
                }
            }
        });
         
    }

});