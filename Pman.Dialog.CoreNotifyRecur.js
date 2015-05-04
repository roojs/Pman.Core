//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.CoreNotifyRecur = {

 _strings : {
  '3728af837fe70019577ddb0ed7125ee5' :"Until",
  'ec211f7c20af43e742bf2570c3cb84f9' :"Add",
  '023a5dfa857c4aa0156e6685231a1dbd' :"Select Type",
  '6c768695a8efb18436d5b7b4374cdb45' :"Select core_enum",
  '157e432ec303efd7d537b653cb255ccc' :"on day(s)",
  '236df51bb0e6416236e255b528346fca' :"Timezone",
  '44c68bed631ff6e62aecc4a4d32176e6' :"Select timezone",
  '867343577fa1f33caa632a19543bd252' :"Keywords",
  '1243daf593fa297e07ab03bf06d925af' :"Searching...",
  'b26686c0a708faee42861d8b905e882e' :"Last Sent",
  'c1d32776cd2d2afcd2c45a52f58679f4' :"Modify Recurrent Notifications",
  '5da618e8e4b89c66fe86e32cdafde142' :"From",
  'ce527d7432bab413730f25c794648e46' :"at Hour(s)",
  'c7179f16884513c990b6cbf44aef7fc3' :"core_notify_recur",
  'f2a6c498fb90ee345d997f888fce3b18' :"Delete",
  'a1fa27779242b4902f7ae3bdd5c6d508' :"Type",
  'f92965e2c8a7afb3c1b9a5c09a263636' :"Done"
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
     autoScroll : true,
     loadOnce : true,
     xns : Roo,
     xtype : 'LayoutRegion'
    },
    '|xns' : 'Roo',
    height : 550,
    modal : true,
    resizable : false,
    title : _this._strings['c1d32776cd2d2afcd2c45a52f58679f4'],
    width : 800,
    xns : Roo,
    xtype : 'LayoutDialog',
    buttons : [
      {
       '|xns' : 'Roo',
       text : _this._strings['f92965e2c8a7afb3c1b9a5c09a263636'],
       xns : Roo,
       xtype : 'Button',
       listeners : {
        click : function (_self, e)
         {
             _this.dialog.hide();
         }
       }
      }
    ],
    listeners : {
     show : function (_self)
      {
          _this.grid.ds.load({});
      }
    },
    items : [
     {
      grid : {
       dataSource : {
        proxy : {
         '|xns' : 'Roo.data',
         method : 'GET',
         url : baseURL + '/Roo/core_notify_recur.php',
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
                 'name': 'person_id',
                 'type': 'int'
             },
             {
                 'name': 'dtstart',
                 'type': 'date',
                 'dateFormat': 'Y-m-d'
             },
             {
                 'name': 'dtend',
                 'type': 'date',
                 'dateFormat': 'Y-m-d'
             },
             {
                 'name': 'tz',
                 'type': 'float'
             },
             {
                 'name': 'last_applied_dt',
                 'type': 'date',
                 'dateFormat': 'Y-m-d'
             },
             {
                 'name': 'freq',
                 'type': 'string'
             },
             {
                 'name': 'freq_day',
                 'type': 'string'
             },
             {
                 'name': 'freq_hour',
                 'type': 'string'
             },
             {
                 'name': 'last_event_id',
                 'type': 'int'
             },
             {
                 'name': 'method',
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
        sortInfo : { field : 'freq', direction: 'ASC' },
        xns : Roo.data,
        xtype : 'Store',
        listeners : {
         beforeload : function (_self, o)
          {
              if (!_this.data) {
                  return false;
              }
              o.params =  Roo.apply(o.params || {}, {
                  person_id : _this.data.person_id,
                  onid : _this.data.onid,
                  ontable : _this.data.ontable,
                  method : _this.data.method
              });
                  
          },
         update : function (_self, record, operation)
          {
              //Roo.log(operation);
              if (operation != 'commit') {
                  return;
              }
              var p = Roo.apply({}, record.data);
              p.dtstart = record.data.dtstart.format('Y-m-d');
              p.dtend = record.data.dtend.format('Y-m-d');    
              
              
              new Pman.Request({
                  url : baseURL + '/Roo/Core_notify_recur',
                  method :'POST',
                  params : p,
                  success : function(data)
                  {
                      //Roo.log(data);
                      record.set('id', data.data.id);
                  },
                  failure : function() {
                      Roo.MessageBox.alert("Error", "There was a problem saving");
                  }
              });
                 
              
              
          }
        },
        items : [

        ]

       },
       toolbar : {
        '|xns' : 'Roo',
        xns : Roo,
        xtype : 'Toolbar',
        items : [
         {
          '|xns' : 'Roo.Toolbar',
          cls : 'x-btn-text-icon',
          icon : Roo.rootURL + 'images/default/dd/drop-add.gif',
          text : _this._strings['ec211f7c20af43e742bf2570c3cb84f9'],
          xns : Roo.Toolbar,
          xtype : 'Button',
          listeners : {
           click : function()
            {
                var grid = _this.grid;
                var r = grid.getDataSource().reader.newRow({
                // defaults..
                    person_id : _this.data.person_id,
                    dtstart : new Date(),
                    dtend : Date.parseDate('2050-01-01', 'Y-m-d'),
                    tz : 'Asia/Hong_Kong',
                    onid : _this.data.onid,
                    ontable : _this.data.ontable,
                    method : _this.data.method, // default...
                    
                    method_id : _this.data.method_id, // default...
                    method_id_display_name : _this.data.method_id_display_name, // default...        
                    
                    last_event_id : 0,
                    freq_day_name : '',
                    freq_hour_name : '',
                    freq_name : ''
                    
                
                });
                grid.stopEditing();
                grid.getDataSource().insert(0, r); 
                grid.startEditing(0, 2); 
                
            
            }
          }
         },
         {
          '|xns' : 'Roo.Toolbar',
          xns : Roo.Toolbar,
          xtype : 'Fill'
         },
         {
          '|xns' : 'Roo.Toolbar',
          cls : 'x-btn-text-icon',
          icon : rootURL + '/Pman/templates/images/trash.gif',
          text : _this._strings['f2a6c498fb90ee345d997f888fce3b18'],
          xns : Roo.Toolbar,
          xtype : 'Button',
          listeners : {
           click : function()
            {
                 _this.grid.stopEditing();
                 var s = _this.grid.selModel.getSelectedCell();
                 if (!s) {
                    Roo.MessageBox.alert("Error", "Select row");
                    return;
                }
                
                new Pman.Request({
                    url : baseURL + '/Roo/core_notify_recur',
                    method : 'POST',
                    params : {
                        _delete : _this.grid.ds.getAt(s[0]).data.id
                    }, 
                    success : function() {
                        _this.grid.ds.load({});
                    },
                    failure : function() {
                        Roo.MessageBox.alert("Error", "Deleting failed - try reloading");
                    }
               });
                
            }
          }
         }
        ]

       },
       '|xns' : 'Roo.grid',
       autoExpandColumn : 'freq_day',
       clicksToEdit : 1,
       loadMask : true,
       xns : Roo.grid,
       xtype : 'EditorGrid',
       colModel : [
         {
          editor : {
           field : {
            store : {
             proxy : {
              '|xns' : 'Roo.data',
              method : 'GET',
              url : baseURL + '/Roo/core_enum.php',
              xns : Roo.data,
              xtype : 'HttpProxy'
             },
             reader : {
              '|xns' : 'Roo.data',
              fields : [{"name":"id","type":"int"},{"name":"etype","type":"string"}],
              id : 'id',
              root : 'data',
              totalProperty : 'total',
              xns : Roo.data,
              xtype : 'JsonReader'
             },
             '|xns' : 'Roo.data',
             remoteSort : true,
             sortInfo : { direction : 'ASC', field: 'id' },
             xns : Roo.data,
             xtype : 'Store',
             listeners : {
              beforeload : function (_self, o){
                   o.params = o.params || {};
                   // set more here
                   o.params.etype = 'core_notify_recur';
               }
             },
             items : [

             ]

            },
            '|xns' : 'Roo.form',
            allowBlank : false,
            displayField : 'display_name',
            editable : false,
            emptyText : _this._strings['023a5dfa857c4aa0156e6685231a1dbd'],
            fieldLabel : 'core_enum',
            forceSelection : true,
            hiddenName : 'method_id',
            listWidth : 400,
            loadingText : _this._strings['1243daf593fa297e07ab03bf06d925af'],
            name : 'method_id_display_name',
            pageSize : 20,
            qtip : _this._strings['6c768695a8efb18436d5b7b4374cdb45'],
            selectOnFocus : true,
            tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{display_name}</b> </div>',
            triggerAction : 'all',
            typeAhead : true,
            valueField : 'id',
            width : 300,
            xns : Roo.form,
            xtype : 'ComboBox',
            items : [

            ]

           },
           '|xns' : 'Roo.grid',
           xns : Roo.grid,
           xtype : 'GridEditor',
           items : [

           ]

          },
          '|xns' : 'Roo.grid',
          dataIndex : 'method_id',
          header : _this._strings['a1fa27779242b4902f7ae3bdd5c6d508'],
          renderer : function(v,x,r) {
               return String.format('{0}', r.data.method_id_display_name); 
          },
          width : 120,
          xns : Roo.grid,
          xtype : 'ColumnModel',
          items : [

          ]

         },
{
          editor : {
           field : {
            '|xns' : 'Roo.form',
            xns : Roo.form,
            xtype : 'DateField'
           },
           '|xns' : 'Roo.grid',
           xns : Roo.grid,
           xtype : 'GridEditor',
           items : [

           ]

          },
          '|xns' : 'Roo.grid',
          dataIndex : 'dtstart',
          header : _this._strings['5da618e8e4b89c66fe86e32cdafde142'],
          renderer : function(v) { return String.format('{0}', v ? v.format('d/M/Y') : ''); },
          width : 75,
          xns : Roo.grid,
          xtype : 'ColumnModel',
          items : [

          ]

         },
{
          editor : {
           field : {
            '|xns' : 'Roo.form',
            xns : Roo.form,
            xtype : 'DateField'
           },
           '|xns' : 'Roo.grid',
           xns : Roo.grid,
           xtype : 'GridEditor',
           items : [

           ]

          },
          '|xns' : 'Roo.grid',
          dataIndex : 'dtend',
          header : _this._strings['3728af837fe70019577ddb0ed7125ee5'],
          renderer : function(v) { return String.format('{0}', v ? v.format('d/M/Y') : ''); },
          width : 75,
          xns : Roo.grid,
          xtype : 'ColumnModel',
          items : [

          ]

         },
{
          editor : {
           field : {
            store : {
             '|xns' : 'Roo.data',
             data : (function() { 
                 var ret = [];
                 Roo.each(Date.dayNames, function(d) {
                     ret.push([ d.substring(0,3).toUpperCase(), d ]);
                 });
                 return ret;
             })(),
             fields : ['code', 'title'],
             sortInfo : { field : 'title', direction: 'ASC' },
             xns : Roo.data,
             xtype : 'SimpleStore'
            },
            '|xns' : 'Roo.form',
            allowBlank : false,
            displayField : 'title',
            editable : false,
            fieldLabel : 'Country',
            hiddenName : 'freq_day',
            listWidth : 300,
            mode : 'local',
            name : 'freq_day_name',
            pageSize : 40,
            triggerAction : 'all',
            valueField : 'code',
            xns : Roo.form,
            xtype : 'ComboCheck',
            items : [

            ]

           },
           '|xns' : 'Roo.grid',
           xns : Roo.grid,
           xtype : 'GridEditor',
           items : [

           ]

          },
          '|xns' : 'Roo.grid',
          dataIndex : 'freq_day',
          header : _this._strings['157e432ec303efd7d537b653cb255ccc'],
          renderer : function(v,x,r) { 
              
              if (v.length) {
               
                  var cm = _this.grid.colModel;
                 
                  var ci = cm.getColumnByDataIndex(this.name);
                 
                   var tv = [];
                  var vals = Roo.decode(v);
                  Roo.each(vals, function(k) {
                      var r = this.findRecord(this.valueField, k);
                      if(r){
                          tv.push(r.data[this.displayField]);
                      }else if(this.valueNotFoundText !== undefined){
                          tv.push( this.valueNotFoundText );
                      }
                  },ci.editor.field);
          
                  r.data[this.name + '_name'] = tv.join(', ');
                  return String.format('{0}',tv.join(', '));
          
                  
              
              }
              r.data[this.name + '_name'] = '';
              return String.format('{0}', r.data.freq_day_name || v); 
              
          },
          width : 100,
          xns : Roo.grid,
          xtype : 'ColumnModel',
          items : [

          ]

         },
{
          editor : {
           field : {
            store : {
             '|xns' : 'Roo.data',
             data : (function() { 
                 var ret = [];
                 for (var i = 5; i < 25; i++) {
                     var h = i < 10 ? ('0' + i) : i;
                     var mer = i < 12 || i > 23 ? 'am' : 'pm';
                     var dh = i < 13 ? i : i-12;
                     
                     ret.push([ h+':00', dh+':00' + mer ]);
                     ret.push([ h+':30', dh+':30' + mer ]);        
                 }
                 return ret;
             })(),
             fields : ['code', 'title'],
             sortInfo : { field : 'title', direction: 'ASC' },
             xns : Roo.data,
             xtype : 'SimpleStore'
            },
            '|xns' : 'Roo.form',
            allowBlank : false,
            displayField : 'title',
            editable : false,
            fieldLabel : 'Country',
            hiddenName : 'freq_hour',
            listWidth : 300,
            mode : 'local',
            name : 'freq_hour_name',
            pageSize : 40,
            triggerAction : 'all',
            valueField : 'code',
            xns : Roo.form,
            xtype : 'ComboCheck',
            items : [

            ]

           },
           '|xns' : 'Roo.grid',
           xns : Roo.grid,
           xtype : 'GridEditor',
           items : [

           ]

          },
          '|xns' : 'Roo.grid',
          dataIndex : 'freq_hour',
          header : _this._strings['ce527d7432bab413730f25c794648e46'],
          renderer : function(v,x,r) { 
              
           
              if (v.length) {
               
                  var cm = _this.grid.colModel;
                 
                  var ci = cm.getColumnByDataIndex(this.name);
                 
                   var tv = [];
                  var vals = Roo.decode(v);
                  Roo.each(vals, function(k) {
                      var r = this.findRecord(this.valueField, k);
                      if(r){
                          tv.push(r.data[this.displayField]);
                      }else if(this.valueNotFoundText !== undefined){
                          tv.push( this.valueNotFoundText );
                      }
                  },ci.editor.field);
          
                   r.data[this.name + '_name'] = tv.join(', ');
                  return String.format('{0}',tv.join(', '));
          
                  
              
              }
                  r.data[this.name + '_name'] = '';
              return String.format('{0}', r.data.freq_hour_name || v); 
              
          },
          width : 100,
          xns : Roo.grid,
          xtype : 'ColumnModel',
          items : [

          ]

         },
{
          editor : {
           field : {
            store : {
             proxy : {
              '|xns' : 'Roo.data',
              method : 'GET',
              url : baseURL + '/Core/I18n/Timezone.php',
              xns : Roo.data,
              xtype : 'HttpProxy'
             },
             reader : {
              '|xns' : 'Roo.data',
              fields : [{"name":"tz","type":"string"}],
              id : 'id',
              root : 'data',
              totalProperty : 'total',
              xns : Roo.data,
              xtype : 'JsonReader'
             },
             '|xns' : 'Roo.data',
             remoteSort : true,
             sortInfo : { direction : 'ASC', field: 'tz' },
             xns : Roo.data,
             xtype : 'Store',
             listeners : {
              beforeload : function (_self, o){
                   o.params = o.params || {};
                   // set more here
               }
             },
             items : [

             ]

            },
            '|xns' : 'Roo.form',
            allowBlank : false,
            displayField : 'tz',
            editable : true,
            emptyText : _this._strings['44c68bed631ff6e62aecc4a4d32176e6'],
            fieldLabel : 'core_enum',
            forceSelection : true,
            listWidth : 400,
            loadingText : _this._strings['1243daf593fa297e07ab03bf06d925af'],
            minChars : 2,
            name : 'tz',
            pageSize : 999,
            qtip : _this._strings['44c68bed631ff6e62aecc4a4d32176e6'],
            queryParam : 'q',
            selectOnFocus : true,
            tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{tz}</b> </div>',
            triggerAction : 'all',
            typeAhead : true,
            width : 300,
            xns : Roo.form,
            xtype : 'ComboBox',
            items : [

            ]

           },
           '|xns' : 'Roo.grid',
           xns : Roo.grid,
           xtype : 'GridEditor',
           items : [

           ]

          },
          '|xns' : 'Roo.grid',
          dataIndex : 'tz',
          header : _this._strings['236df51bb0e6416236e255b528346fca'],
          renderer : function(v) { return String.format('{0}', v); },
          width : 100,
          xns : Roo.grid,
          xtype : 'ColumnModel',
          items : [

          ]

         },
{
          '|xns' : 'Roo.grid',
          dataIndex : 'last_event_id',
          header : _this._strings['b26686c0a708faee42861d8b905e882e'],
          renderer : function(v) { return String.format('{0}', v ? v : 'never'); },
          width : 75,
          xns : Roo.grid,
          xtype : 'ColumnModel'
         },
{
          '|xns' : 'Roo.grid',
          dataIndex : 'keyword_filters',
          header : _this._strings['867343577fa1f33caa632a19543bd252'],
          renderer : function(v) { return String.format('{0}', v ? v : ''); },
          width : 75,
          xns : Roo.grid,
          xtype : 'ColumnModel'
         }
       ],
       listeners : {
        afteredit : function (e)
         {
            e.record.commit();
         },
        cellclick : function (_self, rowIndex, columnIndex, e)
         {
             var di = this.colModel.getDataIndex(columnIndex);
             if (di != 'keyword_filters') {
                 return;
             }
             
             Pman.Dialog.CoreNotifyRecurKeywords.show({}, function(res){
                 Roo.log(res);
             });
             
         },
        render : function() 
         {
             _this.grid = this; 
             //_this.dialog = Pman.Dialog.FILL_IN
             if (_this.panel.active) {
             //   this.footer.onClick('first');
             }
         }
       },
       items : [

       ]

      },
      '|xns' : 'Roo',
      background : false,
      fitContainer : true,
      fitToFrame : true,
      region : 'center',
      tableName : 'core_notify_recur',
      title : _this._strings['c7179f16884513c990b6cbf44aef7fc3'],
      xns : Roo,
      xtype : 'GridPanel',
      listeners : {
       activate : function() {
         _this.panel = this;
            if (_this.grid) {
        //        _this.grid.footer.onClick('first');
            }
        }
      },
      items : [

      ]

     }
    ]

   });
 }
};
