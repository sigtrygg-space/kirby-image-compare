<template>
	<div class="k-image-compare-preview">
		<div
			ref="stage"
			class="k-image-compare-preview-stage"
			:style="{ '--position': position + '%', aspectRatio: ratio }"
			@pointerdown="onPointerDown"
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
	props: {
		attrs: Object,
		content: Object,
		disabled: Boolean,
		endpoints: Object,
		id: String,
		isHidden: Boolean,
		type: String
	},
	data() {
		return {
			beforeUrl: null,
			afterUrl: null,
			// mirror the frontend, which derives the stage ratio
			// from the before image
			ratio: "3 / 2",
			dragging: false,
			dragPosition: 50
		};
	},
	computed: {
		// while dragging the local position wins; otherwise mirror the
		// stored start value (which the range field in the drawer edits)
		position() {
			if (this.dragging) {
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
	async created() {
		const before = await this.resolveFile(this.content?.image_before);
		const after = await this.resolveFile(this.content?.image_after);

		this.beforeUrl = before?.panelImage?.url ?? null;
		this.afterUrl = after?.panelImage?.url ?? null;

		if (before?.dimensions?.width > 0 && before?.dimensions?.height > 0) {
			this.ratio = `${before.dimensions.width} / ${before.dimensions.height}`;
		}
	},
	destroyed() {
		this.unbind();
	},
	methods: {
		// dragging the divider writes the released position back into the
		// block's `start` field — same field the range input edits
		onPointerDown(event) {
			if (this.disabled) {
				return;
			}

			event.preventDefault();

			this.dragging = true;
			this.moveTo(event.clientX);

			this.onPointerMove = (moveEvent) => this.moveTo(moveEvent.clientX);
			this.onPointerUp = () => {
				this.unbind();
				this.dragging = false;
				this.$emit("update", { ...this.content, start: this.dragPosition });
			};

			window.addEventListener("pointermove", this.onPointerMove);
			window.addEventListener("pointerup", this.onPointerUp);
		},
		moveTo(clientX) {
			const rect = this.$refs.stage.getBoundingClientRect();
			const pct = ((clientX - rect.left) / rect.width) * 100;
			this.dragPosition = Math.round(Math.max(0, Math.min(100, pct)));
		},
		unbind() {
			window.removeEventListener("pointermove", this.onPointerMove);
			window.removeEventListener("pointerup", this.onPointerUp);
		},
		// a files field stores an array of file references; resolve the
		// first one to its panel thumb + dimensions via the API
		async resolveFile(fieldValue) {
			if (!fieldValue) {
				return null;
			}

			let files = fieldValue;

			if (typeof files === "string") {
				try {
					files = JSON.parse(files);
				} catch {
					return null;
				}
			}

			if (!Array.isArray(files) || !files.length) {
				return null;
			}

			const first = files[0];
			const uuid = first?.uuid ?? first?.id ?? (typeof first === "string" ? first : null);

			if (!uuid) {
				return null;
			}

			try {
				return await window.panel.api.files.get(null, uuid, {
					select: "panelImage,dimensions"
				});
			} catch {
				return null;
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
