# W5D PHP CMS

**A complete CMS for the W5D Team website — a company and a working ontology operating at 130,000-experts scale.**

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![SQLite](https://img.shields.io/badge/SQLite-3-003B57?logo=sqlite&logoColor=white)](https://www.sqlite.org/)
[![TailAdmin](https://img.shields.io/badge/Admin-TailAdmin-0A5EFF)](https://github.com/TailAdmin/tailadmin-free-tailwind-dashboard-template)
[![License](https://img.shields.io/badge/License-610.5%20TND%2Fh%20(offer%20before%202027)-0A5EFF)](#license)

> **W5D** — *World Five Dimensions* — operates across five macro works: **Cultures/Humanities**, **Built Environment**, **Sciences**, **Governance**, and **Technology**. This CMS powers its public site, its ontology, and an 8-role administration platform sized for **130 expert leaders, 13,000 team managers, and 130,000 expert employees**.

---

## Table of Contents

- [Description](#description)
- [Features](#features)
- [Architecture](#architecture)
- [Organization Model](#organization-model)
- [Data Architecture](#data-architecture)
- [Roles & ACL](#roles--acl)
- [Scope Enforcement](#scope-enforcement)
- [Weekly Work Engine](#weekly-work-engine)
- [Bulk Onboarding](#bulk-onboarding)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Multi-Template System](#multi-template-system)
- [Multi-Language System](#multi-language-system)
- [Admin Panel (TailAdmin)](#admin-panel-tailadmin)
- [Admin Menu](#admin-menu)
- [Owner Features](#owner-features)
- [Business Model Note](#business-model-note)
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

**W5D PHP CMS** is a self-contained content management system built for the W5D Team website — a company and a working ontology operating across five macro works. The system is designed for teams that need a fast, template-driven, multi-language public site **and** a multi-role administrative platform capable of coordinating **~143,000 people** without a heavyweight framework.

The CMS runs on vanilla PHP 8.2+ with SQLite — no Composer, no PHP framework lock-in. It ships with a Blade-inspired templating engine, two-tier view resolution (`views/{ACTIVE_TEMPLATE}/` → `views/_shared/`), and per-template `assets/{name}/` folders. Swapping the public front-end is a single `.env` change.

The **admin dashboard** uses **TailAdmin** (Tailwind + Alpine.js, one-time build then `node_modules/` discarded). Eight role-specific dashboards — **owner, admin, leader, manager, member, investor, customer, user** — share the TailAdmin shell and differ only in the widgets they display, guarded by a **35-permission ACL** and a **scope check** (`Scope::canActOn()`). The owner manages 130 leaders (one per service); each leader manages 100 managers; each manager manages ~10 experts; each expert submits weekly reports approved up the chain.

The CMS ships the **130-service ontology** (26 per macro work, seeded from `data/services.md`). Macro works and services are **owner-manageable**; defaults have locked slugs and cannot be deleted (archive only).

**Scale discipline:** at 130,000 experts × 52 weeks ≈ **6.76M weekly report rows per year**. The CMS stays on SQLite by **splitting** the database — one content DB (`w5d.sqlite`) plus **one work DB per macro work** (5 files) — with WAL, `busy_timeout`, short transactions, and composite indexes. Dashboards read **only from `rollups`**, never from raw rows. The public site reads live KPIs from a cached rollups JSON, not hard-coded strings.

Multi-language is automatic (Accept-Language → cookie → `.env` fallback, no `?lang=xy`). Arabic is fully supported with RTL, Cairo typography, and Tailwind RTL. Admin URL is obfuscated (`ADMIN_URL_PATH` in `.env`); the physical `/admin/` folder is blocked at Apache.

Security is built in: CSRF, session fixation protection, OAuth state validation, path traversal prevention, `.htaccess` denials, rate limiting, nightly `VACUUM INTO` backups, and an append-only audit log.

The CMS is offered at **610.5 TND per hour of work** — promotional rate valid before 2027.

Built for W5D by Maher Kaddoussi. Contact: [owner@w5d.tech](mailto:owner@w5d.tech).

---

## Delivered: static content engine

This repository now ships a **JSON-driven static site generator**. The public site is compiled to
plain HTML/CSS/JS and the **contents of `dist/` are uploaded directly to the root of
https://w5d.tech** — no installer, no database, no SSH command, no PHP at the host.

```bash
php bin/build.php                     # → dist/  (ledger template, en/fr/ar)
php bin/build.php --template=atlas    # → dist/  (atlas template)
php bin/build.php --template=folio    # → dist/  (folio template)
php bin/build.php --out=/tmp/site     # → anywhere else
```

A clean build is currently **639 files across 3 languages and 3 templates** with zero render errors.

### Three rules the engine enforces

1. **One page = one JSON file.** Every page, service, macro, project, declaration and ontology
   layer lives in its own file under `data/content/`. A page cannot contradict another page
   because no two pages share a data source. Collection indexes (`/services/`, `/ecosystem/`,
   `/declarations/`) are *derived* from the records, so they cannot drift.
2. **Any page renders in any installed template.** Blocks resolve two-tier:
   `views/{ACTIVE_TEMPLATE}/blocks/{type}.php` → `views/_blocks/{type}.php`. A template
   restyles blocks; it never restates content.
3. **Every number is a PHP variable.** `config/variables.php` holds the counts, capacity ratios,
   money figures and doctrine thresholds; page JSON refers to them as `{{ counts.services.value }}`.
   Types (audience, member, status, ontology layer, meta solution, …) are vocabularies validated at
   load — an unknown value throws, so a typo can never publish.

### The six-layer ontology

The site models the W5D structure as a **stacked ontology**, not a flat list of features:

> Projects instantiate Meta Solutions; Meta Solutions operate on Macro Works; Macro Works contain
> Services; Services are delivered by Expert Teams; Academies form the competence that Expert Teams
> require.

| Layer | Content | Page |
|---|---|---|
| L5 Projects | `data/content/projects/*.json` (25) | `/ecosystem/` |
| L4 Meta Solutions | `data/content/layers/meta-solutions.json` | `/ontology/meta-solutions/` |
| L3 Macro Works | `data/content/macros/*.json` (5) | `/macro-works/` |
| L2 Services | `data/content/services/*.json` (130) | `/services/` |
| L1 Expert Teams | `data/content/layers/expert-teams.json` | `/ontology/expert-teams/` |
| L0 Academies | `data/content/layers/academies.json` | `/ontology/academies/` |

The full diagram is one block (`stack`) rendered by one file, so every page draws the same stack
from the same source. Each project record also carries `meta_solution` and `services_consumed`
(the §4.5 mapping) which its page renders as *"What this project instantiates"*.

**The load-bearing gaps are published, not buried:** 130,000 is a design equation (the real
footprint is 26 developers in Tunisia); Dyon enforcement is proposed, not verified; the dossier's
5% sampling recommendation is kept separate from the README's 20-reports-per-service (2%) rule;
universal dependencies are acyclic only when time-unrolled; and "Live" never means "1,000 experts
employed". See `docs/ontology-six-layers.md`.

### Documentation

| File | Covers |
|---|---|
| `docs/ontology-six-layers.md` | the six-layer stack, dependency directions, §4.5 mapping, §4.7 caveats |
| `docs/page-data.md` | the page JSON schema: every field and block type |
| `docs/variables.md` | the variable registry and the type vocabularies |
| `docs/developer-guide.md` | engine architecture, adding blocks/pages/templates/languages |
| `docs/deployment.md` | uploading `dist/` to the w5d.tech web root |
| `docs/templates.md` | onboarding a new template |

### Templates

Three skins ship today: **ledger** (editorial/brass, default), **atlas** (cartographic/cyan) and
**folio** (paper/oxblood). Each adds only additive CSS over `assets/_base/css/base.css` and may
override any block or partial. Switching is `--template=` at build time or `active_template` in
`config/app.php`.

### Discipline kept from the specification

- **Zero third-party requests** — no CDN, no jQuery, no icon font; the carousel, theme switch and
  language switcher are dependency-free (`assets/_base/js/site.js`).
- **Honest-disabled forms** — the contact form renders disabled until an `endpoint` is configured.
- **"Sidq"** — any unsupplied value renders as a shared `to be confirmed` chip from
  `v('open_value')`; nothing is guessed.
- **Localisation** — content is `{en,fr,ar}` maps in JSON; UI chrome lives only in `lang/{code}.php`;
  Arabic is RTL with its own font stack and `assets/_base/css/rttl.css` equivalent.

---


## Features

### Templating & Public Site
- Blade-inspired engine (no Composer): `@extends`, `@section`, `@yield`, `@include`, `@if`, `@foreach`, `@for`, `@while`, `@php`, `@csrf`, `@lang`, `@asset`; `{{ }}` escaped, `{!! !!}` raw
- Compiled view cache: `storage/cache/views/` (site) + `storage/cache/views-admin/` (admin)
- Two-tier site resolution: `views/{ACTIVE_TEMPLATE}/` → `views/_shared/`
- Admin resolution: `admin/views/` only
- Multi-template: swap via `ACTIVE_TEMPLATE` in `.env`
- **Owl Carousel 2** sliders on the homepage, 5 macro pages, vision-2027, and countdown-festival (36 slides, animated ApexCharts, gradient backgrounds, RTL-safe — see [Sliders prompt](#sliders-prompt) at the end)

### Multi-Language
- Accept-Language quality-weighted parsing → cookie → `.env` fallback
- **No `?lang=xy`** — cookie `w5d_lang` only
- EN · FR · AR with RTL + Cairo for Arabic (site + admin)
- Fallback: `lang/{code}.php` → `lang/en.php` → key string

### Admin (TailAdmin)
- **TailAdmin** as the default shell (Tailwind + Alpine.js)
- **8 role dashboards**: owner · admin · leader · manager · member · investor · customer · user
- **24-item admin menu** (see [Admin Menu](#admin-menu)), filtered by ACL + scope
- **Obfuscated URL** (`ADMIN_URL_PATH`), physical `/admin/` blocked at Apache
- Dark mode built in

### Roles, Permissions, Scope
- **8 roles**, ordered by rank (ordering only — never used for auth)
- **35 permissions** (`ACL::can($role, $perm)`)
- **Scope check** (`Scope::canActOn($actor, $target)`): owner/admin see all; leader sees own service; manager sees own experts; expert sees self
- **Capacity caps**: leader ≤ 100 managers, manager ≤ 12 experts (soft 10), service ≤ 1,000 experts

### Weekly Work Engine
- Cascading directives: owner → 130 leaders → 13,000 managers → 130,000 experts
- Weekly reports, approvals, blockers, check-ins, timesheets
- Cron `bin/rollup.php` finalises `rollups` hourly (every 15 min on Fridays)
- Aggregate-first dashboards — no page loads 130,000 rows

### Bulk Onboarding
- CSV import (owner area): `email, name, service_slug, parent_email`
- One-time invitation tokens → Google/GitHub sign-in promotes `user` → `member`
- Dyon QR identity linked to each account

### Owner Features
- **Manage Macro Works** — 5 defaults (locked slug, delete forbidden) + full CRUD for custom
- **Manage Services** — 130 defaults (locked slug, delete forbidden, archive allowed) + full CRUD for custom
- Changes propagate to public site (published services only)

### Authentication (3 methods only)
- Email + password — **only `@w5d.tech`**
- Google OAuth — new users → role `user`
- GitHub OAuth — new users → role `user`
- OAuth state validated against `$_SESSION['oauth_state']`

### Data Discipline
- **Split SQLite**: `w5d.sqlite` (content + users) + `work_{macro}.sqlite` × 5 (tasks, reports, rollups)
- WAL · `busy_timeout` · short transactions · composite indexes
- **Aggregate-first**: dashboards and public site read `rollups`, never raw rows
- **Live KPI feed**: public carousels read `/api/kpis.json` (cached rollups)
- Nightly `VACUUM INTO` backup with off-server copy

### Security
- CSRF on all forms (`csrf_token()`, `csrf_field()`, `csrf_verify()`)
- Session fixation protection, HttpOnly + SameSite=Lax
- Rate limiting on login and CSV import
- Path traversal prevention, symlink refusal
- `.htaccess` denies `.env`, `.sqlite`, `.sql`, `.md`
- Append-only audit log for every scope-crossing action
- Personal data handling per Tunisia's Organic Law 2004-63 (confirm with counsel)

### Performance
- Static asset cache 7 days
- Compiled template cache
- TailAdmin built once (no runtime Tailwind CDN)
- No blocking loaders, no third-party requests by default

---

## Architecture

### Request lifecycle

```
Browser → .htaccess → static? → serve
                    ↓ no
              index.php → config.php
                    ├── Env::load('.env')
                    ├── Session::start()
                    ├── Lang detection
                    ├── View (site) + View (admin)
                    ├── ACL::init(data/)
                    └── Router::dispatch()
                        ├── /{ADMIN_URL_PATH}/* → admin/index.php
                        │   ├── Auth::require($perm)
                        │   ├── Scope::canActOn($actor, $target)
                        │   └── Auth::dashboardForRole($role)
                        └── otherwise → site route → View::render()
```

### Key components

| File | Role |
|---|---|
| `src/Env.php` | `.env` parser |
| `src/Lang.php` | Language detection, cookie, `switcherJs()` |
| `src/View.php` | Blade-like compiler + evaluator |
| `src/Router.php` | Site routes + admin obfuscation |
| `src/ACL.php` | Roles, permissions, `sidebarFor()`, capacity checks |
| `src/Auth.php` | Session, business-email check, role dashboards |
| `src/Auth/OAuth.php` | Google + GitHub (cURL) |
| `src/Scope.php` | `canActOn($actor, $target)` — row-level scope |
| `src/Week.php` | Weekly directive cascade, cutoff times |
| `src/Rollup.php` | Aggregate refresh (called by cron) |
| `src/Seed.php` | Idempotent seeder (5 macros, 130 services) |
| `src/helpers.php` | `t() · e() · url() · asset() · admin_url() · admin_asset() · csrf_*()` |
| `config.php` | Bootstraps everything |
| `index.php` | Single entry point |
| `bin/rollup.php` | Cron: refresh rollups hourly / 15-min Fridays |

---

## Organization Model

```
                              ┌──────────┐
                              │  OWNER   │  1
                              └────┬─────┘
                                   │
              ┌────────────────────┼────────────────────┐
              │                    │                    │
        ┌─────▼─────┐        ┌─────▼─────┐        ┌─────▼─────┐
        │  LEADER   │  … ×130 (one per service)        │  LEADER   │
        └─────┬─────┘        └─────┬─────┘        └─────┬─────┘
              │                    │                    │
       100 managers per leader (13,000 total)
              │
        10 experts per manager (~130,000 total, 1,000 per service)
```

- **Accounts total:** ~143,131 (owner + 130 leaders + 13,000 managers + 130,000 experts)
- **Span risk:** a leader with 100 direct reports cannot hold weekly 1:1s — the CMS supports an **optional cluster-lead flag** (`is_cluster_lead`) so leaders can sync with 10 cluster leads weekly and rotate 1:1s through all managers
- **Manager ratio:** 1 manager per ~9 experts (soft target 10)
- **Optional:** managers may be counted inside the 1,000 per service (making the ratio 1:9) — a **documented decision**, configured via `settings` table

---

## Data Architecture

The CMS stays on SQLite but **splits** the database:

```
storage/
├── w5d.sqlite                ← content, users, macro_works, services, sessions, audit_log
├── work_cultures.sqlite      ← tasks, reports, rollups for cultures
├── work_built.sqlite         ← same for built
├── work_sciences.sqlite      ← same for sciences
├── work_governance.sqlite    ← same for governance
└── work_technology.sqlite    ← same for technology
```

**Why split:** 130,000 reports in a 48-hour window ≈ 0.75 writes/s average; a 50× Friday peak ≈ 40 small writes/s. SQLite in WAL mode on SSD handles that when sharded by macro work.

**Data volume:** 130,000 × 52 ≈ **6.76M report rows per year**. Rollups keep dashboard queries at O(130 leaders) instead of O(130,000 experts).

**Fallback:** the schema ports cleanly to PostgreSQL if the "no PostgreSQL" rule is ever relaxed.

### Work DB schema (per macro work)

```sql
CREATE TABLE weeks (
    id          INTEGER PRIMARY KEY,
    iso_year    INTEGER NOT NULL,
    iso_week    INTEGER NOT NULL,
    state       TEXT NOT NULL DEFAULT 'open'
                CHECK (state IN ('open','reported','approved','locked')),
    created_at  TEXT NOT NULL,
    locked_at   TEXT
);

CREATE TABLE directives (
    id          INTEGER PRIMARY KEY,
    week_id     INTEGER NOT NULL REFERENCES weeks(id),
    from_user   INTEGER NOT NULL,
    to_user     INTEGER NOT NULL,
    priority    INTEGER NOT NULL DEFAULT 3,       -- 1..5
    target_num  REAL,
    target_unit TEXT,
    text        TEXT NOT NULL,
    created_at  TEXT NOT NULL
);

CREATE TABLE assignments (
    id          INTEGER PRIMARY KEY,
    week_id     INTEGER NOT NULL REFERENCES weeks(id),
    expert_id   INTEGER NOT NULL,
    task        TEXT NOT NULL,
    placement   TEXT NOT NULL CHECK (placement IN ('real','between','virtual')),
    evidence    TEXT,
    due_at      TEXT NOT NULL,
    created_at  TEXT NOT NULL
);

CREATE TABLE weekly_reports (
    id           INTEGER PRIMARY KEY,
    week_id      INTEGER NOT NULL REFERENCES weeks(id),
    expert_id    INTEGER NOT NULL,
    manager_id   INTEGER NOT NULL,
    output_num   REAL,
    output_unit  TEXT,
    evidence     TEXT NOT NULL,
    submitted_at TEXT NOT NULL,
    status       TEXT NOT NULL DEFAULT 'submitted'
                 CHECK (status IN ('submitted','approved','rejected','late'))
);
CREATE INDEX idx_reports_week_status ON weekly_reports(week_id, status);
CREATE INDEX idx_reports_manager     ON weekly_reports(manager_id, week_id);

CREATE TABLE approvals (
    id           INTEGER PRIMARY KEY,
    report_id    INTEGER NOT NULL REFERENCES weekly_reports(id),
    approver_id  INTEGER NOT NULL,
    level        TEXT NOT NULL CHECK (level IN ('manager','leader')),
    decision     TEXT NOT NULL CHECK (decision IN ('approve','reject')),
    reason       TEXT,
    decided_at   TEXT NOT NULL
);

CREATE TABLE blockers (
    id           INTEGER PRIMARY KEY,
    week_id      INTEGER NOT NULL REFERENCES weeks(id),
    raised_by    INTEGER NOT NULL,
    scope_to     INTEGER NOT NULL,
    text         TEXT NOT NULL,
    state        TEXT NOT NULL DEFAULT 'open'
                 CHECK (state IN ('open','escalated','closed')),
    opened_at    TEXT NOT NULL,
    closed_at    TEXT
);

CREATE TABLE checkins (
    id          INTEGER PRIMARY KEY,
    user_id     INTEGER NOT NULL,
    kind        TEXT NOT NULL CHECK (kind IN ('in','out')),
    at          TEXT NOT NULL,
    dyon_token  TEXT NOT NULL
);

CREATE TABLE rollups (
    id           INTEGER PRIMARY KEY,
    week_id      INTEGER NOT NULL REFERENCES weeks(id),
    level        TEXT NOT NULL CHECK (level IN ('manager','leader','macro')),
    user_id      INTEGER,              -- null at macro level
    headcount    INTEGER NOT NULL,
    reports_ok   INTEGER NOT NULL,
    reports_late INTEGER NOT NULL,
    rework_count INTEGER NOT NULL,
    rag          TEXT NOT NULL CHECK (rag IN ('red','amber','green')),
    computed_at  TEXT NOT NULL
);
CREATE INDEX idx_rollups_week_level ON rollups(week_id, level);
```

**Rollups are the only source for dashboards.** Public carousels read the cached `storage/cache/kpis.json` (written by `bin/rollup.php`).

---

## Roles & ACL

### 8 roles

| Role | Rank | Scope | Purpose |
|---|---:|---|---|
| `owner` | 100 | global | Full access, billing, settings, promote/demote, payroll |
| `admin` | 90 | global | CMS admin, content, users, templates (no billing) |
| `leader` | **80** | own `service_slug` | 100 managers, ~1,000 experts |
| `manager` | 70 | own `parent_id` | ~10 experts |
| `member` | 50 | self | Expert employee — own tasks, weekly reports |
| `investor` | 40 | global (read) | Portfolio, ROI, dividends |
| `customer` | 30 | self | Invoices, services, support |
| `user` | 10 | self | Profile, notifications |

**Rank is ordering only.** Authorization is always `ACL::can($role, $perm)` plus `Scope::canActOn($actor, $target)`.

### 35 permissions

Defined in `data/permissions.php` — each lists the roles that hold it.

**Account & system (13)**
`manage.users` · `manage.roles` · `manage.billing` · `manage.settings` · `manage.templates` · `manage.content` · `manage.macro_works` · `manage.services` · `manage.leaders` · `manage.managers` · `manage.onboarding` · `manage.certification` · `manage.payroll`

**Team & work (10)**
`manage.team` · `assign.work` · `assign.experts` · `approve.work` · `approve.weekly_report` · `submit.work` · `submit.weekly_report` · `view.own.work` · `view.team.reports` · `view.service.reports`

**Reporting & audit (3)**
`view.all.reports` · `view.audit` · `view.portfolio` · `manage.portfolio`

**Commerce (4)**
`view.invoices` · `manage.invoices` · `view.support` · `manage.support`

**Personal (5)**
`view.dashboard` · `view.profile` · `edit.profile` · `view.notifications` · `view.all.reports`

---

## Scope Enforcement

`ACL::can()` answers **"can this role perform this action?"**. `Scope::canActOn()` answers **"can this actor act on this specific target row?"**. Both must pass.

```php
<?php
declare(strict_types=1);
namespace W5D;

final class Scope
{
    public static function canActOn(array $actor, array $target): bool
    {
        return match ($actor['role']) {
            'owner', 'admin' => true,
            'leader'  => $target['service_slug'] === $actor['service_slug'],
            'manager' => (int)$target['parent_id'] === (int)$actor['id'],
            default   => (int)$target['id'] === (int)$actor['id'],
        };
    }

    public static function canList(array $actor, string $serviceSlug = null): bool
    {
        return match ($actor['role']) {
            'owner', 'admin' => true,
            'leader'         => $serviceSlug === null || $serviceSlug === $actor['service_slug'],
            default          => false,
        };
    }
}
```

Every admin controller calls `Scope::canActOn($actor, $row)` before rendering or mutating a target. Every list query is filtered by the scope at the SQL level (`WHERE service_slug = ?` or `WHERE parent_id = ?`).

### Capacity caps (enforced in `data/permissions.php` and code)

```php
const CAPS = [
    'leader.managers' => 100,
    'manager.experts' => 12,     // soft 10
    'service.experts' => 1000,
];
```

Attempting to exceed a cap returns HTTP 422 with a message like:

> "This leader already has 100 managers (cap reached). Reassign a manager first."

---

## Weekly Work Engine

Timezone: **Africa/Tunis**, Monday–Friday.

| When | System event |
|---|---|
| Mon 08:00 | Owner reviews last week's closed scorecards |
| Mon 09:00 | Owner publishes weekly directives |
| Mon 11:00 | Leaders split targets to managers |
| Mon 14:00 | Managers assign tasks to experts |
| Mon 17:00 | Experts accept tasks |
| Wed 12:00 | Managers escalate blockers to leaders |
| Wed 16:00 | Leaders send top blockers to owner |
| Fri 12:00 | Expert reports due |
| Fri 16:00 | Manager approvals due |
| Fri 18:00 | Leader sign-off due · submissions lock |
| Sat 02:00 | Rollups finalised |

Late submissions are flagged automatically and count against the scorecard.

### Weekly rhythm by role

**Owner — ~24h/week**
- Mon 08:00 (60 min) — review 130 leader scorecards, RAG sort
- Mon 09:00 (60 min) — publish directives
- Tue–Thu (5 × 60 min) — one macro review per macro work (26 leaders each)
- Tue–Thu (10 × 20 min) — 1:1s with red-flag leaders only
- Weekly rotation (13 × 20 min) — routine 1:1s (each leader every ~10 weeks)
- Thu (120 min) — payroll & finance
- Weekly (180 min) — investors, customers, partners
- Weekly (4 × 60 min) — public site · risk · leader hiring · Friday decision log

**Leader — ~20h/week**
- Mon 08:00 (30 min) — read owner directives
- Mon by 11:00 (90 min) — split into 100 manager targets
- Tue (60 min) — cluster-lead sync (10 cluster leads)
- Tue–Thu (20 × 20 min) — 1:1s with 20 managers on 5-week rotation + all red managers
- Wed by 12:00 (60 min) — resolve escalated blockers, escalate to owner by 16:00
- Thu (200 min) — quality sampling: 20 random reports against rubric
- Fri (120 min) — approve manager roll-ups, sign off by 18:00
- Fri (60 min) — one-page digest to owner
- Weekly (60 min) — capacity & vacancies
- Weekly (120 min) — service roadmap

**Manager — ~9.7h/week management + ~31h production**
- Mon by 14:00 (60 min) — assign 10 tasks
- Daily 09:15 (4 × 15 min) — stand-up (Tue–Fri)
- Tue–Thu (10 × 15 min) — 1:1 per expert
- Wed by 12:00 (30 min) — escalate 24h+ blockers
- Thu (60 min) — spot-check evidence (QR + outputs)
- Fri by 16:00 (100 min) — approve 10 reports
- Fri (60 min) — team summary
- Weekly (60 min) — timesheets & leave

**Expert employee — 40h/week**
- Mon by 17:00 (30 min) — accept task, plan week
- Daily (5 × 10 min) — stand-up
- Mon–Fri (~33h) — production (Real placement = QR check-in; Virtual = logged outputs)
- Weekly (120 min) — one Dyon module
- Weekly (90 min) — peer review of 2 colleagues
- Tue–Thu (15 min) — 1:1 with manager
- Weekly (60 min) — documentation
- Fri by 12:00 (45 min) — submit weekly report with evidence

**Examples of deliverables by macro:** records digitised (cultures) · site inspection reports (built) · datasets produced (sciences) · regulatory briefs (governance) · commits, models, deployed services (technology). These become the `evidence` field on the weekly report.

---

## Bulk Onboarding

130,000 experts cannot be typed in manually.

**Owner area → Onboarding → CSV import**

CSV columns:
```csv
email,name,service_slug,parent_email
amina.b@example.com,Amina Ben Salah,heritage-digitisation,leader.cultures@w5d.tech
```

Each row:
1. Creates a `user` with `role='member'`, `status='invited'`, `service_slug`, `parent_id` resolved from `parent_email`.
2. Generates a one-time invitation token.
3. On Google/GitHub sign-in, the token **promotes** the account from `user` to `member` (never higher).
4. Links a Dyon QR identity.

**`@w5d.tech` requirement** applies only to **leaders and above**. Experts sign in with Google or GitHub.

Import is **rate-limited** (10 imports/hour per owner) and **idempotent** (re-importing the same email updates but never duplicates).

---

## Requirements

- **PHP ≥ 8.2** with `pdo_sqlite`, `mbstring`, `json`, `session`, `openssl`, `curl`
- **SQLite 3** (bundled)
- **Apache 2.4+** with `mod_rewrite` (or Nginx equivalent)
- **Node.js ≥ 18** — one-time TailAdmin build only (`npm install && npm run build`); discarded after
- **No Composer**

---

## Installation

```bash
git clone https://github.com/W5Downer/w5d-php-cms.git
cd w5d-php-cms

# Folders
mkdir -p src/Auth bin data lang
mkdir -p admin/assets/{css,js,img,fonts}
mkdir -p admin/views/{layouts,partials,pages,widgets}
mkdir -p assets/default/{css,js,img,fonts}
mkdir -p views/default/{layouts,partials,pages} views/_shared/pages
mkdir -p storage/{cache/views,cache/views-admin,sessions,backups}

# Env
cp .env.example .env
php -r "echo password_hash('YourStrongPassword', PASSWORD_DEFAULT), PHP_EOL;"
# paste into .env → WEBMASTER_PASSWORD_HASH=...

# TailAdmin (one-time build)
cd admin/assets
git clone https://github.com/TailAdmin/tailadmin-free-tailwind-dashboard-template .
npm install && npm run build
cd ../..
rm -rf admin/assets/node_modules admin/assets/package.json admin/assets/package-lock.json

# Cron (add to crontab)
# */15 * * * 5 php /path/to/w5d/bin/rollup.php
# 0 * * * *   php /path/to/w5d/bin/rollup.php
# 0 3 * * *   sqlite3 storage/w5d.sqlite "VACUUM INTO 'storage/backups/w5d-'\$(date +%F)'.sqlite'"

# Visit
# http://localhost/w5d/          → home
# http://localhost/w5d/{ADMIN_URL_PATH} → admin
# http://localhost/w5d/admin     → 403
```

---

## Configuration

```dotenv
# Application
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/w5d
APP_BASE_PATH=/w5d
APP_DEFAULT_LANG=en
ACTIVE_TEMPLATE=default
APP_TIMEZONE=Africa/Tunis

# Admin
ADMIN_URL_PATH=/admin
WEBMASTER_USERNAME=admin
WEBMASTER_EMAIL=owner@w5d.tech
WEBMASTER_PASSWORD_HASH=

# OAuth
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=

# Databases (split)
DB_PATH=storage/w5d.sqlite
WORK_DB_PATTERN=storage/work_{macro}.sqlite

# Session
SESSION_NAME=w5d_sess
SESSION_LIFETIME=86400
SESSION_SECURE=false

# License
LICENSE_RATE_TND=610.5
OFFER_DEADLINE=2026-12-31

# Capacity caps
CAP_LEADER_MANAGERS=100
CAP_MANAGER_EXPERTS=12
CAP_SERVICE_EXPERTS=1000
```

---

## Multi-Template System

Each site template owns:
```
assets/{name}/    ← CSS, JS, img, fonts
views/{name}/     ← layouts, partials, pages
```

Resolution: `views/{name}/` → `views/_shared/`.

**Adding a template:** create both folders, copy structure from `views/default/`, move assets, set `ACTIVE_TEMPLATE={name}`, reload.

`views/_shared/` is only for template-agnostic pages (404, error).

---

## Multi-Language System

Detection: Cookie `w5d_lang` → `Accept-Language` → `.env` `APP_DEFAULT_LANG`.

| Code | Language | Direction | Font |
|---|---|---|---|
| `en` | English | LTR | Theme default |
| `fr` | Français | LTR | Theme default |
| `ar` | العربية | RTL | Cairo (auto) |

Switching is cookie-based (`data-w5d-lang` attribute + `Lang::switcherJs()`). **No `?lang=xy`**.

---

## Admin Panel (TailAdmin)

Admin uses **[TailAdmin](https://github.com/TailAdmin/tailadmin-free-tailwind-dashboard-template)** — the free Tailwind + Alpine.js admin template.

| TailAdmin | W5D |
|---|---|
| `index.html` shell | `admin/views/layouts/app.php` |
| Sidebar | `admin/views/partials/sidebar.php` (ACL + scope filtered) |
| Topbar | `admin/views/partials/topbar.php` (dark mode + lang + profile) |
| Dashboard widgets | `admin/views/widgets/{key}.php` (data adapters) |
| Tables, charts, forms | `admin/assets/css/tailadmin.css` + `admin/assets/js/app.js` |
| Webpack build | One-time `npm install && npm run build`, then `node_modules/` deleted |
| Tailwind CDN | **Not used** at runtime |

**Access rules**

| URL | Result |
|---|---|
| `/w5d/{ADMIN_URL_PATH}` | ✅ Admin dashboard |
| `/w5d/{ADMIN_URL_PATH}/macro-works` | ✅ Owner feature (ACL-gated) |
| `/w5d/{ADMIN_URL_PATH}/services` | ✅ Owner feature (ACL-gated) |
| `/w5d/admin` (physical) | 🚫 **403** |
| `/w5d/admin/index.php` | 🚫 **403** |

---

## Admin Menu

Visibility: **O** owner · **A** admin · **L** leader · **M** manager · **E** expert employee.

Everyone sees only their own scope.

| # | Item | Who | Purpose |
|---|---|---|---|
| 1 | Command Center | O A L M E | Role-scoped landing: headcount vs target, completion %, RAG, open blockers |
| 2 | Organization Tree | O A L M | Drill-down with capacity bars (x/100, x/10, x/1,000) |
| 3 | Onboarding & Capacity | O A L M | CSV import, invitations, 8-week checklist, slot assignment |
| 4 | Macro Works & Services | O A (L read) | Owner CRUD + staffing counts per service |
| 5 | Weekly Planner | O A L M | Cascading directives, templates, carry-over |
| 6 | Tasks & Assignments | L M E | Task board with placement and required evidence |
| 7 | Weekly Reports & Approvals | O A L M E | Expert submission + approval queues |
| 8 | Check-ins & Dyon QR | M E (O A L aggregate) | QR in/out + certification status |
| 9 | Quality & Audit Sampling | O A L M | Random-sample generator, rubric scoring, rework |
| 10 | Scorecards & RAG | O A L M E(own) | Weekly weighted score, RAG, leaderboards |
| 11 | Blockers & Escalations | O A L M E | Raise → manager (24h) → leader (48h) → owner (weekly top-5) |
| 12 | Timesheets & Leave | L M E (O A aggregate) | Hours, absences, leave approvals |
| 13 | Payroll & Finance | O (L aggregate) | 600 TND salary runs, invoices, funding vs payroll forecast |
| 14 | Parts, Investors & Customers | O A | Parts sold (10/day, 70/week), funded vs filled, countdown |
| 15 | Learning Hub | O A L M E | Dyon modules, onboarding courses, skill tags |
| 16 | Announcements & Stand-ups | O A L M E | Broadcasts with read receipts, stand-up notes |
| 17 | Reports & Exports | O A L M | Scheduled CSV/PDF within scope |
| 18 | People Cases | O A L M | Replacements, transfers, disputes |
| 19 | Public Site Content | O A | Carousel text, live KPI feed, countdown, EN/FR/AR |
| 20 | Users, Roles & Scope | O A | Role assignment, scope fields, MFA, suspensions |
| 21 | Audit Log & Security | O A | Append-only scope-crossing trail, failed logins |
| 22 | System Health & Rollups | O A | Cron status, DB sizes, backup status, Friday-peak monitor |
| 23 | Settings | O | Week calendar, timezone, RAG thresholds, cutoffs, caps |
| 24 | Profile & Notifications | O A L M E | Personal data, language, alerts |

---

## Owner Features

### Manage Macro Works

`/{ADMIN_URL_PATH}/macro-works`

- 5 defaults (locked slug, delete forbidden) + custom CRUD
- Editable: label (EN/FR/AR), placement, color, sort_order
- Delete custom only if 0 services

### Manage Services

`/{ADMIN_URL_PATH}/services`

- 130 defaults (locked slug, delete forbidden, archive allowed) + custom CRUD
- Editable: title, short, long (EN/FR/AR), macro, placement, virtual_core, status, sort_order
- Public site shows only `status='published'`

**Seeding:** `src/Seed.php` runs on first boot (idempotent). Never overwrites `is_default=0` rows or edited defaults.

**Sources of truth:**
- `data/services.md` — human documentation of all 130
- `data/services.php` — seed source
- `w5d.sqlite` — live data

---

## Business Model Note

**Open decision before seeding payroll:**

- A part costs **10 TND/day** → **300 TND/month**
- A certified post pays **600 TND/month salary**
- At full scale: **130,000 × 600 = 78,000,000 TND/month payroll**

**Options:**

1. **One post needs two parts** → 600 TND/part/day. Update pricing UI to reflect.
2. **Other funding covers the difference** → grants, sponsors, owner capital. Track per service in `Payroll & Finance`.
3. **Salary scaled** → 300 TND/month at 1 part/day. Update the offer.

**The chosen model is stored in `settings` and shown on the Payroll & Finance dashboard.** It must be decided **before** onboarding begins.

---

## Security

- CSRF tokens on all forms
- Session fixation protection, HttpOnly, SameSite=Lax, Secure in prod
- OAuth state validation
- Rate limiting: 5 logins/hour/IP, 10 imports/hour/owner
- Path traversal prevention, symlink refusal
- `.htaccess`: deny `.env`, `.sqlite`, `.sql`, `.md`, `.log`, physical `/admin/`
- HTTP headers: `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`
- Nightly `VACUUM INTO` backup + off-server copy
- Append-only `audit_log` for every scope-crossing action
- **Privacy review** before launch: personal data on ~143,000 people (Tunisia Organic Law 2004-63 — confirm with counsel)
- **Load test** with 130,000 synthetic reports before festival launch

---

## Directory Structure

```
w5d-php-cms/
├── .env · .env.example · .gitignore · .htaccess
├── index.php · config.php
│
├── src/
│   ├── Env.php · Lang.php · View.php · Router.php
│   ├── ACL.php · Auth.php · Auth/OAuth.php
│   ├── Scope.php · Week.php · Rollup.php · Seed.php
│   └── helpers.php
│
├── bin/
│   └── rollup.php                 ← cron
│
├── data/
│   ├── roles.php (8) · permissions.php (35)
│   ├── sidebar.php (24 menu items) · widgets.php
│   ├── services.php · services.md
│
├── assets/{site}/…                ← per-template assets
├── views/
│   ├── default/  ·  _shared/
│   └── {site}/…                    ← more site templates
│
├── admin/
│   ├── .htaccess · index.php
│   ├── assets/{css,js,img,fonts}
│   └── views/{layouts,partials,pages,widgets}
│
├── lang/{en,fr,ar}.php
│
└── storage/
    ├── w5d.sqlite
    ├── work_{cultures,built,sciences,governance,technology}.sqlite
    ├── cache/{views,views-admin}/
    ├── backups/
    └── sessions/
```

---

## Development Workflow

### Add a page
1. Create `views/{ACTIVE_TEMPLATE}/pages/{name}.php`
2. Add route in `src/Router.php`
3. `@extends('layouts.app')` + `@section('content')`

### Add a translation key
Add to `lang/en.php`, `lang/fr.php`, `lang/ar.php` (copy EN in FR/AR).

### Add an admin widget
1. Add to `data/widgets.php`
2. Create `admin/views/widgets/{key}.php` (data adapter)
3. Call `Auth::require($def['perm'])` inside the widget

### Add a permission
1. Add to `data/permissions.php` with roles
2. Add the guard call in the route or widget

### Clear caches
```bash
rm -rf storage/cache/views/* storage/cache/views-admin/*
```

---

## License

**W5D PHP CMS — licensed at 610.5 TND per hour of work.**
Promotional offer valid **before 2027** (strictly before 31 December 2026).
After that date, the standard rate applies.

Footer license block (required on every page):
```html
<p>W5D Admin Dashboard licensed at 610.5 TND/hour of work.
   Promotional offer valid before 2027.</p>
```

---

## Contributing

1. Fork
2. Branch: `git checkout -b feature/{name}`
3. Follow the AI Agent Prompt below
4. Run the pre-submit checklist
5. PR

### Coding conventions
- PHP 8.2+, `declare(strict_types=1);` at the top of every `src/*.php`
- PSR-4-ish `W5D\` namespace, `require_once` in `config.php`
- No Composer
- No inline `<style>` / `<script>`
- Typed methods
- `e()` / `{{ }}` for every user string
- `@csrf` on every state-changing form

---

## AI Agent Prompt

> Copy verbatim into an AI coding assistant's system prompt.

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

The CMS powers THREE surfaces:
  A. Public website          views/{ACTIVE_TEMPLATE}/ + assets/{name}/
  B. Admin dashboard         admin/ (TailAdmin) — 8 roles
  C. Weekly work engine      per-macro work DBs (tasks → reports → rollups)

Scale: 1 owner, 130 leaders (1/service), 13,000 managers (100/leader),
~130,000 experts (~10/manager, 1,000/service). Total ~143,131 accounts.

## 2. HARD CONSTRAINTS

- PHP 8.2+. NO Composer. NO frameworks.
- SQLite 3 (PDO). NO MySQL/PostgreSQL. Split: 1 content DB + 5 work DBs.
- Vanilla JS at runtime. Webpack one-time for TailAdmin only.
- Every src/ file: declare(strict_types=1); + namespace W5D\.
- OAuth: cURL only.

## 3. DEFAULT ADMIN TEMPLATE — TailAdmin

  https://github.com/TailAdmin/tailadmin-free-tailwind-dashboard-template

INTEGRATION:
  1. ONE-TIME BUILD:
       cd admin/assets && npm install && npm run build
     → admin/assets/css/tailadmin.css + admin/assets/js/app.js
     Then DELETE node_modules/, package.json, package-lock.json.
     Tailwind CDN never used at runtime.
  2. SHELL: admin/views/layouts/app.php with @include('partials.sidebar')
     and @include('partials.topbar'), @yield('content'), @yield('scripts').
  3. SIDEBAR: admin/views/partials/sidebar.php iterates
     ACL::sidebarFor(Auth::role()) and renders only accessible items.
  4. TOPBAR: dark mode toggle, lang switcher (data-w5d-lang), profile.
  5. 8 DASHBOARDS: admin/views/pages/dashboard-{owner,admin,leader,
     manager,member,investor,customer,user}.php.
  6. OWNER FEATURES: macro-works-index/edit, services-index/edit,
     onboarding, payroll — all in TailAdmin tables + forms.
  7. RTL: <html dir="rtl"> + Cairo font family.
  8. NEVER modify tailadmin.css or app.js. W5D additions go in
     admin/assets/css/w5d-admin.css and admin/assets/js/w5d-admin.js.

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

## 7. DATA MODEL

Main DB (storage/w5d.sqlite):
  users — id, email, name, password_hash, role, provider, created_at,
          macro_slug, service_slug, parent_id, status(invited|active|paused|exited),
          is_cluster_lead, dyon_qr_id
  macro_works — id, slug, label_en/fr/ar, placement, color, is_default, sort_order
  services — id, slug, macro_slug, title_en/fr/ar, short_en/fr/ar,
             long_en/fr/ar, placement, virtual_core, is_default, sort_order,
             status(published|draft|archived)
  audit_log — append-only
  settings — key/value

Work DBs (storage/work_{macro}.sqlite, one per macro work):
  weeks, directives, assignments, weekly_reports, approvals,
  blockers, checkins, rollups
  → schema in README.md §Data Architecture

Seeded from data/services.php on first boot (idempotent).
Defaults: is_default=1, immutable slug, delete forbidden (archive instead).
Custom rows: full CRUD.

## 8. ROLES (8) & PERMISSIONS (35)

  owner(100) > admin(90) > leader(80) > manager(70) > member(50) >
  investor(40) > customer(30) > user(10)

  Rank is ORDERING only. Authorization is ALWAYS ACL::can($role,$perm).
  Row-level access is ALWAYS Scope::canActOn($actor, $target).

  35 permissions in data/permissions.php:
    manage.users · manage.roles · manage.billing · manage.settings
    manage.templates · manage.content · manage.macro_works · manage.services
    manage.leaders · manage.managers · manage.onboarding ·
    manage.certification · manage.payroll
    manage.team · assign.work · assign.experts ·
    approve.work · approve.weekly_report ·
    submit.work · submit.weekly_report ·
    view.own.work · view.team.reports · view.service.reports
    view.all.reports · view.audit ·
    view.portfolio · manage.portfolio
    view.invoices · manage.invoices · view.support · manage.support
    view.dashboard · view.profile · edit.profile · view.notifications

## 9. SCOPE — src/Scope.php

  canActOn($actor, $target):
    owner/admin → true
    leader      → target.service_slug === actor.service_slug
    manager     → target.parent_id === actor.id
    default     → target.id === actor.id

  canList($actor, $serviceSlug): owner/admin true; leader only own service;
  others false.

  Every list query is scoped in SQL (`WHERE service_slug = ?`
  or `WHERE parent_id = ?`). Never load all rows then filter in PHP.

## 10. CAPACITY CAPS

  leader   ≤ 100 managers
  manager  ≤ 12 experts (soft 10)
  service  ≤ 1,000 experts

  Exceeding a cap returns HTTP 422 with a clear message.

## 11. AUTHENTICATION — 3 methods ONLY

  A. Email + password — ONLY @w5d.tech
  B. Google OAuth — new users → role `user`
  C. GitHub OAuth — new users → role `user`

  NEVER: Facebook, Apple, Twitter, Microsoft, or non-@w5d.tech password signup.

  Session: session_regenerate_id(true) on login/logout. HttpOnly,
  SameSite=Lax, Secure in prod. OAuth state validated against
  $_SESSION['oauth_state'].

  Bulk onboarding: CSV import (owner) creates role=member, status=invited,
  parent_id from parent_email, one-time token. Google/GitHub sign-in
  promotes `user` → `member` only. Rate-limited 10/hour/owner.

## 12. ADMIN URL OBFUSCATION

  ADMIN_URL_PATH in .env. Physical /admin/ blocked by .htaccess.
  Router dispatches /{ADMIN_URL_PATH}/* → admin/index.php.

## 13. WEEKLY WORK ENGINE

  Timezone: Africa/Tunis (fixed in .env APP_TIMEZONE).
  Cutoffs: Mon 09:00 directives · Fri 12:00 reports · Fri 16:00 manager
  approvals · Fri 18:00 leader sign-off · Sat 02:00 rollups.

  Cron (bin/rollup.php):
    */15 * * * 5  → Friday, every 15 minutes
    0 * * * *     → hourly otherwise

  Rollups write to rollups table. Dashboards and public KPI feed read
  ONLY rollups — never raw weekly_reports.

## 14. PUBLIC KPI FEED

  /api/kpis.json (cached in storage/cache/kpis.json) returns:
    { experts_employed, experts_target, services_count, macros_count,
      % toward festival, last_updated, funding_vs_payroll }

  Public carousels read this file (or fetch the JSON) — never hard-code
  numbers in translation strings.

## 15. OWNER FEATURES

  /{ADMIN_URL_PATH}/macro-works           list
  /{ADMIN_URL_PATH}/macro-works/new       create
  /{ADMIN_URL_PATH}/macro-works/{slug}    edit
  /{ADMIN_URL_PATH}/services              list (search, filter, status)
  /{ADMIN_URL_PATH}/services/new          create
  /{ADMIN_URL_PATH}/services/{id}         edit
  /{ADMIN_URL_PATH}/onboarding            CSV import + invitations
  /{ADMIN_URL_PATH}/payroll               funding vs payroll, 600 TND runs

  Rules:
    Defaults: slug IMMUTABLE, delete FORBIDDEN, other fields editable.
    Custom rows: full CRUD. Delete custom macro only if 0 services.
    Archive = status='archived'. Public site only shows 'published'.
    Seeder never overwrites is_default=0 rows.

## 16. LANGUAGE

  Keys: {section}.{key}. Admin prefix: admin.*
  Every key in lang/en.php + lang/fr.php + lang/ar.php.
  Missing fr/ar fall back to en.
  Service titles/shorts/longs fall back per-field to en via DB helper.

## 17. SITE TEMPLATE ONBOARDING (6 STEPS)

  1. mkdir views/{name}/{layouts,pages} + assets/{name}/{css,js,img,fonts}
  2. Move template assets → assets/{name}/ (preserve subfolders)
  3. Main HTML → views/{name}/layouts/app.php:
       - @yield('title') · @yield('head') · @yield('content') · @yield('scripts')
       - <html lang="{{ $lang }}" dir="{{ $dir }}">
       - All URLs → @asset()
       - @include('partials.preloader/header/footer')
       - @if($dir === 'rtl') Cairo + RTL overrides @endif
  4. Other HTML → views/{name}/pages/{slug}.php with @extends + @section
  5. Extract language keys → {{ t('…') }}; add to lang/en/fr/ar
  6. Remove inline <style>/<script> → assets/{name}/css/w5d-dynamic.css
       + assets/{name}/js/w5d-dynamic.js
       Language <a> → data-w5d-lang. NO ?lang=xy.
       Add routes in src/Router.php. Set ACTIVE_TEMPLATE={name} in .env.

## 18. TAILADMIN ONBOARDING (5 STEPS)

  1. git clone TailAdmin into admin/assets/
  2. cd admin/assets && npm install && npm run build
     Delete node_modules/, package.json, package-lock.json.
  3. Extract admin/views/layouts/app.php + partials/{sidebar,topbar,footer}.php
     All asset URLs → admin_asset().
  4. Create 8 dashboards (dashboard-{role}.php) + owner feature pages.
  5. Wire admin/index.php with Auth::require + Scope::canActOn +
     role dispatch (Auth::dashboardForRole).

## 19. SLIDERS — OWL CAROUSEL + APEXCHARTS

  See "Sliders prompt" in README.md (or the appended prompt file).
  Key rules:
    - 8 carousels, 36 slides (or 35 when post-deadline slide is hidden)
    - Owl Carousel 2.3.4 + ApexCharts 3.45+, jQuery 3.7.1
    - loop: false, rewind: true (never loop — clones break chart IDs)
    - Local fadeIn/fadeOut keyframes (animate.css banned)
    - Counters start on FIRST active slide, support decimals/prefix/suffix
    - Text-only KPI variant for "Dyon QR verified" etc.
    - Contrast scrim: white text on amber/emerald/teal/cyan needs dark
      overlay or darker gradient stops — verify ≥ 4.5:1 for every hue
    - Chart height via matchMedia (220/260/320), not hard-coded
    - IntersectionObserver respects prefers-reduced-motion
    - focusin pauses autoplay
    - json_encode(JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) + e()
      for data-chart attribute (apostrophes in labels never break it)
    - Illustrative badge for unsourced series
    - ~ prefix on uncertain figures
    - Server timezone Africa/Tunis
    - Page reloads 2s after countdown ends (post-deadline slide appears)
    - KPI numbers read from /api/kpis.json (live), not hard-coded

## 20. VERIFICATION (12 CHECKS)

  [ ] All 8 roles land on their dashboard (TailAdmin shell renders)
  [ ] @w5d.tech password login works; non-@w5d.tech rejected
  [ ] Google + GitHub login → role=user → user dashboard
  [ ] Sidebar shows only items the role + scope allow
  [ ] Scope::canActOn blocks out-of-scope reads (test a leader acting on
      another service's manager → 403)
  [ ] Capacity caps enforce (leader with 100 managers → 422 on 101st)
  [ ] /macro-works shows 5 defaults locked; custom creation works
  [ ] /services shows 130 defaults; custom creation works
  [ ] Weekly planner → report → approval → rollup works end-to-end
  [ ] Public carousels read live KPIs from /api/kpis.json
  [ ] AR: <html dir="rtl"> + Cairo (site + admin)
  [ ] /admin (physical) → 403; ADMIN_URL_PATH change relocates admin

## 21. PRE-SUBMIT CHECKLIST

  [ ] TailAdmin built once, node_modules/ deleted
  [ ] No inline <style>/<script> (except Arabic RTL block)
  [ ] Every user string escaped ({{ }} or e())
  [ ] Every form has @csrf
  [ ] Every admin route calls Auth::require($perm) AND Scope::canActOn($actor, $target)
  [ ] Every admin widget calls Auth::require($def['perm'])
  [ ] Every list query is scoped in SQL
  [ ] Every new key exists in lang/en + fr + ar
  [ ] All URLs via url()/asset()/admin_url()/admin_asset()
  [ ] No ?lang=xy
  [ ] No Composer dependencies
  [ ] declare(strict_types=1) + namespace W5D\ in every src/ file
  [ ] Footer license block intact (610.5 TND/hour, before 2027)
  [ ] KPI feed (/api/kpis.json) served from rollups, not hard-coded
  [ ] Business model decided (parts vs payroll) before payroll launch
  [ ] Audit log written for every scope-crossing action
  [ ] Backup cron verified

## 22. WHEN TO ASK

  Ask before:
    - Modifying TailAdmin source (add w5d-admin.css/js)
    - Adding a role, permission, or widget without a use case
    - Changing .env keys
    - Modifying src/Env.php, src/Lang.php, src/Auth.php, src/ACL.php,
      src/Scope.php public API
    - Allowing non-@w5d.tech password signup
    - Adding a non-OAuth auth provider
    - Deleting a default macro work or service
    - Raising capacity caps (owner approval)
    - Changing the business model (parts vs payroll)
    - Changing the scope rules

  Do NOT ask:
    - Adding a page, route, or translation key
    - Creating a widget adapter
    - Fixing an obvious bug
    - Writing tests

## 23. GOTCHAS

  1. Sessions before output.
  2. CSRF token survives session_regenerate_id.
  3. /admin physical folder stays blocked at Apache.
  4. Language cookie HttpOnly=false (JS writes, PHP reads).
  5. Compiled views: storage/cache/views/ + storage/cache/views-admin/.
  6. TailAdmin built once; node_modules deleted; no CDN.
  7. License block (610.5 TND/hour, before 2027) in every footer.
  8. Every admin route re-checks Auth + Scope. Every widget re-checks perm.
  9. Rank is ordering only. Authorization is ACL::can() only.
 10. Default macro/service slugs IMMUTABLE. Delete FORBIDDEN.
 11. Public site shows only services with status='published'.
 12. data/services.md = docs; data/services.php = seed; DB = live.
 13. OAuth state must match $_SESSION['oauth_state'].
 14. @w5d.tech emails may use password; non-@w5d.tech CANNOT.
 15. NEVER modify TailAdmin's compiled CSS/JS. Add w5d-admin.css/js.
 16. SQLite split: main DB + 5 work DBs. Cross-DB queries are forbidden
     — join in PHP, never in SQL.
 17. Rollups are the ONLY source for dashboards and public KPI feed.
     Never SELECT from weekly_reports for a list view.
 18. Festival post-deadline slide renders ONLY after 2027-01-01 00:00
     Africa/Tunis. Before that, slide count is 2 (not 3).
 19. Server timezone = Africa/Tunis. Set in .env and in PHP.
 20. Capacity caps: leader 100, manager 12 (soft 10), service 1,000.
```
