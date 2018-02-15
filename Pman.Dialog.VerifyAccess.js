//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.VerifyAccess= function() {}
Roo.apply(Pman.Dialog.VerifyAccess.prototype, {

 _strings : {
  'e2c9d024b79dfb48b42a7807206c6aed' :"Verify New IP Access",
  'd41d8cd98f00b204e9800998ecf8427e' :"",
  'a12a3079e14ced46e69ba52b8a90b21a' :"IP",
  'f6039d44b29456b20f8f373155ae4973' :"Username",
  '004bf6c9a40003140292e97330236c53' :"Action",
  '5a787141d53b573ec9b86e900bfe0d79' :"Expire Date",
  'dfb790522fdea3859af206d32916fe77' :"User Agent",
  '70d9be9b139893aa6c69b5e77e614311' :"Confirm"
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
    xtype : 'Modal',
    allow_close : false,
    cls : 'enable-overflow',
    title : _this._strings['e2c9d024b79dfb48b42a7807206c6aed'] /* Verify New IP Access */,
    listeners : {
     show : function (_self)
      {
          var path = window.location.pathname.split('/');
          
          var authorized_key = path.pop();
          
          var id = path.pop();
          
          new Pman.Request({
              url: baseURL + '/Core/VerifyAccess',
              method : 'POST',
              mask : 'Loading...',
              params : {
                  id : id,
                  authorized_key : authorized_key,
                  _to_data : 1
              }, 
              success : function(res) {
              
                  _this.data = res.data;
                  
                  _this.form.setValues(_this.data);
                  
                  if(_this.data.status * 1 == 0){
                      _this.form.findField('status').reset();
                  }
                  
                  _this.form.clearInvalid();
                  
                  return;
              },
              failure: function(res) {
                  
                  _this.dialog.hide();
                  
                  Roo.bootstrap.MessageBox.alert('Error', res.errorMsg);
                  
                  return;
             }
          });
      }
    },
    xns : Roo.bootstrap,
    '|xns' : 'Roo.bootstrap',
    buttons : [
     {
      xtype : 'Button',
      html : _this._strings['70d9be9b139893aa6c69b5e77e614311'] /* Confirm */,
      weight : 'primary',
      listeners : {
       click : function (_self, e)
        {
            if(!_this.form.isValid()){
                return;
            }
        
            _this.dialog.el.mask('Sending...');
            _this.form.doAction('submit');
            
        }
      },
      xns : Roo.bootstrap,
      '|xns' : 'Roo.bootstrap'
     }
    ],
    items  : [
     {
      xtype : 'Form',
      errorMask : true,
      labelAlign : 'top',
      loadMask : false,
      url : baseURL + '/Core/VerifyAccess',
      listeners : {
       actioncomplete : function (_self, action)
        {
            if (action.type == 'setdata') {
                
                return;
            }
            if (action.type == 'load') {
                
                return;
            }
            if (action.type =='submit') {
                
                _this.dialog.hide();
                
                return;
            }
            
        },
       actionfailed : function (_self, action)
        {
            _this.dialog.el.unmask();
            Roo.log("action failed");
            Roo.log(action);
          
            if(!action.result.errorMsg){
                Roo.bootstrap.MessageBox.alert("Error", "Please contact system adminisrator");
            }
           
            var msg = action.result.errorMsg;
           
            if(msg.length >= 200){
                msg = msg.substring(0,199) + '...'
            }
            
            Roo.bootstrap.MessageBox.alert("Error", msg);
        },
       render : function (_self,e)
        {
            _this.form = _self;
            
        }
      },
      xns : Roo.bootstrap,
      '|xns' : 'Roo.bootstrap',
      items  : [
       {
        xtype : 'Row',
        xns : Roo.bootstrap,
        '|xns' : 'Roo.bootstrap',
        items  : [
         {
          xtype : 'Column',
          xs : 12,
          xns : Roo.bootstrap,
          '|xns' : 'Roo.bootstrap',
          items  : [
           {
            xtype : 'Input',
            fieldLabel : _this._strings['f6039d44b29456b20f8f373155ae4973'] /* Username */,
            name : 'email',
            readOnly : true,
            xns : Roo.bootstrap,
            '|xns' : 'Roo.bootstrap'
           }
          ]
         },
         {
          xtype : 'Column',
          xs : 12,
          xns : Roo.bootstrap,
          '|xns' : 'Roo.bootstrap',
          items  : [
           {
            xtype : 'Input',
            fieldLabel : _this._strings['a12a3079e14ced46e69ba52b8a90b21a'] /* IP */,
            name : 'ip',
            readOnly : true,
            xns : Roo.bootstrap,
            '|xns' : 'Roo.bootstrap'
           }
          ]
         },
         {
          xtype : 'Column',
          xs : 12,
          xns : Roo.bootstrap,
          '|xns' : 'Roo.bootstrap',
          items  : [
           {
            xtype : 'TextArea',
            fieldLabel : _this._strings['dfb790522fdea3859af206d32916fe77'] /* User Agent */,
            name : 'user_agent',
            readOnly : true,
            rows : 3,
            style : 'margin-bottom: 15px;',
            xns : Roo.bootstrap,
            '|xns' : 'Roo.bootstrap'
           }
          ]
         },
         {
          xtype : 'Column',
          xs : 12,
          xns : Roo.bootstrap,
          '|xns' : 'Roo.bootstrap',
          items  : [
           {
            xtype : 'ComboBox',
            allowBlank : false,
            alwaysQuery : true,
            displayField : 'value',
            editable : false,
            fieldLabel : _this._strings['004bf6c9a40003140292e97330236c53'] /* Action */,
            forceSelection : true,
            hiddenName : 'status',
            indicatorpos : 'right',
            mode : 'local',
            name : 'status_name',
            selectOnFocus : true,
            tpl : '<div class=\"roo-select2-result\"><b>{value}</b></div>',
            triggerAction : 'all',
            valueField : 'code',
            listeners : {
             select : function (combo, record, index)
              {
                  _this.expire_dt.allowBlank = true;
                  _this.expire_dt.el.hide();
                  
                  if(record.data.code == '-2'){
                      _this.expire_dt.allowBlank = false;
                      _this.expire_dt.el.show();
                  }
              }
            },
            xns : Roo.bootstrap,
            '|xns' : 'Roo.bootstrap',
            store : {
             xtype : 'SimpleStore',
             data : [
                 ['1', 'Approve'],
                 ['-2', 'Temporary'],
                 ['-1', 'Reject']
             ],
             fields : [ 'code', 'value' ],
             xns : Roo.data,
             '|xns' : 'Roo.data'
            }
           }
          ]
         },
         {
          xtype : 'Column',
          xs : 12,
          xns : Roo.bootstrap,
          '|xns' : 'Roo.bootstrap',
          items  : [
           {
            xtype : 'DateField',
            allowBlank : false,
            fieldLabel : _this._strings['5a787141d53b573ec9b86e900bfe0d79'] /* Expire Date */,
            format : 'Y-m-d',
            indicatorpos : 'right',
            name : 'expire_dt',
            listeners : {
             render : function (_self)
              {
                  _this.expire_dt = this;
                  
                  var d = new Date();
                  
                  d.setDate(d.getDate() - 1);
              
                  this.setStartDate(d);
                  
                  this.el.setVisibilityMode(Roo.Element.DISPLAY);
                  
                  this.el.hide();
              }
            },
            xns : Roo.bootstrap,
            '|xns' : 'Roo.bootstrap'
           }
          ]
         }
        ]
       },
       {
        xtype : 'Row',
        xns : Roo.bootstrap,
        '|xns' : 'Roo.bootstrap',
        items  : [
         {
          xtype : 'Column',
          xs : 12,
          xns : Roo.bootstrap,
          '|xns' : 'Roo.bootstrap',
          items  : [
           {
            xtype : 'Input',
            inputType : 'hidden',
            name : 'id',
            xns : Roo.bootstrap,
            '|xns' : 'Roo.bootstrap'
           },
           {
            xtype : 'Input',
            inputType : 'hidden',
            name : 'authorized_key',
            xns : Roo.bootstrap,
            '|xns' : 'Roo.bootstrap'
           }
          ]
         }
        ]
       }
      ]
     },
     {
      xtype : 'Row',
      listeners : {
       render : function (_self)
        {
            _this.error_row = this;
            
            this.el.setVisibilityMode(Roo.Element.DISPLAY);
            
            this.el.hide();
        }
      },
      xns : Roo.bootstrap,
      '|xns' : 'Roo.bootstrap',
      items  : [
       {
        xtype : 'Column',
        xs : 12,
        xns : Roo.bootstrap,
        '|xns' : 'Roo.bootstrap',
        items  : [
         {
          xtype : 'Element',
          html : _this._strings['d41d8cd98f00b204e9800998ecf8427e'] /*  */,
          listeners : {
           render : function (_self)
            {
                _this.text_el = _self;
            }
          },
          xns : Roo.bootstrap,
          '|xns' : 'Roo.bootstrap'
         }
        ]
       }
      ]
     }
    ]
   }  );
 }
});
Roo.apply(Pman.Dialog.VerifyAccess, Pman.Dialog.VerifyAccess.prototype);
