/*
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$(function() {
	$("#dedipanel_steam_create_mode").parent().hide();
    
    $("#dedipanel_steam_create_game").change(function () {
        game_create = $("#dedipanel_steam_create_game option:selected" ).text();
        if (game_create == 'Counter-Strike: Global Offensive') {
            $('#dedipanel_steam_create_mode').parent().show( 400 );
        }
        else{
            $('#dedipanel_steam_create_mode').parent().hide( 300 );
        }
    });
	
	game_update = $("#dedipanel_steam_update_game option:selected" ).text();
	if (game_update == 'Counter-Strike: Global Offensive') {
		$('#dedipanel_steam_update_mode').parent().show();
	}
	else{
		$('#dedipanel_steam_update_mode').parent().hide();
	}
});
