$(function() {
    $('a.slide').bind('click', function(event) {
        var serverId = $(this).parents('.server-item').attr('rel');        
        $('.details[rel="' + serverId + '"]').slideToggle();
        
        event.preventDefault();
    });
    
    var rconQueryInProgress = false;
    $('#rcon-form').bind('submit', function(e) {
        e.preventDefault();
        var elt = $(this);
        
        if (!rconQueryInProgress) {
            rconQueryInProgress = true;
            
            $.ajax({
                type: 'POST', 
                url: elt.attr('action') + '.json', 
                dataType: 'json', 
                data: elt.serialize(), 
                success: function(data, status) {
                    // Récupération du contenu du textarea
                    // Et ajout des logs récupérés
                    var textarea = elt.children('textarea');
                    var oldVal = textarea.val();
                    var newVal = '';

                    if (typeof data.error == "undefined") {
                        newVal = data.log;
                    }
                    else {
                        newVal = data.error;
                    }

                    // Suppression de la valeur actuelle, pour la remplacer par la nouvelle
                    textarea.val('').val(oldVal + newVal);
                    
                    rconQueryInProgress = false;
                }, 
                beforeSend: function(jqXHR, settings) {
                    elt.find('input#form_cmd').val('');
                }, 
                error: function(jqXHR, textStatus, errorThrown) {
                    rconQueryInProgress = false;
                }
            });
        }
    });
});