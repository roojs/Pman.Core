//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.ChooseFile = {

 _strings : {
  '6ab1321e1dea607035c8000a52002499' :"Displaying Files {0} - {1} of {2}",
  '13348442cc6a27032d2b4aa28b75a5d3' :"Search",
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
            var sel = _this.grid.getSelectionModel().getSelected();
            if(!sel) {
                Roo.MessageBox.alert('Error', 'Please select one file');
                return;
            }
            
            if (_this.callback) {
                _this.callback.call(_this, {
                    file: sel.data
                });
            }
            
            _this.dialog.hide();
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
            if(_this.grid) {
                _this.grid.footer.onClick('first');
            }
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
       toolbar : {
        xtype : 'Toolbar',
        xns : Roo,
        '|xns' : 'Roo',
        items  : [
         {
          xtype : 'TextField',
          emptyText : _this._strings['13348442cc6a27032d2b4aa28b75a5d3'] /* Search */,
          queryParam : 'query[search]',
          listeners : {
           render : function (_self)
            {
                _this.searchBox = _self;
            },
           specialkey : function (_self, e)
            {
                 if (e.getKey() == 13) {
                    _this.grid.footer.onClick('first');
                 }
            }
          },
          xns : Roo.form,
          '|xns' : 'Roo.form'
         },
         {
          xtype : 'Button',
          cls : 'x-btn-icon',
          icon : rootURL + '/Pman/templates/images/search.gif',
          listeners : {
           click : function (_self, e)
            {
                _this.grid.footer.onClick('first');
            }
          },
          xns : Roo.Toolbar,
          '|xns' : 'Roo.Toolbar'
         },
         {
          xtype : 'Button',
          cls : 'x-btn-icon',
          icon : rootURL + '/Pman/templates/images/edit-clear.gif',
          listeners : {
           click : function (_self, e)
            {
               _this.searchBox.setValue('');
              
                _this.grid.footer.onClick('first');
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
        sortInfo : { field : 'filename', direction: 'ASC' },
        listeners : {
         beforeload : function (_self, o)
          {
              o.params = o.params || {};
              o.params.onid = Pman.Login.authUser.id;
              o.params.ontable = 'core_person';
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
        }
       ]
      }
     }
    ]
   });
 }
};
