/*!
 * image-compare — dependency-free before/after slider
 * https://github.com/sigtrygg-space/kirby-image-compare
 */
(function () {
	'use strict';

	// only the currently grabbed slider reacts to move/release; one shared
	// reference lets the document listeners be registered once for all
	// instances instead of once per slider
	var activeUpdate = null;
	var stages = document.querySelectorAll('[data-image-compare]');

	stages.forEach(function (el) {
		var after   = el.querySelector('.image-compare-after');
		var handle  = el.querySelector('.image-compare-handle');
		var current = 0.5;

		if (!after || !handle) {
			return;
		}

		function set(pct) {
			current = pct;
			after.style.clipPath = 'inset(0 ' + (1 - pct) * 100 + '% 0 0)';
			handle.style.left = (pct * 100) + '%';
		}

		function update(clientX) {
			var rect = el.getBoundingClientRect();
			set(Math.max(0, Math.min(1, (clientX - rect.left) / rect.width)));
		}

		var start = parseInt(el.dataset.start, 10);
		set((isNaN(start) ? 50 : Math.max(0, Math.min(100, start))) / 100);

		el.addEventListener('dragstart', function (e) { e.preventDefault(); });
		el.addEventListener('mousedown', function () { activeUpdate = update; });
		el.addEventListener('touchstart', function (e) { activeUpdate = update; update(e.touches[0].clientX); }, { passive: true });

		handle.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
				e.preventDefault();
				set(Math.max(0, Math.min(1, current + (e.key === 'ArrowRight' ? 0.02 : -0.02))));
			}
		});
	});

	if (stages.length) {
		document.addEventListener('mouseup', function () { activeUpdate = null; });
		document.addEventListener('touchend', function () { activeUpdate = null; });
		document.addEventListener('mousemove', function (e) { if (activeUpdate) { e.preventDefault(); activeUpdate(e.clientX); } });
		document.addEventListener('touchmove', function (e) { if (activeUpdate) { e.preventDefault(); activeUpdate(e.touches[0].clientX); } }, { passive: false });
	}
})();
