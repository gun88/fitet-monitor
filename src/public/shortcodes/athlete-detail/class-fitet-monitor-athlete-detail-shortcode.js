jQuery(function ($) {

	$('.fm-sc-athlete-detail-menu a').click(function (event) {
		event.preventDefault();

		$('html, body').animate({
			scrollTop: $($.attr(this, 'href')).offset().top
		}, 500);
	});
});
