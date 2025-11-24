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

    let step = 10;          // visible window size
    let startIndex = 0;     // current start index
    const minStep = 4;      // minimum window size

    // --- DOM buttons ---
    const $btnPrev = $('#fm-chart-prev');
    const $btnNext = $('#fm-chart-next');
    const $btnIncrease = $('#fm-chart-increase');
    const $btnDecrease = $('#fm-chart-decrease');

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
        updateButtons();
    }

    // --- Button enable/disable logic ---
    function updateButtons() {
        const maxStart = Math.max(0, fullLabels.length - step);

        // Disable prev if at start
        $btnPrev.prop('disabled', startIndex <= 0);

        // Disable next if at end
        $btnNext.prop('disabled', startIndex >= maxStart);

        // Disable decrease if at min step
        $btnDecrease.prop('disabled', step <= minStep);

        // Disable increase if window already covers all data
        $btnIncrease.prop('disabled', step >= fullLabels.length);
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
            interaction: { mode: 'index', intersect: false },
            stacked: false,
            plugins: { title: { display: false, text: 'History' } },
            scales: {
                x: { reverse: true },
                y: {
                    type: 'linear', display: true, position: 'left',
                    max: bestPoints, min: 0
                },
                y1: {
                    type: 'linear', display: true, position: 'right',
                    reverse: true, min: bestRanking,
                    grid: { drawOnChartArea: false }
                },
            }
        },
    };

    const chart = new Chart(ctx, config);

    // --- Initialize chart ---
    updateWindow();

    // --- Button handlers ---
    $btnNext.on('click', function () {
        startIndex += step;
        updateWindow();
    });

    $btnPrev.on('click', function () {
        startIndex -= step;
        updateWindow();
    });

    $btnIncrease.on('click', function () {
        step += 4;
        if (step > fullLabels.length) step = fullLabels.length;
        updateWindow();
    });

    $btnDecrease.on('click', function () {
        step -= 4;
        if (step < minStep) step = minStep;
        updateWindow();
    });
});
