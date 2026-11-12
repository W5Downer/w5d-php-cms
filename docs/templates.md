# Onboarding a new template

A template is a **skin**: it changes how blocks look, never what a page says. That is why one page
JSON renders in every installed template.

## Two-tier resolution

For any view or block, the engine looks here first, then falls back:

```
views/{ACTIVE_TEMPLATE}/blocks/{type}.php   →  views/_blocks/{type}.php
views/{ACTIVE_TEMPLATE}/partials/{name}.php →  views/_shared/partials/{name}.php
views/{ACTIVE_TEMPLATE}/layouts/{name}.php   →  views/_shared/layouts/{name}.php
```

A template therefore only needs the files it actually changes. A template with **zero** PHP files is
valid — it would simply reuse the shared ones and change nothing but its CSS.

## Steps

1. Create the folder:

   ```
   views/mytemplate/{blocks,layouts,pages,partials}/
   assets/mytemplate/css/template.css
   ```

2. Write `assets/mytemplate/css/template.css`. It loads **after** `assets/_base/css/base.css`, so it
   is purely additive: override tokens and block appearance, never restate layout.

3. Optionally override any block:

   ```bash
   cp views/_blocks/cards.php views/mytemplate/blocks/cards.php
   ```

   `$block`, `$doc` and `$lang` are already available.

4. Register the template in `config/app.php`:

   ```php
   'templates' => ['ledger', 'atlas', 'folio', 'mytemplate'],
   ```

5. Build:

   ```bash
   php bin/build.php --template=mytemplate --out=/tmp/preview
   ```

## Rules a template must keep

- **Do not read content from anywhere but `$block['data']` and `$doc`.** Never hard-code a figure;
  use `v('counts.services')` so the variable registry stays the single source.
- **Do not introduce a network request.** No CDN font, no icon library, no external script — the
  site promises zero third-party requests.
- **Do not escape-and-forget.** Text goes through `Markup::inline()`; raw HTML only in the `rich`
  block.
- **Keep the accessible shell:** one `<h1>` per page, `<main id="main">`, skip link, `lang`/`dir` on
  `<html>`.

## Shipped templates

| Template | Character | Direction |
|---|---|---|
| `ledger` | editorial, brass, tabular | default |
| `atlas` | cartographic, cyan, slate grid | LTR-strong |
| `folio` | paper, oxblood, drop caps | literary |

Arabic inherits `assets/_base/css/rtl.css` in every template, so a template does not need its own
RTL file unless it wants to change Arabic specifically.
