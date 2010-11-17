//<script type="text/javascript">
/**
 * 
 * GoogleTranslate
 * Translate a string using google translate API
 * 
 * Note: this does not handle html input at present - 
 * @param str {String} input string
 * @param src {String} source language (or '' for autoguess)
 * @param str {String} target language
 * @param str {String} callback - calls back with result or an error object.
 */
Pman.GoogleTranslate = function(str, src, dest, cb) {
        // load script: 
        var cur = 0;
        var sbits = [];
        var complete = '';
        
        function escapeDecode(encodedString) {
            var output = encodedString;
            var binVal, thisString;
            var myregexp = /(&#([0-9]+);)/;
            while ((match = myregexp.exec(output)) != null
                       && match.length > 1
                       && match[1] != '') {
              //     Roo.log(match);
              binVal = parseInt(match[2]);
              thisString = String.fromCharCode(binVal);
              output = output.replace(match[1], thisString);
            }
            return Roo.util.Format.htmlDecode( output);
        }
        
        
        
        function transbits()
        {
            while (true) {
                if ((cur +1) > sbits.length) {
                    //Roo.log("CALLING COMPLETED: " + complete);
                    cb(complete);
                    return;
                }
                if (!sbits[cur].length || !sbits[cur].replace(/\s+/).length) {
                    cur++;
                    continue;
                }
                break;
            }
            
           Roo.log("SEND : " + sbits[cur]);
            Pman.gtranslate( sbits[cur], src, dest, function(result) {
                if (typeof(result) == 'object') {
                    cb(result);
                    return;
                }
                complete += complete.length ? ' ' : ''; 
                complete += result;
                cur++;
                transbits();
            });
        }
        
        // chunk up long strings..
        if (str.length > 200) {
            var bits = str.split(/\s+/);
            sbits[0]  = '';
            for (var i =0; i < bits.length; i++) {
                if (sbits[cur].length + bits[i].length > 190) {
                    cur++;
                    sbits[cur] = bits[i];
                    continue;
                }
                sbits[cur] += sbits[cur].length  ? ' ' : '';
                sbits[cur] += bits[i] + ' '
                
            }
           // Roo.log(sbits);
            cur = 0; // reset cursor.
            
            transbits();
            return;
        }
                
                
                
                
                
        
        
        
        
        var x = new Roo.data.ScriptTagProxy({ 
            url:  'http://ajax.googleapis.com/ajax/services/language/translate', 
            callbackParam : 'callback' 
            
        });
        x.load(
            {
                v: '1.0',
                q : str,
                langpair : src + '|' +dest
            }, // end params.
            { // reader
                readRecords : function (o) {
                    if (!o.responseData) {
                        return o;
                    }
                    return escapeDecode(o.responseData.translatedText);
                }
            }, 
            function (result) {
                cb(result);
            },
            this,
            []
        );
        
            
        
    };
            
    
Pman.gtranslate = Pman.GoogleTranslate;    