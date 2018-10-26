//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.CoreProject = {

 _strings : {
  '99b344c8ae43e3e7213862b8f35c4e51' :"Select Company",
  '231bc72756b5e6de492aaaa1577f61b1' :"Remarks",
  '577d7068826de925ea2aec01dbadf5e4' :"Client",
  'ca528d836417871a349312db705a1951' :"Open date",
  'ddb016a244ff2b895e69d25fb3b5f780' :"Edit / Create Projects",
  'b5b20a9df20ea61c1cc0485f5e83891e' :"Select Project Type",
  '1243daf593fa297e07ab03bf06d925af' :"Searching...",
  'ea4788705e6873b424c65e91c2846b19' :"Cancel",
  '9675747b5ab12d05f18518761e68a533' :"Select Companies",
  'ac848fa228f49ba2b8a5fbd76596817d' :"Team",
  '24f9e53eb92c0995d04433c1f7a4c9c0' :"File location",
  'd1847fa47ea6bc047b413947463262ab' :"Enter Project Name",
  '340c2ee497b85d5954b01c64de7f44f6' :"Select Person",
  '1a11b1adc359c03db0ca798a00e2632c' :"Opened",
  '8e3a42158ee70b67cf55b33e2789a9e5' :"Project Name",
  'ca0dbad92a874b2f69b549293387925e' :"Code",
  '245fe794333c2b0d5c513129b346b93f' :"Project Type",
  'ab83ccde6764ca581702f38d79834615' :"Select Team",
  'c9cc8cce247e49bae79f15173ce97354' :"Save",
  '223aad18ce30620a724d8a97021ce26e' :"Open by"
 },
 _named_strings : {
  'name_qtip' : 'd1847fa47ea6bc047b413947463262ab' /* Enter Project Name */ ,
  'client_id_name_emptyText' : '99b344c8ae43e3e7213862b8f35c4e51' /* Select Company */ ,
  'client_id_name_fieldLabel' : '577d7068826de925ea2aec01dbadf5e4' /* Client */ ,
  'type_desc_emptyText' : 'b5b20a9df20ea61c1cc0485f5e83891e' /* Select Project Type */ ,
  'type_desc_fieldLabel' : '245fe794333c2b0d5c513129b346b93f' /* Project Type */ ,
  'type_desc_qtip' : 'b5b20a9df20ea61c1cc0485f5e83891e' /* Select Project Type */ ,
  'client_id_name_loadingText' : '1243daf593fa297e07ab03bf06d925af' /* Searching... */ ,
  'code_fieldLabel' : 'ca0dbad92a874b2f69b549293387925e' /* Code */ ,
  'open_by_name_qtip' : '340c2ee497b85d5954b01c64de7f44f6' /* Select Person */ ,
  'name_fieldLabel' : '8e3a42158ee70b67cf55b33e2789a9e5' /* Project Name */ ,
  'team_id_name_loadingText' : '1243daf593fa297e07ab03bf06d925af' /* Searching... */ ,
  'remarks_fieldLabel' : '231bc72756b5e6de492aaaa1577f61b1' /* Remarks */ ,
  'open_by_name_emptyText' : '340c2ee497b85d5954b01c64de7f44f6' /* Select Person */ ,
  'open_by_name_fieldLabel' : '223aad18ce30620a724d8a97021ce26e' /* Open by */ ,
  'team_id_name_fieldLabel' : 'ac848fa228f49ba2b8a5fbd76596817d' /* Team */ ,
  'open_date_fieldLabel' : 'ca528d836417871a349312db705a1951' /* Open date */ ,
  'team_id_name_qtip' : 'ab83ccde6764ca581702f38d79834615' /* Select Team */ ,
  'team_id_name_emptyText' : 'ab83ccde6764ca581702f38d79834615' /* Select Team */ ,
  'file_location_fieldLabel' : '24f9e53eb92c0995d04433c1f7a4c9c0' /* File location */ ,
  'open_by_name_loadingText' : '1243daf593fa297e07ab03bf06d925af' /* Searching... */ ,
  'type_desc_loadingText' : '1243daf593fa297e07ab03bf06d925af' /* Searching... */ ,
  'client_id_name_qtip' : '9675747b5ab12d05f18518761e68a533' /* Select Companies */ 
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
    xtype : 'LayoutDialog',
    closable : false,
    collapsible : false,
    height : 450,
    modal : true,
    resizable : false,
    title : _this._strings['ddb016a244ff2b895e69d25fb3b5f780'] /* Edit / Create Projects */,
    width : 470,
    xns : Roo,
    '|xns' : 'Roo',
    center : {
     xtype : 'LayoutRegion',
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
            // do some checks?
             
            
         
            _this.form.doAction("submit");
        
        }
      },
      xns : Roo,
      '|xns' : 'Roo'
     }
    ],
    items  : [
     {
      xtype : 'ContentPanel',
      region : 'center',
      xns : Roo,
      '|xns' : 'Roo',
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
                 //_this.dialog.el.mask("Loading");
                 if (_this.data.id) {
                     this.load({ method: 'GET', params: { '_id' : _this.data.id }});
                 }
                 return;
              }
              if (action.type == 'load') {
                  
                  return;
              }
              if (action.type =='submit') {
              
           
                  _this.dialog.hide();
              
                   if (_this.callback) {
                      _this.callback.call(_this, _this.form.getValues());
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
        xns : Roo.form,
        '|xns' : 'Roo.form',
        items  : [
         {
          xtype : 'TextField',
          fieldLabel : _this._strings['ca0dbad92a874b2f69b549293387925e'] /* Code */,
          name : 'code',
          width : 150,
          xns : Roo.form,
          '|xns' : 'Roo.form'
         },
         {
          xtype : 'TextField',
          fieldLabel : _this._strings['8e3a42158ee70b67cf55b33e2789a9e5'] /* Project Name */,
          name : 'name',
          qtip : _this._strings['d1847fa47ea6bc047b413947463262ab'] /* Enter Project Name */,
          width : 300,
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
          qtip : _this._strings['b5b20a9df20ea61c1cc0485f5e83891e'] /* Select Project Type */,
          queryParam : 'query[name]',
          selectOnFocus : true,
          tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{desc}</b> </div>',
          triggerAction : 'all',
          typeAhead : true,
          valueField : 'code',
          width : 200,
          xns : Roo.form,
          '|xns' : 'Roo.form',
          store : {
           xtype : 'SimpleStore',
           data : [ [  'U' , "Project (Unconfirmed)" ],
           [  'P' , "Project" ],
           [  'C' , "Project (Closed)" ],
           [  'N' , "Non-Project" ],
           [  'X' , "Non-Project (Closed)" ]
           ],
           fields : [ 'code', 'desc' ],
           xns : Roo.data,
           '|xns' : 'Roo.data'
          }
         },
         {
          xtype : 'ComboBox',
          allowBlank : false,
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
          qtip : _this._strings['9675747b5ab12d05f18518761e68a533'] /* Select Companies */,
          queryParam : 'query[name]',
          selectOnFocus : true,
          tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{name}</b> </div>',
          triggerAction : 'all',
          typeAhead : true,
          valueField : 'id',
          width : 300,
          listeners : {
           add : function (combo)
            {
             Pman.Dialog.Companies.show( {  id: 0 },  function(data) {
                        _this.form.setValues({ 
                            client_id : data.id,
                            client_id_name : data.name
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
            fields : [{"name":"id","type":"int"},{"name":"code","type":"string"}],
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
          allowBlank : false,
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
          qtip : _this._strings['ab83ccde6764ca581702f38d79834615'] /* Select Team */,
          queryParam : 'query[name]',
          selectOnFocus : true,
          tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{name}</b> </div>',
          triggerAction : 'all',
          typeAhead : true,
          valueField : 'id',
          width : 300,
          xns : Roo.form,
          '|xns' : 'Roo.form',
          store : {
           xtype : 'Store',
           remoteSort : true,
           sortInfo : { direction : 'ASC', field: 'id' },
           listeners : {
            beforeload : function (_self, o){
                 o.params = o.params || {};
                 // set more here
             }
           },
           xns : Roo.data,
           '|xns' : 'Roo.data',
           proxy : {
            xtype : 'HttpProxy',
            method : 'GET',
            url : baseURL + '/Roo/Groups.php',
            xns : Roo.data,
            '|xns' : 'Roo.data'
           },
           reader : {
            xtype : 'JsonReader',
            fields : [{"name":"id","type":"int"},{"name":"name","type":"string"}],
            id : 'id',
            root : 'data',
            totalProperty : 'total',
            xns : Roo.data,
            '|xns' : 'Roo.data'
           }
          }
         },
         {
          xtype : 'TextField',
          fieldLabel : _this._strings['24f9e53eb92c0995d04433c1f7a4c9c0'] /* File location */,
          name : 'file_location',
          width : 300,
          xns : Roo.form,
          '|xns' : 'Roo.form'
         },
         {
          xtype : 'TextArea',
          fieldLabel : _this._strings['231bc72756b5e6de492aaaa1577f61b1'] /* Remarks */,
          height : 100,
          name : 'remarks',
          width : 300,
          xns : Roo.form,
          '|xns' : 'Roo.form'
         },
         {
          xtype : 'FieldSet',
          legend : _this._strings['1a11b1adc359c03db0ca798a00e2632c'] /* Opened */,
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
            qtip : _this._strings['340c2ee497b85d5954b01c64de7f44f6'] /* Select Person */,
            queryParam : 'query[name]',
            selectOnFocus : true,
            tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{name}</b> </div>',
            triggerAction : 'all',
            typeAhead : true,
            valueField : 'id',
            width : 300,
            xns : Roo.form,
            '|xns' : 'Roo.form',
            store : {
             xtype : 'Store',
             remoteSort : true,
             sortInfo : { direction : 'ASC', field: 'id' },
             listeners : {
              beforeload : function (_self, o){
                   o.params = o.params || {};
                   // set more here
               }
             },
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
              fields : [{"name":"id","type":"int"},{"name":"name","type":"string"}],
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
       }
      ]
     }
    ]
   });
 }
};
