$(document).ready(function () {
	var title;
	$('img[data-toggle="tooltip"]').each(function() {
		title = $(this).data('title');	
		$(this).tooltip({ 
			template: '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-title">' + title + '</div><div class="tooltip-inner"></div></div>'  
		});

	});

} );
