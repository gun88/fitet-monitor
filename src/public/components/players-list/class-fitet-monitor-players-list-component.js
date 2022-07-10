jQuery(function ($) {

	$('#fm-player-list-filter').change(function () {
		const filter = $(this).find(":selected").val();
		const url = new URL(window.location.href);
		if (filter === 'none') {
			url.searchParams.delete('filter');

		} else {
			url.searchParams.set('filter', filter);
		}
		window.location.href = url.href;
	});
});
