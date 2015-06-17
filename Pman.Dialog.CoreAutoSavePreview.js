//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.CoreAutoSavePreview = {

 _strings : {
  '53e5aa2c97fef1555d2511de8218c544' :"By",
  '87f9f735a1d36793ceaecd4e47124b63' :"Events",
  'ea4788705e6873b424c65e91c2846b19' :"Cancel",
  'bd88a20b53a47f7b5704a83a15ff5506' :"Saved Version",
  '44749712dbec183e983dcd78a7736c41' :"Date",
  'e0aa021e21dddbd6d8cecec71e9cf564' :"OK"
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
     xns : Roo,
     xtype : 'LayoutRegion'
    },
    west : {
     '|xns' : 'Roo',
     split : true,
     width : 200,
     xns : Roo,
     xtype : 'LayoutRegion'
    },
    '|xns' : 'Roo',
    background : false,
    closable : false,
    collapsible : false,
    height : 500,
    modal : true,
    resizable : false,
    title : _this._strings['bd88a20b53a47f7b5704a83a15ff5506'],
    width : 800,
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
         
             _this.dialog.hide();
             
             if (_this.callback && _this.source != '') {
                 _this.callback.call(this, _this.source);
             }
         }
       }
      }
    ],
    listeners : {
     show : function (_self)
      {
          if(typeof(_this.data) != 'undefined'){
              _this.grid.footer.onClick('first');
          }
          
      }
    },
    items : [
     {
      grid : {
       sm : {
        '|xns' : 'Roo.grid',
        singleSelect : true,
        xns : Roo.grid,
        xtype : 'RowSelectionModel',
        listeners : {
         afterselectionchange : function (_self)
          {
              var selected = this.getSelected();
              
              _this.source = '';
              
              if(!selected){
                 _this.viewPanel.setContent("Please select an saved version on the left"); 
                 return;
              }
              
              _this.viewPanel.load( { url : baseURL + "/Roo/Events", method : 'GET' }, {_id : selected.data.id, _retrieve_source : 1}, function(oElement, bSuccess, oResponse){
                  
                  var res = Roo.decode(oResponse.responseText);
                  
                  if(!bSuccess || !res.success){
                      _this.viewPanel.setContent("Load data failed?!");
                  }
                  
                  if(typeof(res.data) === 'string'){
                      _this.viewPanel.setContent(res.data);
                      return;
                  }
                  
                  if(!_this.data.successFn){
                      Roo.MessageBox.alert('Error', 'Please setup the successFn');
                      return;
                  }
                  
                  _this.source = _this.data.successFn(res);
          
                  _this.viewPanel.setContent(_this.source);
                  
              });
          }
        }
       },
       dataSource : {
        proxy : {
         '|xns' : 'Roo.data',
         method : 'GET',
         url : baseURL + '/Roo/Events.php',
         xns : Roo.data,
         xtype : 'HttpProxy'
        },
        reader : {
         '|xns' : 'Roo.data',
         fields : [
             {
                 'name': 'id',
                 'type': 'int'
             },
             {
                 'name': 'event_when',
                 'type': 'string'
             }
         ],
         id : 'id',
         root : 'data',
         totalProperty : 'total',
         xns : Roo.data,
         xtype : 'JsonReader'
        },
        '|xns' : 'Roo.data',
        remoteSort : true,
        sortInfo : { field : 'event_when', direction: 'DESC' },
        xns : Roo.data,
        xtype : 'Store',
        listeners : {
         beforeload : function (_self, o)
          {
              o.params = o.params || {};
              
              if(typeof(_this.data) == 'undefined'){
                  this.removeAll();
                  return false;
              }
          
              var d = Roo.apply({}, _this.data);
              delete d.successFn;
          
              Roo.apply(o.params, d);
              
          },
         load : function (_self, records, options)
          {
              var sm = _this.grid.getSelectionModel();
              if (!sm.getSelections().length) {
                  sm.selectFirstRow();
                  
                  sm.fireEvent('afterselectionchange', sm);
              }
          }
        },
        items : [

        ]

       },
       footer : {
        '|xns' : 'Roo',
        displayInfo : false,
        pageSize : 25,
        xns : Roo,
        xtype : 'PagingToolbar'
       },
       '|xns' : 'Roo.grid',
       autoExpandColumn : 'event_when',
       loadMask : true,
       xns : Roo.grid,
       xtype : 'Grid',
       colModel : [
         {
          '|xns' : 'Roo.grid',
          dataIndex : 'event_when',
          header : _this._strings['44749712dbec183e983dcd78a7736c41'],
          renderer : function(v) { return String.format('{0}', v ? v.format('Y-m-d H:i:s') : ''); },
          width : 100,
          xns : Roo.grid,
          xtype : 'ColumnModel'
         },
{
          '|xns' : 'Roo.grid',
          dataIndex : 'person_id_name',
          header : _this._strings['53e5aa2c97fef1555d2511de8218c544'],
          renderer : function(v) { return String.format('{0}', v ); },
          width : 100,
          xns : Roo.grid,
          xtype : 'ColumnModel'
         }
       ],
       listeners : {
        render : function() 
         {
             _this.grid = this; 
             
             if (_this.panel.active) {
                this.footer.onClick('first');
             }
         }
       },
       items : [

       ]

      },
      '|xns' : 'Roo',
      background : false,
      fitContainer : true,
      fitToframe : true,
      region : 'west',
      tableName : 'Events',
      title : _this._strings['87f9f735a1d36793ceaecd4e47124b63'],
      xns : Roo,
      xtype : 'GridPanel',
      listeners : {
       activate : function() {
            _this.panel = this;
            if (_this.grid) {
                _this.grid.footer.onClick('first');
            }
        }
      },
      items : [

      ]

     },
     {
      '|xns' : 'Roo',
      autoScroll : true,
      background : false,
      fitContainer : true,
      fitToFrame : true,
      region : 'center',
      xns : Roo,
      xtype : 'ContentPanel',
      listeners : {
       render : function (_self)
        {
            _this.viewPanel = _self;
        }
      }
     }
    ]

   });
 }
};
