//<script type="text/javascript">
// for needed for new person in External contacts...



// needs adding to init!!!!
Pman.on('beforeload', function() {
    
    // edit - company readonly /office  - selectable..
    // CONTACTS!!!!!
    Pman.Dialog.PersonSimpleContact = new Pman.Dialog.PersonEditor({
        type : 'edit',
        dialogConfig : {
            title: "Edit Contact Details",
            height: 200 // slightly taller..
        },
        itemList : [
            'name', 'phone', 'fax', 'email',
            'id'
        ]
    });
});