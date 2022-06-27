const DONE = 4;
const delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

jQuery(function ($) {

	function attachStatusMonitor($clubRow) {
		let find = $clubRow.find('.fm-club-code-input');
		const clubCode = find.val();
		const mainContent = $clubRow.find('.fm-main-content');
		const updateContent = $clubRow.find('.fm-update-content');
		mainContent.hide();
		updateContent.show();

		const progressBar = updateContent.closest('div').find('.fm-progressbar-inner');
		const logLine = updateContent.closest('div').find('.fm-progressbar-log');
		progressBar.removeClass('completed');
		progressBar.width("0%");
		progressBar.text('');
		logLine.text('...');
		let intervalStep = 1000;
		let lastIndex = 0;
		const id = setInterval(frame, intervalStep);

		function frame() {
			wp.apiRequest({
				path: 'fitet-monitor/v1/status',//todo APEX.api.url,
				type: 'GET',
				data: {clubCode: clubCode}
			}).done(response => {
				progressBar.removeClass('fail');
				progressBar.removeClass('warning');
				progressBar.removeClass('idle');

				try {
					let logs = response.logs.slice(lastIndex);

					lastIndex = response.logs.length;
					if (logs.length > intervalStep)
						logs = logs.slice(-intervalStep);

					(async () => {
						for (const log of logs) {
							progressBar.width(log.progress + "%");
							progressBar.text(log.progress + "%");
							logLine.text(log.message);
							await delay(intervalStep / logs.length);
						}


					})();


					if (response.status === 'ready') {
						progressBar.addClass('completed');
						/* progressBar.width(logs[logs.length - 1] + "%");
						 progressBar.text(logs[logs.length - 1] + "%");
						 logLine.text(logs[logs.length - 1].message);*/
						clearInterval(id);
						setTimeout(function () {
							updateContent.hide();
							mainContent.show();
							$clubRow.removeClass('fm-updating')
							$clubRow.addClass('fm-ready')
						}, 2000);

						wp.apiRequest({
							path: 'fitet-monitor/v1/club',
							type: 'GET',
							data: {clubCode: clubCode}
						}).done(response => {
							$clubRow.find('.column-lastUpdate').text(response.lastUpdate);

						});
						return;
					}
					if (response.status === 'fail') {
						progressBar.addClass('fail');
						clearInterval(id);
						// todo retry on fail
						/*setTimeout(function () {
							updateContent.hide();
							mainContent.show();
							$clubRow.removeClass('fm-updating')
							$clubRow.addClass('fm-ready')
						}, 2000);*/
						return;
					}


					if (logs.length === 0) {
						progressBar.addClass('idle');
						return;
					}


				} catch (e) {
					progressBar.addClass('warning');
					console.error(e);
				}


			});


		}
	}

	function update($clubRow) {

		let find = $clubRow.find('.fm-club-code-input');
		const clubCode = find.val();

		wp.apiRequest({
			path: 'fitet-monitor/v1/update',//todo APEX.api.url,
			type: 'POST',
			data: {clubCode: clubCode}
		});
		$clubRow.removeClass('fm-ready')
		$clubRow.removeClass('fm-new')
		$clubRow.addClass('fm-updating')
		attachStatusMonitor($clubRow);
	}

	$('.fm-club-cell').on('click', '.fm-btn-delete', function () {
		const $this = $(this);
		const $clubRow = $this.closest('td');
		$clubRow.find('.fm-main-content').hide();
		$clubRow.find('.fm-delete-content').show();
	}).on('click', '.fm-btn-update', function () {
		const $this = $(this);
		const $clubRow = $this.closest('tr');
		update($clubRow);
	});

	$('.fm-club-cell .fm-delete-content ').on('click', '.button-link', function () {
		const $this = $(this);
		const action = $this.data('value');
		const clubCode = $this.data('club-code');
		if (action === 'delete') {
			const url = new URL(window.location.href);
			url.searchParams.set('clubCode', clubCode);
			url.searchParams.set('action', 'delete');
			window.location.href = url.href;
		} else {
			const $clubRow = $this.closest('td');
			$clubRow.find('.fm-main-content').show();
			$clubRow.find('.fm-delete-content').hide();
		}
	});

	$('.fm-updating').each(function (i, el) {
		attachStatusMonitor($(el).closest('tr'))
	})
	$('.fm-new').each(function (i, el) {
		update($(el).closest('tr'))
	})
});

