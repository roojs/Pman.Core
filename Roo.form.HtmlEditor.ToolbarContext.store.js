Roo.form.HtmlEditor.ToolbarContext.store = {
   'font-family' : {
            xtype : 'Store',
             // load using HTTP
            proxy: {
               xtype : 'HttpProxy',
               url: baseURL + '/Roo/core_enum.html',
               method: 'GET'
            },
             reader : {
                xtype: 'JsonReader',
                xns: Roo.data,
                id : 'id',
                root : 'data',
                totalProperty : 'total',
                fields : [
                    { name:'id','type':'int'},
                    { name : 'name' , mapping: 'val'} ,
                    { name:'display_name', mapping: 'display'}
                ]
            },
           listeners : {
               beforeload : function(st,o)
               {
                   // compnay myst be set..
                    o.params.etype = 'HtmlEditor.font-family'
                    o.params.active = 1;
               },
               loadexception : Pman.loadException
           
           },
           sortInfo: {
               field: 'display_name', direction: 'ASC'
           }
    } 
}