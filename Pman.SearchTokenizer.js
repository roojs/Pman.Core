/**
 *
 * Search tokenizer - used with fields that are sent to Text_SearchParser
 * 
 *
 * usage :
 *  x = new Pman.SearchTokenizer('a and b or "test this" or ( tst:aval and vvv:erer }')
 *  data = x.parse();
 *
 *
 *  or
 *  data = Pman.SearchTokenizer.parse(....)
 */
 
Pman.SearchTokenizer = function(s)
{
        this.str = s;
        this.strlen = s.length;
        this.i = 0;
        this.tokens = [];
       //print_r(this);
}
Pman.SearchTokenizer.prototype =  {
    i : 0,
    str : '',
    strlen : 0,
    tokens : false ,
       //print_r(this);
    
    parse : function ()
    {
        var c;
        while(true) {
            c = this.getChar();
            
            if (false === c) { //eof..
                return this.tokens;
            }
            switch(c) {
                case ' ': continue;
                case ':': this.tokens.push( { type : ':' }) ; break;
                case '(': this.tokens.push( { type : '(' }) ; break;
                case ')': this.tokens.push( { type : ')' }) ; break;
                default:
                    this.ungetChar();
                    this.strParse();
                    break;
                
               
            }
        }
        // should not get here...
        return this.tokens;
    },
    strParse : function ()
    {
        var c;
        var str = '';
        while(true) {
            c = this.getChar();
            if (false === c) {
                this.addStr(str);
                return;
            }
            switch(c) {
                // end chars.
                case ' ': 
                case ':': 
                case '(': 
                case ')': this.addStr(str); this.ungetChar(); return;
                case '"': 
                    if (str.length) {
                        this.addStr(str); 
                        str = '';
                    }
                    this.strParseQuoted(c);
                    break;
                    
                default : 
                    str += c;
                    continue;
            }
            
        }
    },
    
    strParseQuoted: function (end) 
    {
        var str = '';   /// ignore \" slashed ???
        var c;
        while(true) {
            c = this.getChar();
            if (false === c) {
                this.addStr(str,true);
                return;
            }
            if (c == end) {
                this.addStr(str,true);
                return;
            }
            str += c;
        }
            
    }
    function addStr(s, q=false) { //q == quoted..
        s = q ? s : trim(s);
        if (!strlen(s)) {
            return;
        }
        if (!q) {
            
            if ((strtoupper(s) == 'AND') || (strtoupper(s) == 'OR')) {
                this.tokens[] = new Text_SearchParser_Token_Op(strtoupper(s));
                return;
            }
        }
        this.tokens[] = new Text_SearchParser_Token_String(s);
    }
    
    function getChar()
    {
        if (this.i >= this.strlen) {
            return false;
        }
        c = this.str[this.i];
        this.i++;
        return c;
    }
    function ungetChar()
    {
        this.i--;
    }
    
    
    
    
}