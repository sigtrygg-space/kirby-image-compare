# Kirby Image Compare

A before/after image comparison block for [Kirby CMS](https://getkirby.com) —
two images, a draggable divider, done.

- **Zero dependencies, zero config.** ~50 lines of vanilla JS, no slider
  library. The frontend CSS/JS are injected automatically, once per page.
- **Responsive images** out of the box: WebP + JPEG `<picture>` with `srcset`,
  built on Kirby's own thumb engine.
- **Accessible:** works with mouse, touch, and keyboard (arrow keys on the
  handle).
- **Themeable** through CSS custom properties.
- **Panel preview** showing both images side by side.

## Requirements

- Kirby 5
- PHP 8.2+

## Installation

### Composer

```bash
composer require sigtrygg-space/kirby-image-compare
```

### Download

Download the latest release and copy the folder to `site/plugins/image-compare`.

### Git submodule

```bash
git submodule add https://github.com/sigtrygg-space/kirby-image-compare.git site/plugins/image-compare
```

## Usage

Allow the block type in any `blocks` field:

```yaml
fields:
  text:
    type: blocks
    fieldsets:
      - heading
      - text
      - image-compare
```

Editors then pick a before image, an after image, an optional caption, and the
initial divider position (0–100 %, default 50).

The block renders a `<figure class="image-compare">` with both images as
responsive `<picture>` elements and a draggable divider. The stylesheet and
script are injected automatically the first time a block appears on a page —
nothing to add to your templates.

## Theming

Override these custom properties on `.image-compare` or any ancestor:

| Property | Default | Purpose |
| --- | --- | --- |
| `--image-compare-line-width` | `2px` | divider line width |
| `--image-compare-line-color` | `#fff` | divider line color |
| `--image-compare-handle-size` | `2rem` | diameter of the round grip |
| `--image-compare-handle-bg` | `#fff` | grip background |
| `--image-compare-handle-color` | `#555` | grip arrow color |

The stage's aspect ratio is derived from the before image automatically; set
`--image-compare-ratio` yourself to force a different ratio.

### Custom image markup

The responsive `<picture>` lives in its own snippet. To replace it (different
widths, formats, a lazy-loading library, …), copy
`snippets/image-compare-picture.php` to `site/snippets/image-compare-picture.php`
and adjust it — site snippets override plugin snippets of the same name. The
same goes for the block markup itself (`snippets/blocks/image-compare.php` →
`site/snippets/blocks/image-compare.php`).

## Development

The Panel preview is a Vue single-file component, precompiled with
[kirbyup](https://github.com/johannschopplich/kirbyup):

```bash
npm install
npm run build   # rebuilds index.js/index.css from src/
```

The built `index.js`/`index.css` are committed; CI fails when they are stale.

## License

[MIT](LICENSE)
