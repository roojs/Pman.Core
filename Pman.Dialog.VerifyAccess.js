//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.VerifyAccess= function() {}
Roo.apply(Pman.Dialog.VerifyAccess.prototype, {

 _strings : {
  'e2c9d024b79dfb48b42a7807206c6aed' :"Verify New IP Access",
  'd41d8cd98f00b204e9800998ecf8427e' :"",
  'b945ed1d61d8edbe16a1192ad6d2fd58' :"Please enter a memorable password for logging into your application",
  'ee7ad62ad69b9ab5f25d273e4e0a1736' :"Your Username",
  '51eea3dc60ae3a0b1bb8188bc6337dc2' :"enter password",
  '1849ffc8731b5e74ae6157c91ba73575' :"Please confirm your password",
  'd71940f24ee38ee09f6e06b908480bcf' :"Resend email",
  '14cf5e829f5cb6fbf8cb54f7c5ff4ca9' :"Start the application process   "
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
     render : function (_self)
      {
          _this.modal = _self;
      },
     show : function (_self)
      {
          var path = window.location.pathname.split('/');
          
          var authorized_key = path.pop();
          
          var id = path.pop();
          
          new Pman.Request({
              url: baseURL + '/Core/VerifyAccess',
              method : 'POST',
              params : {
                  id : id,
                  authorized_key : authorized_key,
                  _to_data : 1
              }, 
              success : function(res) {
                  Roo.log(res);
                  return;
                  _this.form.setValues(res.data);
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
      html : _this._strings['14cf5e829f5cb6fbf8cb54f7c5ff4ca9'] /* Start the application process    */,
      weight : 'primary',
      listeners : {
       click : function (_self, e)
        {
            if(!_this.form.isValid()){
                return;
            }
            
            var p1 = _this.form.findField('password').getValue();
            var p2 = _this.form.findField('password1').getValue();
            
            if (p1 != p2) {
                _this.form.findField('password1').markInvalid('Password do not match');
                return;
            }
            
            _this.dialog.el.mask('Sending...');
            _this.form.doAction('submit');
            
        },
       render : function (_self)
        {
            _this.btn_ok = _self;
        }
      },
      xns : Roo.bootstrap,
      '|xns' : 'Roo.bootstrap'
     },
     {
      xtype : 'Button',
      html : _this._strings['d71940f24ee38ee09f6e06b908480bcf'] /* Resend email */,
      weight : 'primary',
      listeners : {
       click : function (_self, e)
        {
            var path = window.location.pathname.split('/');
            
            var verify_key = path.pop();
            
            var id = path.pop();
            
            new Pman.Request({
                url: baseURL + '/Roo/Coba_application_signup',
                method : 'POST',
                mask : 'Sending...',
                params : {
                    _resend : id
                }, 
                success : function(res) {
                    var msg = "We have re-sent you an invitation via email." +
                                "<br/><br/>" + 
                                "Please check your inbox for the final registration step." + 
                                 "<br/><br/>" + 
                                "<B>(Note. emails may accidentally be sent to your Spam Folder)</B>";
                                
                    Roo.bootstrap.MessageBox.alert('Please check your email', msg) ;
                },
                failure: function(res) {
                    Roo.bootstrap.MessageBox.alert('Error', res.errorMsg) ;
                }
            });
        },
       render : function (_self)
        {
            _this.btn_resend = _self;
             this.el.setVisibilityMode(Roo.Element.DISPLAY);
             this.el.hide();
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
      loadMask : false,
      url : baseURL + '/Register/Confirm',
      listeners : {
       actioncomplete : function (_self, action)
        {
            if (action.type == 'setdata') {
            
                _this.form.findField('password').focus();
                
                return;
            }
            if (action.type == 'load') {
                
                return;
            }
            if (action.type =='submit') {
                
                _this.dialog.hide();
                
                Roo.get(document.body).mask('Start your Application');
                
                setTimeout(function() {
                    window.location.href = baseURL;
                }, 500); 
                
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
            fieldLabel : _this._strings['ee7ad62ad69b9ab5f25d273e4e0a1736'] /* Your Username */,
            labelAlign : 'top',
            name : 'email',
            readOnly : true,
            xns : Roo.bootstrap,
            '|xns' : 'Roo.bootstrap'
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
              _this.row_pwd_label = _self;
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
            xtype : 'FieldLabel',
            html : _this._strings['b945ed1d61d8edbe16a1192ad6d2fd58'] /* Please enter a memorable password for logging into your application */,
            xns : Roo.bootstrap,
            '|xns' : 'Roo.bootstrap'
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
              _this.row_pwd1 = _self;
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
            xtype : 'SecurePass',
            allowBlank : false,
            inputType : 'password',
            labelAlign : 'top',
            name : 'password',
            placeholder : _this._strings['51eea3dc60ae3a0b1bb8188bc6337dc2'] /* enter password */,
            xns : Roo.bootstrap,
            '|xns' : 'Roo.bootstrap'
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
              _this.row_pwd2 = _self;
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
            xtype : 'Input',
            allowBlank : false,
            inputType : 'password',
            invalidText : 'The passwords you entered do not match',
            name : 'password1',
            placeholder : _this._strings['1849ffc8731b5e74ae6157c91ba73575'] /* Please confirm your password */,
            validator : function(v){
                
                if(_this.form.findField('password').getValue() == v) {
                    
                    return true;
                }
                
                return false;
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
          xs : 8,
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
            name : 'verify_key',
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
