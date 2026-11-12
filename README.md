# W5D PHP CMS

**A JSON-driven static site generator for the W5D Team website — five macro works, 130 services, six ontology layers, multi-template, three languages.**

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Build](https://img.shields.io/badge/build-bin%2Fbuild.php-0A5EFF)](#build)
[![Templates](https://img.shields.io/badge/templates-ledger%20%C2%B7%20atlas%20%C2%B7%20folio-0A5EFF)](#templates)
[![Languages](https://img.shields.io/badge/languages-en%20%C2%B7%20fr%20%C2%B7%20ar-0A5EFF)](#languages)
[![License](https://img.shields.io/badge/License-610.5%20TND%2Fh%20(before%202027)-0A5EFF)](#license)

> **W5D** — *World Five Dimensions* — is a company and a working ontology operating across five macro works: **Cultures/Humanities**, **Built Environment**, **Sciences**, **Governance**, and **Technology**. This repository turns that ontology into a **static site** that the owner compiles and uploads.

---

## Table of Contents

- [What this repository is](#what-this-repository-is)
- [The owner's strategy (130,000 experts)](#the-owners-strategy-130000-experts)
- [Audience types and meta websites](#audience-types-and-meta-websites)
- [Architecture](#architecture)
- [The six-layer ontology](#the-six-layer-ontology)
- [Build](#build)
- [Templates](#templates)
- [Languages](#languages)
- [Data model](#data-model)
- [Directory structure](#directory-structure)
- [Requirements](#requirements)
- [Installation](#installation)
- [License](#license)
- [AI Agent Prompt](#ai-agent-prompt)
- [Author](#author)

---

## What this repository is

W5D PHP CMS is **a static site generator**, not a runtime CMS. You edit JSON and PHP, run `php bin/build.php`, and upload the `dist/` folder to the root of `https://w5d.tech`. There is no database on the host, no installer, no SSH command, and no PHP running at the host.[reference:2]

Three rules the engine enforces:

1. **One page = one JSON file.** Every page, service, macro, project, declaration, and ontology layer lives in its own file under `data/content/`. No two pages share a data source, so no two pages can contradict each other.
2. **Any page renders in any installed template.** Blocks resolve two-tier: `views/{ACTIVE_TEMPLATE}/blocks/{type}.php` → `views/_blocks/{type}.php`. A template restyles blocks; it never restates content.
3. **Every number is a PHP variable.** `config/variables.php` holds counts, capacity ratios, money figures, and doctrine thresholds. Page JSON refers to them as `{{ counts.services.value }}`. Unknown types throw at load — a typo cannot publish.[reference:3]

A clean build today is **639 files across 3 languages and 3 templates**, zero render errors.

---

## The owner's strategy (130,000 experts)

The owner runs a **single dynamic strategy**: turn 130,000 unemployed Tunisian higher-education graduates into certified W5D team members, then use those teams to solve problems inside the five macro works. The generator models this strategy as a **four-stage pipeline**, each stage a JSON collection the owner edits.

### Stage 1 — Ontology (what needs solving)
- **5 macro works** — `data/content/macros/*.json`
- **130 services** — 26 per macro work, `data/content/services/*.json`
- **Meta solutions** — `data/content/layers/meta-solutions.json`
- **Projects** — 25 records in `data/content/projects/*.json`, each carrying `meta_solution` and `services_consumed`[reference:4]

### Stage 2 — Certification (how a member becomes an expert)
Every service declares a **certification task set** (`data/content/certification/{service_slug}.json`). A task is:

```json
{
  "id": "heritage-digitisation-L2-T3",
  "service_slug": "heritage-digitisation",
  "level": 2,
  "title": { "en": "...", "fr": "...", "ar": "..." },
  "instructions": { "en": "...", "fr": "...", "ar": "..." },
  "evidence": "signed PDF + metadata sheet",
  "pass_rule": "all fields present; 0 schema errors"
}
```

A member subscribes → completes the task set → the owner (or a delegate) marks each task `approved` → the member becomes a **certified expert team member** for that service.

### Stage 3 — Delivery (what the certified team produces)
Each certified service carries a **delivery contract** (`data/content/delivery/{service_slug}.json`): deliverable unit, weekly target, evidence rule. This is what the team actually ships once certified.

### Stage 4 — Reporting (how the owner sees it)
Every delivery row becomes a **rollup** the owner reads on `/ecosystem/`. No page loads the raw 130,000 rows — the generator only publishes aggregates.

> **Honesty constraint (kept from the ontology spec):** 130,000 is a design equation. The real footprint today is 26 developers in Tunisia. “Live” never means “1,000 experts employed”. See `docs/ontology-six-layers.md`.[reference:5]

---

## Audience types and meta websites

The owner manages **audience types** as a vocabulary in `config/audiences.php`. Each audience is a first-class record with its own meta website under `views/templates/{template}` + `data/content/audiences/{slug}.json`.

| Audience | Default template | Meta website purpose |
|---|---|---|
| `owner` | `ledger` | Internal strategy and status register |
| `admin` | `ledger` | Operations and content admin |
| `leader` | `ledger` | Service scorecards and team view |
| `member` | `atlas` | Certification path and weekly work |
| `investor` | `folio` | Portfolio, ROI, dividends |
| `customer` | `atlas` | Services, invoices, support |
| `user` | `folio` | Profile and notifications |

**Two things the owner controls:**

1. **Which template an audience uses.** Set `template` in the audience record. The build renders the audience's meta site in that template only.
2. **Which audiences exist.** Add a new record to `config/audiences.php` and a matching `data/content/audiences/{slug}.json` — a new meta website appears at build time.

This is why the repository ships multiple templates: **each audience can be exposed under its own skin**, and a single audience can be re-skinned by changing one field.

---

## Architecture

```
data/content/           JSON — one file per page, service, macro, project, audience
config/variables.php    Every number the site publishes
config/audiences.php    Audience registry
views/_blocks/          Block implementations (hero, cards, carousel, form, ...)
views/_shared/          Layouts and partials shared across templates
views/templates/{name}  Additive CSS only — never restates content
assets/_base/           Base CSS + JS (zero third-party requests)
lang/{en,fr,ar}.php     UI chrome only
bin/build.php           Compiles everything into dist/
dist/                   The folder you upload to w5d.tech
```

**Block resolution is two-tier:** `views/{ACTIVE_TEMPLATE}/blocks/{type}.php` → `views/_blocks/{type}.php`. A template restyles; it never restates.

**Zero third-party requests.** No CDN, no jQuery, no icon font. The carousel, theme switch, and language switcher are dependency-free in `assets/_base/js/site.js`.[reference:6]

---

## The six-layer ontology

The site models the W5D structure as a **stacked ontology**, not a flat list:

| Layer | Content | Page |
|---|---|---|
| **L5 Projects** | `data/content/projects/*.json` (25) | `/ecosystem/` |
| **L4 Meta Solutions** | `data/content/layers/meta-solutions.json` | `/ontology/meta-solutions/` |
| **L3 Macro Works** | `data/content/macros/*.json` (5) | `/macro-works/` |
| **L2 Services** | `data/content/services/*.json` (130) | `/services/` |
| **L1 Expert Teams** | `data/content/layers/expert-teams.json` | `/ontology/expert-teams/` |
| **L0 Academies** | `data/content/layers/academies.json` | `/ontology/academies/` |

The full diagram is one block (`stack`) rendered by one file — every page draws the same stack from the same source.

---

## Build

```bash
php bin/build.php                              # → dist/ (default template, 3 languages)
php bin/build.php --template=atlas             # → dist/ (atlas template)
php bin/build.php --template=folio             # → dist/ (folio template)
php bin/build.php --out=/tmp/site              # → anywhere else
```

Upload the contents of `dist/` to the root of `https://w5d.tech`. There is no installer, no database, no PHP at the host.[reference:7]

---

## Templates

Three skins ship today. Each adds **only additive CSS** over `assets/_base/css/base.css` and may override any block or partial.

| Template | Feel | Overrides allowed |
|---|---|---|
| `ledger` | Editorial / brass | Any block, any partial |
| `atlas` | Cartographic / cyan | Any block, any partial |
| `folio` | Paper / oxblood | Any block, any partial |

**Switching** is `--template=` at build time, or `active_template` in `config/app.php`.

**Adding a template** (see also AI Agent Task 4):
1. Create `views/templates/{name}/blocks/` (empty is fine — falls back to `_blocks/`).
2. Create `assets/_base/css/templates/{name}.css` with additive rules only.
3. Build with `--template={name}` and verify the site renders.

**`views/templates/cda-w5d-template/`** — this folder contains an HTML template (`index.html` + `assets/`) that has **not yet been exposed** into the block system. Exposing it is AI Agent Task 3.

---

## Languages

Content is `{en, fr, ar}` maps inside JSON. UI chrome lives only in `lang/{code}.php`. Arabic is RTL with its own font stack and `assets/_base/css/rtl.css` equivalent.[reference:8]

Language switching is cookie-based. No `?lang=xy` URLs.

---

## Data model

Every content record is a JSON file with a `type` field validated at load. Unknown values throw.

**Page types:** `home`, `about`, `service`, `macro`, `project`, `ontology`, `audience`
**Block types:** `hero`, `cards`, `bars`, `carousel`, `contact-desks`, `cta`, `definitions`, `form`, `stack`

**Variables** live in `config/variables.php` and are referenced as `{{ counts.services.value }}` or `{{ money.salary_month }}`. Numbers never appear in prose.

---

## Directory structure

```
w5d-php-cms/
├── bin/
│   └── build.php                       # the static build
├── config/
│   ├── app.php                         # active_template, base URL
│   ├── variables.php                   # every number the site publishes
│   └── audiences.php                   # audience registry
├── data/
│   └── content/
│       ├── pages/                      # home, about, ...
│       ├── macros/                     # 5 files
│       ├── services/                   # 130 files
│       ├── projects/                   # 25 files
│       ├── layers/                     # meta-solutions, expert-teams, academies
│       ├── audiences/                  # one JSON per audience
│       ├── certification/              # one JSON per service (task set)
│       └── delivery/                   # one JSON per service (contract)
├── views/
│   ├── _blocks/                        # hero, cards, carousel, form, ...
│   ├── _shared/
│   │   ├── layouts/                    # app.php, home.php
│   │   └── partials/                   # head, header, footer, band, subnav, ...
│   └── templates/
│       ├── ledger/                     # additive CSS only
│       ├── atlas/
│       ├── folio/
│       └── cda-w5d-template/           # NOT YET EXPOSED — see Task 3
├── assets/
│   └── _base/
│       ├── css/                        # base.css, rtl.css, templates/
│       └── js/                         # site.js (zero dependencies)
├── lang/
│   ├── en.php
│   ├── fr.php
│   └── ar.php
├── docs/
│   ├── ontology-six-layers.md
│   ├── page-data.md
│   ├── variables.md
│   ├── developer-guide.md
│   ├── deployment.md
│   └── templates.md
└── dist/                               # ← upload this to w5d.tech
```

---

## Requirements

- **PHP ≥ 8.2** — no extensions beyond the defaults
- **No Composer**, **no Node.js at build time**, **no database**
- **Zero third-party runtime requests** (no CDN, no jQuery, no icon font)

---

## Installation

```bash
git clone https://github.com/W5Downer/w5d-php-cms.git
cd w5d-php-cms
php bin/build.php
# verify: ls dist/ → HTML files for 3 languages × 3 templates
```

There is no `.env`, no SQLite, no admin server. The owner's controls live in `config/` and `data/content/`.

---

## License

**W5D PHP CMS — licensed at 610.5 TND per hour of work.**

Promotional offer valid **before 2027** (strictly before 31 December 2026). After that date, the standard rate applies.

Every rendered page must carry the license footer:

```html
<p>W5D Admin Dashboard licensed at 610.5 TND/hour of work.
   Promotional offer valid before 2027.</p>
```

---

## AI Agent Prompt

> **Copy this block verbatim into an AI coding assistant's system prompt.**
>
> **Why this prompt is different:** the previous prompt was a 23-section plan. Agents loop on plans. This prompt is **12 atomic, sequential tasks**, each ending in a PASS/FAIL check. Do not skip a task. Do not start task N+1 until task N passes.

```text
================================================================================
PROJECT: w5d-php-cms
ROLE: Senior PHP engineer working on a JSON-driven static site generator.
STYLE: Sequential task list. One task at a time. Every task has a PASS/FAIL
       check. Do not loop. Do not skip. Do not redesign. Do not add features.

READ FIRST — the three rules of the engine
  1. One page = one JSON file under data/content/.
  2. Any page renders in any template. Blocks resolve two-tier:
     views/{ACTIVE_TEMPLATE}/blocks/{type}.php → views/_blocks/{type}.php
  3. Every number is a PHP variable in config/variables.php, referenced as
     {{ counts.services.value }}. Numbers never appear in prose.

HARD CONSTRAINTS
  - PHP 8.2+ only. No Composer. No Node at build time. No database.
  - Zero third-party runtime requests. No CDN. No jQuery. No icon font.
  - Do not modify TailAdmin, admin/, or the old PHP-CMS sections of the
    repository — this build is static-only.
  - Do not rename or reorder the 130 services or the 5 macro works.
  - Every change must survive `php bin/build.php` with zero render errors.
  - Every task ends with a PASS/FAIL report line. Stop and report on FAIL.

================================================================================
TASK 1 — Baseline the build
================================================================================
Run:   php bin/build.php
Check: dist/ exists; a clean build reports 639 files, 3 languages, 3 templates,
       zero render errors.
PASS if: build exits 0 and file count = 639.
FAIL if: build errors or file count diverges — stop and report the diff.
Do not modify any file in Task 1.

================================================================================
TASK 2 — Map the current block surface
================================================================================
Read:  views/_blocks/ (one file per block type)
       views/_shared/layouts/app.php and home.php
       views/_shared/partials/ (head, header, footer, band, subnav, ...)
Produce a table (in your report only): block_type | file | used_by_pages
PASS if: table covers every file in views/_blocks/ and every partial.
Do not create or edit files in Task 2.

================================================================================
TASK 3 — Expose views/templates/cda-w5d-template/ into the block system
================================================================================
This folder ships an HTML template (index.html + assets/) that is not yet
part of the engine. Expose it as a proper template.

  3a. Create views/templates/cda-w5d-template/blocks/ (empty is fine —
      it will fall back to views/_blocks/).
  3b. Read index.html. For each <section> in it, decide whether it maps
      to an existing block type (hero, cards, bars, carousel, contact-desks,
      cta, definitions, form, stack) or needs a new block type.
  3c. If a section maps to an existing block, do NOT copy its markup —
      instead create a page JSON under data/content/pages/ that uses
      that block with the cda-w5d-template's content.
  3d. If a section needs a NEW block type, create ONLY:
        views/_blocks/{new-type}.php          (the block implementation)
        one page JSON under data/content/pages/ that exercises it
      Do NOT modify any existing block.
  3e. Copy the template's CSS/JS into assets/_base/css/templates/
      cda-w5d-template.css and assets/_base/js/templates/cda-w5d-template.js
      (only the additive rules — never duplicate base.css or site.js).
  3f. Register the template name in config/app.php under `templates`.
PASS if: `php bin/build.php --template=cda-w5d-template` exits 0 and
         dist/ contains the rendered pages for this template in en/fr/ar.
FAIL if: build errors, or any existing template (ledger/atlas/folio) breaks.
Report which files you created and which existing files (if any) you touched.

================================================================================
TASK 4 — Document the template
================================================================================
Update docs/templates.md with a new section "cda-w5d-template":
  - which block types it uses,
  - which blocks it overrides (or "none"),
  - which additive CSS/JS file it adds,
  - a one-line build command.
PASS if: section exists and matches Task 3's actual files.
Do not touch any other doc.

================================================================================
TASK 5 — Define the certification task model
================================================================================
Create data/content/certification/README.md (schema only, no data yet):
  - one JSON file per service: data/content/certification/{service_slug}.json
  - fields: id, service_slug, level (1–4), title {en,fr,ar},
            instructions {en,fr,ar}, evidence, pass_rule
  - a task belongs to exactly one service
  - a service's certification is complete when every task in its file is
    approved (approval is out of scope for the static build)
PASS if: README.md exists and the schema is valid JSON in an example block.
Do not create 130 certification files in Task 5 — schema only.

================================================================================
TASK 6 — Define the delivery contract model
================================================================================
Create data/content/delivery/README.md (schema only):
  - one JSON file per service: data/content/delivery/{service_slug}.json
  - fields: service_slug, deliverable_unit, weekly_target, evidence_rule
PASS if: README.md exists and the schema is valid JSON in an example block.
Do not create 130 delivery files in Task 6 — schema only.

================================================================================
TASK 7 — Define the audience meta-website model
================================================================================
Create data/content/audiences/README.md (schema only):
  - one JSON file per audience: data/content/audiences/{slug}.json
  - fields: slug, label {en,fr,ar}, template (ledger|atlas|folio|cda-w5d-template),
            audience_type (owner|admin|leader|member|investor|customer|user)
  - the build MUST render the audience's meta site in the assigned template
PASS if: README.md exists, schema is valid JSON, and the 7 default audience
         slugs are listed.
Do not create the 7 audience JSON files in Task 7 — schema only.

================================================================================
TASK 8 — Add ONE example certification file
================================================================================
Create data/content/certification/heritage-digitisation.json as the single
reference example. Include exactly 3 tasks at levels 1, 2, 3. Every field
populated, all three languages present.
PASS if: `php bin/build.php` still exits 0 and the file validates against
         the schema from Task 5.
Do not create any other certification file.

================================================================================
TASK 9 — Add ONE example delivery file
================================================================================
Create data/content/delivery/heritage-digitisation.json as the reference
example. All fields populated.
PASS if: build exits 0 and the file validates against the schema from Task 6.
Do not create any other delivery file.

================================================================================
TASK 10 — Add ONE example audience file
================================================================================
Create data/content/audiences/member.json with template = "atlas".
PASS if: `php bin/build.php --template=atlas` exits 0 and a member meta site
         appears in dist/.
Do not create the other 6 audience files.

================================================================================
TASK 11 — Wire the example into a page
================================================================================
Create data/content/pages/membership.json that:
  - uses the `cards` block to show the 3 certification tasks from Task 8,
  - uses the `bars` block to show the weekly target from Task 9,
  - links to the member meta site from Task 10.
PASS if: build exits 0 for ledger, atlas, and cda-w5d-template; the page
         renders in all three languages.
Do not add new block types in Task 11.

================================================================================
TASK 12 — Final verification and report
================================================================================
Run:   php bin/build.php
       php bin/build.php --template=atlas
       php bin/build.php --template=folio
       php bin/build.php --template=cda-w5d-template
Check:
  - all four builds exit 0,
  - file counts match the baseline from Task 1 (plus the new template's files),
  - zero third-party requests in the generated HTML,
  - license footer present in every rendered page,
  - no existing service, macro, or project was renamed.
PASS if: all four conditions hold.
Produce a final report:
  Files created      : (list)
  Files modified     : (list)
  Files deleted      : (list — should be empty)
  Build file counts  : baseline vs after, per template
  New block types    : (list or "none")
  New audience files : member.json only
  New cert files     : heritage-digitisation.json only
  New delivery files : heritage-digitisation.json only
  Third-party check  : PASS/FAIL
  License footer     : PASS/FAIL

================================================================================
STOP CONDITION
================================================================================
You are done when Task 12 reports PASS. Do not continue past Task 12.
Do not add features, do not optimise, do not rename, do not "improve" the
engine. The next 127 certification files, the next 129 delivery files, and
the next 6 audience files are a follow-up prompt — not this one.

================================================================================
WHAT NOT TO DO — common loop triggers
================================================================================
  ✗ Do not "also fix" the old PHP-CMS admin/ folder.
  ✗ Do not touch TailAdmin or SQLite.
  ✗ Do not create all 130 certification files at once.
  ✗ Do not rewrite existing blocks to "make them generic".
  ✗ Do not rename a block, a service, a macro, or a project.
  ✗ Do not add a second templating syntax.
  ✗ Do not introduce a framework, a bundler, or a CDN.
  ✗ Do not enter a loop re-reading the whole README. Read it once.
================================================================================
```

---

## Author

**Maher Kaddoussi** — Founder and owner, W5D
📧 [owner@w5d.tech](mailto:owner@w5d.tech) · 🌐 https://w5d.tech · 💻 https://github.com/W5Downer/w5d-php-cms
