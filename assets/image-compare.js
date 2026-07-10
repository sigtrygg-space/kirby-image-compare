/*!
 * image-compare — dependency-free before/after slider
 * https://github.com/sigtrygg-space/kirby-image-compare
 */
(function () {
	'use strict';

	document.querySelectorAll('[data-image-compare]').forEach(function (el) {
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

		var start = parseInt(el.dataset.start, 10);
		set((isNaN(start) ? 50 : Math.max(0, Math.min(100, start))) / 100);

		// one captured pointer per stage: capture guarantees a terminal
		// pointerup/pointercancel even off-window, and the id filter keeps
		// a second (resting) finger from hijacking or ending the drag
		var pointerId = null;
		var rect = null;

		function move(e) {
			if (e.pointerId === pointerId) {
				set(Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width)));
			}
		}

		function release(e) {
			if (e.pointerId === pointerId) {
				pointerId = null;
			}
		}

		el.addEventListener('dragstart', function (e) { e.preventDefault(); });
		el.addEventListener('pointerdown', function (e) {
			if (pointerId !== null || e.button !== 0) {
				return;
			}
			pointerId = e.pointerId;
			rect = el.getBoundingClientRect();
			el.setPointerCapture(e.pointerId);
			move(e);
		});
		el.addEventListener('pointermove', move);
		el.addEventListener('pointerup', release);
		el.addEventListener('pointercancel', release);

		handle.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
				e.preventDefault();
				set(Math.max(0, Math.min(1, current + (e.key === 'ArrowRight' ? 0.02 : -0.02))));
			}
		});
	});
})();
