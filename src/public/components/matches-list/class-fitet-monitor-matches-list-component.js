jQuery(function ($) {

	let body = $('html, body');
	body.animate({
		scrollTop: 0
	}, 0);

	body.animate({
		scrollTop: $("#fm-match-now").offset().top - 10
	}, 200);


});
