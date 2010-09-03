//<script type="text/javascript">

/**
 * 
 * >>> Pman.layout.getRegion('center').tabs.stripWrap
 * ==> tab.???
 * var tbh = Pman.layout.getRegion('center').tabs.stripWrap.child('div').createChild(
 * 
 * {tag: 'div', style: 'display:block;position:absolute;top:2;left:300;width:100%;height:25px'});
 * 
 */
 
if (typeof(_T) == 'undefined') { _T={};}
 

  

Pman = new Roo.Document(
{
   /// appVersion: '1.7', // fixme = needs to be removed - use Global AppVersion
    subMenuItems : [],
    topMenuItems : [],
    rightNames: { }, /// register right names here - so they can be translated and rendered.
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
            if (Ext.get('loading')) {
                Ext.get('loading').remove();
            }
            
            Ext.state.Manager.setProvider(new Ext.state.CookieProvider());
            
            // link errors...
            
            if (AppLinkError.length) {
                Ext.MessageBox.alert("Error", AppLinkError, function() {
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
   

    
    layout: false,
    
    onload: function() {
        //this.fireEvent('beforeload',this);
        
        
        
        if (this.layout) {
            return; // already loaded
        } 
        if (Ext.get('loading')) {
            Ext.get('loading').remove();
        }
        if (Ext.get('loading-mask')) {
            Ext.get('loading-mask').show();
        }
        
        
       
        
        /*
        Ext.MessageBox.show({
           title: "Please wait...",
           msg: "Building Interface...",
           width:340,
           progress:true,
           closable:false
          
        });
        */
        //Pman.onLoadBuild();
        //Ext.get(document.body).mask("Building Interface");
        //Pman.onLoadBuild.defer(100, Pman);
       //Pman.onLoadBuild();
                    
   // },
    //onLoadBuild : function() {
        
        var _this = this;
        this.stime = new Date();
        this.layout = new Ext.BorderLayout(document.body, {
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
            } /*,
            south: {
                split:false,
                initialSize: 25,
                titlebar: false
            }
            */
        });
        
        this.fireEvent('beforeload',this);
        
        
        
        this.layout.beginUpdate();
        this.layout.add('north', new Ext.ContentPanel('title', 'North'));
        var au = Pman.Login.authUser;
        if (au.id > 0 && au.company_id_background_color.length) {
            Ext.get('title').dom.style.backgroundColor = '#' + au.company_id_background_color;
            Ext.get('headerInformation').dom.style.color = this.invertColor('#' + au.company_id_background_color);
        }
        if (au.id > 0 && au.company_id_logo_id * 1 > 0) {
            Ext.get('headerInformation-company-logo').dom.src =  baseURL + 
                '/Images/' + au.company_id_logo_id + '/' + au.company_id_logo_id_filename;
        } else {
            Ext.get('headerInformation-company-logo').dom.src = Roo.BLANK_IMAGE_URL;
        }
        
        Ext.get('headerInformation').dom.innerHTML = String.format(
                "You are Logged in as <b>{0} ({1})</b>", // to {4} v{3}", // for <b>{2}</b>",
                au.name, au.email, au.company_id_name, 
                AppVersion , appNameShort
        );
        
        
        document.title = appName + ' v' + AppVersion + ' - ' + au.company_id_name;
        Ext.QuickTips.init(); 
        if (Ext.isGecko) {
           Ext.useShims = true;
        }
       
        //this.mainLayout.beginUpdate();
        //var maskDom = Ext.get(document.body)._maskMsg.dom
        this.layout.beginUpdate();
        
        Pman.building = true;
        
        this.buildModules(this, 
            function() {
                
                _this.layout.getRegion('center').showPanel(0);
                _this.layout.endUpdate(); 
                _this.addTopToolbar();  
                _this.finalize();
                _this.fireEvent('load',this);
            }
        );
        
        
     
    },
    
    addTopToolbar : function()
    {
          //console.log( "t6:" + ((new Date())-stime));
        //this.mainLayout.endUpdate();
        // make a new tab to hold administration stuff...
        
       
        //console.log( "t7:" + ((new Date())-stime));
        var se = Pman.layout.getRegion('center').tabs.stripEl;
        var tbh = se.createChild( 
                { tag: 'td', style: 'width:100%;'  });
        
        var lotb = new Ext.Toolbar(tbh);
        
        if (Roo.isSafari) {
            var tbl = se.child('table', true);
            tbl.setAttribute('width', '100%');
        }
        lotb.add(
            new Ext.Toolbar.Fill(), 
     
            {
                text: "Change Password",
                cls: 'x-btn-text-icon',
                icon: rootURL + '/Pman/templates/images/change-password.gif',
                handler : function(){
                    Pman.PasswordChange.show({});
                }
            }, '-'
        );
         
        
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
            
            lotb.add(
                {
                     
                    text: "Add New Item",
                    cls: 'x-btn-text-icon',
                    icon: Ext.rootURL + 'images/default/dd/drop-add.gif',
                    menu : {
                        items : this.subMenuItems
                    }     
                } ,'-'
            );
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
            var e = e || window.event;
            var r = "Closing this window will loose changes, are you sure you want to do that?";

            // For IE and Firefox
            if (e) {
                e.returnValue = r;
            }

            // For Safari
            return r;
            
        };
        
        Ext.MessageBox.hide();
        if (Ext.get('loading-mask')) {
           Ext.get('loading-mask').remove();
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
            Ext.state.Manager.set('Pman.Login.username', data.email),
            window.onbeforeunload = false;
            document.location = baseURL + '?ts=' + Math.random();
        }
        
        var forceCompany = function(data) {
            if (Pman.Login.authUser.company_id * 1 > 0) {
                forceAdmin();
                return;
            }
            if (!data || !data.id) {
                Pman.Dialog.Companies.show( { id : 0, isOwner : 1, comptype: 'OWNER' }, function(data) {
                    forceCompany(data);
                });
                return;
            }
            Pman.Login.authUser.company_id  = data.id;
            Pman.Login.authUser.company_id_name  = data.name;
            forceAdmin();
        }
        
        if (Pman.Login.authUser.id < 0) {
            forceCompany();
            /// create account..
            
            
        }
        

    },
    
    
    
    
     
    onLoadTrack : function(id,cb) {
        this.onLoadTrackCall(id, cb, 'DocumentsCirc_');
    },
    onLoadTrackEdit : function(id,cb) {
        this.onLoadTrackCall(id, cb, 'Documents_');
    },
    
    
    /// ----------- FIXME -----
    
    
    onLoadTrackCall : function(id,cb, cls) {
        Ext.get(document.body).mask("Loading Document details");

        Pman.request({
            url: baseURL + '/Roo/Documents.html',  
            params: {
                _id: id
            },  
            method: 'GET',  
            success : function(data) {
                Ext.get(document.body).unmask();
             
                
                switch(data.in_out) {
                    case 'IN' : cls+='In';break;
                    case 'OUT' : cls+='Out';break;
                    case 'WIP' : cls+='Wip';break;
                    default: 
                        Ext.MessageBox.alert("Error", "invalid in_out");
                        return;
                }
                Pman.Dialog[cls].show(data, cb ? cb : Pman.refreshActivePanel);
            }, 
            
            failure: function() {
                Ext.get(document.body).unmask();
                //if (cb) {
                //    cb.call(false);
                //}
                 
           }
        });
          
    },
    
    /**
     * eg. has Pman.hasPerm('Admin.Admin_Tab', 'S') == showlist..
     * 
     */
    hasPerm: function(name, lvl) {
        if (typeof(Pman.Login.authUser) != 'object') {
            return false;
        }
        if (typeof(Pman.Login.authUser.perms[name]) != 'string') {
            return false;
        }
        return Pman.Login.authUser.perms[name].indexOf(lvl) > -1;
        
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
            res = Ext.decode(response.responseText);
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
    genericDelete : function(tab,tbl) {
        
        var r = [];
        
            
        var s = tab.grid.getSelectionModel().getSelections();
        if (!s.length)  {
            Ext.MessageBox.alert("Error", "Select at least one Row to delete" );
            return '';
        }
        
        for(var i = 0; i < s.length; i++) {
            r.push(s[i].data.id);
        }
    
        Ext.MessageBox.confirm("Confirm", "Are you sure you want to delete that?",
            function(btn) {
                if (btn != 'yes') {
                    return;
                }
                // what about the toolbar??
                tab.grid.getView().mainWrap.mask("Deleting");
                Pman.request({
                    url: baseURL + '/Roo/'+tbl+'.php',
                    method: 'GET',
                    params: {
                        _delete : r.join(',')
                    },
                    success: function(response) {
                        tab.grid.getView().mainWrap.unmask();
                        if ( tab.paging ) {
                            tab.paging.onClick('refresh');   
                        } else if (tab.refresh) {
                            tab.refresh();
                        } else if (tab.grid.footer && tab.grid.footer.onClick) {
                            // new xtype built grids
                            tab.grid.footer.onClick('refresh');   
                        } else {
                            tab.grid.getDataSource().load();
                        }
                        
                        
                        
                    },
                    failure: function(act) {
                        tab.grid.getView().mainWrap.unmask();
                        Ext.MessageBox.alert("Error", "Error Deleting");
                    }
                    
                });
            }
        );
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
    
    standardActionFailed :  function(f, act, cb) {
    
        if (act.failureType == 'client') {
            Ext.MessageBox.alert("Error", "Please Correct all the errors in red", cb);
            return;
        }
        if (act.failureType == 'connect') {
            Ext.MessageBox.alert("Error", "Problem Connecting to Server - please try again.", cb);
            return;
        }
        
        if (act.type == 'submit') {
            
            Ext.MessageBox.alert("Error", typeof(act.result.errorMsg) == 'string' ?
                String.format('{0}', act.result.errorMsg) : 
                "Saving failed = fix errors and try again", cb);
            return;
        }
        
        // what about load failing..
        Ext.MessageBox.alert("Error", "Error loading details",cb); 
    },
    /**
     * 
     * similar to Ext.Ajax, but handles our responses better...
     * c.url
     * c.method
     * c.params
     * c.failure() == failure function..
     * c.success(data) == success function..
     * 
     * 
     */
    request : function(c) {
        var r= new Roo.data.Connection({
            timeout : typeof(c.timeout) == 'undefined' ?  30000 c.timeout
        });
        r.request({
            url: c.url,
            method : c.method,
            params: c.params,
            xmlData : c.xmlData,
            success:  function(response, opts)  {  // check successfull...
               
                var res = Pman.processResponse(response);
                
                if (!res.success) { // error!
                    if (c.failure) {
                        if (true === c.failure.call(this,response, opts)) {
                            return;
                        }
                    }
                    Roo.MessageBox.hide();
                    Ext.MessageBox.alert("Error", res.errorMsg ? res.errorMsg : "Error Sending");
                    return;
                }
                
                c.success.call(this, res.data);
                
                return; 
            },
            failure :  function(response, opts)  {  // check successfull...
                
                if (c.failure) {
                    if (true === c.failure.call(this,response, opts)) {
                        return;
                    }
                }
                Roo.MessageBox.hide();
                Roo.MessageBox.alert("Error", "Connection timed out sending");
                console.log(response);
                
            },
            scope: this
            
        });
    },
    csvFrame : false,
    
    createCsvFrame: function()
    {
        
        if (this.csvFrame) {
            document.body.removeChild(this.csvFrame);
        }
            
        var id = Ext.id();
        this.csvFrame = document.createElement('iframe');
        this.csvFrame.id = id;
        this.csvFrame.name = id;
        this.csvFrame.className = 'x-hidden';
        if(Ext.isIE){
            this.csvFrame.src = Ext.SSL_SECURE_URL;
        }
        document.body.appendChild(this.csvFrame);

        if(Ext.isIE){
            document.frames[id].name = id;
        }
        
    },
    /**
     * download({
          url: 
         })
     * 
     * 
     */
    
    
    download : function(c) {
        
        if (c.newWindow) {
            // as ie seems buggy...
            window.open( c.url + '?' + Roo.urlEncode(c.params || {}), '_blank');
            return;
            
        }
        
        this.createCsvFrame();
        function cb(){
            var r = { responseText : "", responseXML : null };

            var frame = this.csvFrame;

            try { 
                var doc = Ext.isIE ? 
                    frame.contentWindow.document : 
                    (frame.contentDocument || window.frames[this.csvFrame.id].document);
                
                if(doc && doc.body && doc.body.innerHTML.length){
                  //  alert(doc.body.innerHTML);
                    Ext.MessageBox.alert("Error download",doc.body.innerHTML);
                }
                 
            }
            catch(e) {
            }

            Ext.EventManager.removeListener(frame, 'load', cb, this);
 
        }
        Ext.EventManager.on( this.csvFrame, 'load', cb, this);
        this.csvFrame.src = c.url;
    },
    downloadRevision : function(doc, rev)
    {
        this.download({
            url: baseURL + '/Documents/Doc/DownloadRev/'+ doc.id + '/' + rev + '/' +
                doc.project_id_code + '-' + doc.cidV + '-' + rev  + '-' +  doc.filename
        }); 
                    
    },
    exportCSV : function(c) {
        //this.createCsvFrame(); 
        for(var i=0;i < c.csvFormat.length;i++) {
            c.params['csvCols['+i+']'] = c.csvFormat[i][0];
            c.params['csvTitles['+i+']'] = c.csvFormat[i][1];
        }
        
        
        c.url +=  '?' + Ext.urlEncode(c.params);
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
        if (daysSince < 7) {
            return value.dateFormat('D H:i');
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
                  
        }, 1000); // every 120 secs = 2mins..
         document.title =   msg;
        
        
        
    },
    
    modules : false,
    /**
     * example:
     * 
     * Pman.register({
          modKey : '00-admin-xxxx',
          module : Pman.Tab.projectMgr,
          region : 'center',
          parent : Pman.layout
        })
     * 
     */
    register : function(obj) {
        if (!obj.parent) {
            if (obj.parent === false) {
                //console.log('skip module (no parent)' + obj.modkey);
                return;
            }
            
            console.log(obj);
        }
        if (!obj.parent.modules) {
            obj.parent.modules = new Roo.util.MixedCollection(false, function(o) { return o.modKey });
        }
        
        obj.parent.modules.add(obj);
        
    },
    
    buildModules : function(parent, onComplete) 
    {
        
        var _this = this;
        var cmp = function(a,b) {   
            return String(a).toUpperCase() > String(b).toUpperCase() ? 1 : -1;
            
        };
        if (!parent.modules) {
            return;
        }
        parent.modules.keySort('ASC',  cmp );
        var mods = [];
        
        
        // add modules to their parents..
        var addMod = function(m) {
           // console.log(m.modKey);
            
            mods.push(m);
            if (m.module.modules) {
                m.module.modules.keySort('ASC',  cmp );
                m.module.modules.each(addMod);
            }
            if (m.finalize) {
                m.finalize.name = m.name + " (clean up) ";
                mods.push(m.finalize);
            }
            
        }
 
        parent.modules.each(addMod);
        //this.allmods = mods;
        //console.log(mods);
        //return;
        if (!mods.length) {
            if (onComplete) onComplete();
            return;
        }
        // flash it up as modal - so we store the mask!?
        Ext.MessageBox.show({ title: 'loading' });
        Ext.MessageBox.show({
           title: "Please wait...",
           msg: "Building Interface...",
           width:450,
           progress:true,
           closable:false,
           modal: false
          
        });
        var n = 0;
        var progressRun = function() {
            
            var mod = mods[n];
            
            
            Ext.MessageBox.updateProgress(
                (n+1)/mods.length,  "Building Interface " + (n+1) + 
                    " of " + mods.length + 
                    (mod.name ? (' - ' + mod.name) : '')
                    );
            
            
            
            if (typeof(mod) == 'function') {
                mod();
                
            } else {
                if (mod.parent.layout && !mod.module.disabled) {
                    mod.module.add(mod.parent.layout, mod.region);    
                }
                
            }
            
            
            n++;
            if (n >= mods.length) {
                onComplete();  
                return;
            }
                
            
            progressRun.defer(10, Pman);    
        }
        progressRun.defer(1, Pman);
     
        
        
    },
    
    gtranslate : function(str, src, dest, cb) {
        // load script: 
        
        
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
                    return o.responseData.translatedText;
                }
            }, 
            function (result) {
                cb(result);
            },
            this,
            []
        );
        
            
        
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
    
