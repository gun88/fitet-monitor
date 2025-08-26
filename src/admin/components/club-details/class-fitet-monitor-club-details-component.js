jQuery(function ($) {

	$('#fm-championships-table').on('dynatable:beforeUpdate', function () {
		console.log('beforeUpdate');
		$('.fm-toggle').unbind();
		$('.fm-update-single-championship').unbind('click')
		$('.fm-reset-single-championship').unbind('click')

	}).on('dynatable:afterUpdate', function () {
		console.log('afterUpdate');
		$('.fm-toggle').bind('click', fmToggle)
		$('.fm-update-single-championship').bind('click', updateSingleChampionship)
		$('.fm-reset-single-championship').bind('click', resetSingleChampionship)

	})

	$('.fm-download-full-history').click(async function (e) {
		e.preventDefault();
		const clubCode = $(this).data('club-code');
		const seasonIdList = $(this).data('season-id-list');
		const mode = 'championships';

		console.log(clubCode, seasonIdList);

		for (const seasonId of seasonIdList) {
            console.log(seasonId)

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

	function resetSingleChampionship(event) {
		event.preventDefault();
		$('#fm-championships-table .fm-reset-single-championship').prop("disabled", true);
		const clubCode = $(this).data('club-code');
		const seasonId = $(this).data('season-id');
		const mode = 'championships'
		console.log('resetting ', {clubCode, mode, seasonId})

		wp.apiRequest({
			path: 'fitet-monitor/v1/reset',
			type: 'POST',
			data: {clubCode, mode, seasonId}
		})
			.done(x => {
				$(event.currentTarget).replaceWith("ok");
				console.log('done', x);
			})
			.fail((e, f) => console.log(f, e))
			.always(y => {
				$('#fm-championships-table .fm-reset-single-championship').prop("disabled", false);
				console.log('Finish.', y);
			});
	}

        function fmToggle(event) {
                let parentNode = event.target.parentNode;
                parentNode.classList.toggle('fm-closed');
                parentNode.nextElementSibling.classList.toggle('fm-closed');
        }

        $(document).on('change', '.fm-player-visible', function () {
                const playerId = $(this).data('player-id');
                const visible = $(this).is(':checked') ? 1 : 0;
                wp.apiRequest({
                        path: 'fitet-monitor/v1/player/visible',
                        type: 'POST',
                        data: {playerId, visible}
                });
        });

        $(document).on('submit', '.fm-player-upload-form', function (e) {
                e.preventDefault();
                const playerId = $(this).data('player-id');
                const fileInput = $(this).find('input[type=file]')[0];
                if (!fileInput || !fileInput.files.length) {
                        return;
                }
                const formData = new FormData();
                formData.append('playerId', playerId);
                formData.append('image', fileInput.files[0]);
                $.ajax({
                        url: wpApiSettings.root + 'fitet-monitor/v1/player/image',
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        beforeSend: function (xhr) {
                                xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
                        }
                });
        });


});

