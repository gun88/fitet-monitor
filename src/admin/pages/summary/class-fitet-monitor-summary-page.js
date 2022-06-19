jQuery(function ($) {
	$('#fm-summary-page').on('click', '#fm-btn-add', function () {
		const url = new URL(window.location.href);
		url.searchParams.set('mode', 'club');
		window.location.href = url.href;
	});
});
