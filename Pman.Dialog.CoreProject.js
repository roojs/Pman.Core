//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.CoreProject = {

 _strings : {
  '2f26e35d61be90501e099089dc533638' :"Select Images",
  'f2a6c498fb90ee345d997f888fce3b18' :"Delete",
  'bc9e270b7b1a09eeac794d32b3d9dc8f' :"Display name",
  '189f63f277cd73395561651753563065' :"Tags",
  '231bc72756b5e6de492aaaa1577f61b1' :"Remarks",
  '1a11b1adc359c03db0ca798a00e2632c' :"Opened",
  'b5b20a9df20ea61c1cc0485f5e83891e' :"Select Project Type",
  'ec211f7c20af43e742bf2570c3cb84f9' :"Add",
  'ac848fa228f49ba2b8a5fbd76596817d' :"Team",
  '64f860745b1f9242aa562fb18111b6f2' :"Active on site",
  '1243daf593fa297e07ab03bf06d925af' :"Searching...",
  '577d7068826de925ea2aec01dbadf5e4' :"Client",
  'd41d8cd98f00b204e9800998ecf8427e' :"",
  'ab83ccde6764ca581702f38d79834615' :"Select Team",
  '245fe794333c2b0d5c513129b346b93f' :"Project Type",
  '340c2ee497b85d5954b01c64de7f44f6' :"Select Person",
  'b9c49611cfda3259a2b837b39489e650' :"Add Image",
  'ca0dbad92a874b2f69b549293387925e' :"Code",
  'ea4788705e6873b424c65e91c2846b19' :"Cancel",
  'ddb016a244ff2b895e69d25fb3b5f780' :"Edit / Create Projects",
  'ceab4b6639642ee9542291d28434c780' :"Select platform",
  '223aad18ce30620a724d8a97021ce26e' :"Open by",
  'c02bd0c22c290ae599ee2f3ff2023fd3' :"Manage Images / Attachments >>",
  'ca528d836417871a349312db705a1951' :"Open date",
  '3b878279a04dc47d60932cb294d96259' :"Overview",
  '8e3a42158ee70b67cf55b33e2789a9e5' :"Project Name",
  'bcf3f8775a521d487bf32f87529f9f28' :"Metadata (JSON)",
  '0c908588520b3ef787bce443fc2b507c' :"Slug",
  '99b344c8ae43e3e7213862b8f35c4e51' :"Select Company",
  'ed49a36c4fd547e5e2ace11bef4f21cf' :"Platforms",
  'ce21470ab49d1d1976bc3dc72438c183' :"Metadata",
  '308f2757bfc9ce92fb00ff93fdffd279' :"Images / Attachments",
  '1351017ac6423911223bc19a8cb7c653' :"Filename",
  'c9cc8cce247e49bae79f15173ce97354' :"Save",
  'bdde10910dfaa1303de9d1f5368bd9d8' :"Select tag",
  '3e371bc6ee85d7e4299a27b9d61e2c1e' :"Content management information",
  '3bd2771ecc7ec940003d82863c818cb4' :"Hero blurb",
  'fff0d600f8a0b5e19e88bfb821dd1157' :"Images"
 },
 _named_strings : {
  'client_id_name_emptyText' : '99b344c8ae43e3e7213862b8f35c4e51' /* Select Company */ ,
  'client_id_name_fieldLabel' : '577d7068826de925ea2aec01dbadf5e4' /* Client */ ,
  'type_desc_emptyText' : 'b5b20a9df20ea61c1cc0485f5e83891e' /* Select Project Type */ ,
  'type_desc_fieldLabel' : '245fe794333c2b0d5c513129b346b93f' /* Project Type */ ,
  'client_id_name_loadingText' : '1243daf593fa297e07ab03bf06d925af' /* Searching... */ ,
  'cms_active_boxLabel' : '64f860745b1f9242aa562fb18111b6f2' /* Active on site */ ,
  'code_fieldLabel' : 'ca0dbad92a874b2f69b549293387925e' /* Code */ ,
  'cms_hero_blurb_fieldLabel' : '3bd2771ecc7ec940003d82863c818cb4' /* Hero blurb */ ,
  'cms_platforms_name_fieldLabel' : 'ed49a36c4fd547e5e2ace11bef4f21cf' /* Platforms */ ,
  'cms_overview_html_fieldLabel' : '3b878279a04dc47d60932cb294d96259' /* Overview */ ,
  'name_fieldLabel' : '8e3a42158ee70b67cf55b33e2789a9e5' /* Project Name */ ,
  'team_id_name_loadingText' : '1243daf593fa297e07ab03bf06d925af' /* Searching... */ ,
  'remarks_fieldLabel' : '231bc72756b5e6de492aaaa1577f61b1' /* Remarks */ ,
  'cms_name_fieldLabel' : 'bc9e270b7b1a09eeac794d32b3d9dc8f' /* Display name */ ,
  'open_by_name_emptyText' : '340c2ee497b85d5954b01c64de7f44f6' /* Select Person */ ,
  'open_by_name_fieldLabel' : '223aad18ce30620a724d8a97021ce26e' /* Open by */ ,
  'team_id_name_fieldLabel' : 'ac848fa228f49ba2b8a5fbd76596817d' /* Team */ ,
  'open_date_fieldLabel' : 'ca528d836417871a349312db705a1951' /* Open date */ ,
  'cms_slug_fieldLabel' : '0c908588520b3ef787bce443fc2b507c' /* Slug */ ,
  'team_id_name_emptyText' : 'ab83ccde6764ca581702f38d79834615' /* Select Team */ ,
  'open_by_name_loadingText' : '1243daf593fa297e07ab03bf06d925af' /* Searching... */ ,
  'cms_meta_json_fieldLabel' : 'bcf3f8775a521d487bf32f87529f9f28' /* Metadata (JSON) */ ,
  'cms_tag_ids_name_fieldLabel' : '189f63f277cd73395561651753563065' /* Tags */ ,
  'type_desc_loadingText' : '1243daf593fa297e07ab03bf06d925af' /* Searching... */ 
 },

 dialog : false,
 callback:  false,

 show : function(data, cb)
 {
  if (!this.dialog) {
   this.create();
  }

  this.callback = cb;
  this.data = data;
  this.dialog.show.apply(this.dialog,  Array.prototype.slice.call(arguments).slice(2));
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
    xtype : 'LayoutDialog',
    background : true,
    closable : false,
    collapsible : false,
    height : 600,
    modal : true,
    resizable : false,
    setStylesheets : function() {
        if (!_this.hasCms) {
            return;
        }
        var sheets = [];
        sheets.push(rootURL + '/Pman/Cms/templates/undoreset.css');
        if (typeof(uiConfig) !== 'undefined' && typeof(uiConfig.cms_css) != 'undefined') {
            Roo.each(uiConfig.cms_css, function(v) {
                sheets.push(rootURL + v);
            });
        }
        new Pman.Request({
            url: baseURL + '/Roo/cms_page',
            method: 'GET',
            params: { _stylesheets: 1 },
            success: function(res) {
                if (res.data && res.data._stylesheets && res.data._stylesheets.length) {
                    Roo.each(res.data._stylesheets.split('\n'), function(s) {
                        sheets.push(s.replace('{rootURL}', rootURL));
                    });
                }
                if (!_this.form1) {
                    return;
                }
                var ed = _this.form1.findField('cms_overview_html');
                if (!ed) {
                    return;
                }
                if (ed.editorcore) {
                    ed.editorcore.bodyCls = 'roojscom r-project-body';
                }
                ed.removeStylesheets();
                ed.setStylesheets(sheets);
            }
        });
    },
    title : _this._strings['ddb016a244ff2b895e69d25fb3b5f780'] /* Edit / Create Projects */,
    width : 850,
    listeners : {
     show : function (_self)
      {
          _this.hasCms = (typeof(AppModules) != 'undefined') &&
              (',' + AppModules + ',').indexOf(',Cms,') > -1;
          if (!_this.hasCms) {
              if (_this.cmsColumn && _this.cmsColumn.el) {
                  _this.cmsColumn.el.setDisplayed(false);
              }
              if (_this.imagesBtn) {
                  _this.imagesBtn.hide();
              }
              try {
                  _this.centerLayoutPanel.getLayout().getRegion('south').hide(true);
              } catch (e) {}
              try {
                  _this.dialog.getLayout().getRegion('east').hide(true);
              } catch (e) {}
              this.resizeTo(470, 450);
              this.center();
              return;
          }
          if (_this.cmsColumn && _this.cmsColumn.el) {
              _this.cmsColumn.el.setDisplayed(true);
          }
          if (_this.imagesBtn) {
              _this.imagesBtn.show();
          }
          try {
              _this.centerLayoutPanel.getLayout().getRegion('south').show(true);
              _this.centerLayoutPanel.getLayout().getRegion('south').showPanel(0);
          } catch (e) {}
          var w = Roo.lib.Dom.getViewWidth();
          var h = Roo.lib.Dom.getViewHeight();
          this.resizeTo(w - 50, h - 50);
          this.center();
          _this.centerLayoutPanel.getLayout().getRegion('south').resizeTo(h - 450);
          _this.dialog.getLayout().getRegion('east').collapse();
      }
    },
    xns : Roo,
    '|xns' : 'Roo',
    center : {
     xtype : 'LayoutRegion',
     titlebar : false,
     xns : Roo,
     '|xns' : 'Roo'
    },
    east : {
     xtype : 'LayoutRegion',
     collapsed : true,
     collapsedTitle : 'Images / Attachments',
     collapsible : true,
     fitToFrame : true,
     split : true,
     title : _this._strings['308f2757bfc9ce92fb00ff93fdffd279'] /* Images / Attachments */,
     titlebar : true,
     width : 280,
     listeners : {
      expanded : function (_self)
       {
           var id = _this.form.findField('id').getValue() * 1;
           if (id < 1) {
               Roo.MessageBox.alert('Error', 'Save first');
               this.collapse();
               return;
           }
           var w = Roo.lib.Dom.getViewWidth();
           var h = Roo.lib.Dom.getViewHeight();
           _self.resizeTo.defer(110, _self, [ w - 400, h ]);
           if (_this.grid) {
               _this.grid.getDataSource().load();
           }
       }
     },
     xns : Roo,
     '|xns' : 'Roo'
    },
    buttons : [
     {
      xtype : 'Button',
      text : _this._strings['ea4788705e6873b424c65e91c2846b19'] /* Cancel */,
      listeners : {
       click : function (_self, e)
        {
            _this.dialog.hide();
        }
      },
      xns : Roo,
      '|xns' : 'Roo'
     },
     {
      xtype : 'Button',
      text : _this._strings['c9cc8cce247e49bae79f15173ce97354'] /* Save */,
      listeners : {
       click : function (_self, e)
        {
            _this.dialog.el.mask("Saving");
            if (_this.hasCms && _this.form1) {
                _this.form1.findField('cms_overview_html').syncValue();
            }
            _this.form.doAction("submit");
        }
      },
      xns : Roo,
      '|xns' : 'Roo'
     }
    ],
    items  : [
     {
      xtype : 'NestedLayoutPanel',
      background : false,
      fitContainer : true,
      fitToFrame : true,
      region : 'center',
      listeners : {
       render : function (_self)
        {
            _this.centerLayoutPanel = this;
        }
      },
      xns : Roo,
      '|xns' : 'Roo',
      layout : {
       xtype : 'BorderLayout',
       xns : Roo,
       '|xns' : 'Roo',
       center : {
        xtype : 'LayoutRegion',
        tabPosition : 'top',
        xns : Roo,
        '|xns' : 'Roo'
       },
       south : {
        xtype : 'LayoutRegion',
        height : 250,
        split : true,
        tabPosition : 'top',
        xns : Roo,
        '|xns' : 'Roo'
       },
       items  : [
        {
         xtype : 'ContentPanel',
         autoScroll : true,
         fitToFrame : true,
         region : 'center',
         listeners : {
          render : function (_self)
           {
               _this.cpanel = _self;
           },
          resize : function (_self, width, height)
           {
               var ew = Math.max(250, width - 10);
               var eh = _this.centerLayoutPanel.getLayout().getRegion('south').el.getHeight() - 30;
               if (!_this.form1) {
                   return;
               }
               var bd = _this.form1.findField('cms_overview_html');
               if (bd && bd.resizeEl) {
                   bd.width = ew;
                   bd.height = eh;
                   bd.resizeEl.resizeTo.defer(110, bd.resizeEl, [ bd.width, bd.height ]);
               }
               try {
                   _this.dialog.layout.el.dom.scrollTop = 0;
               } catch (e) {
               }
           }
         },
         xns : Roo,
         '|xns' : 'Roo',
         toolbar : {
          xtype : 'Toolbar',
          xns : Roo,
          '|xns' : 'Roo',
          items  : [
           {
            xtype : 'Fill',
            xns : Roo.Toolbar,
            '|xns' : 'Roo.Toolbar'
           },
           {
            xtype : 'Button',
            text : _this._strings['c02bd0c22c290ae599ee2f3ff2023fd3'] /* Manage Images / Attachments >> */,
            listeners : {
             click : function (_self, e)
              {
                  if (_this.panel.region.collapsed) {
                      _this.panel.region.expand();
                  } else {
                      _this.panel.region.collapse();
                  }
              },
             render : function (_self)
              {
                  _this.imagesBtn = _self;
              }
            },
            xns : Roo.Toolbar,
            '|xns' : 'Roo.Toolbar'
           }
          ]
         },
         items  : [
          {
           xtype : 'Form',
           method : 'POST',
           style : 'margin:10px;',
           url : baseURL + '/Roo/core_project',
           listeners : {
            actioncomplete : function(_self,action)
             {
                 if (action.type == 'setdata') {
                     if (_this.hasCms && _this.form1) {
                         this.addForm(_this.form1);
                         if (_this.metaForm) {
                             this.addForm(_this.metaForm);
                         }
                         _this.dialog.setStylesheets();
                     }
                     if (_this.data.id) {
                        _this.dialog.el.mask("Loading");
                        this.load({ method: 'GET', params: { '_id' : _this.data.id }});
                    } else {
                        if (_this.hasCms) {
                            this.findField('cms_active').setValue(1);
                        }
                        var od = new Date();
                        od.setDate(1);
                        this.findField('open_date').setValue(od);
                        if (typeof(Pman.Login.authUser) == 'object' && Pman.Login.authUser.id > 0) {
                            this.findField('open_by').setValue(Pman.Login.authUser.id);
                            this.findField('open_by_name').setValue(Pman.Login.authUser.name);
                        }
                    }
                    return;
                 }
                 if (action.type == 'load') {
                     _this.dialog.el.unmask();
                     var d = action.result.data;
                     if (_this.hasCms) {
                         this.findField('cms_platforms').setValue(Roo.decode(d.cms_platforms_list || '[]'));
                         var tags = Roo.decode(d.cms_tags_list || '[]');
                         Roo.each(tags, function(t) {
                             t.id = t.page_id;
                             t.title = t.page_id_title;
                         });
                         this.findField('cms_tag_ids').setValue(tags);
                         if (_this.metaForm) {
                             _this.metaForm.findField('cms_meta_json').setValue(d.cms_meta_json || '');
                         }
                         _this.dialog.setStylesheets();
                         if (_this.grid) {
                             _this.grid.getDataSource().load();
                         }
                     }
                     return;
                 }
                 if (action.type =='submit') {
                     _this.dialog.el.unmask();
                     _this.dialog.hide();
                      if (_this.callback) {
                         _this.callback.call(_this, _this.form.getValues());
                      }
                      _this.form.reset();
                      return;
                 }
             },
            actionfailed : function (_self, action)
             {
                 _this.dialog.el.unmask();
                 Pman.standardActionFailed(_self, action);
             },
            rendered : function (form)
             {
                 _this.form= form;
             }
           },
           xns : Roo.form,
           '|xns' : 'Roo.form',
           items  : [
            {
             xtype : 'Column',
             width : 480,
             xns : Roo.form,
             '|xns' : 'Roo.form',
             items  : [
              {
               xtype : 'TextField',
               fieldLabel : _this._strings['ca0dbad92a874b2f69b549293387925e'] /* Code */,
               name : 'code',
               width : 250,
               xns : Roo.form,
               '|xns' : 'Roo.form'
              },
              {
               xtype : 'TextField',
               fieldLabel : _this._strings['8e3a42158ee70b67cf55b33e2789a9e5'] /* Project Name */,
               name : 'name',
               width : 250,
               xns : Roo.form,
               '|xns' : 'Roo.form'
              },
              {
               xtype : 'ComboBox',
               allowBlank : false,
               displayField : 'desc',
               editable : false,
               emptyText : _this._strings['b5b20a9df20ea61c1cc0485f5e83891e'] /* Select Project Type */,
               fieldLabel : _this._strings['245fe794333c2b0d5c513129b346b93f'] /* Project Type */,
               forceSelection : true,
               hiddenName : 'type',
               listWidth : 400,
               loadingText : _this._strings['1243daf593fa297e07ab03bf06d925af'] /* Searching... */,
               minChars : 2,
               name : 'type_desc',
               pageSize : 20,
               queryParam : 'query[name]',
               selectOnFocus : true,
               tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{desc}</b> </div>',
               triggerAction : 'all',
               typeAhead : true,
               valueField : 'code',
               width : 250,
               xns : Roo.form,
               '|xns' : 'Roo.form',
               store : {
                xtype : 'SimpleStore',
                data : [ [ 'U' , "Project (Unconfirmed)" ], [ 'P' , "Project" ], [ 'C' , "Project (Closed)" ], [ 'N' , "Non-Project" ], [ 'X' , "Non-Project (Closed)" ] ],
                fields : [ 'code', 'desc' ],
                xns : Roo.data,
                '|xns' : 'Roo.data'
               }
              },
              {
               xtype : 'ComboBox',
               allowBlank : false,
               alwaysQuery : true,
               displayField : 'name',
               editable : false,
               emptyText : _this._strings['99b344c8ae43e3e7213862b8f35c4e51'] /* Select Company */,
               fieldLabel : _this._strings['577d7068826de925ea2aec01dbadf5e4'] /* Client */,
               forceSelection : true,
               hiddenName : 'client_id',
               listWidth : 400,
               loadingText : _this._strings['1243daf593fa297e07ab03bf06d925af'] /* Searching... */,
               minChars : 2,
               name : 'client_id_name',
               pageSize : 20,
               queryParam : 'query[name]',
               selectOnFocus : true,
               tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{name}</b> </div>',
               triggerAction : 'all',
               typeAhead : true,
               valueField : 'id',
               width : 250,
               listeners : {
                add : function (combo)
                 {
                     Pman.Dialog.Companies.show({ id: 0 }, function(data) {
                         _this.form.setValues({
                             client_id: data.id,
                             client_id_name: data.name
                         });
                     });
                 }
               },
               xns : Roo.form,
               '|xns' : 'Roo.form',
               store : {
                xtype : 'Store',
                remoteSort : true,
                sortInfo : { direction : 'ASC', field: 'name' },
                listeners : {
                 beforeload : function (_self, o){
                      o.params = o.params || {};
                      o.params.type = 1;
                      o.params['query[group_pulldown]'] = 1;
                  }
                },
                xns : Roo.data,
                '|xns' : 'Roo.data',
                proxy : {
                 xtype : 'HttpProxy',
                 method : 'GET',
                 url : baseURL + '/Roo/core_company',
                 xns : Roo.data,
                 '|xns' : 'Roo.data'
                },
                reader : {
                 xtype : 'JsonReader',
                 id : 'id',
                 root : 'data',
                 totalProperty : 'total',
                 xns : Roo.data,
                 '|xns' : 'Roo.data'
                }
               }
              },
              {
               xtype : 'ComboBox',
               allowBlank : true,
               alwaysQuery : true,
               displayField : 'name',
               editable : false,
               emptyText : _this._strings['ab83ccde6764ca581702f38d79834615'] /* Select Team */,
               fieldLabel : _this._strings['ac848fa228f49ba2b8a5fbd76596817d'] /* Team */,
               forceSelection : true,
               hiddenName : 'team_id',
               listWidth : 400,
               loadingText : _this._strings['1243daf593fa297e07ab03bf06d925af'] /* Searching... */,
               minChars : 2,
               name : 'team_id_name',
               pageSize : 20,
               queryParam : 'query[name]',
               selectOnFocus : true,
               tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{name}</b> </div>',
               triggerAction : 'all',
               typeAhead : true,
               valueField : 'id',
               width : 250,
               xns : Roo.form,
               '|xns' : 'Roo.form',
               store : {
                xtype : 'Store',
                remoteSort : true,
                sortInfo : { direction : 'ASC', field: 'id' },
                xns : Roo.data,
                '|xns' : 'Roo.data',
                proxy : {
                 xtype : 'HttpProxy',
                 method : 'GET',
                 url : baseURL + '/Roo/core_group',
                 xns : Roo.data,
                 '|xns' : 'Roo.data'
                },
                reader : {
                 xtype : 'JsonReader',
                 id : 'id',
                 root : 'data',
                 totalProperty : 'total',
                 xns : Roo.data,
                 '|xns' : 'Roo.data'
                }
               }
              },
              {
               xtype : 'TextArea',
               fieldLabel : _this._strings['231bc72756b5e6de492aaaa1577f61b1'] /* Remarks */,
               height : 40,
               name : 'remarks',
               width : 250,
               xns : Roo.form,
               '|xns' : 'Roo.form'
              },
              {
               xtype : 'FieldSet',
               legend : _this._strings['1a11b1adc359c03db0ca798a00e2632c'] /* Opened */,
               style : 'width:340px;',
               xns : Roo.form,
               '|xns' : 'Roo.form',
               items  : [
                {
                 xtype : 'DateField',
                 altFormats : 'Y-m-d|d/m/Y',
                 fieldLabel : _this._strings['ca528d836417871a349312db705a1951'] /* Open date */,
                 format : 'd/m/Y',
                 name : 'open_date',
                 width : 100,
                 xns : Roo.form,
                 '|xns' : 'Roo.form'
                },
                {
                 xtype : 'ComboBox',
                 allowBlank : false,
                 alwaysQuery : true,
                 displayField : 'name',
                 editable : false,
                 emptyText : _this._strings['340c2ee497b85d5954b01c64de7f44f6'] /* Select Person */,
                 fieldLabel : _this._strings['223aad18ce30620a724d8a97021ce26e'] /* Open by */,
                 forceSelection : true,
                 hiddenName : 'open_by',
                 listWidth : 400,
                 loadingText : _this._strings['1243daf593fa297e07ab03bf06d925af'] /* Searching... */,
                 minChars : 2,
                 name : 'open_by_name',
                 pageSize : 20,
                 queryParam : 'query[name]',
                 selectOnFocus : true,
                 tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{name}</b> </div>',
                 triggerAction : 'all',
                 typeAhead : true,
                 valueField : 'id',
                 width : 250,
                 xns : Roo.form,
                 '|xns' : 'Roo.form',
                 store : {
                  xtype : 'Store',
                  remoteSort : true,
                  sortInfo : { direction : 'ASC', field: 'id' },
                  xns : Roo.data,
                  '|xns' : 'Roo.data',
                  proxy : {
                   xtype : 'HttpProxy',
                   method : 'GET',
                   url : baseURL + '/Roo/core_person',
                   xns : Roo.data,
                   '|xns' : 'Roo.data'
                  },
                  reader : {
                   xtype : 'JsonReader',
                   id : 'id',
                   root : 'data',
                   totalProperty : 'total',
                   xns : Roo.data,
                   '|xns' : 'Roo.data'
                  }
                 }
                }
               ]
              },
              {
               xtype : 'Hidden',
               name : 'id',
               xns : Roo.form,
               '|xns' : 'Roo.form'
              }
             ]
            },
            {
             xtype : 'Column',
             width : 420,
             listeners : {
              render : function (_self)
               {
                   _this.cmsColumn = _self;
               }
             },
             xns : Roo.form,
             '|xns' : 'Roo.form',
             items  : [
              {
               xtype : 'FieldSet',
               legend : _this._strings['3e371bc6ee85d7e4299a27b9d61e2c1e'] /* Content management information */,
               xns : Roo.form,
               '|xns' : 'Roo.form',
               items  : [
                {
                 xtype : 'TextField',
                 fieldLabel : _this._strings['bc9e270b7b1a09eeac794d32b3d9dc8f'] /* Display name */,
                 name : 'cms_name',
                 width : 250,
                 xns : Roo.form,
                 '|xns' : 'Roo.form'
                },
                {
                 xtype : 'TextField',
                 fieldLabel : _this._strings['0c908588520b3ef787bce443fc2b507c'] /* Slug */,
                 name : 'cms_slug',
                 width : 250,
                 xns : Roo.form,
                 '|xns' : 'Roo.form'
                },
                {
                 xtype : 'Row',
                 hideLabels : true,
                 labelWidth : 0,
                 xns : Roo.form,
                 '|xns' : 'Roo.form',
                 items  : [
                  {
                   xtype : 'Checkbox',
                   boxLabel : _this._strings['64f860745b1f9242aa562fb18111b6f2'] /* Active on site */,
                   inputValue : 1,
                   name : 'cms_active',
                   valueOff : 0,
                   width : 150,
                   xns : Roo.form,
                   '|xns' : 'Roo.form'
                  }
                 ]
                },
                {
                 xtype : 'TextArea',
                 fieldLabel : _this._strings['3bd2771ecc7ec940003d82863c818cb4'] /* Hero blurb */,
                 height : 40,
                 name : 'cms_hero_blurb',
                 width : 250,
                 xns : Roo.form,
                 '|xns' : 'Roo.form'
                },
                {
                 xtype : 'ComboBoxArray',
                 fieldLabel : _this._strings['ed49a36c4fd547e5e2ace11bef4f21cf'] /* Platforms */,
                 hiddenName : 'cms_platforms',
                 name : 'cms_platforms_name',
                 width : 250,
                 xns : Roo.form,
                 '|xns' : 'Roo.form',
                 combo : {
                  xtype : 'ComboBox',
                  allowBlank : true,
                  alwaysQuery : true,
                  displayField : 'display_name',
                  editable : true,
                  emptyText : _this._strings['ceab4b6639642ee9542291d28434c780'] /* Select platform */,
                  forceSelection : true,
                  listWidth : 250,
                  loadingText : _this._strings['1243daf593fa297e07ab03bf06d925af'] /* Searching... */,
                  minChars : 2,
                  queryParam : 'query[search]',
                  selectOnFocus : true,
                  tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{display_name}</b></div>',
                  triggerAction : 'all',
                  valueField : 'id',
                  width : 235,
                  listeners : {
                   add : function (_self)
                    {
                        var cba = _this.form.findField('cms_platforms');
                        Pman.Dialog.CoreEnum.show({
                            id: 0,
                            etype: 'cms_platform'
                        }, function(data) {
                            if (!data || !data.id) {
                                return;
                            }
                            cba.addItem(data);
                        });
                    }
                  },
                  xns : Roo.form,
                  '|xns' : 'Roo.form',
                  store : {
                   xtype : 'Store',
                   remoteSort : true,
                   sortInfo : { direction : 'ASC', field: 'seqid' },
                   listeners : {
                    beforeload : function (_self, o){
                         o.params = o.params || {};
                         o.params.etype = 'cms_platform';
                     }
                   },
                   xns : Roo.data,
                   '|xns' : 'Roo.data',
                   proxy : {
                    xtype : 'HttpProxy',
                    method : 'GET',
                    url : baseURL + '/Roo/core_enum',
                    xns : Roo.data,
                    '|xns' : 'Roo.data'
                   },
                   reader : {
                    xtype : 'JsonReader',
                    id : 'id',
                    root : 'data',
                    totalProperty : 'total',
                    xns : Roo.data,
                    '|xns' : 'Roo.data'
                   }
                  }
                 }
                },
                {
                 xtype : 'ComboBoxArray',
                 fieldLabel : _this._strings['189f63f277cd73395561651753563065'] /* Tags */,
                 hiddenName : 'cms_tag_ids',
                 name : 'cms_tag_ids_name',
                 width : 250,
                 xns : Roo.form,
                 '|xns' : 'Roo.form',
                 combo : {
                  xtype : 'ComboBox',
                  allowBlank : true,
                  alwaysQuery : true,
                  displayField : 'title',
                  editable : true,
                  emptyText : _this._strings['bdde10910dfaa1303de9d1f5368bd9d8'] /* Select tag */,
                  forceSelection : true,
                  listWidth : 400,
                  loadingText : _this._strings['1243daf593fa297e07ab03bf06d925af'] /* Searching... */,
                  minChars : 2,
                  pageSize : 40,
                  queryParam : 'search[name]',
                  selectOnFocus : true,
                  tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{title}</b> <i>{page_link}</i></div>',
                  triggerAction : 'all',
                  valueField : 'id',
                  width : 235,
                  xns : Roo.form,
                  '|xns' : 'Roo.form',
                  store : {
                   xtype : 'Store',
                   remoteSort : true,
                   sortInfo : { field: 'title', direction: 'ASC' },
                   listeners : {
                    beforeload : function (_self, o){
                         o.params = o.params || {};
                         o.params['search[page_link_no_empty]'] = 1;
                         o.params._page_type = 'category';
                     }
                   },
                   xns : Roo.data,
                   '|xns' : 'Roo.data',
                   proxy : {
                    xtype : 'HttpProxy',
                    method : 'GET',
                    url : baseURL + '/Roo/cms_page',
                    xns : Roo.data,
                    '|xns' : 'Roo.data'
                   },
                   reader : {
                    xtype : 'JsonReader',
                    id : 'id',
                    root : 'data',
                    totalProperty : 'total',
                    xns : Roo.data,
                    '|xns' : 'Roo.data'
                   }
                  }
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
         xtype : 'ContentPanel',
         background : false,
         fitContainer : true,
         fitToFrame : true,
         region : 'south',
         title : _this._strings['3b878279a04dc47d60932cb294d96259'] /* Overview */,
         xns : Roo,
         '|xns' : 'Roo',
         items  : [
          {
           xtype : 'Form',
           labelAlign : 'top',
           method : 'POST',
           url : baseURL + '/Roo/core_project',
           listeners : {
            rendered : function (form)
             {
                 _this.form1 = form;
             }
           },
           xns : Roo.form,
           '|xns' : 'Roo.form',
           items  : [
            {
             xtype : 'Row',
             hideLabels : true,
             xns : Roo.form,
             '|xns' : 'Roo.form',
             items  : [
              {
               xtype : 'HtmlEditor',
               bodyCls : 'roojscom r-project-body',
               fieldLabel : _this._strings['3b878279a04dc47d60932cb294d96259'] /* Overview */,
               height : 150,
               name : 'cms_overview_html',
               resizable : 's',
               white : [ 'iframe' ],
               width : 550,
               xns : Roo.form,
               '|xns' : 'Roo.form',
               toolbars : [
                {
                 xtype : 'ToolbarStandard',
                 xns : Roo.form.HtmlEditor,
                 '|xns' : 'Roo.form.HtmlEditor',
                 btns : [
                  {
                   xtype : 'ComboBox',
                   alwaysQuery : true,
                   displayField : 'name',
                   editable : false,
                   emptyText : _this._strings['b9c49611cfda3259a2b837b39489e650'] /* Add Image */,
                   fieldLabel : _this._strings['fff0d600f8a0b5e19e88bfb821dd1157'] /* Images */,
                   forceSelection : true,
                   listWidth : 400,
                   loadingText : _this._strings['1243daf593fa297e07ab03bf06d925af'] /* Searching... */,
                   minChars : 2,
                   pageSize : 20,
                   qtip : _this._strings['2f26e35d61be90501e099089dc533638'] /* Select Images */,
                   queryParam : 'search',
                   selectOnFocus : true,
                   tpl : '<div class=\"x-grid-cell-text x-btn button\"><img src=\"' + baseURL + '/Images/Thumb/150x150/{id}.jpg\" height=\"150\" width=\"150\"><br/><b>{filename}</b> </div>',
                   triggerAction : 'all',
                   typeAhead : true,
                   valueField : 'id',
                   width : 100,
                   listeners : {
                    beforequery : function (combo, query, forceAll, cancel, e)
                     {
                         var id = _this.form.findField('id').getValue() * 1;
                         if (!id) {
                             Roo.MessageBox.alert("Error", "Save first");
                             return false;
                         }
                     },
                    render : function (_self)
                     {
                         _this.bodyimgselect = _self;
                     },
                    select : function (combo, record, index)
                     {
                         (function() {
                             combo.setValue('');
                         }).defer(100);
                         var editor = _this.form1.findField('cms_overview_html').editorcore;
                         var sn = editor.getSelectedNode();
                         var bl = sn ? Roo.htmleditor.Block.factory(sn) : false;
                         var caption = (record.data.descript && record.data.descript.length) ? record.data.descript : record.data.filename;
                         var cfg = {
                             image_src : String.format('{0}/Images/{1}/{2}#image-{1}', baseURL, record.data.id, record.data.filename),
                             caption : caption
                         };
                         if (bl) {
                             Roo.apply(bl, cfg);
                             bl.updateElement(sn);
                             editor.owner.fireEvent('editorevent', editor, false);
                             return;
                         }
                         var fig = new Roo.htmleditor.BlockFigure(cfg);
                         editor.insertAtCursor(fig.toHTML());
                         editor.owner.fireEvent('editorevent', editor, false);
                     }
                   },
                   xns : Roo.form,
                   '|xns' : 'Roo.form',
                   store : {
                    xtype : 'Store',
                    remoteSort : true,
                    sortInfo : { direction : 'ASC', field: 'id' },
                    listeners : {
                     beforeload : function (_self, o){
                          o.params = o.params || {};
                          var id = _this.form.findField('id').getValue() * 1;
                          if (!id) {
                              Roo.MessageBox.alert("Error", "Save first");
                              return false;
                          }
                          o.params.onid = id;
                          o.params.ontable = 'core_project';
                          o.params['query[imagesize]'] = '150x150';
                      }
                    },
                    xns : Roo.data,
                    '|xns' : 'Roo.data',
                    proxy : {
                     xtype : 'HttpProxy',
                     method : 'GET',
                     url : baseURL + '/Roo/Images',
                     xns : Roo.data,
                     '|xns' : 'Roo.data'
                    },
                    reader : {
                     xtype : 'JsonReader',
                     id : 'id',
                     root : 'data',
                     totalProperty : 'total',
                     xns : Roo.data,
                     '|xns' : 'Roo.data'
                    }
                   }
                  }
                 ]
                },
                {
                 xtype : 'ToolbarContext',
                 xns : Roo.form.HtmlEditor,
                 '|xns' : 'Roo.form.HtmlEditor'
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
         xtype : 'ContentPanel',
         autoScroll : true,
         background : false,
         fitContainer : true,
         fitToFrame : true,
         region : 'south',
         title : _this._strings['ce21470ab49d1d1976bc3dc72438c183'] /* Metadata */,
         xns : Roo,
         '|xns' : 'Roo',
         items  : [
          {
           xtype : 'Form',
           labelAlign : 'top',
           method : 'POST',
           style : 'margin:10px;',
           url : baseURL + '/Roo/core_project',
           listeners : {
            rendered : function (form)
             {
                 _this.metaForm = form;
             }
           },
           xns : Roo.form,
           '|xns' : 'Roo.form',
           items  : [
            {
             xtype : 'TextArea',
             fieldLabel : _this._strings['bcf3f8775a521d487bf32f87529f9f28'] /* Metadata (JSON) */,
             height : 280,
             name : 'cms_meta_json',
             width : 550,
             listeners : {
              render : function (_self)
               {
                   _this.metaJsonBox = _self;
               }
             },
             xns : Roo.form,
             '|xns' : 'Roo.form'
            }
           ]
          }
         ]
        }
       ]
      }
     },
     {
      xtype : 'GridPanel',
      background : false,
      fitContainer : true,
      fitToframe : true,
      region : 'east',
      tableName : 'Images',
      title : _this._strings['308f2757bfc9ce92fb00ff93fdffd279'] /* Images / Attachments */,
      listeners : {
       activate : function() {
            _this.panel = this;
        }
      },
      xns : Roo,
      '|xns' : 'Roo',
      grid : {
       xtype : 'Grid',
       autoExpandColumn : 'filename',
       loadMask : true,
       listeners : {
        render : function() {
             _this.grid = this;
         },
        rowdblclick : function (_self, rowIndex, e)
         {
             var rd = _this.grid.getDataSource().getAt(rowIndex);
             var editor = _this.form1.findField('cms_overview_html').editorcore;
             var sn = editor.getSelectedNode();
             var bl = sn ? Roo.htmleditor.Block.factory(sn) : false;
             var caption = (rd.data.descript && rd.data.descript.length) ? rd.data.descript : rd.data.filename;
             var cfg = {
                 image_src : String.format('{0}/Images/{1}/{2}#image-{1}', baseURL, rd.data.id, rd.data.filename),
                 caption : caption
             };
             if (bl) {
                 Roo.apply(bl, cfg);
                 bl.updateElement(sn);
                 editor.owner.fireEvent('editorevent', editor, false);
                 return;
             }
             var fig = new Roo.htmleditor.BlockFigure(cfg);
             editor.insertAtCursor(fig.toHTML());
             editor.owner.fireEvent('editorevent', editor, false);
         }
       },
       xns : Roo.grid,
       '|xns' : 'Roo.grid',
       toolbar : {
        xtype : 'Toolbar',
        xns : Roo,
        '|xns' : 'Roo',
        items  : [
         {
          xtype : 'Button',
          cls : 'x-btn-text-icon',
          icon : Roo.rootURL + 'images/default/dd/drop-add.gif',
          text : _this._strings['ec211f7c20af43e742bf2570c3cb84f9'] /* Add */,
          listeners : {
           click : function()
            {
                var id = _this.form.findField('id').getValue() * 1;
                if (id < 1) {
                    Roo.MessageBox.alert("Error", "Save first");
                    return;
                }
                Pman.Dialog.Image.show( { id : 0, onid : id, ontable: 'core_project' }, function() {
                    _this.grid.getDataSource().load();
                });
            }
          },
          xns : Roo,
          '|xns' : 'Roo'
         },
         {
          xtype : 'Button',
          cls : 'x-btn-text-icon',
          icon : rootURL + '/Pman/templates/images/trash.gif',
          text : _this._strings['f2a6c498fb90ee345d997f888fce3b18'] /* Delete */,
          listeners : {
           click : function()
            {
                Pman.genericDelete(_this, _this.panel.tableName);
            }
          },
          xns : Roo,
          '|xns' : 'Roo'
         }
        ]
       },
       dataSource : {
        xtype : 'Store',
        listeners : {
         beforeload : function (_self, o)
          {
              var id = _this.form.findField('id').getValue() * 1;
              if (id < 1) {
                  this.removeAll();
                  return false;
              }
              o.params = o.params || {};
              o.params.limit = 9999;
              o.params.onid = id;
              o.params.ontable = 'core_project';
          }
        },
        xns : Roo.data,
        '|xns' : 'Roo.data',
        proxy : {
         xtype : 'HttpProxy',
         method : 'GET',
         url : baseURL + '/Roo/Images',
         xns : Roo.data,
         '|xns' : 'Roo.data'
        },
        reader : {
         xtype : 'JsonReader',
         id : 'id',
         root : 'data',
         totalProperty : 'total',
         xns : Roo.data,
         '|xns' : 'Roo.data'
        }
       },
       colModel : [
        {
         xtype : 'ColumnModel',
         dataIndex : 'id',
         header : _this._strings['d41d8cd98f00b204e9800998ecf8427e'] /*  */,
         renderer : function(v,x,r) { return String.format('<img src="{0}/Images/Thumb/100/{1}/{2}" height="100">', baseURL, v, r.data.filename); },
         sortable : false,
         width : 75,
         xns : Roo.grid,
         '|xns' : 'Roo.grid'
        },
        {
         xtype : 'ColumnModel',
         dataIndex : 'filename',
         header : _this._strings['1351017ac6423911223bc19a8cb7c653'] /* Filename */,
         renderer : function(v) { return String.format('{0}', v); },
         width : 140,
         xns : Roo.grid,
         '|xns' : 'Roo.grid'
        }
       ]
      }
     }
    ]
   });
 }
};
