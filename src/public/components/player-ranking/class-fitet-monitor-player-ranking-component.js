jQuery(function ($) {
    let chartElement = document.getElementById('fm-sc-player-detail-chart');
    if (!chartElement) {
        return;
    }

    const ctx = chartElement.getContext('2d');
    let chartEl = $('#fm-sc-player-detail-chart');

    let fullLabels   = chartEl.data('labels')   || [];
    let fullPoints   = chartEl.data('points')   || [];
    let fullRankings = chartEl.data('rankings') || [];
    let bestRanking  = chartEl.data('best-ranking');
    let bestPoints   = chartEl.data('best-points');

    let step = 12;          // visible window size
    let startIndex = 0;     // current start index

    // --- Update function ---
    function updateWindow() {
        const maxStart = Math.max(0, fullLabels.length - step);

        // Clamp start index
        if (startIndex < 0) startIndex = 0;
        if (startIndex > maxStart) startIndex = maxStart;

        const endIndex = startIndex + step;

        const labels   = fullLabels.slice(startIndex, endIndex);
        const points   = fullPoints.slice(startIndex, endIndex);
        const rankings = fullRankings.slice(startIndex, endIndex);

        chart.data.labels = labels;
        chart.data.datasets[0].data = points;
        chart.data.datasets[1].data = rankings;

        chart.update();
    }

    const tension = 0.4;
    const cubicInterpolationMode = 'monotone';
    const data = {
        labels: [],
        datasets: [
            {
                label: 'Points',
                data: [],
                borderColor: '#0288d1',
                yAxisID: 'y',
                cubicInterpolationMode: cubicInterpolationMode,
                tension: tension
            },
            {
                label: 'Ranking',
                data: [],
                borderColor: '#ff4081',
                yAxisID: 'y1',
                cubicInterpolationMode: cubicInterpolationMode,
                tension: tension
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
                },
                y: {
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
                    grid: {
                        drawOnChartArea: false,
                    },
                },
            }
        },
    };

    const chart = new Chart(ctx, config);

    // init first window
    updateWindow();

    // BUTTONS
    $('#fm-chart-next').on('click', function () {
        startIndex -= step;
        updateWindow();
    });

    $('#fm-chart-prev').on('click', function () {
        startIndex += step;
        updateWindow();
    });

    // --- Window size adjustment ---
    $('#fm-chart-increase').on('click', function () {
        step += 4;
        if (step > fullLabels.length) step = fullLabels.length; // prevent overflow
        updateWindow();
    });

    $('#fm-chart-decrease').on('click', function () {
        step -= 4;
        if (step < 4) step = 4; // minimum window size
        updateWindow();
    });
});
