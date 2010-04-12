//<script type="text/javascript">
// for needed for new person in External contacts...



// needs adding to init!!!!
Pman.on('beforeload', function() {
     
    Pman.Dialog.PersonStaff  = new Pman.Dialog.PersonEditor({
        type : 'staff',
        dialogConfig : {
            title: "Add / Edit Staff"
        },
        itemList : [
            
            
            'office_id_name',
            'name','role', 'phone', 'fax', 'email_req',
            'passwd1', 'passwd2',
            
            'id',  'office_id', 'company_id',
            'active',
            // not really needed??
            'company_id_email','company_id_address','company_id_tel','company_id_fax'
        ]
    });
    
    
    
});
 
