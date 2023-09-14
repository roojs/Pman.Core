//<script type="text/javascript">

// Auto generated file - created by app.Builder.js- do not edit directly (at present!)

Roo.namespace('Pman.Dialog');

Pman.Dialog.CoreCompanies = {

 _strings : {
  'ce8ae9da5b7cd6c3df2929543a9af92d' :"Email",
  '231bc72756b5e6de492aaaa1577f61b1' :"Remarks",
  'b33457e7e1cd5dbf1db34a0c60fcb75f' :"Company ID (for filing Ref.)",
  '023a5dfa857c4aa0156e6685231a1dbd' :"Select Type",
  '8535bcc0f05358a583bb432bbadf7e0d' :"Select type",
  '733640ec0c9367df1b4d85eb286ed9ae' :"Enter code",
  '8c04eb09879a05470fae436ba76e3bb9' :"Enter Url",
  '4ef6052d74436756f08e95fd63949653' :"Enter Company Name",
  'c54b90756cfbeff9217293b567cb2eb0' :"Enter remarks",
  '1243daf593fa297e07ab03bf06d925af' :"Searching...",
  'bcc254b55c4a1babdf1dcb82c207506b' :"Phone",
  'cf3a5d25d39613ad5bbc2f5eb0f9b675' :"Enter Fax Number",
  '9f86c00615b1a210935ac28ff8ebbb22' :"Enter Email Address",
  'e7b47c58815acf1d3afa59a84b5db7fb' :"Company Name",
  'ea4788705e6873b424c65e91c2846b19' :"Cancel",
  '32c4e9483403d60115b21519178e5384' :"Enter Address",
  'b9c49611cfda3259a2b837b39489e650' :"Add Image",
  '72b9d3d2e231f1de7a2bd70737f644aa' :"Add / Edit Organization",
  'a1fa27779242b4902f7ae3bdd5c6d508' :"Type",
  '02a3a357710cc2a5dfdfb74ed012fb59' :"Url",
  'dd7bf230fde8d4836917806aff6a6b27' :"Address",
  'c9cc8cce247e49bae79f15173ce97354' :"Save",
  'bc3a4c40d007b8d610a16312970e5cb3' :"Enter Phone Number",
  '9810aa2b9f44401be4bf73188ef2b67d' :"Fax",
  '35cb9e66ff801a819684ee0fbeabaeeb' :"Background Colour",
  'bc87ef2144ae15ef4f78211e73948051' :"Logo Image"
 },
 _named_strings : {
  'name_qtip' : '4ef6052d74436756f08e95fd63949653' /* Enter Company Name */ ,
  'tel_fieldLabel' : 'bcc254b55c4a1babdf1dcb82c207506b' /* Phone */ ,
  'address_qtip' : '32c4e9483403d60115b21519178e5384' /* Enter Address */ ,
  'background_color_fieldLabel' : '35cb9e66ff801a819684ee0fbeabaeeb' /* Background Colour */ ,
  'comptype_id_display_name_emptyText' : '023a5dfa857c4aa0156e6685231a1dbd' /* Select Type */ ,
  'comptype_id_display_name_fieldLabel' : 'a1fa27779242b4902f7ae3bdd5c6d508' /* Type */ ,
  'comptype_id_display_name_loadingText' : '1243daf593fa297e07ab03bf06d925af' /* Searching... */ ,
  'code_fieldLabel' : 'b33457e7e1cd5dbf1db34a0c60fcb75f' /* Company ID (for filing Ref.) */ ,
  'fax_fieldLabel' : '9810aa2b9f44401be4bf73188ef2b67d' /* Fax */ ,
  'name_fieldLabel' : 'e7b47c58815acf1d3afa59a84b5db7fb' /* Company Name */ ,
  'fax_qtip' : 'cf3a5d25d39613ad5bbc2f5eb0f9b675' /* Enter Fax Number */ ,
  'url_qtip' : '8c04eb09879a05470fae436ba76e3bb9' /* Enter Url */ ,
  'remarks_fieldLabel' : '231bc72756b5e6de492aaaa1577f61b1' /* Remarks */ ,
  'code_qtip' : '733640ec0c9367df1b4d85eb286ed9ae' /* Enter code */ ,
  'tel_qtip' : 'bc3a4c40d007b8d610a16312970e5cb3' /* Enter Phone Number */ ,
  'email_qtip' : '9f86c00615b1a210935ac28ff8ebbb22' /* Enter Email Address */ ,
  'url_fieldLabel' : '02a3a357710cc2a5dfdfb74ed012fb59' /* Url */ ,
  'remarks_qtip' : 'c54b90756cfbeff9217293b567cb2eb0' /* Enter remarks */ ,
  'comptype_id_display_name_qtip' : '8535bcc0f05358a583bb432bbadf7e0d' /* Select type */ ,
  'email_fieldLabel' : 'ce8ae9da5b7cd6c3df2929543a9af92d' /* Email */ ,
  'address_fieldLabel' : 'dd7bf230fde8d4836917806aff6a6b27' /* Address */ ,
  'logo_id_fieldLabel' : 'bc87ef2144ae15ef4f78211e73948051' /* Logo Image */ 
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
    autoCreate : true,
    closable : false,
    collapsible : false,
    draggable : false,
    height : 400,
    modal : true,
    shadow : true,
    title : _this._strings['72b9d3d2e231f1de7a2bd70737f644aa'] /* Add / Edit Organization */,
    width : 750,
    xns : Roo,
    '|xns' : 'Roo',
    center : {
     xtype : 'LayoutRegion',
     alwaysShowTabs : false,
     autoScroll : false,
     closeOnTab : true,
     hideTabs : true,
     titlebar : false,
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
      text : _this._strings['c9cc8cce247e49bae79f15173ce97354'] /* Save */,
      listeners : {
       click : function (_self, e)
        {
            if(!_this.form.isValid()){
                Roo.MessageBox.alert('Error', 'Please Correct all the errors in red');
                return;
            }
            
            new Pman.Request({
                url : baseURL + '/Roo/Core_company.php',
                method : 'POST',
                params : {
                  id : _this.form.findField('id').getValue() * 1,
                  name : _this.form.findField('name').getValue(),
                  _check_name : 1
                }, 
                success : function(res) {
                    _this.dialog.el.mask("Saving");
                    _this.form.doAction("submit");
                },
                failure : function(res) {
                    Roo.MessageBox.confirm(
                        "Confirm", 
                        "The company name has been used. Save it anyway?", 
                        function(res) {
                            if(res != 'yes') {
                                return;
                            }
                            
                            _this.dialog.el.mask("Saving");
                            _this.form.doAction("submit");
                        }
                    );
                }
            });
            
            return;
            
        }
      },
      xns : Roo,
      '|xns' : 'Roo'
     }
    ],
    items  : [
     {
      xtype : 'ContentPanel',
      autoCreate : true,
      fitToFrame : true,
      region : 'center',
      xns : Roo,
      '|xns' : 'Roo',
      items  : [
       {
        xtype : 'Form',
        fileUpload : true,
        labelWidth : 160,
        url : baseURL + '/Roo/core_company.php',
        listeners : {
         actioncomplete : function(f, act) {
              _this.dialog.el.unmask();
              //console.log('load completed'); 
              // error messages?????
              if(act.type == 'setdata'){
                  this.load({ method: 'GET', params: { '_id' : _this.data.id }});
                  return;
              }
             
              if (act.type == 'load') {
                  _this.data = act.result.data;
                  var meth = _this.data.comptype == 'OWNER' ? 'disable' : 'enable';
               
                      
                  if (_this.form.findField('comptype')) {
                      _this.form.findField('comptype')[meth]();
                  }
                   
                 // _this.loaded();
                  return;
              }
              
              
              if (act.type == 'submit') { // only submitted here if we are 
                  _this.dialog.hide();
                 
                  if (_this.callback) {
                      _this.callback.call(this, act.result.data);
                  }
                  return; 
              }
              // unmask?? 
          },
         actionfailed : function(f, act) {
              _this.dialog.el.unmask();
              // error msg???
              Pman.standardActionFailed(f,act);
                        
          },
         rendered : function (form)
          {
              _this.form = form;
          }
        },
        xns : Roo.form,
        '|xns' : 'Roo.form',
        items  : [
         {
          xtype : 'Column',
          width : 500,
          xns : Roo.form,
          '|xns' : 'Roo.form',
          items  : [
           {
            xtype : 'TextField',
            allowBlank : true,
            fieldLabel : _this._strings['b33457e7e1cd5dbf1db34a0c60fcb75f'] /* Company ID (for filing Ref.) */,
            name : 'code',
            qtip : _this._strings['733640ec0c9367df1b4d85eb286ed9ae'] /* Enter code */,
            width : 100,
            xns : Roo.form,
            '|xns' : 'Roo.form'
           },
           {
            xtype : 'ComboBox',
            allowBlank : false,
            alwaysQuery : true,
            displayField : 'display_name',
            emptyText : _this._strings['023a5dfa857c4aa0156e6685231a1dbd'] /* Select Type */,
            fieldLabel : _this._strings['a1fa27779242b4902f7ae3bdd5c6d508'] /* Type */,
            forceSelection : true,
            hiddenName : 'comptype_id',
            listWidth : 250,
            loadingText : _this._strings['1243daf593fa297e07ab03bf06d925af'] /* Searching... */,
            minChars : 2,
            name : 'comptype_id_display_name',
            pageSize : 20,
            qtip : _this._strings['8535bcc0f05358a583bb432bbadf7e0d'] /* Select type */,
            queryParam : 'query[name]',
            selectOnFocus : true,
            tpl : '<div class=\"x-grid-cell-text x-btn button\"><b>{name}</b> : {display_name}</div>',
            triggerAction : 'all',
            typeAhead : false,
            valueField : 'id',
            width : 200,
            listeners : {
             render : function (_self)
              {
                  _this.etypeCombo = _self;
              }
            },
            xns : Roo.form,
            '|xns' : 'Roo.form',
            store : {
             xtype : 'Store',
             remoteSort : true,
             sortInfo : { direction : 'ASC', field: 'id' },
             listeners : {
              beforeload : function (_self, o){
                   o.params = o.params || {};
                   // set more here
                   //o.params['query[empty_etype]'] = 1;
                   o.params.etype = 'COMPTYPE';
               }
             },
             xns : Roo.data,
             '|xns' : 'Roo.data',
             proxy : {
              xtype : 'HttpProxy',
              method : 'GET',
              url : baseURL + '/Roo/core_enum.php',
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
            xtype : 'TextField',
            allowBlank : false,
            fieldLabel : _this._strings['e7b47c58815acf1d3afa59a84b5db7fb'] /* Company Name */,
            name : 'name',
            qtip : _this._strings['4ef6052d74436756f08e95fd63949653'] /* Enter Company Name */,
            width : 300,
            xns : Roo.form,
            '|xns' : 'Roo.form'
           },
           {
            xtype : 'TextField',
            allowBlank : true,
            fieldLabel : _this._strings['bcc254b55c4a1babdf1dcb82c207506b'] /* Phone */,
            name : 'tel',
            qtip : _this._strings['bc3a4c40d007b8d610a16312970e5cb3'] /* Enter Phone Number */,
            width : 300,
            xns : Roo.form,
            '|xns' : 'Roo.form'
           },
           {
            xtype : 'TextField',
            allowBlank : true,
            fieldLabel : _this._strings['9810aa2b9f44401be4bf73188ef2b67d'] /* Fax */,
            name : 'fax',
            qtip : _this._strings['cf3a5d25d39613ad5bbc2f5eb0f9b675'] /* Enter Fax Number */,
            width : 300,
            xns : Roo.form,
            '|xns' : 'Roo.form'
           },
           {
            xtype : 'TextField',
            allowBlank : true,
            fieldLabel : _this._strings['ce8ae9da5b7cd6c3df2929543a9af92d'] /* Email */,
            name : 'email',
            qtip : _this._strings['9f86c00615b1a210935ac28ff8ebbb22'] /* Enter Email Address */,
            width : 300,
            xns : Roo.form,
            '|xns' : 'Roo.form'
           },
           {
            xtype : 'TextField',
            allowBlank : true,
            fieldLabel : _this._strings['02a3a357710cc2a5dfdfb74ed012fb59'] /* Url */,
            name : 'url',
            qtip : _this._strings['8c04eb09879a05470fae436ba76e3bb9'] /* Enter Url */,
            width : 300,
            xns : Roo.form,
            '|xns' : 'Roo.form'
           },
           {
            xtype : 'TextArea',
            allowBlank : true,
            fieldLabel : _this._strings['dd7bf230fde8d4836917806aff6a6b27'] /* Address */,
            name : 'address',
            qtip : _this._strings['32c4e9483403d60115b21519178e5384'] /* Enter Address */,
            width : 300,
            xns : Roo.form,
            '|xns' : 'Roo.form'
           },
           {
            xtype : 'TextArea',
            allowBlank : true,
            fieldLabel : _this._strings['231bc72756b5e6de492aaaa1577f61b1'] /* Remarks */,
            height : 120,
            name : 'remarks',
            qtip : _this._strings['c54b90756cfbeff9217293b567cb2eb0'] /* Enter remarks */,
            width : 300,
            xns : Roo.form,
            '|xns' : 'Roo.form'
           }
          ]
         },
         {
          xtype : 'Column',
          labelAlign : 'top',
          width : 200,
          xns : Roo.form,
          '|xns' : 'Roo.form',
          items  : [
           {
            xtype : 'ColorField',
            fieldLabel : _this._strings['35cb9e66ff801a819684ee0fbeabaeeb'] /* Background Colour */,
            name : 'background_color',
            xns : Roo.form,
            '|xns' : 'Roo.form'
           },
           {
            xtype : 'DisplayField',
            fieldLabel : _this._strings['bc87ef2144ae15ef4f78211e73948051'] /* Logo Image */,
            height : 170,
            icon : 'rootURL + \'images/default/dd/drop-add.gif\'',
            name : 'logo_id',
            style : 'border: 1px solid #ccc;',
            valueRenderer : function(v) {
                //var vp = v ? v : 'Companies:' + _this.data.id + ':-LOGO';
                if (!v) {
                    return "No Image Available" + '<BR/>';
                }
                return String.format('<a target="_new" href="{1}"><img src="{0}" width="150"></a>', 
                        baseURL + '/Images/Thumb/150x150/' + v + '/logo.jpg',
                        baseURL + '/Images/'+v+'/logo.jpg'           // fixme - put escaped company name..
                );
            },
            width : 170,
            xns : Roo.form,
            '|xns' : 'Roo.form'
           },
           {
            xtype : 'Button',
            text : _this._strings['b9c49611cfda3259a2b837b39489e650'] /* Add Image */,
            listeners : {
             click : function (_self, e)
              {
                  var _t = _this.form.findField('logo_id');
                                       
                  Pman.Dialog.Image.show({
                      onid :_this.data.id,
                      ontable : 'pressrelease_terminals',
                      imgtype : 'LOGO'
                  }, function(data) {
                      if  (data) {
                          _t.setValue(data.id);
                      }
                      
                  });
              }
            },
            xns : Roo,
            '|xns' : 'Roo'
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
