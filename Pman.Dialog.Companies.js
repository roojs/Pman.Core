//<script type="text/javascript">


Pman.Dialog.Companies =   new Roo.util.Observable({
//    events : {
//        'beforerender' : true, // trigger so we can add modules later..
//        'show' : true, // trigger on showing form.. - to load additiona data..
//        'beforesave' : true
//    },
    show : function (data, callback)
    {
        Pman.Dialog.CoreCompanies.show({id:data.id});
    }

});
