//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Pman.Dialog.CoreMailingListMessage = {

    dialog : false,
    callback:  false,

    show : function(data, cb)
    {
        if (!this.dialog) {
            this.create();
        }

        this.callback = cb;
        this.data = data;
        this.dialog.show(this.data._el);
        if (this.form) {
           this.form.reset();
           this.form.setValues(data);
           this.form.fireEvent('actioncomplete', this.form,  { type: 'setdata', data: data });
        }

    },

    create : function()
    {
        var _this = this;
        this.dialog = Roo.factory({
            xtype: 'LayoutDialog',
            xns: Roo,
            listeners : {
                show : function (_self)
                {
                    
                    _self.layout.getRegion('center').showPanel(0);
                    var w = Roo.lib.Dom.getViewWidth();
                    var h = Roo.lib.Dom.getViewHeight();        this.resizeTo(w-50, h-50);
                    this.center();    
                    var ew = Math.max(250, w-320);
                    var eh = Math.max(250, h-350) ;
                    var e = _this.dialog.layout.getRegion('east');
                    if (e.visible) {
                        e.hide();
                    }
                    
                    var el = _self.getEl();
                    var elw = el.dom.clientWidth;
                    
                    var bdtext = _this.form.findField('bodytext');
                    var ptext = _this.form.findField('plaintext');
                    if(bdtext.resizeEl){
                        bdtext.width = elw-100;
                        bdtext.resizeEl.resizeTo.defer(110, bdtext.resizeEl,[ bdtext.width, bdtext.height  ] );
                        ptext.setSize(bdtext.width , bdtext.height);
                    }
                    
                }
            },
            closable : true,
            collapsible : false,
            height : 500,
            modal : true,
            resizable : true,
            title : "Edit / Create Message",
            width : 800,
            items : [
                {
                    xtype: 'NestedLayoutPanel',
                    xns: Roo,
                    autoScroll : false,
                    fitContainer : true,
                    fitToFrame : true,
                    region : 'center',
                    toolbar : {
                        xtype: 'Toolbar',
                        xns: Roo,
                        items : [
                            {
                                xtype: 'Button',
                                xns: Roo.Toolbar,
                                text : "Import",
                                menu : {
                                    xtype: 'Menu',
                                    xns: Roo.menu,
                                    items : [
                                        {
                                            xtype: 'Item',
                                            xns: Roo.menu,
                                            listeners : {
                                                click : function (_self, e)
                                                {
                                                    Pman.Dialog.CoreImportUrl.show({
                                                        target : '/Core/ImportMailMessage.php'
                                                    }, function(data) {
                                                        if  (data) {
                                                          //  Roo.log(data);
                                                            _this.form.findField('bodytext').setValue(data);
                                                        }
                                                    });
                                                }
                                            },
                                            text : "URL"
                                        },
                                        {
                                            xtype: 'Item',
                                            xns: Roo.menu,
                                            listeners : {
                                                click : function (_self, e)
                                                {
                                                    Pman.Dialog.Image.show({
                                                        _url : baseURL + '/Core/ImportMailMessage.php'
                                                    }, function(data) {
                                                        if  (data) {
                                                            _this.form.findField('bodytext').setValue(data);
                                                        }
                                                    });
                                                }
                                            },
                                            text : "Html File"
                                        }
                                    ]
                                }
                            },
                            {
                                xtype: 'Button',
                                xns: Roo.Toolbar,
                                text : "Use template",
                                menu : {
                                    xtype: 'Menu',
                                    xns: Roo.menu,
                                    items : [
                                        {
                                            xtype: 'Item',
                                            xns: Roo.menu,
                                            listeners : {
                                                click : function (_self, e)
                                                {
                                                
                                                    var l = document.location;
                                                    new Pman.Request({
                                                
                                                        url : baseURL + '/Core/ImportMailMessage.php',
                                                
                                                        method: 'POST',
                                                        mask : "Loading",
                                                        params : {
                                                              importUrl : l.protocol +'//' + l.host +   rootURL + '/Pman/Crm/mail_templates/responsive1.html',
                                                       },
                                                        success : function (res) {
                                                
                                                         _this.form.findField('bodytext').setValue(res.data);
                                                        }
                                                  
                                                    });
                                                }
                                            },
                                            text : "Responsive Email (1)"
                                        }
                                    ]
                                }
                            },
                            {
                                xtype: 'ComboBox',
                                xns: Roo.form,
                                listeners : {
                                    select : function (combo, record, index)
                                    {
                                        Roo.log(record);
                                    /*
                                        (function() { 
                                            combo.setValue('');
                                        }).defer(100);
                                    */    
                                        _this.form.findField('bodytext').setValue(record.data.content);
                                    
                                    }
                                },
                                allowBlank : true,
                                alwaysQuery : true,
                                displayField : 'file',
                                editable : false,
                                emptyText : "Select Template",
                                fieldLabel : 'Template',
                                forceSelection : true,
                                hiddenName : 'template',
                                listWidth : 400,
                                loadingText : "Searching...",
                                minChars : 2,
                                name : 'template',
                                pageSize : 20,
                                qtip : "Select Template",
                                selectOnFocus : true,
                                tpl : '<div class="x-grid-cell-text x-btn button"><b>{file}</b> </div>',
                                triggerAction : 'all',
                                typeAhead : true,
                                valueField : 'file',
                                width : 200,
                                store : {
                                    xtype: 'Store',
                                    xns: Roo.data,
                                    listeners : {
                                        beforeload : function (_self, o){
                                            o.params = o.params || {};
                                            // set more here
                                           
                                        }
                                    },
                                    remoteSort : true,
                                    sortInfo : { direction : 'DESC', field: 'file' },
                                    proxy : {
                                        xtype: 'HttpProxy',
                                        xns: Roo.data,
                                        method : 'GET',
                                        url : baseURL + '/Core/MailTemplateList.php'
                                    },
                                    reader : {
                                        xtype: 'JsonReader',
                                        xns: Roo.data,
                                        id : 'name',
                                        root : 'data',
                                        totalProperty : 'total',
                                        fields : [{"name":"file","type":"string"},{"name":"content","type":"string"}]
                                    }
                                }
                            },
                            {
                                xtype: 'Fill',
                                xns: Roo.Toolbar
                            },
                            {
                                xtype: 'Button',
                                xns: Roo.Toolbar,
                                listeners : {
                                    click : function (_self, e)
                                    {
                                        var el = _this.dialog.layout.getRegion('east');
                                        if (el.visible) {
                                            el.hide();
                                        } else {
                                            el.show();
                                            el.showPanel(0);
                                        }
                                        
                                    }
                                },
                                text : "Images / Attachments >>"
                            }
                        ]
                    },
                    layout : {
                        xtype: 'BorderLayout',
                        xns: Roo,
                        items : [
                            {
                                xtype: 'ContentPanel',
                                xns: Roo,
                                listeners : {
                                    render : function (_self, width, height)
                                    {
                                        
                                          Roo.log("RESIZE, " + width + ',' + height);
                                        
                                        var ew = Math.max(250, width-50);
                                        var eh = Math.max(250,height-50) ;
                                        
                                       
                                    
                                    },
                                    resize : function (_self, width, height)
                                    {
                                       var ew = Math.max(250, width-50);
                                        var eh = Math.max(250,height-50) ;
                                        
                                        if (!_this.form) {
                                            return;
                                        }
                                        var bdtext = _this.form.findField('bodytext');
                                        var ptext = _this.form.findField('plaintext');
                                        if(bdtext.resizeEl){
                                            bdtext.width = ew-50;
                                            bdtext.resizeEl.resizeTo.defer(110, bdtext.resizeEl,[ bdtext.width, bdtext.height  ] );
                                            ptext.setSize(bdtext.width , bdtext.height);
                                        }
                                    
                                    }
                                },
                                autoScroll : false,
                                background : false,
                                fitContainer : true,
                                fitToFrame : true,
                                region : 'center',
                                title : "Message",
                                items : [
                                    {
                                        xtype: 'Form',
                                        xns: Roo.form,
                                        listeners : {
                                            actioncomplete : function(_self,action)
                                            {
                                               
                                                if (action.type == 'setdata') {
                                                
                                                    _this.data.module = _this.data.module || 'crm';
                                                    
                                                    _this.form.url = baseURL + '/Roo/' + _this.data.module + '_mailing_list_message.php';
                                                    
                                                    _this.html_preview.hide();
                                                    _this.preview_btn.hide();
                                                        
                                                    if(_this.data.id*1 > 0){
                                                        _this.dialog.el.mask("Loading");
                                                        this.load({ method: 'GET', params: { '_id' : _this.data.id }});
                                                        if(_this.data.module == 'crm'){
                                                            _this.preview_btn.show();
                                                            _this.html_preview.show();
                                                        }
                                                        
                                                    } else {
                                                        _this.form.setValues({
                                                            'from_name' : Pman.Login.authUser.name,
                                                            'from_email' : Pman.Login.authUser.email
                                                        });
                                                    }
                                                   return;
                                                }
                                                if (action.type == 'load') {
                                                    _this.dialog.el.unmask();
                                                    return;
                                                }
                                                if (action.type =='submit') {
                                                
                                                    _this.dialog.el.unmask();
                                                    _this.dialog.hide();
                                                
                                                     if (_this.callback) {
                                                        _this.callback.call(_this, action.result.data);
                                                     }
                                                     _this.form.reset();
                                                     return;
                                                }
                                            },
                                            rendered : function (form)
                                            {
                                                _this.form= form;
                                            }
                                        },
                                        labelAlign : 'right',
                                        labelWidth : 120,
                                        method : 'POST',
                                        style : 'margin:10px',
                                        preValidate : function(done_callback) {
                                            
                                            Roo.MessageBox.progress("Uploading Images", "Uploading");
                                            var html = _this.form.findField('bodytext').getValue();
                                            
                                            var s = Roo.get(_this.form.findField('bodytext').doc.documentElement);
                                            
                                            var ontable = (_this.data.module) ? _this.data.module + '_mailing_list_message' : 'crm_mailing_list_message';
                                            
                                            var nodes = [];
                                            s.select('img[src]').each(function(i) {
                                                nodes.push(i.dom);
                                            });
                                            var total = nodes.length;
                                            var mkimg = function() {
                                            
                                                if (!nodes.length) {
                                                      Roo.MessageBox.hide();
                                                      _this.form.findField('bodytext').syncValue();
                                                      done_callback(true);
                                                   //    _this.form.doAction("submit");
                                                      return;
                                                }
                                                var i = nodes.pop();        
                                                var n = i.getAttribute('src').match(/^http(.*)/);
                                                if(!n ){
                                                    mkimg();
                                                    return;
                                                }
                                                
                                                new Pman.Request({
                                                    url : baseURL + '/Roo/Images.php',
                                                    method : 'POST',
                                                    params : {
                                                        onid : _this.form.findField('id').getValue(),
                                                        ontable : ontable ,
                                                        _remote_upload : i.src
                                                    },
                                                    success : function(res){
                                                        if(res.success == true){      
                                                            i.setAttribute('src', res.data);
                                                            Roo.MessageBox.updateProgress( (total - nodes.length) / total , "Done " + (total - nodes.length) + '/' + total);
                                                        }
                                                        mkimg();
                                                    }
                                                });
                                               
                                            }
                                            mkimg();
                                        },
                                        url : baseURL + '/Roo/Core_mailing_list_message.php',
                                        items : [
                                            {
                                                xtype: 'Row',
                                                xns: Roo.form,
                                                items : [
                                                    {
                                                        xtype: 'TextField',
                                                        xns: Roo.form,
                                                        allowBlank : false,
                                                        fieldLabel : 'Mailout Name',
                                                        name : 'name',
                                                        width : 400
                                                    }
                                                ]
                                            },
                                            {
                                                xtype: 'Row',
                                                xns: Roo.form,
                                                items : [
                                                    {
                                                        xtype: 'TextField',
                                                        xns: Roo.form,
                                                        allowBlank : false,
                                                        fieldLabel : 'From',
                                                        name : 'from_name',
                                                        width : 300
                                                    },
                                                    {
                                                        xtype: 'TextField',
                                                        xns: Roo.form,
                                                        allowBlank : false,
                                                        fieldLabel : 'Email address',
                                                        name : 'from_email',
                                                        width : 300
                                                    }
                                                ]
                                            },
                                            {
                                                xtype: 'TextField',
                                                xns: Roo.form,
                                                allowBlank : false,
                                                fieldLabel : 'Subject',
                                                name : 'subject',
                                                width : 600
                                            },
                                            {
                                                xtype: 'Row',
                                                xns: Roo.form,
                                                hideLabels : true,
                                                items : [
                                                    {
                                                        xtype: 'FieldSet',
                                                        xns: Roo.form,
                                                        hideLabels : true,
                                                        legend : "Html Editor",
                                                        style : 'text-align:center;',
                                                        items : [
                                                            {
                                                                xtype: 'HtmlEditor',
                                                                xns: Roo.form,
                                                                height : 250,
                                                                name : 'bodytext',
                                                                resizable : 's',
                                                                cwhite : [ 
                                                                    'margin',
                                                                    'padding',
                                                                    'text-align',
                                                                    'background',
                                                                    'height',
                                                                    'width',
                                                                    'background-color',
                                                                    'font-size',
                                                                    'line-height',
                                                                    'color',
                                                                    'outline',
                                                                    'text-decoration',
                                                                    'position',
                                                                    'clear',
                                                                    'overflow',
                                                                    'margin-top',
                                                                    'border-bottom',
                                                                    'top',
                                                                    'list-style',
                                                                    'margin-left',
                                                                    'border',
                                                                    'float' ,
                                                                    'margin-right',
                                                                    'padding-top',
                                                                    'min-height',
                                                                    'left',
                                                                    'padding-left',
                                                                    'font-weight',
                                                                    'font-family',
                                                                    'display',
                                                                    'margin-bottom',
                                                                    'padding-bottom',
                                                                    'vertical-align',
                                                                    'cursor',
                                                                    'z-index',
                                                                    'right',
                                                                 ],
                                                                toolbars : [
                                                                    {
                                                                        xtype: 'ToolbarContext',
                                                                        xns: Roo.form.HtmlEditor
                                                                    },
                                                                    {
                                                                        xtype: 'ToolbarStandard',
                                                                        xns: Roo.form.HtmlEditor,
                                                                        btns : [
                                                                            {
                                                                                xtype: 'ComboBox',
                                                                                xns: Roo.form,
                                                                                listeners : {
                                                                                    render : function (_self)
                                                                                    {
                                                                                        _this.extendimgselect = _self;
                                                                                    },
                                                                                    select : function (combo, record, index)
                                                                                    {
                                                                                        Roo.log(record);
                                                                                        (function() { 
                                                                                            combo.setValue('');
                                                                                        }).defer(100);
                                                                                        var editor = _this.form.findField('bodytext');
                                                                                        editor.insertAtCursor(
                                                                                                String.format('<img src="{0}/Images/{1}/{2}#image-{1}">',
                                                                                                baseURL,  record.data.id, record.data.filename
                                                                                                )
                                                                                         );
                                                                                    
                                                                                        
                                                                                     },
                                                                                    beforequery : function (combo, query, forceAll, cancel, e)
                                                                                    {
                                                                                        var id = _this.form.findField('id').getValue() * 1;    
                                                                                        if (!id) {
                                                                                            Roo.MessageBox.alert("Error", "Save message first");
                                                                                            return false;
                                                                                        }
                                                                                    }
                                                                                },
                                                                                alwaysQuery : true,
                                                                                displayField : 'name',
                                                                                editable : false,
                                                                                emptyText : "Add Image",
                                                                                fieldLabel : 'Images',
                                                                                forceSelection : true,
                                                                                listWidth : 400,
                                                                                loadingText : "Searching...",
                                                                                minChars : 2,
                                                                                pageSize : 20,
                                                                                qtip : "Select Images",
                                                                                selectOnFocus : true,
                                                                                tpl : '<div class="x-grid-cell-text x-btn button"><img src="{public_baseURL}/Core/Images/Thumb/150x150/{id}.jpg" height="150" width="150"><b>{filename}</b> </div>',
                                                                                triggerAction : 'all',
                                                                                typeAhead : true,
                                                                                valueField : 'id',
                                                                                width : 100,
                                                                                store : {
                                                                                    xtype: 'Store',
                                                                                    xns: Roo.data,
                                                                                    listeners : {
                                                                                        beforeload : function (_self, o){
                                                                                            o.params = o.params || {};
                                                                                        
                                                                                            var id = _this.form.findField('id').getValue() * 1;    
                                                                                            if (!id) {
                                                                                                Roo.MessageBox.alert("Error", "Save email template first");
                                                                                                return false;
                                                                                            }
                                                                                            o.params.onid = id
                                                                                            o.params.ontable = 'core_mailing_list_message';
                                                                                            
                                                                                           // o.params.imgtype = 'PressRelease';
                                                                                            //o.params['query[imagesize]'] = '150x150';
                                                                                            // set more here
                                                                                        }
                                                                                    },
                                                                                    remoteSort : true,
                                                                                    sortInfo : { direction : 'ASC', field: 'id' },
                                                                                    proxy : {
                                                                                        xtype: 'HttpProxy',
                                                                                        xns: Roo.data,
                                                                                        method : 'GET',
                                                                                        url : baseURL + '/Roo/Images.php'
                                                                                    },
                                                                                    reader : {
                                                                                        xtype: 'JsonReader',
                                                                                        xns: Roo.data,
                                                                                        id : 'id',
                                                                                        root : 'data',
                                                                                        totalProperty : 'total',
                                                                                        fields : [{"name":"id","type":"int"},{"name":"filename","type":"string"},{"name":"url_thumb","type":"string"}]
                                                                                    }
                                                                                }
                                                                            },
                                                                            {
                                                                                xtype: 'ComboBox',
                                                                                xns: Roo.form,
                                                                                listeners : {
                                                                                    render : function (_self)
                                                                                    {
                                                                                        _this.unsubscribeselect = _self;
                                                                                    },
                                                                                    select : function (combo, record, index)
                                                                                    {
                                                                                        Roo.log(record);
                                                                                        (function() { 
                                                                                            combo.setValue('');
                                                                                        }).defer(100);
                                                                                        var editor = _this.form.findField('bodytext');
                                                                                        
                                                                                        if(record.data.name == 'Unsubscribe'){
                                                                                            editor.insertAtCursor(
                                                                                                String.format('<a href="{0}">{1}</a>',
                                                                                                    record.data.type,  record.data.name
                                                                                                )
                                                                                            );
                                                                                            return;     
                                                                                        }
                                                                                        
                                                                                        editor.insertAtCursor(
                                                                                            String.format('{0}',
                                                                                                record.data.type
                                                                                            )
                                                                                        );
                                                                                        
                                                                                     }
                                                                                },
                                                                                alwaysQuery : true,
                                                                                displayField : 'name',
                                                                                editable : false,
                                                                                emptyText : "Insert Field",
                                                                                fieldLabel : 'Field',
                                                                                forceSelection : true,
                                                                                listWidth : 400,
                                                                                loadingText : "Searching...",
                                                                                minChars : 2,
                                                                                pageSize : 20,
                                                                                qtip : "Insert Field",
                                                                                selectOnFocus : true,
                                                                                tpl : '<div class="x-grid-cell-text x-btn button"><b>{name}</b> </div>',
                                                                                triggerAction : 'all',
                                                                                typeAhead : true,
                                                                                valueField : 'type',
                                                                                width : 100,
                                                                                store : {
                                                                                    xtype: 'SimpleStore',
                                                                                    xns: Roo.data,
                                                                                    data : [ 
                                                                                        [ '{person.firstname}', "First Name"],
                                                                                        [ '{person.lastname}' , "Last Name"],
                                                                                        [ '{person.name}', "Full Name"],
                                                                                        [ '#unsubscribe', "Unsubscribe"]
                                                                                    ],
                                                                                    fields : [  'type', 'name']
                                                                                }
                                                                            }
                                                                        ]
                                                                    }
                                                                ]
                                                            }
                                                        ]
                                                    }
                                                ]
                                            },
                                            {
                                                xtype: 'Row',
                                                xns: Roo.form,
                                                hideLabels : true,
                                                items : [
                                                    {
                                                        xtype: 'Button',
                                                        xns: Roo,
                                                        listeners : {
                                                            click : function (_self, e)
                                                            {
                                                                var h = _this.form.findField('bodytext').getValue();
                                                                var p = _this.form.findField('plaintext');
                                                                
                                                                new Pman.Request({
                                                                    url : baseURL + '/Core/ImportMailMessage.php',
                                                                    method : 'POST',
                                                                    params : {
                                                                      bodytext : h,
                                                                      _convertToPlain : true,
                                                                      _check_unsubscribe : true
                                                                    }, 
                                                                    success : function(res) {
                                                                        if(res.success == true){
                                                                           p.setValue(res.data);
                                                                        }
                                                                    }
                                                                });  
                                                                
                                                            }
                                                        },
                                                        text : "Convert Html to Text"
                                                    }
                                                ]
                                            },
                                            {
                                                xtype: 'Row',
                                                xns: Roo.form,
                                                hideLabels : true,
                                                items : [
                                                    {
                                                        xtype: 'FieldSet',
                                                        xns: Roo.form,
                                                        hideLabels : true,
                                                        legend : "Plain Text",
                                                        style : 'text-align:center;',
                                                        items : [
                                                            {
                                                                xtype: 'TextArea',
                                                                xns: Roo.form,
                                                                height : 50,
                                                                name : 'plaintext'
                                                            }
                                                        ]
                                                    }
                                                ]
                                            },
                                            {
                                                xtype: 'Hidden',
                                                xns: Roo.form,
                                                name : 'id'
                                            }
                                        ]
                                    }
                                ]
                            }
                        ],
                        center : {
                            xtype: 'LayoutRegion',
                            xns: Roo,
                            autoScroll : true
                        }
                    }
                },
                {
                    xtype: 'GridPanel',
                    xns: Roo,
                    listeners : {
                        activate : function() {
                            _this.ipanel = this;
                            if (_this.igrid) {
                               _this.igrid.ds.load({});
                            }
                        }
                    },
                    autoScroll : false,
                    background : false,
                    fitContainer : true,
                    fitToframe : true,
                    region : 'east',
                    tableName : 'Images',
                    title : "Images / Attachments",
                    grid : {
                        xtype: 'Grid',
                        xns: Roo.grid,
                        listeners : {
                            render : function() 
                            {
                                _this.igrid = this; 
                                //_this.dialog = Pman.Dialog.FILL_IN
                                if (_this.ipanel.active) {
                               //    _this.igrid.ds.load({});
                                }
                            }
                        },
                        autoExpandColumn : 'filename',
                        loadMask : true,
                        dataSource : {
                            xtype: 'Store',
                            xns: Roo.data,
                            listeners : {
                                beforeload : function (_self, options)
                                {
                                    options.params = options.params || {};
                                    if (typeof(_this.data) == 'undefined') {
                                        return false;
                                    }
                                    if(_this.data.id * 1 >= 0)
                                    {
                                        options.params.onid = _this.data.id;
                                
                                        options.params.ontable = (_this.data.module) ? _this.data.module + '_mailing_list_message' : 'crm_mailing_list_message';
                                    }
                                }
                            },
                            remoteSort : true,
                            sortInfo : { field : 'filename', direction: 'ASC' },
                            proxy : {
                                xtype: 'HttpProxy',
                                xns: Roo.data,
                                method : 'GET',
                                url : baseURL + '/Roo/Images.php'
                            },
                            reader : {
                                xtype: 'JsonReader',
                                xns: Roo.data,
                                id : 'id',
                                root : 'data',
                                totalProperty : 'total',
                                fields : [
                                    {
                                        'name': 'id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'filename',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'ontable',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'onid',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'mimetype',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'width',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'height',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'filesize',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'displayorder',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'language',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'parent_image_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'created',
                                        'type': 'date',
                                        'dateFormat': 'Y-m-d'
                                    },
                                    {
                                        'name': 'imgtype',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'linkurl',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'descript',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'title',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'parent_image_id_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'parent_image_id_filename',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'parent_image_id_ontable',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'parent_image_id_onid',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'parent_image_id_mimetype',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'parent_image_id_width',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'parent_image_id_height',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'parent_image_id_filesize',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'parent_image_id_displayorder',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'parent_image_id_language',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'parent_image_id_parent_image_id',
                                        'type': 'int'
                                    },
                                    {
                                        'name': 'parent_image_id_created',
                                        'type': 'date'
                                    },
                                    {
                                        'name': 'parent_image_id_imgtype',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'parent_image_id_linkurl',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'parent_image_id_descript',
                                        'type': 'string'
                                    },
                                    {
                                        'name': 'parent_image_id_title',
                                        'type': 'string'
                                    }
                                ]
                            }
                        },
                        toolbar : {
                            xtype: 'Toolbar',
                            xns: Roo,
                            items : [
                                {
                                    xtype: 'Button',
                                    xns: Roo.Toolbar,
                                    listeners : {
                                        click : function()
                                        {
                                            var id = _this.form.findField('id').getValue();
                                            
                                            if(id*1 < 1){
                                                Roo.MessageBox.alert('Error', 'Please save the email template first');
                                                return;
                                            }
                                            
                                            var ontable = (_this.data.module) ? _this.data.module + '_mailing_list_message' : 'crm_mailing_list_message';
                                            
                                            Pman.Dialog.Image.show( { id : 0, onid: id, ontable: ontable }, function() {
                                                _this.igrid.getDataSource().load({});
                                            }); 
                                        }
                                    },
                                    cls : 'x-btn-text-icon',
                                    text : "Add",
                                    icon : Roo.rootURL + 'images/default/dd/drop-add.gif'
                                },
                                {
                                    xtype: 'Button',
                                    xns: Roo.Toolbar,
                                    listeners : {
                                        click : function()
                                        {
                                            var s = _this.igrid.getSelectionModel().getSelected();
                                            if (!s || isNaN(s.id *1)) {
                                                Roo.MessageBox.alert("Error", "Select a image"); 
                                                return;
                                            }
                                            Roo.MessageBox.confirm("Confirm", "Are sure you want to delete this image?", function (v){
                                                if (v != 'yes') {
                                                    return;
                                                }
                                                
                                                new Pman.Request({
                                                    url : baseURL + '/Roo/Images.php',
                                                    method: 'POST',
                                                    params : {
                                                        _delete : s.id
                                                    },
                                                    success : function()
                                                    {
                                                        Roo.log('Got Success!!');
                                                       _this.igrid.ds.load({});
                                                    }
                                                });
                                            });
                                        }
                                    },
                                    cls : 'x-btn-text-icon',
                                    text : "Delete",
                                    icon : rootURL + '/Pman/templates/images/trash.gif'
                                }
                            ]
                        },
                        colModel : [
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                dataIndex : 'filename',
                                header : 'Filename',
                                width : 300,
                                renderer : function(v,x,r)
                                {
                                   return '<img src="' + baseURL + '/Images/' + r.data.id + '/' + r.data.filename + '" width="' + r.data.width + '" height="' + r.data.height + '" />';
                                }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                header : 'Displayorder',
                                width : 75,
                                dataIndex : 'displayorder',
                                renderer : function(v) { return String.format('{0}', v); }
                            },
                            {
                                xtype: 'ColumnModel',
                                xns: Roo.grid,
                                dataIndex : 'title',
                                header : 'Title',
                                width : 75,
                                renderer : function(v) { return String.format('{0}', v); }
                            }
                        ]
                    }
                }
            ],
            center : {
                xtype: 'LayoutRegion',
                xns: Roo,
                tabPosition : 'top'
            },
            east : {
                xtype: 'LayoutRegion',
                xns: Roo,
                hidden : true,
                split : true,
                title : "Images / Attachments",
                titlebar : true,
                width : 500
            },
            buttons : [
                {
                    xtype: 'Button',
                    xns: Roo,
                    listeners : {
                        click : function (_self, e)
                        {
                            //_this.dialog.hide();
                            
                            Pman.Dialog.CoreMailingListMessagePreview.show({ id : _this.form.findField('id').getValue() });
                        },
                        render : function (_self)
                        {
                            _this.preview_btn = _self;
                        }
                    },
                    text : "Preview"
                },
                {
                    xtype: 'Button',
                    xns: Roo,
                    listeners : {
                        click : function (_self, e)
                        {
                            //_this.dialog.hide();
                            
                            var id = _this.form.findField('id').getValue();
                            
                            if(id*1 < 1){
                                Roo.MessageBox.alert('Error', 'Please save the message frist!');
                                return;
                            }
                           
                            new Pman.Request({
                                url : baseURL + '/Crm/MessagePreview',
                                method : 'POST',
                                mask: 'Sending',
                                params : {
                                    _id : id,
                                    _action : 'html'
                                }, 
                                success : function(res) { 
                                    if(res.data == 'SUCCESS'){
                                        Roo.MessageBox.alert("Email Sent", 'The report was sent to your email (HTML format).');
                                    }
                                }
                            });
                        },
                        render : function (_self)
                        {
                            _this.html_preview = _self;
                        }
                    },
                    text : "Send me a test copy"
                },
                {
                    xtype: 'Button',
                    xns: Roo,
                    listeners : {
                        click : function (_self, e)
                        {
                            _this.dialog.hide();
                        }
                    },
                    text : "Cancel"
                },
                {
                    xtype: 'Button',
                    xns: Roo,
                    listeners : {
                        click : function (_self, e)
                        {
                            // do some checks?
                            _this.form.preValidate(function(res) {
                                if (!res) {
                                    return; //failed.
                                }
                                 _this.form.doAction("submit");
                            });
                        
                        }
                    },
                    text : "Save"
                }
            ]
        });
    }
};
