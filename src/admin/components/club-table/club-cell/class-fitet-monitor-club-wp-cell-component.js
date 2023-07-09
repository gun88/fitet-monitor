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
                        $clubRow.find('.fm-update-actions-container').show();
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

    function update($clubRow, mode = null, seasonId = null) {

        let find = $clubRow.find('.fm-club-code-input');
        $clubRow.find('.fm-update-actions-container').hide();
        const clubCode = find.val();

        wp.apiRequest({
            path: 'fitet-monitor/v1/update',
            type: 'POST',
            data: {clubCode, mode, seasonId}
        });
        $clubRow.removeClass('fm-ready')
        $clubRow.removeClass('fm-new')
        $clubRow.addClass('fm-updating')
        attachStatusMonitor($clubRow);
    }

    function exportJson($clubRow) {

        let find = $clubRow.find('.fm-club-code-input');
        // $clubRow.find('.fm-btn-export').disable();
        const clubCode = find.val();
        wp.apiRequest({
            path: 'fitet-monitor/v1/export',
            type: 'POST',
            data: {clubCode}
        }).done(response => {
            const today = new Date();
            const date = today.getFullYear() + '-' + (today.getMonth() + 1) + '-' + today.getDate();
            const time = today.getHours() + "." + today.getMinutes() + "." + today.getSeconds();
            const dataLabel = date + '_' + time;
            const link = document.createElement("a");
            const file = new Blob([response], {type: 'text/plain'});
            link.href = URL.createObjectURL(file);
            link.download = "export_" + clubCode + "_" + dataLabel + ".json";
            link.click();
            URL.revokeObjectURL(link.href);
            //    $clubRow.find('.fm-btn-export').enable();

        });

    }

    function resetRid($clubRow) {

        let find = $clubRow.find('.fm-club-code-input');
        // $clubRow.find('.fm-btn-export').disable();
        const clubCode = find.val();
        wp.apiRequest({
            path: 'fitet-monitor/v1/resetRid',
            type: 'GET',
            data: {clubCode}
        }).done(response => {
            alert("done");
            console.log('resetRid response', response)
        }).error(response => {
            alert("ERROR");
            console.error('resetRid response', response)
        });

    }

    $('.fm-club-table-form').submit(function (e, a, b, c) {
        const $form = $(e.currentTarget);
        const action = $form.find('select[name="action"] option:selected').val();
        if (action === 'update') {
            e.preventDefault();
            $form.find('input[name="clubCode[]"]')
                .each((x, el) => {
                    if (el.checked) {
                        const $this = $(el);
                        const $clubRow = $this.closest('tr');
                        update($clubRow);
                    }
                });
        }
    })


    $('.fm-club-cell').on('click', '.fm-btn-delete', function () {
        const $this = $(this);
        const $clubRow = $this.closest('td');
        $clubRow.find('.fm-main-content').hide();
        $clubRow.find('.fm-delete-content').show();
    }).on('click', '.fm-btn-update', function () {
        const $this = $(this);
        const $clubRow = $this.closest('tr');
        update($clubRow, $this.data('mode'));
    }).on('click', '.fm-btn-export', function () {
        const $this = $(this);
        const $clubRow = $this.closest('tr');
        exportJson($clubRow);
    }).on('click', '.fm-btn-reset-rid', function () {
        const $this = $(this);
        const $clubRow = $this.closest('tr');
        resetRid($clubRow);
    });

    $('.fm-club-cell .fm-delete-content').on('click', '.button-link', function () {
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
    $('.fm-club-cell .fm-update-content').on('click', '.fm-btn-cancel', function () {
        const $this = $(this);
        const $clubRow = $this.closest('td');
        $clubRow.find('.fm-main-content').show();
        $clubRow.find('.fm-update-content').hide();

    });

    $('.fm-updating').each(function (i, el) {
        attachStatusMonitor($(el).closest('tr'))
    })
    $('.fm-new').each(function (i, el) {
        update($(el).closest('tr'))
    })
});

