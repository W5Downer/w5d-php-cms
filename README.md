# W5D PHP CMS

**A complete CMS for the W5D Team website.**

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![SQLite](https://img.shields.io/badge/SQLite-3-003B57?logo=sqlite&logoColor=white)](https://www.sqlite.org/)
[![License](https://img.shields.io/badge/License-610.5%20TND%2Fh%20(offer%20before%202027)-0A5EFF)](./LICENSE)
[![Templates](https://img.shields.io/badge/Templates-multi--template-0A5EFF)](#multi-template-system)
[![Languages](https://img.shields.io/badge/Languages-EN%20%7C%20FR%20%7C%20AR-0A5EFF)](#multi-language-system)

> **W5D** — *World Five Dimensions* — is a company and a working ontology operating across five macro works: **Cultures/Humanities**, **Built Environment**, **Sciences**, **Governance**, and **Technology**. This CMS powers its website.

---

## Table of Contents

- [Description](#description)
- [Features](#features)
- [Architecture](#architecture)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Multi-Template System](#multi-template-system)
- [Multi-Language System](#multi-language-system)
- [Admin Panel](#admin-panel)
- [Security](#security)
- [Directory Structure](#directory-structure)
- [Development Workflow](#development-workflow)
- [License](#license)
- [Contributing](#contributing)
- [AI Agent Prompt](#ai-agent-prompt)
- [Author](#author)
- [Changelog](#changelog)

---

## Description

**W5D PHP CMS** is a self-contained content management system built for the W5D Team website — a company and a working ontology operating across five macro works: Cultures/Humanities, Built Environment, Sciences, Governance, and Technology. The system is designed for teams that need a fast, template-driven, multi-language site without the overhead of a heavyweight framework.

The CMS runs on vanilla PHP 8.2+ with SQLite — no Composer dependencies, no framework lock-in. It ships with a Blade-inspired templating engine supporting `@extends`, `@section`, `@yield`, `@include`, `{{ }}` escaping, `{!! !!}` raw output, `@if`/`@foreach`/`@for`/`@while` directives, `@csrf`, `@lang`, and `@asset`. Views resolve in two tiers: `views/{ACTIVE_TEMPLATE}/` first, then `views/_shared/` as fallback. Every template is fully self-contained, owning both its `views/{name}/` and `assets/{name}/` directories, so swapping the entire front-end is a single `.env` change.

Multi-language support is automatic. The system detects language from the browser's `Accept-Language` header on first visit, then stores the user's choice in a cookie. No `?lang=xy` query parameters are ever needed. Arabic is fully supported with RTL layout direction, Cairo typography, and Tailwind RTL injection. All strings come from `lang/{code}.php` files with English fallback via `array_merge`.

Configuration is environment-driven. The `.env` file holds application settings, database path, session parameters, webmaster credentials, and — critically — the admin URL path. The `/admin` route is fully obfuscated: change `ADMIN_URL_PATH=/secret-xyz` in `.env`, and the admin dashboard moves with it. Direct HTTP access to the `/admin/` folder is blocked by `.htaccess`; only the router can dispatch admin requests.

Security is built in: CSRF tokens on every form, session fixation protection, HttpOnly/SameSite cookies, path traversal prevention, and defense-in-depth `.htaccess` rules that deny access to `.env`, `.sqlite`, and `.sql` files.

The CMS is offered at **610.5 TND per hour of work** — a promotional rate valid before 2027.

Built for W5D by Maher Kaddoussi. Contact: owner@w5d.tech.

---

## Features

### Templating
- **Blade-inspired engine**, zero Composer dependencies
- Directives: `@extends`, `@section`, `@endsection`, `@show`, `@yield`, `@include`, `@if`/`@elseif`/`@else`/`@endif`, `@foreach`/`@endforeach`, `@for`/`@endfor`, `@while`/`@endwhile`, `@php`/`@endphp`, `@csrf`, `@lang`, `@asset`
- `{{ $var }}` (escaped) and `{!! $var !!}` (raw)
- Compiled templates cached under `storage/cache/views/` (md5 of path + mtime)
- Two-tier view resolution: `views/{ACTIVE_TEMPLATE}/` → `views/_shared/`

### Multi-Template
- Each template owns `views/{name}/` + `assets/{name}/`
- Activate with `ACTIVE_TEMPLATE={name}` in `.env`
- Static assets served directly by Apache (no PHP hit)
- Add a new template without touching existing ones

### Multi-Language
- Auto-detection from `Accept-Language` header (quality-weighted parsing)
- Persists user choice in a cookie (`w5d_lang`)
- **No `?lang=xy`** — the URL never carries language state
- Full RTL support (Arabic) with Cairo typography
- Fallback chain: `lang/{code}.php` → `lang/en.php` → key string

### Admin
- **Obfuscated URL** from `.env` (`ADMIN_URL_PATH=/secret-xyz`)
- `/admin/` folder blocked at Apache level — only the router can dispatch
- Admin views under `admin/views/`, assets under `admin/assets/` (router-served)
- Webmaster credentials from `.env` (`WEBMASTER_USERNAME`, `WEBMASTER_PASSWORD_HASH`)
- CSRF-protected login

### Security
- CSRF tokens on all forms (`csrf_token()`, `csrf_field()`, `csrf_verify()`)
- Session fixation protection (secure cookie params)
- HttpOnly + SameSite=Lax cookies
- Path traversal prevention in router
- Defense-in-depth `.htaccess` (deny `.env`, `.sqlite`, `.sql`, `.md`)
- `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` headers

### Performance
- Static assets cached for 7 days (Cache-Control)
- Compiled template caching
- SQLite with WAL journal mode
- No blocking loaders, no third-party requests by default

---

## Architecture

### Request lifecycle

```
Browser
  │
  ▼
.htaccess  ──── static asset? ─── yes ──► serve file
  │
  no
  ▼
index.php
  │
  ▼
config.php
  ├── Env::load('.env')
  ├── Session::start()
  ├── new Lang('lang/')             ← Accept-Language → cookie → .env fallback
  ├── new View(views/{tpl}/, views/_shared/, storage/cache/views/)
  └── new Router(basePath, adminUrlPath, adminFolder, view)
  │
  ▼
$router->dispatch()
  │
  ├── path starts with ADMIN_URL_PATH? ── yes ─► admin/index.php
  │
  no
  ▼
siteRoute($path)
  │
  ▼
$view->render('pages.home')
  │
  ├── resolve('layouts.app') → views/{tpl}/layouts/app.php (or views/_shared/)
  ├── compile → storage/cache/views/{md5}.php
  ├── evaluate → extract($data), include compiled
  └── echo
```

### Key components

| File | Role |
|---|---|
| `src/Env.php` | Minimal `.env` parser |
| `src/Lang.php` | Language detection, cookie storage, RTL flag, `switcherJs()` |
| `src/View.php` | Blade-like compiler + evaluator |
| `src/Router.php` | Site routes + admin URL obfuscation |
| `src/helpers.php` | `t()`, `e()`, `url()`, `asset()`, `admin_url()`, `csrf_*()` |
| `config.php` | Wires everything; returns `[$view, $router]` |
| `index.php` | Single entry point |

---

## Requirements

- **PHP ≥ 8.2** (typed properties, named arguments, `str_contains`, `str_starts_with`, `match`)
- **SQLite 3** (bundled with PHP by default)
- **Apache 2.4+** with `mod_rewrite` (or Nginx with equivalent config)
- **PHP extensions**: `pdo_sqlite`, `mbstring`, `json`, `session`, `openssl`
- **No Composer** required

---

## Installation

```bash
# 1. Clone
git clone https://github.com/YOUR_USERNAME/w5d-php-cms.git
cd w5d-php-cms

# 2. Create required folders
mkdir -p src admin/assets/css admin/assets/js admin/views/layouts admin/views/pages
mkdir -p assets/default/css assets/default/js assets/default/img assets/default/fonts
mkdir -p views/default/layouts views/default/partials views/default/pages
mkdir -p views/_shared/pages lang
mkdir -p storage/cache/views storage/sessions

# 3. Configure environment
cp .env.example .env
# Edit .env (see Configuration below)

# 4. Generate webmaster password hash
php -r "echo password_hash('YourStrongPassword', PASSWORD_DEFAULT), PHP_EOL;"
# Paste the output into .env → WEBMASTER_PASSWORD_HASH=...

# 5. Point Apache document root to the repository root
# 6. Visit http://localhost/w5d/
```

---

## Configuration

All configuration lives in `.env`. Copy `.env.example` to `.env` and edit:

```dotenv
# ---- Application ----
APP_ENV=local                  # local | production
APP_DEBUG=true                 # verbose errors (disable in production)
APP_URL=http://localhost/w5d
APP_BASE_PATH=/w5d             # subfolder if not at document root
APP_DEFAULT_LANG=en            # fallback language
ACTIVE_TEMPLATE=default        # → views/default/ + assets/default/

# ---- Admin ----
ADMIN_URL_PATH=/admin          # change to /secret-xyz in production
WEBMASTER_USERNAME=admin
WEBMASTER_EMAIL=owner@w5d.tech
WEBMASTER_PASSWORD_HASH=

# ---- Database ----
DB_PATH=storage/w5d.sqlite

# ---- Session ----
SESSION_NAME=w5d_sess
SESSION_LIFETIME=86400
SESSION_SECURE=false           # true in production (HTTPS only)

# ---- License ----
LICENSE_RATE_TND=610.5
OFFER_DEADLINE=2026-12-31
```

### Environment presets

**Local development**
```dotenv
APP_ENV=local
APP_DEBUG=true
SESSION_SECURE=false
```

**Production**
```dotenv
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE=true
ADMIN_URL_PATH=/xyz-9a1b2c-panel   # never the default /admin
```

---

## Multi-Template System

Each template is a **self-contained unit**:

```
assets/{name}/                 ← CSS, JS, images, fonts
views/{name}/                  ← layouts, partials, pages
```

### How it works

1. `ACTIVE_TEMPLATE={name}` in `.env` sets the active template
2. `View` searches `views/{name}/` first, then `views/_shared/` as fallback
3. `asset('css/app.css')` resolves to `/w5d/assets/{name}/css/app.css`
4. `.htaccess` serves `assets/{name}/*` directly (no PHP)

### Adding a new template

1. Create `assets/{new}/` and `views/{new}/`
2. Copy the structural folders from `views/default/` as a starting point:
   ```
   views/{new}/layouts/app.php
   views/{new}/partials/{header,footer,preloader}.php
   views/{new}/pages/home.php
   ```
3. Move the template's CSS/JS/img into `assets/{new}/`
4. Change `.env` → `ACTIVE_TEMPLATE={new}`
5. Reload — new template serves automatically

### What `views/_shared/` is for

Template-agnostic pages that must work regardless of the active template:
- `pages/404.php`
- `pages/error.php`
- Any future generic page (e.g. maintenance)

`views/_shared/` **does not** need its own `layouts/` — it inherits `layouts.app` from the active template. If you want a truly self-contained 404, add `views/_shared/layouts/minimal.php` and change the 404 to `@extends('layouts.minimal')`.

### Asset resolution

| Call | Resolves to |
|---|---|
| `@asset('css/app.css')` | `/w5d/assets/{ACTIVE_TEMPLATE}/css/app.css` |
| `asset('img/logo.png')` | same, via PHP helper |
| `admin_asset('css/dash.css')` | `/w5d/{ADMIN_URL_PATH}/assets/css/dash.css` |

---

## Multi-Language System

### Detection priority

1. **Cookie** `w5d_lang` (user's saved choice)
2. **HTTP `Accept-Language` header** — quality-weighted parsing (e.g. `fr-CH, fr;q=0.9, en;q=0.8` → `fr`)
3. **`.env` `APP_DEFAULT_LANG`**

### Supported languages

| Code | Language | Direction | Font |
|---|---|---|---|
| `en` | English | LTR | Theme default (Cal Sans, Golos Text) |
| `fr` | Français | LTR | Theme default |
| `ar` | العربية | RTL | Cairo (auto-injected) |

### Adding a language

1. Create `lang/{code}.php` mirroring `lang/en.php`
2. Add the code to `Lang::SUPPORTED` in `src/Lang.php`
3. Add a button in your template's language switcher: `<a href="#" data-w5d-lang="{code}">{Label}</a>`

### How switching works

The switcher is pure JS — no server round-trip through a controller:

```html
<a href="#" data-w5d-lang="fr">FR</a>
```

The `Lang::switcherJs()` snippet (injected into the footer by `layouts/app.php`) sets a cookie and reloads. On reload, `Lang` picks up the cookie and serves the right translation file.

**No `?lang=xy` URLs are ever generated.**

---

## Admin Panel

The admin dashboard lives in `admin/`. Its **public URL is set from `.env`**:

```dotenv
ADMIN_URL_PATH=/admin           # local
ADMIN_URL_PATH=/xyz-panel-2026  # production (recommended)
```

### Access rules

| URL | Result |
|---|---|
| `/w5d/{ADMIN_URL_PATH}` | ✅ Admin dashboard (via router) |
| `/w5d/{ADMIN_URL_PATH}/users` | ✅ Admin page (via router) |
| `/w5d/{ADMIN_URL_PATH}/assets/css/dash.css` | ✅ Admin asset (via router) |
| `/w5d/admin` (the physical folder) | 🚫 **403 Forbidden** |
| `/w5d/admin/index.php` | 🚫 **403 Forbidden** |

The `.htaccess` at the root denies any request whose path starts with `/w5d/admin/`. The router bypasses this by `require`-ing `admin/index.php` directly from PHP — never via HTTP.

### Admin assets

Admin CSS/JS/images live in `admin/assets/`. They are served by the router under the obfuscated path:

```html
<link rel="stylesheet" href="<?= admin_asset('css/dash.css') ?>">
```

Which renders as:
```html
<link rel="stylesheet" href="/w5d/admin/assets/css/dash.css">   <!-- local -->
<link rel="stylesheet" href="/w5d/xyz-panel-2026/assets/css/dash.css">  <!-- production -->
```

### Webmaster credentials

Stored in `.env`:

```dotenv
WEBMASTER_USERNAME=admin
WEBMASTER_EMAIL=owner@w5d.tech
WEBMASTER_PASSWORD_HASH=$2y$10$...   # password_hash() output
```

On first boot, the admin login page compares against these values. Change the password by regenerating the hash:

```bash
php -r "echo password_hash('NewPassword', PASSWORD_DEFAULT), PHP_EOL;"
```

---

## Security

### CSRF

Every state-changing form must include:

```html
@csrf
```

This renders `<input type="hidden" name="csrf_token" value="...">`. On POST, verify with:

```php
if (!csrf_verify($_POST['csrf_token'] ?? null)) {
    http_response_code(419);
    exit('CSRF token mismatch');
}
```

### Session

- Session name from `.env` (`SESSION_NAME`)
- HttpOnly cookies (JavaScript cannot read)
- SameSite=Lax
- `SESSION_SECURE=true` in production (HTTPS only)
- Regenerate ID on login: `session_regenerate_id(true);`

### File uploads

- Store outside the web root (`storage/`)
- Random UUID filenames
- Whitelist MIME types
- Never trust `$_FILES['type']` — use `finfo`

### HTTP headers

Set in `.htaccess`:
```
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
Referrer-Policy: strict-origin-when-cross-origin
```

### Denied paths

`.htaccess` `<FilesMatch>` denies direct access to:
- `.env`, `.env.*`
- `*.sqlite`
- `*.sql`
- `*.log`
- `*.md`

---

## Directory Structure

```
w5d-php-cms/
├── .env                         ← real config (gitignored)
├── .env.example                 ← committed template
├── .gitignore
├── .htaccess                    ← root rewrites + security
├── index.php                    ← single entry point
├── config.php                   ← bootstrap
│
├── src/
│   ├── Env.php                  ← .env loader
│   ├── Lang.php                 ← multi-language detection
│   ├── View.php                 ← Blade-like engine
│   ├── Router.php               ← site + admin routing
│   └── helpers.php              ← t, e, url, asset, csrf_*
│
├── assets/                      ← ONE FOLDER PER TEMPLATE
│   ├── default/
│   │   ├── css/
│   │   ├── js/
│   │   ├── img/
│   │   └── fonts/
│   └── {other-template}/
│
├── views/                       ← ONE FOLDER PER TEMPLATE
│   ├── default/
│   │   ├── layouts/
│   │   │   └── app.php
│   │   ├── partials/
│   │   │   ├── header.php
│   │   │   ├── footer.php
│   │   │   └── preloader.php
│   │   └── pages/
│   │       └── home.php
│   ├── {other-template}/
│   └── _shared/                 ← template-agnostic fallback
│       └── pages/
│           └── 404.php
│
├── admin/
│   ├── .htaccess                ← deny all direct HTTP
│   ├── index.php                ← admin entry (router-required)
│   ├── assets/
│   │   ├── css/
│   │   └── js/
│   └── views/
│       ├── layouts/
│       └── pages/
│
├── lang/
│   ├── en.php
│   ├── fr.php
│   └── ar.php
│
└── storage/
    ├── cache/
    │   └── views/               ← compiled Blade templates
    ├── sessions/
    └── w5d.sqlite
```

---

## Development Workflow

### Adding a page

1. Create `views/{ACTIVE_TEMPLATE}/pages/{name}.php`
2. Add a route in `src/Router.php`:

   ```php
   $routes = [
       '/'          => 'pages.home',
       '/about'     => 'pages.about',   // new
   ];
   ```

3. Write the view:

   ```php
   @extends('layouts.app')

   @section('title', 'About')

   @section('content')
   <div class="container py-80">
     <h1>{{ t('about.title') }}</h1>
     <p>{{ t('about.text') }}</p>
   </div>
   @endsection
   ```

### Adding a translation key

1. Add to `lang/en.php`:
   ```php
   'about.title' => 'About W5D',
   'about.text'  => 'A company and a working ontology.',
   ```

2. Add matching keys to `lang/fr.php` and `lang/ar.php`

3. Use in views:
   ```php
   {{ t('about.title') }}
   ```

### Adding a partial

1. Create `views/{ACTIVE_TEMPLATE}/partials/{name}.php`
2. Include from any view:
   ```php
   @include('partials.{name}')
   ```
3. Pass data:
   ```php
   @include('partials.card', ['title' => 'X', 'body' => 'Y'])
   ```

### Clearing the template cache

```bash
rm -rf storage/cache/views/*
```

The cache auto-rebuilds on next request. Individual files are invalidated automatically when their source changes (md5 of path + mtime).

---

## License

**W5D PHP CMS — licensed at 610.5 TND per hour of work.**

Promotional offer valid **before 2027** (strictly before 31 December 2026).

After that date, the standard rate applies.

See [`LICENSE`](./LICENSE) for the full text.

### Footer license block (required on every page)

```html
<p>W5D Admin Dashboard licensed at 610.5 TND/hour of work.
   Promotional offer valid before 2027.</p>
```

This block must appear in every template's footer.

---

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/{name}`
3. Follow the conventions in the [AI Agent Prompt](#ai-agent-prompt) below
4. Run the [Pre-submit checklist](#pre-submit-checklist)
5. Open a Pull Request with a clear description

### Coding conventions

- **PHP 8.2+** with `declare(strict_types=1);` at the top of every `src/*.php`
- **PSR-4**-ish namespacing (`W5D\`) — no autoloader, `require_once` in `config.php`
- **No Composer** dependencies for the core CMS
- **No inline `<style>` or `<script>`** in templates — use the template's `assets/{name}/` folder
- **Every class method typed** (parameters + return)
- **Every user-supplied string escaped** with `e()` or `{{ }}`
- **Every state-changing form** includes `@csrf`

---

## AI Agent Prompt

The following prompt is designed for AI coding assistants (Claude, GPT, Copilot, etc.) working on this codebase. Copy it verbatim into the assistant's system prompt or paste it at the start of a session.

```
================================================================================
PROJECT: w5d-php-cms
ROLE: You are a senior PHP engineer working on W5D PHP CMS.
================================================================================

## CONTEXT

W5D is a company and a working ontology operating across 5 macro works:
  1. Cultures/Humanities  (slug: cultures)
  2. Built Environment    (slug: built)
  3. Sciences             (slug: sciences)
  4. Governance           (slug: governance)
  5. Technology           (slug: technology)

The CMS powers the W5D Team website. It runs on vanilla PHP 8.2+ with SQLite,
has a Blade-like templating engine, multi-template support, and auto language
detection (en/fr/ar with RTL for ar).

## TECH STACK — HARD CONSTRAINTS

- PHP 8.2+ only. NO Composer. NO frameworks (no Laravel, Symfony, etc.).
- SQLite 3 (PDO) only. NO MySQL, PostgreSQL.
- Vanilla JS only. NO build tools. NO npm bundler.
- Every PHP file in src/ MUST start with `declare(strict_types=1);`.
- Every class lives in namespace `W5D\`.
- Use `require_once` for loading — no autoloader.

## TEMPLATE RESOLUTION

Views resolve in TWO tiers, in this exact order:
  1. views/{ACTIVE_TEMPLATE}/       ← from .env ACTIVE_TEMPLATE
  2. views/_shared/                 ← template-agnostic fallback

Assets resolve as: assets/{ACTIVE_TEMPLATE}/{path}
Never hardcode paths — use @asset('...') or asset('...').

## BLADE-LIKE SYNTAX — SUPPORTED DIRECTIVES

Views use .php files with Blade-like directives. ONLY these are supported:

  @extends('layouts.app')
  @section('name') ... @endsection
  @section('name', 'inline value')
  @yield('name')  /  @yield('name', 'default')
  @include('partials.name')
  @include('partials.name', ['x' => 1])
  @if(...) @elseif(...) @else @endif
  @foreach(...) @endforeach
  @for(...) @endfor
  @while(...) @endwhile
  @php ... @endphp
  @csrf
  @lang('key')
  @asset('path')

  {{ $var }}   → escaped (htmlspecialchars, ENT_QUOTES)
  {!! $var !!} → raw

DO NOT invent new directives. If you need new syntax, extend
src/View.php::compileSource() — but prefer existing patterns.

## HELPERS — ALWAYS USE THESE

  t('key')             → translate (Lang class)
  t('key', 'default')  → translate with fallback
  e($value)            → escape HTML
  url('/path')         → absolute URL with base path
  asset('css/x.css')   → assets/{ACTIVE_TEMPLATE}/css/x.css
  admin_url('/x')      → /{ADMIN_URL_PATH}/x
  admin_asset('x.css') → /{ADMIN_URL_PATH}/assets/x.css
  csrf_token()         → current CSRF token
  csrf_field()         → <input type="hidden" name="csrf_token" value="...">
  csrf_verify($token)  → bool

NEVER:
  - hardcode /w5d/ or /admin in URLs → use url() or admin_url()
  - echo raw user input          → use e() or {{ }}
  - construct admin links manually → use admin_url()

## WHAT NOT TO TOUCH

Read-only files (assume maintained upstream):
  - assets/{template}/css/bootstrap.css
  - assets/{template}/css/font-awesome-pro.css
  - assets/{template}/css/animate.css
  - assets/{template}/css/swiper-bundle.css
  - assets/{template}/css/magnific-popup.css
  - assets/{template}/css/nice-select.css
  - assets/{template}/css/spacing.css
  - assets/{template}/css/w5d.css

Where to add NEW CSS:
  - assets/{template}/css/w5d-dynamic.css

Why: the base CSS files come from the theme vendor. We don't fork them —
we layer additions on top via w5d-dynamic.css.

## LANGUAGE KEYS — NAMING CONVENTION

Format:  {section}.{key}
Examples:
  'nav.home'
  'nav.services'
  'hero.1.title'
  'macro.cultures'
  'macro.built'
  'pricing.basic.price'
  'footer.copyright'

Rules:
  - Lowercase
  - Dot-separated
  - No spaces
  - Prefix with section name (nav, hero, macro, footer, etc.)

Every key MUST exist in lang/en.php. Missing keys in fr/ar fall back to en
via array_merge in src/Lang.php.

## FILE PATHS — WHERE TO PUT THINGS

New page       → views/{tpl}/pages/{name}.php  +  route in src/Router.php
New partial    → views/{tpl}/partials/{name}.php
New layout     → views/{tpl}/layouts/{name}.php
New CSS        → assets/{tpl}/css/{name}.css
New JS         → assets/{tpl}/js/{name}.js
New language   → lang/{code}.php  +  add to Lang::SUPPORTED
New helper     → src/helpers.php
New class      → src/{Name}.php
New admin page → admin/views/pages/{name}.php  +  handle in admin/index.php
New admin asset→ admin/assets/{css|js}/{name}.{css|js}

## COMMON TASKS — EXAMPLES

### Add a page

1. views/{tpl}/pages/about.php:
   @extends('layouts.app')
   @section('title', 'About')
   @section('content')
     <div class="container py-80">
       <h1>{{ t('about.title') }}</h1>
     </div>
   @endsection

2. src/Router.php $routes array:
   '/about' => 'pages.about',

3. lang/en.php:
   'about.title' => 'About W5D',

### Add a translation key

1. Add to ALL three: lang/en.php, lang/fr.php, lang/ar.php
2. Use as: {{ t('my.key') }}

### Add a new template

1. mkdir assets/{new}/css assets/{new}/js assets/{new}/img assets/{new}/fonts
2. mkdir views/{new}/layouts views/{new}/partials views/{new}/pages
3. Copy structure from views/default/ as a starting point
4. .env → ACTIVE_TEMPLATE={new}
5. Test at http://localhost/w5d/

### Add a new language

1. cp lang/en.php lang/{code}.php
2. Translate every value
3. src/Lang.php::SUPPORTED → add '{code}'
4. Add to Lang::RTL if right-to-left (e.g. 'he', 'fa')
5. Add switcher button: <a href="#" data-w5d-lang="{code}">{Label}</a>

### Add an admin page

1. admin/views/pages/{name}.php
2. admin/index.php — add route in the $subPath switch
3. Verify: /{ADMIN_URL_PATH}/{name}

## PRE-SUBMIT CHECKLIST

Before you finish any change, verify ALL of these:

[ ] No inline <style> or <script> in views — moved to assets/{tpl}/
[ ] Every user-supplied string escaped with e() or {{ }}
[ ] Every state-changing form has @csrf
[ ] All new translation keys exist in lang/en.php
[ ] All URLs use url() / asset() / admin_url() — no hardcoded paths
[ ] No ?lang=xy query strings anywhere — use data-w5d-lang
[ ] No new Composer dependencies
[ ] All new PHP files have declare(strict_types=1);
[ ] All new classes are in namespace W5D\
[ ] Change tested at:
    - http://localhost/w5d/                      (home)
    - http://localhost/w5d/{ADMIN_URL_PATH}      (admin stub)
    - http://localhost/w5d/admin                 (must 403)
    - Lang switch EN → FR → AR (must reload cleanly)
[ ] License block still present in footer
[ ] Clearing cache works: rm -rf storage/cache/views/*

## WHEN TO ASK FOR CLARIFICATION

Ask before:
  - Changing the View engine's supported directives
  - Adding a Composer dependency (generally refuse, ask why)
  - Changing .env keys (may break existing deployments)
  - Changing the admin URL scheme
  - Deleting any file in assets/{tpl}/ that isn't w5d-dynamic.css
  - Modifying src/Env.php or src/Lang.php detection order

Do NOT ask before:
  - Adding a new page/partial/route
  - Adding a new translation key
  - Creating a new template folder
  - Fixing an obvious bug
  - Writing tests

## GOTCHAS

1. Sessions MUST start before any output. If you echo HTML before
   session_start(), cookies won't set. config.php handles this — don't
   move session logic.

2. CSRF token is per-session. If you regenerate the session ID, the token
   survives (stored in $_SESSION['csrf_token']). Don't wipe $_SESSION.

3. The admin folder MUST stay blocked at Apache level. Do not add rules
   to .htaccess that unblock /admin/*. The router is the only path.

4. Language cookie is HttpOnly=false (JavaScript needs to read it? No — JS
   WRITES it, PHP READS it). Actually: PHP sets it via setcookie() with
   httponly=false so the JS switcher can write it. Keep this.

5. Compiled views live in storage/cache/views/. They are keyed by md5(path
   + mtime). If you edit a view and don't see changes, clear the cache.

6. Template resolution is FIRST match wins: views/{tpl}/ then views/_shared/.
   If a file exists in both, views/{tpl}/ wins. Use _shared/ ONLY for
   template-agnostic pages.

7. The license block (610.5 TND/hour, before 2027) MUST appear in the
   footer of every template. Don't remove it.

## AI Agent Prompt — Next Step: Template Onboarding

> This section extends the AI Agent Prompt above. It documents the **template onboarding workflow**: the exact sequence an AI agent must follow when the human sends a template's HTML + CSS + JS files (or an admin dashboard template).

================================================================================
NEXT STEP — TEMPLATE ONBOARDING
================================================================================

You will receive ONE of these two inputs at a time:

  (A) A SITE TEMPLATE        — HTML + CSS + JS files for the public-facing site
  (B) AN ADMIN TEMPLATE      — HTML + CSS + JS files for the admin dashboard

Your job is to integrate that template into the CMS without touching other
templates. You do NOT redesign, restyle, or "improve" the template. You only
refactor it into the CMS's file layout and wire it to the templating engine.

--------------------------------------------------------------------------------
WORKFLOW (SITE TEMPLATE) — when input is (A)
--------------------------------------------------------------------------------

Assume the human has named the template. If not, ask for a slug
(lowercase, hyphenated, e.g. `nexva`, `w5d-classic`). Call it {name}.

STEP 1 — CREATE THE TEMPLATE FOLDERS

  mkdir -p views/{name}/layouts
  mkdir -p views/{name}/partials
  mkdir -p views/{name}/pages
  mkdir -p assets/{name}/css
  mkdir -p assets/{name}/js
  mkdir -p assets/{name}/img
  mkdir -p assets/{name}/fonts

STEP 2 — MOVE THE TEMPLATE'S CSS/JS/IMG/FONTS

  From the template's `assets/css/`   → assets/{name}/css/
  From the template's `assets/js/`    → assets/{name}/js/
  From the template's `assets/img/`   → assets/{name}/img/
  From the template's `assets/fonts/` → assets/{name}/fonts/

  Preserve subfolders. Do NOT rewrite `url(...)` inside the CSS —
  if the template referenced `../fonts/x.woff2`, it will still resolve
  because the relative folder structure is preserved.

STEP 3 — SPLIT THE HTML

  For each HTML file the human sends:

  A. `index.html` (or the main/home page) becomes the LAYOUT + a PAGE:

     views/{name}/layouts/app.php     ← the full <html>…</html> shell
     views/{name}/pages/home.php      ← @extends('layouts.app') + @section('content') with the <main> content

  B. Reusable <header>, <nav>, <footer>, <preloader>, mobile off-canvas,
     search popup, language switcher, cookie banner — extract EACH into:

     views/{name}/partials/header.php
     views/{name}/partials/footer.php
     views/{name}/partials/preloader.php
     views/{name}/partials/offcanvas.php
     views/{name}/partials/search.php
     views/{name}/partials/lang-switcher.php
     views/{name}/partials/cookie-banner.php     ← only if present in the source

  C. Any OTHER HTML file (about.html, services.html, contact.html, etc.)
     becomes a PAGE:

     views/{name}/pages/{slug}.php

  D. If the template has reusable section blocks (hero variants,
     service cards, testimonials, pricing tiers, team cards), extract them
     into `views/{name}/partials/sections/` and include them from pages:

     views/{name}/partials/sections/hero.php
     views/{name}/partials/sections/services.php
     views/{name}/partials/sections/pricing.php
     ...

STEP 4 — WIRE THE BLADE-LIKE ENGINE

  In the LAYOUT (views/{name}/layouts/app.php):

    • Replace <title> and meta description with @yield():
        <title>@yield('title', 'W5D') — W5D</title>
        <meta name="description" content="@yield('description', t('site.tagline'))">

    • Set <html lang="{{ $lang }}" dir="{{ $dir }}">

    • Replace all CSS/JS src/href with @asset():
        <link rel="stylesheet" href="@asset('css/bootstrap.css')">
        <script src="@asset('js/app.js')"></script>

    • Replace the header/footer/preloader/offcanvas block with @include():
        @include('partials.preloader')
        @include('partials.header')
        <main>@yield('content')</main>
        @include('partials.footer')

    • Just before </body>, keep the base scripts the template needs, then add:
        <script src="@asset('js/w5d-dynamic.js')" defer></script>
        <script>{!! W5D\Lang::switcherJs() !!}</script>
        @yield('scripts')

    • Add the Arabic RTL block AFTER the base <link> tags:
        @if($dir === 'rtl')
          <script src="https://cdn.tailwindcss.com"></script>
          <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
          <style>
            body, h1, h2, h3, h4, p, span, a, li, button { font-family: 'Cairo', sans-serif; }
          </style>
        @endif

  In EACH PAGE (views/{name}/pages/{slug}.php):

        @extends('layouts.app')

        @section('title', 'About')

        @section('content')
          ... page body ...
        @endsection

  In EACH PARTIAL, replace text with {{ t('key') }}. See STEP 5 for
  key extraction.

STEP 5 — EXTRACT LANGUAGE KEYS

  Walk every visible user-facing string in the template and replace it
  with {{ t('section.key') }}. Then add the key to ALL THREE language files:

    lang/en.php    ← original string (source of truth)
    lang/fr.php    ← copy of EN value (human will translate later)
    lang/ar.php    ← copy of EN value (human will translate later)

  Key naming (see the main AI Agent Prompt for the full rule):

    Navbar links         → 'nav.home', 'nav.about', 'nav.services', …
    Hero                 → 'hero.subtitle', 'hero.1.title', 'hero.1.cta', …
    Section headings     → 'choose.title', 'about.title', 'pricing.title', …
    Card titles          → 'services.card.1.title', 'services.card.1.desc', …
    Buttons              → 'btn.contact', 'btn.read_more', 'btn.submit', …
    Footer               → 'footer.tagline', 'footer.quick_links', 'footer.license', …
    Form labels          → 'form.name', 'form.email', 'form.message', …
    404                  → 'errors.not_found'

  If the template has a phrase that is clearly data (numbers, names of
  people, addresses), DO NOT translate it. Leave it in place or move it
  to `views/{name}/partials/data/` if it repeats.

STEP 6 — REMOVE INLINE STYLES AND SCRIPTS

  For EVERY <style>…</style> block found in the template's HTML:
    • Move the rules into assets/{name}/css/w5d-dynamic.css
    • Keep the file ordered by section (see the CSS file's top comment)
    • Delete the <style> block from the HTML

  For EVERY inline <script>…</script> block:
    • If it is a third-party CDN loader or config (Swiper init, GSAP,
      countdown, etc.), keep it inside the layout or the partial it belongs
      to — BUT prefer moving it to assets/{name}/js/w5d-dynamic.js
    • If it is a small page-specific effect (e.g. an accordion on the About
      page only), move it to assets/{name}/js/pages/{slug}.js and load it
      via @section('scripts') in that page:

        @section('scripts')
          <script src="@asset('js/pages/about.js')" defer></script>
        @endsection

  The ONLY allowed inline <style> is the Arabic RTL Cairo block in the
  layout (guarded by @if($dir === 'rtl')).

STEP 7 — WIRE THE LANGUAGE SWITCHER

  Find the template's language selector (usually in the header or in a
  settings menu). Replace its links with:

    <a href="#" data-w5d-lang="en">EN</a>
    <a href="#" data-w5d-lang="fr">FR</a>
    <a href="#" data-w5d-lang="ar">AR</a>

  If the template uses a dropdown or "globe" icon, keep the styling —
  only the <a> hrefs change to `#` + `data-w5d-lang`.

  NEVER generate ?lang=xy URLs.

STEP 8 — ADD ROUTES

  In src/Router.php, add one entry per page:

    $routes = [
      '/'            => 'pages.home',
      '/about'       => 'pages.about',
      '/services'    => 'pages.services',
      '/contact'     => 'pages.contact',
      '/blog'        => 'pages.blog',
      '/blog/{slug}' => 'pages.blog-detail',   ← if dynamic
      ...
    ];

  For dynamic routes (e.g. /services/{macro}), extend siteRoute() with a
  small dispatch table:

    if (preg_match('#^/services/([a-z]+)$#', $path, $m)) {
      $macro = $m[1];
      $data  = ['macro' => $macro, 'services' => $services];
      echo $this->view->render('pages.service-category', $data);
      return;
    }

STEP 9 — UPDATE .env

  ACTIVE_TEMPLATE={name}

  Add any template-specific config keys under a `# --- Template: {name} ---`
  block at the bottom of `.env`:

    TEMPLATE_{NAME}_HERO_VIDEO=assets/{name}/img/hero.mp4
    TEMPLATE_{NAME}_BRAND_COLOR=#0A5EFF

  These are read via Env::get('TEMPLATE_{NAME}_…').

STEP 10 — VERIFY

  Run all of these before declaring done:

  [ ] http://localhost/w5d/                     → home page renders
  [ ] http://localhost/w5d/about                → page renders
  [ ] http://localhost/w5d/contact              → page renders
  [ ] http://localhost/w5d/nonexistent          → 404 from _shared
  [ ] Language switcher: EN → FR → AR works
  [ ] In AR mode: <html dir="rtl">, Cairo applied, layout flips
  [ ] http://localhost/w5d/{ADMIN_URL_PATH}     → admin stub still loads
  [ ] http://localhost/w5d/admin                → 403
  [ ] DevTools → Network → all assets 200 OK
  [ ] DevTools → Console → no errors
  [ ] View source → no inline <style> (except the Cairo block in AR)
  [ ] View source → no inline <script> (except the Arabic injection)
  [ ] Footer shows: "W5D … licensed at 610.5 TND/hour … before 2027."
  [ ] rm -rf storage/cache/views/* → pages still render (cache rebuild)

--------------------------------------------------------------------------------
WORKFLOW (ADMIN TEMPLATE) — when input is (B)
--------------------------------------------------------------------------------

Assume the human sends a full admin dashboard template. Its slug is fixed:
`admin` (not from .env — the folder is `admin/`).

STEP 1 — CREATE THE ADMIN FOLDERS

  mkdir -p admin/views/layouts
  mkdir -p admin/views/partials
  mkdir -p admin/views/pages
  mkdir -p admin/assets/css
  mkdir -p admin/assets/js
  mkdir -p admin/assets/img
  mkdir -p admin/assets/fonts

STEP 2 — MOVE THE ADMIN'S CSS/JS/IMG/FONTS

  From the template's assets/ → admin/assets/
  (same rule as site templates, preserve subfolders)

STEP 3 — SPLIT THE ADMIN HTML

  admin/index.html becomes:

    admin/views/layouts/app.php    ← full shell
    admin/views/pages/dashboard.php ← @extends('layouts.app')

  Extract reusable pieces into:

    admin/views/partials/sidebar.php
    admin/views/partials/topbar.php
    admin/views/partials/breadcrumb.php
    admin/views/partials/footer.php        ← footer of the admin shell
    admin/views/partials/notification.php

  Each admin page the template ships (users.html, settings.html, etc.)
  becomes:

    admin/views/pages/{slug}.php

STEP 4 — CREATE A SECOND VIEW ENGINE FOR ADMIN

  The public View class resolves to views/{tpl}/ + views/_shared/.
  Admin needs its own search paths.

  In config.php, AFTER the public $view, add:

    $adminView = new View(
        W5D_ROOT . '/admin/views',
        W5D_ROOT . '/admin/views',   // no fallback; admin is self-contained
        W5D_ROOT . '/storage/cache/views-admin'
    );
    $adminView->share('lang', $lang->code());
    $adminView->share('dir',  $lang->dir());

  In Router, pass $adminView into the admin entry:

    $_SERVER['W5D_ADMIN_PATH'] = $sub;
    $_SERVER['W5D_ADMIN_VIEW'] = $adminView;   ← NEW
    require $this->adminFolder . '/index.php';

  In admin/index.php, retrieve it:

    $adminView = $_SERVER['W5D_ADMIN_VIEW'] ?? $GLOBALS['w5d_admin_view'];

STEP 5 — AUTHENTICATE IN admin/index.php

  Replace the stub body with:

    session already started by config.php.

    // 1. Which admin page is requested?
    $subPath = $_SERVER['W5D_ADMIN_PATH'] ?? '/';
    $subPath = '/' . ltrim($subPath, '/');

    // 2. Public admin routes (no auth required)
    $publicRoutes = [
      '/login' => 'pages.login',
    ];

    // 3. Protected routes (auth required)
    $protectedRoutes = [
      '/'         => 'pages.dashboard',
      '/users'    => 'pages.users',
      '/settings' => 'pages.settings',
      '/content'  => 'pages.content',
      // extend as the admin template ships pages
    ];

    // 4. If POST to /login, verify credentials
    if ($subPath === '/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $u = trim($_POST['username'] ?? '');
        $p = (string) ($_POST['password'] ?? '');
        $expectedUser = (string) Env::get('WEBMASTER_USERNAME', '');
        $expectedHash = (string) Env::get('WEBMASTER_PASSWORD_HASH', '');

        if ($u === $expectedUser && $expectedHash !== ''
            && password_verify($p, $expectedHash)
            && csrf_verify($_POST['csrf_token'] ?? null)) {
            session_regenerate_id(true);
            $_SESSION['w5d_admin'] = ['user' => $u, 'at' => time()];
            header('Location: ' . admin_url('/'));
            exit;
        }

        $_SESSION['admin_login_error'] = 'Invalid credentials.';
        header('Location: ' . admin_url('/login'));
        exit;
    }

    // 5. If not authenticated and requesting a protected route → /login
    $authed = !empty($_SESSION['w5d_admin']);
    if (!$authed && !isset($publicRoutes[$subPath])) {
        header('Location: ' . admin_url('/login'));
        exit;
    }

    // 6. Logout
    if ($subPath === '/logout') {
        unset($_SESSION['w5d_admin']);
        session_regenerate_id(true);
        header('Location: ' . admin_url('/login'));
        exit;
    }

    // 7. Dispatch to view
    $viewName = $protectedRoutes[$subPath] ?? $publicRoutes[$subPath] ?? null;

    if ($viewName === null) {
        http_response_code(404);
        echo $adminView->render('pages.404');
        return;
    }

    echo $adminView->render($viewName, [
        'user'       => $_SESSION['w5d_admin']['user'] ?? null,
        'admin_base' => admin_url('/'),
    ]);

STEP 6 — REWRITE ADMIN ASSETS

  Every <link> and <script src> in admin/views/layouts/app.php becomes:

    <link rel="stylesheet" href="<?= admin_asset('css/dash.css') ?>">
    <script src="<?= admin_asset('js/dash.js') ?>"></script>

  admin_asset() already knows the obfuscated path — do NOT hardcode
  `/admin/assets/...` anywhere.

STEP 7 — ADD ADMIN LANGUAGE KEYS

  Admin uses the SAME lang/*.php files as the public site.
  New keys use a distinct prefix:

    'admin.title'              → 'W5D Admin'
    'admin.nav.dashboard'      → 'Dashboard'
    'admin.nav.users'          → 'Users'
    'admin.login.username'     → 'Username'
    'admin.login.password'     → 'Password'
    'admin.login.submit'       → 'Sign in'
    'admin.login.error'        → 'Invalid credentials.'

  Add to all three: lang/en.php, lang/fr.php, lang/ar.php.

STEP 8 — VERIFY ADMIN

  [ ] http://localhost/w5d/{ADMIN_URL_PATH}            → redirects to /login
  [ ] http://localhost/w5d/{ADMIN_URL_PATH}/login      → login form
  [ ] POST with wrong creds                            → error message
  [ ] POST with WEBMASTER_USERNAME + password           → redirects to /
  [ ] http://localhost/w5d/{ADMIN_URL_PATH}            → dashboard
  [ ] /{ADMIN_URL_PATH}/users                          → users page
  [ ] /{ADMIN_URL_PATH}/logout                         → back to login
  [ ] http://localhost/w5d/admin                       → 403
  [ ] Change ADMIN_URL_PATH=/new-secret in .env        → /old-secret 404,
                                                         /new-secret works
  [ ] Arabic works inside admin (Cairo + RTL)
  [ ] Session cookie is HttpOnly; no token in URL

--------------------------------------------------------------------------------
RULES THAT APPLY TO BOTH WORKFLOWS
--------------------------------------------------------------------------------

DO:
  • Preserve the template's HTML structure, class names, and JS behaviour
  • Add CMS directives AROUND the template — never rewrite its markup
  • Test on a real browser, not just "looks right in the file"
  • Add missing translation keys even if translations are absent
  • Keep the footer license block
  • Ask for the template's slug if not provided

DO NOT:
  • Redesign or "clean up" the template's visual design
  • Introduce a CSS framework that isn't already in the template
  • Introduce jQuery if the template uses vanilla JS (or vice versa)
  • Rename CSS classes the template's JS depends on
  • Delete or overwrite another template's folder
  • Modify .htaccess to unblock /admin/*
  • Add ?lang=xy query parameters
  • Remove the Arabic RTL injection from the layout

--------------------------------------------------------------------------------
TEMPLATE REGISTRY — keep this list in sync
--------------------------------------------------------------------------------

After onboarding a site template, update this list in README.md:

  | Slug          | Path              | Status   | Onboarded  |
  |---------------|-------------------|----------|------------|
  | default       | views/default/    | demo     | 2026-11-11 |
  | {name}        | views/{name}/     | active   | YYYY-MM-DD |
  | {name-2}      | views/{name-2}/   | standby  | YYYY-MM-DD |

Only ONE is active at a time — the one in `.env` → ACTIVE_TEMPLATE.

================================================================================
END OF NEXT-STEP PROMPT
================================================================================

## AI Agent Prompt

> Copy this block verbatim into an AI coding assistant's system prompt, or paste it at the start of a session. It is the single source of truth for how to build, extend, and maintain W5D PHP CMS.

```text
================================================================================
PROJECT: w5d-php-cms
ROLE: Senior PHP engineer and CMS architect.
================================================================================

## 1. PROJECT CONTEXT

W5D — World Five Dimensions — is a company and a working ontology operating
across 5 macro works:

  1. Cultures/Humanities  (slug: cultures)
  2. Built Environment    (slug: built)
  3. Sciences             (slug: sciences)
  4. Governance           (slug: governance)
  5. Technology           (slug: technology)

The CMS powers the W5D Team website AND a multi-role admin dashboard.

## 2. HARD CONSTRAINTS — DO NOT VIOLATE

- PHP 8.2+ only. NO Composer. NO frameworks (no Laravel, Symfony, Slim, etc.).
- SQLite 3 (PDO) only. NO MySQL, PostgreSQL.
- Vanilla JS only. NO build tools, NO npm bundler.
- Every PHP file in src/ MUST start with `declare(strict_types=1);`.
- Every class lives in namespace `W5D\`.
- Use `require_once` for loading — no autoloader.
- OAuth: vanilla cURL only (no `league/oauth2-client`).

## 3. SYSTEM ARCHITECTURE

Three request types:

  A. Public site         → views/{ACTIVE_TEMPLATE}/  →  pages.home, pages.about, …
  B. Admin dashboard     → admin/views/              →  gated by Auth + ACL
  C. Static assets       → served by Apache (no PHP)

Flow:
  Browser → .htaccess
    → /admin/* (physical)     → 403 (blocked)
    → /assets/{tpl}/*         → serve file
    → everything else         → index.php → config.php → Router::dispatch()
        → /{ADMIN_URL_PATH}/* → admin/index.php (Auth + ACL + View)
        → otherwise           → site routes (View)

## 4. ROLES (7 total) — data/roles.php

  owner    — full access, billing, settings, can promote/demote anyone
  admin    — CMS admin, content, users, templates (no billing)
  manager  — team manager: roster, assignments, approvals
  member   — team member: own tasks, submissions
  investor — portfolio view, ROI, dividends
  customer — invoices, services, support
  user     — basic registered user: profile, notifications

Rank (higher = more privileged):
  owner(100) > admin(90) > manager(70) > member(50) > investor(40) > customer(30) > user(10)

Rank is ONLY for ordering. Authorization is ALWAYS via ACL::can() — never
via rank comparison.

## 5. PERMISSIONS — data/permissions.php

A permission is a dot-separated string. Each permission lists the roles that
hold it. ACL::can($role, $perm) returns true iff $role is listed.

Current permissions (extend only when necessary):

  manage.users, manage.roles, manage.billing, manage.settings,
  manage.templates, manage.content, manage.team, assign.work,
  approve.work, submit.work, view.own.work, view.team.reports,
  view.all.reports, view.portfolio, manage.portfolio, view.invoices,
  manage.invoices, view.support, manage.support, view.dashboard,
  view.profile, edit.profile, view.notifications.

Rules:
  - Widgets and admin routes MUST guard by permission, not by role.
  - When adding a permission: add to data/permissions.php AND list who has it.
  - When adding a role: add to data/roles.php AND review every permission.

## 6. AUTHENTICATION — src/Auth.php, src/Auth/OAuth.php

Three methods only:

  A. Email + password
     - ONLY for @w5d.tech business emails (Auth::isBusinessEmail()).
     - Against WEBMASTER_USERNAME / WEBMASTER_PASSWORD_HASH in .env (for
       the webmaster account), OR against users.password_hash (for staff).
     - password_hash(PASSWORD_DEFAULT) / password_verify().

  B. Google OAuth
     - Any Google account.
     - New users get role `user`.
     - Admin/owner can promote via /{ADMIN_URL_PATH}/users.

  C. GitHub OAuth
     - Any GitHub account.
     - Same onboarding as Google.

NEVER add: Facebook, Apple, Twitter, Microsoft, Discord, or any other provider
without explicit owner approval. NEVER allow non-@w5d.tech password sign-ups.

Session:
  - session_regenerate_id(true) on login AND logout.
  - Cookie: HttpOnly, SameSite=Lax, Secure in production.
  - Auth::check() / Auth::role() / Auth::user() are the only session readers.

## 7. TEMPLATE RESOLUTION

Views (site):
  1. views/{ACTIVE_TEMPLATE}/
  2. views/_shared/
FIRST match wins. `_shared/` is ONLY for template-agnostic pages (404, error).

Views (admin):
  admin/views/  ONLY (no fallback — admin is self-contained).

Assets (site):    assets/{ACTIVE_TEMPLATE}/{path}
Assets (admin):   admin/assets/{path}   (served via router, not .htaccess)

NEVER hardcode paths. Use:
  @asset('css/x.css')     → assets/{tpl}/css/x.css
  admin_asset('css/x.css')→ /{ADMIN_URL_PATH}/assets/css/x.css
  url('/about')           → /w5d/about
  admin_url('/users')     → /w5d/{ADMIN_URL_PATH}/users

## 8. BLADE-LIKE SYNTAX

Only these directives are supported. Do NOT invent new ones.

  @extends('layouts.app')
  @section('name') … @endsection
  @section('name', 'inline')
  @yield('name')  /  @yield('name', 'default')
  @include('partials.name')
  @include('partials.name', ['x' => 1])
  @if(...) @elseif(...) @else(@endif)
  @foreach(...) @endforeach
  @for(...) @endfor
  @while(...) @endwhile
  @php … @endphp
  @csrf
  @lang('key')
  @asset('path')

  {{ $var }}     → escaped (htmlspecialchars, ENT_QUOTES)
  {!! $var !!}   → raw (ONLY for trusted HTML)

## 9. HELPERS — ALWAYS USE THESE

  t('key')             translate
  t('key', 'default')  translate with fallback
  e($value)            escape HTML
  url('/path')         base-path-prefixed URL
  asset('css/x.css')   template-scoped asset
  admin_url('/x')      admin URL (obfuscated)
  admin_asset('x.css') admin asset
  csrf_token()         current token
  csrf_field()         hidden input
  csrf_verify($t)      bool

Shortcut in views:
  {{ t('nav.home') }}       → translated + escaped
  {!! t('nav.home') !!}     → translated + raw (rare; only for HTML strings)
  @lang('nav.home')         → translated (raw echo)

## 10. ADMIN DASHBOARDS — SEVEN PAGES, ONE TEMPLATE

There is ONE admin template (admin/views/layouts/app.php) and SEVEN dashboard
page views. Each user lands on the dashboard for their role after login.

  Role        Dashboard view              URL after login
  ----------  --------------------------  -----------------------------------
  owner       pages.dashboard-owner       /{ADMIN_URL_PATH}/
  admin       pages.dashboard-admin       /{ADMIN_URL_PATH}/
  manager     pages.dashboard-manager     /{ADMIN_URL_PATH}/
  member      pages.dashboard-member      /{ADMIN_URL_PATH}/
  investor    pages.dashboard-investor    /{ADMIN_URL_PATH}/
  customer    pages.dashboard-customer    /{ADMIN_URL_PATH}/
  user        pages.dashboard-user        /{ADMIN_URL_PATH}/

All seven share:
  - admin/views/layouts/app.php       (sidebar + topbar + footer)
  - admin/views/partials/sidebar.php  (renders nav filtered by permission)
  - admin/views/partials/topbar.php
  - admin/views/partials/footer.php

Each dashboard page MUST:
  1. @extends('layouts.app')
  2. @section('title', t('admin.dashboard.' . $role))
  3. Loop over ACL::widgetsFor($role) and render each via @include('widgets.' . $key)
  4. Group widgets by `group` (business, people, work, content, system, account, services, invest)

Example dashboard (pages.dashboard-owner):

  @extends('layouts.app')
  @section('title', t('admin.dashboard.owner'))
  @section('content')
    @php $grouped = \W5D\ACL::widgetsGrouped('owner'); @endphp
    @foreach($grouped as $group => $widgets)
      <section class="admin-group">
        <h2>{{ t('admin.group.' . $group) }}</h2>
        <div class="row g-3">
          @foreach($widgets as $key => $def)
            <div class="col-{{ $def['size'] === 'full' ? '12' : ($def['size'] === 'lg' ? '8' : ($def['size'] === 'sm' ? '4' : '6')) }}">
              @include('widgets.' . $key)
            </div>
          @endforeach
        </div>
      </section>
    @endforeach
  @endsection

## 11. ADMIN WIDGETS — 5 + 6 + 4 + 3 + 4 + 4 + 3

Widgets are defined in data/widgets.php and rendered by
admin/views/partials/widgets/{key}.php. Each partial receives $def (from the
registry) as its only contract.

Owner (5):
  owner.revenue, owner.users_by_role, owner.system_health,
  owner.billing, owner.recent_activity

Admin (6):
  admin.content_stats, admin.registrations, admin.uploads,
  admin.approvals, admin.templates, admin.server_log

Manager (4):
  manager.team, manager.pending_work, manager.assignments,
  manager.performance

Member (3):
  member.my_tasks, member.my_submissions, member.notifications

Investor (4):
  investor.portfolio, investor.roi, investor.investments,
  investor.dividends

Customer (4):
  customer.services, customer.invoices, customer.tickets,
  customer.renewals

User (3):
  user.profile, user.notifications
  (note: user.notifications is shared with member/investor/customer —
   the widget guard is the same permission, so no duplication)

Adding a widget:
  1. Add to data/widgets.php with {title, perm, roles, group, icon, size}
  2. Create admin/views/partials/widgets/{key}.php
  3. Widget receives $def; it MUST itself call Auth::require($def['perm'])
     before rendering sensitive data — do not trust the caller.

## 12. ADMIN COMPONENTS

Reusable atomic components (admin/views/partials/components/):

  button.php        variants: primary|secondary|ghost|danger, sizes: sm|md|lg
  card.php          props: title, body, actions, variant: default|elevated|outlined
  badge.php         variants: success|warning|danger|info|neutral
  avatar.php        props: src, name, size: xs|sm|md|lg, fallback initials
  form-input.php    props: name, label, type, value, required, error
  form-select.php   props: name, label, options[], value
  form-textarea.php props: name, label, value, rows
  form-toggle.php   props: name, label, checked
  table.php         props: columns[], rows[], actions
  pagination.php    props: page, perPage, total
  modal.php         props: id, title, body, actions
  toast.php         props: type, message, duration
  tabs.php          props: items[] (label, content)
  accordion.php     props: items[] (title, content)
  breadcrumb.php    props: items[] (label, url)
  empty-state.php   props: icon, title, description, action
  skeleton.php      props: width, height, variant: text|card|circle
  chart.php         props: type: line|bar|donut|stat, data, height

Composite components (multi-file, admin/views/partials/blocks/):

  data-table.php    table + search + pagination + bulk actions
  chart-card.php    chart + title + KPI delta
  user-picker.php   typeahead over users
  role-picker.php   multi-select over ACL::allRoles()
  file-upload.php   drag-drop + progress (uses /api/upload)
  rich-text.php     minimal WYSIWYG (contenteditable, no library)
  notification-bell.php  bell + dropdown
  sidebar-nav.php   nav auto-filtered by ACL::can()

Every component:
  - Receives props via @include('components.button', ['text' => 'Save'])
  - Has NO inline styles; relies on admin/assets/css/components.css
  - Is RTL-safe (use margin-inline-*, text-align:start)
  - Escapes all user strings with e() or {{ }}

## 13. SITE TEMPLATE WIDGETS & COMPONENTS

Every site template (assets/{name}/ + views/{name}/) MUST ship with these
subfolders after onboarding:

  views/{name}/partials/widgets/
    hero.php            variants: text | image-right | slider
    stats.php           N stat counters with labels
    features.php        3–4 feature cards
    services.php        service cards grid
    process.php         3–5 step process
    testimonials.php    slider
    pricing.php         2–4 tiers
    team.php            team cards grid
    blog.php            latest N posts
    cta.php             call-to-action band
    contact-form.php    form (name/email/phone/service/message)
    newsletter.php      email + submit
    faq.php             accordion
    partners.php        logo cloud

  views/{name}/partials/components/
    button.php, card.php, badge.php, avatar.php,
    form-input.php, form-select.php, form-textarea.php,
    alert.php, breadcrumb.php, pagination.php,
    tabs.php, accordion.php, modal.php,
    empty-state.php, skeleton.php, chart.php

Every site widget:
  - Receives its content via a $data array (from a page or a controller)
  - Falls back to t('...') strings if $data is missing (so it renders in demo)
  - Is RTL-safe
  - Uses @asset() for any assets

When onboarding a NEW site template, the workflow (section 15) requires
extracting these widgets/components from the template's HTML into
views/{name}/partials/widgets/ and views/{name}/partials/components/.

## 14. LANGUAGE KEYS

Format: {section}.{key}
Examples:
  nav.home, hero.1.title, macro.cultures, pricing.basic.price,
  footer.copyright, admin.dashboard.owner, admin.group.business

Every key MUST exist in lang/en.php. Missing keys in fr/ar fall back to en
via array_merge in src/Lang.php.

Admin keys use the prefix `admin.*`.

## 15. SITE TEMPLATE ONBOARDING WORKFLOW

When the human sends a site template's HTML + CSS + JS:

STEP 1 — Create folders
  mkdir -p views/{name}/{layouts,partials,pages,partials/widgets,partials/components}
  mkdir -p assets/{name}/{css,js,img,fonts}

STEP 2 — Move assets
  assets/css/*    → assets/{name}/css/
  assets/js/*     → assets/{name}/js/
  assets/img/*    → assets/{name}/img/
  assets/fonts/*  → assets/{name}/fonts/

STEP 3 — Split HTML
  Main page       → views/{name}/layouts/app.php + views/{name}/pages/home.php
  <header>        → views/{name}/partials/header.php
  <footer>        → views/{name}/partials/footer.php
  Preloader       → views/{name}/partials/preloader.php
  Offcanvas       → views/{name}/partials/offcanvas.php
  Search popup    → views/{name}/partials/search.php
  Language switch → views/{name}/partials/lang-switcher.php
  Other pages     → views/{name}/pages/{slug}.php

STEP 4 — Extract widgets/components
  For each <section> in the template HTML that repeats or is reusable:
    → views/{name}/partials/widgets/{widget}.php
  For each atomic UI element (button, card, badge, form field, …):
    → views/{name}/partials/components/{component}.php

STEP 5 — Wire the engine
  - <title> and meta description → @yield()
  - <html lang="{{ $lang }}" dir="{{ $dir }}">
  - All CSS/JS src → @asset()
  - Header/footer/preloader → @include()
  - Page-specific JS → @section('scripts')
  - Arabic RTL block guarded by @if($dir === 'rtl')

STEP 6 — Extract language keys
  - Replace every visible string with {{ t('section.key') }}
  - Add to lang/en.php (source) + lang/fr.php + lang/ar.php (copy EN value)
  - Skip data (numbers, person names, addresses)

STEP 7 — Remove inline styles/scripts
  - <style>…</style>            → assets/{name}/css/w5d-dynamic.css
  - Inline <script>…</script>   → assets/{name}/js/pages/{slug}.js (page-specific)
                                → assets/{name}/js/w5d-dynamic.js (shared)
  - EXCEPTION: Arabic RTL Cairo block stays in the layout, guarded

STEP 8 — Language switcher
  - Every language <a> gets href="#" data-w5d-lang="{code}"
  - NO ?lang=xy URLs
  - Lang::switcherJs() handles the rest via cookie

STEP 9 — Add routes
  - src/Router.php $routes[] for each page
  - Dynamic routes (e.g. /services/{macro}) via regex in siteRoute()

STEP 10 — Update .env
  - ACTIVE_TEMPLATE={name}

STEP 11 — Verify (see section 16)

## 16. ADMIN DASHBOARD ONBOARDING WORKFLOW

When the human sends the admin dashboard template:

STEP 1 — Create folders
  mkdir -p admin/views/{layouts,partials,pages,partials/widgets,partials/components,partials/blocks}
  mkdir -p admin/assets/{css,js,img,fonts}

STEP 2 — Move assets
  Template's CSS/JS/img/fonts → admin/assets/

STEP 3 — Split HTML
  Main HTML      → admin/views/layouts/app.php
  Sidebar        → admin/views/partials/sidebar.php
  Topbar         → admin/views/partials/topbar.php
  Footer         → admin/views/partials/footer.php
  Widget blocks  → admin/views/partials/widgets/{key}.php (7 dashboards)
  Components     → admin/views/partials/components/{name}.php

STEP 4 — Create the 7 dashboard views
  admin/views/pages/dashboard-{role}.php for each role in:
    owner, admin, manager, member, investor, customer, user
  Each @extends('layouts.app') and loops ACL::widgetsGrouped($role).

STEP 5 — admin/index.php router
  - public:    /login, /oauth/{provider}/start, /oauth/{provider}/callback
  - protected: /, /users, /settings, /content, /profile, /notifications, …
  - /logout
  - Auth::require(...) on every protected route
  - ACL::can(...) on every widget (defence in depth)
  - Role-specific dashboard: $view = Auth::dashboardForRole(Auth::role())

STEP 6 — Sidebar auto-filtered
  admin/views/partials/sidebar.php iterates a menu registry and hides items
  the current role cannot access (checked via ACL::can).

STEP 7 — Verify (see section 17)

## 17. VERIFICATION CHECKLISTS

Site template:
  [ ] /                       → home renders
  [ ] /{slug}                 → each page renders
  [ ] /nonexistent            → 404 from _shared
  [ ] Lang switch EN → FR → AR
  [ ] AR: <html dir="rtl">, Cairo applied
  [ ] /{ADMIN_URL_PATH}       → admin still works
  [ ] /admin                  → 403
  [ ] No inline <style> except Arabic block
  [ ] No inline <script> except switcher
  [ ] Footer license block present
  [ ] rm -rf storage/cache/views/* → pages still render

Admin dashboard:
  [ ] /{ADMIN_URL_PATH}       → redirects to /login if not authed
  [ ] /{ADMIN_URL_PATH}/login → email OR Google OR GitHub options
  [ ] @w5d.tech email + password → correct dashboard per role
  [ ] Non-@w5d.tech email + password → rejected with clear message
  [ ] Google login → new user (role=user) → user dashboard
  [ ] GitHub login → new user (role=user) → user dashboard
  [ ] Owner sees owner widgets only
  [ ] Admin sees admin widgets only
  [ ] Manager sees manager widgets only
  [ ] Member sees member widgets only
  [ ] Investor sees investor widgets only
  [ ] Customer sees customer widgets only
  [ ] User sees user widgets only
  [ ] Widget called directly with wrong role → 403
  [ ] Route called directly with wrong role → 403
  [ ] /{ADMIN_URL_PATH}/logout → back to login
  [ ] Change ADMIN_URL_PATH=/new in .env → /old 404, /new works

## 18. PRE-SUBMIT CHECKLIST

[ ] No inline <style> or <script> in views (except Arabic RTL block)
[ ] Every user string escaped with e() or {{ }}
[ ] Every state-changing form has @csrf
[ ] Every admin route has Auth::require(...)
[ ] Every admin widget calls Auth::require($def['perm']) internally
[ ] Every new key exists in lang/en.php + fr + ar
[ ] All URLs via url() / asset() / admin_url() / admin_asset()
[ ] No ?lang=xy — use data-w5d-lang
[ ] No new Composer dependencies
[ ] All PHP files have declare(strict_types=1);
[ ] All classes are in namespace W5D\
[ ] All 3 auth methods still work (email, Google, GitHub)
[ ] All 7 dashboards render for their role
[ ] Non-@w5d.tech password signup rejected
[ ] /admin (physical) still 403
[ ] Footer license block intact
[ ] storage/cache/views/* deletion → pages still render
[ ] RTL works end-to-end

## 19. WHEN TO ASK FOR CLARIFICATION

Ask before:
  - Changing the View engine's supported directives
  - Adding a Composer dependency (generally refuse)
  - Changing .env keys (may break existing deployments)
  - Changing the admin URL scheme
  - Removing a widget, component, role, or permission
  - Adding a new role or permission without a use case
  - Modifying src/Env.php or src/Lang.php detection order
  - Modifying src/Auth.php or src/ACL.php public API
  - Allowing a non-@w5d.tech email to sign up with a password

Do NOT ask before:
  - Adding a new page/partial/route
  - Adding a new translation key
  - Adding a widget that guards by existing permission
  - Creating a new template folder
  - Fixing an obvious bug
  - Writing tests

## 20. GOTCHAS

1.  Sessions MUST start before any output. config.php handles this.
2.  CSRF token is per-session; regenerating session ID does not wipe the token.
3.  The admin folder MUST stay blocked at Apache level. Do not unblock.
4.  The language cookie is HttpOnly=false so JS can read/write it.
5.  Compiled views are in storage/cache/views/ (and views-admin/). If a
    view edit doesn't show, clear the cache.
6.  Site templates resolve views/{tpl}/ → views/_shared/ (FIRST match wins).
    Admin resolves admin/views/ only.
7.  The license block (610.5 TND/hour, before 2027) MUST appear in every
    site template footer AND the admin footer.
8.  Every widget MUST re-check its permission. Do not trust the caller.
9.  Every admin route MUST call Auth::require(...). Do not trust the sidebar.
10. Rank is for ordering only. Authorization is ACL::can() only.
11. OAuth callbacks land at /{ADMIN_URL_PATH}/oauth/{provider}/callback.
    The state parameter MUST be validated against $_SESSION['oauth_state'].
12. When a new Google/GitHub user signs in, insert them into users with
    role='user' unless their email matches an existing invited record.
13. @w5d.tech emails may sign in with password if they have a row in
    users.password_hash (or if they equal WEBMASTER_USERNAME).
14. Non-@w5d.tech emails CANNOT sign up with password. Full stop.

## AI Agent Prompt

> Copy this block verbatim into an AI coding assistant's system prompt. It is the single source of truth for W5D PHP CMS.

```text
================================================================================
PROJECT: w5d-php-cms
ROLE: Senior PHP engineer and CMS architect.
================================================================================

## 1. PROJECT CONTEXT

W5D — World Five Dimensions — is a company and a working ontology operating
across 5 DEFAULT macro works:

  1. Cultures/Humanities  (slug: cultures)
  2. Built Environment    (slug: built)
  3. Sciences             (slug: sciences)
  4. Governance           (slug: governance)
  5. Technology           (slug: technology)

Macro works are OWNER-MANAGEABLE — the owner can add new ones. Services are
OWNER-MANAGEABLE too. The default catalogue is 130 services (26 per macro),
fully specified in data/services.md.

The CMS powers BOTH the W5D public website AND a 7-role admin dashboard.

## 2. HARD CONSTRAINTS — DO NOT VIOLATE

- PHP 8.2+ only. NO Composer. NO frameworks.
- SQLite 3 (PDO) only. NO MySQL, PostgreSQL.
- Vanilla JS only. NO build tools, NO npm bundler.
- Every PHP file in src/ MUST start with `declare(strict_types=1);`.
- Every class lives in namespace `W5D\`.
- Use `require_once` for loading — no autoloader.
- OAuth: vanilla cURL only.

## 3. SYSTEM ARCHITECTURE

Three request types:

  A. Public site         → views/{ACTIVE_TEMPLATE}/ → admin/shared/ → views/_shared/
  B. Admin dashboard     → admin/views/ → admin/shared/
  C. Static assets       → served by Apache

Flow:
  Browser → .htaccess
    → /admin/* (physical)     → 403 (blocked)
    → /assets/{tpl}/*         → serve file
    → everything else         → index.php → config.php → Router::dispatch()

## 4. DATA MODEL

SQLite tables:

  users              — id, email, name, password_hash, role, provider, created_at
  macro_works        — id, slug, label_en/fr/ar, placement, color, is_default, sort_order
  services           — id, slug, macro_slug, title_en/fr/ar, short_en/fr/ar,
                       long_en/fr/ar, placement, virtual_core, is_default, sort_order, status
  sessions           — native PHP session storage
  audit_log          — append-only

macros and services seed from data/services.php on first boot. Default rows
have is_default=1 and cannot be deleted (only edited, except slug on defaults).
Owner-added rows have is_default=0 and full CRUD.

## 5. ROLES (7)

owner(100) > admin(90) > manager(70) > member(50) > investor(40) > customer(30) > user(10)

Rank is ONLY for ordering. Authorization is ALWAYS ACL::can().

## 6. PERMISSIONS — data/permissions.php

Permission → roles list. Extend data/permissions.php to add new permissions.

Current (25):
  manage.users, manage.roles, manage.billing, manage.settings,
  manage.templates, manage.content, manage.macro_works, manage.services,
  manage.team, assign.work, approve.work, submit.work, view.own.work,
  view.team.reports, view.all.reports, view.portfolio, manage.portfolio,
  view.invoices, manage.invoices, view.support, manage.support,
  view.dashboard, view.profile, edit.profile, view.notifications.

Widgets and admin routes MUST guard by permission, not by role.

## 7. AUTHENTICATION — 3 methods ONLY

  A. Email + password — ONLY for @w5d.tech business emails
  B. Google OAuth — any Google account
  C. GitHub OAuth — any GitHub account

NEVER add Facebook, Apple, Twitter, Microsoft, Discord, or any other provider
without owner approval. NEVER allow non-@w5d.tech password sign-ups.

New Google/GitHub users get role `user`. Owner/admin can promote.

Session: session_regenerate_id(true) on login AND logout; HttpOnly,
SameSite=Lax, Secure in prod. Auth::check/role/user are the only session readers.

OAuth callbacks land at /{ADMIN_URL_PATH}/oauth/{provider}/callback.
State is validated against $_SESSION['oauth_state'].

## 8. TEMPLATE RESOLUTION

Site views:
  1. views/{ACTIVE_TEMPLATE}/
  2. admin/shared/                     ← SHARED components & forms
  3. views/_shared/

Admin views:
  1. admin/views/
  2. admin/shared/                     ← SHARED components & forms

FIRST match wins.

## 9. SHARED COMPONENTS — the admin is the source of truth

The admin dashboard defines the canonical component library under:

  admin/shared/components/      → button, card, badge, avatar,
                                  form-input, form-select, form-textarea,
                                  form-toggle, alert, breadcrumb,
                                  pagination, tabs, accordion, modal,
                                  empty-state, skeleton, chart
  admin/shared/forms/           → contact, newsletter, login, search

Site templates DO NOT duplicate these. They include from admin:

  @include('components.button', [...])
  @include('forms.contact', [...])
  @include('components.form-input', [...])

What lives WHERE:

  SHARED (admin/shared/, used by site + admin):
    forms.contact, forms.newsletter, forms.login, forms.search
    components.button, card, badge, avatar, form-input, form-select,
    form-textarea, form-toggle, alert, breadcrumb, pagination, tabs,
    accordion, modal, empty-state, skeleton, chart

  SITE-SPECIFIC (views/{site}/partials/):
    hero, features, services, process, testimonials, pricing, team,
    blog, cta, partners, faq, stats

  ADMIN-SPECIFIC (admin/views/widgets/):
    owner.revenue, admin.content_stats, manager.team, etc. (26 widgets)

To OVERRIDE a shared component in one site template (rare):
  Put views/{site}/components/button.php — it wins over admin/shared/.

NEVER copy shared components into views/{site}/ to "customize" them.
Use the override mechanism, or edit the shared source (affects admin too).

## 10. BLADE-LIKE SYNTAX

Only these directives are supported. Do NOT invent new ones.

  @extends, @section, @endsection, @show, @yield,
  @include, @if/@elseif/@else/@endif, @foreach, @for, @while,
  @php, @csrf, @lang, @asset
  {{ $var }}     escaped
  {!! $var !!}   raw (trusted HTML only)

## 11. HELPERS

  t('key'), t('key','default'), e($v), url('/path'),
  asset('css/x.css'), admin_url('/x'), admin_asset('x.css'),
  csrf_token(), csrf_field(), csrf_verify($t)

## 12. ADMIN DASHBOARDS — 7 pages, 1 template

  Role        Dashboard                     URL
  owner       pages.dashboard-owner         /{ADMIN_URL_PATH}/
  admin       pages.dashboard-admin         /{ADMIN_URL_PATH}/
  manager     pages.dashboard-manager       /{ADMIN_URL_PATH}/
  member      pages.dashboard-member        /{ADMIN_URL_PATH}/
  investor    pages.dashboard-investor      /{ADMIN_URL_PATH}/
  customer    pages.dashboard-customer      /{ADMIN_URL_PATH}/
  user        pages.dashboard-user          /{ADMIN_URL_PATH}/

Each dashboard @extends('layouts.app'), loops ACL::widgetsGrouped($role),
renders each via @include('widgets.{key}'), grouped by `group`.

## 13. ADMIN WIDGETS (29 total)

Owner (8):
  owner.revenue, owner.users_by_role, owner.system_health, owner.billing,
  owner.recent_activity,
  owner.macro_works, owner.services_overview, owner.recent_service_edits

Admin (6):
  admin.content_stats, admin.registrations, admin.uploads,
  admin.approvals, admin.templates, admin.server_log

Manager (4):
  manager.team, manager.pending_work, manager.assignments,
  manager.performance

Member (3):
  member.my_tasks, member.my_submissions, member.notifications

Investor (4):
  investor.portfolio, investor.roi, investor.investments,
  investor.dividends

Customer (4):
  customer.services, customer.invoices, customer.tickets,
  customer.renewals

User (3):
  user.profile, user.notifications
  (member/investor/customer/user share user.notifications)

Widget contract: receives $def (from data/widgets.php) as its only prop.
Each widget MUST internally call Auth::require($def['perm']) before rendering
sensitive data. Do NOT trust the caller.

## 14. ADMIN COMPONENTS

All under admin/shared/components/ (shared with site):
  button, card, badge, avatar, form-input, form-select, form-textarea,
  form-toggle, alert, breadcrumb, pagination, tabs, accordion, modal,
  empty-state, skeleton, chart

All under admin/shared/forms/ (shared with site):
  contact, newsletter, login, search

Composite blocks (admin/views/blocks/ — admin-only):
  data-table, chart-card, user-picker, role-picker, file-upload,
  rich-text, notification-bell, sidebar-nav

Every component:
  - No inline styles (uses admin/assets/css/components.css)
  - RTL-safe (margin-inline-*, text-align:start)
  - Escapes user strings with e() or {{ }}

## 15. SITE TEMPLATE WIDGETS (template-specific)

views/{site}/partials/:
  hero, stats, features, services, process, testimonials, pricing,
  team, blog, cta, contact-form, newsletter, faq, partners

BUT: contact-form and newsletter MUST @include('forms.contact') and
@include('forms.newsletter') from admin/shared/forms/ — not re-implemented.

Every site widget:
  - Receives content via $data (fallback to t(...) for demo)
  - RTL-safe
  - Uses @asset() for assets

## 16. OWNER FEATURES — MACRO WORKS & SERVICES

The OWNER dashboard exposes two management surfaces at:

  /{ADMIN_URL_PATH}/macro-works          (list)
  /{ADMIN_URL_PATH}/macro-works/new      (create)
  /{ADMIN_URL_PATH}/macro-works/{slug}   (edit)
  /{ADMIN_URL_PATH}/services             (list, searchable, filterable)
  /{ADMIN_URL_PATH}/services/new         (create)
  /{ADMIN_URL_PATH}/services/{id}        (edit)

Both routes require: 'manage.macro_works' or 'manage.services' respectively.
Owner holds both. Admin holds manage.services (not macro_works).

MACRO WORKS RULES:
  - 5 defaults exist with is_default=1.
  - Owner can ADD new macro works (is_default=0, slug freely chosen).
  - Default rows: label_fr, label_ar, placement, color, sort_order editable;
    slug is IMMUTABLE (breaks service URLs).
  - Deleting a default is FORBIDDEN.
  - Deleting a custom macro work is only allowed if it has 0 services.

SERVICES RULES:
  - 130 defaults seed from data/services.php, is_default=1.
  - Owner can ADD services (id assigned by DB).
  - Default rows: all text fields (title_*, short_*, long_*), placement,
    virtual_core, status, sort_order editable; slug is IMMUTABLE.
  - Deleting a default is FORBIDDEN (archive it — status='archived').
  - Deleting a custom service is allowed.
  - The public site ONLY shows services with status='published'.

SEEDING — src/Seed.php:
  1. Runs on first boot (idempotent).
  2. Upserts the 5 defaults + 130 services from data/services.php.
  3. NEVER overwrites is_default=0 rows.
  4. NEVER overwrites a default's user-edited fields more than once
     (guard: if services.updated_at > seed time, skip).

## 17. LANGUAGE KEYS

{section}.{key}, always in lang/en.php + lang/fr.php + lang/ar.php.
Admin keys prefix: admin.*
Demo fallback: if a service field is empty in fr/ar, fall back to en
via the services DB helper (services_title($svc, $lang)).

## 18. SITE TEMPLATE ONBOARDING WORKFLOW

When human sends a site template:

STEP 1 — mkdir views/{name}/{layouts,partials,pages} + assets/{name}/{css,js,img,fonts}

STEP 2 — Move assets into assets/{name}/

STEP 3 — Split HTML:
  Main → layouts/app.php + pages/home.php
  <header>  → partials/header.php
  <footer>  → partials/footer.php
  Preloader → partials/preloader.php
  Others    → pages/{slug}.php

STEP 4 — Extract SITE-SPECIFIC widgets into
         views/{name}/partials/{hero,features,services,...}.php
         DO NOT extract forms/buttons/inputs — those come from admin/shared/

STEP 5 — Wire the engine:
  - @yield('title'), @yield('description'), @yield('head'), @yield('scripts')
  - <html lang="{{ $lang }}" dir="{{ $dir }}">
  - All CSS/JS → @asset()
  - Header/footer/preloader → @include()
  - Arabic RTL guarded by @if($dir === 'rtl')

STEP 6 — Extract language keys → lang/{en,fr,ar}.php

STEP 7 — Remove inline <style>/<script>:
  → assets/{name}/css/w5d-dynamic.css
  → assets/{name}/js/w5d-dynamic.js
  (Arabic Cairo block stays in layout, guarded)

STEP 8 — Every lang switcher <a> gets data-w5d-lang

STEP 9 — Add routes in src/Router.php

STEP 10 — .env ACTIVE_TEMPLATE={name}

STEP 11 — Verify (see §20)

## 19. ADMIN DASHBOARD ONBOARDING WORKFLOW

When human sends the admin dashboard template:

STEP 1 — mkdir admin/views/{layouts,partials,pages,widgets,blocks} + admin/assets/{css,js,img,fonts}

STEP 2 — Move assets into admin/assets/

STEP 3 — Split HTML:
  Main → admin/views/layouts/app.php
  Sidebar → admin/views/partials/sidebar.php (auto-filtered by ACL::can)
  Topbar → admin/views/partials/topbar.php
  Footer → admin/views/partials/footer.php
  Forms/inputs/buttons → admin/shared/forms/ + admin/shared/components/
  Widgets → admin/views/widgets/{key}.php

STEP 4 — Create 7 dashboard views
  admin/views/pages/dashboard-{role}.php for each of:
  owner, admin, manager, member, investor, customer, user

STEP 5 — admin/index.php router:
  - public: /login, /oauth/{provider}/start, /oauth/{provider}/callback
  - protected: /, /users, /settings, /content, /profile, /notifications,
    /macro-works, /services, …
  - Auth::require(...) on every protected route
  - Role-specific dashboard: Auth::dashboardForRole(Auth::role())

STEP 6 — Owner features UI:
  - pages/macro-works-index.php, pages/macro-works-edit.php
  - pages/services-index.php, pages/services-edit.php
  - Role-picker for service → macro_slug
  - Status toggle (published/draft/archived)
  - Default-row lock (🔒) that blocks delete and slug edits
  - Save/delete handlers with CSRF + ACL

STEP 7 — Verify (see §20)

## 20. VERIFICATION CHECKLISTS

Site template:
  [ ] /  and each /{slug} render
  [ ] /nonexistent → 404
  [ ] Lang switch EN → FR → AR
  [ ] AR: <html dir="rtl">, Cairo applied
  [ ] /{ADMIN_URL_PATH} works, /admin → 403
  [ ] No inline <style>/<script>
  [ ] Footer license block present
  [ ] Contact/newsletter forms use @include('forms.*') from admin/shared/
  [ ] rm -rf storage/cache/views/* → pages still render

Admin dashboard:
  [ ] 7 roles each land on their dashboard
  [ ] @w5d.tech email+password → login
  [ ] Non-@w5d.tech email+password → rejected
  [ ] Google login → new user role=user → user dashboard
  [ ] GitHub login → new user role=user → user dashboard
  [ ] Owner sees owner widgets only, admin sees admin widgets only, etc.
  [ ] /macro-works shows 5 defaults with lock
  [ ] /macro-works/new creates a custom macro work
  [ ] /macro-works/cultures → slug field is read-only
  [ ] /services shows 130 defaults
  [ ] /services/1 (heritage-digitisation) → slug read-only, text editable
  [ ] /services/new creates a new service
  [ ] Archiving a default service → hidden on public site, visible in admin
  [ ] Deleting a default service → 403/error
  [ ] Deleting a custom service → OK
  [ ] Changing a service title → public site updates immediately
  [ ] Widget called with wrong role → 403
  [ ] Route called with wrong role → 403
  [ ] Admin URL change in .env → /old 404, /new works

## 21. PRE-SUBMIT CHECKLIST

[ ] No inline <style>/<script> (except Arabic RTL block)
[ ] Every user string escaped with e() or {{ }}
[ ] Every state-changing form has @csrf
[ ] Every admin route has Auth::require(...)
[ ] Every widget calls Auth::require($def['perm']) internally
[ ] Every new key exists in lang/en.php + fr + ar
[ ] All URLs via url()/asset()/admin_url()/admin_asset()
[ ] No ?lang=xy — use data-w5d-lang
[ ] No Composer dependencies
[ ] declare(strict_types=1) in every src/ file
[ ] namespace W5D\ in every class
[ ] All 3 auth methods still work
[ ] All 7 dashboards render
[ ] Non-@w5d.tech password signup rejected
[ ] /admin (physical) still 403
[ ] Footer license block intact
[ ] Contact/newsletter forms use admin/shared/forms/
[ ] Owner macro-works & services CRUD works end-to-end
[ ] Defaults locked (delete + slug edit blocked)
[ ] Service title change → public site updates (via cached query invalidation)
[ ] storage/cache/views/* deletion → pages still render
[ ] RTL works end-to-end

## 22. WHEN TO ASK FOR CLARIFICATION

Ask before:
  - Changing View engine directives
  - Adding Composer dependencies (refuse)
  - Changing .env keys
  - Removing a widget, component, role, or permission
  - Adding a role or permission without a use case
  - Modifying src/Env.php, src/Lang.php, src/Auth.php, src/ACL.php public API
  - Allowing non-@w5d.tech password sign-up
  - Adding a non-OAuth auth provider
  - Deleting a default macro work or service

Do NOT ask before:
  - Adding a page/partial/route
  - Adding a translation key
  - Adding a widget with an existing permission
  - Creating a template folder
  - Fixing an obvious bug
  - Writing tests

## 23. GOTCHAS

1.  Sessions before output. config.php handles it.
2.  CSRF token is per-session; regeneration keeps the token.
3.  /admin physical folder stays blocked at Apache.
4.  Language cookie: HttpOnly=false (JS switcher writes it, PHP reads).
5.  Compiled views: storage/cache/views/ (site) + storage/cache/views-admin/ (admin).
    Clear cache when debugging.
6.  Site search paths: views/{tpl}/ → admin/shared/ → views/_shared/.
    Admin search paths: admin/views/ → admin/shared/.
    Admin pages NEVER leak to site (site's path 2 is admin/shared/, not admin/views/).
7.  License block (610.5 TND/hour, before 2027) in every footer.
8.  Every widget re-checks its permission. Every admin route re-checks auth.
9.  Rank is for ordering. Authorization is ACL::can() only.
10. Default macro works + default services have IMMUTABLE slug.
11. Deleting a default macro work or service is FORBIDDEN. Archive instead.
12. Public site only shows services with status='published'.
13. data/services.md is documentation; data/services.php is the seed; DB is live.
14. After editing a service in admin, invalidate the relevant cache entry
    (services are cached per request by default, so no extra step needed —
    but if you add an APCu/Redis layer later, invalidate on write).
15. OAuth state must be validated against $_SESSION['oauth_state'] on
    callback. Missing state = reject.
16. @w5d.tech emails MAY use password login if they have a users.password_hash
    row (or match WEBMASTER_USERNAME). Non-@w5d.tech emails CANNOT.
17. Forms MUST @include('forms.contact') / ('forms.newsletter') from
    admin/shared/forms/. Do not re-implement form markup per template.
18. Components MUST @include('components.button') etc. from
    admin/shared/components/. Site-specific override goes in
    views/{site}/components/ (rare).
```

---

## Author

**Maher Kaddoussi**
Founder and owner, W5D

- 📧 owner@w5d.tech
- 🌐 https://w5d.tech
- 💼 Repository: [https://github.com/W5Downer/w5d-php-cms](https://github.com/W5Downer/w5d-php-cms)

---

## Changelog

### [Unreleased]
- Initial scaffolding: `.env` loader, Blade-like View engine, `Lang` detection, `Router` with admin obfuscation
- Multi-template support (`views/{name}/` + `assets/{name}/`)
- Stub `admin/index.php` ready for real template integration
- Demo home + 404 pages
- `w5d-dynamic.css` layer for template-scoped additions

### [0.1.0] — 2026-11-11
- Project created
- Foundation architecture complete (56% of full idea)
- Awaiting: first template, admin dashboard template, remaining templates

---

<div align="center">

**W5D PHP CMS** — built by Maher Kaddoussi for the W5D Team.

*Licensed at 610.5 TND/hour — offer valid before 2027.*

</div>
