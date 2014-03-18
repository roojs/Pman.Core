//<script type="text/javascript">
// for needed for new person in External contacts...



// needs adding to init!!!!
Pman.on('beforeload', function() {
     // new - company/office pulldowns.
     // used by pman
    Pman.Dialog.PersonNew = new Pman.Dialog.PersonEditor({
        type : 'new',
        dialogConfig : {
            title: "New Contact Details",
            height: 400 // slightly taller..
        },
        itemList : [
            'company_id_name',
            'office_id_name',
            'name','role', 'phone', 'fax', 'email',
            'project_id_fs',
            'id',  
            'company_id_email',
            'company_id_address','company_id_tel','company_id_fax', 
            'project_id_addto' // hidden..
            
        ]
    });
});