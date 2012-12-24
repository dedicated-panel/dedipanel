$(function() {    
    $('a.slide').bind('click', function(event) {
        var serverId = $(this).parents('.server-item').attr('rel');        
        $('.details[rel="' + serverId + '"]').slideToggle();
        
        event.preventDefault();
    });
});