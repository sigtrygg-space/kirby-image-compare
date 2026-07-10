import Preview from "./components/Preview.vue";

window.panel.plugin("sigtrygg-space/kirby-image-compare", {
	components: {
		"k-block-type-image-compare": Preview
	}
});
