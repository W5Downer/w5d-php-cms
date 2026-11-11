# W5D PHP CMS

**A complete CMS for the W5D Team website.**

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![SQLite](https://img.shields.io/badge/SQLite-3-003B57?logo=sqlite&logoColor=white)](https://www.sqlite.org/)
[![TailAdmin](https://img.shields.io/badge/Admin-TailAdmin-0A5EFF)](https://github.com/TailAdmin/tailadmin-free-tailwind-dashboard-template)
[![License](https://img.shields.io/badge/License-610.5%20TND%2Fh%20(offer%20before%202027)-0A5EFF)](#license)

> **W5D** — *World Five Dimensions* — is a company and a working ontology operating across five macro works: **Cultures/Humanities**, **Built Environment**, **Sciences**, **Governance**, and **Technology**. This CMS powers its website and its multi-role admin dashboard.

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
- [Admin Panel (TailAdmin)](#admin-panel-tailadmin)
- [Roles & ACL](#roles--acl)
- [Owner Features](#owner-features)
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

**W5D PHP CMS** is a self-contained content management system built for the W5D Team website — a company and a working ontology operating across five macro works: Cultures/Humanities, Built Environment, Sciences, Governance, and Technology. The system is designed for teams that need a fast, template-driven, multi-language site **and** a multi-role administrative backend without the overhead of a heavyweight framework.

The CMS runs on vanilla PHP 8.2+ with SQLite — no Composer dependencies, no PHP framework lock-in. It ships with a Blade-inspired templating engine supporting `@extends`, `@section`, `@yield`, `@include`, `{{ }}` escaping, `{!! !!}` raw output, `@if`/`@foreach`/`@for`/`@while` directives, `@csrf`, `@lang`, and `@asset`. Public site views resolve in two tiers: `views/{ACTIVE_TEMPLATE}/` first, then `views/_shared/` as fallback. Every site template is fully self-contained, owning both its `views/{name}/` and `assets/{name}/` directories, so swapping the public front-end is a single `.env` change.

The **admin dashboard** ships with **TailAdmin** — the free, open-source Tailwind CSS + Alpine.js admin template (1 unique dashboard, 30+ dashboard components, 50+ UI elements, sidebar, charts, tables, profile page, 404, auth forms, dark mode). TailAdmin is compiled once (`npm install && npm run build`), then `node_modules/` is discarded; Tailwind CDN is **never** used at runtime. Seven role-specific dashboards — owner, admin, manager, member, investor, customer, user — share the TailAdmin shell and differ only in the widgets they display, guarded by a permission-based ACL.

Multi-language support is automatic. The system detects language from the browser's `Accept-Language` header on first visit, then stores the user's choice in a cookie. No `?lang=xy` query parameters are ever needed. Arabic is fully supported with RTL layout direction, Cairo typography, and Tailwind RTL injection. All strings come from `lang/{code}.php` files with English fallback via `array_merge`.

Configuration is environment-driven. The `.env` file holds application settings, database path, session parameters, webmaster credentials, OAuth client IDs and secrets, and — critically — the admin URL path. The `/admin` route is fully obfuscated: change `ADMIN_URL_PATH=/secret-xyz` in `.env`, and the admin dashboard moves with it. Direct HTTP access to the `/admin/` folder is blocked by `.htaccess`; only the router can dispatch admin requests.

**Owner features** (Manage Macro Works, Manage Services) let the owner extend the ontology beyond the 5 default macro works and 130 default services — all editable in the TailAdmin shell, all backed by SQLite, all seeded from `data/services.php` on first boot.

Security is built in: CSRF tokens on every form, session fixation protection, HttpOnly/SameSite cookies, OAuth state validation, path traversal prevention, and defense-in-depth `.htaccess` rules that deny access to `.env`, `.sqlite`, and `.sql` files.

The CMS is offered at **610.5 TND per hour of work** — a promotional rate valid before 2027.

Built for W5D by Maher Kaddoussi. Contact: [owner@w5d.tech](mailto:owner@w5d.tech).

---

## Features

### Templating
- **Blade-inspired engine**, zero Composer dependencies
- Directives: `@extends`, `@section`, `@endsection`, `@show`, `@yield`, `@include`, `@if`/`@elseif`/`@else`/`@endif`, `@foreach`/`@endforeach`, `@for`/`@endfor`, `@while`/`@endwhile`, `@php`/`@endphp`, `@csrf`, `@lang`, `@asset`
- `{{ $var }}` (escaped) and `{!! $var !!}` (raw)
- Compiled templates cached under `storage/cache/views/` (site) and `storage/cache/views-admin/` (admin), keyed by md5 of path + mtime
- Two-tier site view resolution: `views/{ACTIVE_TEMPLATE}/` → `views/_shared/`
- Admin view resolution: `admin/views/` only (self-contained)

### Multi-Template
- Each site template owns `views/{name}/` + `assets/{name}/`
- Activate with `ACTIVE_TEMPLATE={name}` in `.env`
- Static assets served directly by Apache (no PHP hit)
- Add a new template without touching existing ones

### Multi-Language
- Auto-detection from `Accept-Language` header (quality-weighted parsing)
- Persists user choice in a cookie (`w5d_lang`)
- **No `?lang=xy`** — the URL never carries language state
- Full RTL support (Arabic) with Cairo typography (site + admin)
- Fallback chain: `lang/{code}.php` → `lang/en.php` → key string

### Admin Panel (TailAdmin)
- **TailAdmin** as the default admin template (Tailwind CSS + Alpine.js)
- **7 role-specific dashboards**: owner, admin, manager, member, investor, customer, user
- **Permission-based ACL** — sidebar items, routes, and widgets are all guarded
- **Obfuscated URL** from `.env` (`ADMIN_URL_PATH=/secret-xyz`)
- `/admin/` folder blocked at Apache level — only the router can dispatch
- Admin views under `admin/views/`, assets under `admin/assets/` (router-served)
- Dark mode built in
- Charts (Line, Bar), tables, modals, dropdowns, alerts, 404, profile — all from TailAdmin

### Authentication (3 methods only)
- **Email + password** — only for `@w5d.tech` business emails
- **Google OAuth** — any Google account (new users → role `user`)
- **GitHub OAuth** — any GitHub account (new users → role `user`)
- OAuth state validated against `$_SESSION['oauth_state']`
- Session fixation protection, HttpOnly/SameSite cookies

### Owner Features
- **Manage Macro Works** — 5 defaults (locked slug, delete forbidden) + full CRUD for custom macro works
- **Manage Services** — 130 defaults seeded from `data/services.php` (locked slug, delete forbidden, archive allowed) + full CRUD for custom services
- TailAdmin table + form components for both list and edit views
- Changes propagate to the public site immediately (published services only)

### Security
- CSRF tokens on all forms (`csrf_token()`, `csrf_field()`, `csrf_verify()`)
- Session fixation protection (secure cookie params)
- HttpOnly + SameSite=Lax cookies
- OAuth state validation
- Path traversal prevention in router
- Defense-in-depth `.htaccess` (deny `.env`, `.sqlite`, `.sql`, `.md`)
- `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` headers

### Performance
- Static assets cached for 7 days (Cache-Control)
- Compiled template caching
- SQLite with WAL journal mode
- No blocking loaders, no third-party requests by default
- TailAdmin compiled once — no runtime Tailwind CDN

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
  ├── new View(views/{tpl}/, views/_shared/, storage/cache/views/)     ← site
  ├── new View(admin/views/, admin/views/, storage/cache/views-admin/) ← admin
  ├── ACL::init(data/)
  └── new Router(basePath, adminUrlPath, adminFolder, view, adminView)
  │
  ▼
$router->dispatch()
  │
  ├── path starts with ADMIN_URL_PATH? ── yes ─► admin/index.php
  │                                                ├── public: /login, /oauth/*
  │                                                ├── protected: /, /users, /macro-works, /services, …
  │                                                ├── Auth::require($perm) per route
  │                                                └── Auth::dashboardForRole($role)
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
| `src/ACL.php` | Roles, permissions, widget registry, `sidebarFor()` |
| `src/Auth.php` | Session helpers, business-email check, role dashboards |
| `src/Auth/OAuth.php` | Google + GitHub OAuth (cURL only) |
| `src/Seed.php` | Idempotent seeder for macro works + 130 services |
| `src/helpers.php` | `t()`, `e()`, `url()`, `asset()`, `admin_url()`, `admin_asset()`, `csrf_*()` |
| `config.php` | Wires everything; returns `[$view, $adminView, $router]` |
| `index.php` | Single entry point |

---

## Requirements

- **PHP ≥ 8.2** (typed properties, named arguments, `str_contains`, `str_starts_with`, `match`)
- **SQLite 3** (bundled with PHP by default)
- **Apache 2.4+** with `mod_rewrite` (or Nginx with equivalent config)
- **PHP extensions**: `pdo_sqlite`, `mbstring`, `json`, `session`, `openssl`, `curl`
- **Node.js ≥ 18** — only for the one-time TailAdmin build (`npm install && npm run build`). Discarded after.
- **No Composer** required

---

## Installation

```bash
# 1. Clone
git clone https://github.com/W5Downer/w5d-php-cms.git
cd w5d-php-cms

# 2. Create required folders
mkdir -p src/Auth admin/assets/css admin/assets/js admin/assets/img admin/assets/fonts
mkdir -p admin/views/layouts admin/views/partials admin/views/pages admin/views/widgets
mkdir -p assets/default/css assets/default/js assets/default/img assets/default/fonts
mkdir -p views/default/layouts views/default/partials views/default/pages
mkdir -p views/_shared/pages
mkdir -p data lang
mkdir -p storage/cache/views storage/cache/views-admin storage/sessions

# 3. Configure environment
cp .env.example .env
# Edit .env (see Configuration below)

# 4. Generate webmaster password hash
php -r "echo password_hash('YourStrongPassword', PASSWORD_DEFAULT), PHP_EOL;"
# Paste the output into .env → WEBMASTER_PASSWORD_HASH=...

# 5. Build TailAdmin once (produces admin/assets/css/tailadmin.css + admin/assets/js/app.js)
cd admin/assets
git clone https://github.com/TailAdmin/tailadmin-free-tailwind-dashboard-template .
npm install
npm run build
cd ../..
# Delete build artefacts — they are not needed at runtime
rm -rf admin/assets/node_modules admin/assets/package.json admin/assets/package-lock.json

# 6. Seed the database on first request (automatic — no manual step)

# 7. Point Apache document root to the repository root
# 8. Visit http://localhost/w5d/
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

# ---- OAuth ----
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=

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

Each site template is a **self-contained unit**:

```
assets/{name}/                 ← CSS, JS, images, fonts
views/{name}/                  ← layouts, partials, pages
```

### How it works

1. `ACTIVE_TEMPLATE={name}` in `.env` sets the active template
2. `View` searches `views/{name}/` first, then `views/_shared/` as fallback
3. `asset('css/app.css')` resolves to `/w5d/assets/{name}/css/app.css`
4. `.htaccess` serves `assets/{name}/*` directly (no PHP)

### Adding a new site template

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

`views/_shared/` **does not** need its own `layouts/` — it inherits `layouts.app` from the active template.

---

## Multi-Language System

### Detection priority

1. **Cookie** `w5d_lang` (user's saved choice)
2. **HTTP `Accept-Language` header** — quality-weighted parsing (e.g. `fr-CH, fr;q=0.9, en;q=0.8` → `fr`)
3. **`.env` `APP_DEFAULT_LANG`**

### Supported languages

| Code | Language | Direction | Font |
|---|---|---|---|
| `en` | English | LTR | Theme default |
| `fr` | Français | LTR | Theme default |
| `ar` | العربية | RTL | Cairo (auto-injected) |

### Adding a language

1. Create `lang/{code}.php` mirroring `lang/en.php`
2. Add the code to `Lang::SUPPORTED` in `src/Lang.php`
3. Add a button in your template's language switcher: `<a href="#" data-w5d-lang="{code}">{Label}</a>`

### How switching works

The switcher is pure JS — no server round-trip through a controller. The `Lang::switcherJs()` snippet (injected into the footer by `layouts/app.php`) sets a cookie and reloads. On reload, `Lang` picks up the cookie and serves the right translation file.

**No `?lang=xy` URLs are ever generated.**

---

## Admin Panel (TailAdmin)

The admin dashboard uses **[TailAdmin](https://github.com/TailAdmin/tailadmin-free-tailwind-dashboard-template)** — the free, open-source Tailwind CSS + Alpine.js admin template.

### What TailAdmin provides

- 1 unique dashboard layout (sidebar + topbar + content grid)
- 30+ dashboard components (charts, tables, activity feeds, KPI cards, stats grids)
- 50+ UI elements (buttons, badges, avatars, alerts, dropdowns, modals, form controls)
- Dark mode toggle (built in)
- Profile page, 404 page, auth forms
- Data-visualisation components (Line, Bar charts)

### How it's integrated

| TailAdmin piece | W5D integration |
|---|---|
| `index.html` shell | → `admin/views/layouts/app.php` (Blade-style) |
| Sidebar | → `admin/views/partials/sidebar.php` (ACL-filtered) |
| Topbar | → `admin/views/partials/topbar.php` (dark mode + lang switcher + profile) |
| Footer | → `admin/views/partials/footer.php` |
| Dashboard widgets | → `admin/views/widgets/{key}.php` (thin data adapters) |
| Tables, charts, forms | → kept in `admin/assets/css/tailadmin.css` + `admin/assets/js/app.js` |
| Webpack build | → one-time `npm install && npm run build`, then `node_modules/` deleted |
| Tailwind CDN | **Not used at runtime** — compiled CSS served statically |

### Access rules

| URL | Result |
|---|---|
| `/w5d/{ADMIN_URL_PATH}` | ✅ Admin dashboard (via router) |
| `/w5d/{ADMIN_URL_PATH}/users` | ✅ Admin page (via router) |
| `/w5d/{ADMIN_URL_PATH}/macro-works` | ✅ Owner feature (via router, ACL-gated) |
| `/w5d/{ADMIN_URL_PATH}/services` | ✅ Owner feature (via router, ACL-gated) |
| `/w5d/{ADMIN_URL_PATH}/assets/css/tailadmin.css` | ✅ Admin asset (via router) |
| `/w5d/admin` (the physical folder) | 🚫 **403 Forbidden** |
| `/w5d/admin/index.php` | 🚫 **403 Forbidden** |

The `.htaccess` at the root denies any request whose path starts with `/w5d/admin/`. The router bypasses this by `require`-ing `admin/index.php` directly from PHP — never via HTTP.

### Admin assets

Admin CSS/JS/images live in `admin/assets/`. They are served by the router under the obfuscated path:

```html
<link rel="stylesheet" href="<?= admin_asset('css/tailadmin.css') ?>">
<script src="<?= admin_asset('js/app.js') ?>"></script>
```

Which renders as:
```html
<link rel="stylesheet" href="/w5d/admin/assets/css/tailadmin.css">            <!-- local -->
<link rel="stylesheet" href="/w5d/xyz-panel-2026/assets/css/tailadmin.css">  <!-- production -->
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

## Roles & ACL

### 7 roles

| Role | Rank | Purpose |
|---|---:|---|
| `owner` | 100 | Full access, billing, settings, promote/demote |
| `admin` | 90 | CMS admin, content, users, templates (no billing) |
| `manager` | 70 | Team roster, assignments, approvals |
| `member` | 50 | Own tasks, submissions |
| `investor` | 40 | Portfolio, ROI, dividends |
| `customer` | 30 | Invoices, services, support |
| `user` | 10 | Profile, notifications |

**Rank is ordering only.** Authorization is always `ACL::can($role, $perm)`.

### 25 permissions

Defined in `data/permissions.php` — each permission lists the roles that hold it:

- Account & system: `manage.users`, `manage.roles`, `manage.billing`, `manage.settings`, `manage.templates`, `manage.content`, `manage.macro_works`, `manage.services`
- Team & work: `manage.team`, `assign.work`, `approve.work`, `submit.work`, `view.own.work`, `view.team.reports`, `view.all.reports`
- Investment: `view.portfolio`, `manage.portfolio`
- Commerce: `view.invoices`, `manage.invoices`, `view.support`, `manage.support`
- Personal: `view.dashboard`, `view.profile`, `edit.profile`, `view.notifications`

### How ACL is enforced

- **Routes**: `Auth::require($permission)` before dispatch
- **Sidebar**: `ACL::sidebarFor($role)` filters menu items
- **Widgets**: each widget calls `Auth::require($def['perm'])` internally

---

## Owner Features

### Manage Macro Works

`/{ADMIN_URL_PATH}/macro-works`

- Lists the **5 default macro works** (locked: slug immutable, delete forbidden)
- Owner can add **custom macro works** (full CRUD, any slug)
- Deleting a custom macro work requires **0 services** (archive services first)
- Editable fields: label (EN/FR/AR), placement, color, sort order

### Manage Services

`/{ADMIN_URL_PATH}/services`

- Lists the **130 default services** (locked: slug immutable, delete forbidden, archive allowed)
- Owner can add **custom services** (full CRUD)
- Editable fields: title, short description, long description (EN/FR/AR), macro, placement, virtual core flag, status (published/draft/archived), sort order
- Public site only shows services with `status = 'published'`

### Seeding

`src/Seed.php` runs on first boot (idempotent):

1. Upserts the 5 default macro works (`is_default = 1`)
2. Upserts the 130 default services (`is_default = 1`) from `data/services.php`
3. **Never** overwrites `is_default = 0` rows
4. **Never** overwrites a default's user-edited fields more than once

`data/services.md` is the human-readable documentation of all 130 services. `data/services.php` is the seed source.

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

### OAuth

- State parameter validated against `$_SESSION['oauth_state']` on callback
- Client IDs and secrets stored in `.env` only
- Google + GitHub are the only providers

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
│   ├── ACL.php                  ← roles, permissions, sidebar
│   ├── Auth.php                 ← session + business-email check
│   ├── Auth/
│   │   └── OAuth.php            ← Google + GitHub (cURL only)
│   ├── Seed.php                 ← idempotent seeder
│   └── helpers.php              ← t, e, url, asset, admin_url, admin_asset, csrf_*
│
├── data/
│   ├── roles.php                ← 7 roles
│   ├── permissions.php          ← 25 permissions × roles
│   ├── widgets.php              ← admin widget registry
│   ├── sidebar.php              ← sidebar menu registry
│   ├── services.php             ← seed source for 130 services
│   └── services.md              ← human-readable 130 services spec
│
├── assets/                      ← ONE FOLDER PER SITE TEMPLATE
│   └── default/
│       ├── css/
│       ├── js/
│       ├── img/
│       └── fonts/
│
├── views/                       ← ONE FOLDER PER SITE TEMPLATE
│   ├── default/
│   │   ├── layouts/
│   │   │   └── app.php
│   │   ├── partials/
│   │   │   ├── header.php
│   │   │   ├── footer.php
│   │   │   └── preloader.php
│   │   └── pages/
│   │       └── home.php
│   └── _shared/                 ← template-agnostic fallback
│       └── pages/
│           └── 404.php
│
├── admin/                       ← TAILADMIN DASHBOARD
│   ├── .htaccess                ← deny all direct HTTP
│   ├── index.php                ← admin entry (router-required)
│   ├── assets/
│   │   ├── css/
│   │   │   ├── tailadmin.css    ← compiled Tailwind (one-time build)
│   │   │   └── w5d-admin.css    ← W5D additions (load after)
│   │   ├── js/
│   │   │   ├── app.js            ← compiled Alpine.js (one-time build)
│   │   │   └── w5d-admin.js      ← W5D additions (load after)
│   │   ├── img/
│   │   └── fonts/
│   └── views/
│       ├── layouts/
│       │   └── app.php           ← TailAdmin shell
│       ├── partials/
│       │   ├── sidebar.php       ← ACL-filtered
│       │   ├── topbar.php        ← dark mode + lang + profile
│       │   └── footer.php
│       ├── pages/
│       │   ├── dashboard-owner.php
│       │   ├── dashboard-admin.php
│       │   ├── dashboard-manager.php
│       │   ├── dashboard-member.php
│       │   ├── dashboard-investor.php
│       │   ├── dashboard-customer.php
│       │   ├── dashboard-user.php
│       │   ├── login.php
│       │   ├── macro-works-index.php
│       │   ├── macro-works-edit.php
│       │   ├── services-index.php
│       │   ├── services-edit.php
│       │   └── 404.php
│       └── widgets/
│           ├── owner-revenue.php
│           ├── owner-users-by-role.php
│           ├── …
│           └── user-notifications.php
│
├── lang/
│   ├── en.php
│   ├── fr.php
│   └── ar.php
│
└── storage/
    ├── cache/
    │   ├── views/               ← compiled site Blade templates
    │   └── views-admin/         ← compiled admin Blade templates
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
rm -rf storage/cache/views/* storage/cache/views-admin/*
```

The cache auto-rebuilds on next request. Individual files are invalidated automatically when their source changes (md5 of path + mtime).

### Adding an admin widget

1. Add to `data/widgets.php`:
   ```php
   'manager.my-widget' => [
       'title' => 'My Widget',
       'perm'  => 'view.team.reports',
       'roles' => ['owner', 'admin', 'manager'],
       'group' => 'work',
       'icon'  => 'fa-chart-bar',
       'size'  => 'md',
   ],
   ```
2. Create `admin/views/widgets/manager-my-widget.php` — a thin data adapter that renders a TailAdmin card or chart.
3. The widget is automatically picked up by the manager dashboard.

---

## License

**W5D PHP CMS — licensed at 610.5 TND per hour of work.**

Promotional offer valid **before 2027** (strictly before 31 December 2026).

After that date, the standard rate applies.

See [`LICENSE`](https://github.com/W5Downer/w5d-php-cms/blob/main/LICENSE) for the full text.

### Footer license block (required on every page)

```html
<p>W5D Admin Dashboard licensed at 610.5 TND/hour of work.
   Promotional offer valid before 2027.</p>
```

This block must appear in every template's footer — site and admin.

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

> Copy this block verbatim into an AI coding assistant's system prompt. Single source of truth for W5D PHP CMS.

```text
================================================================================
PROJECT: w5d-php-cms
ROLE: Senior PHP engineer and CMS architect.
================================================================================

## 1. CONTEXT

W5D — World Five Dimensions — operates across 5 DEFAULT macro works
(cultures, built, sciences, governance, technology) and 130 DEFAULT services
(26 per macro, specified in data/services.md). Macro works and services are
OWNER-MANAGEABLE. Defaults have immutable slug and cannot be deleted
(archive instead).

The CMS powers TWO surfaces:
  A. A multi-template public website   (views/{ACTIVE_TEMPLATE}/ + assets/{name}/)
  B. A 7-role admin dashboard          (admin/ — powered by TailAdmin)

Roles: owner · admin · manager · member · investor · customer · user.
Rank is ORDERING only. Authorization is ACL::can($role, $perm).

## 2. DEFAULT ADMIN TEMPLATE — TailAdmin

The default admin dashboard uses TailAdmin:
  https://github.com/TailAdmin/tailadmin-free-tailwind-dashboard-template

INTEGRATION RULES:

  1. ONE-TIME BUILD:
       cd admin/assets
       npm install
       npm run build
     Produces admin/assets/css/tailadmin.css + admin/assets/js/app.js.
     Then delete node_modules/, package.json, package-lock.json.
     Tailwind CDN is NEVER used at runtime.

  2. SHELL EXTRACTION:
     admin/views/layouts/app.php is the TailAdmin shell:
       <html lang="{{ $lang }}" dir="{{ $dir }}">
       <link href="<?= admin_asset('css/tailadmin.css') ?>" rel="stylesheet">
       @include('partials.sidebar')   ← ACL-filtered
       @include('partials.topbar')    ← dark mode + lang switcher
       <main>@yield('content')</main>
       @include('partials.footer')
       <script src="<?= admin_asset('js/app.js') ?>" defer></script>
       @yield('scripts')

  3. 7 ROLE DASHBOARDS (share the TailAdmin shell):
       admin/views/pages/dashboard-{owner,admin,manager,member,
                                    investor,customer,user}.php
     Each @extends('layouts.app') and renders role-specific widgets.

  4. OWNER FEATURES (TailAdmin tables + forms):
       macro-works-index.php, macro-works-edit.php,
       services-index.php, services-edit.php.

  5. RTL (Arabic):
     TailAdmin is Tailwind-based → <html dir="rtl"> works.
     Add in layout: @if($dir === 'rtl') body { font-family: 'Cairo', sans-serif; } @endif

  6. NEVER modify TailAdmin's compiled CSS/JS. W5D additions go in
     admin/assets/css/w5d-admin.css and admin/assets/js/w5d-admin.js,
     loaded after tailadmin.css / app.js.

## 3. HARD CONSTRAINTS

  - PHP 8.2+. NO Composer. NO PHP frameworks.
  - SQLite 3 (PDO). NO MySQL/PostgreSQL.
  - Vanilla JS at runtime. Webpack only once for TailAdmin, then discarded.
  - Every src/ file: declare(strict_types=1); + namespace W5D\.
  - OAuth: cURL only.

## 4. TEMPLATE RESOLUTION

  Site views:   views/{ACTIVE_TEMPLATE}/  →  views/_shared/
  Admin views:  admin/views/              (no fallback)
  FIRST match wins.

  Assets site:  assets/{ACTIVE_TEMPLATE}/…   via @asset('…')
  Assets admin: admin/assets/…               via admin_asset('…')

## 5. BLADE-LIKE SYNTAX

  @extends · @section · @endsection · @show · @yield
  @include · @if/@elseif/@else/@endif · @foreach · @for · @while
  @php · @csrf · @lang · @asset
  {{ $v }} escaped   ·   {!! $v !!} raw

## 6. HELPERS

  t('key') · t('key','default') · e($v) · url('/p') · asset('x')
  admin_url('/x') · admin_asset('x') · csrf_token() · csrf_field() · csrf_verify($t)

## 7. DATA MODEL (SQLite)

  users              — id, email, name, password_hash, role, provider, created_at
  macro_works        — id, slug, label_en/fr/ar, placement, color, is_default, sort_order
  services           — id, slug, macro_slug, title_en/fr/ar, short_en/fr/ar,
                       long_en/fr/ar, placement, virtual_core, is_default,
                       sort_order, status(published|draft|archived)
  audit_log          — append-only

Seeded from data/services.php on first boot (idempotent).
Defaults: is_default=1, immutable slug, delete forbidden (archive instead).
Custom rows: full CRUD.

## 8. ROLES & PERMISSIONS

  7 roles: owner · admin · manager · member · investor · customer · user
  Rank is ordering only. Authorization is ALWAYS ACL::can($role, $perm).

  25 permissions in data/permissions.php, including:
    manage.users · manage.roles · manage.billing · manage.settings
    manage.templates · manage.content · manage.macro_works · manage.services
    manage.team · assign.work · approve.work · submit.work
    view.own.work · view.team.reports · view.all.reports
    view.portfolio · manage.portfolio
    view.invoices · manage.invoices · view.support · manage.support
    view.dashboard · view.profile · edit.profile · view.notifications

## 9. AUTHENTICATION — 3 methods ONLY

  A. Email + password — ONLY @w5d.tech (Auth::isBusinessEmail)
  B. Google OAuth — any account, new users → role `user`
  C. GitHub OAuth — any account, new users → role `user`

  NEVER: Facebook, Apple, Twitter, Microsoft, or non-@w5d.tech password signup.

  Session: session_regenerate_id(true) on login/logout. HttpOnly,
  SameSite=Lax, Secure in prod. OAuth state validated against
  $_SESSION['oauth_state'].

## 10. ADMIN URL OBFUSCATION

  ADMIN_URL_PATH in .env controls the public path (default /admin).
  .htaccess blocks the physical /admin/ folder for direct HTTP access.
  Router dispatches /{ADMIN_URL_PATH}/* → admin/index.php.
  NEVER unblock /admin/* in .htaccess.

## 11. OWNER FEATURES — MACRO WORKS & SERVICES

  Routes (perm: manage.macro_works / manage.services):
    /{ADMIN_URL_PATH}/macro-works          list
    /{ADMIN_URL_PATH}/macro-works/new      create
    /{ADMIN_URL_PATH}/macro-works/{slug}   edit
    /{ADMIN_URL_PATH}/services             list (search, filter, status)
    /{ADMIN_URL_PATH}/services/new         create
    /{ADMIN_URL_PATH}/services/{id}        edit

  Rules:
    Defaults: slug IMMUTABLE, delete FORBIDDEN, other fields editable.
    Custom rows: full CRUD.
    Delete custom macro only if 0 services.
    Archive = status='archived'; public site only shows 'published'.
    Seeder never overwrites is_default=0 rows.

## 12. LANGUAGE

  Keys: {section}.{key}. Admin prefix: admin.*
  Every key in lang/en.php + lang/fr.php + lang/ar.php.
  Missing fr/ar fall back to en via array_merge.
  Service titles/shorts/longs fall back per-field to en via DB helper.

## 13. WORKFLOW — SITE TEMPLATE ONBOARDING (6 STEPS)

  1. mkdir views/{name}/{layouts,pages} + assets/{name}/{css,js,img,fonts}
  2. Move template's CSS/JS/img/fonts → assets/{name}/ (preserve subfolders)
  3. Main HTML → views/{name}/layouts/app.php:
       - @yield('title'), @yield('head'), @yield('content'), @yield('scripts')
       - <html lang="{{ $lang }}" dir="{{ $dir }}">
       - All CSS/JS → @asset()
       - @include('partials.preloader/header/footer')
       - @if($dir === 'rtl') Cairo font + RTL overrides @endif
  4. Other HTML → views/{name}/pages/{slug}.php using
       @extends('layouts.app') + @section('content')
  5. Extract language keys → replace visible strings with {{ t('…') }};
       add to lang/en + fr + ar.
  6. Remove inline <style>/<script> → assets/{name}/css/w5d-dynamic.css +
       assets/{name}/js/w5d-dynamic.js (keep only Arabic Cairo block in layout).
       Language links → data-w5d-lang. NO ?lang=xy.
       Add routes in src/Router.php. Set ACTIVE_TEMPLATE={name} in .env.

## 14. WORKFLOW — TAILADMIN ONBOARDING (5 STEPS)

  1. Clone TailAdmin into admin/assets/ (temporary):
       git clone https://github.com/TailAdmin/tailadmin-free-tailwind-dashboard-template admin/assets
  2. Build once:
       cd admin/assets && npm install && npm run build
     Then delete node_modules/, package.json, package-lock.json.
     Verify admin/assets/css/tailadmin.css and admin/assets/js/app.js exist.
  3. Extract shell:
       admin/views/layouts/app.php     ← from TailAdmin's index.html
       admin/views/partials/sidebar.php ← TailAdmin sidebar, ACL-filtered
       admin/views/partials/topbar.php  ← dark-mode toggle + lang switcher + profile
       admin/views/partials/footer.php
     All asset URLs → admin_asset('…').
  4. Create 7 dashboards:
       admin/views/pages/dashboard-{role}.php for each of:
         owner, admin, manager, member, investor, customer, user
     All @extends('layouts.app'). Each renders role-specific widgets.
  5. Create owner feature pages:
       admin/views/pages/{macro-works-index,macro-works-edit,
                          services-index,services-edit}.php
     Use TailAdmin table + form classes. Wire admin/index.php with
     Auth::require + ACL::can and role dispatch (Auth::dashboardForRole).

## 15. WIDGETS — ROLE-AGNOSTIC COMPONENTS

  Admin widgets live in data/widgets.php and admin/views/widgets/{key}.php.
  Each widget: {title, perm, roles[], group, icon, size}.

  Canonical widget list (reuse, don't duplicate):
    kpi-card · chart-card · data-table · activity-feed ·
    stats-grid · system-health · user-card · action-list

  Owner widgets (data adapters):
    owner-revenue, owner-users-by-role, owner-system-health,
    owner-billing, owner-recent-activity,
    owner-macro-works, owner-services-overview, owner-recent-service-edits
  Admin widgets:
    admin-content-stats, admin-registrations, admin-uploads,
    admin-approvals, admin-templates, admin-server-log
  Manager widgets:
    manager-team, manager-pending-work, manager-assignments, manager-performance
  Member widgets:
    member-my-tasks, member-my-submissions, member-notifications
  Investor widgets:
    investor-portfolio, investor-roi, investor-investments, investor-dividends
  Customer widgets:
    customer-services, customer-invoices, customer-tickets, customer-renewals
  User widgets:
    user-profile, user-notifications

  Each widget file calls Auth::require($def['perm']) internally.

## 16. VERIFICATION (10 CHECKS)

  [ ] All 7 roles land on their dashboard (TailAdmin shell renders)
  [ ] @w5d.tech password login works; non-@w5d.tech password rejected
  [ ] Google + GitHub login → role=user → user dashboard
  [ ] Sidebar shows only items the role can access
  [ ] /macro-works shows 5 defaults locked; custom creation works
  [ ] /services shows 130 defaults; custom creation works
  [ ] Default macro/service: slug read-only, delete blocked, archive works
  [ ] Site: home + pages render; /nonexistent → 404
  [ ] AR: <html dir="rtl"> + Cairo (site + admin)
  [ ] /admin (physical) → 403; ADMIN_URL_PATH change relocates admin

## 17. PRE-SUBMIT CHECKLIST

  [ ] TailAdmin CSS/JS served from admin/assets/css/tailadmin.css +
      admin/assets/js/app.js (no CDN at runtime)
  [ ] node_modules/ + package.json deleted after build
  [ ] No inline <style>/<script> (except Arabic RTL block in layout)
  [ ] Every user string escaped ({{ }} or e())
  [ ] Every state-changing form has @csrf
  [ ] Every admin route calls Auth::require($perm)
  [ ] Every admin widget calls Auth::require($def['perm'])
  [ ] Every new key exists in lang/en + fr + ar
  [ ] All URLs via url()/asset()/admin_url()/admin_asset()
  [ ] No ?lang=xy — use data-w5d-lang
  [ ] No Composer dependencies
  [ ] declare(strict_types=1) + namespace W5D\ in every src/ file
  [ ] Footer license block intact (610.5 TND/hour, before 2027)
  [ ] All 3 auth methods still work
  [ ] RTL works end-to-end (site + admin)

## 18. WHEN TO ASK

  Ask before:
    - Modifying TailAdmin's source CSS/JS (add w5d-admin.css/js instead)
    - Adding a new role, permission, or widget without a use case
    - Changing .env keys
    - Modifying src/Env.php, src/Lang.php, src/Auth.php, src/ACL.php public API
    - Allowing non-@w5d.tech password signup
    - Adding a non-OAuth auth provider
    - Deleting a default macro work or service

  Do NOT ask before:
    - Adding a page, route, or translation key
    - Creating a widget adapter in admin/views/widgets/
    - Fixing an obvious bug
    - Writing tests

## 19. GOTCHAS

  1. Sessions before output. config.php handles it.
  2. CSRF token survives session_regenerate_id.
  3. /admin (physical) stays blocked at Apache.
  4. Language cookie HttpOnly=false (JS writes, PHP reads).
  5. Compiled views: storage/cache/views/ (site) + storage/cache/views-admin/ (admin).
  6. TailAdmin must be BUILT once (npm install && npm run build) then
     node_modules/ deleted. Never rely on Tailwind CDN at runtime.
  7. License block (610.5 TND/hour, before 2027) in every footer
     (site footer AND admin footer).
  8. Every admin route re-checks Auth::require; every admin widget re-checks
     ACL::can. Do not trust the caller.
  9. Rank is ordering only. Authorization is ACL::can() only.
 10. Default macro/service slugs are IMMUTABLE. Delete is FORBIDDEN.
 11. Public site shows only services with status='published'.
 12. data/services.md is documentation; data/services.php is the seed; DB is live.
 13. OAuth state must match $_SESSION['oauth_state'].
 14. @w5d.tech emails may use password login; non-@w5d.tech CANNOT.
 15. NEVER modify TailAdmin's compiled tailadmin.css or app.js — W5D
     additions go in admin/assets/css/w5d-admin.css and
     admin/assets/js/w5d-admin.js (loaded after).
```
````

---

## Author

**Maher Kaddoussi**
Founder and owner, W5D

- 📧 [owner@w5d.tech](mailto:owner@w5d.tech)
- 🌐 https://w5d.tech
- 💼 Repository: https://github.com/W5Downer/w5d-php-cms

---

## Changelog

### [Unreleased]
- **TailAdmin** integrated as the default admin dashboard (Tailwind CSS + Alpine.js)
- **7 role dashboards** (owner, admin, manager, member, investor, customer, user)
- **ACL system** — 7 roles, 25 permissions, sidebar/route/widget guards
- **Owner features** — Manage Macro Works, Manage Services
- **130 services** specification in `data/services.md`
- **OAuth** (Google + GitHub) alongside `@w5d.tech` password login
- `admin/shared/` shared components used by site templates
- Updated AI Agent Prompt with TailAdmin onboarding workflow

### [0.1.0] — 2026-11-11
- Project foundation: `.env` loader, Blade-like View engine, `Lang` detection, `Router` with admin obfuscation
- Multi-template support (`views/{name}/` + `assets/{name}/`)
- Demo home + 404 pages
- Initial AI Agent Prompt

---

<div align="center">

**W5D PHP CMS** — built by Maher Kaddoussi for the W5D Team.

*Licensed at 610.5 TND/hour — offer valid before 2027.*

</div>
