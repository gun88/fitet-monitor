jQuery(function ($) {


    $('#fm-create-page').on('click', async function(e){

        let clubCode = $(this).data('club');


        $('#fm-overlay').show();
        $('#fm-progressbar').progressbar({ value: 0, max: 100 });
        $('#fm-label').text('Creazione pagine...');


        wp.apiRequest({
            path: 'fitet-monitor/v1/create_pages',
            type: 'POST',
            data: {clubCode}
        })
            .done(x => {
                console.log('done', x);
                alert('Pagine create');
            })
            .fail((e, f) => {
                alert('Errore durante la creazione delle pagine');
                console.log(f, e);
            })
            .always(y => {
                console.log('Finish.', y);
                $('#fm-progressbar').progressbar('value', 1);
                $('#fm-overlay').hide();

            });


    });

    $(document).on('click', '.fm-upload-btn', function () {
        const $uploader = $(this).closest('.fm-uploader');
        const $file = $uploader.find('.fm-file');
        const $status = $uploader.find('.fm-status');

        $file.data('uploader', $uploader);
        $status.text('');
        $file.val('').trigger('click');
    });

// selezione file => upload
    $(document).on('change', '.fm-file', function () {
        if (!this.files || !this.files.length) return;

        const f = this.files[0];
        const $uploader = $(this).data('uploader') || $(this).closest('.fm-uploader');
        const $status = $uploader.find('.fm-status');
        const code = $(this).data('code'); // preso dal tuo data-code

        // solo png/jpg/jpeg
        const okMime = ['image/png', 'image/jpeg'].includes(f.type);
        const ext = (f.name.split('.').pop() || '').toLowerCase();
        const okExt = ['png', 'jpg', 'jpeg'].includes(ext);
        if (!okMime || !okExt) {
            $status.text('Formato non valido. Usa PNG o JPG.');
            this.value = '';
            return;
        }

        const fd = new FormData();
        fd.append('file', f, f.name);
        fd.append('code', code);

        $status.text('Caricamento…');

        $.ajax({
            url: (window.wpApiSettings?.root || '/index.php?rest_route=/') + 'fitet-monitor/v1/upload-player-image',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            beforeSend: function (xhr) {
                if (window.wpApiSettings?.nonce) {
                    xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
                }
            },
            success: function (res) {
                $status.text('Immagine caricata ✅');

                // 1) trova il <tr> dell’uploader corrente
                const $tr = $uploader.closest('tr');
                // 2) trova l'immagine target nella cella .fm-player-image
                const $img = $tr.find('.fm-player-image img').first();

                if ($img.length && res?.url) {
                    // cache-busting per vedere subito l’aggiornamento
                    const freshUrl = res.url + (res.url.includes('?') ? '&' : '?') + 'v=' + Date.now();

                    // se usi srcset/sizes di WP, rimuovili per forzare la nuova src
                    $img.removeAttr('srcset').removeAttr('sizes');

                    // se usi lazyload con data-src, aggiorna anche quello
                    if ($img.attr('data-src')) $img.attr('data-src', freshUrl);

                    $img.attr('src', freshUrl);
                } else {
                    // fallback: mostra un link
                    $('<div/>').append(
                        $('<a/>', {href: res?.url || '#', text: 'Apri immagine', target: '_blank', rel: 'noopener'})
                    ).appendTo($status);
                }

                // reset input per poter ricaricare lo stesso file
                $uploader.find('.fm-file').val('');
            },
            error: function (xhr) {
                let msg = 'Errore upload';
                try {
                    const data = JSON.parse(xhr.responseText);
                    if (data?.message) msg += ': ' + data.message;
                } catch (e) {
                }
                $status.text(msg);
            }
        });
    });

    $(document).on('click', '.fm-default-btn', function () {
        const $btn = $(this);
        const code = $btn.data('code');
        const $uploader = $btn.closest('.fm-uploader');
        const $status = $uploader.find('.fm-status');

        $status.text('Ripristino immagine…');

        $.ajax({
            url: (window.wpApiSettings?.root || '/index.php?rest_route=/') + 'fitet-monitor/v1/delete-player-image',
            method: 'POST',
            data: { code: code },
            beforeSend: function (xhr) {
                if (window.wpApiSettings?.nonce) {
                    xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
                }
            },
            success: function (res) {
                $status.text('Immagine ripristinata ✅');

                // trova il <tr> e aggiorna l’immagine con quella di default
                const $tr  = $uploader.closest('tr');
                const $img = $tr.find('.fm-player-image img').first();
                if ($img.length && res?.url) {
                    const freshUrl = res.url + (res.url.includes('?') ? '&' : '?') + 'v=' + Date.now();
                    $img.removeAttr('srcset').removeAttr('sizes');
                    if ($img.attr('data-src')) $img.attr('data-src', freshUrl);
                    $img.attr('src', freshUrl);
                }
            },
            error: function (xhr) {
                let msg = 'Errore reset';
                try {
                    const data = JSON.parse(xhr.responseText);
                    if (data?.message) msg += ': ' + data.message;
                } catch(e){}
                $status.text(msg);
            }
        });
    });

	$('#fm-championships-table').on('dynatable:beforeUpdate', function () {
		// console.log('beforeUpdate');
		$('.fm-toggle').unbind();
		$('.fm-update-single-championship').unbind('click')
		$('.fm-reset-single-championship').unbind('click')

	}).on('dynatable:afterUpdate', function () {
		// console.log('afterUpdate');
		$('.fm-toggle').bind('click', fmToggle)
		$('.fm-update-single-championship').bind('click', updateSingleChampionship)
		$('.fm-reset-single-championship').bind('click', resetSingleChampionship)

	})

    $('.fm-download-full-history').on('click', async function(e){
        e.preventDefault();

        const clubCode = $(this).data('club-code');
        let seasonIdList = $(this).data('season-id-list');
        const mode = 'championships';

        if (typeof seasonIdList === 'string') {
            seasonIdList = seasonIdList.split(',').map(s=>s.trim()).filter(Boolean);
        }

        const total = seasonIdList.length;
        if (!total) { alert('Nessuna seasonId'); return; }

        // mostra overlay
        $('#fm-overlay').show();
        $('#fm-progressbar').progressbar({ value: 0, max: 100 });
        $('#fm-label').text('Inizio download…');

        try {
            for (let i=0; i<total; i++) {
                const seasonId = seasonIdList[i];
                $('#fm-label').text('Downloading season: ' + seasonId);

                await new Promise((resolve,reject)=>{
                    wp.apiRequest({
                        path:'fitet-monitor/v1/update',
                        type:'POST',
                        data:{clubCode, mode, seasonId}
                    })
                        .done(x=>{
                            const pct = Math.round(((i+1)/total)*100);
                            $('#fm-progressbar').progressbar('value', pct);
                            resolve(x);
                        })
                        .fail(err=>reject(err));
                });
            }
            alert('Download completato');
        } catch(e) {
            alert('Errore durante il download');
            console.error(e);
        } finally {
            $('#fm-overlay').hide();
        }
    });


    function updateSingleChampionship(event) {
		event.preventDefault();
		$('#fm-championships-table .fm-update-single-championship').prop("disabled", true);
		const clubCode = $(this).data('club-code');
		const seasonId = $(this).data('season-id');
		const mode = 'championships'
		console.log('updating ', {clubCode, mode, seasonId})

        $('#fm-label').text('Inizio download…');
        $('#fm-overlay').show();
        $('#fm-progressbar').progressbar({ value: 0, max: 1 });
        $('#fm-label').text('Downloading season: ' + seasonId);


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
                $('#fm-progressbar').progressbar('value', 1);
                $('#fm-overlay').hide();

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


});

