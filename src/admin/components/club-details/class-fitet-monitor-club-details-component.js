jQuery(function ($) {

	$('#fm-championships-table').on('dynatable:beforeUpdate', function () {
		console.log('beforeUpdate');
		$('.fm-toggle').unbind();
		$('.fm-update-single-championship').unbind('click')

	}).on('dynatable:afterUpdate', function () {
		console.log('afterUpdate');
		$('.fm-toggle').bind('click', fmToggle)
		$('.fm-update-single-championship').bind('click', updateSingleChampionship)

	})

	$('.fm-download-full-history').click(async function (e) {
		e.preventDefault();
		const clubCode = $(this).data('club-code');
		const seasonIdList = $(this).data('season-id-list');
		const mode = 'championships';

		console.log(clubCode, seasonIdList);

		for (const seasonId of seasonIdList) {

			let promise = new Promise((resolve, reject) => {
				wp.apiRequest({
					path: 'fitet-monitor/v1/update',
					type: 'POST',
					data: {clubCode, mode, seasonId}
				})
					.done(x => {
						console.log(seasonId, x);
						resolve(x);
					})
					.fail(r => {
						console.log(f, e);
						reject(r);
					});

			});
			await promise;
		}

		alert('done full cronology download');
// wait until the promise resolves (*)

		/*wp.apiRequest({
			path: 'fitet-monitor/v1/update',
			type: 'POST',
			data: {clubCode: clubCode, mode: 'full-history'}
		});

		const url = new URL(window.location.href);
		url.searchParams.delete('clubCode');
		url.searchParams.delete('mode');
		window.location.href = url.href;*/


	});

	function updateSingleChampionship(event) {
		event.preventDefault();
		$('#fm-championships-table .fm-update-single-championship').prop("disabled", true);
		const clubCode = $(this).data('club-code');
		const seasonId = $(this).data('season-id');
		const mode = 'championships'
		console.log('updating ', {clubCode, mode, seasonId})

		wp.apiRequest({
			path: 'fitet-monitor/v1/update',
			type: 'POST',
			data: {clubCode, mode, seasonId}
		})
			.done(x => {
				$(event.currentTarget).replaceWith("ok");
				console.log('done', x);
			})
			.fail((e, f) => console.log(f, e))
			.always(y => {
				$('#fm-championships-table .fm-update-single-championship').prop("disabled", false);
				console.log('Finish.', y);
			});
	}

	function fmToggle(event) {
		let parentNode = event.target.parentNode;
		parentNode.classList.toggle('fm-closed');
		parentNode.nextElementSibling.classList.toggle('fm-closed');
	}


});

