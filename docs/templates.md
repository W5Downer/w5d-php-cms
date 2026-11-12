# Templates

A template is a **skin**. It changes how blocks look, never what a page says.

## The one path rule

| What | Where |
|---|---|
| Template block override | `views/templates/{name}/blocks/{type}.php` |
| Template layout / partial override | `views/templates/{name}/layouts/{name}.php`, `views/templates/{name}/partials/{name}.php` |
| Template CSS (additive only) | `assets/_base/css/templates/{name}.css` |
| Fallback block | `views/_blocks/{type}.php` |
| Fallback layout / partial | `views/_shared/layouts/`, `views/_shared/partials/` |

Resolution is two-tier: the template path first, then the shared path. `View::resolveBlock()`
implements this rule and `docs/templates.md` is the only place it is written down.

The active template is one value, `template` in `config/app.php` (or `W5D_TEMPLATE` in `.env`).
Unknown values fall back to `ledger`.

## Installed templates

| Template | Feel | Override files |
|---|---|---|
| `ledger` | editorial, brass | CSS only |
| `atlas` | cartographic, cyan | CSS only |
| `folio` | paper, oxblood, serif | CSS only |

A template with zero PHP files is valid: it reuses the shared blocks and changes only its CSS.

## Adding a template

1. Create `assets/_base/css/templates/{name}.css`. Override tokens and accents only; do not restate layout.
2. Optionally copy any block into `views/templates/{name}/blocks/{type}.php` and edit it.
3. Add the name to `templates` in `config/app.php`.
4. Run `php tests/RouteMatrixTest.php`. It renders every route in every template and language.

No content file changes. A template may not introduce a third-party request (no CDN, no font
service, no external script).

## Rules a block or template must keep

- Read content only from `$block['data']` and `$doc`. Never hard-code a figure; use `{{ counts.services }}`.
- Text goes through `e()` or `Markup::inline()`. Raw HTML is never emitted from content.
- Every block declares `id`, `tier` (E/I/X) and `focus` (1–5). A block without them throws.
- Inline `style` attributes are not used (the CSP forbids them). Widths use classes such as `w-40`.
- Keep one `<h1>` per page and the skip link, `lang` and `dir` on `<html>`.
