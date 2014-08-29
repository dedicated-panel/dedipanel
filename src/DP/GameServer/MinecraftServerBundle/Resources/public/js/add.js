/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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