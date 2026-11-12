# Developer guide

## Architecture

```
config/app.php            app config (paths, languages, templates, build flags)
config/variables.php      the variable registry + type vocabularies
src/bootstrap.php         explicit require list, config load, Registry::boot()
src/Registry.php          static container: config, vars, lang, content, route, view
src/Content.php           loads + validates data/content/**, exposes collections
src/Route.php             id → path map, language-aware URLs, output paths
src/View.php              two-tier view resolver + Blade-inspired compiler
src/Site.php              record → document → rendered HTML
src/helpers.php           global v(), t(), e(), href(), asset(), type_label(), …
src/Support/{Text,Vars,Markup,Json}.php
views/_blocks/*.php       one renderer per block type (shared tier)
views/_shared/…           layouts + partials (shared tier)
views/{template}/…        per-template overrides
assets/_base/…            base CSS/JS every template builds on
assets/{template}/…       per-template CSS
lang/{code}.php           UI chrome only (no content)
bin/build.php             the static generator
tools/gen_*.py            content generators (not part of the runtime)
```

## Request/render flow (build time)

```
bin/build.php
  → Registry::boot()
  → for each language:
      → for each record (pages, macros, services, projects, declarations, layers)
          → Site::documentForX()   builds the document, localises, resolves {{ tokens }}, builds TOC
          → Site::render()         picks layout by kind, renders blocks through View
          → Route::outputPath()    decides dist/{path}/index.html
```

## Adding a block type

1. Create `views/_blocks/myblock.php`. Read `$block['data']`, `$doc`, `$lang`.
2. Register it in `config/variables.php` → `types.block.options` (this is what validation checks).
3. Add styles to `assets/_base/css/base.css`.
4. Use it in any page JSON: `{ "type": "myblock", "title": {...}, "data": {...} }`.
5. If it carries a heading that should appear in the sidebar TOC, add its type to the
   `$headingBlocks` list in `Site::prepareBlocks()`.

## Adding a language

1. Add the code to `config/app.php` → `langs`, `lang_names`, `lang_dirs`, `locales`; add to
   `rtl_langs` if it is right-to-left.
2. Copy `lang/en.php` → `lang/{code}.php` and translate the values (keep the keys).
3. Build — every page renders in the new language immediately (content falls back to English where
   a translation is missing).

## Adding a collection kind (e.g. `layers`)

The `layers` kind is the worked example:

1. Property + `readCollection('layers')` in `Content`, plus an accessor.
2. Route keys in `Route::index()`.
3. `documentForLayer()` in `Site`.
4. A loop in `bin/build.php`'s document collector.
5. A block to render it, and (optionally) an index page.

## The compiler

`View::compileString()` supports `@extends @section @endsection @show @yield @include @block`,
`@if/@elseif/@else/@endif`, `@foreach @for @while @unless`, `@php/@endphp`, and
`{{ escaped }}` / `{!! raw !!}`.

**Gotcha that once broke every page:** a view's opening `<?php declare(strict_types=1);` is illegal
inside `eval()`, so the compiler strips *only the declare statement* and keeps the opening `<?php`.
Removing the tag too would emit the rest of the file as literal text.

## Build and verify

```bash
php -l src/View.php                                   # lint one file
for f in $(find src -name '*.php'); do php -l "$f"; done   # lint everything
rm -rf storage/cache/views/*                          # compiled-view cache
php bin/build.php --out=/tmp/site                     # must report zero errors
for t in ledger atlas folio; do php bin/build.php --template=$t --out=/tmp/s-$t; done
```

A clean run currently reports **639 files** per template across three languages.

## Conventions

- `declare(strict_types=1)` in every PHP file.
- No Composer, no autoloader — `src/bootstrap.php` lists files explicitly, in order.
- English comments; template output escaped through `Markup`/`e()`.
- Content changes go in `data/content/`, never in a view.
- Numbers go in `config/variables.php`, never in a page.

## Content generators

`tools/gen_*.py` re-derive the JSON from source documents (`data/services.md`, the dossiers) so the
130 services and 25 register entries are reproducible rather than hand-maintained:

```bash
python3 tools/gen_services.py     # 130 services from data/services.md
python3 tools/gen_macros.py       # 5 macro works
python3 tools/gen_projects.py     # 25 register entries
python3 tools/gen_pages_a.py      # 12 pages
python3 tools/gen_pages_b.py      # 25 pages
python3 tools/gen_declarations.py # 7 declarations
python3 tools/gen_site.py         # site.json nav + footer
python3 tools/gen_ontology.py     # 6 ontology layers + /ontology/ page
```
