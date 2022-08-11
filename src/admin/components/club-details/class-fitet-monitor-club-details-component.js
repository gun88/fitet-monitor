jQuery(function ($) {

	$('.fm-download-full-history').click(function (e) {
		e.preventDefault();
		const clubCode = $(this).data('club-code');

		wp.apiRequest({
			path: 'fitet-monitor/v1/update',
			type: 'POST',
			data: {clubCode: clubCode, mode: 'full-history'}
		});

		const url = new URL(window.location.href);
		url.searchParams.delete('clubCode');
		url.searchParams.delete('mode');
		window.location.href = url.href;


	});

});

function fmToggle(event) {
	let parentNode = event.target.parentNode;
	parentNode.classList.toggle('fm-closed');
	parentNode.nextElementSibling.classList.toggle('fm-closed');
}
