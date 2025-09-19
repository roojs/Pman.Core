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
    for reference valuetype values..
    	VALUE_EMPTY	= 10,
	VALUE_BOOLEAN	= 20,
	VALUE_FLOAT	= 40,
	VALUE_ERROR	= 50,
	VALUE_STRING	= 60,
	VALUE_CELLRANGE = 70,
	VALUE_ARRAY	= 80

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
	    'load' : true
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
    
    /**
     * @cfg {String} dateFormat Default date format for HTML output (default: 'd/M/Y')
     */
    this.dateFormat = 'd/M/Y';
     
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
     * @type {Object} colInfoDom - column sizes dom element
     */
    colInfoDom : false,
    /**
     * @type {Object} rowInfo - list of row sizes
     */
    rowInfo : false,
     /**
     * @type {Object} rowInfoDom - dom elements with sizes
     */
    rowInfoDom : false,
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
     * @type {Number} rowOffset - used by table importer to enable multiple tables to be improted
     */
    
    rowOffset : 0,
    
    /**
     * @type {String} format - either XLSX (if images are used) or XLS - as ssconvert does not do images that well.
     */
    
    format : 'xlsx',
    
    
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
        this.colInfoDom = false;
        this.rowInfo = false;
        this.rowInfoDom = false;
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
                
                _t.parseDoc(0);
                
                
                _t.applyData();
    
                _t.fireEvent('load', _t);
            },
            failure : function()
            {
                Roo.MessageBox.alert("Error", "Failed to Load Template for Spreadsheet");
            }
        });
        

    },
    
    
     
    RCtoCell : function(r,c)
    {
        // we wil only support AA not AAA
        var top = Math.floor(c/26);
        var bot = c % 26;
        var cc = top > 0 ? String.fromCharCode('A'.charCodeAt(0) + (top-1)) : '';
        cc += String.fromCharCode('A'.charCodeAt(0)  + bot);
        return cc+'' +r;
        
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
    parseDoc : function(sheetnum) 
    {
        var _t = this;
        this.grid = {}
        this.rmax = 1;
        this.cmax = 1;
        
        this.sheet = _t.doc.getElementsByTagNameNS('*','Sheet')[sheetnum];
        
        
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
            if (vt == 40 && isNaN(val)) {
                vf = _t.dateFormat;
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
            //Roo.log(JSON.stringify(rc));
            if (typeof(_t.grid[rc[0].r][rc[0].c]) == 'undefined') {
                //Roo.log(["creating empty cell for  ",rc[0].r,  rc[0].c ]);
                 _t.createCell(rc[0].r,  rc[0].c );
                //_t.grid[rc[0].r][rc[0].c] =  //Roo.applyIf({ r : rc[0].r, c : rc[0].c }, _t.defaultCell);
            }
                
            _t.grid[rc[0].r][rc[0].c].colspan = (rc[1].c - rc[0].c) + 1;
            _t.grid[rc[0].r][rc[0].c].rowspan = (rc[1].r - rc[0].r) + 1;
            for(var r = (rc[0].r); r < (rc[1].r+1); r++) {
               for(var cc = rc[0].c; cc < (rc[1].c+1); cc++) {
                    //Roo.log('adding alias : ' + r+','+c);
                   _t.grid[r][cc] = _t.grid[rc[0].r][rc[0].c];
               }
           }
            
        });
        // read colinfo..
        var ci = this.sheet.getElementsByTagNameNS('*','ColInfo');
        this.colInfo = {};
        this.colInfoDom = {};
        
        Roo.each(ci, function(c) {
            var count = c.getAttribute('Count') || 1;
            var s =  c.getAttribute('No')*1;
            for(var i =0; i < count; i++) {
                _t.colInfo[s+i] = Math.floor(c.getAttribute('Unit')*1);
                _t.colInfoDom[s+i] = c;
            }
        });
        
        
        ci = this.sheet.getElementsByTagNameNS('*','RowInfo');
        
        this.rowInfo = {};
        this.rowInfoDom = {};
        Roo.each(ci, function(c) {
            var count = c.getAttribute('Count') || 1;
            var s =  c.getAttribute('No')*1;
            for(var i =0; i < count; i++) {
                _t.rowInfoDom[s+i] = c;
                _t.rowInfo[s+i] = Math.floor(c.getAttribute('Unit')*1);
            }
        });
    
        _t.parseStyles();
        _t.overlayStyles();
                
        
     
        
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
                    if (c > _t.cmax) {
                        continue;
                    }
    
                    if (typeof(_t.grid[r][c]) == 'undefined') {
                        _t.createCell(r,c);
                        //_t.grid[r][c] = Roo.applyIf({ r: r , c : c }, _t.defaultCell);
                    }
                    var g=_t.grid[r][c];
                    if (typeof(g.cls) =='undefined') {
                        g.cls = [];
                        g.styles = [];
                    }
                    if (g.cls.indexOf(s.name)  > -1) {
                       continue;
                    }
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
                ent['vertical-align'] = { '1' : 'top', '4': 'middle', '8' : 'bottom'}[v]  || 'top'
            },
            Fore : function(ent,v) { 
                var col=[];
                Roo.each(v.split(':'), function(c) { col.push(Math.round(parseInt(c,16)/256)); });
                ent['color'] = 'rgb(' + col.join(',') + ')';
            },
            Back : function(ent,v) { 
                var col=[];
                Roo.each(v.split(':'), function(c) { col.push(Math.round(parseInt(c,16)/256)); });
                ent['background-color'] = 'rgb(' + col.join(',') + ')';
            },
            FontUnit : function(ent,v) { 
                ent['font-size'] = v + 'px';
            },
            FontBold : function(ent,v) { 
                if (v*1 < 1) { return; }
                ent['font-weight'] = 'bold';
            },
            FontItalic : function(ent,v) { 
                if (v*0 < 1) { return; }
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
                Roo.each(vv[1].split(':'), function(c) { col.push(Math.round(parseInt(c,16)/256)); });
                ent['border-'+vv[0]+'-color'] = 'rgb(' + col.join(',') + ')';
            }
        };
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
        
        
        Roo.log( cs.r+ ',' + cs.c + ' = '+ v);
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
        // cell might not be rendered yet... so if we try and create a cell, it overrides the default formating..
        
        if (typeof(this.grid[cs.r][cs.c].dom) == 'undefined') {
            Roo.log('no default content for cell:' + cell);
            Roo.log(this.grid[cs.r][cs.c]);
            //this.createCell(cs.r,cs.c);
            //return;
        }
        // Handle date conversion for ValueType=40 or Date objects
        if ( v instanceof Date) {
            var dateValue;
            vf = vf || this.dateFormat;
            vt = 40;
            // Convert Date object to Gnumeric date serial number
            dateValue = (v.getTime() - new Date(1899, 11, 30).getTime()) / 86400000 + 1;
            // Auto-set ValueType to 40 for Date objects
            
                
            
            this.grid[cs.r][cs.c].value = dateValue;
            if (this.grid[cs.r][cs.c].dom) {
                this.grid[cs.r][cs.c].dom.textContent = dateValue;
            }
        } else {
            this.grid[cs.r][cs.c].value = v;
            if (this.grid[cs.r][cs.c].dom) {
                this.grid[cs.r][cs.c].dom.textContent = v;
            }
        }
        
        
        if (typeof(vt) != 'undefined') {
            this.grid[cs.r][cs.c].valueType = vt;
            this.grid[cs.r][cs.c].dom.setAttribute('ValueType', vt);
            if (vt === '' || vt === false) { // value type is empty for formula's
                this.grid[cs.r][cs.c].dom.removeAttribute('ValueType');
            }
        }
        if (typeof(vf) != 'undefined' && vf !== false) {
            this.grid[cs.r][cs.c].valueFormat = vf;
            this.grid[cs.r][cs.c].dom.setAttribute('ValueFormat', vf);
            if (vf === '' || vf === false) { // value type is empty for formula's
                this.grid[cs.r][cs.c].dom.removeAttribute('ValueFormat');
            }
        }
        
    },
    
    // private
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
    
    // private
    
    createCell: function(r,c)
    {
        //<gnm:Cell Row="6" Col="5" ValueType="60">Updated</gnm:Cell>    
        var nc = this.doc.createElementNS('http://www.gnumeric.org/v10.dtd', 'gnm:Cell');
        this.cellholder.appendChild(nc);
        var lb = this.doc.createTextNode("\n");// add a line break..
        this.cellholder.appendChild(lb);
        
        nc.setAttribute('Row', new String(r));
        nc.setAttribute('Col', new String(c));
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
    
    // private
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
            if (typeof(this.grid[r]) == 'undefined') {
                continue;
            }
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
    
    readTableData : function(table)
    {
        // read the first row.
        var tds = Roo.get(table).select('tr').item(0).select('td');
        var maxnc = 0;
        
        Roo.get(table).select('tr').each(function(trs) {
            var nc = 0;
           
            trs.select('td').each(function(td) {
                var cs = td.dom.getAttribute('colspan');
                cs = cs ? cs * 1 : 1;
                nc += cs;
            });
            maxnc = Math.max(nc, maxnc);
        });
        
        var tr = document.createElement('tr');
        table.appendChild(tr);
        var ar = {};
        for (i =0; i < maxnc; i++) {
            ar[i] = document.createElement('td');
            tr.appendChild(ar[i]);
        }
        // find the left.
        var ret = { cols : maxnc, pos : {} };
        for (i =0; i < maxnc; i++) {
            ret.pos[ Roo.get(ar[i]).getLeft()] =i;
        }
        ret.near = function(p) {
            // which one is nearest..
            
            if (this.pos[p]) {
                return this.pos[p];
            }
            var prox = 100000;
            var match = 0;
            for(var i in this.pos) {
                var dis = Math.abs(p-i);
                if (dis < prox) {
                    prox = dis;
                    match = this.pos[i];
                }
            }
            return match;
            
        }
        table.removeChild(tr);
        return ret;
    },
    
     
   
     
    /**
     * importTable: 
     * Import a table and put it into the spreadsheet
     * @param {HTMLTable} datagrid dom element of html table.
     * @param {Number} xoff X offset to start rendering to
     * @param {Number} yoff Y offset to start rendering to
     **/
     
 
    importTable : function (datagrid, xoff,yoff)
    {
        if (!datagrid) {
            Roo.log("Error table not found!?");
            return;
        }
        xoff = xoff || 0;
        yoff = yoff || 0;
        
        
        var table_data = this.readTableData(datagrid);
        
        // oroginally this cleaned line breaks, but we acutally need them..
        var cleanHTML = function (str) {
            
            var ret = str;
            ret = ret.replace(/&nbsp;/g,' ');
           // ret = ret.replace(/\n/g,'.');
          //  ret = ret.replace(/\r/g,'.');
            var i;
             
            return ret;
        };

        
        // <cell col="A" row="1">Test< / cell>
        // <cell col="B" row="2" type="Number" format="test1">30< / cell>
        var rowOffsets = {};
        var rows = datagrid.getElementsByTagName('tr');
        //alert(rows.length);
        
        
        for(var row=0;row<rows.length;row++) {
            
            // let's see what affect this has..
            // it might mess things up..
            
            if (rows[row].getAttribute('xls:height')) {
                this.setRowHeight(row + yoff +1, 1* rows[row].getAttribute('xls:height'));
            } else {
                this.setRowHeight( row + yoff +1, Roo.get(rows[row]).getHeight());
            }
            
            var cols = rows[row].getElementsByTagName('td');
            
            for(var col=0;col < cols.length; col++) {
                
                if (cols[col].getAttribute('xls:width')) {
                    this.setColumnWidth(col, 1 * cols[col].getAttribute('xls:width'));
                }
                
                var colspan = cols[col].getAttribute('colspan');
                colspan  = colspan ? colspan *1 : 1;
                
                var rowspan = cols[col].getAttribute('rowspan');
                rowspan = rowspan ? rowspan * 1 : 1;
                
                var realcol = table_data.near( Roo.get(cols[col]).getLeft() );
                
                
                
                if (colspan > 1 || rowspan > 1) {
                    
                    // getting thisese right is tricky..
                    this.mergeRegion(
                        realcol + xoff,
                        row + yoff +1,
                        realcol+ xoff + (colspan -1),
                        row + yoff + rowspan 
                    );
                    
                }
                
                // skip blank cells
                // set the style first..
                this.parseHtmlStyle( cols[col], row + yoff, realcol + xoff   , colspan, rowspan);
                
                if (!cols[col].childNodes.length) {
                     
                    continue;
                }
                
                
                
                
                var vt = '60';
                var vf = false;
                var xlstype = cols[col].getAttribute('xls:type');
                switch(xlstype) {
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
                        vt = 40;
                        //ValueFormat="d/m/yyyy" 38635  
                        var vf = this.dateFormat;
                        if (cols[col].getAttribute('xls:dateformat')) {
                            vf= cols[col].getAttribute('xls:dateformat');
                        }
                        
                       
                        
                        break;
                    
                    default:
                       
                        break;
                }
               
                if (!cols[col].childNodes[0].nodeValue) {
                   
                    continue;
                }
                if (!cols[col].childNodes[0].nodeValue.replace(/^\s*|\s*$/g,"").length) {
                  
                    continue;
                }
                // strip me.!
                var cell_value_text = cleanHTML(cols[col].childNodes[0].nodeValue);
       
                if (cols[col].getAttribute('xls:percent')) {
                    cell_value_text = '' + ((cell_value_text * 1) / 100);
                }

                if (cell_value_text.length && (vt == 40) && xlstype == 'date') {
                    var bits = cell_value_text.split(/-/);
                    var cur = new Date(bits[0],bits[1]-1,bits[2]);
                    cell_value_text = '' + Math.round((cur.getTime() - Date.UTC(1899,11,30)) / (24 * 60 * 60 * 1000));
                }

                
                
                if (cols[col].getAttribute('xls:formula')) {
                    var s = cols[col].getAttribute('xls:formula');
                    vt = '';
                    cell_value_text = s.replace(/#row#/g,(row + yoff + 1));
                }
                this.set({ r: row + yoff, c : realcol + xoff }, cell_value_text, vt, vf);
                 
                  
                
                
                
            }
        }
        this.rowOffset += rows.length;
        
    },
    
    
    
    parseHtmlStyle : function(dom, row, col, colspan, rowspan) {
        
        function toCol (rgb) {
            
            var ar = rgb.replace(/rgb[a]?\(/, '').replace(/\)/, '').replace(/ /, '').split(',');
            var rcs = [];
            ar = ar.slice(0,3);
            Roo.each(ar, function(c) { 
                rcs.push((c*c).toString(16)) ;   
            });
            return rcs.join(':');
            
        }
        
        var el = Roo.get(dom);
        var map =  {
            'text-align'  : function(ent,v) { 
                ent['HAlign'] = { 'left' : '1', 'center' : '8' ,  'right' : '4' }[v] || '1';
            },
            'vertical-align': function(ent,v) { 
                ent['VAlign'] = { 'top' : '1', 'middel' : '8' ,  'bottom' : '4' }[v] || '1';
            },
            
            'color': function(ent,v) { 
                ent['Fore'] = toCol(v);
                // this is a bit dumb.. we assume that if it's not black text, then it's shaded..
                if (ent['Fore'] != '0:0:0') {
                    ent['Shade'] = 1;
                }
                
            },
            'background-color' : function(ent,v) { 
                ent['Back'] = toCol(v);
                 
            }
            
        };
       
        var ent = {
                HAlign:"1",
                VAlign:"2",
                WrapText:"0",
                ShrinkToFit:"0",
                Rotation:"0",
                Shade:"0",
                Indent:"0",
                Locked:"0",
                Hidden:"0",
                Fore:"0:0:0",
                Back:"FFFF:FFFF:FFFF",
                PatternColor:"0:0:0",
                Format:"General"
        };
           
        for(var k in map) {
            var val = el.getStyle(k);
            if (!val || !val.length) {
               continue;
            }
            map[k](ent,val);
        }
        // special flags..
        if (el.dom.getAttribute('xls:wraptext')) {
            ent.WrapText = 1;
        }
        if (el.dom.getAttribute('xls:valign')) {
            ent.VAlign= 1;
        }
        if (el.dom.getAttribute('xls:halign')) {
            ent.HAlign= 1;
        }
        // fonts..
        var fmap = {
            
           
            'font-size' : function(ent,v) { 
                ent['Unit'] = v.replace(/px/, '');
            },
            'font-weight' : function(ent,v) { 
                if (v != 'bold') {
                   return;
                }
                ent['Bold'] = 1;
            },
            'font-style' : function(ent,v) { 
                if (v != 'italic') {
                    return;
                }
                ent['Italic'] = 1;
            } 
        };
       
        var fent = {
            Unit:"10",
            Bold:"0",
            Italic:"0",
            Underline:"0",
            StrikeThrough:"0"
        };
        
        for(var k in fmap) {
            var val = el.getStyle(k);
            if (!val || !val.length) {
               continue;
            }
            fmap[k](fent,val);
        }
        var font = el.getStyle('font-family') || 'Sans';
        if (font.split(',').length > 1) {
            font = font.split(',')[1].replace(/\s+/, '');
        }
        
        
        /// -- now create elements..
        
        var objs = this.sheet.getElementsByTagNameNS('*','Styles')[0];
        
        //<gnm:StyleRegion startCol="0" startRow="0" endCol="255" endRow="65535"
        var sr = this.doc.createElementNS('http://www.gnumeric.org/v10.dtd', 'gnm:StyleRegion');
        objs.appendChild(sr);
        objs.appendChild(this.doc.createTextNode("\n"));// add a line break..

        sr.setAttribute('startCol', col);
        sr.setAttribute('endCol', col+ colspan-1);
        sr.setAttribute('startRow', row);
        sr.setAttribute('endRow', row + rowspan -1);
        
        
        var st = this.doc.createElementNS('http://www.gnumeric.org/v10.dtd', 'gnm:Style');
        sr.appendChild(st);
        // do we need some defaults..
        for(var k in ent) {
            //Roo.log(k);
            st.setAttribute(k, ent[k]);
        }
        
        var fo = this.doc.createElementNS('http://www.gnumeric.org/v10.dtd', 'gnm:Font');
        st.appendChild(fo);
        // do we need some defaults..
        for(var k in fent) {
            fo.setAttribute(k, fent[k]);
        }
        fo.textContent  = font;
        
        var sb = false;
        // borders..
        Roo.each(['top','left','bottom','right'], function(p) {
            var w = el.getStyle('border-' + p + '-width').replace(/px/, '');
            if (!w || !w.length || (w*1) < 1) {
                return;
            }
            if (!sb) {
                sb= this.doc.createElementNS('http://www.gnumeric.org/v10.dtd', 'gnm:StyleBorder');
            }
            var be = this.doc.createElementNS('http://www.gnumeric.org/v10.dtd', 'gnm:' + p[0].toUpperCase() + p.substring(1));
            be.setAttribute('Style', '1');
            be.setAttribute('Color', '0:0:0'); // fixme..
            sb.appendChild(be);
            
        }, this);
        // start adding them all together..
        
        if (sb) {
            st.appendChild(sb);
        }
        
        
        
        
    },
    
    /**
     * writeImageOld:
     * write an image in old gnumberic format (needs base64 data to write it)
     * 
     * 
     * @param {Number} row  row to put it in (rows start at 0)
     * @param {Number} col  column to put it in
     * @param {Number} data  the base64 description of the images
     * @param {Number} width image width
     * @param {Number} width image height
     * 
     */
    writeImageOld : function (row, col, data, width, height, type, size) 
    {
        
        if (!data) {
            throw "write Image called with missing data";
        }
        
        row*=1;
        col*=1;
        height*=1;
        width*=1;
        var objs = this.sheet.getElementsByTagNameNS('*','Objects')[0];
        var soi = this.doc.createElementNS('http://www.gnumeric.org/v10.dtd', 'gnm:SheetObjectImage');
        
        var colwidth = 0;
        var endcol=col;
        for ( endcol=col;endcol <100; endcol++) {
            if (!this.colInfo[endcol]) {
                this.colInfo[endcol] = 100; // eak fudge
            }
            colwidth += this.colInfo[endcol];
            if (colwidth > width) {
                break;
            }
        }
        
        soi.setAttribute('ObjectBound', this.RCtoCell(row,col) + ':' + this.RCtoCell(row,endcol));
     
        var ww = 0.01; // offset a bit...
        var hh = 0.01; //
        
        var rowHeight = typeof(this.rowInfoDom[row]) == 'undefined' ? 100 : 
		this.rowInfoDom[row].getAttribute('Unit')*1;
	
	
        var ww2 = 1 - ((colwidth - width) / this.colInfo[endcol]);
        var hh2 = 1 - ((rowHeight - height) /    rowHeight);
        
        var offset_str = ww + ' '  + hh + ' ' + ww2 + ' '+hh2;
        
	// offset string 0.01 0.01 0.01 0.392 << last one needs to be calculated based on proportions.
	// so what are our dimentions..
	
	
	
	
	
        //alert(offset_str);
        soi.setAttribute('ObjectOffset', offset_str);
        soi.setAttribute('ObjectAnchorType','16 16 16 16');
        soi.setAttribute('Direction','17');
        soi.setAttribute('crop-top','0.000000');
        soi.setAttribute('crop-bottom','0.000000');
        soi.setAttribute('crop-left','0.000000');
        soi.setAttribute('crop-right','0.000000');
        
	
	
	
	
        var content = this.doc.createElement('Content');
        content.setAttribute('image-type', type ? type : 'jpeg');
        content.setAttribute('size-bytes', size);
	content.appendChild( this.doc.createTextNode(data));
        soi.appendChild(content);
        objs.appendChild(soi);
        
        if (typeof(this.grid[row]) == 'undefined') {
            this.grid[row] = [];
        }
        if (typeof(this.grid[row][col]) == 'undefined') {
            this.createCell(row,col);
        }
        
        this.grid[row][col].value=  data;
        this.grid[row][col].valueFormat = 'image';
        this.grid[row][col].imageType = type;
        this.grid[row][col].width = width;
        this.grid[row][col].height = height;
        
        var godoc = this.doc.getElementsByTagNameNS('*','GODoc')[0];
        
        if(godoc && godoc.parentNode) {
            godoc.parentNode.removeChild(godoc);
        }
        
        return true;
    },
    
    /**
     * writeImage:
     * write an image (needs base64 data to write it)
     * 
     * 
     * @param {Number} row  row to put it in (rows start at 0)
     * @param {Number} col  column to put it in
     * @param {Number} data  the base64 description of the images
     * @param {Number} width image width
     * @param {Number} width image height
     * 
     */
    
    writeImage : function (row, col, data, width, height, type) 
    {
        
        if (!data) {
            throw "write Image called with missing data";
        }
        // our default height width is 50/50 ?!
        //console.log('w='+width+',height='+height);
                //        <gmr:Objects>
        row*=1;
        col*=1;
        height*=1;
        width*=1;
        var objs = this.sheet.getElementsByTagNameNS('*','Objects')[0];
        var soi = this.doc.createElementNS('http://www.gnumeric.org/v10.dtd', 'gnm:SheetObjectImage');
        
        //<gmr:SheetObjectImage 
        //      ObjectBound="A3:J8" 
        //      ObjectOffset="0.375 0.882 0.391 0.294" 
        //      ObjectAnchorType="16 16 16 16" 
        //      Direction="17" 
        //      crop-top="0.000000" 
        //      crop-bottom="0.000000" 
        //      crop-left="0.000000" 
        //      crop-right="0.000000">
                
                
        //alert(gnumeric_colRowToName(row,col));
               
        // this is where we really have fun!!!... 
        // since our design currently assumes the height is enough to fit
        // stuff in, we only really need to work out how wide it has to be..
        
        // note we should probably use centralized calcs if it fits in the first cell!
        
        // step 1 - work out how many columns it will span..
        // lets hope the spreadsheet is big enought..
        var colwidth = 0;
        var endcol=col;
        for ( endcol=col;endcol <100; endcol++) {
            if (!this.colInfo[endcol]) {
                this.colInfo[endcol] = 100; // eak fudge
            }
            colwidth += this.colInfo[endcol];
            if (colwidth > width) {
                break;
            }
        }
        
        soi.setAttribute('ObjectBound',
            //gnumeric_colRowToName(row,col) + ':' + gnumeric_colRowToName(row+1,col+1));
            this.RCtoCell(row,col) + ':' + this.RCtoCell(row,endcol));
     
        var ww = 0.01; // offset a bit...
        var hh = 0.01; //
        
	var rowHeight = typeof(this.rowInfoDom[row]) == 'undefined' ? 100 : 
		this.rowInfoDom[row].getAttribute('Unit')*1;
	
	
        var ww2 = 1 - ((colwidth - width) / this.colInfo[endcol]);
        var hh2 = 1 - ((rowHeight - height) /    rowHeight);
        
        var offset_str = ww + ' '  + hh + ' ' + ww2 + ' '+hh2;
        
        //alert(offset_str);
        soi.setAttribute('ObjectOffset', offset_str);
        soi.setAttribute('ObjectAnchorType','16 16 16 16');
        soi.setAttribute('Direction','17');
        soi.setAttribute('crop-top','0.000000');
        soi.setAttribute('crop-bottom','0.000000');
        soi.setAttribute('crop-left','0.000000');
        soi.setAttribute('crop-right','0.000000');
                // <Content image-type="jpeg" size-bytes="3900">......  < / Content>
                
        var name = 'Image' + Math.random().toString(36).substring(2);
        var content = this.doc.createElement('Content');
        content.setAttribute('image-type', type ? type : 'jpeg');
        content.setAttribute('name', name);
        soi.appendChild(content);
        objs.appendChild(soi);
        
        var godoc = this.doc.getElementsByTagNameNS('*','GODoc')[0];
        
        var goimage = this.doc.createElement('GOImage');
        goimage.setAttribute('image-type', type ? type : 'jpeg');
        goimage.setAttribute('name', name);
        goimage.setAttribute('type', 'GOPixbuf');
        goimage.setAttribute('width', width);
        goimage.setAttribute('height', height);
        goimage.textContent = data;
        
        godoc.appendChild(goimage);
        
        if (typeof(this.grid[row]) == 'undefined') {
            this.grid[row] = [];
        }
        if (typeof(this.grid[row][col]) == 'undefined') {
            this.createCell(row,col);
        }
        
        this.grid[row][col].value=  data;
        this.grid[row][col].valueFormat = 'image';
        this.grid[row][col].imageType = type;
        this.grid[row][col].width = width;
        this.grid[row][col].height = height;
        
        return true;
                //< /gnm:SheetObjectImage>
                // < /gnm:Objects>

    },
    
    /**
     * writeFixedImageOld:
     * write an image in old gnumberic format (needs base64 data to write it)
     */
    writeFixedImageOld : function (startCol, startRow, endCol, endRow, type, data, width, height, size) 
    {
        if (!data) {
            throw "write Image called with missing data";
        }
        
        startCol = startCol * 1;
        startRow = startRow * 1;
        endCol = endCol * 1;
        endRow = endRow * 1;
        width = width * 1;
        height = height * 1;
        
        var objs = this.sheet.getElementsByTagNameNS('*','Objects')[0];
        var soi = this.doc.createElementNS('http://www.gnumeric.org/v10.dtd', 'gnm:SheetObjectImage');
        
        soi.setAttribute('ObjectBound',this.RCtoCell(startRow, startCol) + ':' + this.RCtoCell(endRow, endCol));
        
        soi.setAttribute('ObjectOffset', '0 0 0 0');
        soi.setAttribute('ObjectAnchorType','16 16 16 16');
        soi.setAttribute('Direction','17');
        soi.setAttribute('crop-top','0.000000');
        soi.setAttribute('crop-bottom','0.000000');
        soi.setAttribute('crop-left','0.000000');
        soi.setAttribute('crop-right','0.000000');
        
        var content = this.doc.createElement('Content');
        content.setAttribute('image-type', type ? type : 'jpeg');
        content.setAttribute('size-bytes', size);
        content.appendChild( this.doc.createTextNode(data));
        soi.appendChild(content);
        objs.appendChild(soi);
        
        if (typeof(this.grid[startRow]) == 'undefined') {
            this.grid[startRow] = [];
        }
        if (typeof(this.grid[startRow][startCol]) == 'undefined') {
            this.createCell(startRow,startCol);
        }
        
        this.grid[startRow][startCol].value=  data;
        this.grid[startRow][startCol].valueFormat = 'image';
        this.grid[startRow][startCol].imageType = type;
        this.grid[startRow][startCol].width = width;
        this.grid[startRow][startCol].height = height;
        
        var godoc = this.doc.getElementsByTagNameNS('*','GODoc')[0];
        
        if(godoc && godoc.parentNode) {
            godoc.parentNode.removeChild(godoc);
        }
        
        return true;
    },
    
    writeFixedImage : function (startCol, startRow, endCol, endRow, type, data, width, height) 
    {
        if (!data) {
            throw "write Image called with missing data";
        }
        
        startCol = startCol * 1;
        startRow = startRow * 1;
        endCol = endCol * 1;
        endRow = endRow * 1;
        width = width * 1;
        height = height * 1;
        
        var objs = this.sheet.getElementsByTagNameNS('*','Objects')[0];
        var soi = this.doc.createElementNS('http://www.gnumeric.org/v10.dtd', 'gnm:SheetObjectImage');
        
        soi.setAttribute('ObjectBound',this.RCtoCell(startRow, startCol) + ':' + this.RCtoCell(endRow, endCol));
        
        soi.setAttribute('ObjectOffset', '0 0 0 0');
        soi.setAttribute('ObjectAnchorType','16 16 16 16');
        soi.setAttribute('Direction','17');
        soi.setAttribute('crop-top','0.000000');
        soi.setAttribute('crop-bottom','0.000000');
        soi.setAttribute('crop-left','0.000000');
        soi.setAttribute('crop-right','0.000000');
        
        var name = 'Image' + Math.random().toString(36).substring(2);
        var content = this.doc.createElement('Content');
        content.setAttribute('image-type', type ? type : 'jpeg');
        content.setAttribute('name', name);
	
        soi.appendChild(content);
        objs.appendChild(soi);
        
        var godoc = this.doc.getElementsByTagNameNS('*','GODoc')[0];
        
        var goimage = this.doc.createElement('GOImage');
        goimage.setAttribute('image-type', type ? type : 'jpeg');
        goimage.setAttribute('name', name);
        goimage.setAttribute('type', 'GOPixbuf');
        goimage.setAttribute('width', width);
        goimage.setAttribute('height', height);
        goimage.textContent = data;
        
        godoc.appendChild(goimage);
        
        if (typeof(this.grid[startRow]) == 'undefined') {
            this.grid[startRow] = [];
        }
        if (typeof(this.grid[startRow][startCol]) == 'undefined') {
            this.createCell(startRow,startCol);
        }
        
        this.grid[startRow][startCol].value=  data;
        this.grid[startRow][startCol].valueFormat = 'image';
        this.grid[startRow][startCol].imageType = type;
        this.grid[startRow][startCol].width = width;
        this.grid[startRow][startCol].height = height;
        
        return true;
    },
 
    /**
     * mergeRegion:
     * Merge cells in the spreadsheet. (does not check if existing merges exist..)
     * 
     * @param {Number} col1  first column 
     * @param {Number} row1  first row
     * @param {Number} col2  to column 
     * @param {Number} row2  to row
     * 
     */
    mergeRegion : function (col1,row1,col2,row2)
    {
        var cell = this.doc.createElementNS('http://www.gnumeric.org/v10.dtd', 'gnm:Merge');
        //if (col1 > 50|| col2 > 50) { // do not merge cols off to right?
       //     return;
        //}
        
        cell.textContent = this.RCtoCell(row1,col1) + ':' + this.RCtoCell(row2,col2);
        
        //var merges = this.gnumeric.getElementsByTagNameNS('*','MergedRegions');
        var merges = this.sheet.getElementsByTagNameNS('*','MergedRegions');
        if (!merges || !merges.length) {
            merges = this.doc.createElementNS('http://www.gnumeric.org/v10.dtd','gnm:MergedRegions');
            var sl = this.sheet.getElementsByTagNameNS('*','SheetLayout')[0];
            this.sheet.insertBefore(merges,sl);
        } else {
            merges = merges[0];
        }
        merges.appendChild(cell);
    
    },
    /**
     * setRowHeight:
     * Sets the height of a row.
     * 
     * @param {Number} r  the row to set the height of. (rows start at 0)
     * @param {Number} height (in pixels)
     */
    setRowHeight : function (r,height)
    {
        
        //<gmr:Rows DefaultSizePts="12.75">
        //   <gmr:RowInfo No="2" Unit="38.25" MarginA="0" MarginB="0" HardSize="1"/>
    //  < /gmr:Rows>
        
        // this doesnt handle row ranges very well.. - with 'count in them..'
        
        if (this.rowInfoDom[r]) {
            this.rowInfoDom[r].setAttribute('Unit', height);
            return;
        }
    
        var rows = this.sheet.getElementsByTagNameNS('*','Rows')[0]; // assume this exists..
        var ri = this.doc.createElementNS('http://www.gnumeric.org/v10.dtd','gnm:RowInfo');
        // assume we have no rows..
        ri.setAttribute('No', r-1);
        ri.setAttribute('Unit', height);
        ri.setAttribute('MarginA', 0);
        ri.setAttribute('MarginB', 0);
        ri.setAttribute('HardSize', 1);
        rows.appendChild(ri);
        this.rowInfoDom[r] = ri;
    },
     
    /**
     * setSheetName: 
     * Set the sheet name.
     * @param {String} title for sheet
     **/
    setSheetName : function(name,sheet)
    {
        sheet = sheet || 0;
        /*
        <gnm:SheetNameIndex>
        <gnm:SheetName>Sheet1</gnm:SheetName>
        <gnm:SheetName>Sheet2</gnm:SheetName>
        <gnm:SheetName>Sheet3</gnm:SheetName>
        </gnm:SheetNameIndex>
        */
        // has to set sheet name on index and body..
        Roo.log(sheet);
        Roo.log(name);
        var sheetnames = this.doc.getElementsByTagNameNS('*','SheetName');
        if (sheet >=  sheetnames.length) {
            
            sheetnames[0].parentNode.appendChild(sheetnames[sheetnames.length-1].cloneNode(true));
            // copy body.
            sheetnames = this.doc.getElementsByTagNameNS('*','Sheet');
            sheetnames[0].parentNode.appendChild(sheetnames[sheetnames.length-1].cloneNode(true));
            var sn = this.doc.getElementsByTagNameNS('*','Sheet')[sheet];
            var cls = sn.getElementsByTagNameNS('*','Cells')[0];
            while (cls.childNodes.length) {
                cls.removeChild(cls.firstChild);
            }
            
        }
        
        var sheetn = this.doc.getElementsByTagNameNS('*','SheetName')[sheet];
        sheetn.textContent = name;
        var sheetb = this.doc.getElementsByTagNameNS('*','Sheet')[sheet].getElementsByTagNameNS('*','Name')[0];
        sheetb.textContent = name;
        this.parseDoc(sheet);
        
        
        
        
    },
     /**
     * setColumnWidth: 
     * Set the column width
     * @param {Number} column number (starts at '0')
     * @param {Number} width size of column
     **/
    setColumnWidth : function(column, width)
    {
        column = column *1; 
        width= width*1;
        if (typeof(this.colInfoDom[column]) == 'undefined') {
            var cols = this.sheet.getElementsByTagNameNS('*','Cols')[0];
            var ri = this.doc.createElementNS('http://www.gnumeric.org/v10.dtd', 'gnm:ColInfo');
            ri.setAttribute('No', column);
            ri.setAttribute('Unit', width);
            ri.setAttribute('MarginA', 2);
            ri.setAttribute('MarginB', 2);
            ri.setAttribute('HardSize', 1);
            cols.appendChild(ri);
            this.colInfo[column] = width;
            this.colInfoDom[column]  = ri;
            return;
        }
        this.colInfoDom[column].setAttribute('Unit', width);
        
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
                if (typeof(grid[r][c]) == 'undefined')  {
                    this.createCell(r,c);
                    
                }
                var g = grid[r][c];
                
                if (typeof(g.cls) =='undefined') {
                    g.cls = [];
                }
                var w= calcWidth(c,g.colspan);
                
                var value = g.value[0] == '=' ? 'CALCULATED' : g.value;
                
                // Handle date formatting for ValueType=40 or specific date formats
                try {
                    if (g.valueType == 40 && g.value[0] != '=' && !isNaN(value * 1) && value != 0 && g.valueFormat == this.dateFormat) {
                        // Convert Gnumeric date serial number to Date object
                        var dateObj = new Date(value * 24 * 60 * 60 * 1000 + new Date(1899, 11, 30).getTime());
                        value = dateObj.format(this.dateFormat);
                    } else if (
                        g.styles && g.styles[0] && g.styles[0].firstElementChild && 
                        g.styles[0].firstElementChild.getAttribute('Format') == "D\\-MMM\\-YYYY;@" &&
                        g.value[0] != '=' &&
                        !isNaN(value * 1) && 
                        value != 0
                    ){
                        // Legacy date format handling
                        var dateObj = new Date(value * 24 * 60 * 60 * 1000 + new Date('1899-12-30').getTime());
                        value = dateObj.format(this.dateFormat);
                    }
                    
                } catch(e) {
                    // Keep original value if date conversion fails
                }
                
                if(g.valueFormat == 'image') {
                
                    out+=String.format('<td colspan="{0}" rowspan="{1}"  class="{2}"><div style="{3}"><img src="data:image/{4};base64, {5}" width="{6}" height="{7}"></div></td>', 
                        g.colspan, g.rowspan, g.cls.join(' '),
                        'overflow:hidden;' + 
                        'width:'+g.width+'px;' +

                        'text-overflow:ellipsis;' +
                        'white-space:nowrap;',
                         g.imageType,
                         value, g.width, g.height

                    );
                    c+=(g.colspan-1);
                    continue;
                }
                
                out+=String.format('<td colspan="{0}" rowspan="{1}"  class="{4}"><div style="{3}">{2}</div></td>', 
                    g.colspan, g.rowspan, value,
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
        
        if (this.downloadURL && this.downloadURL.charAt(this.downloadURL.length-1) != '/') {
            this.downloadURL += '/';
        }
        
        var ser = new XMLSerializer();
		Roo.get(document.body).mask("Downloading");
        var x = new Pman.Download({
            method: 'POST',
            timeout :240000, // quite a long wait.. 2 minutes.
            params : {
               xml : ser.serializeToString(this.doc),
               format : this.format,
               debug : 0
               
            },
            url : (this.downloadURL || (baseURL + '/GnumericToExcel/')) + name + '.xls',
            success : function() {
				Roo.get(document.body).unmask();
                Roo.MessageBox.alert("Alert", "File should have downloaded now");
                if (callback) {
                    callback();
                }
            },
			failure : function() {
				Roo.get(document.body).unmask();
				Roo.MessageBox.alert("Alert", "Download failed");
			}
        });
         
    }

});