//<script type="text/javascript">

/*
usage:

writer = new GnumericWriter(rooturl + '/FlexyShipping/templates/spreadsheets/base.gnumeric.xml', function(w) {
    w.setSheetName(document.getElementById('title').childNodes[0].nodeValue);

    w.writeString(0,0, 'Summary for ' + document.getElementById('title').childNodes[0].nodeValue + 
                ' as of ' + document.getElementById('date').childNodes[0].nodeValue);

     
    
    w.importGrid(grid, rows)
    
    // post the form...
    return true; // will post the form.. false will stop it..

});


cell information for auto tables:

<td xls:type="int|float|string">  (string default)

<td xls:Format="#,##0.00"  (2 decimal places) on a float
<td xls:Format="0.00%">   percentage with float
<td xls:Format="0%">   percentage (with no float)



-  added so the editor works..

*/
function GnumericWriter(cfg) { }
   
/*
function GnumericWriter(cfg) 
{
        //alert(template);
    Roo.apply(this,cfg);
    var _this = this;
    if (!this.targetURL.length) {
        this.targetURL = baseURL + '/GnumericToExcel';
   }
    
    if (!GnumericWriter.postform) {
        GnumericWriter.postform = Roo.get(document.body).createChild({
            tag: 'form', 
            method: 'POST', 
            //defaultaction : baseURL + '/GnumericToExcel',
            target : '_new'
        });
        
        GnumericWriter.postxml = GnumericWriter.postform.createChild({
            tag: 'input', type: 'hidden', name: 'xml'
        });
        GnumericWriter.postformat = GnumericWriter.postform.createChild({
            tag: 'input', type: 'hidden', name: 'format', value: 'xls'
        });
        GnumericWriter.postdebug = GnumericWriter.postform.createChild({
            tag: 'input', type: 'hidden', name: 'debug', value: ''
        });
    }
    
    
    
    Roo.Ajax.request({
        url : this.template,
        method : 'GET',
        success : function(resp, opts) {
            _this.init(resp.responseXML);
        }
    });
}
GnumericWriter.postform = false;
GnumericWriter.postxml = false;
GnumericWriter.postformat = false;
GnumericWriter.postdebug = false;

Roo.apply(GnumericWriter.prototype, {
    format : 'xls',
    debug: 0,
    targetURL : '',
    afterInit: false,
    sheetName : 'unnamed',
    filename  : 'test.gnumeric',
    init : function(resp) {
        if (!resp) {
            alert('Failed to load ' + this.template);
        }
        
        this.gnumeric        = resp;
        
        //alert(this.gnumeric);
        //gmr:Sheet
        var g = this.gnumeric;
        this.worksheet       = Roo.isIE ? g.getElementsByTagName('gmr:Sheet')[0] :  g.getElementsByTagNameNS('*','Sheet')[0];
        this.workbook        = Roo.isIE ? g.getElementsByTagName('gmr:Workbook')[0] :  this.gnumeric.getElementsByTagNameNS('*','Workbook')[0];
        this.worksheet_cells = Roo.isIE ? g.getElementsByTagName('gmr:Cells')[0] :  this.worksheet.getElementsByTagNameNS('*','Cells')[0];
        this.merges          = false;
        this.widths          = new Array();
         
        
        // set some defaults....
        this.setSheetName(this.sheetName);
        if (!this.afterInit) {
            alert('GnumericWriter: no afterInit defined');
            return;
        }
        if (false === this.afterInit(this)) {
            return;
        };
        this.writeImages(); // which in turn posts the form..
    },
    
    
    
    
    toXML : function () 
    {
        if (Roo.isIE) {
            return this.gnumeric.xml;
        }
        var ser = new XMLSerializer();
        return ser.serializeToString(this.gnumeric);
    },
 
    postForm : function ()  // post a form wit the contents..?
    {
        var form = GnumericWriter.postform.dom;
        //alert(form.action); 
        if (!form) {
            alert('GnumericWriter: form not loaded yet?');
            return;
        }
        GnumericWriter.postxml.dom.setAttribute('value', this.toXML());
        GnumericWriter.postdebug.dom.setAttribute('value', this.debug);
        GnumericWriter.postformat.dom.setAttribute('value', this.format);
        
        
        
        form.setAttribute('action', this.targetURL + '/' + this.filename);
        //alert( form.getAttribute('action'));
        form.submit();
         
        
    },


    setFileName : function(name) 
    {
        this.filename = name;
    },
    
    setSheetName: function(name) 
    {
        // this doesnt work yet!!!
        //this.worksheet.setAttribute('name',name);
    },
    
    

    setColumnWidth: function(col, width)
    {
        // <gmr:Cols DefaultSizePts="48">
        //   <gmr:ColInfo No="1" Unit="67.5" MarginA="2" MarginB="2" HardSize="1"/>
        // < /gmr:Cols>
       var g = this.gnumeric;
        var cols = Roo.isIE ? g.getElementsByTagName('gmr:Cols')[0] :  this.gnumeric.getElementsByTagNameNS('*','Cols')[0];
        var ri = this.gnumeric.createElement('gmr:ColInfo');
        ri.setAttribute('No', col);
        ri.setAttribute('Unit', width);
        ri.setAttribute('MarginA', 2);
        ri.setAttribute('MarginB', 2);
        ri.setAttribute('HardSize', 1);
        cols.appendChild(ri);
        this.widths[col+0] = width+0;
    },
     
    setRowHeight: function(row,height)
    {
    //<gmr:Rows DefaultSizePts="12.75">
    //   <gmr:RowInfo No="2" Unit="38.25" MarginA="0" MarginB="0" HardSize="1"/>
    //  < /gmr:Rows>
        var g = this.gnumeric;
        var rows = Roo.isIE ? g.getElementsByTagName('gmr:Rows')[0] :  this.gnumeric.getElementsByTagNameNS('*','Rows')[0];
        var ri = this.gnumeric.createElement('gmr:RowInfo');
        ri.setAttribute('No', row);
        ri.setAttribute('Unit', height);
        ri.setAttribute('MarginA', 0);
        ri.setAttribute('MarginB', 0);
        ri.setAttribute('HardSize', 1);
        rows.appendChild(ri);
    },
     
    
    writeString: function (x,y,string) 
    {
    
        var cell = this.gnumeric.createElement('gmr:Cell');
        cell.setAttribute('Row',y);
        cell.setAttribute('Col',x);
        cell.setAttribute('ValueType',60); // a string.

        var cell_value = this.gnumeric.createTextNode(string);
        cell.appendChild(cell_value);
        this.worksheet_cells.appendChild(cell);
    },
   // not sure what this is for..  
    mergeRegion: function(col1,row1,col2,row2)
    {
        var cell = this.gnumeric.createElement('gmr:Merge');
        var cell_value = this.gnumeric.createTextNode( 
            this.colRowToName(row1,col1) + ':' + this.colRowToName(row2,col2)
        );
        cell.appendChild(cell_value);
        
        //var merges = this.gnumeric.getElementsByTagNameNS('*','MergedRegions');
        var g = this.gnumeric;
        if (!this.merges) {
            this.merges = this.gnumeric.createElement('gmr:MergedRegions');
            var sl = Roo.isIE ? g.getElementsByTagName('gmr:SheetLayout')[0] : this.gnumeric.getElementsByTagNameNS('*','SheetLayout')[0];

            this.worksheet.insertBefore(this.merges,sl);
        }
        this.merges.appendChild(cell);
    
    },
    
    //
     // -- you may want to load with all the data.. if paged...
     // grid = grid object
     // row = grid.getSelectionModel.getSelected()
    // or
     // row = grid.getDataSource().getRange(0, grid.getDataSource().getCount());
     // 
     //
    
    
    importGrid: function(cfgIn) 
    {
        var _this = this;
        var cfg = {
            grid: false,
            rows : [],
            rowOffset : 0,
            colOffset : 0,
            colModel : cfgIn.grid.getColumnModel().config
        };
        Roo.apply(cfg, cfgIn);
        
        
        if (!cfg.grid) {
            alert('no grid selected.');
            return;
        }
         
        var _ds = cfg.grid.getDataSource();
         
        // get the grid column model.. - and use for headings..
        
        Roo.each(cfg.colModel, function(c,col) {
            _this.setColumnWidth(col,c.width);
            //this.setRowHeight(0, 0 + col.getAttribute('xls:height'));
            var cell = _this.gnumeric.createElement('gmr:Cell');
            cell.setAttribute('Row',0);
            cell.setAttribute('Col',col);
            cell.setAttribute('ValueType',60); // string!!!!
            var cell_value = _this.gnumeric.createTextNode(c.header);
            cell.appendChild(cell_value);
            _this.worksheet_cells.appendChild(cell);
        });
        
        
        Roo.each(cfg.rows, function(r,rownum) {
           
            Roo.each(cfg.colModel, function(c,col) {
                    var val = r.get(c.dataIndex);
                    if (typeof(val) == 'undefined') {
                        return;
                    }
                    
                    var cell = _this.gnumeric.createElement('gmr:Cell');
                    cell.setAttribute('Row',rownum+1);
                    cell.setAttribute('Col',col);
                    
                    if (c.gRenderer) {
                        // then - just use the datasource
                        //cell.setAttribute('ValueType',60); // string!!!!
                        if (false === c.gRenderer(_this, val, r, cell, rownum+1, col)) {
                            return;
                        }
                        //cell.appendChild(cell_value);
                        _this.worksheet_cells.appendChild(cell);
                        var lb = _this.gnumeric.createTextNode('\n');
                        _this.worksheet_cells.appendChild(lb);
                        return;
                    }
                    // otherwise use the details..
                    
                    
                    
                    var cell_cfg = _ds.getAt(0).fields.get(c.dataIndex);
                    switch(cell_cfg.type) {
                        case 'int': 
                            cell.setAttribute('ValueType',30); 
                            break;
                        case 'float': 
                            cell.setAttribute('ValueType',40); 
                            break;
                        case 'date':  
                            cell.setAttribute('ValueType',30); 
                            cell.setAttribute('ValueFormat','d/mmm/yyyy');
                            val = '' + Math.round((val.getTime() - 
                                Date.UTC(1899,11,30)) / (24 * 60 * 60 * 1000));
                            break;
                        default: // string
                            cell.setAttribute('ValueType',60);
                            break;
                    }
                    
                    var cell_value = _this.gnumeric.createTextNode(val);
                    cell.appendChild(cell_value);
                    _this.worksheet_cells.appendChild(cell);
                    var lb = _this.gnumeric.createTextNode('\n');
                    _this.worksheet_cells.appendChild(lb);
            });
           
        });
        
         
        
          
    },
    
    
    newStyle: function(cfg) 
    {
        return new GnumericWriter.Style(this, cfg);
    },
    
    
    
    colRowToName: function(row,col)
    {
        // we dont support > 26 cols yet!
        
        return String.fromCharCode(65+col) + (row + 1);
    },
    //
     // writeImages([
     //   { row: 0, col: 0, url: xxx, data: xxx, width: xxx, height: yyy }
     // ]);
     // 
     ///
    writeImages: function() 
    {
        this.images = this.images ||  [];
        var ar = this.images;
        if (!ar.length) {
            this.postForm();
            return;
        }
        Roo.MessageBox.show({
           title: 'Please wait...",
           msg: "Adding Images...",
           width:350,
           progress:true,
           closable:false
          
        });
        var i =0;
        var _this = this;
        var wis = function () {
            if (i == ar.length) {
                Roo.MessageBox.hide();
                _this.postForm();
                return;
            }
            Roo.MessageBox.updateProgress( 
                (i+1)/ar.length,  'Adding Image ' + (i+1) +  ' of ' + ar.length 
            );
            
             
            var c = ar[i];
            i++;
            Roo.Ajax.request({
                url : c.url,
                method : 'GET',
                success : function(resp, opts) {
                    c.data = resp.responseText;
                    _this.writeImage(c);
                    wis();
                },
                failure: function()
                {
                    // error condition!?!?
                    wis();
                }
                
            });
            
            
            
        };
        wis();
        
      
      
    },
    images : false,
    addImage: function(cfg) {
        this.images = this.images ||  [];
        this.images.push(cfg);
    },
    
    writeImage: function(cfg) 
    {
        
        // our default height width is 50/50 ?!
        //console.log('w='+width+',height='+height);
                //        <gmr:Objects>
        this.setRowHeight(cfg.row,cfg.height);
        var g =  this.gnumeric;
        var objs = Roo.isIE ? g.getElementsByTagName('gmr:Objects')[0] : this.gnumeric.getElementsByTagNameNS('*','Objects')[0];
        var soi = this.gnumeric.createElement('gmr:SheetObjectImage');
        
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
        var colwidth = this.widths[cfg.col];
        //
        //for (var endcoll=cfg.col;endcol <100; endcol++) {
         //   if (!this.widths[endcol]) {
          //      this.widths[endcol] = 100; // eak fudge
         //   }
         //   colwidth += this.widths[endcol];
         //   if (colwidth > cfg.width) {
         //       break;
         //   }
       // }
        //
        soi.setAttribute('ObjectBound',
            this.colRowToName(cfg.row,cfg.col)
        );
            //gnumeric_colRowToName(row,col) + ':' + gnumeric_colRowToName(row+1,col+1));
            //this.colRowToName(cfg.row,cfg.col) + ':' + this.colRowToName(cfg.row,endcol));
     
        var ww = 0.01; // offset a bit...
        var hh = 0.01; //
        
        // cfg/widths == % of cell taken up..
        // 1/1 = all of image.. so we should say '1'
        // 50/100 = 50%  -> say '0.5'
        //  1/100 => 1% say 0.01 ?
        
        var ww2 = (cfg.width *0.1/ this.widths[cfg.col]*0.1);
        var hh2 = 0.99;
        
        var offset_str = ww + ' '  + hh + ' ' + ww2 + ' '+hh2;
        //console.log(offset_str );
        //alert(offset_str);
        soi.setAttribute('ObjectOffset', offset_str);
        soi.setAttribute('ObjectAnchorType','16 16 16 16');
        soi.setAttribute('Direction','17');
        soi.setAttribute('crop-top','0.000000');
        soi.setAttribute('crop-bottom','0.000000');
        soi.setAttribute('crop-left','0.000000');
        soi.setAttribute('crop-right','0.000000');
                // <Content image-type="jpeg" size-bytes="3900">......  < / Content>
        var content = this.gnumeric.createElement('Content');
        content.setAttribute('image-type','jpeg');
         
        content.setAttribute('size-bytes', cfg.data.length);
      
        var body = this.gnumeric.createTextNode(cfg.data) ;
        content.appendChild(body);
        soi.appendChild(content);
        objs.appendChild(soi);
        return true;
                //< /gmr:SheetObjectImage>
                // < /gmr:Objects>

    },
 
  
    cleanHtml: function(str) 
    {
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
 
});


GnumericWriter.Style = function (gnumeric,cfg) 
    {
        cfg = cfg || {}; 
        
        this.gnumeric = gnumeric.gnumeric;
        
        this.HAlign             = 1;
        this.VAlign             = 1;
        this.WrapText           = 0;  // set to 1 for wrapping...
        this.ShrinkToFit        = 0;
        this.Rotation           = 0;
        this.Shade              = 0;  // set to 1 for fill?
        this.Indent             = 0;
        this.Locked             = 0;
        this.Hidden             = 0;
        this.Fore               = '0:0:0';
        this.Back               = 'FFFF:FFFF:FFFF';
        this.PatternColor       = '0:0:0';
        this.Format             = 'General';
        // dubious - font as it's own objecT???
        this.FontUnit           = 10;
        this.FontBold           = 0;
        this.FontItalic         = 0;
        this.FontUnderline      = 0;
        this.FontStrikeThrough  = 0;
        this.FontName           = 'Sans'; // or .. ."Courier New"
        // borders
        this.Style              = 0; // set to 1 to set all styles...
        this.StyleColor         = '';
        
        this.TopStyle           = 0;
        this.TopStyleColor      = '';
        this.BottomStyle        = 0;
        this.BottomStyleColor   = '';
        this.LeftStyle          = 0;
        this.LeftStyleColor     = '';
        this.RightStyle         = 0;
        this.RightStyleColor    = '';
        Roo.apply(this, cfg);
        
        
        
        // methods...
         
}
Roo.apply(   GnumericWriter.Style.prototype, {
    
    
    toString : function() 
    {
        var ret = '';
        t = this;
        for (var i in t) {
            if ((t[i]+'').match(/function/)) { 
                continue; 
            }
            ret += i + '=' +  t[i] + '\n';
        }
        return ret;
        
    },
    
    rgbToHex : function(str) 
    {
        str = str.replace(/[^0-9,]+/g,'');
        
        var bits = str.split(',');
        return '' + (bits[0]*256).toString(16) +
            ':'  +
            ((bits.length > 2) ? (bits[1]*256).toString(16) : '00') +
            ':'  +
            ((bits.length > 2) ? (bits[2]*256).toString(16) : '00');
    
    },
    
    debugNode : function(node)
    {   
        var s = document.defaultView.getComputedStyle(node, '');
        
        
        var str = '';
        for (var i in s) {
            if ((s[i] + '').match(/function/)) { continue; }
            str += i + '=' + s[i] + '<br>';
        }
        document.write('xls:debug = Note, outputing this, will skew the results \n' +  str);
        return;
    },
       
    setFrom : function(node) 
    {
        var ret = false;
        var style = document.defaultView.getComputedStyle(node, '');
        
       
        
        var sides = new Array( 'Left' , 'Right', 'Top' , 'Bottom' );
        for (var i in sides) {
            var side = sides[i];
            
            if (style['border'+side+'Style'] == 'solid') {
                this[side+'Style']           = 1;
                this[side+'StyleColor']      = this.rgbToHex(style['border'+side+'Color']);
                ret = true;
            }   
        }
        
        // alignment:
        switch(style.textAlign) {
            case 'center':
                this.HAlign             = 8;
                ret = true;
                break;
            case 'start':
                this.HAlign             = 0;
                ret = true;
                break;
            default:
                this.HAlign             = 1;
                ret = true;
                break;
        }
         
        if (style.verticalAlign  != 'middle') {
            // eak what?
            this.VAlign             = 1;
            ret = true;
        }
        if ((style.backgroundColor != 'rgb(255, 255, 255)') && 
            style.backgroundColor.match(/rgb/))
        {
            this.Back               = this.rgbToHex(style.backgroundColor);    
            this.Shade              = 1;  // set to 1 for fill?
            ret = true;
        }
        if ((style.color != 'rgb(0, 0, 0)') && 
            style.color.match(/rgb/))
        {
            this.Fore               = this.rgbToHex(style.color);    
            ret = true;
        }
        
        // font stuff.. - only valid if you specify a specific font!
        if (node.getAttribute('xls:debug')) {
            alert( style['fontFamily'] + ' : ' + (  (style.fontFamily + '').match(/xls/) ? 'isxls' : 'not xls'));
        }
        if (style.fontFamily.match(/xls/)) {
            
            this.FontUnit           = style.fontSize.replace(/[^0-9.]+/g,'');
            this.FontBold           = (style.fontWeight == 'bold') ? 1 : 0;
            this.FontItalic         = (style.fontStyle == 'italic') ? 1 : 0;
            //this.FontUnderline      = 0;
            //this.FontStrikeThrough  = 0;
            this.FontName           = style.fontFamily.split(',')[0];
            ret = true;
        }
        
        
        
        var t = this;
        for (var i in t) {
            if (node.getAttribute('xls:'+i)) {
                this[i] = node.getAttribute('xls:'+i);
                ret = true;
            }
        }
        if (ret && this.Style > 0) {
            this.TopStyle           = this.Style;
            this.TopStyleColor      = this.StyleColor;
            this.BottomStyle        = this.Style;
            this.BottomStyleColor   = this.StyleColor;
            this.LeftStyle          = this.Style;
            this.LeftStyleColor     = this.StyleColor;
            this.RightStyle         = this.Style;
            this.RightStyleColor    = this.StyleColor;
        }
        if (node.getAttribute('xls:debug')) {
            alert(node.innerHTML + ':' + ret + '\n' + this.toString());
        }
        if (node.getAttribute('xls:debug')) {
            //alert(style.fontFamily);
             GnumericWriter_Style_debugNode(node);
        }
        return ret;
    },
    
    
    add : function(startCol, startRow, endCol, endRow) 
    {
        
        
        //  <gmr:StyleRegion startCol="4" startRow="13" endCol="4" endRow="13">
        //       <gmr:Style HAlign="1" VAlign="1" WrapText="1" ShrinkToFit="0" Rotation="0" Shade="1" Indent="0" Locked="0" Hidden="0" Fore="FFFF:0:0" Back="CCCC:FFFF:FFFF" PatternColor="0:0:0" Format="General">
        //         <gmr:Font Unit="8" Bold="0" Italic="1" Underline="0" StrikeThrough="0">Courier New</gmr:Font>
        //         <gmr:StyleBorder>
        //           <gmr:Top Style="0"/>
        //           <gmr:Bottom Style="1" Color="0:0:0"/>
        //           <gmr:Left Style="0"/>
        //           <gmr:Right Style="1" Color="0:0:0"/>
        //           <gmr:Diagonal Style="0"/>
        //           <gmr:Rev-Diagonal Style="0"/>
        //         </gmr:StyleBorder>
        //       </gmr:Style>
        //</gmr:StyleRegion>
        
        sr   = this.gnumeric.createElement('gmr:StyleRegion');
        s    = this.gnumeric.createElement('gmr:Style');
        f    = this.gnumeric.createElement('gmr:Font');
        sb   = this.gnumeric.createElement('gmr:StyleBorder');
        sbt  = this.gnumeric.createElement('gmr:Top');
        sbb  = this.gnumeric.createElement('gmr:Bottom');
        sbl  = this.gnumeric.createElement('gmr:Left');
        sbr  = this.gnumeric.createElement('gmr:Right');
        sbd  = this.gnumeric.createElement('gmr:Diagonal');
        sbrd = this.gnumeric.createElement('gmr:Rev-Diagonal');
        fn   = this.gnumeric.createTextNode(this.FontName);
        
        
        sr.setAttribute('startCol',startCol);
        sr.setAttribute('startRow',startRow);
        sr.setAttribute('endCol',endCol);
        sr.setAttribute('endRow',endRow);
    
         
        s.setAttribute('HAlign',this.HAlign);
        s.setAttribute('VAlign',this.VAlign);
        s.setAttribute('WrapText',this.WrapText);
        s.setAttribute('ShrinkToFit',this.ShrinkToFit);
        s.setAttribute('Rotation',this.Rotation);
        s.setAttribute('Shade',this.Shade);
        s.setAttribute('Indent',this.Indent); 
        s.setAttribute('Locked',this.Locked);
        s.setAttribute('Hidden',this.Hidden);
        s.setAttribute('Fore',this.Fore);
        s.setAttribute('Back',this.Back);
        s.setAttribute('PatternColor',this.PatternColor);
        s.setAttribute('Format',this.Format);
        
        
        f.setAttribute('Unit',    this.FontUnit );
        f.setAttribute('Bold',   this.FontBold);
        f.setAttribute('Italic',   this.FontItalic);
        f.setAttribute('Underline',   this.FontUnderline);
        f.setAttribute('StrikeThrough',   this.FontStrikeThrough);
            
        
        
        
         
        sbt.setAttribute('Style',  this.TopStyle);
        if (this.TopStyle > 0) sbt.setAttribute('Color',  this.TopStyleColor);
        
        sbb.setAttribute('Style',  this.BottomStyle);
        if (this.BottomStyle > 0) sbb.setAttribute('Color',  this.BottomStyleColor);
        
        sbl.setAttribute('Style',  this.LeftStyle);
        if (this.LeftStyle > 0) sbl.setAttribute('Color',  this.LeftStyleColor);
        
        sbr.setAttribute('Style',  this.RightStyle);
        if (this.RightStyle > 0) sbr.setAttribute('Color',  this.RightStyleColor);
        
        sbd.setAttribute('Style',  0);
        sbrd.setAttribute('Style',  0);
        
        // now add them all together!!!
        sb.appendChild(sbt);
        sb.appendChild(sbb);
        sb.appendChild(sbl);
        sb.appendChild(sbr);
        sb.appendChild(sbd);
        sb.appendChild(sbrd);
        s.appendChild(sb);
        f.appendChild(fn);
        s.appendChild(f);
        sr.appendChild(s);
        
        // .. this will only hit the first occurance!!!
        var g = this.gnumeric;
        
        var styles = Roo.isIE ? g.getElementsByTagName('gmr:Styles')[0] : this.gnumeric.getElementsByTagNameNS('*','Styles')[0];
        
        // and finally to the document..
        styles.appendChild(sr);
        
    }
});    
       */