jQuery(function ($) {
	$('#my-table').dynatable({
		features: {
			paginate: true,
			sort: true,
			pushState: false,
			search: true,
			recordCount: true,
			perPageSelect: true
		}
	});
});
