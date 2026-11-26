<template>
	<div class="k-image-compare-preview">
		<div
			ref="stage"
			class="image-compare-stage k-image-compare-preview-stage"
			:style="{ '--image-compare-start': position + '%', '--image-compare-ratio': ratio }"
			@pointerdown="onPointerDown"
			@pointermove="onPointerMove"
			@pointerup="onPointerUp"
			@pointercancel="onPointerCancel"
			@dblclick="$emit('open')"
		>
			<div class="image-compare-before k-image-compare-preview-layer" :style="beforeStyle" />
			<div class="image-compare-after k-image-compare-preview-layer" :style="afterStyle" />
			<div
				class="image-compare-handle"
				:data-active="pointerId !== null || null"
				:data-direction="direction"
			/>
		</div>
		<p v-if="content.caption" class="k-image-compare-preview-caption">
			{{ content.caption }}
		</p>
	</div>
</template>

<script>
export default {
	inheritAttrs: false,
	props: {
		content: Object,
		disabled: Boolean
	},
	emits: ["update", "open"],
	data() {
		return {
			// Kirby 6 delivers picker values as UUID strings, so the URLs
			// are resolved through the Panel API (see the content watchers)
			beforeUrl: null,
			afterUrl: null,
			// mirrors the frontend: the stage ratio follows the before
			// image; null falls back to the stylesheet's 3 / 2
			ratio: null,
			pointerId: null,
			dragged: false,
			dragPosition: 50,
			direction: null
		};
	},
	computed: {
		// while dragging the local position wins; otherwise mirror the
		// stored start value (which the range field in the drawer edits)
		position() {
			if (this.pointerId !== null && this.dragged) {
				return this.dragPosition;
			}

			const start = parseInt(this.content?.start, 10);
			return isNaN(start) ? 50 : Math.max(0, Math.min(100, start));
		},
		beforeStyle() {
			return this.beforeUrl ? { backgroundImage: `url(${this.beforeUrl})` } : {};
		},
		afterStyle() {
			return this.afterUrl ? { backgroundImage: `url(${this.afterUrl})` } : {};
		}
	},
	unmounted() {
		clearTimeout(this.idleTimer);
	},
	watch: {
		"content.image_before": {
			immediate: true,
			handler(value) {
				this.resolveUrl(value, "beforeUrl");
			}
		},
		"content.image_after": {
			immediate: true,
			handler(value) {
				this.resolveUrl(value, "afterUrl");
			}
		},
		beforeUrl(url) {
			// the API response carries no dimensions, so read them off the
			// loaded image itself (same URL as the layer — fetched once)
			this.ratio = null;

			if (!url) {
				return;
			}

			const probe = new Image();
			probe.onload = () => {
				if (this.beforeUrl === url && probe.naturalWidth > 0 && probe.naturalHeight > 0) {
					this.ratio = `${probe.naturalWidth} / ${probe.naturalHeight}`;
				}
			};
			probe.src = url;
		}
	},
	methods: {
		fileId(fieldValue) {
			const first = Array.isArray(fieldValue) ? fieldValue[0] : null;

			// Kirby 6 stores UUID/ID strings in the form value
			if (typeof first === "string") {
				return first;
			}

			return first?.uuid ?? first?.id ?? null;
		},
		async resolveUrl(fieldValue, target) {
			const id = this.fileId(fieldValue);

			if (!id) {
				this[target] = null;
				return;
			}

			// ignore responses (and errors) when the field changed meanwhile
			const isCurrent = () =>
				this.fileId(target === "beforeUrl" ? this.content?.image_before : this.content?.image_after) === id;

			try {
				const file = await this.$panel.api.files.get(null, id, { select: "panelImage" });

				if (isCurrent()) {
					this[target] = file?.panelImage?.url ?? null;
				}
			} catch {
				if (isCurrent()) {
					this[target] = null;
				}
			}
		},
		// dragging the divider writes the released position back into the
		// block's `start` field — same field the range input edits; a plain
		// click (no movement) selects the block without touching content
		onPointerDown(event) {
			if (this.disabled || this.pointerId !== null || event.button !== 0) {
				return;
			}

			this.pointerId = event.pointerId;
			this.dragged = false;
			this.dragPosition = this.position;
			this.startX = event.clientX;
			this.stageRect = this.$refs.stage.getBoundingClientRect();
			this.$refs.stage.setPointerCapture(event.pointerId);
		},
		onPointerMove(event) {
			if (event.pointerId !== this.pointerId) {
				return;
			}

			if (this.dragged === false && Math.abs(event.clientX - this.startX) < 3) {
				return;
			}

			this.dragged = true;
			const pct = ((event.clientX - this.stageRect.left) / this.stageRect.width) * 100;
			const next = Math.round(Math.max(0, Math.min(100, pct)));

			if (next !== this.dragPosition) {
				this.direction = next > this.dragPosition ? "right" : "left";
				clearTimeout(this.idleTimer);
				this.idleTimer = setTimeout(() => (this.direction = null), 400);
			}

			this.dragPosition = next;
		},
		onPointerUp(event) {
			if (event.pointerId !== this.pointerId) {
				return;
			}

			const dragged = this.dragged;
			this.pointerId = null;
			this.dragged = false;
			this.direction = null;
			clearTimeout(this.idleTimer);

			if (dragged) {
				this.$emit("update", { ...this.content, start: this.dragPosition });
			}
		},
		// a cancelled pointer (scroll takeover, palm rejection) discards
		// the drag; the divider falls back to the stored start value
		onPointerCancel(event) {
			if (event.pointerId === this.pointerId) {
				this.pointerId = null;
				this.dragged = false;
				this.direction = null;
				clearTimeout(this.idleTimer);
			}
		}
	}
};
</script>

<style>
/* stage/divider/grip visuals come from the bundled frontend stylesheet
   (assets/image-compare.css, imported in src/index.js) — below are only
   the panel-specific extras */

.k-image-compare-preview-stage {
	--image-compare-handle-color: var(--color-gray-600);
	max-height: 24rem;
	border-radius: var(--rounded);
	background: var(--color-gray-200);
}

.k-image-compare-preview-layer {
	background-size: cover;
	background-position: center;
}

.k-image-compare-preview-caption {
	font-size: var(--text-xs);
	color: var(--color-gray-500);
	margin-top: var(--spacing-1);
	padding: 0 var(--spacing-1);
}
</style>
