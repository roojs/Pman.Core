//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.CoreNotifyRecurKeywords = {

 _strings : {
  '0ee0f676f631ad4e8a5844314a3a20de' :"Select campaign",
  '1243daf593fa297e07ab03bf06d925af' :"Searching...",
  'ea4788705e6873b424c65e91c2846b19' :"Cancel",
  'e0aa021e21dddbd6d8cecec71e9cf564' :"OK",
  '790f855c2139f2faecb810519e90b833' :"Add Notification Keywords",
  'ded4cba1b04eb8236e24a3e39470d8a7' :"Select Campaign"
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
    center : {
     '|xns' : 'Roo',
     titlebar : false,
     xns : Roo,
     xtype : 'LayoutRegion'
    },
    '|xns' : 'Roo',
    background : true,
    closable : false,
    collapsible : false,
    height : 180,
    modal : true,
    resizable : false,
    title : _this._strings['790f855c2139f2faecb810519e90b833'],
    width : 600,
    xns : Roo,
    xtype : 'LayoutDialog',
    buttons : [
      {
       '|xns' : 'Roo',
       text : _this._strings['ea4788705e6873b424c65e91c2846b19'],
       xns : Roo,
       xtype : 'Button',
       listeners : {
        click : function() {
             _this.form.reset();
             _this.dialog.hide();
         }
       }
      },
{
       '|xns' : 'Roo',
       text : _this._strings['e0aa021e21dddbd6d8cecec71e9cf564'],
       xns : Roo,
       xtype : 'Button',
       listeners : {
        click : function() {
             
             _this.form.doAction('submit');
             
         }
       }
      }
    ],
    listeners : {
     show : function (_self)
      {
          
      }
    },
    items : [
     {
      '|xns' : 'Roo',
      background : true,
      fitToFrame : true,
      region : 'center',
      xns : Roo,
      xtype : 'ContentPanel',
      items : [
       {
        '|xns' : 'Roo.form',
        method : 'POST',
        style : 'margin: 5px',
        url : baseURL + '/Roo/Core_notify_recur.php',
        xns : Roo.form,
        xtype : 'Form',
        listeners : {
         actioncomplete : function (_self, action)
          {
            if (action.type == 'setdata') {
                  
                  if(_this.data.id){
                      _this.dialog.el.mask("Loading");
                      this.load({ method: 'GET', params: { '_id' : _this.data.id }}); 
                  }
                  
                  return;
              }
              if (action.type == 'load') {
                  _this.dialog.el.unmask();
                  return;
              }
              if (action.type == 'submit' ) {
                  _this.dialog.el.unmask();
                  _this.dialog.hide();
          
                  if (_this.callback) {
                     _this.callback.call(_this, action.result.data);
                  }
                  _this.form.reset();
              }
          },
         rendered : function (form)
          {
             _this.form = form;
          }
        },
        items : [
         {
          store : {
           proxy : {
            '|xns' : 'Roo.data',
            method : 'GET',
            url : baseURL + '/Roo/Projects.php',
            xns : Roo.data,
            xtype : 'HttpProxy'
           },
           reader : {
            '|xns' : 'Roo.data',
            fields : [{"name":"id","type":"int"},{"name":"name","type":"string"}],
            id : 'id',
            root : 'data',
            totalProperty : 'total',
            xns : Roo.data,
            xtype : 'JsonReader'
           },
           '|xns' : 'Roo.data',
           remoteSort : true,
           sortInfo : { direction : 'DESC', field: 'id' },
           xns : Roo.data,
           xtype : 'Store',
           listeners : {
            beforeload : function (_self, o){
                 o.params = o.params || {};
                 
             }
           },
           items : [

           ]

          },
          '|xns' : 'Roo.form',
          allowBlank : false,
          alwaysQuery : true,
          displayField : 'name',
          editable : true,
          emptyText : _this._strings['ded4cba1b04eb8236e24a3e39470d8a7'],
          fieldLabel : 'Campaign',
          forceSelection : true,
          hiddenName : 'campaign_id',
          listWidth : 400,
          loadingText : _this._strings['1243daf593fa297e07ab03bf06d925af'],
          minChars : 2,
          name : 'camapign_id_name',
          pageSize : 20,
          qtip : _this._strings['0ee0f676f631ad4e8a5844314a3a20de'],
          queryParam : 'query[name]',
          selectOnFocus : true,
          tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{name}</b></div>',
          triggerAction : 'all',
          typeAhead : false,
          valueField : 'id',
          width : 400,
          xns : Roo.form,
          xtype : 'ComboBox',
          items : [

          ]

         },
         {
          combo : {
           store : {
            proxy : {
             '|xns' : 'Roo.data',
             method : 'GET',
             url : baseURL + '/Roo/clipping_keywords.php',
             xns : Roo.data,
             xtype : 'HttpProxy'
            },
            reader : {
             '|xns' : 'Roo.data',
             fields : [{"name":"id","type":"int"},{"name":"keyword","type":"string"}],
             id : 'code',
             root : 'data',
             totalProperty : 'total',
             xns : Roo.data,
             xtype : 'JsonReader'
            },
            '|xns' : 'Roo.data',
            remoteSort : true,
            sortInfo : { direction : 'ASC', field: 'display_name' },
            xns : Roo.data,
            xtype : 'Store',
            listeners : {
             beforeload : function (_self, o){
                  o.params = o.params || {};
                  
                  var s = _this.form.findField('campaign_id').getValue() * 1;
                  
                  if(isNaN(s) || s < 1){
                      return false;
                  }
                  
                  o.params.is_active  = 1;
                  o.params.is_keyword = 1;
                  o.params.project_id = s;
              }
            },
            items : [

            ]

           },
           '|xns' : 'Roo.form',
           allowBlank : true,
           alwaysQuery : true,
           displayField : 'keyword',
           editable : true,
           fieldLabel : 'Keyword',
           forceSelection : true,
           listWidth : 400,
           minChars : 2,
           queryParam : 'query[keyword]',
           tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{keyword}</b> </div>',
           triggerAction : 'all',
           valueField : 'id',
           width : 400,
           xns : Roo.form,
           xtype : 'ComboBox',
           items : [

           ]

          },
          '|xns' : 'Roo.form',
          allowBlank : false,
          fieldLabel : 'Keywords',
          hiddenName : 'keyword_filters',
          name : 'keyword_filters_name',
          width : 410,
          xns : Roo.form,
          xtype : 'ComboBoxArray',
          items : [

          ]

         },
         {
          '|xns' : 'Roo.form',
          name : 'id',
          xns : Roo.form,
          xtype : 'Hidden'
         }
        ]

       }
      ]

     }
    ]

   });
 }
};
