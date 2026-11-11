/* IPTV Pro — JS minimal (menu mobile, animations, suivi des clics de commande). */
(function () {
	'use strict';
	document.documentElement.classList.add('js');

	// Menu mobile.
	var toggle = document.querySelector('.nav-toggle');
	var nav = document.getElementById('primary-nav');
	if (toggle && nav) {
		toggle.addEventListener('click', function () {
			var open = nav.classList.toggle('is-open');
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
		nav.addEventListener('click', function (e) {
			if (e.target.closest('a')) {
				nav.classList.remove('is-open');
				toggle.setAttribute('aria-expanded', 'false');
			}
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && nav.classList.contains('is-open')) {
				nav.classList.remove('is-open');
				toggle.setAttribute('aria-expanded', 'false');
				toggle.focus();
			}
		});
	}

	// Apparition progressive des cartes.
	var targets = document.querySelectorAll('.plan, .feature, .step, .device, .guide-card, .card, .testimonial');
	if ('IntersectionObserver' in window && targets.length) {
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					entry.target.classList.add('is-visible');
					io.unobserve(entry.target);
				}
			});
		}, { rootMargin: '0px 0px -40px 0px' });
		targets.forEach(function (el) { el.classList.add('reveal'); io.observe(el); });
	}

	// Événements GA4 (si gtag est chargé) : clics « commander » et WhatsApp.
	document.addEventListener('click', function (e) {
		var link = e.target.closest('a');
		if (!link || typeof window.gtag !== 'function') return;
		if (link.dataset.plan) {
			window.gtag('event', 'begin_checkout', { items: [{ item_id: link.dataset.plan }] });
		} else if (link.href.indexOf('wa.me') !== -1) {
			window.gtag('event', 'contact_whatsapp');
		}
	});
})();
