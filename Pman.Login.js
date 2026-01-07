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
// not sure where this is from??
  

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
    logging_out : false,
    
    checkConnection : false, // the Roo.data.Connection for checking if still authenticated.
    authCheckPaused : false, // pause auth checks during long-running SSE operations
    
    onLoad : function() // called on page load...
    {
        // load 
        // exclude chrome extensions. - this only works on https (crypto is not available in http - except localhost
        var has_crypto = document.location.protocol == 'https:' || (
            document.location.protocol == 'http:'  && document.location.hostname == 'localhost'    
        );
        if (Pman.Login.window_id === false && has_crypto) {
            // persitant in windows..
            Pman.Login.window_id = window.sessionStorage.getItem('windowid');
            if (!Pman.Login.window_id) {
                Pman.Login.window_id = crypto.randomUUID();
                window.sessionStorage.setItem('windowid', Pman.Login.window_id);               
            }
        }
        
         
        if (Roo.get('loading')) {
            Roo.get('loading').remove();
        }
        this.switchLang('en');
       
        // inital check if we are logged in..
        // if we are - then it will load the page,
        // otherwise - show login.
        new Pman.Request({  
            url: baseURL + '/Core/Auth/State',  
            params: { 
                window_id : this.window_id,
                _require_window : 1  // we require that this window is logged in - otherwise we force the user to login again (and create a session)
                
            },  
            method: 'GET',  
            success:  function(res)  {  // check successfull...
            
                this.checkFails =0;
                if (!res.success) { // error!
                    this.checkFails = 5;
                    //console.log('call failure');
                    return Pman.Login.failure(res);
                }
                
                if (res.data.id*1 < 0) {
                    this.fillAuth(res.data);
                    return this.openSystem();
                }
                
                if (!res.data.id) { // id=0 == login failure.
                    
                    if(window.location.pathname.substr(baseURL.length).match(/\/PasswordReset\//)){
                        return Pman.Dialog.AdminPasswordReset.show({}, function(){
                            window.location.href = baseURL;
                        });
                    
                    }
                    
                    return this.show(true);
                }
                
                              
                        //console.log(success);
                this.fillAuth(res.data);   
                this.checkFails =0;
                Pman.onload();
                return false;
            },
            failure :  function(res)  {
                
                
                 

                
                //Roo.log(res);
                if (res.code == 'NOTICE-MULTI-WIN') {
                    Roo.MessageBox.show({
                        title: "Multiple Windows",
                        msg: "You are currently using this application in another window, What do you want to do?",
                        buttons : {ok: "Logout other window", cancel: true},
                        fn : function(r) {
                            if (r == 'ok') {
                                this.show(false, false, { logout_other_windows : 1 });
                                return;
                            }
                            window.close();
                            
                        },
                        scope: this
                        
                    });
                    return;
                    
                } 
                this.show();
                
                
            },
            scope : Pman.Login
              
        });  
    }, 
    
    
    check: function(again) // called every so often to refresh cookie etc..
    {
        if (Pman.Login.logging_out) {
            return; // don't keep rechecking if we are already about to log out.
        }
        if (Pman.Login.authCheckPaused) {
            return; // skip auth check during long-running SSE operations
        }
        
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
        
        new Pman.Request({
            url: baseURL + '/Core/Auth/State',  
            params: {
                getAuthUser: true,
                window_id : this.window_id,
                app_id : appNameShort
            },  
            method: 'GET',  
            success:  Pman.Login.success,
            failure : Pman.Login.failure,
            scope : Pman.Login
              
        });  
    }, 
    
    
    
    failure : function (res) // called if login 'check' fails.. (causes re-check)
    {
        this.authUser = -1;
        this.sending = false;
        if (res.code == 'NOTICE-MULTI-WIN') {
             
            Roo.MessageBox.show({
                title: "Multiple Windows",
                msg: "You are currently using this application in another window, What do you want to do?",
                buttons : {ok: "Logout other window", cancel: true},
                fn : function(r) {
                    if (r == 'ok') {
                        this.show(true, false, { logout_other_windows : 1 });
                        return;
                    }
                    window.onbeforeunload = function() { };
                    window.close(); // will probably not work.
                    document.location = "about:blank";
                    
                },
                scope: this
                
            });
            return;            
        }
        if (res.code == 'NOTICE-FORCE-LOGOUT') {
            // kill the rechecks..
            Pman.Login.logging_out = true;
            Roo.MessageBox.alert("Forced Logout", "You have been logged out by the Administrator", function() {
                Pman.Login.logout();
            });
            return;
        }
        // other responses?
        if (res.code == 'NOTICE-LOGIN-NOAUTH') {
            // not logged in...
            Pman.Login.show();
            return;
            
        }
        
        console.log(["failed", res]);
        if ( Pman.Login.checkFails > 2) {
            //if (typeof(Pman.Preview) != 'undefined') {
            Pman.Preview.disable(); // not sure why this was added - but MO chrome does not have it.
            //}
            Pman.Login.show();
            return;
        }
            
        Pman.Login.check.defer(1000, Pman.Login, [ true ]);
     },
    
    
    success : function(res)  // check successfull...
    {  
        this.sending = false;
         if (!res.success) {
            return this.failure(res);
        }
        if (!res.data || !res.data.id) {
            return this.failure(res);
        }
        //console.log(res);
        this.fillAuth(res.data);
        
        this.checkFails =0;
        
        
        if (Pman.onload) { 
            Pman.onload(); // classic roo..
        }
        if (Pman.Login.callback) {
            Pman.Login.callback();
            
        }
        return false;
    },
    
    fillAuth: function(au)
    {
        if(au.id * 1 > 0) {  // BS allows un-authtenticated.
            this.startAuthCheck();
        }
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
      
    
     
    
    show: function (modal, cb, values) 
    {
        if (this.disabled) {
            return;
        }
        values = values || {};
        values.modal = modal; // why?
        Pman.Dialog.Login.show(values, function(res) {
            Pman.Login.fillAuth(res.data);  
            if (cb) {
                cb();
            }
            Pman.onload();
        });
        return;
     
        
    },
 
    
     
    logout: function()
    {
        window.onbeforeunload = function() { }; // false does not work for IE..
        Pman.Login.authUserId = -1;
        new Pman.Request({  
            url: baseURL + '/Core/Auth/Logout',  
            params: {
                logout: 1,
                app_id : appNameShort
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
                
            var lbl = Pman.Dialog.Login.form.findField( name ).el.dom.parentNode.parentNode;
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
            Pman.Dialog.Login.dialog.setTitle("Login");
            Pman.Dialog.Login.dialog.buttons[0].setText("Forgot Password");
            Pman.Dialog.Login.dialog.buttons[1].setText("Login");
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
     * hasPerm:
     * Does the authenticated user have permission to see this.
     * 
     * @param {String} name the [Module].[permission] to check for
     * @param {Char} lvl  - which type of permission to use (eg. S=show...)
     * @returns {Boolean} tue indicates permission allowed
     */
    hasPerm: function(name, lvl) {
        if (
            (typeof(this.authUser) != 'object')
            ||
            (typeof(this.authUser.perms) != 'object')
            ||
            (typeof(this.authUser.perms[name]) != 'string')
            ) {
                return false;
        }
        
        return this.authUser.perms[name].indexOf(lvl) > -1;
        
    },
    /**
     * hasPermExists:
     * Is there a permission defined for this (used by module registration.)
     * 
     * @param {String} name the [Module].[permission] to check for
     * @returns {Boolean} tue indicates permission exists.
     */
    hasPermExists: function(name) {
        if (
            (typeof(this.authUser) != 'object')
            ||
            (typeof(this.authUser.perms) != 'object')
            ||
            (typeof(this.authUser.perms[name]) != 'string')
            ) {
                return false;
        }
        return true;
    },
    
    
    
    
    
    openSystemCreateUser : function(data)
    {
        if (!data || !data.id) {
            //Roo.log("Force Admin");
            Pman.Dialog.AdminStaff.show( 
                { 
                    id : 0, 
                    company_id : Pman.Login.authUser.company_id* 1,
                    company_id_name : Pman.Login.authUser.company_id_name,
                    role : 'Administrators'
                }, function(data) {
                    //forceAdmin(data);
                    Pman.Login.openSystemCreateUser(data);
                }
            );
            return;
        }
        Roo.state.Manager.set('Pman.Login.username', data.email),
        window.onbeforeunload = false;
        document.location = baseURL + '?ts=' + Math.random();
    },
    
    openSystemCreateCompany: function(data)
    {
        if (Pman.Login.authUser.company_id * 1 > 0) {
            //forceAdmin();
            Pman.Login.openSystemCreateUser(data);
            return;
        }
        if (!data || !data.id) {
            Pman.Dialog.AdminCompany.show( { id : 0, comptype: 'OWNER' }, function(data) {
                Roo.log("company dialog returned");
                Roo.log(data);
                //forceCompany(data);
                Pman.Login.openSystemCreateCompany(data);
            });
            return;
        }
        Pman.Login.authUser.company_id_id  = data.id;
        Pman.Login.authUser.company_id  = data.id;
        Pman.Login.authUser.company_id_name  = data.name;
        Roo.log("forcing admin");
        this.openSystemCreateUser();
    },
    
    openSystem : function()
    {
        Roo.MessageBox.alert("Error", "Admin accounts have not been created - use the old admin at present");
        
        new Pman.Request({  
            url: baseURL + '/Core/Auth/HasOwnerCompany',
            method: 'POST',  
            
            success:  function(res)  {  // check successfull...
                
                if(res.data == 1) {
                    this.openSystemCreateUser();
                    return;
                }
                
                if(res.data == 0) {
                    this.openSystemCreateCompany();
                    return;
                }
                
                if(res.data > 0) {
                    Roo.MessageBox.alert(
                        "Error",  
                        "There are more than 1 company in the system. please fix the data"
                    );
                    return;
                }
            },
            failure : function(res)
            {
                Roo.MessageBox.alert(
                    "Error",  
                    "Invalid params for check owner company"
                );
            },
            scope : Pman.Login
        });
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




   
