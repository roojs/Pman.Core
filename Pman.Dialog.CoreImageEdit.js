//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.CoreImageEdit = {

 _strings : {
  'e64df1d7c22b9638f084ce8a4aff3ff3' :"Target URL",
  'a9da081df4fba9b50b97fd23e6e6cb68' :"Image Preview",
  '1243daf593fa297e07ab03bf06d925af' :"Searching...",
  'ea4788705e6873b424c65e91c2846b19' :"Cancel",
  'f6295847b54e1e61e3daa57fa41ab74a' :"Name / description",
  '96ad0de89f69ffcba7e7caf33f8a0c06' :"Edit Image Details",
  'c3e2419b6452ebc5c84b0074ece90a02' :"Select Image type",
  'a1fa27779242b4902f7ae3bdd5c6d508' :"Type",
  'e0aa021e21dddbd6d8cecec71e9cf564' :"OK"
 },
 _named_strings : {
  'imgtype_fieldLabel' : 'a1fa27779242b4902f7ae3bdd5c6d508' /* Type */ ,
  'imgtype_qtip' : 'c3e2419b6452ebc5c84b0074ece90a02' /* Select Image type */ ,
  'title_fieldLabel' : 'f6295847b54e1e61e3daa57fa41ab74a' /* Name / description */ ,
  'filename_fieldLabel' : 'a9da081df4fba9b50b97fd23e6e6cb68' /* Image Preview */ ,
  'linkurl_fieldLabel' : 'e64df1d7c22b9638f084ce8a4aff3ff3' /* Target URL */ ,
  'imgtype_loadingText' : '1243daf593fa297e07ab03bf06d925af' /* Searching... */ ,
  'imgtype_emptyText' : 'c3e2419b6452ebc5c84b0074ece90a02' /* Select Image type */ 
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
    height : 320,
    modal : true,
    resizable : false,
    title : _this._strings['96ad0de89f69ffcba7e7caf33f8a0c06'] /* Edit Image Details */,
    width : 580,
    xns : Roo,
    '|xns' : 'Roo',
    center : {
     xtype : 'LayoutRegion',
     titlebar : false,
     xns : Roo,
     '|xns' : 'Roo'
    },
    buttons : [
     {
      xtype : 'Button',
      text : _this._strings['ea4788705e6873b424c65e91c2846b19'] /* Cancel */,
      listeners : {
       click : function()
        {
            _this.form.reset();
            _this.dialog.hide();
        }
      },
      xns : Roo,
      '|xns' : 'Roo'
     },
     {
      xtype : 'Button',
      text : _this._strings['e0aa021e21dddbd6d8cecec71e9cf564'] /* OK */,
      listeners : {
       click : function()
        {
            _this.dialog.el.mask('Saving');
            _this.form.doAction('submit');
        }
      },
      xns : Roo,
      '|xns' : 'Roo'
     }
    ],
    items  : [
     {
      xtype : 'ContentPanel',
      background : true,
      fitToFrame : true,
      region : 'center',
      xns : Roo,
      '|xns' : 'Roo',
      items  : [
       {
        xtype : 'Form',
        method : 'POST',
        style : 'margin: 5px',
        url : baseURL + '/Roo/Images',
        listeners : {
         actioncomplete : function (_s, action)
          {
              if (action.type == 'setdata') {
                  if (!_this.form.findField('imgtype').getValue()) {
                      _this.form.findField('imgtype').setValue('IMAGE');
                  }
                  return;
              }
              if (action.type == 'submit') {
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
         rendered : function (_self)
          {
              _this.form = _self;
          }
        },
        xns : Roo.form,
        '|xns' : 'Roo.form',
        items  : [
         {
          xtype : 'Column',
          labelAlign : 'top',
          width : 200,
          xns : Roo.form,
          '|xns' : 'Roo.form',
          items  : [
           {
            xtype : 'DisplayImage',
            fieldLabel : _this._strings['a9da081df4fba9b50b97fd23e6e6cb68'] /* Image Preview */,
            name : 'filename',
            readOnly : true,
            renderer : function(v)
            {
                var src = String.format('{0}/Images/{1}/{2}', baseURL, _this.data.id, _this.data.filename);
                if (_this.data.mimetype != 'image/svg+xml') {
                    src = String.format('{0}/Images/Thumb/150/{1}/{2}', baseURL, _this.data.id, _this.data.filename);
                }
                return String.format('<img src="{0}" width="150">', src);
            },
            width : 180,
            xns : Roo.form,
            '|xns' : 'Roo.form'
           }
          ]
         },
         {
          xtype : 'Column',
          labelAlign : 'top',
          width : 320,
          xns : Roo.form,
          '|xns' : 'Roo.form',
          items  : [
           {
            xtype : 'ComboBox',
            alwaysQuery : true,
            displayField : 'name',
            editable : false,
            emptyText : _this._strings['c3e2419b6452ebc5c84b0074ece90a02'] /* Select Image type */,
            fieldLabel : _this._strings['a1fa27779242b4902f7ae3bdd5c6d508'] /* Type */,
            forceSelection : true,
            listWidth : 300,
            loadingText : _this._strings['1243daf593fa297e07ab03bf06d925af'] /* Searching... */,
            minChars : 0,
            name : 'imgtype',
            pageSize : 20,
            qtip : _this._strings['c3e2419b6452ebc5c84b0074ece90a02'] /* Select Image type */,
            queryParam : 'query[name]',
            selectOnFocus : true,
            tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{name}</b></div>',
            triggerAction : 'all',
            typeAhead : true,
            valueField : 'name',
            width : 300,
            xns : Roo.form,
            '|xns' : 'Roo.form',
            store : {
             xtype : 'Store',
             remoteSort : true,
             sortInfo : { direction : 'ASC', field: 'name' },
             listeners : {
              beforeload : function (_self, o){
                   o.params = o.params || {};
                   o.params.etype = 'Image Types';
                   o.params.active = 1;
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
            xtype : 'TextArea',
            allowBlank : true,
            fieldLabel : _this._strings['f6295847b54e1e61e3daa57fa41ab74a'] /* Name / description */,
            height : 80,
            name : 'title',
            width : 300,
            xns : Roo.form,
            '|xns' : 'Roo.form'
           },
           {
            xtype : 'TextField',
            allowBlank : true,
            fieldLabel : _this._strings['e64df1d7c22b9638f084ce8a4aff3ff3'] /* Target URL */,
            name : 'linkurl',
            width : 300,
            xns : Roo.form,
            '|xns' : 'Roo.form'
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
