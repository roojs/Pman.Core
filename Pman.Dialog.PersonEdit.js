//<script type="text/javascript">
// for needed for new person in External contacts...



// needs adding to init!!!!
Pman.on('beforeload', function() {
    
    // edit - company readonly /office  - selectable..
    // CONTACTS!!!!!
    Pman.Dialog.PersonEdit = new Pman.Dialog.PersonEditor({
        type : 'edit',
        dialogConfig : {
            title: "Edit Contact Details",
            height: 400 // slightly taller..
            
        },
        itemList : [
            'company_id_name',
            'office_id_name',
            'name','role', 'phone', 'fax', 'email',
            'passwd1', 'passwd2',
            'id', 
            //'company_id', 
            'company_id_email',
            'company_id_address','company_id_tel','company_id_fax'
        ]
    });
});