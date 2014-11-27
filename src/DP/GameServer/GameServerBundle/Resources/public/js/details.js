/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$(function() {
    $('a.slide').bind('click', function(event) {
        var details = $(this).parents('.server-item').children('.details');
        
        // Slide de la partie détaillé uniquement si le serveur est dispo
        if (details.children().length > 0) {
            details.slideToggle();
            
            event.preventDefault();
        }
    });
});