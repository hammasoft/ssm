/* IPTV Offer Booster — déclenchement, fermeture, compte à rebours, copie du code, statistiques. */
(function () {
	'use strict';
	var cfg = window.IOB || {};
	var store = {
		get: function (k) { try { return window.localStorage.getItem(k); } catch (e) { return null; } },
		set: function (k, v) { try { window.localStorage.setItem(k, v); } catch (e) {} }
	};

	function track(e) {
		if (!cfg.track || cfg.preview) return;
		try {
			var body = new FormData();
			body.append('e', e);
			if (navigator.sendBeacon) navigator.sendBeacon(cfg.track, body);
			else fetch(cfg.track, { method: 'POST', body: body, keepalive: true });
		} catch (err) {}
		if (typeof window.gtag === 'function') {
			window.gtag('event', e === 'view' ? 'view_promotion' : 'select_promotion', { promotion_id: cfg.key });
		}
	}

	// Compte à rebours sur une vraie date de fin (masque l'offre à l'échéance).
	function initCountdown(root) {
		var el = root.querySelector('.iob-countdown');
		if (!el) return;
		var end = parseInt(el.getAttribute('data-end'), 10) * 1000;
		var pad = function (n) { return (n < 10 ? '0' : '') + n; };
		function tick() {
			var diff = Math.max(0, end - Date.now());
			if (!diff) { root.hidden = true; return; }
			var s = Math.floor(diff / 1000);
			var vals = { d: Math.floor(s / 86400), h: Math.floor(s % 86400 / 3600), m: Math.floor(s % 3600 / 60), s: s % 60 };
			Object.keys(vals).forEach(function (u) {
				var b = el.querySelector('[data-u="' + u + '"]');
				if (b) b.textContent = pad(vals[u]);
			});
			setTimeout(tick, 1000);
		}
		tick();
	}

	function initCopy(root) {
		root.querySelectorAll('.iob-copy').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var code = btn.getAttribute('data-code');
				var done = function () {
					var label = btn.textContent;
					btn.textContent = btn.getAttribute('data-done');
					setTimeout(function () { btn.textContent = label; }, 2000);
				};
				if (navigator.clipboard) navigator.clipboard.writeText(code).then(done, done);
				else { var t = document.createElement('textarea'); t.value = code; document.body.appendChild(t); t.select(); try { document.execCommand('copy'); } catch (e) {} t.remove(); done(); }
			});
		});
	}

	// Shortcodes [iptv_offre] (toujours visibles).
	document.querySelectorAll('.iob--inline').forEach(function (el) { initCountdown(el); initCopy(el); });

	var root = document.querySelector('.iob:not(.iob--inline)');
	if (!root) return;

	var isMobile = window.matchMedia('(max-width: 767px)').matches;
	if ((cfg.devices === 'mobile' && !isMobile) || (cfg.devices === 'desktop' && isMobile)) return;

	var closedAt = parseInt(store.get(cfg.key) || '0', 10);
	if (!cfg.preview && closedAt && (cfg.frequency === 0 ? false : Date.now() - closedAt < cfg.frequency * 86400000)) return;

	var tpl = root.getAttribute('data-template');
	var isModal = tpl === 'popup' || tpl === 'exit';
	var lastFocus = null;
	var shown = false;

	// Placement : barre en haut du body, bannière après l'en-tête du site.
	if (tpl === 'topbar') {
		document.body.insertBefore(root, document.body.firstChild);
	} else if (tpl === 'banner') {
		var header = document.querySelector('.site-header, header[role="banner"], body > header, header');
		var main = document.querySelector('main, #content, .site-main');
		if (header && header.parentNode && !(main && header.contains(main))) header.parentNode.insertBefore(root, header.nextSibling);
		else if (main) main.insertBefore(root, main.firstChild);
	}

	function show() {
		if (shown) return;
		shown = true;
		root.hidden = false;
		initCountdown(root);
		initCopy(root);
		track('view');
		if (isModal) {
			lastFocus = document.activeElement;
			document.documentElement.style.overflow = 'hidden';
			var focusable = root.querySelector('.iob-dialog');
			if (focusable) focusable.focus({ preventScroll: true });
		}
	}

	function close() {
		root.hidden = true;
		store.set(cfg.key, String(Date.now()));
		if (isModal) {
			document.documentElement.style.overflow = '';
			if (lastFocus && lastFocus.focus) lastFocus.focus();
		}
	}

	root.addEventListener('click', function (e) {
		if (e.target.closest('.iob-close, [data-iob-close]')) close();
		if (e.target.closest('[data-iob-click]')) { track('click'); store.set(cfg.key, String(Date.now())); }
	});
	document.addEventListener('keydown', function (e) {
		if (root.hidden) return;
		if (e.key === 'Escape' && (isModal || tpl === 'slidein')) close();
		if (e.key === 'Tab' && isModal) {
			var f = root.querySelectorAll('a[href], button:not([disabled])');
			if (!f.length) return;
			var first = f[0], last = f[f.length - 1];
			if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
			else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
		}
	});

	if (cfg.preview || tpl === 'topbar' || tpl === 'banner') { show(); return; }

	if (tpl === 'exit') {
		// Ordinateur : souris qui sort par le haut. Mobile : remontée rapide après défilement.
		document.addEventListener('mouseout', function (e) {
			if (!e.relatedTarget && e.clientY <= 0) show();
		});
		var lastY = window.scrollY, lastT = Date.now();
		window.addEventListener('scroll', function () {
			var y = window.scrollY, t = Date.now();
			if (y > 600 && lastY - y > 120 && t - lastT < 250) show();
			lastY = y; lastT = t;
		}, { passive: true });
		return;
	}

	// Popup / slide-in : après le délai OU le pourcentage de défilement (le premier atteint).
	if (cfg.delay > 0) setTimeout(show, cfg.delay * 1000);
	if (cfg.scroll > 0) {
		var onScroll = function () {
			var h = document.documentElement.scrollHeight - window.innerHeight;
			if (h > 0 && window.scrollY / h * 100 >= cfg.scroll) { show(); window.removeEventListener('scroll', onScroll); }
		};
		window.addEventListener('scroll', onScroll, { passive: true });
	}
	if (!cfg.delay && !cfg.scroll) show();
})();
