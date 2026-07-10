# Changelog

## 0.2.0 — 2026-07-10

- **Interactive Panel preview:** the preview now mirrors the frontend
  (overlaid images with a divider) and dragging the divider writes the
  released position back into the block's `start` field — the range field
  in the drawer stays in sync. Double-clicking the preview opens the drawer.
- The Panel preview uses the before image's aspect ratio (like the
  frontend), capped at a maximum height, instead of a flat fixed-height
  strip.
- The handle arrows now scale with the grip (75 % of
  `--image-compare-handle-size`) and are tunable via the new
  `--image-compare-arrow-size` custom property.

## 0.1.0 — 2026-07-10

Initial release.

- `image-compare` block: before/after images, caption, configurable start position (0–100 %)
- Responsive `<picture>` output (WebP + JPEG) via Kirby's built-in `srcset()` — no external image pipeline
- Dependency-free vanilla JS slider (~50 lines): mouse, touch, and keyboard (arrow keys)
- Themeable through CSS custom properties
- Panel preview showing both images side by side
- Zero config: frontend CSS/JS are injected automatically, once per page
