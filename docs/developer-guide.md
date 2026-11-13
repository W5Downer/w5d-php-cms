# Developer guide

W5D is a dynamic PHP 8.2+ application. One front controller (`index.php`) serves the public site,
registration (`/app/register/`), the owner portal (`/{ADMIN_URL_PATH}/`) and the JSON API.
Read `docs/templates.md`, `docs/variables.md` and `docs/design.md` before changing a view.

## Layout

```
index.php                 front controller (repository root)
src/                      engine: App, Router, View, Page, Content, Variables, Country, DB, Auth, ACL, Scope,
                          Sampling, Rollup, RateLimit, Audit, Sms (stub), Controllers/{Site,Register,Portal,Forms,Api}
views/_blocks/            one renderer per block type (vocabulary: config/vocab.php, lint enforces the match)
views/_shared/            layouts, partials and page bodies shared by every template
views/templates/{name}/   per-template overrides (may be empty)
assets/_base/            attention.css, site.js (no dependencies), templates/{name}.css (additive)
data/content/             JSON content: pages, macros, services, projects, layers, statistics
data/countries/tn.json    every Tunisia-specific fact (regions, currency, legal profile, calendar)
config/                   app.php, variables.php (numbers, one place), vocab.php, roles.php
lang/{en,fr,ar}.php       UI chrome only (content text lives in JSON)
db/sqlite/              ordered SQL for the default driver (SQLite)
db/mysql/               ordered SQL for the optional MySQL/MariaDB driver; same tables
                        applied by bin/migrate.php (chooses the folder from DB_DRIVER)
bin/                      migrate, sync-content, rollup, lint, import-w5d.py (content generator)
tests/                    RouteMatrixTest.php, FlowTest.php, serve.php (local router)
```

## Request path

`index.php` → `App::run()` → `App::handle()`: method check, path check, session (only for POST and
the app/portal/form routes), language (cookie `w5d_lang`, then `Accept-Language`) → `Router::match()` →
controller → `Page::render()` or `Page::wrap()` → layout. Every error goes through `App::error()`.

## Content

- Services, macros, projects and layers are generated from the sources by `python3 bin/import-w5d.py`.
  Edit the source (`data/services.md`, the HTML report, the legacy registry) and regenerate. Do not
  hand-edit generated fields.
- Static pages are `data/content/pages/{id}.json`. Blocks are `{type, id, tier, focus, title, data, cta}`.
- Numbers are tokens, `{{ counts.services }}`, `{{ experts.target }}`. `bin/lint.php` fails on any
  unresolved token, on a literal `130,000` or `2027` outside `config/variables.php`, and on Tunisia
  literals in code.
- Translation status: fr/ar values equal to the English text are placeholders. The page shows the
  "not reviewed yet" notice, and lint reports the count.

## Checks before every commit

```bash
php bin/lint.php                # must print lint: PASS
php tests/RouteMatrixTest.php   # 200 for every route, 3 templates x 3 languages
php tests/FlowTest.php          # needs a migrated database (php bin/migrate.php)
```

## Adding a block type

1. Create `views/_blocks/{type}.php`. Read only `$block['data']`, `$block['title']`, `$lang`, `$doc`.
2. Add the name to `config/vocab.php` under `block` (lint fails if the two lists differ).
3. Use it in a page JSON with `tier`, `focus` and an `id`. A block without them throws.

## Security rules

- Never emit raw content HTML. Use `e()` or `Markup::inline()`.
- SQL is prepared statements only (`DB::all/one/value/run`).
- Forms carry a CSRF token (`csrf_field()`), are checked with `csrf_valid()`, and have a honeypot.
- The 5% sample rate is a constant (`Sampling::RATE_PERCENT`). It has no setter and no UI control.
- Audit rows are append-only. Personal data (e-mail, CIN) is stored as keyed HMAC, not plain text.
