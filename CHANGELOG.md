# Changelog

## 1.0.1 — 2026-07-19

### Changed
- Frontend assets (`assets/image-compare.{js,css}`) are now minified at
  build time; source moved to `src/frontend/`. Shipped paths and runtime
  behavior are unchanged, license banner preserved.

## 1.0.0 — 2026-07-11

First stable release — no functional changes over 0.7.1. From here on,
semantic versioning guards this public surface:

- the `image-compare` block type and its blueprint fields
  (`image_before`, `image_after`, `caption`, `start`)
- the plugin options (`label`, `step`, `widths`, `formats`, `quality`,
  `sizes`, `fallback`)
- the documented CSS custom properties (`--image-compare-*`)
- the snippet override points (`blocks/image-compare`,
  `image-compare-picture`)
- the frontend API `window.kirbyImageCompare.init(root)`
- the translation key `image-compare.drag`

## 0.7.1 — 2026-07-11

- The arrow highlight fades instead of snapping: both arrow colors are
  typed (registered) custom properties now, so every state change —
  engage, direction switch, idle re-engage, release — is a smooth 250 ms
  color transition. Browsers without `@property` keep the previous
  instant switches. The mid-drag idle timeout was raised to 400 ms.

## 0.7.0 — 2026-07-11

- Cleaner drag-state model (refines v0.6.0, closes #5): while a pointer
  holds the slider, both arrows take the engaged (hover) color — a plain
  tap on mobile no longer lights a single arrow. Actual movement (≥ 3px)
  narrows the highlight to the direction arrow; after 200 ms of
  standstill both arrows re-engage; release returns to neutral. Panel
  preview behaves identically.

## 0.6.0 — 2026-07-10

- While dragging (pointer or keyboard), only the arrow pointing in the
  drag direction takes the hover color — the two glyphs share one mask,
  so a 50/50 gradient colors them independently. Works in the Panel
  preview too. Internal: the hover color is resolved once into a private
  custom property, removing the duplicated `@supports` blocks.

## 0.5.0 — 2026-07-10

- New `--image-compare-handle-hover-color` custom property: the grip
  arrows change color on hover and keyboard focus (`:focus-visible`).
  Defaults to the OS accent color where the browser supports the
  `AccentColor` keyword; elsewhere the arrows keep their regular color
  until the property is set (guarded via `@supports` — the keyword must
  not sit inside `var()`, or unsupporting browsers would blank the
  arrows).

## 0.4.1 — 2026-07-10

- The default handle labels are noun phrases now ("Image comparison" /
  "Bildvergleich" instead of "Drag to compare") — screen readers announce
  the slider role themselves, so the label should name what is controlled,
  not the widget. README documents the same guidance for the `label` option.

## 0.4.0 — 2026-07-10

Closes the first round of backlog issues (#1–#4).

- **Responsive image options** (#1): `widths`, `formats` (incl. AVIF),
  `quality` (int, per-format map, or `null` to fall through to Kirby's
  `thumbs` config), `sizes`, and `fallback` are now plugin options — no
  more snippet fork to change a number.
- **Re-init API** (#2): `window.kirbyImageCompare.init(root)` initializes
  dynamically inserted blocks (htmx/Turbo swaps, infinite scroll);
  idempotent per stage.
- **ARIA slider** (#3): the handle is a proper WAI-ARIA slider
  (`role="slider"`, `aria-valuemin/max/now`) with full keyboard support —
  arrow keys (configurable `step` option, default 2 %), PageUp/PageDown
  (10 %), Home/End.
- **Single source for the visuals** (#4): the Panel preview now bundles
  the frontend stylesheet (kirbyup import) and reuses its classes —
  divider/grip styles exist exactly once; the preview divider moves via
  the same custom property as the frontend.

## 0.3.0 — 2026-07-10

Hardening release: all findings from a full code review, verified against the
Kirby 5.5 core.

**Fixed**

- A plain click on the Panel preview no longer rewrites the start position —
  only an actual drag (≥ 3px movement, primary button) commits a value; the
  double-click-to-open gesture and right-clicks leave the content untouched.
- Frontend assets are now injected into the `<head>` via a `page.render:after`
  hook — per rendered document instead of per PHP request. Excerpt/JSON
  renders no longer consume the injection, and cached pages always contain
  the tags.
- Asset URLs now use Kirby's hashed plugin-asset URLs
  (`$plugin->asset()->url()`), so browser caches are busted on every plugin
  update and the deprecated hash-less media route is no longer relied on.
- The frontend slider was rewritten on Pointer Events with pointer capture:
  releasing the mouse outside the window no longer leaves the drag stuck, a
  second (resting) finger no longer hijacks or aborts the drag, and only the
  primary button starts one.
- The Panel drag likewise uses pointer capture and handles `pointercancel` —
  no more stuck drags or leaked window listeners on touch devices.
- The Panel preview now reads its image URLs from the block content
  (pickerData) instead of two API round-trips: previews update immediately
  when images are swapped in the drawer, and the stage ratio is read from
  the loaded image itself.
- The image fields now restrict the picker to images (`query: page.images`);
  non-image files can no longer be selected and silently rendered as empty
  layers.
- `--image-compare-ratio` (and `--image-compare-start`) are set on the
  `figure` instead of the stage, so the documented CSS override on
  `.image-compare-stage` actually works.
- Layer sizing uses low-specificity element selectors as a fallback, so a
  custom picture snippet without the `.image-compare-pic` class is still
  sized correctly.
- New `label` plugin option to set the handle's `aria-label` on
  single-language sites (where the English translation would otherwise
  always win).

**Internal**

- Removed dead code paths (unreachable JSON parsing, redundant guards,
  unused props, `max: 1` next to `multiple: false`), cached the stage rect
  per drag, and dropped all document/window-level listeners in favor of
  element-scoped ones.

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
