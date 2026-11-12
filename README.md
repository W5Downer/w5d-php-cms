# W5D PHP CMS

**A dynamic PHP + MySQL CMS for the W5D Team website — five macro works, 130 services, six ontology layers, multi-template, three languages, country-scoped, single domain.**

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Templates](https://img.shields.io/badge/templates-ledger%20%C2%B7%20atlas%20%C2%B7%20folio-0A5EFF)](#templates)
[![Languages](https://img.shields.io/badge/languages-en%20%C2%B7%20fr%20%C2%B7%20ar-0A5EFF)](#languages)
[![Sampling](https://img.shields.io/badge/sampling-5%25-0A5EFF)](#quality--sampling)
[![License](https://img.shields.io/badge/License-610.5%20TND%2Fh%20(before%202027)-0A5EFF)](#license)

> **W5D** — *World Five Dimensions* — is a company and a working ontology operating across five macro works: **Cultures/Humanities**, **Built Environment**, **Sciences**, **Governance**, and **Technology**. This CMS renders that ontology as a live website with a registration and certification portal attached.

---

## Table of Contents

- [What this is](#what-this-is)
- [The owner's strategy (130,000 experts)](#the-owners-strategy-130000-experts)
- [Deployment model](#deployment-model)
- [Architecture](#architecture)
- [Design system](#design-system)
- [Pages](#pages)
- [Owner dashboard](#owner-dashboard)
- [Quality & sampling](#quality--sampling)
- [Single source of truth](#single-source-of-truth)
- [Data model](#data-model)
- [Requirements](#requirements)
- [Installation](#installation)
- [Security](#security)
- [Directory structure](#directory-structure)
- [Templates](#templates)
- [Languages](#languages)
- [License](#license)
- [AI Agent Prompt](#ai-agent-prompt)
- [Author](#author)

---

## What this is

W5D PHP CMS is a **dynamic content management system**: PHP 8.2 + MySQL 8, no framework, no Composer, no third‑party runtime requests. Every page is rendered at request time from two layers:

- **Content** — JSON files under `data/content/` (pages, macros, services, projects, ontology layers, audiences, variables). Content is versioned in git, reviewed by the owner, and edited as files.
- **State** — MySQL tables (users, applicants, teams, weekly reports, payroll, tickets, audit). State is edited through the portal.

Both layers are joined by **slug** at render time. A service JSON defines what the service *is*; the `services_country` MySQL table says whether it is *active* in Tunisia and how many seats it has.

Three rules the engine enforces:

1. **One fact, one place.** Every number, vocabulary, country, and service fact is declared once — in `config/variables.php`, `config/vocab.php`, `data/countries/{iso}.json`, or the content JSON. Duplicates fail the linter.
2. **Any page renders in any template.** Blocks resolve two‑tier: `views/{ACTIVE_TEMPLATE}/blocks/{type}.php` → `views/_blocks/{type}.php`. A template restyles; it never restates content.
3. **Every number is a token.** Page JSON refers to variables as `{{ counts.services }}`. Tokens resolve at render; unresolved tokens fail the linter.

**No CDN, no jQuery, no icon font.** The carousel, theme switch, language switcher, and map are dependency‑free in `assets/_base/js/site.js` and `assets/_base/css/attention.css`.

**Honesty constraint.** `experts_current` is **0** today. The real footprint is **26 developers in Tunisia**. "130,000 experts" is a **design equation** (5 × 26 × 1,000), not a claim of employment. Every page that mentions the number links to `/trust/statement/`, and no page promises a job. See `docs/ontology-six-layers.md`.

---

## The owner's strategy (130,000 experts)

The owner runs a **single dynamic strategy**: turn 130,000 unemployed Tunisian higher‑education graduates into certified W5D team members, then use those teams to solve problems inside the five macro works. The CMS models this as a **four‑stage pipeline**, each stage a collection the owner edits from the portal.

### Stage 1 — Ontology (what needs solving)
- **5 macro works** — `data/content/macros/*.json`
- **130 services** — 26 per macro, `data/content/services/*.json`
- **Meta solutions** — `data/content/layers/meta-solutions.json`
- **Projects** — 25 records, `data/content/projects/*.json`

### Stage 2 — Certification (how a member becomes an expert)
Each service declares a **certification task set** in `data/content/certification/{service_slug}.json`. A task is a JSON object with `id`, `level` (1–4), `title` (en/fr/ar), `instructions` (en/fr/ar), `evidence`, `pass_rule`. A member registers → completes the task set → the owner (or a delegate) marks each task `approved` in MySQL → the member becomes a **certified expert team member** for that service.

### Stage 3 — Delivery (what the certified team produces)
Each service carries a **delivery contract** in `data/content/delivery/{service_slug}.json` — deliverable unit, weekly target, evidence rule. This is what the team actually ships once certified.

### Stage 4 — Reporting (how the owner sees it)
Every delivery row feeds the weekly report engine. `bin/rollup.php` (a cron script) writes daily aggregates to `rollups_daily`, and every dashboard reads **only** from `rollups_daily`. No page loads the raw 130,000 rows.

**Realistic claim:** one month is enough to **register and verify** 130,000 people (~4,334/day, ~1,000 per service). Certification takes longer. Every CTA below promises a **path**, never a job.

---

## Deployment model

**One domain, one repository root, one entry point.**

```
https://w5d.tech/
├── /                    → public site (dynamic)
├── /macro-works/{slug}/ → public site (dynamic)
├── /services/{slug}/    → public site (dynamic)
├── /projects/{slug}/    → public site (dynamic)
├── /join/, /app/*       → registration flow (dynamic)
├── /{ADMIN_URL_PATH}/*  → owner portal (dynamic, auth required)
└── /assets/*            → static assets (served directly by Apache)
```

- **Repository root = Apache document root.** You point the vhost at the repository root; Apache serves `assets/*` directly and rewrites every other request to `index.php`.
- **`index.php` is at the root.** One front controller. Same file for the public site, the registration flow, and the portal.
- **Sensitive folders are denied by `.htaccess`**: `src/`, `config/`, `data/`, `lang/`, `db/`, `bin/`, `storage/`, and every `.env`, `.sql`, `.log`, `.md` file.
- **No `dist/`, no build step, no upload.** Deploy = `git pull` + `php bin/migrate.php` (if migrations changed).
- **Portal on the same domain.** The portal mounts at `/{ADMIN_URL_PATH}` — obfuscated, set in `.env`, default `/panel-x9z`. Registration flow mounts at `/app/*` and requires no login. Both are served by the same `index.php`.

The `.htaccess` at the root performs four duties: serve `assets/*` directly, block the sensitive folders, block `.env`/`.sql`/`.log`/`.md`, and rewrite everything else to `index.php`.

---

## Architecture

```
Browser
  │
  ▼
Apache + .htaccess
  ├── /assets/*            → served directly
  ├── /src/*, /config/*,
  │   /data/*, /lang/*,
  │   /db/*, /bin/*,
  │   /storage/*           → 403 (denied)
  └── everything else      → index.php
        │
        ▼
src/App.php — bootstrap
  ├── Env::load('.env')
  ├── Session::start()
  ├── Lang detection (cookie → Accept-Language → env)
  ├── DB::connect(PDO MySQL 8)
  ├── Content::load('services')  → data/content/services/*.json
  ├── Variables::load()          → config/variables.php
  ├── Vocab::load()              → config/vocab.php
  └── Router::dispatch()
      ├── /                        → public site
      ├── /macro-works/{slug}/     → 5 macro pages
      ├── /services/{slug}/        → 130 service pages
      ├── /projects/{slug}/        → 25 project pages
      ├── /{ADMIN_URL_PATH}/*      → portal (auth required)
      ├── /app/*                   → registration + role dashboards
      └── /api/*                   → JSON APIs (rollups, eligibility)
```

**Two rendering modes, one engine.** Public pages and portal pages both use the same View engine, the same blocks, and the same layout. The only differences are which routes require authentication and which queries touch state tables.

**Rollups.** `bin/rollup.php` runs hourly (every 15 minutes on Fridays) and writes to `rollups_daily`. Public KPI tiles, `/api/kpis.json`, and every dashboard read only from `rollups_daily`. The raw `weekly_reports` table is never selected for a list view.

**No external dependencies at runtime.** No CDN. No jQuery. No icon font. No Tailwind at runtime. The portal and the public site share `attention.css` and `site.js`.

---

## Design system

One CSS file: `assets/_base/css/attention.css`. All templates import it. All templates may add additive rules in `assets/_base/css/templates/{name}.css`.

### Reading tiers — every section gets exactly one

| Tier | Code | Voice | Example |
|---|---|---|---|
| Easy | `E` | Short sentences, Darija‑friendly Arabic, warm | «شهادتك تنجم تولّي خدمة» |
| In‑between | `I` | Explains how it works: steps, numbers, dates | "4 certification levels, weekly reports due Fri 12:00" |
| Expert | `X` | Ontology, evidence levels, schemas | "L2 service, E1 evidence, sampled at 5%" |

### Focus levels — F1 to F5 (peak‑end rule)

| Focus | Purpose |
|---|---|
| `F1` | Background context |
| `F2` | Supporting detail |
| `F3` | Explaining section (warm) |
| `F4` | Secondary action |
| `F5` | **The** main action — max one per screen |

### Gradient tokens (Tunisian palette)

```css
--g-calm:  linear-gradient(135deg,#0E3B5C,#1B6F8A);  /* F1–F2 Sidi Bou Said blue */
--g-warm:  linear-gradient(135deg,#F7EEDD,#E9C37A);  /* F3 jasmine & sand */
--g-focus: linear-gradient(135deg,#1B6F8A,#2BB3A3);  /* F4 secondary */
--g-peak:  linear-gradient(135deg,#C8102E,#F05A28);  /* F5 primary CTA */
/* macro accents */
--g-cultures:   #B5532E → #E39B5B;
--g-built:      #5E6B3A → #B8A36A;
--g-sciences:   #2B2F7A → #5A6CF0;
--g-governance: #0F4D3A → #C9A227;
--g-technology: #0B7FA6 → #7B4DE0;
```

### Button classes

| Class | Focus | Use |
|---|---|---|
| `btn-peak` | F5 | The single main action (Register / Check eligibility) |
| `btn-focus` | F4 | Next step (Choose my service) |
| `btn-ghost` | F2 | Explore (Read more) |
| `btn-link` | F1 | Expert detail (See the schema) |
| `sticky-cta` | F5 | Mobile bottom bar after 40% scroll |

**Honest psychology only:**
- Social proof uses **real rollup numbers**.
- Deadlines come from **dated variables**, never fake scarcity.
- Every CTA carries its matching **declared limit** one click away.
- The linter enforces **one `btn-peak` per section** — no double call‑to‑action per screen.

---

## Pages

Every page is a route. Every route is a template + a content JSON + (optionally) a MySQL query. Section IDs are fixed — the linter checks that every section declared here is present in the rendered output.

### Homepage `/`

| # | Section ID | Block | Tier | Focus | Main CTA |
|---|---|---|---|---|---|
| 1 | `home-hero` | hero | E | F5 · g-peak | **Check my eligibility** → `/join/eligibility/` |
| 2 | `home-trust-strip` | kpis | E | F2 · g-calm | — |
| 3 | `home-problem` | prose | E | F3 · g-warm | Is this me? → `#home-fit` |
| 4 | `home-fit` | cards | E | F3 | Find my field → macro page |
| 5 | `home-pipeline` | **pipeline** | I | F3 | See the certification path → `/how-it-works/` |
| 6 | `home-macros` | carousel | I | F3 · macro gradients | Explore the 26 services → macro |
| 7 | `home-mid-cta` | cta | E | F5 · g-peak | **Register now** → `/app/register` |
| 8 | `home-gov-map` | **gov-map** | E | F3 | See my governorate → `/tn/{gov}/` |
| 9 | `home-stack` | stack | X | F1 | See the ontology → `/ontology/` |
| 10 | `home-projects` | cards | I | F2 | View project → project page |
| 11 | `home-founder` | quote | E | F2 · g-warm | Read declarations |
| 12 | `home-limits` | notice | I | F1 | Read our limits → `/trust/statement/` |
| 13 | `home-faq` | **faq** | E | F2 | Ask us → contact |
| 14 | `home-final` | cta | E | F5 · g-peak | **Start my registration** |
| — | `sticky` | **sticky-cta** | E | F5 | Check eligibility |

### Macro work landing pages `/macro-works/{slug}/` × 5

Slugs: `cultures`, `built`, `sciences`, `governance`, `technology`. Each uses its own macro gradient.

| # | Section ID | Block | Tier | Focus | Main CTA |
|---|---|---|---|---|---|
| 1 | `mw-hero` | hero | E | F5 · g-{macro} | **Find my service here** → `#mw-catalog` |
| 2 | `mw-why-tn` | prose | E | F3 | — |
| 3 | `mw-kpis` | kpis | I | F2 | — |
| 4 | `mw-diplomas` | table | E | F3 · g-warm | Check my diploma → eligibility |
| 5 | `mw-catalog` | service-catalog | I | F4 | Open service → service page |
| 6 | `mw-mid-cta` | cta | E | F5 · g-peak | **Register in {macro}** |
| 7 | `mw-projects` | cards | I | F2 | View project |
| 8 | `mw-stack` | stack (highlight L3) | X | F1 | — |
| 9 | `mw-leaders` | roster | I | F2 | Apply as leader → careers |
| 10 | `mw-faq` | faq | E | F2 | — |
| 11 | `mw-final` | cta | E | F5 · g-peak | **Join {macro} now** |

### Service pages `/services/{slug}/` × 130

| # | Section ID | Block | Tier | Focus | Main CTA |
|---|---|---|---|---|---|
| 1 | `svc-hero` | hero | E | F5 · g-{macro} | **Apply to this service** → `/app/register?service={slug}` |
| 2 | `svc-problem` | prose | E | F3 | — |
| 3 | `svc-fit` | **eligibility** | E | F4 · g-focus | Check in 30 seconds |
| 4 | `svc-path` | steps | I | F3 | See L1 tasks → `#svc-tasks` |
| 5 | `svc-tasks` | cards | X | F2 | — |
| 6 | `svc-week` | definitions | I | F2 | — |
| 7 | `svc-seats` | bars | I | F3 | — |
| 8 | `svc-mid-cta` | cta | E | F5 · g-peak | **Reserve my place in the L1 cohort** |
| 9 | `svc-delivery` | definitions | X | F1 | — |
| 10 | `svc-limits` | notice | I | F1 | — |
| 11 | `svc-projects` | cards | I | F2 | View project |
| 12 | `svc-related` | cards (generated) | I | F2 | Compare |
| 13 | `svc-faq` | faq | E | F2 | — |
| 14 | `svc-final` | cta | E | F5 · g-peak | **Apply now** |

### Project pages `/projects/{slug}/` × 25

| # | Section ID | Block | Tier | Focus | Main CTA |
|---|---|---|---|---|---|
| 1 | `prj-hero` | hero | E | F5 · g-peak | **Contribute to this project** |
| 2 | `prj-outcome` | prose | E | F3 | — |
| 3 | `prj-status` | register | X | F2 | Read evidence |
| 4 | `prj-meta` | definitions | X | F1 | — |
| 5 | `prj-services` | cards | I | F3 | Apply to a needed service |
| 6 | `prj-team` | bars | I | F3 | — |
| 7 | `prj-timeline` | timeline | I | F2 | — |
| 8 | `prj-split-cta` | cta (3 actions) | E | F5 / F4 / F4 | **Join as expert** / Become a client / Invest |
| 9 | `prj-limits` | notice | I | F1 | — |
| 10 | `prj-final` | cta | E | F5 | **Join the team** |

### Basic pages

| Page | URL | Main CTA |
|---|---|---|
| Join (main recruiting landing) | `/join/` | **Register now** |
| Eligibility | `/join/eligibility/` | **Continue to registration** |
| How it works | `/how-it-works/` | Start L1 |
| Country hub (Tunisia) | `/countries/tunisia/` | See my governorate |
| Governorate × 24 | `/tn/{gov}/` | Register from {gov} |
| Partners / Clients | `/partners/` | Book a scoping call |
| Investors | `/investors/` | Request the dossier |
| About | `/about/` | Meet the founder |
| Ontology | `/ontology/` | Read the expert docs |
| Careers | `/careers/` | Apply as manager |
| FAQ | `/faq/` | Ask a question |
| Contact | `/contact/` | Write to the right desk |
| Status | `/status/` | — |
| Trust | `/trust/*` | File a correction |
| Thank you | `/join/thanks/` | Share with a friend |
| 404 | `/404` | Back home |

---

## Owner dashboard

**Mount path:** `/{ADMIN_URL_PATH}` — obfuscated, set in `.env`, default `/panel-x9z`. Same domain as the public site.

**Global header:** country switcher (`All countries` · `🇹🇳 Tunisia` · `+ Add country`), language switch, quick search for a person by CIN, email, or ID. **Every menu filters by the selected country.**

| # | Menu | Key owner actions |
|---|---|---|
| 1 | **Command Center** | 30‑day burn‑down (target 130,000 vs actual), today's registrations, funnel, per‑macro heatmap, alerts |
| 2 | **Countries** | Add country wizard (ISO code, languages, currency, timezone, legal entity, data‑protection authority, salary model, calendar with Ramadan/Eid), activate/pause |
| 3 | **Regions & Hubs** | 24 governorates → delegations, universities, physical hubs, regional coordinators, per‑region quotas |
| 4 | **Recruitment Campaigns** | Waves (W1–W4), channels (Facebook, WhatsApp, universities, ANETI‑style offices), UTM tracking, daily quotas |
| 5 | **Applicants** | Pipeline board: Registered → Verified → Screened → Assigned → L1; filters by macro, service, region, degree |
| 6 | **Identity & Verification** | CIN checks, diploma upload review, duplicate detection, Dyon competency records |
| 7 | **Macro Works & Services** | Activate the 130 services per country, capacity per service, service leaders |
| 8 | **Academies & Certification** | Task sets L1–L4 per service, review queues, pass rates, certificate issuing |
| 9 | **Teams & Org** | Tree leader → managers → experts; enforces cap 12 experts per manager; orphan alerts |
| 10 | **Weekly Work Engine** | Directives (Mon 09:00), reports (Fri 12:00), approvals, lock (Fri 18:00), rollup (Sat 02:00), timed per country timezone |
| 11 | **Projects** | 25 projects, services consumed, staffing gaps, milestones |
| 12 | **Clients & Contracts** | Clients, scopes, quotes, invoices (local currency), licence (610.5 TND/h) |
| 13 | **Quality & Sampling** | RAG scores (red < 70, amber < 90), **5% sampling queue**, blockers past SLA |
| 14 | **Payroll & Finance** | Salary model (600 TND per certified post), payroll simulation per country, cash forecast |
| 15 | **Investors** | Investor list, parts, reports, dividends (modelled) |
| 16 | **Communications** | SMS, email, WhatsApp templates in ar/Darija/fr/en; segmented broadcasts; Ramadan‑aware send times |
| 17 | **Support & Tickets** | Applicant questions, blockers, SLA timers, macro answers |
| 18 | **Website & Content** | Pages, blocks, CTA variants, live content re‑render, cache purge |
| 19 | **Variables & Vocabularies** | Edit counts, money, time, doctrine values (derived ones read‑only) |
| 20 | **Users, Roles & Permissions** | 8 roles, 35 permissions, each scoped by country and region |
| 21 | **Reports & Analytics** | Rollups only: country, macro, service, governorate; cohort retention; CTA conversion per section ID |
| 22 | **Compliance & Privacy** | Consent records, retention, data‑subject requests, country law profile (Tunisia: Organic Law 2004‑63, INPDP) |
| 23 | **Audit Log & Corrections** | Every approval and edit with user and timestamp; public corrections with 14‑day SLA |
| 24 | **System** | DB shards per country, backups, queues, health, integrations (SMS, email), security headers |

**Role dashboards.** `leader`, `manager`, `member`, `investor`, `customer`, `user` each land on a scoped dashboard after login. They share the same sidebar shell but see only their permitted items. The sidebar is filtered by `ACL::can($role, $perm)` and by `Scope::canActOn($actor, $target)`.

---

## Quality & sampling

**Sampling rule: 5%.** Every week, 5% of submitted reports are randomly selected for re‑review by the leader or a delegate. The sampled 5% is re‑scored against the same rubric the manager used, and the pass rate is tracked per service and per manager.

- **Scope:** 5% of `weekly_reports` rows where `status = 'submitted'` for the current week.
- **Selection:** deterministic random sample per country, macro, and service, seeded by `(country_id, week_id)` so the sample is reproducible.
- **Reviewer:** the service leader, or a manager the leader delegates (no self‑review, no manager reviewing their own team's sample).
- **Outcome:** each sampled report is marked `sampled_pass` or `sampled_fail`. A `sampled_fail` triggers the same rework path as a manager rejection.
- **Owner dashboard:** the "Quality & Sampling" menu shows the sample size, pass rate, and rework rate per service.
- **Honesty:** the 5% is published on `/status/` and on each service page (`svc-seats` block) as part of the declared limits. It is a real number, not marketing.

**Why 5%:** it is large enough to detect a manager approving low‑quality work (a manager with 10 reports will, on average, have one sampled every two weeks) and small enough that 130,000 reports × 5% = 6,500 re‑reviews per week — ~1,300 per working day, spread across 130 leaders and 13,000 managers, i.e. under 1 sampled report per manager per week.

**No alternate rule exists.** The dossier's older "2% baseline vs 5% proposal" is superseded. This repository uses **5%**.

---

## Single source of truth

Many facts have historically been edited in more than one place. One change fixes all of them: **every fact is declared once, every derived figure is computed, and a linter fails if a fact is duplicated.**

| Problem | Fix |
|---|---|
| Three audience vocabularies | One `config/vocab.php`: `audience` (who reads) vs `role` (who logs in) |
| Three token syntaxes | One syntax: `{{ counts.services }}`. Anything else fails the linter |
| Broken keys (`money.salary_month` vs `salary_monthly`) | Lint unresolved tokens |
| Derived numbers typed by hand | `'derive' => 'experts_target * salary_monthly'` evaluated at render |
| Counts not computed from content | Count the files in `data/content/*` |
| Block lists disagree | Generate the vocabulary from `views/_blocks/*.php` |
| Template paths disagree | One path rule, documented once in `docs/templates.md` |
| Static vs dynamic contradiction | Both the public site and the portal are dynamic. No `dist/` exists |
| Service files repeat data | Store the number once in `macros/*.json`; build related cards automatically |
| Tunisia hard‑coded | `data/countries/tn.json`. Everything country‑specific reads from it |

---

## Data model

Single MySQL 8 database: `w5d`. Every table carries `country_id`.

### Content tables (mirror of `data/content/`)
Populated by `bin/sync-content.php` on deploy or on demand from the owner dashboard. Content JSON remains the source of truth; the tables exist for joins and search.

`pages`, `macros`, `services`, `projects`, `layers`, `audiences`, `certification_tasks`, `delivery_contracts`

### Platform tables
`countries`, `regions`, `users`, `roles`, `permissions`, `applicants`, `documents`, `verifications`, `services_country`, `cohorts`, `cert_submissions`, `teams`, `team_members`, `directives`, `weekly_reports`, `approvals`, `clients`, `contracts`, `invoices`, `payroll_runs`, `campaigns`, `messages`, `tickets`, `audit_log`, `consents`, `rollups_daily`

### Scale & indexing
- `applicants` — ~150,000 rows; indexes on `(country_id, status)`, `(country_id, macro_slug, status)`, `(country_id, region_id, status)`, and a unique index on `cin` per country.
- `weekly_reports` — ~7,000,000 rows per year; partitioned by `country_id` and `YEAR(created_at)`.
- `rollups_daily` — every dashboard and `/api/kpis.json` reads only from this table.

### Migration discipline
One migration file per schema change under `db/migrations/`. Applied in order by `bin/migrate.php`. No `ALTER TABLE` outside a migration.

---

## Requirements

- **PHP ≥ 8.2** with `pdo_mysql`, `mbstring`, `json`, `session`, `openssl`, `curl`
- **MySQL ≥ 8.0** (InnoDB, `utf8mb4_unicode_ci`)
- **Apache 2.4+** with `mod_rewrite` (or Nginx equivalent)
- **No Composer at runtime**
- **Zero third‑party runtime requests** (no CDN, no jQuery, no icon font)

---

## Installation

```bash
git clone https://github.com/W5Downer/w5d-php-cms.git
cd w5d-php-cms

# 1. MySQL
mysql -u root -e "CREATE DATABASE w5d CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -e "CREATE USER 'w5d'@'localhost' IDENTIFIED BY '***';"
mysql -u root -e "GRANT ALL PRIVILEGES ON w5d.* TO 'w5d'@'localhost';"

# 2. Configure
cp .env.example .env
# Edit .env with DB credentials, APP_URL, ADMIN_URL_PATH, webmaster credentials

# 3. Migrate + seed
php bin/migrate.php
php bin/sync-content.php

# 4. Generate the webmaster password hash
php -r "echo password_hash('YourStrongPassword', PASSWORD_DEFAULT), PHP_EOL;"
# Paste into .env → WEBMASTER_PASSWORD_HASH

# 5. Point Apache document root at the repository root
#    (the same folder that contains index.php)
```

**Cron:**

```cron
*/15 * * * 5  php /path/to/w5d/bin/rollup.php
0    * * * *  php /path/to/w5d/bin/rollup.php
0    3 * * *  php /path/to/w5d/bin/backup.php
```

**First login:** `https://w5d.tech/{ADMIN_URL_PATH}` — sign in as `WEBMASTER_USERNAME` / `WEBMASTER_PASSWORD_HASH`.

---

## Security

- CSRF token on every state‑changing form
- Session fixation protection — `session_regenerate_id(true)` on login and logout
- HttpOnly, SameSite=Lax, Secure in production
- Argon2id password hashing
- Prepared statements everywhere (PDO)
- Rate limiting on login and registration (per IP, per CIN)
- OAuth state validated against `$_SESSION['oauth_state']` (Google + GitHub only)
- Path traversal prevention in the router
- `.htaccess` denies `src/`, `config/`, `data/`, `lang/`, `db/`, `bin/`, `storage/`, and every `.env`, `.sql`, `.log`, `.md` file
- HTTP headers: `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`
- Append‑only `audit_log` for every approval and every scope‑crossing action
- Nightly `mysqldump` + off‑server copy
- Privacy: personal data on ~150,000 people — Tunisia Organic Law 2004‑63, INPDP (confirm with counsel)

---

## Directory structure

```
w5d-php-cms/                    ← Apache document root
├── .env                        (denied)
├── .env.example
├── .htaccess                   ← serves assets/, blocks sensitive folders, rewrites
├── index.php                   ← single entry point
│
├── assets/                     ← public, served directly
│   ├── _base/
│   │   ├── css/                ← attention.css, templates/{name}.css
│   │   └── js/                 ← site.js (zero dependencies)
│   └── img/
│
├── src/                        (denied)
│   ├── App.php
│   ├── Env.php
│   ├── DB.php
│   ├── Lang.php
│   ├── View.php
│   ├── Router.php
│   ├── ACL.php
│   ├── Auth.php
│   ├── Auth/OAuth.php
│   ├── Scope.php
│   ├── Content.php
│   ├── Variables.php
│   ├── Vocab.php
│   ├── Country.php
│   ├── Rollup.php
│   ├── Lint.php
│   ├── controllers/
│   └── models/
│
├── views/                      (denied)
│   ├── _blocks/
│   ├── _shared/
│   │   ├── layouts/
│   │   └── partials/
│   └── templates/
│       ├── ledger/
│       ├── atlas/
│       └── folio/
│
├── data/                       (denied — KEPT FROM REPO)
│   ├── content/
│   │   ├── pages/
│   │   ├── macros/
│   │   ├── services/
│   │   ├── projects/
│   │   ├── layers/
│   │   ├── audiences/
│   │   ├── certification/
│   │   └── delivery/
│   └── countries/
│       └── tn.json
│
├── config/                     (denied)
│   ├── app.php
│   ├── variables.php
│   └── vocab.php
│
├── lang/                       (denied)
│   ├── en.php
│   ├── fr.php
│   └── ar.php
│
├── db/                         (denied)
│   └── migrations/
│
├── bin/                        (denied)
│   ├── migrate.php
│   ├── sync-content.php
│   ├── rollup.php
│   ├── backup.php
│   └── lint.php
│
├── docs/                       (denied)
│   ├── ontology-six-layers.md
│   ├── templates.md
│   └── design.md
│
└── storage/                    (denied)
    ├── cache/views/
    ├── logs/
    └── uploads/
```

---

## Templates

Three skins ship today. Each template lives in `views/templates/{name}/blocks/` (overrides, optional) and `assets/_base/css/templates/{name}.css` (additive rules only). Blocks fall back to `views/_blocks/` when a template does not override them.

| Template | Feel |
|---|---|
| `ledger` | Editorial / brass |
| `atlas` | Cartographic / cyan |
| `folio` | Paper / oxblood |

Switching is a single config value in `config/app.php`. Adding a template: create the two folders, add the additive CSS, reload — no content files change.

---

## Languages

Content is `{en, fr, ar}` maps inside JSON. UI chrome lives only in `lang/{code}.php`. Arabic is RTL with its own font stack and `assets/_base/css/rtl.css`.

Switching is cookie‑based. No `?lang=xy` URLs.

---

## License

See [LICENSE](LICENSE) for the full text.

**Summary:**

- **W5D PHP CMS** is licensed at **610.5 TND per hour of work**.
- Promotional offer valid **before 2027** (strictly before 31 December 2026).
- **Sampling rule: 5%** of weekly reports are re‑reviewed each week. This is an operating doctrine of the software, not an optional setting.
- **Honesty constraint:** "130,000 experts" is a design equation, not a claim of employment.

Every rendered page carries the license footer:

```html
<p>W5D Admin Dashboard licensed at 610.5 TND/hour of work.
   Promotional offer valid before 2027.</p>
```

---

## AI Agent Prompt

> **Copy this block verbatim into an AI coding assistant's system prompt.**
>
> **Why this prompt is different:** the previous prompt was a 23‑section plan. Agents loop on plans. This prompt is **18 atomic, sequential tasks** across **4 phases**, each ending in a PASS/FAIL check. Do not skip. Do not start Phase N+1 until Phase N passes.

```text
================================================================================
PROJECT: w5d-php-cms
ROLE: Senior PHP 8.2 engineer working on a dynamic CMS.
STYLE: Sequential tasks. One at a time. Every task has a PASS/FAIL check.
       Do not loop. Do not skip. Do not redesign. Do not add features.

ARCHITECTURE (fixed, do not change)
  Runtime:    PHP 8.2, MySQL 8 (InnoDB, utf8mb4_unicode_ci)
  Deployment: SINGLE DOMAIN — index.php at the repository root.
              Apache document root = repository root.
              assets/* served directly by Apache.
              src/, config/, data/, lang/, db/, bin/, storage/, docs/
              and every .env, .sql, .log, .md file are denied by .htaccess.
              All other requests rewrite to /index.php.
  Content:    JSON under data/content/ (KEPT — do not delete, do not rename)
  State:      MySQL tables (users, applicants, teams, weekly_reports, ...)
  Render:     Dynamic at request time — no static build, no dist/,
              no bin/build.php
  Portal:     /{ADMIN_URL_PATH} + /app/* — SAME domain as public site
  Templates:  ledger, atlas, folio — blocks fall back to views/_blocks/
  Sampling:   5% of weekly_reports, fixed. No alternate rule exists.
  Constraint: zero third-party runtime requests
              (no CDN, no jQuery, no icon font)

READ FIRST — the three rules
  1. One fact, one place. Duplicates fail the linter.
  2. Any page renders in any template. Blocks resolve two-tier:
     views/{ACTIVE_TEMPLATE}/blocks/{type}.php → views/_blocks/{type}.php
  3. Every number is a token {{ counts.services }}. Unresolved tokens
     fail the linter.

STOP CONDITIONS
  - Any PASS/FAIL reports FAIL: stop and report.
  - Any test requires deleting a file under data/content/: stop and report.
  - Any task requires a Composer dependency: stop and report.
  - Any task requires a CDN or jQuery: stop and report.
  - Any task tries to introduce a public/ folder or a dist/ folder:
    stop and report.

================================================================================
PHASE A — SINGLE SOURCE OF TRUTH (7 tasks)
================================================================================

TASK A1 — Baseline
  Run:      php bin/migrate.php && php bin/lint.php
  Check:    migration applies cleanly; lint reports zero errors.
  PASS if:  both exit 0.
  FAIL if:  any error — stop and report.
  Do not edit files in A1.

TASK A2 — Vocabularies
  Create config/vocab.php containing:
    audience (owner|admin|leader|member|investor|customer|user),
    role (8 values matching audiences),
    status (registered|verified|screened|assigned|l1|l2|l3|l4|certified),
    evidence (E0|E1|E2|E3),
    block (hero|kpis|cards|bars|carousel|contact-desks|cta|definitions|
           form|stack|prose|table|steps|notice|quote|register|timeline|
           roster|service-catalog|pipeline|eligibility|faq|gov-map|
           sticky-cta|progress|country-switch),
    ontology_layer (L0|L1|L2|L3|L4|L5),
    meta_solution (list — read from data/content/layers/meta-solutions.json).
  Remove duplicated arrays from config/variables.php.
  PASS if: grep -r shows each vocabulary defined exactly once.
  FAIL if: any vocabulary appears in more than one file.

TASK A3 — Token syntax
  Standardize on {{ path.to.var }} (e.g. {{ counts.services }}).
  Migrate every JSON under data/content/ to this syntax.
  Remove any {{ var.* }} prefix.
  Fix money.salary_month → money.salary_monthly (or vice versa — pick one,
  update both config/variables.php and every JSON referencing it).
  Fix license_footer to use an existing token for the deadline year.
  Extend bin/lint.php to fail on any unresolved token.
  PASS if: php bin/lint.php exits 0.
  FAIL if: any token is unresolved, or any JSON still uses {{ var.* }}.

TASK A4 — Derived variables
  Extend config/variables.php with 'derive' expressions for:
    accounts_total   = experts_target + managers_target + leaders_target + 1
    reports_per_year = experts_target * 52
    payroll_monthly  = experts_target * money.salary_monthly
    part_monthly     = money.part_day * 30
    managers_target  = leaders_target * 100
  Compute counts.services, counts.projects, counts.macros from the files
  under data/content/ at render (Content::count).
  PASS if: changing experts_target in config/variables.php updates every
           derived figure on the next request (test: change to 1000, load
           /, confirm all figures scale, revert).
  FAIL if: any figure is hard-coded in a JSON file or in a block.

TASK A5 — Block vocabulary and paths
  Generate the block vocabulary from views/_blocks/*.php (one file per
  block type). Remove 'sparkline' if it has no renderer; implement it if
  it is used.
  Document the template path rule ONCE in docs/templates.md:
    Template blocks: views/templates/{name}/blocks/{type}.php
    Template CSS:    assets/_base/css/templates/{name}.css
    Fallback block:  views/_blocks/{type}.php
  Update config/app.php to follow this rule.
  PASS if: config/vocab.php 'block' list matches ls views/_blocks/*.php
           exactly (same count, same names).
  FAIL if: any block file has no vocabulary entry, or vice versa.

TASK A6 — Service JSON cleanup
  For each file under data/content/services/*.json:
    - Remove the repeated service number (store it once in macros/*.json
      as the label source).
    - Remove macro name translations (they belong in macros/*.json).
    - Remove "Other services" cards that copy sibling titles — related
      cards are generated at render from macro membership.
    - Add fr and ar fields where missing (placeholders copied from en
      with a TODO note).
  Extend bin/lint.php to report:
    - duplicated literals across services,
    - missing fr/ar fields,
    - services whose macro_slug does not exist in macros/*.json.
  PASS if: php bin/lint.php exits 0.
  FAIL if: any service has an unresolved macro_slug or any field is
           empty in en.

TASK A7 — Country entity
  Create data/countries/tn.json with:
    iso, names {en, fr, ar}, currency TND, timezone Africa/Tunis,
    locale ar_TN, regions (24 governorates), legal_profile
    (Organic Law 2004-63, INPDP), salary_model ref, calendar
    (Ramadan, Eid), default_languages [ar, fr, en].
  Remove every Tunisia-specific literal from config/, src/, and
  views/_shared/. Replace each with a read from Country::get('tn').
  PASS if: grep for "Tunisia", "TND", "Africa/Tunis", "ar_TN" outside
           data/countries/tn.json returns zero hits in config/, src/,
           views/_shared/.
  FAIL if: any Tunisia literal remains outside data/countries/tn.json.

================================================================================
PHASE B — DESIGN SYSTEM & PAGES (5 tasks)
================================================================================

TASK B1 — attention.css
  Create assets/_base/css/attention.css with:
    --g-calm, --g-warm, --g-focus, --g-peak (Tunisian palette),
    --g-cultures, --g-built, --g-sciences, --g-governance, --g-technology,
    reading-tier classes (.tier-e, .tier-i, .tier-x),
    focus classes (.focus-1 .. .focus-5),
    button classes (.btn-peak, .btn-focus, .btn-ghost, .btn-link,
                    .sticky-cta).
  Extend bin/lint.php to fail if any rendered page contains more than one
  .btn-peak inside a single <section>.
  Verify contrast for all gradient tokens: each block with text on a
  gradient must have a scrim or darker stops, achieving >= 4.5:1. Add the
  proof (computed ratios) to docs/design.md.
  PASS if: lint enforces one .btn-peak per section; docs/design.md has
           contrast proof for every gradient token.
  FAIL if: any gradient fails contrast without a scrim.

TASK B2 — Block signature
  Every block under views/_blocks/ accepts these props via $data:
    id (string, required),
    tier ('E'|'I'|'X', required),
    focus (1..5, required),
    gradient (string|null),
    cta (array|null — {label{en,fr,ar}, href, class, psychology}).
  Validate in the block loader (src/View.php): a block that renders
  without tier or focus throws.
  PASS if: rendering any page fails loudly if tier or focus is missing.
  FAIL if: a block renders with missing tier or focus.

TASK B3 — New blocks
  Create these blocks under views/_blocks/:
    pipeline.php       — Register -> Verify -> Certify (L1-L4) -> Deliver
    eligibility.php    — 3-question check (degree, field, availability)
    faq.php            — accordion, no JS dependencies
    gov-map.php        — inline SVG of Tunisia (24 governorates), no
                         external tiles, no Leaflet
    sticky-cta.php     — mobile bottom bar after 40% scroll
    progress.php       — open progress bar (Zeigarnik)
    country-switch.php — country selector in the portal header
  PASS if: php bin/lint.php exits 0 and each block appears in
           config/vocab.php.
  FAIL if: any of these blocks references an external URL.

TASK B4 — Pages
  Implement routes exactly as specified in the README section Pages, using
  the section IDs listed there. For each route:
    - create the controller in src/controllers/,
    - render the template + JSON content,
    - wire CTAs to /app/register (public) and portal routes.
  Required routes:
    / , /macro-works/{slug}/ x 5, /services/{slug}/ x 130,
    /projects/{slug}/ x 25,
    /join/ , /join/eligibility/ , /join/thanks/ ,
    /how-it-works/ , /countries/tunisia/ , /tn/{gov}/ x 24,
    /partners/ , /investors/ , /about/ , /ontology/ , /careers/ ,
    /faq/ , /contact/ , /status/ , /trust/* , /404
  PASS if: every route returns HTTP 200 in ledger, atlas, and folio
           templates, in en, fr, and ar (3 x 3 x route count all 200).
  FAIL if: any route returns non-200 in any template or language.

TASK B5 — Honesty constraints
  - No countdown or scarcity claim unless backed by a dated variable
    from config/variables.php or a value from rollups_daily.
  - Every page renders the limits notice (home-limits / svc-limits /
    prj-limits).
  - Every CTA to /app/register carries its declared limit one click away.
  PASS if: grep finds no hard-coded "2027" or "130,000" outside
           config/variables.php and data/countries/tn.json.
  FAIL if: any page hard-codes a number that should be a token.

================================================================================
PHASE C — PORTAL (5 tasks)
================================================================================

TASK C1 — MySQL schema
  Create db/migrations/001_initial.sql with:
    countries, regions, users, roles, permissions, applicants, documents,
    verifications, services_country, cohorts, cert_submissions, teams,
    team_members, directives, weekly_reports, approvals, clients,
    contracts, invoices, payroll_runs, campaigns, messages, tickets,
    audit_log, consents, rollups_daily.
  Every table carries country_id (FK to countries.id).
  Indexes:
    applicants: (country_id, status), (country_id, macro_slug, status),
                (country_id, region_id, status), unique(country_id, cin)
    weekly_reports: PARTITION BY country_id + YEAR(created_at),
                    (country_id, week_id, status),
                    (country_id, manager_id, week_id)
    rollups_daily: (country_id, day), (country_id, macro_slug, day)
  PASS if: php bin/migrate.php applies 001 cleanly on an empty MySQL 8.
  FAIL if: migration errors, or any table lacks country_id.

TASK C2 — Registration
  Build /app/register:
    - 4-minute flow: eligibility -> account -> CIN + diploma upload ->
      service choice
    - ar/fr/en, mobile-first
    - OTP by SMS (interface only — stub adapter)
    - duplicate CIN guard (unique index)
    - consent capture written to consents
    - rate limited 5/hour/IP
  Load target: 5,000 registrations/day; p95 < 500 ms (test with
  wrk/ab on the /app/register POST path, 100 concurrent, 10 minutes).
  PASS if: load test reaches 5,000 registrations/day with p95 < 500 ms,
           and every CIN duplicate is rejected with HTTP 409.
  FAIL if: p95 > 500 ms, or a duplicate CIN is accepted.

TASK C3 — Owner dashboard
  Implement the 24 menus from README section Owner dashboard at
  /{ADMIN_URL_PATH}/. Each menu:
    - is scoped by the currently selected country,
    - calls Auth::require($perm) and Scope::canActOn($actor, $row)
      on every action,
    - reads list data only from rollups_daily or from indexed,
      paginated, filtered MySQL queries,
    - paginates at 50 rows/page.
  Sidebar items are filtered by ACL::can($role, $perm).
  The "Quality & Sampling" menu shows the 5% rule and its current sample
  size and pass rate — 5% is fixed and cannot be changed from the UI.
  PASS if: the sidebar shows only permitted items for each of the 8 roles;
           no menu loads more than 50 rows; changing the country switcher
           filters every menu; the Quality & Sampling menu displays the
           fixed 5% rule.
  FAIL if: any menu renders > 50 rows in a single response, or a role
           sees a menu item it cannot access, or the UI allows changing
           the sampling rate away from 5%.

TASK C4 — Country wizard
  Implement /{ADMIN_URL_PATH}/countries/new:
    - writes data/countries/{iso}.json,
    - inserts a countries row,
    - clones Tunisia defaults (roles, permissions, 130 services as
      inactive, salary model, calendar, 5% sampling rule).
  PASS if: adding a test country 'ma' works end-to-end (wizard completes,
           data/countries/ma.json appears, the country switcher lists it,
           every menu filters to it), and deleting it removes the row and
           the file.
  FAIL if: any menu leaks data from another country when 'ma' is selected.

TASK C5 — Nightly rollup
  Implement bin/rollup.php to aggregate weekly_reports -> rollups_daily.
  Cron: */15 * * * 5 (Friday) and 0 * * * * (otherwise).
  Expose /api/kpis.json (served from rollups_daily).
  PASS if: after a synthetic load of 10,000 weekly_reports, rollups_daily
           has the correct aggregates and /api/kpis.json returns them in
           < 50 ms.
  FAIL if: /api/kpis.json queries weekly_reports directly.

================================================================================
PHASE D — VERIFY & REPORT (1 task)
================================================================================

TASK D1 — Final verification
  Run all of the following:
    - php bin/migrate.php (clean DB)
    - php bin/sync-content.php
    - php bin/lint.php
    - A browser pass on: /, /macro-works/technology/,
      /services/heritage-digitisation/, /projects/meta-x-academy/,
      /join/, /join/eligibility/, /tn/tunis/, /app/register,
      /{ADMIN_URL_PATH}/, /{ADMIN_URL_PATH}/applicants
    - All 3 templates x 3 languages on /, /macro-works/technology/,
      /services/heritage-digitisation/
    - Load test on /app/register POST (p95 < 500 ms)
    - Security: no third-party requests in any HTML; license footer on
      every page; .htaccess denies src/, config/, data/, lang/, db/,
      bin/, storage/, docs/, .env, .sql, .log, .md; the physical
      folders are not reachable directly.
    - Confirm index.php lives at the repository root (no public/ folder).
  Produce a final report:
    Files created      : (list)
    Files modified     : (list)
    Files deleted      : (must be empty — data/content/ must be intact)
    Migrations applied : (list)
    Lint result        : PASS/FAIL
    Load p95           : (ms)
    Third-party check  : PASS/FAIL
    License footer     : PASS/FAIL
    Sampling rule      : 5% (confirmed fixed)
    Open items         : (list — nothing in Phase A-C should be open)
  PASS if: every line above reports PASS.
  FAIL if: any line reports FAIL — stop and report the details.
  STOP after Phase D. Do not start new features.

================================================================================
COMMON LOOP TRIGGERS — do not do these
================================================================================
  x Do not create a public/ folder. index.php is at the repository root.
  x Do not bring back a static build (bin/build.php, dist/).
  x Do not switch to SQLite.
  x Do not add TailAdmin, Bootstrap, or any framework.
  x Do not rename a service, a macro, or a project.
  x Do not create all 130 certification files or all 25 project state
    rows — that is a follow-up prompt.
  x Do not modify data/content/ in Phase B or C.
  x Do not re-read the whole README more than once per phase.
  x Do not change the 5% sampling rule. It is fixed.
  x Do not split the site onto app.w5d.tech. One domain, one index.php.
================================================================================
```

---

## Author

**Maher Kaddoussi** — Founder and owner, W5D
📧 [owner@w5d.tech](mailto:owner@w5d.tech) · 🌐 https://w5d.tech · 💻 https://github.com/W5Downer/w5d-php-cms
