Roo.form.HtmlEditor.ToolbarContext.stores = {
   'font-family' :  {
            xtype : 'Store',
             // load using HTTP
            proxy: {
               xtype : 'HttpProxy',
               url: baseURL + '/Roo/Core_enum',
               method: 'GET'
            },
            reader : {
                xtype: 'JsonReader',
                xns: Roo.data,
                id : 'id',
                root : 'data',
                totalProperty : 'total',
                metaFromRemote : true,
                fields : [
                    { name:'id','type':'int'},
                    { name : 'val' , mapping: 'name'} ,
                    { name:'display', mapping: 'display_name'}
                ]
            },
           listeners : {
               beforeload : function(st,o)
               {
                   // compnay myst be set..
                    o.params.etype = 'HtmlEditor.font-family'
                    o.params.active = 1;
                    o.params._requestMeta=0; // do not fetch meta..
               },
               loadexception : Pman.loadException
           
           },
           sortInfo: {
               field: 'display_name', direction: 'ASC'
           }
    } 
}