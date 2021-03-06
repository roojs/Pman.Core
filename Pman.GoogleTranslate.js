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
 * @param cb {String} callback - calls back with result or an error object.
 */
Pman.GoogleTranslate = function(str, src, dest, cb, force) {
        // load script: 
        var cur = 0;
        var sbits = [];
        var complete = '';
        
        
//        if (!Pman.GoogleTranslate.key) {
//            
//            new Pman.Request({
//                method : 'POST',
//                url : baseURL + '/Core/GoogleKey',
//                success : function(data)
//                {
//                    Pman.GoogleTranslate.key = data.data;
//                    
//                    Pman.GoogleTranslate(str, src, dest, cb, force);
//                    
//                },
//                failure : function() {
//                    Roo.log("Google key fetch failed");
//                    // do not display error message...
//                    return true;
//                }
//                
//                
//            });
//            
//            
//            
//            
//            return;
//        }
        
        
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
            
           // Roo.log("SEND : " + sbits[cur]);
            Pman.GoogleTranslate( sbits[cur], src, dest, function(result) {
                if (typeof(result) == 'object') {
                    cb(result);
                    return;
                }
                //padding might not be needed...
                complete += complete.length ? ' ' : ''; 
                complete += result;
                cur++;
                transbits();
            }, true);
        }
        
        // chunk up long strings..
        // we should roo.encode to test lenght..
        if (!force && str.length > 200) {
            var bits = str.split(/(\s+|[0-9\u3002\uff0c\u3001\u201c\u201d]+)/);
            sbits[0]  = '';
            for (var i =0; i < bits.length; i++) {
                if (sbits[cur].length + bits[i].length > 190) {
                    cur++;
                    sbits[cur] = bits[i];
                    continue;
                }
                //sbits[cur] += sbits[cur].length  ? ' ' : '';
                sbits[cur] += bits[i] + ' '
                
            }
           // Roo.log(sbits);
            cur = 0; // reset cursor.
            
            transbits();
            return;
        }
//                
//                
//                
//                
//          
//        var x = new Roo.data.ScriptTagProxy({ 
//            url:   'https://www.googleapis.com/language/translate/v2',
//                  //'http://ajax.googleapis.com/ajax/services/language/translate', 
//            callbackParam : 'callback'
//            
//            
//        });
//        
        src = src.replace('_','-');
        dest = dest.replace('_','-');
        // google does not recognize HK...
        
        switch(dest){
            case 'zh':
                src = 'zh-CN';
                break;
            case 'zh-HK':
                src = 'zh-TW';
                break;
        }
        
        switch(dest){
            case 'zh':
                dest = 'zh-CN';
                break;
            case 'zh-HK':
                dest = 'zh-TW';
                break;
        }
        
        new Pman.Request({
            url : baseURL + '/Core/GoogleTranslate.php',
            method :'POST',
            mask : 'Translating',
            maskEl : document.body,
            params : {
                text : str,
                src  : src,
                dest : dest
            },
            success: function(o)
            {
                if (!o.data) {
                    return o;
                }
                if(typeof(o.data.error) != 'undefined'){
                    Roo.get(document.body).unmask();
                    Roo.MessageBox.alert("Failure ", o.data.error.message);
                    return;
                }
                if(typeof(o.data.translations[0].translatedText) == 'undefined'){
                    Roo.MessageBox.alert("Failure ", "Does not found the translated text.");
                }
                cb(o.data.translations[0].translatedText);
            },
            failure: function (res) {
                Roo.log(res);
                if (!res.data) {
                    return res;
                }

                if(typeof(res.data.error) != 'undefined'){
                    Roo.get(document.body).unmask();
                    Roo.MessageBox.alert("Failure ", res.data.error.message);
                    return;
                }

                if(typeof(res.data.translations[0].translatedText) == 'undefined'){
                    Roo.MessageBox.alert("Failure ", "Does not found the translated text.");
                }
                cb(res.data.translations[0].translatedText);
                
                //Roo.MessageBox.alert("Failure ", res.message);
            }
        });
        
    };
            
    
Pman.gtranslate = Pman.GoogleTranslate;    