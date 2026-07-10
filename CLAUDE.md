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
- `document`-level `touchmove` listeners are passive by default in Chrome —
  pass `{ passive: false }` when calling `preventDefault()` (the stage also
  sets `touch-action: none`, which covers scrolling on the element itself).

## Testing

Manual smoke test in a Kirby 5 Starterkit: allow the block in a blueprint,
create a block with before/after + start position, check desktop drag, touch,
keyboard, and a second block on the same page (shared listeners, single asset
injection). `php -l` on all PHP files; `npm run build` must leave
`index.js`/`index.css` unchanged.
