/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2015 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$(function() {
	$("#dedipanel_steam_mode").parent().parent().hide();
    
    $("#dedipanel_steam_game").change(function () {
        game = $("#dedipanel_steam_game option:selected" ).text();
        if (game == 'Counter-Strike: Global Offensive') {
            $('#dedipanel_steam_mode').parent().parent().show( 400 );
        }
        else{
            $('#dedipanel_steam_mode').parent().parent().hide( 300 );
        }
    });
});
