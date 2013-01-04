$(function() {
    // Change la valeur du champ queryPort selon le champ port uniquement si queryPort est vide
    var userModified = false;
    
    $('#dp_gameserver_minecraftserverbundle_addminecraftservertype_port').bind('blur', function() {
        if (!userModified) {
            $('#dp_gameserver_minecraftserverbundle_addminecraftservertype_queryPort').val(
                $(this).val()
            );
        }
    });
    
    $('#dp_gameserver_minecraftserverbundle_addminecraftservertype_queryPort').bind('blur', function() {
        if ($(this).val() != '') {
            userModified = true;
        }
        else {
            userModified = false;
        }
    });
});