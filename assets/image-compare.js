/*!
 * image-compare — dependency-free before/after slider
 * https://github.com/sigtrygg-space/kirby-image-compare
 */
(function () {
	'use strict';

	function setup(el) {
		var after  = el.querySelector('.image-compare-after');
		var handle = el.querySelector('.image-compare-handle');

		if (!after || !handle || el.dataset.imageCompareReady) {
			return;
		}

		el.dataset.imageCompareReady = 'true';

		var current = 0.5;

		function set(pct) {
			current = pct;
			after.style.clipPath = 'inset(0 ' + (1 - pct) * 100 + '% 0 0)';
			handle.style.left = (pct * 100) + '%';
			handle.setAttribute('aria-valuenow', Math.round(pct * 100));
		}

		var start = parseInt(el.dataset.start, 10);
		set((isNaN(start) ? 50 : Math.max(0, Math.min(100, start))) / 100);

		var step = parseInt(handle.dataset.step, 10);
		step = (isNaN(step) || step < 1 ? 2 : step) / 100;

		// one captured pointer per stage: capture guarantees a terminal
		// pointerup/pointercancel even off-window, and the id filter keeps
		// a second (resting) finger from hijacking or ending the drag
		var pointerId = null;
		var rect = null;

		// only the arrow pointing in the current direction lights up (CSS)
		function setWithDirection(pct) {
			if (pct !== current) {
				handle.dataset.direction = pct > current ? 'right' : 'left';
			}
			set(pct);
		}

		function move(e) {
			if (e.pointerId === pointerId) {
				setWithDirection(Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width)));
			}
		}

		function release(e) {
			if (e.pointerId === pointerId) {
				pointerId = null;
				delete handle.dataset.direction;
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
			var pct = {
				ArrowLeft:  current - step,
				ArrowRight: current + step,
				PageDown:   current - 0.1,
				PageUp:     current + 0.1,
				Home:       0,
				End:        1
			}[e.key];

			if (pct !== undefined) {
				e.preventDefault();
				setWithDirection(Math.max(0, Math.min(1, pct)));
			}
		});
		handle.addEventListener('keyup', function () { delete handle.dataset.direction; });
		handle.addEventListener('blur', function () { delete handle.dataset.direction; });
	}

	function init(root) {
		(root || document).querySelectorAll('[data-image-compare]').forEach(setup);
	}

	// re-init hook for dynamically inserted blocks (htmx/Turbo swaps,
	// infinite scroll): idempotent per stage via a data flag
	window.kirbyImageCompare = { init: init };

	init();
})();
