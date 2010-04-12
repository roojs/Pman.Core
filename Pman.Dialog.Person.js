//<script type="text/javascript">
// for needed for new person in External contacts...



// needs adding to init!!!!
Pman.on('beforeload', function() {
  
   
    // new - office / company readonly
    Pman.Dialog.Person  = new Pman.Dialog.PersonEditor({
        type : 'edit2',
        dialogConfig : {
            title: "Edit Contact Details", 
            height: 400 // slightly taller..

        },
        itemList : [
            
            'company_id_name_ro',
            'office_id_name_ro',
            'name','role', 'phone', 'fax', 'email',
            'id',  'office_id', 'company_id',
            
            // not really needed??
            'company_id_email','company_id_address','company_id_tel','company_id_fax'
        ]
    });
});