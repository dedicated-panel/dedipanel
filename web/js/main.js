$(function() {
    if ($('#sidebar ul li.tree').length > 0) {
        $('#sidebar ul li.tree').each(function (el, id) {
            var el = $(this);
            var submenu = el.children('ul.menu_level_1');
            
            el.children('span').bind('click', function () {
                el.toggleClass('in');
                submenu.slideToggle();
            });
        });
    }
});
