jQuery(function ($) {


	let recentAnchor = $("#fm-match-now");
	if (recentAnchor.length) {
		let body = $('html, body');
		body.animate({
			scrollTop: 0
		}, 0);

		body.animate({
			scrollTop: recentAnchor.offset().top - 10
		}, 200);
	}


	$('#fm-match-list-filter').change(function () {
		const filter = $(this).find(":selected").val();
		const url = new URL(window.location.href);
		url.searchParams.set('season', filter);
		window.location.href = url.href;
	});

});
