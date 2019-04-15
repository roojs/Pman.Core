//<script type="text/javascript">


/**
* A few usefull tools to convert language info...
* 
* Our login details contain the available translation data..
* 
* 
* Languages are included in the main application
* using <script src="baseURL/Core/I18N/Data.js"></script>
* which set's up. Pman.I18n.data 
* 
* 
* 
* includes standard pulldowns.
*/



Pman.I18n = {
    
    
    
    /**
     * turn zh_HK,en  => into Chinese(HK) , English
     * @arg type type (c = country, l = lang)
     * @arg codes list of languages
     */
    listToNames: function (type, codes)
    {
        var ret = [];
        var _this = this;
        var cl = codes.split(',');
        Roo.each(cl , function(c) {
            ret.push(_this.toName(type, c));
        });
        return ret.join(', ');
    },
    /**
     * 
     * turns zh_HK into a Chinese(HK)
     * @arg type type (c = country, l = lang)
     * @arg langcode language code (eg. zh_HK, UK etc.)
     * 
     */
    toName: function(type, code) 
    {
        var ret = code;
        
        var lang = 'en';
        
        if(typeof(Pman.Login) != 'undefined' && typeof(Pman.Login.authUser.lang) == 'undefined'){
            lang = Pman.Login.authUser.lang;
        }
        
        if (code.indexOf('_') > -1) {
            var clang = code.split('_').shift();
            var cc = code.split('_').pop();
            return this.toName('l', clang.toLowerCase()) + ' (' +  this.toName('c', cc.toUpperCase()) + ')';
        }
        
        
        Roo.each(Pman.I18n.Data[lang][type], function(d) {
            if (d.code == code) {
                ret = d.title;
                return false; // stop!
            }
            return true;
        });
        return ret;
        
    },
    /**
     * List to Objects
     * zh_HK,en to [ { code=zh_HK, title=Chinese }, .... ]
     * @arg type type (c = country, l = lang)
     * @arg codes list of languages
     */
    listToObjects: function (type, codes)
    {
        var ret = [];
        var _this = this;
        if (!codes.length) {
            return ret;
        };
        var cl = codes.split(',');
        Roo.each(cl , function(c) {
            ret.push({
                code : c,
                title : _this.toName(type,c)
            })
        });
        return ret;
    },
    
    
    
    reader :   { // std. reader for i18n items.
        root : 'data',
        totalProperty : 'total',
        id : 'code',
        xtype : 'JsonReader',
        fields : [
            'code',
            'title'
        ]
	},
    
    
    
    /**
     * dataToProxy
     * return proxy data for a pulldown.
     * @param {String} type  eg. l,c,m (lang/country/money)
     *    
     * usage:
     {
      xtype: 'Store',
      xns: Roo.data,
      reader : Pman.I18n.reader,
      proxy : {
         xtype : 'MemoryProxy',
         xns : Roo.data,
         data : Pman.I18n.dataToProxy('l'), // eg. language
         remoteSort : false,
         sortInfo : { field : 'title' , direction : 'ASC' } 
      }
         * 
         *}
     * 
     * 
     */
    
    dataToProxy : function(type)
    {
        var lang = Pman.Login.authUser.lang || 'en';
        return Pman.I18n.Data[lang][type];
    },
    
    /**
     * simpleStoreData:
     * return a simplestore to be used by country/language combos
     * eg.
     * store: (function() {
            return Pman.I18n.simpleStoreData('c');
        })(),
     *
     * @param {Char} type (c,l,m)
     * @param {function} (optional) filter language list
     *     called with object { code: xxx , title: xxx }
     *     if it exists then returning false will hide the entry.
     */
    
    simpleStoreData : function(type, filter)
    {
        filter = typeof(filter) == 'undefined' ? false : filter;
        var lang =  'en';
        try {
            lang = Pman.Login.authUser.lang;
        } catch (E) {};
        lang = lang || 'en';
        var ret = [];
        Roo.each(Pman.I18n.Data[lang][type], function (o) {
            if (filter !== false && filter(o) === false) {
                return;
            }
            ret.push([ o.code, o.title ]);
        });
        
         ret = ret.sort(function(a,b) {
            if (a[0] == '**') { return 1; } // other always at end..
            if (b[0] == '**') { return -1; } // other always at end..
            return a[1]  > b[1] ? 1 : -1;
        });
        
        return ret;
    },
    // DEPRECIATED... -- see dataToProxy
    countryStore : function() { return {
        
        // load using HTTP
        xtype: 'Store',
        proxy: {
            xtype: 'HttpProxy',
            url: baseURL + '/Core/I18n/Country.html',
            method: 'GET'
        },
        
        reader: Pman.I18n.reader,
        listeners : {
             
            loadexception : Pman.loadException

        },
        remoteSort: false,
        sortInfo: {
            field: 'title', direction: 'ASC'
        }
              
    }},
      // DEPRECIATED...
    languageStore: function() {return{
        // load using HTTP
        xtype: 'Store',
        proxy: {
            xtype: 'HttpProxy',
            url: baseURL + '/Core/I18n/Lang.html',
            method: 'GET'
        },
        
        reader: Pman.I18n.reader,
        listeners : {
            loadexception : Pman.loadException
        },
        remoteSort: false,
        sortInfo: {
            field: 'title', direction: 'ASC'
        }
    }},
      // DEPRECIATED...
    currencyStore: function() {return{
        // load using HTTP
        xtype: 'Store',
        proxy: {
            xtype: 'HttpProxy',
            url: baseURL + '/Core/I18n/Currency.html',
            method: 'GET'
        },
        
        reader: Pman.I18n.reader,
        listeners : {
             
            loadexception : Pman.loadException
    
        },
        remoteSort: false,
        sortInfo: {
            field: 'title', direction: 'ASC'
        }
    }},
      // DEPRECIATED...
    country: function(cfg) {
        var _this = this;
        cfg = cfg || {};
        return Roo.apply({
                // things that might need chnaging
                name : 'country_title',
                hiddenName : 'country',
                width : 290,
                listWidth : 300,
                fieldLabel : "Country",
                allowBlank : false,
                
                // less likely
                qtip : "Select Country",
                
                value : '',
                // very unlinkly
                xtype : 'ComboBox',   
                store: this.countryStore(),
                displayField:'title',
                valueField : 'code',
                typeAhead: false,
                editable: false,
                //mode: 'local',
                triggerAction: 'all',
                //emptyText:'Select a state...',
                selectOnFocus:true 
                 
            }, cfg);
    },
      // DEPRECIATED...
    language: function(cfg) {
               var _this = this;
        cfg = cfg || {};
        return Roo.apply({
                // things that might need chnaging
                
                name : 'language_title',
                hiddenName : 'language',
                width : 290,
                listWidth : 300,
                fieldLabel : "Language",
                allowBlank : false,
                
                // less likely
                qtip : "Select Language",
                
                value : '',
                // very unlinkly
                xtype : 'ComboBox',   
                store: this.languageStore(),
                displayField:'title',
                valueField : 'code',
                
                typeAhead: false,
                editable: false,
                //mode: 'local',
                triggerAction: 'all',
                //emptyText:'Select a state...',
                selectOnFocus:true 
                
            }, cfg);
    },
         // DEPRECIATED...
    currency: function(cfg) {
        var _this = this;
        cfg = cfg || {};
        return Roo.apply({
                // things that might need chnaging
                name : 'currency_title',
                hiddenName : 'currency',
                width : 290,
                listWidth : 300,
                fieldLabel : "Currency",
                allowBlank : false,
                
                // less likely
                qtip : "Select Currency",
                
                value : '',
                // very unlinkly
                xtype : 'ComboBox',   
                store: this.currencyStore(),
                displayField:'code',
                valueField : 'code',
                typeAhead: false,
                editable: false,
                //mode: 'local',
                triggerAction: 'all',
                //emptyText:'Select a state...',
                selectOnFocus:true,
                   tpl: new Ext.Template(
                    '<div class="x-grid-cell-text x-btn button">',
                        '{title} ({code})</b>',
                    '</div>'
                ) 
                 
            }, cfg);
    },
      // DEPRECIATED...
    languageList : function(cfg) {
        cfg = cfg || {};
         
        return Roo.apply({
                
                name : 'language',
                //hiddenListName
                fieldLabel : "Language(s)",
                idField : 'code',
                nameField: 'title',
                renderer : function(d) {
                    return String.format('{0}',  d.title );
                },
                
                
                xtype: 'ComboBoxLister',
                displayField:'title',
                value : '',
               
                qtip : "Select a language to add.",
                selectOnFocus:true,
                allowBlank : true,
                width: 150,
                boxWidth: 300,
                 
                store:  this.languageStore(),
               
                editable: false,
                //typeAhead: true,
                forceSelection: true,
                //mode: 'local',
                triggerAction: 'all',
                tpl: new Ext.Template(
                    '<div class="x-grid-cell-text x-btn button">',
                        '{title}</b>',
                    '</div>'
                ),
                queryParam: 'query[name]',
                loadingText: "Searching...",
                listWidth: 400,
               
                minChars: 2,
               // pageSize:20,
                setList : function(ar) {
                    var _this = this;
                    Roo.each(ar, function(a) {
                        _this.addItem(a);
                    });
                },
                toList : function() {
                    var ret = [];
                    this.items.each(function(a) {
                        ret.push(a.data);
                    });
                    return ret;
                }
                
                 
            }, cfg);
    },
      // DEPRECIATED...
    countryList : function(cfg) {
        cfg = cfg || {};
         
         
        return Roo.apply({
                
                name : 'countries',
                fieldLabel : "Country(s)",
                idField : 'code',
                nameField: 'title',
                renderer : function(d) {
                    return String.format('{0}',  d.title );
                },
                
                
                xtype: 'ComboBoxLister',
                displayField:'title',
                value : '',
               
                qtip : "Select a country to add.",
                selectOnFocus:true,
                allowBlank : true,
                width: 150,
                boxWidth: 300,
                 
                store:  this.countryStore(), 
               
                editable: false,
                //typeAhead: true,
                forceSelection: true,
                //mode: 'local',
                triggerAction: 'all',
                tpl: new Ext.Template(
                    '<div class="x-grid-cell-text x-btn button">',
                        '{title}</b>',
                    '</div>'
                ),
                queryParam: 'query[name]',
                loadingText: "Searching...",
                listWidth: 400,
               
                minChars: 2,
               // pageSize:20,
                setList : function(ar) {
                    var _this = this;
                    Roo.each(ar, function(a) {
                        _this.addItem(a);
                    });
                },
                toList : function() {
                    var ret = [];
                    this.items.each(function(a) {
                        ret.push(a.data);
                    });
                    return ret;
                }
                
                 
            }, cfg);
    }
     
     
    
};
 
