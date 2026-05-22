/**
 * Counter Animation
 *
 * Animates `.home-fun-facts__number[data-counter]` elements from 0 to the
 * value of their `data-counter` attribute when they enter the viewport.
 *
 * Falls back to the static value when IntersectionObserver is unavailable
 * (very old browsers) so the number is never stuck at "0".
 *
 * @package Brio_Guiseppe
 */

(function () {
	'use strict';

	var counters = document.querySelectorAll('.home-fun-facts__number[data-counter]');
	if (!counters.length) {
		return;
	}

	// Fallback for browsers without IntersectionObserver.
	if (!('IntersectionObserver' in window)) {
		counters.forEach(function (el) {
			el.textContent = el.dataset.counter;
		});
		return;
	}

	function animate(el) {
		var target = parseInt(el.dataset.counter, 10);
		if (isNaN(target)) {
			return;
		}
		var duration = 1800;
		var start    = performance.now();

		function tick(now) {
			var t     = Math.min((now - start) / duration, 1);
			var eased = 1 - Math.pow(1 - t, 3);
			el.textContent = String(Math.round(target * eased));
			if (t < 1) {
				requestAnimationFrame(tick);
			}
		}

		requestAnimationFrame(tick);
	}

	var obs = new IntersectionObserver(function (entries) {
		entries.forEach(function (entry) {
			if (entry.isIntersecting) {
				animate(entry.target);
				obs.unobserve(entry.target);
			}
		});
	}, { threshold: 0.4 });

	counters.forEach(function (c) {
		obs.observe(c);
	});
})();
