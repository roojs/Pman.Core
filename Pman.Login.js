//<script type="text/javascript">

/**
 * login code -
 * fires Pman 'authrefreshed'  event on every poll to server..
 *
 */

/***
re-arrange language code...
* flipping language should be like this:
* 
* Ext.apply(_T, _T[lang]);
* 
**/
 
  

Pman.Login =  new Roo.util.Observable({
    
    events : {
        'render' : true
    },
    disabled : false,
    
    dialog : false,
    form: false,
    haslogo : false,
    
    authUserId: 0,
    authUser: { id : false },
    oldAuthUser: false,
       
    checkFails : 0,
    versionWarn: false,
    sending : false,
    
    window_id : false, // we generate a UID so that we can track opened windows (and allow force logout / single window restrictions etc.)
    
    checkConnection : false, // the Roo.data.Connection for checking if still authenticated.
    
    onLoad : function() // called on page load...
    {
        // load 
       
        if (this.window_id === false) {
            this.window_id = crypto.randomUUID();
        }
        
         
        if (Roo.get('loading')) {
            Roo.get('loading').remove();
        }
        this.switchLang('en');
       
        // inital check if we are logged in..
        // if we are - then it will load the page,
        // otherwise - show login.
        Roo.Ajax.request({  
            url: baseURL + '/Login',  
            params: {
                getAuthUser: true,
                window_id : this.window_id
                
            },  
            method: 'GET',  
            success:  function(response, opts)  {  // check successfull...
            
                var res = Pman.processResponse(response);
                this.checkFails =0;
                if (!res.success) { // error!
                    this.checkFails = 5;
                    //console.log('call failure');
                    return Pman.Login.failure(response,opts);
                }
                
                
                if (!res.data.id) { // id=0 == login failure.
                    return this.show(true);
                }
                
                              
                        //console.log(success);
                this.fillAuth(res.data);   
                this.checkFails =0;
                Pman.onload();
                return false;
            },
            failure : Pman.Login.show,
            scope : Pman.Login
              
        });  
    }, 
    
    
    check: function(again) // called every so often to refresh cookie etc..
    {
        if (again) { // could be undefined..
            Pman.Login.checkFails++;
        } else {
            Pman.Login.checkFails = 0;
        }
        var _this = this;
        if (this.sending) {
            
            if ( Pman.Login.checkFails > 4) {
                Pman.Preview.disable();
                Pman.Login.show();
                return;
            }
            
            _this.check.defer(10000, _this, [ true ]); // check in 10 secs.
            return;
        }
        this.sending = true;
        if (!this.checkConnection) {
            this.checkConnection = new Roo.data.Connection();
        }
        this.checkConnection.request({
            url: baseURL + '/Login',  
            params: {
                getAuthUser: true,
                window_id : this.window_id
            },  
            method: 'GET',  
            success:  Pman.Login.success,
            failure : Pman.Login.failure,
            scope : Pman.Login
              
        });  
    }, 
    
    
    
    failure : function (response, opts) // called if login 'check' fails.. (causes re-check)
    {
        this.authUser = -1;
        this.sending = false;
        var res = Pman.processResponse(response);
        //console.log(res);
        if ( Pman.Login.checkFails > 2) {
            if (typeof(Pman.Preview) != 'undefined') {
                Pman.Preview.disable(); // not sure why this was added - but MO chrome does not have it.
            }
            Pman.Login.show();
            return;
        }
            
        Pman.Login.check.defer(1000, Pman.Login, [ true ]);
        return;  
    },
    
    
    success : function(response, opts)  // check successfull...
    {  
        this.sending = false;
        var res = Pman.processResponse(response);
        if (!res.success) {
            return this.failure(response, opts);
        }
        if (!res.data || !res.data.id) {
            return this.failure(response,opts);
        }
        //console.log(res);
        this.fillAuth(res.data);
        
        this.checkFails =0;
        
        
        if (Pman.onload) { 
            Pman.onload();
        }
        if (Pman.Login.callback) {
            Pman.Login.callback();
            
        }
        return false;
    },
    
    fillAuth: function(au) {
        this.startAuthCheck();
        this.authUserId = au.id;
        this.authUser = au;
        this.oldAuthUser = au;
        this.lastChecked = new Date();
        // if login is used on other applicaitons..
        if (Pman.fireEvent) { Pman.fireEvent('authrefreshed', au); }
        
        
        //Pman.Tab.FaxQueue.newMaxId(au.faxMax);
        //Pman.Tab.FaxTab.setTitle(au.faxNumPending);
        
        //this.switchLang(Roo.state.Manager.get('Pman.Login.lang', 'en'));
        Roo.state.Manager.set('Pman.Login.lang.'+appNameShort, au.lang);
        this.switchLang(au.lang);
        
     
        // open system... - -on setyp..
        if (this.authUserId  < 0) {
            Roo.MessageBox.alert("Warning", 
                "This is an open system - please set up a admin user with a password.");  
        }
         
        //Pman.onload(); // which should do nothing if it's a re-auth result...
        
             
    },
    
    
    intervalID : false,   /// the login refresher...
    
    lastChecked : false,
    
    startAuthCheck : function() // starter for timeout checking..
    {
        if (Pman.Login.intervalID) { // timer already in place...
            return false;
        }
        
        Pman.Login.intervalID =  window.setInterval(function() {
                  Pman.Login.check(false);
                }, 120000); // every 120 secs = 2mins..
        return true;
        
    },
    
    
    create : function()
    {
        if (this.dialog) {
            return;
        }
        var _this = this;
        
        this.dialog = new Roo.LayoutDialog(Roo.get(document.body).createChild({tag:'div'}),
        { // the real end set is here...
            autoCreated: true,
            title: "Login",
            modal: true,
            width:  350,
            height: 230,
            shadow:true,
            minWidth:200,
            minHeight:180,
            //proxyDrag: true,
            closable: false,
            draggable: false,
            collapsible: false,
            resizable: false,
            center: {
                autoScroll:false,
                titlebar: false,
               // tabPosition: 'top',
                hideTabs: true,
                closeOnTab: true,
                alwaysShowTabs: false
            }  
            
        });
        
        
        
        this.dialog.addButton("Forgot Password", function()
        {
            
            var n = _this.form.findField('username').getValue();
            if (!n.length) {
                Roo.MessageBox.alert("Error", "Fill in your email address");
                return;
            }
            new Pman.Request({
                url: baseURL + '/Login.js',
                mask : "Sending Password Reset email",
                params: {
                    passwordRequest: n
                },
                method: 'POST',  
                success:  function(res)  {  // check successfull...
                
                    if (!res.success) { // error!
                       Roo.MessageBox.alert("Error" , res.errorMsg ? res.errorMsg  : "Problem Requesting Password Reset");
                       return;
                    }
                    Roo.MessageBox.alert("Notice" , "Please check you email for the Password Reset message");
                },
                failure : function() {
                    Roo.MessageBox.alert("Error" , "Problem Requesting Password Reset");
                }
                
            });
        });
        
        this.dialog.addButton("Login", function()
        {
            Pman.Login.dialog.el.mask("Logging in");
            Pman.Login.form.doAction('submit', {
                    url: baseURL + '/Login',
                    method: 'POST'
            });
        });
        this.layout = this.dialog.getLayout();
        this.layout.beginUpdate();
        
        //layout.add('center', new Roo.ContentPanel('center', {title: 'The First Tab'}));
        // generate some other tabs
        this.form = new Roo.form.Form({
            labelWidth: 100 ,
            
            listeners : {
                actionfailed : function(f, act) {
                    // form can return { errors: .... }
                        
                    //act.result.errors // invalid form element list...
                    //act.result.errorMsg// invalid form element list...
                    
                    Pman.Login.dialog.el.unmask();
                    var msg = act.result.errorMsg || act.result.message;
                    msg = msg ||   "Login failed - communication error - try again.";
                    Roo.MessageBox.alert("Error",  msg); 
                              
                },
                actioncomplete: function(re, act) {
                     
                    Roo.state.Manager.set('Pman.Login.username.'+appNameShort,  Pman.Login.form.findField('username').getValue() );
                    Roo.state.Manager.set('Pman.Login.lang.'+appNameShort,  Pman.Login.form.findField('lang').getValue() );

                    // session expired && login as another user => reload
                    if(
                        Pman.Login.oldAuthUser && 
                        Pman.Login.oldAuthUser.email != Pman.Login.form.findField('username').getValue()
                    ) {
                        window.onbeforeunload = function() { };
                        document.location = baseURL + '?ts=' + Math.random();
                    }

                    Pman.Login.fillAuth(act.result.data);
                      
                    Pman.Login.dialog.hide();
                    if (Roo.get('loading-mask')) {
                        //Roo.get('loading').show();
                        Roo.get('loading-mask').show();
                    }
                    if (Pman.onload) { 
                        Pman.onload();
                    }
                    if (Pman.Login.callback) {
                        Pman.Login.callback();
                     
                    }
                    
                }
            }
        
            
            
             
        });
          
        
        
        this.form.add( 
       
            new Roo.form.TextField({
                fieldLabel: "Email Address",
                name: 'username',
                width:200,
                autoCreate : {tag: "input", type: "text", size: "20"}
            }),

            new Roo.form.Password({
                fieldLabel: "Password",
                name: 'password',
                width:200,
                autoCreate : {tag: "input", type: "text", size: "20"},
                listeners : {
                    specialkey : function(e,ev) {
                        if (ev.keyCode == 13) {
                            Pman.Login.dialog.el.mask("Logging in");
                            Pman.Login.form.doAction('submit', {
                                    url: baseURL + '/Login',
                                    method: 'POST'
                            });
                        }
                    }
                }  
            }) ,
            new Roo.form.ComboBox({
                fieldLabel: "Language",
                name : 'langdisp',
                store: {
                    xtype : 'SimpleStore',
                    fields: ['lang', 'ldisp'],
                    data : [
                        [ 'en', 'English' ],
                        [ 'zh_HK' , '\u7E41\u4E2D' ],
                        [ 'zh_CN', '\u7C21\u4E2D' ]
                    ]
                },
                
                valueField : 'lang',
                hiddenName:  'lang',
                width: 200,
                displayField:'ldisp',
                typeAhead: false,
                editable: false,
                mode: 'local',
                triggerAction: 'all',
                emptyText:'Select a Language...',
                selectOnFocus:true,
                listeners : {
                    select :  function(cb, rec, ix) {
                        
                        
                        Pman.Login.switchLang(rec.data.lang);
                        
                    }
                }
            
            })

        );
         
        
        var ef = this.dialog.getLayout().getEl().createChild({tag: 'div'});
        ef.dom.style.margin = 10;
          
        this.form.render(ef.dom);
         // logoprefix comes from base config - normally the owner company logo...
         // ??? 
         
        var img = typeof(appLogo) != 'undefined'  && appLogo.length ? appLogo :
            rootURL + '/Pman/'+appNameShort + '/templates/images/logo.gif' ;
         
         Pman.Login.form.el.createChild({
                tag: 'img', 
                src: img,
                style: 'margin-bottom: 10px;'
            },
            Pman.Login.form.el.dom.firstChild 
        ).on('error', function() {
            this.dom.style.visibility = 'hidden';
            this.dom.style.height = '10px';
        });
       
        var vp = this.dialog.getLayout().add('center', new Roo.ContentPanel(ef, {
            autoCreate : true,
            //title: 'Org Details',
            //toolbar: this.tb,
            width: 250,
            maxWidth: 250,
            fitToFrame:true
        }));
        
        this.layout.endUpdate();
        
        this.fireEvent('render', this);
        
        
        
        
        
    },
    resizeToLogo : function()
    {
        var sz = Roo.get(Pman.Login.form.el.query('img')[0]).getSize();
        if (!sz) {
            this.resizeToLogo.defer(1000,this);
            return;
        }
        var w = Roo.lib.Dom.getViewWidth() - 100;
        var h = Roo.lib.Dom.getViewHeight() - 100;
        Pman.Login.dialog.resizeTo(Math.max(350, Math.min(sz.width + 30, w)),Math.min(sz.height+200, h));
        Pman.Login.dialog.center();
    },
    
     
    
    show: function (modal, cb) 
    {
        if (this.disabled) {
            return;
        }
        
        
        
        this.callback = cb; // used for non-pman usage..
        modal = modal || false;
        if (Pman.Login.authUserId < 0) { // logout!?
            return;
        }
        
        if (Pman.Login.intervalID) {
            // remove the timer
            window.clearInterval(Pman.Login.intervalID);
            Pman.Login.intervalID = false;
        }
        
        this.create();
        
        
        
        if (Roo.get('loading')) {
            Roo.get('loading').remove();
        }
        if (Roo.get('loading-mask')) {
            Roo.get('loading-mask').hide();
        }
        
        //incomming._node = tnode;
        // why we want this non-modal????
        this.form.reset();
        this.dialog.modal = !modal;
        this.dialog.show();
        this.dialog.el.unmask(); 
        this.resizeToLogo.defer(1000,this);
        
        // if we have not created a provider.. do it now...
        if (!Roo.state.Manager.getProvider().expires) { 
            Roo.state.Manager.setProvider(new Roo.state.CookieProvider());
        }
         
         
        this.form.setValues({
            'username' : Roo.state.Manager.get('Pman.Login.username.'+appNameShort, ''),
            'lang' : Roo.state.Manager.get('Pman.Login.lang.'+appNameShort, 'en')
        });
        Pman.Login.switchLang(Roo.state.Manager.get('Pman.Login.lang.'+appNameShort, ''));
        if (this.form.findField('username').getValue().length > 0 ){
            this.form.findField('password').focus();
        } else {
           this.form.findField('username').focus();
        }
        
        
    },
 
    
     
    logout: function()
    {
        window.onbeforeunload = function() { }; // false does not work for IE..
        Pman.Login.authUserId = -1;
        Roo.Ajax.request({  
            url: baseURL + '/Login.html',  
            params: {
                logout: 1,
                window_id : this.window_id
            },  
            method: 'GET',
            failure : function() {
                Roo.MessageBox.alert("Error", "Error logging out. - continuing anyway.", function() {
                    document.location = baseURL + '?ts=' + Math.random();
                });
                
            },
            success : function() {
                Pman.Login.authUserId = -1;
                Pman.Login.checkFails =0;
                // remove the 
                document.location = baseURL + '?ts=' + Math.random();
            }
              
              
        }); 
    },
    switchLang : function (lang) {
        
        var  formLabel = function(name, val) {
                
                var lbl = Pman.Login.form.findField( name ).el.dom.parentNode.parentNode;
                if (lbl.getElementsByTagName('label').length) {
                    lbl = lbl.getElementsByTagName('label')[0];
                } else  {
                    lbl = lbl.parentNode.getElementsByTagName('label')[0];
                }
                   
                lbl.innerHTML = val;
            };
        
        if (!lang || !lang.length) {
            return;
        }
        if (typeof(_T.en) == 'undefined') {
            _T.en = {};
            Roo.apply(_T.en, _T);
        }
        
        if (typeof(_T[lang]) == 'undefined') {
            Roo.MessageBox.alert("Sorry", "Language not available yet (" + lang +')');
            return;
        }
        
        
        Roo.apply(_T, _T[lang]);
        // just need to set the text values for everything...
        if (this.form) {
            
               
            
            
            formLabel('password', "Password"+':');
            formLabel('username', "Email Address"+':');
            formLabel('lang', "Language"+':');
            this.dialog.setTitle("Login");
            this.dialog.buttons[0].setText("Forgot Password");
            this.dialog.buttons[1].setText("Login");
        }
        
        
    },
    
    inGroup : function(g)
    {
        return this.authUser && this.authUser.groups && 
            this.authUser.groups.indexOf(g) > -1;
    },
    isOwner : function()
    {
        return this.authUser && this.authUser.company_id_comptype && 
            this.authUser.company_id_comptype == 'OWNER';
    },
    
    /**
     * Depreciated = use Pman.I18n
     */
    
    i18nList: function (type, codes)
    {
        
        return Pman.I18n.listToNames(type, codes);
    },
    i18n: function(type, code) 
    {
        return Pman.I18n.toName(type, code);
        
    }
    
    
});




   
