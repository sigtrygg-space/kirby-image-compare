<template>
	<div class="k-image-compare-preview">
		<div class="k-image-compare-preview-images">
			<div class="k-image-compare-preview-half" :style="beforeStyle" />
			<div class="k-image-compare-preview-half" :style="afterStyle" />
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
		endpoints: Object,
		id: String,
		isHidden: Boolean,
		type: String
	},
	data() {
		return {
			beforeUrl: null,
			afterUrl: null
		};
	},
	computed: {
		beforeStyle() {
			return this.beforeUrl ? { backgroundImage: `url(${this.beforeUrl})` } : {};
		},
		afterStyle() {
			return this.afterUrl ? { backgroundImage: `url(${this.afterUrl})` } : {};
		}
	},
	async created() {
		this.beforeUrl = await this.resolveThumb(this.content?.image_before);
		this.afterUrl = await this.resolveThumb(this.content?.image_after);
	},
	methods: {
		// a files field stores an array of file references; resolve the
		// first one to its panel thumb via the API
		async resolveThumb(fieldValue) {
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
				const file = await window.panel.api.files.get(null, uuid, { select: "panelImage" });
				return file?.panelImage?.url ?? null;
			} catch {
				return null;
			}
		}
	}
};
</script>

<style>
.k-image-compare-preview-images {
	display: flex;
	gap: 1px;
	overflow: hidden;
	border-radius: var(--rounded);
	background: var(--color-white);
}

.k-image-compare-preview-half {
	flex: 1;
	height: 80px;
	background: var(--color-gray-200);
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
