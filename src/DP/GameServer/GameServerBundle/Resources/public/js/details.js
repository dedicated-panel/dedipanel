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