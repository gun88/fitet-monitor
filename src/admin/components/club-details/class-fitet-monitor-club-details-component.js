// fm-download-full-history

jQuery(function ($) {

	$('.fm-download-full-history').click(function (e) {
		e.preventDefault();
		const clubCode = $(this).data('club-code');
		console.log(clubCode)

		wp.apiRequest({
			path: 'fitet-monitor/v1/update',//todo APEX.api.url,
			type: 'POST',
			data: {clubCode: clubCode, mode: 'full-history'}
		});

		const url = new URL(window.location.href);
		url.searchParams.delete('clubCode');
		url.searchParams.delete('mode');
		window.location.href = url.href;


	})

});
