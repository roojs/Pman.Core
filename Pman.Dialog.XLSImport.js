//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.XLSImport = {

 _strings : {
  '57f21c454b7a90784699cce8315fa4bf' :"1st Row",
  'f77f8c0e4a05a384a886554d76cbd6b1' :"Import XLS",
  'ec211f7c20af43e742bf2570c3cb84f9' :"Add",
  'b4daba1e7a3f227329f66e17180aebcc' :"Import into Mailing List:",
  '72d6d7a1885885bb55a565fd1070581a' :"Import",
  'f7aec8fa9a417536bfb549b4bbf83af0' :"Database col",
  '1243daf593fa297e07ab03bf06d925af' :"Searching...",
  'cfcd208495d565ef66e7dff9f98764da' :"0",
  '340c2ee497b85d5954b01c64de7f44f6' :"Select Person ",
  'ea4788705e6873b424c65e91c2846b19' :"Cancel",
  '81a5726cb8da16023374870e4f8282e0' :"Select a List ",
  '5578591ead1e76e5eca8723f992950e1' :"2st Row",
  '35c31ea9e29f774dba060916d184fe7d' :"Your Data",
  'dcce7ae3bed98022daa78cd837c7ac54' :"Select col"
 },
 _named_strings : {
  'name_qtip' : '340c2ee497b85d5954b01c64de7f44f6' /* Select Person  */ ,
  'name_emptyText' : '81a5726cb8da16023374870e4f8282e0' /* Select a List  */ ,
  'db_col_name_emptyText' : 'dcce7ae3bed98022daa78cd837c7ac54' /* Select col */ ,
  'db_col_name_value' : 'cfcd208495d565ef66e7dff9f98764da' /* 0 */ ,
  'name_loadingText' : '1243daf593fa297e07ab03bf06d925af' /* Searching... */ 
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
    closable : false,
    collapsible : false,
    height : 500,
    modal : true,
    resizable : true,
    title : _this._strings['f77f8c0e4a05a384a886554d76cbd6b1'] /* Import XLS */,
    width : 700,
    listeners : {
     show : function (_self)
      {
          Roo.log('IMPORT XLS SHOW');
          Roo.log(_this.data);
      }
    },
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
      text : _this._strings['72d6d7a1885885bb55a565fd1070581a'] /* Import */,
      listeners : {
       click : function (_self, e)
        {
            // do some checks?
            
            var setName = false;
            var setEmail = false;
            var setCompany = false;
            var setCompanyType = false;
            
             
            var rec = _this.grid.getDataSource().data.items;
            var data =  [] ; 
            Roo.each(rec, function(v,k){
                var a = {};
                a.header_index = k;
                a.db_col = (v.data.db_col) ? v.data.db_col : '';
                switch(a.db_col) {
                    case 'email' :
                        setEmail = true;
                        break;
                    case 'firstname' :
                    case 'lastname' :
                    case 'nametype1':
                    case 'nametype2':
                        setName = true;
                        break;
                    case 'company_name' :
                        setCompany = true;
                        break;
                    case 'company_type' :
                        setCompanyType = true;
                        break;
                }
                data.push(a);
            });
            
            if(!setName) {
                Roo.MessageBox.alert('Error', 'Please set First Name or Last Name');
                return;
            }
            
            if(!setEmail) {
                Roo.MessageBox.alert('Error', 'Please set Email');
                return;
            }
            if(!setCompany) {
                Roo.MessageBox.alert('Error', 'Please set Company');
                return;
            }
            if(!setCompanyType) {
                Roo.MessageBox.alert('Error', 'Please set Company Type');
                return;
            }
            Roo.log(data);
            
            if (!(1*  _this.mailing_list.getValue())) {
                Roo.MessageBox.alert('Error',
                 'Select a mailing list to add this to' + 
                 ' (its a good idea to create a new on specifically for your uploads)');
                return;
        
            } 
            
            new Pman.Request({
                method : 'POST',
                url : baseURL + '/Crm/Import/ImportAddress',
                mask : 'Uploading',
                timeout: 60000,
                params : { 
                    _id : _this.data._id,
                    _import: 1,
                    mailing_list_id : _this.mailing_list.getValue(),
                    data: Roo.encode(data)
                },
                success : function(res){
         
                    _this.dialog.hide();
                },
                failure : function(res)
                {
                    //Roo.log(res);
                    Roo.MessageBox.show( {
                        title: "Fix these issues, and try uploading again", 
                        prompt : true,
                        multiline : 500,
                        value : res.errorMsg,
                        buttons : Roo.MessageBox.OK 
                    });
                      
        
                    
                    return true;
                
                }
            });
            //_this.form.doAction("submit");
        
        }
      },
      xns : Roo,
      '|xns' : 'Roo'
     }
    ],
    items  : [
     {
      xtype : 'GridPanel',
      background : false,
      fitContainer : true,
      fitToframe : true,
      region : 'center',
      xns : Roo,
      '|xns' : 'Roo',
      grid : {
       xtype : 'EditorGrid',
       autoExpandColumn : 'db_col',
       clicksToEdit : 1,
       listeners : {
        render : function() 
         {
             _this.grid = this;
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
          xtype : 'TextItem',
          text : _this._strings['b4daba1e7a3f227329f66e17180aebcc'] /* Import into Mailing List: */,
          xns : Roo.Toolbar,
          '|xns' : 'Roo.Toolbar'
         },
         {
          xtype : 'ComboBox',
          allowBlank : true,
          alwaysQuery : true,
          displayField : 'name',
          editable : false,
          emptyText : _this._strings['81a5726cb8da16023374870e4f8282e0'] /* Select a List  */,
          forceSelection : true,
          listWidth : 250,
          loadingText : _this._strings['1243daf593fa297e07ab03bf06d925af'] /* Searching... */,
          minChars : 2,
          name : 'name',
          pageSize : 20,
          qtip : _this._strings['340c2ee497b85d5954b01c64de7f44f6'] /* Select Person  */,
          queryParam : 'query[name]',
          selectOnFocus : true,
          tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{name}</b></div>',
          triggerAction : 'all',
          typeAhead : true,
          valueField : 'id',
          width : 150,
          listeners : {
           render : function (_self)
            {
              _this.mailing_list = _self;
            }
          },
          xns : Roo.form,
          '|xns' : 'Roo.form',
          store : {
           xtype : 'Store',
           sortInfo : { field : 'name' , direction : 'ASC' },
           listeners : {
            beforeload : function (_self, o)
             {
                 o.params = o.params || {};
                 
             }
           },
           xns : Roo.data,
           '|xns' : 'Roo.data',
           proxy : {
            xtype : 'HttpProxy',
            method : 'GET',
            url : baseURL + '/Roo/Crm_mailing_list.php',
            xns : Roo.data,
            '|xns' : 'Roo.data'
           },
           reader : {
            xtype : 'JsonReader',
            fields : [
                {
                    'name': 'id',
                    'type': 'int'
                },
                {
                    'name': 'name',
                    'type': 'string'
                }
            ],
            id : 'id',
            root : 'data',
            totalProperty : 'total',
            xns : Roo.data,
            '|xns' : 'Roo.data'
           }
          }
         },
         {
          xtype : 'Button',
          text : _this._strings['ec211f7c20af43e742bf2570c3cb84f9'] /* Add */,
          listeners : {
           click : function (_self, e)
            {
                Pman.Dialog.CrmMailingList.show(
                    { id : 0,  owner_id : Pman.Login.authUserId, is_import : 1} ,
                    function(res) {
                      _this.mailing_list.setValue(res);
                   }
                ); 
            }
          },
          xns : Roo.Toolbar,
          '|xns' : 'Roo.Toolbar'
         }
        ]
       },
       dataSource : {
        xtype : 'Store',
        remoteSort : true,
        listeners : {
         beforeload : function (_self, o)
          {
              o.params = o.params || {};
              o.params._id = _this.data._id;
          //    this.proxy.loadResponse = this.loadResponse;
          }
        },
        xns : Roo.data,
        '|xns' : 'Roo.data',
        proxy : {
         xtype : 'HttpProxy',
         method : 'GET',
         url : baseURL + '/Crm/Import/ImportAddress.php',
         xns : Roo.data,
         '|xns' : 'Roo.data'
        },
        reader : {
         xtype : 'JsonReader',
         fields : [
             {
                 'name': 'header_name',
                 'type': 'string'
             },
             {
                 'name': 'db_col',
                 'type': 'string'
             },
             {
                 'name': 'db_col_name',
                 'type': 'string'
             },
             'row_1', 'row_2'
         ],
         id : 'id',
         root : 'data',
         totalProperty : 'total',
         xns : Roo.data,
         '|xns' : 'Roo.data'
        }
       },
       sm : {
        xtype : 'CellSelectionModel',
        enter_is_tab : true,
        xns : Roo.grid,
        '|xns' : 'Roo.grid'
       },
       colModel : [
        {
         xtype : 'ColumnModel',
         dataIndex : 'header_name',
         header : _this._strings['35c31ea9e29f774dba060916d184fe7d'] /* Your Data */,
         renderer : function(v,x,r)
         {
             return String.format('{0}', v);
         },
         width : 150,
         xns : Roo.grid,
         '|xns' : 'Roo.grid'
        },
        {
         xtype : 'ColumnModel',
         dataIndex : 'row_1',
         header : _this._strings['57f21c454b7a90784699cce8315fa4bf'] /* 1st Row */,
         renderer : function(v,x,r)
         {
             return String.format('{0}', v);
         },
         width : 150,
         xns : Roo.grid,
         '|xns' : 'Roo.grid'
        },
        {
         xtype : 'ColumnModel',
         dataIndex : 'row_2',
         header : _this._strings['5578591ead1e76e5eca8723f992950e1'] /* 2st Row */,
         renderer : function(v,x,r)
         {
             return String.format('{0}', v);
         },
         width : 150,
         xns : Roo.grid,
         '|xns' : 'Roo.grid'
        },
        {
         xtype : 'ColumnModel',
         dataIndex : 'db_col',
         header : _this._strings['f7aec8fa9a417536bfb549b4bbf83af0'] /* Database col */,
         renderer : function(v,r,x)
         {
             return String.format('{0}', (v) ? x.data.db_col_name : '');
         //    return String.format('{0}', v);
         },
         width : 150,
         xns : Roo.grid,
         '|xns' : 'Roo.grid',
         editor : {
          xtype : 'GridEditor',
          xns : Roo.grid,
          '|xns' : 'Roo.grid',
          field : {
           xtype : 'ComboBox',
           allowBlank : true,
           displayField : 'name',
           editable : false,
           emptyText : _this._strings['dcce7ae3bed98022daa78cd837c7ac54'] /* Select col */,
           hiddenName : 'db_col',
           mode : 'local',
           name : 'db_col_name',
           selectOnFocus : false,
           tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{name}</b> </div>',
           triggerAction : 'all',
           typeAhead : true,
           value : 0,
           valueField : 'col',
           width : 150,
           xns : Roo.form,
           '|xns' : 'Roo.form',
           store : {
            xtype : 'SimpleStore',
            data : [
                [ '0', "--not set--"],
                [ 'firstname', "First Name" ],
                [ 'lastname', "Last Name" ],
                [ 'phone_direct', "Phone Direct" ],
                [ 'phone_mobile', 'Mobile Phone' ],
                [ 'email', "Email" ],
                [ 'alt_email', "Secondary Email" ],
                [ 'fax', "Fax" ],
                [ 'nametype1', "name ('given name family name')" ],
                [ 'nametype2', "name ('family name given name')" ],
                [ 'company_name', "Company Name" ] ,
                [ 'lang', "Language Spoken" ],
                [ 'client_of_competitor', 'Client of Competitor'],
                [ 'company_type', 'Company Type'],
                [ 'role', 'Job Title'],
                [ 'client_industry', 'Client Industry'],
                [ 'company_website', "Website" ],
                [ 'office_country', 'Country' ]
            ],
            fields : [ {name: 'col', type: 'string'}, { name: 'name', type: 'string'} ],
            xns : Roo.data,
            '|xns' : 'Roo.data'
           }
          }
         }
        }
       ]
      }
     }
    ]
   });
 }
};