/* Beffroi Dépannage — menu mobile + suivi des clics d'appel (aucune dépendance). */
(function () {
	'use strict';

	var toggle = document.querySelector('.nav-toggle');
	var nav = document.getElementById('nav-principale');
	if (toggle && nav) {
		toggle.addEventListener('click', function () {
			var open = nav.classList.toggle('is-open');
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	}

	// Chaque clic sur un lien tel: est envoyé à GTM / GA4 (conversion « appel »).
	document.addEventListener('click', function (e) {
		var link = e.target.closest && e.target.closest('a[href^="tel:"]');
		if (!link) { return; }
		var payload = { event: 'call_click', call_location: link.getAttribute('data-call') || 'inline', page_path: location.pathname };
		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push(payload);
		if (typeof window.gtag === 'function') {
			window.gtag('event', 'call_click', payload);
		}
	});
})();
