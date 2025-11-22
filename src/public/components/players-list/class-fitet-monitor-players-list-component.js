jQuery(function ($) {

	const mainContent = $('.fm-player-list-main-content');

	function toggleFmClass(className, checked) {
		if (checked)
			mainContent.removeClass(className);
		else
			mainContent.addClass(className);
	}

	$('.fm-player-list-filter #fm-pt-it').change(function () {
		toggleFmClass('fm-hide-italiani', this.checked);
	});
	$('.fm-player-list-filter #fm-pt-st').change(function () {
		toggleFmClass('fm-hide-stranieri', this.checked);
	});
	$('.fm-player-list-filter #fm-pt-pr').change(function () {
		toggleFmClass('fm-hide-provvisori', this.checked);
	});
	$('.fm-player-list-filter #fm-pt-pz').change(function () {
		toggleFmClass('fm-hide-promozionali', this.checked);
	});
	$('.fm-player-list-filter #fm-pt-fq').change(function () {
		toggleFmClass('fm-hide-fuori-quadro', this.checked);
	});

});
