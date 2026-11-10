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
```

---

## Author

**Maher Kaddoussi**
Founder and owner, W5D

- 📧 owner@w5d.tech
- 🌐 https://w5d.tech
- 💼 Repository: [https://github.com/YOUR_USERNAME/w5d-php-cms](https://github.com/W5Downer/w5d-php-cms)

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
