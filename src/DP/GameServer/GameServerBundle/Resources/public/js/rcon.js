/*
** Copyright (C) 2010-2013 Kerouanton Albin, Smedts Jérôme
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License along
** with this program; if not, write to the Free Software Foundation, Inc.,
** 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
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
                url: elt.attr('action') + '.json', 
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
                }, 
                beforeSend: function(jqXHR, settings) {
                    var oldVal = textarea.val();
                    var cmd = elt.find('input#form_cmd').val();
                    
                    textarea.val(oldVal + "> " + cmd + "\n");
                    elt.find('input#form_cmd').val('');
                }, 
                error: function(jqXHR, textStatus, errorThrown) {
                    rconQueryInProgress = false;
                }
            });
        }
    });
});