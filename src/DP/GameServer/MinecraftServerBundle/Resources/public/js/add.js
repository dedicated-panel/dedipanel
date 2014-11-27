/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$(function() {
    // Change la valeur du champ queryPort selon le champ port uniquement si queryPort est vide
    var userModified = false;
    
    $('#dedipanel_minecraft_port').bind('blur', function() {
        if (!userModified) {
            $('#dedipanel_minecraft_queryPort').val(
                $(this).val()
            );
        }
    });
    
    $('#dedipanel_minecraft_queryPort').bind('blur', function() {
        if ($(this).val() != '') {
            userModified = true;
        }
        else {
            userModified = false;
        }
    });
});