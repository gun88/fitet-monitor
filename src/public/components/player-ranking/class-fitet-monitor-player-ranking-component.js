jQuery(function ($) {
    let chartElement = document.getElementById('fm-sc-player-detail-chart');
    if (!chartElement) {
        return;
    }
    const ctx = chartElement.getContext('2d');
	let chart = $('#fm-sc-player-detail-chart');
	let labels = chart.data('labels');
	let points = chart.data('points');
	let rankings = chart.data('rankings');
	let bestRanking = chart.data('best-ranking');
	let bestPoints = chart.data('best-points');

	const data = {
		labels: labels,
		datasets: [
			{
				label: 'Points',
				data: points,
				borderColor: '#0288d1',
				yAxisID: 'y',
			},
			{
				label: 'Ranking',
				data: rankings,
				borderColor: '#ff4081',
				yAxisID: 'y1',
			}
		]
	};


	const config = {
		type: 'line',
		data: data,
		options: {
			responsive: true,
			interaction: {
				mode: 'index',
				intersect: false,
			},
			stacked: false,
			plugins: {
				title: {
					display: false,
					text: 'History'
				}
			},
			scales: {
				x: {
					reverse: true,

				}, y: {
					type: 'linear',
					display: true,
					position: 'left',
					max: bestPoints,
					min: 0

				},
				y1: {
					type: 'linear',
					display: true,
					position: 'right',
					reverse: true,
					min: bestRanking,

					// grid line settings
					grid: {
						drawOnChartArea: false, // only want the grid lines for one axis to show up
					},
				},
			}
		},
	};
	new Chart(ctx, config);
});
