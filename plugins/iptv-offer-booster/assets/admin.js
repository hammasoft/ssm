/* IPTV Offer Booster — admin : offres prédéfinies + sélection d'image. */
jQuery(function ($) {
	var presets = window.IOB_PRESETS || {};
	var fields = ['badge', 'title', 'text', 'old_price', 'new_price', 'coupon', 'button'];

	$('.iob-preset').on('click', function () {
		var p = presets[$(this).data('preset')];
		if (!p) return;
		fields.forEach(function (f) {
			if (typeof p[f] !== 'undefined') $('[name="iob_settings[' + f + ']"]').val(p[f]);
		});
		if (p.template) $('[name="iob_settings[template]"][value="' + p.template + '"]').prop('checked', true);
		if (p.style) $('[name="iob_settings[style]"][value="' + p.style + '"]').prop('checked', true);
		if (p.countdown) {
			$('[name="iob_settings[countdown]"]').prop('checked', true);
			var $end = $('#iob-end');
			if (!$end.val()) {
				// Propose une fin dans 3 jours : à ajuster selon votre vraie promotion.
				var d = new Date(Date.now() + 3 * 86400000);
				var pad = function (n) { return (n < 10 ? '0' : '') + n; };
				$end.val(d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + 'T23:59');
			}
		}
		$('.iob-presets .button').removeClass('button-primary');
		$(this).addClass('button-primary');
	});

	$('.iob-media').on('click', function (e) {
		e.preventDefault();
		var frame = wp.media({ title: 'Image de l’offre', multiple: false, library: { type: 'image' } });
		frame.on('select', function () {
			$('#iob-image').val(frame.state().get('selection').first().toJSON().url);
		});
		frame.open();
	});
});
