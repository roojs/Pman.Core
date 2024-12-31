//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.PreviewRowsImport = {

 _strings : {
  'b718adec73e04ce3ec720dd11a06a308' :"ID",
  '72d6d7a1885885bb55a565fd1070581a' :"Import",
  'ea4788705e6873b424c65e91c2846b19' :"Cancel",
  '0600a5eb111ff39e7603c8b957a9c767' :"Preview Rows from XLS"
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
    height : 400,
    minHeight : 400,
    minWidth : 1300,
    modal : true,
    resizable : true,
    title : _this._strings['0600a5eb111ff39e7603c8b957a9c767'] /* Preview Rows from XLS */,
    width : 950,
    listeners : {
     show : function (_self)
      {
          var fields = [{
              name: 'valid',
              type: 'string'
          }];
          var cols = [{
              dataIndex: 'valid',
              header: 'Valid',
              renderer: function (v) { return  String.format('{0}', v); },
              width: 30
          }];
          
          var missingEmail = true;
          
          Roo.each(_this.data.data.headers, function(h, index)  {
              // no mapping
              if(_this.data.colMap[index] == '') {
                  return;
              }
              
              if(_this.data.colMap[index] == 'email') {
                  missingEmail = false;
              }
              
              fields.push({
                  name: _this.data.colMap[index],
                  type: 'string'
              });
              
              // get display name
              var header = h;
              _this.data.dbCols.forEach(function(c) {
                  if(_this.data.colMap[index] == c[0]) {
                      header = c[1];
                  }
              });
              
              cols.push({
                  dataIndex: _this.data.colMap[index],
                  header: header,
                  renderer: function (v, x, r) {
                      if(r.data.valid === '') {
                          if(r.data[_this.data.colMap[index] + '_valid'] === true) {
                              return String.format('<span style="color: green;">{0}</span>', v);
                          }
                          return String.format('<s style="color: red;">{0}</s>', v);
                      }
                      return String.format('{0}', v);
                  },
                  width: 75
              });
          });
          
          var ds = new Roo.data.SimpleStore({
              fields: fields
          });
          
          Roo.each(_this.data.data.rows, function(r)  {
              var data = {
                  valid: 'V'
              };
              Roo.each(_this.data.data.headers, function (h, index)  {
                  // no mapping
                  if(_this.data.colMap[index] == '') {
                      return;
                  }
                  data[_this.data.colMap[index]] = r[index];
              });
              
              ds.add(new Roo.data.Record(data));
          });
          
          _this.grid.reconfigure(ds, new Roo.grid.ColumnModel(cols));
          
          if(missingEmail) {
              Roo.MessageBox.alert('Error', 'The mapping to "Email" database column is missing');
              return;
          }
          
          var emails = [];
          
          Roo.each(_this.data.data.rows, function (r, i) {
              Roo.each(_this.data.data.headers, function (h, headerIndex)  {
                  if(_this.data.emailCols.includes(_this.data.colMap[headerIndex]) && r[headerIndex] != '') {
                      emails.push({
                          email: r[headerIndex],
                          error: false,
                          rowIndex: i,
                          col: _this.data.colMap[headerIndex]
                      });
                  }
              });
          });
          
          var emailColIndexes = [];
          Roo.each(_this.data.data.headers, function (h, headerIndex)  {
              if(_this.data.emailCols.includes(_this.data.colMap[headerIndex])) {
                  emailColIndexes.push(headerIndex);
              }
          });
          
          var validateIndex = 0;
          
          var validateEmail = function() {
              var email = emails[validateIndex]['email'];
              var rowIndex = emails[validateIndex]['rowIndex'];
              var emailCol = emails[validateIndex]['col'];
              
              new Pman.Request({
                  url: _this.data.url,
                  timeout : 60000,
                  params: {
                      _validate_email_file_id: _this.data.fileId,
                      _validate_email: email
                  },
                  failure : function(res)
                  {
                      validateEmail(); // try again?
                  },
                  success: function(res) {
                      if(!res.data.valid) {
                          emails[validateIndex]['error'] = res.data.errorMsg;
                          _this.grid.dataSource.getAt(rowIndex).set('valid', '');
                      }
                      else {
                          _this.grid.dataSource.getAt(rowIndex).set(emailCol + '_valid', true);
                      }
                      
                      validateIndex ++;
                      Roo.MessageBox.updateProgress(
                          validateIndex / emails.length,
                          validateIndex + " / " + emails.length + " emails validated"
                      );
                      
                      
                      if(emails.length == validateIndex) {
                          Roo.MessageBox.hide();
                          
                          _this.validIndexes = Array.from(_this.data.data.rows.keys());
                          
                          var errors = [];
                          
                          Roo.each(emails, function(e)  {
                              if(e.error !== false) {
                                  errors.push(e.error);
                                  _this.validIndexes.remove(e.rowIndex);
                              }
                          });
                          
                          if(errors.length) {
                              // show errors
                              Roo.MessageBox.show({
                                  title: errors.length + " emails have failed, we will import the contacts without bad email", 
                                  multiline: 500,
                                  value: errors.join("\n"),
                                  buttons: {ok: "Download failed contacts"},
                                  closable: false,
                                  fn: function(res) {
                                      new Pman.Download({
                                          newWindow :  true,
                                          url : _this.data.url,
                                          method : 'GET',
                                          params: {
                                              'fileId': _this.data.fileId,
                                              'emailColIndexes': Roo.encode(emailColIndexes)
                                          }
                                      });
                                  }
                              });
                          }
                          
                          return;
                      }
                      
                      validateEmail();
                  }
              });
          };
          
          Roo.MessageBox.progress("Validating emails", "Starting");
          
          new Pman.Request({
              url: _this.data.url,
              timeout : 60000,
              params: {
                  _get_old_emails: 1,
                  fileId: _this.data.fileId,
                  colMap: Roo.encode(_this.data.colMap),
                  emailColIndexes: Roo.encode(emailColIndexes)
              },
              success: function(res) {
                  var oldEmails = res.data;
                  emails = emails.filter(function(emailObj) {
                      if(!oldEmails.includes(emailObj.email)) {
                          return true;
                      }
                      
                      // existing emails are valid
                      // no need to revalidate
                      _this.grid.dataSource.getAt(emailObj.rowIndex).set(emailObj.col + '_valid', true);
                      
                      return false;
                  });
                  
                  if(!emails.length) {
                      Roo.MessageBox.hide();
                      _this.validIndexes = Array.from(_this.data.data.rows.keys());
                  }
                  validateEmail();
              }
          });
      }
    },
    xns : Roo,
    '|xns' : 'Roo',
    center : {
     xtype : 'LayoutRegion',
     tabPosition : 'top',
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
            new Pman.Request({
                method: 'POST',
                url: _this.data.url,
                mask: 'Deleting old uploaded files',
                params: {
                    _delete: _this.data.fileId
                },
                success: function(res) {
                    _this.dialog.hide();
                }
            });
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
            var params = {
                fileId: _this.data.fileId,
                colMap: Roo.encode(_this.data.colMap),
                rowIndexes: Roo.encode(_this.validIndexes),
                _import: 1
            };
            
            if(typeof(_this.data.mailingListId) != 'undefined') {
                params['mailingListId'] = _this.data.mailingListId;
            }
            
            new Pman.Request({
                method: 'POST',
                url: _this.data.url,
                mask: 'Importing',
                params: params,
                success: function(res) {
                    _this.dialog.hide();
                }
            });
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
      fitContainer : false,
      fitToframe : false,
      region : 'center',
      xns : Roo,
      '|xns' : 'Roo',
      grid : {
       xtype : 'Grid',
       listeners : {
        render : function() 
         {
             _this.grid = this; 
         }
       },
       xns : Roo.grid,
       '|xns' : 'Roo.grid',
       dataSource : {
        xtype : 'SimpleStore',
        fields : [
        
        ],
        xns : Roo.data,
        '|xns' : 'Roo.data'
       },
       colModel : [
        {
         xtype : 'ColumnModel',
         dataIndex : 'id',
         header : _this._strings['b718adec73e04ce3ec720dd11a06a308'] /* ID */,
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
