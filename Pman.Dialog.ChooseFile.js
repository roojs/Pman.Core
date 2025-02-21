//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.ChooseFile = {

 _strings : {
  '6ab1321e1dea607035c8000a52002499' :"Displaying Files {0} - {1} of {2}",
  '8807c97721e343cdc1fa2444cc00415b' :"Thumb",
  'be464219de4567dd548c3a6dfe9be5c6' :"No Files found",
  'ea4788705e6873b424c65e91c2846b19' :"Cancel",
  '91f3a2c0e4424c87689525da44c4db11' :"Files",
  '55c0c397d15c15f5a1ab5c0dec919ac0' :"Choose from existing files",
  '49ee3087348e8d44e1feda1917443987' :"Name",
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
    height : 480,
    modal : true,
    resizable : true,
    title : _this._strings['55c0c397d15c15f5a1ab5c0dec919ac0'] /* Choose from existing files */,
    width : 550,
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
      text : _this._strings['e0aa021e21dddbd6d8cecec71e9cf564'] /* OK */,
      listeners : {
       click : function (_self, e)
        {
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
      title : _this._strings['91f3a2c0e4424c87689525da44c4db11'] /* Files */,
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
        render : function() 
         {
             _this.grid = this;
             this.footer.onClick('first');
         }
       },
       xns : Roo.grid,
       '|xns' : 'Roo.grid',
       footer : {
        xtype : 'PagingToolbar',
        displayInfo : true,
        displayMsg : _this._strings['6ab1321e1dea607035c8000a52002499'] /* Displaying Files {0} - {1} of {2} */,
        emptyMsg : _this._strings['be464219de4567dd548c3a6dfe9be5c6'] /* No Files found */,
        pageSize : 25,
        xns : Roo,
        '|xns' : 'Roo'
       },
       dataSource : {
        xtype : 'Store',
        remoteSort : true,
        sortInfo : { field : 'filename', direction: 'ASC' },
        listeners : {
         beforeload : function (_self, o)
          {
              o.params = o.params || {};
              o.params.onid = Pman.Login.authUser.id;
              o.params.ontable = 'core_person';
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
       },
       sm : {
        xtype : 'RowSelectionModel',
        singleSelect : true,
        xns : Roo.grid,
        '|xns' : 'Roo.grid'
       },
       cm : [
        {
         xtype : 'ColumnModel',
         dataIndex : 'filename',
         header : _this._strings['49ee3087348e8d44e1feda1917443987'] /* Name */,
         renderer : function(v, x, r) {
             return String.format('{0}', v); 
         },
         xns : Roo.grid,
         '|xns' : 'Roo.grid'
        },
        {
         xtype : 'ColumnModel',
         dataIndex : 'url_thumb',
         header : _this._strings['8807c97721e343cdc1fa2444cc00415b'] /* Thumb */,
         renderer : function(v, x, r) { 
             return v ? "<img src='" + v + "' width=150 height=150>" : "";
         },
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
