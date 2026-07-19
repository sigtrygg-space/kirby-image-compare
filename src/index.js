import Preview from "./components/Preview.vue";

// single source for the divider/grip visuals: the frontend stylesheet is
// bundled into the panel css, and the preview reuses its class names
import "./frontend/image-compare.css";

window.panel.plugin("sigtrygg-space/kirby-image-compare", {
	components: {
		"k-block-type-image-compare": Preview
	}
});
