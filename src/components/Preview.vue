<template>
	<div class="k-image-compare-preview">
		<div
			ref="stage"
			class="k-image-compare-preview-stage"
			:style="{ '--position': position + '%', aspectRatio: ratio }"
			@pointerdown="onPointerDown"
			@pointermove="onPointerMove"
			@pointerup="onPointerUp"
			@pointercancel="onPointerCancel"
			@dblclick="$emit('open')"
		>
			<div class="k-image-compare-preview-layer" :style="beforeStyle" />
			<div
				class="k-image-compare-preview-layer k-image-compare-preview-after"
				:style="afterStyle"
			/>
			<div class="k-image-compare-preview-divider" />
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
	data() {
		return {
			// mirrors the frontend: the stage ratio follows the before
			// image; null falls back to the stylesheet's 3 / 2
			ratio: null,
			pointerId: null,
			dragged: false,
			dragPosition: 50
		};
	},
	computed: {
		// the files field delivers its value as pickerData objects inside
		// the reactive content prop — no API round-trip needed, and image
		// swaps in the drawer update the preview automatically
		beforeUrl() {
			return this.fileUrl(this.content?.image_before);
		},
		afterUrl() {
			return this.fileUrl(this.content?.image_after);
		},
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
	watch: {
		beforeUrl: {
			immediate: true,
			// pickerData carries no dimensions, so read them off the loaded
			// image itself (same URL as the layer — the browser fetches once)
			handler(url) {
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
		}
	},
	methods: {
		fileUrl(fieldValue) {
			const first = Array.isArray(fieldValue) ? fieldValue[0] : null;
			return first?.image?.url ?? first?.url ?? null;
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
			this.dragPosition = Math.round(Math.max(0, Math.min(100, pct)));
		},
		onPointerUp(event) {
			if (event.pointerId !== this.pointerId) {
				return;
			}

			const dragged = this.dragged;
			this.pointerId = null;
			this.dragged = false;

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
			}
		}
	}
};
</script>

<style>
.k-image-compare-preview-stage {
	position: relative;
	aspect-ratio: 3 / 2;
	max-height: 24rem;
	overflow: hidden;
	border-radius: var(--rounded);
	background: var(--color-gray-200);
	cursor: ew-resize;
	user-select: none;
	touch-action: none;
}

.k-image-compare-preview-layer {
	position: absolute;
	inset: 0;
	background-size: cover;
	background-position: center;
}

.k-image-compare-preview-after {
	clip-path: inset(0 calc(100% - var(--position, 50%)) 0 0);
}

.k-image-compare-preview-divider {
	position: absolute;
	inset: 0 auto 0 var(--position, 50%);
	transform: translateX(-50%);
	width: 2px;
	background: var(--color-white);
}

.k-image-compare-preview-divider::before {
	content: "";
	position: absolute;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
	width: 1.5rem;
	height: 1.5rem;
	border-radius: 50%;
	background: var(--color-white);
	box-shadow: var(--shadow);
}

.k-image-compare-preview-divider::after {
	content: "";
	position: absolute;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
	width: 1.5rem;
	height: 1.5rem;
	background: var(--color-gray-600);
	mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M8 7l-5 5 5 5V7zm8 0v10l5-5-5-5z'/%3E%3C/svg%3E") center / 75% no-repeat;
}

.k-image-compare-preview-caption {
	font-size: var(--text-xs);
	color: var(--color-gray-500);
	margin-top: var(--spacing-1);
	padding: 0 var(--spacing-1);
}
</style>
