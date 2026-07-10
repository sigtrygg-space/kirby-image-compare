# kirby-image-compare — Project Context

Before/after image comparison block plugin for Kirby 5. Public repo:
`sigtrygg-space/kirby-image-compare`. Extracted from a private site project;
keep it generic — nothing site-specific may enter this repo.

## Conventions

- Chat in German; code, comments, commits, issues, README, CHANGELOG in
  English (international Kirby community).
- Code style = Kirby core: tabs, aligned array arrows.
- Block type / assets / blueprint are named `image-compare` (no `kirby-`
  prefix); only the repo and the Composer package carry `kirby-`.
- SemVer; `version` field in composer.json is the single source of truth.
  Releasing = one PR that bumps the version and dates the CHANGELOG section;
  the release workflow tags and publishes on merge. Never move a published
  tag (Packagist skips tags that don't match the version field).

## Architecture decisions

- **No slider library.** JuxtaposeJS takes a single `src` per side (no
  `srcset`) and would defeat the responsive `<picture>`; the vanilla JS is
  ~50 lines. If a library is ever wanted, `img-comparison-slider` (slot-based
  web component) is the one to consider — not JuxtaposeJS.
- **Asset injection via `$GLOBALS` guard** in the block snippet: zero-config
  for the site owner, CSS/JS injected once per request. A cleaner
  `page.render:after` hook is a possible v2.
- **Both CSS and JS set the divider position.** The inline
  `--image-compare-start` custom property covers no-JS and pre-hydration;
  the script takes over from `data-start` on init.
- **Snippets are override points.** Site snippets shadow plugin snippets of
  the same name — that is the documented customization path (README), so
  renaming the snippets is a breaking change.

## Hard-won Kirby 5 lessons (from sibling project kirby-trash)

- **Never use Vue `template:` strings.** Kirby's security guide recommends
  disabling the Vue template compiler (deprecated in Kirby 6); runtime-only
  Vue renders such components silently as nothing. Panel components are SFCs
  in `src/components/*.vue`, precompiled with kirbyup. The built
  `index.js`/`index.css` are committed; rebuild after every `src/` change
  (`npm run build`). CI fails when the committed build is stale.
- **Verify Kirby internals before building on them** — read
  `vendor/getkirby/cms/src/**` instead of assuming.
- Keep a `version` field in composer.json — the Panel shows it for
  manual/submodule installs (Composer prints a warning; that is acceptable).
- `.gitattributes` `export-ignore` keeps dev files out of the Composer dist.
- PHPUnit (if ever added): `App::destroy()` wipes the static plugin registry
  (setUp must conditionally `require` index.php again); `$kirby->clone()`
  drops impersonation.

## Verified facts

- kirbyup bundles CSS imported in `src/index.js` (e.g.
  `import "../assets/image-compare.css"`) into the built `index.css` —
  that is how the Panel preview shares the frontend divider/grip styles
  (single source, same class names in both DOMs).

- `page.render:after` is an apply-hook (`$kirby->apply(..., 'html')`,
  Page.php ~987 in 5.5): the closure receives named args (contentType,
  data, html, page), its return value replaces the HTML, and it runs
  BEFORE the page cache stores the result — injected tags are cached.
- `$plugin->asset('file.css')->url()` returns the hashed media URL
  (crc32(filename) + '-' + mtime) — the cache-busting canonical form.
  `mediaUrl() . '/file.css'` (hash-less) resolves only via a route branch
  marked deprecated in core.
- In the Panel, a files field inside block content arrives as an array of
  pickerData objects (uuid, url, image.url/src/srcset, filename, …) — no
  API call needed for URLs. pickerData contains NO dimensions; read
  naturalWidth/naturalHeight off the loaded image instead. Top-level `url`
  and `image.url` are the ORIGINAL file URL (core's image block does the
  same); `image.srcset` only has tiny list thumbs (38/76w).
- On single-language installs, frontend `I18n::$locale` is hardcoded 'en'
  (AppTranslations.php) — plugin translations other than 'en' are
  unreachable there; the `label` option exists for that case.
- `Blocks::excerpt()` renders block snippets and strips the markup — any
  side effects (output, globals) of block snippets fire during excerpt
  rendering too. Never make block snippets stateful per request.

- `$file->srcset()` accepts an array keyed by descriptor
  (`['480w' => ['width' => 480, 'format' => 'webp', ...]]`) and returns a
  ready srcset string (null when the file has no thumbs).
- Plugin `assets` are served under
  `/media/plugins/sigtrygg-space/kirby-image-compare/…`;
  `$kirby->plugin(...)->mediaUrl()` returns that base.
- A blocks field only offers custom block types when they are listed in
  `fieldsets` — there is no auto-registration into the default set.
- The Panel resolves a block preview to the global component
  `k-block-type-<type>`; plugin-registered components qualify. Without one,
  the Panel falls back to the generic fields preview.
- Block preview components update content by emitting `update` with the
  **full merged content object** (`this.$emit("update", { ...this.content,
  start: value })`) — verified against core `Types/Default.vue` in Kirby
  5.5.0, whose `update(content)` merges exactly like this. The new content
  flows back into the `content` prop asynchronously (Vue re-render), so a
  computed that falls back to `content.start` after a drag keeps the
  position stable without local state.
- `document`-level `touchmove` listeners are passive by default in Chrome —
  pass `{ passive: false }` when calling `preventDefault()` (the stage also
  sets `touch-action: none`, which covers scrolling on the element itself).

## Testing

Manual smoke test in a Kirby 5 Starterkit: allow the block in a blueprint,
create a block with before/after + start position, check desktop drag, touch,
keyboard, and a second block on the same page (shared listeners, single asset
injection). `php -l` on all PHP files; `npm run build` must leave
`index.js`/`index.css` unchanged.
