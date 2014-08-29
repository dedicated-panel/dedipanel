/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$(function() {
    var rconQueryInProgress = false;
    
    $('#rcon-form').bind('submit', function(e) {
        e.preventDefault();
        
        var elt = $(this);
        var textarea = elt.children('textarea');
        
        if (!rconQueryInProgress) {
            rconQueryInProgress = true;
            
            $.ajax({
                type: 'POST', 
                url: elt.attr('action'),
                dataType: 'json', 
                data: elt.serialize(), 
                success: function(data, status) {
                    // Récupération du contenu du textarea
                    // Et ajout des logs récupérés
                    var oldVal = textarea.val();
                    var newVal = '';

                    if (typeof data.error == "undefined") {
                        newVal = data.ret;
                    }
                    else {
                        newVal = data.error;
                    }

                    // Suppression de la valeur actuelle, pour la remplacer par la nouvelle
                    textarea.val('').val(oldVal + newVal + "\n");
                    
                    rconQueryInProgress = false;
                    elt.find('input#form_cmd').val('').removeAttr('disabled');
                }, 
                beforeSend: function(jqXHR, settings) {
                    var oldVal = textarea.val();
                    var cmd = elt.find('input#form_cmd').val();
                    
                    textarea.val(oldVal + "> " + cmd + "\n");
                    elt.find('input#form_cmd').val('').attr('disabled', 'disabled');
                }, 
                error: function(jqXHR, textStatus, errorThrown) {
                    rconQueryInProgress = false;
                    elt.find('input#form_cmd').val('').removeAttr('disabled');
                }
            });
        }
    });
});