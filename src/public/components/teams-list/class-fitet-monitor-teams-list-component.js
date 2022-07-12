jQuery(function ($) {

	$('#fm-team-list-season-filter').change(function () {
		const filter = $(this).find(":selected").val();
		const url = new URL(window.location.href);
		url.searchParams.set('season', filter);
		window.location.href = url.href;
	});
});
