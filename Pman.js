 
/**
 * 
 *  
 *  This is the main Pman class
 *  - it's a bit too heavy at present - needs to go on a diet....
 */
 
// translation..
if (typeof(_T) == 'undefined') { _T={};}



Roo.XComponent.on('register', function(e) {
    if (typeof(Pman) != 'undefined') { return Pman.xregister(e); } return true;
});
Roo.XComponent.on('beforebuild', function(e) {
    if (typeof(Pman) != 'undefined') { return Pman.xbeforebuild(e); } return true;
});

Roo.XComponent.on('buildcomplete',  
    function() {
        Pman.building = false;   
        Pman.layout.getRegion('center').showPanel(0);
        Pman.layout.endUpdate(); 
        Pman.addTopToolbar();  
        Pman.finalize();
        Pman.fireEvent('load',this);
        
        if (!Pman.layout.getRegion('south').panels.length) {
            Pman.layout.getRegion('south').hide();
        }
    
    
    }
);

//Roo.debug = 1;
  

Pman = new Roo.Document(
{
   /// appVersion: '1.7', // fixme = needs to be removed - use Global AppVersion
    subMenuItems : [],
    topMenuItems : [],
    rightNames: { }, /// register right names here - so they can be translated and rendered.
    /**
     * @property {Roo.menu.Menu} pulldownMenu - the 'add menu pulldown, you can use it to add items..
     *
     */
    pulldownMenu : false, 
    
    
    buildCompleted : false, // flag to say if we are building interface..
    events : {
        'beforeload' : true, // fired after page ready, before module building.
        'load' : true, // fired after module building
        'authrefreshed' : true // fire on auth updated?? - should be on Login?!?!?
    },
    
    listeners : {
        'ready' : function()
        {
            // kludge to fix firebug debugger
            if (typeof(console) == 'undefined') {
                console = { log : function() {  } };
            }
            
            // remove loader..
            if (Roo.get('loading')) {
                Roo.get('loading').remove();
            }
            
            Roo.state.Manager.setProvider(new Roo.state.CookieProvider());
            
            // link errors...
            
            if (AppLinkError.length) {
                Roo.MessageBox.alert("Error", AppLinkError, function() {
                    Pman.Login.onLoad();
                });
                return;
            }
            
            
            // reset password!!!!
            if (showNewPass.length) {
                Pman.PasswordChange.show(  { passwordReset : showNewPass },
                    function(data) {
                        // fail and success we do  a load...
                        Pman.Login.onLoad();
                    }
                );
                return;
            }
             
            Pman.Login.onLoad();
            
        },
        'load' : function()
        {
            if (Roo.get('loading-logo-tile')) {
                Roo.get('loading-logo-tile').remove();
            }
            if (Roo.get('loading-logo-tile-top')) {
                Roo.get('loading-logo-tile-top').remove();
            }
            if (Roo.get('loading-logo-bottom')) {
                Roo.get('loading-logo-bottom').remove();
            }
            if (Roo.get('loading-logo-center')) {
                Roo.get('loading-logo-center').remove();
            }
        }   
        
    },
   
    fakeRoot :  new Roo.XComponent( {
        modKey : '000',
        module : 'Pman',
        region : 'center',
        parent : false,
        isTop : true,
        name : "Pman Base",
        disabled : false, 
        permname: '' ,
        render : function (el) { this.el = this.layout; }
    }),
    
    layout: false,
    
    onload: function() {
        //this.fireEvent('beforeload',this);
        
        
        
        if (this.layout) {
            return; // already loaded
        } 
        if (Roo.get('loading')) {
            Roo.get('loading').remove();
        }
        if (Roo.get('loading-mask')) {
            Roo.get('loading-mask').show();
        }
        
     
        var _this = this;
        this.stime = new Date();
        this.layout = new Roo.BorderLayout(document.body, {
            north: {
                split:false,
                initialSize: 25,
                titlebar: false
            },
         
             
            center: {
                titlebar: false,
                autoScroll:false,
                closeOnTab: true,
                tabPosition: 'top',
                //resizeTabs: true,
                alwaysShowTabs: true,
                minTabWidth: 140
            } ,
            south: {
                collapsible : true,
                collapsed : true,
                split:false,
                height: 120,
                titlebar: false 
            }
            
        });
        this.fakeRoot.layout = this.layout;
        /*
        Pman.register( Roo.apply(this.fakeRoot, {
            layout : this.layout      
                
                                 
        } ) );
        */
        
        // creates all the modules ready to load..
        
        this.fireEvent('beforeload',this);
        
        
        
        this.layout.beginUpdate();
        this.layout.add('north', new Roo.ContentPanel('title', 'North'));
        var au = Pman.Login.authUser;
        if (au.id > 0 && au.company_id_background_color && au.company_id_background_color.length) {
            Roo.get('title').dom.style.backgroundColor = '#' + au.company_id_background_color;
            Roo.get('headerInformation').dom.style.color = this.invertColor('#' + au.company_id_background_color);
        }
        if (au.id > 0 && au.company_id_logo_id * 1 > 0) {
            Roo.get('headerInformation-company-logo').dom.src =  baseURL + 
                '/Images/' + au.company_id_logo_id + '/' + au.company_id_logo_id_filename;
        } else {
            Roo.get('headerInformation-company-logo').dom.src = Roo.BLANK_IMAGE_URL;
        }
        
        Roo.get('headerInformation').dom.innerHTML = String.format(
                "You are Logged in as <b>{0} ({1})</b>", // to {4} v{3}", // for <b>{2}</b>",
                au.name, au.email, au.company_id_name, 
                AppVersion , appNameShort
        );
        
        
        document.title = appName + ' v' + AppVersion + ' - ' + au.company_id_name;
        Roo.QuickTips.init(); 
        if (Roo.isGecko) {
           Roo.useShims = true;
        }
       
        //this.mainLayout.beginUpdate();
        //var maskDom = Roo.get(document.body)._maskMsg.dom
        this.layout.beginUpdate();
        
        Pman.building = true;
        Roo.XComponent.build();
         
        
        
     
    },
    
    addTopToolbar : function()
    {
          //console.log( "t6:" + ((new Date())-stime));
        //this.mainLayout.endUpdate();
        // make a new tab to hold administration stuff...
        
       
        //console.log( "t7:" + ((new Date())-stime));
        if (!Pman.layout.getRegion('center').tabs) {
                Roo.log("Error could not find tabs? - not adding toolbar?");
                return;
        }
        
        var se = Pman.layout.getRegion('center').tabs.stripEl;
        var tbh = se.createChild( 
                { tag: 'td', style: 'width:100%;'  });
        
        var lotb = new Roo.Toolbar(tbh);
        
        if (Roo.isSafari) {
            var tbl = se.child('table', true);
            tbl.setAttribute('width', '100%');
        }
        
        if (Pman.hasPerm('Core.ChangePassword','S')) {
            
            lotb.add(
                new Roo.Toolbar.Fill(), 
         
                {
                    text: "Change Password",
                    cls: 'x-btn-text-icon',
                    icon: rootURL + '/Pman/templates/images/change-password.gif',
                    handler : function(){
                        Pman.PasswordChange.show({});
                    }
                }, '-'
            );
        }     
            
        if (this.topMenuItems.length) {
            
            Roo.each(this.topMenuItems, function (mi) {
                lotb.add(mi);
            });
            lotb.add('-');
        }
        
        
        
        if (this.subMenuItems.length) {
            
            this.subMenuItems.sort(function (a,b) {
                return a.seqid > b.seqid ? 1 : -1;
            });
            // chop off last seperator.
            // since we always add it.. just chop of last item
            this.subMenuItems.pop(); 
            
            var btn = new Roo.Toolbar.Button( 
                {
                    text: "Add New Item",
                    cls: 'x-btn-text-icon',
                    icon: Roo.rootURL + 'images/default/dd/drop-add.gif',
                    menu : {
                        items : this.subMenuItems
                    }     
                }
            );
            this.pulldownMenu = btn.menu;
            lotb.add(btn, '-');
            
        }
       
        lotb.add(
            {
                text: "Logout",
                cls: 'x-btn-text-icon',
                icon: rootURL + '/Pman/templates/images/logout.gif',
                handler: function() {
                    Pman.Login.logout();
                }
                 
            }
        );
      
       // this.layout.endUpdate();
    },
    
    
    finalize : function() {
        
      
       
        window.onbeforeunload = function(e) { 
            e = e || window.event;
            var r = "Closing this window will loose changes, are you sure you want to do that?";

            // For IE and Firefox
            if (e) {
                e.returnValue = r;
            }

            // For Safari
            return r;
            
        };
        
        Roo.MessageBox.hide();
        if (Roo.get('loading-mask')) {
           Roo.get('loading-mask').remove();
        }
        
        
        this.buildCompleted = true; // now we can force refreshes on everything..
        
        
        // does the URL indicate we want to see a system..
        if (AppTrackOnLoad * 1 > 0) {
            this.onLoadTrack(AppTrackOnLoad,false);
        }
        
        // Open system..
        
        var forceAdmin = function(data)
        {
            if (!data || !data.id) {
                //Roo.log("Force Admin");
                Pman.Dialog.PersonStaff.show( 
                    { 
                        id : 0, 
                        company_id : Pman.Login.authUser.company_id * 1, 
                        company_id_name : Pman.Login.authUser.company_id_name
                    }, function(data) {
                        forceAdmin(data);
                    }
                );
                return;
            }
            
            Roo.state.Manager.set('Pman.Login.username', data.email),
            window.onbeforeunload = false;
            document.location = baseURL + '?ts=' + Math.random();
        }
        
        var forceCompany = function(data) {
            if (Pman.Login.authUser.company_id * 1 > 0) {
                forceAdmin();
                return;
            }
            if (!data || !data.id) {
                Pman.Dialog.CoreCompanies.show( { id : 0, comptype: 'OWNER' }, function(data) {
                    Roo.log("company dialog returned");
                    Roo.log(data);
                    forceCompany(data);
                });
                return;
            }
            Pman.Login.authUser.company_id_id  = data.id;
            Pman.Login.authUser.company_id  = data.id;
            Pman.Login.authUser.company_id_name  = data.name;
            Roo.log("forcing admin");
            forceAdmin();
        }
        
        if (Pman.Login.authUser.id < 0) {
            // admin company has been created - create the user..
            if (Pman.Login.authUser.company_id* 1 > 0) {
                forceAdmin();
                return;
            }
            
            forceCompany();
            /// create account..
            
            
        }
        

    },
    
    
    // REMOVE THESE 
    
     
    onLoadTrack : function(id,cb) {
        this.onLoadTrackCall(id, cb, 'DocumentsCirc_');
    },
    onLoadTrackEdit : function(id,cb) {
        this.onLoadTrackCall(id, cb, 'Documents_');
    },
    
    
    /// ----------- FIXME -----
    
    
    onLoadTrackCall : function(id,cb, cls) {
        Roo.get(document.body).mask("Loading Document details");

        new Pman.Request({
            url: baseURL + '/Roo/Documents.html',  
            params: {
                _id: id
            },  
            method: 'GET',  
            success : function(res) {
                var data = res.data;
                Roo.get(document.body).unmask();
             
                
                switch(data.in_out) {
                    case 'IN' : cls+='In';break;
                    case 'OUT' : cls+='Out';break;
                    case 'WIP' : cls+='Wip';break;
                    default: 
                        Roo.MessageBox.alert("Error", "invalid in_out");
                        return;
                }
                Pman.Dialog[cls].show(data, cb ? cb : Pman.refreshActivePanel);
            }, 
            
            failure: function() {
                Roo.get(document.body).unmask();
                //if (cb) {
                //    cb.call(false);
                //}
                 
           }
        });
          
    },
    
    refreshActivePanel : function() {
        var actpan = this.layout.getRegion('center').getActivePanel();
        if (actpan.controller && actpan.controller.paging) {
            actpan.controller.paging.onClick('refresh');
            return;
        }
        
        var agid = Pman.layout.getRegion('center').getActivePanel().id;
        if (!agid) {
            return;
        }
        Pman.Tab[agid].paging.onClick('refresh');
    },
    toCidV : function(data) {
        return 'C' + data.in_out.substring(0,1) + data.cid;
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
            (typeof(Pman.Login.authUser) != 'object')
            ||
            (typeof(Pman.Login.authUser.perms) != 'object')
            ||
            (typeof(Pman.Login.authUser.perms[name]) != 'string')
            ) {
                return false;
        }
        
        return Pman.Login.authUser.perms[name].indexOf(lvl) > -1;
        
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
            (typeof(Pman.Login.authUser) != 'object')
            ||
            (typeof(Pman.Login.authUser.perms) != 'object')
            ||
            (typeof(Pman.Login.authUser.perms[name]) != 'string')
            ) {
                return false;
        }
        return true;
    },
    
    
    
    
    
    
    Readers : {},
    ColModels : {},
    Forms : {},
    Tab : {},
    Dialog : {},
    
    processResponse : function (response)
    {
        var res = '';
        try {
            res = Roo.decode(response.responseText);
            // oops...
            if (typeof(res) != 'object') {
                res = { success : false, errorMsg : res, errors : true };
            }
            if (typeof(res.success) == 'undefined') {
                res.success = false;
            }
            
        } catch(e) {
            res = { success : false,  errorMsg : response.responseText, errors : true };
        }
        return res;
    },
    genericDelete : function(tab,tbl)
    {
        Pman.Delete.progress(tab,tbl) 
    },
    
    
    standardActionFailed :  function(f, act, cb) {
    
        if (act.failureType == 'client') {
            Roo.MessageBox.alert("Error", "Please Correct all the errors in red", cb);
            return;
        }
        if (act.failureType == 'connect') {
            Roo.MessageBox.alert("Error", "Problem Connecting to Server - please try again.", cb);
            return;
        }
        
        if (act.type == 'submit') {
            
            Roo.MessageBox.alert("Error", typeof(act.result.errorMsg) == 'string' ?
                String.format('{0}', act.result.errorMsg) : 
                "Saving failed = fix errors and try again", cb);
            return;
        }
        
        // what about load failing..
        Roo.MessageBox.alert("Error", "Error loading details",cb); 
    },
    /**
     * Depreciated - USE new Pman.Request
    *  We need to replace all the uses with this, however the api is slightly different,
    *  the success argument is res.data, not res..
     * 
     */
    request : function(c) {
        return new Pman.Request(c);
          
    },
    
    
    // depreciated - use Pman.Download()
    
    download : function(c) {
        
        return new Pman.Download(c);
    },
    
    // fixme - move to document manager...
    downloadRevision : function(doc, rev)
    {
        this.download({
            url: baseURL + '/Documents/Doc/DownloadRev/'+ doc.id + '/' + rev + '/' +
                doc.project_id_code + '-' + doc.cidV + '-' + rev  + '-' +  doc.filename
        }); 
                    
    },
    
    
    exportCSV : function(c) {
        
        for(var i=0;i < c.csvFormat.length;i++) {
            c.params['csvCols['+i+']'] = c.csvFormat[i][0];
            c.params['csvTitles['+i+']'] = c.csvFormat[i][1];
        }
        c.url +=  '?' + Roo.urlEncode(c.params);
        this.download(c);

    },
    
    
    prettyDate : function (value) 
    {
        if (typeof(value) == 'string') {
            var ds = Date.parseDate(value, 'Y-m-d H:i:s');
            if (ds) {
                return this.prettyDate(ds);
            }
            ds = Date.parseDate(value, 'Y-m-d');
            if (ds) {
                return this.prettyDate(ds);
            }
            return '';
        }
// last 7 days...
        if (!value) {
            return '';
        }
        var td = new Date();
        var daysSince = Math.floor(td.getElapsed(value) / (1000 * 60*60*24));
        
        if (daysSince < 1) {
            return value.dateFormat('g:ia');
        }
        if (daysSince < 7) {
            return value.dateFormat('D g:ia');
        }
        
        // same month
        if (td.dateFormat('m') == value.dateFormat('m')) {
            return value.dateFormat('dS D');
        }
        // same year?
        if (td.dateFormat('Y') == value.dateFormat('Y')) {
            return value.dateFormat('dS M');
        }
        return value.dateFormat('d M Y');
    },
    loadException : function(a,b,c,d)
    {
        if (d && d.authFailure) {
            Pman.Login.show();
            return;
        }
        Roo.MessageBox.alert("Problem Loading Data", a.message || c.statusText);
    },
    
    
    /**
     * 
     * Routine to flash alerts in the title bar..
     * 
     * 
     */
    
    notifyActive : false,
    
    notifyTitle: function(msg)
    {
        if (this.notifyActive ) {
            return;
        }
        var stop = false;
        
        var stopper = function() {
            stop = true;
             document.title = oldtitle;
        };
        
        Roo.get(document.body).on('mousemove', stopper, this);
        var oldtitle = document.title;
        var s = 1;
        var _this = this;
        var ivl = window.setInterval(function() {
            
            if (stop) {
                Roo.get(document.body).un('mousemove', stopper, this);
                _this.notifyActive = false;
                document.title = oldtitle;
                window.clearInterval(ivl);
                return true;
            }
            s = !s;
            document.title = s ? msg : oldtitle;
            return false;     
        }, 1000); // every 120 secs = 2mins..
         document.title =   msg;
        
        
        
    },
    /**
     * @property {Array} appModules  - array based on AppModules global
     */
    appModules : false,
    
    modules : false,
    
    
    xregister : function(obj)
    {
        
        // work out owner..
        if (!Pman.appModules === false) {
            Pman.appModules = typeof(AppModules ) == 'undefined'? [] :
                AppModules.split(',');
        }
        
        
        
        // ignore registration of objects which are disabled.
        // global supplied by master.html
        appDisabled = typeof(appDisabled) == 'undefined' ? [] : appDisabled;
        
        
        /// design flaw
        // previously we did not a good naming policy for module and parts
        // most things that are called module here, really are 'parts'
        // new versions should have 'part' as [ module : part ]
         if (typeof(obj.part) != 'undefined')  {
           
            var permname = obj.part.join('.');
                // we now have permission...
                // obj.moduleOwner '.' lname
           
           
            if (appDisabled.indexOf(permname) > -1)  {
                Roo.log(permname + " is Disabled for this site");
                obj.disabled = true;
                return;
            }
            
            
        }
        
       
        
        if ( obj.isTop) {
            // false parent... use it..
            return;
        }
        
        
        if (obj.parent === Pman || obj.parent  == 'Pman') {
            // Roo.log("PARENT OF : " + obj.name + " replacing with fake");
            obj.parent = Pman.fakeRoot;
        }
        
        if (typeof(obj.parent) == 'undefined') {
            Roo.log("Parent is undefined");
            Roo.log(obj);
            obj.disabled = true;
            return;
        }
            
            
        if (obj.parent === false) {
            obj.disabled = true;
            Roo.log('ignoring top level object (as parent===false found)');
            Roo.log(obj);
            return;
        }
        // this is an error condition - the parent does not exist..
            // technically it should not happen..
          
        // hack for Pman parent == Pman..
        if (obj.parent == obj.module) {
            obj.parent = false;
            
        }
       
        
    },
    /**
     * fired before building on each compoenent
     * used to apply permissions.
     */
    
    xbeforebuild : function(obj)
    {
        if (typeof(obj.part) != 'undefined')  {
           
            if (!obj.part[1].length) {
                obj.part[1] = obj.part[0];
            }
            var permname = obj.part.join('.');
            
            Roo.log("CHECKING: "+ permname);
            
                // we now have permission...
                // obj.moduleOwner '.' lname
            
            if (Pman.hasPermExists(permname) && !Pman.hasPerm(permname,'S')) {
                // it's a turned off permission...
                Roo.log(permname + " is Disabled for this user");
                obj.disabled = true;
                return;
            }
            
            if (obj.permname && obj.permname.length && Pman.hasPermExists(obj.permname) && !Pman.hasPerm(obj.permname,'S')) {
                // it's a turned off permission...
                Roo.log(obj.permname + " is Disabled for this user");
                obj.disabled = true;
                return;
            }
            
        }
        
        
    },
    
    /**
     * DEPRICATED : use Roo.XComponents now..
     * 
     * Pman.register({
          modKey : '00-admin-xxxx',
          module : Pman.Tab.projectMgr, << really a components..
          part : [ 'Admin', 'ProjectManager' ]
          moduleOwner : 
          region : 'center',
          parent : Pman.layout
        })
     * 
     */
    register : function(obj)
    {
        
        //this.xregister(obj);
        
        
        // old style calls go in here..
        // we need to convert the object so that it looks a bit like an XCompoenent..
         
        obj.render = function()
        {
            if (!this.parent) {
                Roo.log("Skip module, as parent does not exist");
                Roo.log(this);
                return;
            }
            //if (typeof(mod) == 'function') {
            //    mod();
                
            if (typeof(this.region) == 'undefined') {
                Roo.log("Module does not have region defined, skipping");
                Roo.log(this);
                return;
            }
            if (this.module.disabled) {
                Roo.log("Module disabled, should not rendering");
                Roo.log(this);
                return;
            }
             
            if (!this.parent.layout) {
                Roo.log("Module parent does not have property layout.");
                Roo.log(this);
                return;
            }
        
           // honour DEPRICATED permname setings..
           // new code should use PART name, and matching permissions.
            if (this.permname && this.permname.length) {
                if (!Pman.hasPerm(this.permname, 'S')) {
                    return;
                }
                
            }
            this.add(this.parent.layout, this.region);
            this.el = this.layout;
            
            
              
        };
        // map some of the standard properties..
        obj.order = obj.modKey;
        
        // a bit risky...
        
        
        
        // the other issue we have is that
         
        
        // Roo.log("CALLING XComponent register with : " + obj.name);
        Roo.log(obj);
        // this will call xregister as it's the on.register handler..
        Roo.XComponent.register(obj.isTop ? obj : Roo.apply(obj.module, obj));
         
    } ,
    invertColor : function(c)
    {
        // read..
        var ca = [];
        for(var i = 0; i < 3; i++){
            ca[i] = parseInt(c.charAt((i*2)+1) + c.charAt((i*2)+2), 16);
        }
            
        // invert..
        var col = '';
        Roo.each(ca, function(hi) {
            var h = parseInt(255-hi).toString(16);
            if(h < 16){
                h = '0' + h;
            }
            col += h;
        });
        return '#' + col;
        
    }
    
    
    
    
    
    
    
});
    
