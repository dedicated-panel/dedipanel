$(document).ready(function() {	
	$('a.slide').each(function(index, el) {
		var toSlideSel = 'div#' + el.id + '.details';
		$(toSlideSel).hide();
		
		$(el).bind('click', function(event) {
            event.preventDefault();
			$(toSlideSel).slideToggle();
		});
	});
});