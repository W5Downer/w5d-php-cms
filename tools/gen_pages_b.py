#!/usr/bin/env python3
"""Pages: ecosystem, status, services index, macro-works, declarations, studio,
trust and the error page — plus the seven founder declarations.

Usage:  python3 tools/gen_pages_b.py
"""
from __future__ import annotations

from gen_common import (L, cards, cta, definitions, kpis, note_text, prose,
                        quote, steps, table, timeline, write)

PAGES = []

# ── Ecosystem index ───────────────────────────────────────────────────────
PAGES.append({
    "path": "/ecosystem/",
    "kind": "index",
    "nav_group": "ecosystem",
    "audience": "public",
    "title": L("Ecosystem", "Écosystème", "المنظومة"),
    "nav_label": L("Ecosystem", "Écosystème", "المنظومة"),
    "summary": L("Everything W5D is building, at its true status.", "Tout ce que W5D construit, à son statut véritable.", "كل ما تبنيه W5D، بحالته الحقيقية."),
    "description": L("Five L2 programmes and the projects beneath them, each with a status word and a dated verification."),
    "blocks": [
        {"type": "hero", "id": "top", "data": {
            "eyebrow": L("Ecosystem"),
            "grand": L("Everything W5D is building, at its true status."),
            "lead": L("Five L2 programmes and the projects beneath them. Each page opens with a status word, an evidence rating and the date it was last verified."),
            "split": True,
        }},
        prose(title=L("How to read the badges"), body=[{"type": "p", "text": L("Nine status words are used, defined on the status register. A \"draft\" is a working draft and not a product. An evidence rating of E1 means declared by the founder and not independently checked. No item on this site reaches E3.")}]),
        {"type": "register", "title": L("The register"), "data": {"legend": True}},
        cta(L("Read a claim, then check it.", ), [
            {"label": L("Status register"), "href": "page:status", "primary": True},
            {"label": L("Declarations"), "href": "page:declarations"},
        ]),
    ],
})

# ── Status register ───────────────────────────────────────────────────────
PAGES.append({
    "path": "/status/",
    "kind": "register",
    "nav_group": "ecosystem",
    "audience": "public",
    "title": L("Status register", "Registre de statut", "سجل الحالة"),
    "nav_label": L("Status register", "Registre de statut", "سجل الحالة"),
    "summary": L("Every programme and project, dated and rated.", "Chaque programme et projet, daté et évalué.", "كل برنامج ومشروع، مؤرَّخ ومُقيَّم."),
    "description": L("Generated from data, not typed."),
    "blocks": [
        {"type": "hero", "id": "top", "data": {
            "eyebrow": L("Trust · Public record"),
            "grand": L("Status register."),
            "lead": L("Every programme and project, with one of nine status words, an evidence rating and the date it was last verified. Generated from the register records, not typed."),
            "split": True,
        }},
        {"type": "register", "title": L("The register"), "data": {"legend": True}},
        prose(title=L("How verification works"), body=[{"type": "p", "text": L("\"Last verified\" means a person looked at the public artefact, or confirmed its absence, on that date. E2 means a public artefact was observed. It does not mean the artefact is good, complete or independently reviewed. That is E3, and no item has it.")}]),
        definitions(title=L("Open items"), items=[
            {"term": L("Facts not yet supplied"), "var": "counts.open_items", "note": L("Each appears on the site as a grey chip, and none has been guessed.")},
            {"term": L("Social links, logo files"), "open": True},
        ]),
        cta(L("If something here is wrong, file a correction.", ), [
            {"label": L("File a correction"), "href": "page:contact", "primary": True},
            {"label": L("Corrections record"), "href": "page:corrections"},
        ]),
    ],
})

# ── Services index ────────────────────────────────────────────────────────
PAGES.append({
    "path": "/services/",
    "kind": "index",
    "nav_group": "services",
    "audience": "public",
    "title": L("Services", "Services", "الخدمات"),
    "nav_label": L("Services", "الخدمات", "الخدمات"),
    "summary": L("130 registered services across five macro works.", "130 services enregistrés dans cinq macro-œuvres.", "130 خدمة مسجّلة عبر خمسة أعمال كبرى."),
    "description": L("The complete catalogue, grouped by macro work."),
    "blocks": [
        {"type": "hero", "id": "top", "data": {
            "eyebrow": L("Services"),
            "grand": L("130 registered services."),
            "lead": L("Twenty-six services per macro work, each with its own record, placement and declared boundary. The catalogue is generated from the service records, so it cannot disagree with them."),
            "split": True,
            "meta": [
                {"label": L("Registered services"), "var": "counts.services"},
                {"label": L("Macro works"), "var": "counts.macros"},
                {"label": L("Per macro"), "var": "counts.services_per_macro"},
                {"label": L("Designed capacity"), "var": "counts.service_capacity"},
            ],
        }},
        {"type": "service-catalog", "title": L("The catalogue")},
        cta(L("Each service has a page. Each page states its boundary.", ), [
            {"label": L("Macro works"), "href": "page:macro-works", "primary": True},
            {"label": L("The register"), "href": "page:status"},
        ]),
    ],
})

# ── Macro works index ─────────────────────────────────────────────────────
PAGES.append({
    "path": "/macro-works/",
    "kind": "index",
    "nav_group": "services",
    "audience": "public",
    "title": L("Macro works", "Macro-œuvres", "الأعمال الكبرى"),
    "nav_label": L("Macro works", "Macro-œuvres", "الأعمال الكبرى"),
    "summary": L("Five macro works, twenty-six services each.", "Cinq macro-œuvres, vingt-six services chacune.", "خمسة أعمال كبرى، ست وعشرون خدمة لكل منها."),
    "description": L("The top-level demand map of the W5D ontology."),
    "blocks": [
        {"type": "hero", "id": "top", "data": {
            "eyebrow": L("Services"),
            "grand": L("Five macro works."),
            "lead": L("The top-level demand map of the W5D ontology: five macro works, 26 registered services each, and a distinct placement on the real/virtual spectrum."),
            "split": True,
        }},
        {"type": "cards", "title": L("The macro works"), "data": {"source": "macros", "columns": 2}},
        table(L("Placement across the five macro works"),
              head=[L("Macro work"), L("Services"), L("Placement")],
              rows=[
                  [{"text": L("Cultures / Humanities"), "href": "macro:cultures"}, "001–026", "Real 1.0"],
                  [{"text": L("Built Environment"), "href": "macro:built"}, "027–052", "Real 1.0"],
                  [{"text": L("Sciences"), "href": "macro:sciences"}, "053–078", "Between 1.0"],
                  [{"text": L("Governance"), "href": "macro:governance"}, "079–104", "Between 1.0"],
                  [{"text": L("Technology"), "href": "macro:technology"}, "105–130", "Real 0.5 / Virtual 0.5"],
              ]),
        cta(L("The catalogue lists all 130 in one place.", ), [
            {"label": L("All services"), "href": "page:services", "primary": True},
            {"label": L("The five dimensions"), "href": "page:dimensions"},
        ]),
    ],
})

# ── Declarations index ────────────────────────────────────────────────────
PAGES.append({
    "path": "/declarations/",
    "kind": "index",
    "nav_group": "ecosystem",
    "audience": "public",
    "title": L("Declarations", "Déclarations", "الإعلانات"),
    "nav_label": L("Declarations", "الإعلانات", "الإعلانات"),
    "summary": L("The founder's seven texts.", "Les sept textes du fondateur.", "نصوص المؤسس السبعة."),
    "description": L("Each declaration carries declared limits and a corrections link."),
    "blocks": [
        {"type": "hero", "id": "top", "data": {
            "eyebrow": L("Trust · Doctrine"),
            "grand": L("The founder's seven texts."),
            "lead": L("Each declaration is written in the first person, carries its declared limits, and can be corrected by anyone who files a defect."),
            "split": True,
        }},
        {"type": "cards", "title": L("The seven declarations"), "data": {"source": "declarations", "columns": 2}},
        cta(L("A declaration is a working framework, not a final definition.", ), [
            {"label": L("The five dimensions"), "href": "page:dimensions", "primary": True},
            {"label": L("Corrections"), "href": "page:corrections"},
        ]),
    ],
})

# ── Studio ────────────────────────────────────────────────────────────────
STUDIO = [
    ("studio", "/studio/", L("Studio"), L("Static-first sites, built to be checked."), L("W5D Studio applies the company's discipline to client work: no blocking loaders, no third-party requests, every figure dated, every limit declared.")),
    ("studio-audit", "/studio/audit/", L("Audit"), L("Find out what your site really does."), L("A measured read of an existing site: what loads, what is requested from third parties, what a visitor without JavaScript sees, and what a screen reader announces.")),
    ("studio-platforms", "/studio/platforms/", L("Platforms"), L("Static-first sites and web platforms."), L("Sites generated at build time, served as files, with behaviour added only where it is needed.")),
    ("studio-estates", "/studio/estates/", L("Estates"), L("Websites for real-estate developers."), L("The studio's service for developers: fast, accessible estate sites. It is related to the MetaX Estates project but is not the same thing.")),
    ("studio-migration", "/studio/migration/", L("Migration"), L("Move without losing what ranks."), L("Moving a site to a static-first stack while keeping its addresses, content and search visibility intact.")),
    ("studio-compliance", "/studio/compliance/", L("Compliance"), L("Privacy and accessibility, checked."), L("Checks against a stated standard, reported with what was and was not tested. The studio does not issue certificates.")),
    ("studio-security", "/studio/security/", L("Security"), L("Hardening a static-first stack."), L("Headers, dependencies, forms and build pipeline. Static-first removes whole classes of risk, and the work is to avoid adding them back.")),
    ("studio-process", "/studio/process/", L("Process"), L("How a project runs."), L("Five stages, each ending with something you can check.")),
    ("case-gloulou", "/studio/cases/gloulou/", L("Gloulou case"), L("Removing a page loader."), L("On 3 Oct 2026 a blocking page loader was removed from a client site. This page records the change and what is not yet published.")),
]

STUDIO_BODY = {
    "studio": [
        steps(L("What the studio will not do"), [
            {"title": L("Publish a number it cannot show"), "text": L("No invented clients, logos, testimonials or certifications.")},
            {"title": L("Add third-party scripts, trackers or fonts"), "text": L("Without a stated reason, and stated in the report.")},
            {"title": L("Hide a limit"), "text": L("Every deliverable lists what it does not cover.")},
        ], ordered=False),
        cards(L("Services"), [
            {"title": L("Audit"), "summary": L("Find out what your site really does."), "href": "page:studio-audit"},
            {"title": L("Platforms"), "summary": L("Static-first sites and web platforms."), "href": "page:studio-platforms"},
            {"title": L("Estates"), "summary": L("Websites for real-estate developers."), "href": "page:studio-estates"},
            {"title": L("Migration"), "summary": L("Move without losing what ranks."), "href": "page:studio-migration"},
            {"title": L("Compliance"), "summary": L("Privacy and accessibility, checked."), "href": "page:studio-compliance"},
            {"title": L("Security"), "summary": L("Hardening a static-first stack."), "href": "page:studio-security"},
            {"title": L("Process"), "summary": L("How a project runs."), "href": "page:studio-process"},
        ]),
    ],
    "studio-audit": [
        steps(L("What is measured"), [
            {"title": L("Performance"), "text": L("Lighthouse mobile scores, layout shift and blocking time, with the test date and device profile stated.")},
            {"title": L("Third parties"), "text": L("Every request that leaves the site's own origin, with its purpose.")},
            {"title": L("Content without JavaScript"), "text": L("Whether the real content is in the HTML.")},
            {"title": L("Accessibility"), "text": L("Automated checks plus keyboard and mobile-menu walkthroughs.")},
            {"title": L("Headers and basics"), "text": L("Content-Security-Policy, transport security, robots and sitemap behaviour.")},
        ], ordered=False),
        prose(title=L("What you receive"), body=[{"type": "p", "text": L("A dated report with each finding, how to reproduce it, and what it would take to fix it. Automated tools find only part of the problems, and the report says which parts were not checked.")}]),
    ],
    "studio-platforms": [
        prose(title=L("How it is built"), body=[{"type": "ul", "items": [
            L("Pages are generated at build time, so the content is in the HTML for crawlers, readers without JavaScript and slow connections."),
            L("Fonts and icons are self-hosted; there are no third-party requests."),
            L("All text lives in one file per language. Adding a language needs no code change, and right-to-left languages are supported."),
            L("Forms post to an endpoint you choose, and show a clear disabled state until one is configured."),
            L("Status and evidence labels are first-class, so drafts are never presented as products."),
        ]}]),
        note_text(L("Declared limits"), [L("A static-first approach is a poor fit for sites that need per-user content on every page. The studio will say so rather than force it.")]),
    ],
    "studio-estates": [
        prose(title=L("What is offered"), body=[{"type": "p", "text": L("Websites for developers and their programmes: listings, plans, enquiry forms, and location information, built static-first. Details and examples are not published yet.")}]),
        note_text(L("Declared limits"), [L("No client names, listings or results are shown, because none were supplied for publication.")]),
    ],
    "studio-migration": [
        steps(L("Steps"), [
            {"title": L("Inventory"), "text": L("Every public address and what it returns today.")},
            {"title": L("Map"), "text": L("Old addresses to new ones, with permanent redirects where they change.")},
            {"title": L("Rebuild"), "text": L("Content in the new stack, compared page by page.")},
            {"title": L("Measure"), "text": L("Before and after, with the same tools and settings.")},
            {"title": L("Cut over"), "text": L("At an agreed time, with a way back.")},
        ]),
        note_text(L("Declared limits"), [L("No migration can promise a search ranking. The studio promises that addresses and content are preserved and that results are measured and reported.")]),
    ],
    "studio-compliance": [
        definitions(L("Checks"), [
            {"term": L("Accessibility"), "value": L("WCAG 2.2 AA with automated tools and keyboard-only, screen-reader and mobile walkthroughs.")},
            {"term": L("Privacy"), "value": L("An inventory of cookies, storage, third-party requests and forms, matched against what the privacy notice says.")},
        ]),
        note_text(L("What this is not"), [L("It is not a certification, an audit by an accredited body, or legal advice. A site that passes these checks has passed these checks on that date.")]),
    ],
    "studio-security": [
        prose(title=L("What is reviewed"), body=[{"type": "ul", "items": [
            L("A Content-Security-Policy that allows only the site's own origin where possible."),
            L("Transport security, content-type and framing headers."),
            L("Dependencies and build tooling, pinned and reviewed."),
            L("Form endpoints: spam protection, rate limits and where messages go."),
        ]}]),
        note_text(L("What is not offered"), [L("Penetration testing and incident response are not part of this service. The studio does not claim SOC 2, ISO 27001 or any other certification.")]),
    ],
    "studio-process": [
        steps(L("Stages"), [
            {"title": L("Brief"), "text": L("What the site must do and who it is for. Quote on request.")},
            {"title": L("Baseline"), "text": L("Measured starting numbers, with date and tools.")},
            {"title": L("Build"), "text": L("Short iterations with a live preview, not indexed until launch.")},
            {"title": L("Verify"), "text": L("Accessibility, performance, link and no-JavaScript checks, with the numbers.")},
            {"title": L("Hand over"), "text": L("Source, build instructions, a list of open items and what the site does not cover.")},
        ]),
        prose(title=L("Pre-launch"), body=[{"type": "p", "text": L("Until a domain is live, a site can run in preview mode: it is hidden from search engines, shows a preview banner and has no canonical addresses. One setting switches it to launch mode.")}]),
    ],
    "case-gloulou": [
        prose(title=L("What changed"), body=[
            {"type": "p", "text": L("A loader that hid the page until everything had finished loading was removed. A loader that hides content delays the first view of real content, and it hides that content entirely from visitors whose scripts fail. The page content now renders without waiting.")},
            {"type": "p", "text": L("Date of the change: 3 Oct 2026.")},
        ]),
        definitions(L("Figures"), [
            {"term": L("Case figures (before and after)"), "open": True},
            {"term": L("Client consent to publish"), "open": True},
        ]),
        note_text(L("Declared limits"), [L("This is a single case. No before-and-after numbers are shown until they are confirmed, and no generalisation is intended. The client's consent to publication is recorded as open.")]),
    ],
}

for slug, path, title, grand, lead in STUDIO:
    blocks = [
        {"type": "hero", "id": "top", "data": {
            "eyebrow": L("Studio"),
            "grand": grand,
            "lead": lead,
            "split": True,
        }},
    ] + STUDIO_BODY[slug] + [
        cta(L("Every claim on this site is dated, and every limit declared.", ), [
            {"label": L("Start with an audit"), "href": "page:studio-audit", "primary": True},
            {"label": L("Write to the studio"), "href": "page:contact"},
        ]),
    ]
    PAGES.append({
        "path": path,
        "kind": "page",
        "nav_group": "studio",
        "audience": "client",
        "title": title,
        "nav_label": title,
        "summary": lead,
        "description": grand,
        "blocks": blocks,
    })

# ── Trust pages ───────────────────────────────────────────────────────────
PAGES += [
    {
        "path": "/trust/principles/",
        "kind": "page",
        "nav_group": "trust",
        "audience": "public",
        "title": L("Principles", "Principes", "المبادئ"),
        "nav_label": L("Principles", "Principes", "المبادئ"),
        "summary": L("Four working principles, and one orientation stated openly.", "Quatre principes de travail, et une orientation dite ouvertement.", "أربعة مبادئ عمل، وتوجّه يُعلن صراحة."),
        "description": L("Sidq, falsifiability, declared limits and correctability."),
        "blocks": [
            {"type": "hero", "id": "top", "data": {
                "eyebrow": L("Trust · Doctrine"),
                "grand": L("Four working principles, and one orientation stated openly."),
                "lead": L("Sidq, falsifiability, declared limits and correctability. They come from MetaX Academy and apply to this whole site."),
                "split": True,
            }},
            definitions(L("The four principles"), [
                {"term": L("Sidq"), "value": L("Truthfulness as an operative constraint: no page asserts more than its evidence supports; unknown figures are shown as unknown; a draft is never presented as a product.")},
                {"term": L("Falsifiability"), "value": L("Every substantive claim carries what would refute it.")},
                {"term": L("Declared limits"), "value": L("Every body of work states what it does not cover.")},
                {"term": L("Correctability"), "value": L("Any reader can file a defect, and corrections are published with reasons.")},
            ]),
            note_text(L("Orientation, stated openly"), [L("The founder's orientation includes \"God first, then human\", and the Ontology Department aligns its work toward al-Ḥaqq, the Real. These are stated here so that readers can weigh the work knowing them. They are not claims that the five-dimensional ontology depends on them.")], tone="info"),
            definitions(L("2030B principles"), [{"term": L("Seven principles"), "var": "counts.principles_2030b", "note": L("An earlier draft said \"eleven\"; the declaration lists seven.")}]),
            cta(L("A principle that cannot be checked is a slogan.", ), [
                {"label": L("The 25 harms"), "href": "page:harms", "primary": True},
                {"label": L("Our statement"), "href": "page:statement"},
            ]),
        ],
    },
    {
        "path": "/trust/license/",
        "kind": "legal",
        "nav_group": "trust",
        "audience": "public",
        "title": L("Licence", "Licence", "الرخصة"),
        "nav_label": L("Licence", "Licence", "الرخصة"),
        "summary": L("Maher's Licence 2.3 (ML-2.3), and the licence of this website.", "La Licence Maher 2.3 (ML-2.3), et la licence de ce site.", "رخصة ماهر 2.3 (ML-2.3)، ورخصة هذا الموقع."),
        "description": L("This site does not restate ML-2.3's terms."),
        "blocks": [
            {"type": "hero", "id": "top", "data": {
                "eyebrow": L("Trust · Doctrine"),
                "grand": L("Licence: Maher's Licence 2.3 (ML-2.3)."),
                "lead": L("MetaX Academy publishes its content under a licence called ML-2.3. This site does not restate its terms."),
                "split": True,
            }},
            definitions(L("MetaX Academy"), [
                {"term": L("Licence"), "value": L("ML-2.3, published at metax.academy/license. Read the terms there; a summary here could misstate them.")},
            ]),
            definitions(L("This website"), [
                {"term": L("Licence for text and code"), "open": True, "note": L("Until decided, all rights are reserved by the author of each text.")},
            ]),
            note_text(L("Default rule"), [L("Until the licence is decided, all rights are reserved by the author of each text. Quoting with attribution and a link is welcome.")], tone="open"),
            cta(L("Read the licence where it is published.", ), [
                {"label": L("ML-2.3 at MetaX"), "href": "https://metax.academy/license", "primary": True},
                {"label": L("Legal notice"), "href": "page:legal"},
            ]),
        ],
    },
    {
        "path": "/trust/harms/",
        "kind": "register",
        "nav_group": "trust",
        "audience": "public",
        "title": L("The 25 harms", "Les 25 préjudices", "الضرر الـ25"),
        "nav_label": L("The 25 harms", "Les 25 préjudices", "الضرر الـ25"),
        "summary": L("A register of structural harms, HX-01 to HX-25, in six strata.", "Un registre de préjudices structurels, HX-01 à HX-25, en six strates.", "سجل أضرار بنيوية، HX-01 إلى HX-25، في ست طبقات."),
        "description": L("This page gives the groups, not the harms' texts."),
        "blocks": [
            {"type": "hero", "id": "top", "data": {
                "eyebrow": L("Trust · Doctrine"),
                "grand": L("The 25 structural harms."),
                "lead": L("MetaX Academy publishes a register of 25 structural harms, HX-01 to HX-25, grouped into six strata. This page gives the groups, not the harms' texts."),
                "split": True,
            }},
            table(L("Six strata"), head=[L("Stratum"), L("Register entries")], rows=[
                [L("Cognitive"), "HX-01–03"],
                [L("Relational"), "HX-04–06"],
                [L("Economic"), "HX-07–09, 13"],
                [L("Material"), "HX-10–12, 14"],
                [L("Reflexive"), "HX-15–19"],
                [L("Successor"), "HX-20–25"],
            ]),
            cta(L("The harms themselves are on metax.academy, so that the source stays the single record.", ), [
                {"label": L("Read the register"), "href": "https://metax.academy/", "primary": True},
                {"label": L("Our statement"), "href": "page:statement"},
            ]),
        ],
    },
    {
        "path": "/trust/statement/",
        "kind": "page",
        "nav_group": "trust",
        "audience": "public",
        "title": L("Our statement", "Notre déclaration", "بياننا"),
        "nav_label": L("Our statement", "Notre déclaration", "بياننا"),
        "summary": L("Where W5D has not yet remedied a harm.", "Là où W5D n'a pas encore remédié à un préjudice.", "حيث لم تعالج W5D ضررًا بعد."),
        "description": L("A company that publishes a harms register owes its readers an account of where it falls short."),
        "blocks": [
            {"type": "hero", "id": "top", "data": {
                "eyebrow": L("Trust · Doctrine"),
                "grand": L("Where W5D has not yet remedied a harm."),
                "lead": L("A company that publishes a harms register owes its readers an account of where it falls short. This is the place for it."),
                "split": True,
            }},
            prose(L("What can be said today"), [
                {"type": "p", "text": L("W5D has not published a harm-by-harm assessment of its own work. That is a gap, and it is stated here as one.")},
                {"type": "p", "text": L("Two programmes touch areas that the register treats as sensitive: identity and cloned selves (KernID, SelfAI), and living substrates (BCIA). Neither has a published consent policy or an independent review.")},
                {"type": "p", "text": L("A long identifier (W5D UUID) and a ledger design (Aevum and Meridian) are specifications without implementations. No harm can be measured yet, and none is claimed to be absent.")},
            ]),
            definitions(L("What is still to be written"), [
                {"term": L("The harm-by-harm statement, for each of HX-01 to HX-25"), "open": True},
            ]),
            cta(L("Use the contact page with the reason set to \"Correction\".", ), [
                {"label": L("Corrections record"), "href": "page:corrections", "primary": True},
                {"label": L("Contact"), "href": "page:contact"},
            ]),
        ],
    },
    {
        "path": "/trust/corrections/",
        "kind": "register",
        "nav_group": "trust",
        "audience": "public",
        "title": L("Corrections", "Corrections", "التصحيحات"),
        "nav_label": L("Corrections", "Corrections", "التصحيحات"),
        "summary": L("What this site changed, when and why.", "Ce que ce site a changé, quand et pourquoi.", "ما غيّره هذا الموقع، ومتى، ولماذا."),
        "description": L("Corrections are published with reasons."),
        "blocks": [
            {"type": "hero", "id": "top", "data": {
                "eyebrow": L("Trust · Public record"),
                "grand": L("Corrections."),
                "lead": L("What this site changed, when and why. Wording that overreached was corrected before publication, and each correction is listed here."),
                "split": True,
            }},
            timeline(L("The record"), [
                {"date": "2026-10-10", "kind": "event", "title": L("\"Eleven principles\" corrected to \"seven principles\""), "text": L("The 2030B declaration headed its list \"The Eleven Principles of the Ecosystem\" but states and lists seven. The heading now reads \"Seven principles\".")},
                {"date": "2026-10-10", "kind": "event", "title": L("BCIA is a programme, not an \"achievement\""), "text": L("MetaX reports no findings, so this site says \"the first research programme from the beyond\".")},
                {"date": "2026-10-10", "kind": "event", "title": L("KernID: designed on zero-knowledge principles, not yet audited"), "text": L("No specification or audit is published. The site says it is designed on zero-knowledge principles and has not yet been independently audited.")},
            ]),
            prose(L("File a correction"), [
                {"type": "p", "text": L("Use the contact page with the reason set to \"Correction\". Say where the error is and, if you can, what would show it. Accepted and declined corrections are both recorded.")},
            ]),
            cta(L("A correction is recorded whether accepted or declined.", ), [
                {"label": L("File a correction"), "href": "page:contact", "primary": True},
                {"label": L("Status register"), "href": "page:status"},
            ]),
        ],
    },
    {
        "path": "/trust/glossary/",
        "kind": "page",
        "nav_group": "trust",
        "audience": "public",
        "title": L("Glossary", "Glossaire", "المعجم"),
        "nav_label": L("Glossary", "Glossaire", "المعجم"),
        "summary": L("Terms used on this site.", "Termes utilisés sur ce site.", "المصطلحات المستخدمة في هذا الموقع."),
        "description": L("One definition per term, and where it is used."),
        "blocks": [
            {"type": "hero", "id": "top", "data": {
                "eyebrow": L("Trust"),
                "grand": L("Terms used on this site."),
                "lead": L("One definition per term, with the page where it is used. Where a term is contested, the page says so."),
                "split": True,
            }},
            definitions(L("Terms"), [
                {"term": L("Macro work"), "value": L("One of the five top-level demand maps, each holding 26 registered services.")},
                {"term": L("Service"), "value": L("One of the 130 registered entries, numbered 001–130.")},
                {"term": L("Placement"), "value": L("Where a service sits on the real/virtual spectrum: Real 1.0, Between 1.0, or Real 0.5 / Virtual 0.5.")},
                {"term": L("Status word"), "value": L("One of nine words on the status ladder, from concept to live.")},
                {"term": L("Evidence rating"), "value": L("E0 to E3. E1 is declared and specified; E2 is publicly observable; E3 is independently checked, and nothing on this site reaches E3.")},
                {"term": L("L2 / L3"), "value": L("L2 is a programme in the register; L3 is a project under a programme.")},
                {"term": L("Dyon"), "value": L("The identity-linked account and certification layer described in the CMS design.")},
                {"term": L("Sidq"), "value": L("Truthfulness as an operative constraint; one of the four working principles.")},
            ]),
            cta(L("A term without a definition is a place where two pages can disagree.", ), [
                {"label": L("Principles"), "href": "page:principles", "primary": True},
                {"label": L("Status register"), "href": "page:status"},
            ]),
        ],
    },
    {
        "path": "/trust/security/",
        "kind": "legal",
        "nav_group": "trust",
        "audience": "public",
        "title": L("Security policy", "Politique de sécurité", "سياسة الأمن"),
        "nav_label": L("Security policy", "Politique de sécurité", "سياسة الأمن"),
        "summary": L("How this website is protected, and how to report a problem.", "Comment ce site est protégé, et comment signaler un problème.", "كيف يُحمى هذا الموقع، وكيف تبلّغ عن مشكلة."),
        "description": L("It makes no certification claims."),
        "blocks": [
            {"type": "hero", "id": "top", "data": {
                "eyebrow": L("Trust · Policies"),
                "grand": L("Security policy."),
                "lead": L("How this website is protected, and how to report a problem. It makes no certification claims."),
                "split": True,
            }},
            prose(L("How the site is built"), [
                {"type": "ul", "items": [
                    L("The site is static files. There is no application server or database to attack in the published build."),
                    L("A strict Content-Security-Policy allows scripts, styles, fonts and images from the site's own origin only."),
                    L("There are no third-party scripts, trackers, fonts or embedded widgets."),
                    L("Transport security and content-type headers are set by the host."),
                ]},
            ]),
            note_text(L("What this does not mean"), [L("W5D holds no security certification, such as SOC 2 or ISO 27001, and has had no independent penetration test. Earlier template copy that implied otherwise was removed.")], tone="warn"),
            definitions(L("Reporting a vulnerability"), [
                {"term": L("Security desk"), "open": True},
                {"term": L("Response time"), "open": True, "note": L("Not promised here because none has been decided.")},
            ]),
            cta(L("Write to the security desk with the page address and how to reproduce the problem.", ), [
                {"label": L("Contact"), "href": "page:contact", "primary": True},
                {"label": L("Privacy"), "href": "page:privacy"},
            ]),
        ],
    },
    {
        "path": "/trust/ai/",
        "kind": "legal",
        "nav_group": "trust",
        "audience": "public",
        "title": L("AI use", "Usage de l'IA", "استخدام الذكاء الاصطناعي"),
        "nav_label": L("AI use", "Usage de l'IA", "استخدام الذكاء الاصطناعي"),
        "summary": L("Where AI is, and is not, involved.", "Où l'IA est impliquée, et où elle ne l'est pas.", "أين يُستخدم الذكاء الاصطناعي وأين لا."),
        "description": L("Where AI is, and is not, involved in this website and in W5D's programmes."),
        "blocks": [
            {"type": "hero", "id": "top", "data": {
                "eyebrow": L("Trust · Policies"),
                "grand": L("Where AI is, and is not, involved."),
                "lead": L("This page states plainly what an AI assistant did on this site, and where no AI is present at all."),
                "split": True,
            }},
            prose(L("This website"), [
                {"type": "p", "text": L("The site was specified and built with the help of an AI coding assistant, working from the founder's texts. Statements of fact come from supplied material or from public pages checked on the dates shown. Nothing on the site is generated at visit time, and the site has no chatbot.")},
            ]),
            prose(L("Programmes"), [
                {"type": "ul", "items": [
                    L("SelfAI is a concept. No model or service exists."),
                    L("The Ontology AI Agent is described by the Ontology Department as trained on first principles and allowed to say \"I do not know yet\". Its behaviour is not evaluated here."),
                ]},
            ]),
            note_text(L("Human review"), [L("A person remains responsible for what is published. Mistakes can be reported as corrections.")], tone="info"),
            cta(L("No claim on this site is generated at visit time.", ), [
                {"label": L("Corrections"), "href": "page:corrections", "primary": True},
                {"label": L("Privacy"), "href": "page:privacy"},
            ]),
        ],
    },
    {
        "path": "/trust/privacy/",
        "kind": "legal",
        "nav_group": "trust",
        "audience": "public",
        "title": L("Privacy", "Confidentialité", "الخصوصية"),
        "nav_label": L("Privacy", "Confidentialité", "الخصوصية"),
        "summary": L("No tracking, no cookies.", "Pas de suivi, pas de cookies.", "لا تتبّع، لا ملفات تعريف ارتباط."),
        "description": L("This website sets no cookies and loads nothing from third parties."),
        "blocks": [
            {"type": "hero", "id": "top", "data": {
                "eyebrow": L("Trust · Policies"),
                "grand": L("Privacy: no tracking, no cookies."),
                "lead": L("This website sets no cookies and loads nothing from third parties. The only thing it stores in your browser is your theme choice."),
                "split": True,
            }},
            definitions(L("What is stored"), [
                {"term": L("Theme choice"), "value": L("Saved in your browser's local storage under the name \"w5d-theme\". It never leaves your device.")},
            ]),
            definitions(L("What is not collected"), [
                {"term": L("Cookies, analytics, advertising identifiers, fingerprinting"), "value": L("None. Your host may keep standard server logs.")},
            ]),
            definitions(L("Contact messages"), [
                {"term": L("Form endpoint"), "open": True, "note": L("Until one is configured the form is disabled and you can write by email.")},
                {"term": L("Data protection contact, legal basis, retention"), "open": True},
            ]),
            cta(L("To ask what is held about you, or to have it deleted, write to the privacy desk once it has an address.", ), [
                {"label": L("Contact"), "href": "page:contact", "primary": True},
                {"label": L("Legal notice"), "href": "page:legal"},
            ]),
        ],
    },
    {
        "path": "/trust/legal/",
        "kind": "legal",
        "nav_group": "trust",
        "audience": "public",
        "title": L("Legal notice", "Mentions légales", "إشعار قانوني"),
        "nav_label": L("Legal notice", "Mentions légales", "إشعار قانوني"),
        "summary": L("Who publishes this site.", "Qui publie ce site.", "من ينشر هذا الموقع."),
        "description": L("Fields that have not been supplied are shown as open and not guessed."),
        "blocks": [
            {"type": "hero", "id": "top", "data": {
                "eyebrow": L("Trust · Policies"),
                "grand": L("Legal notice."),
                "lead": L("Who publishes this site. Fields that have not been supplied are shown as open and not guessed."),
                "split": True,
            }},
            definitions(L("Publisher"), [
                {"term": L("Legal name"), "open": True},
                {"term": L("Legal form"), "open": True},
                {"term": L("Year founded"), "open": True},
                {"term": L("Company register number (RNE)"), "open": True},
                {"term": L("Tax identification number"), "open": True},
                {"term": L("Share capital"), "open": True},
                {"term": L("Registered address"), "value": "{{ site.address }}"},
                {"term": L("Publication director"), "open": True},
                {"term": L("Host"), "open": True},
                {"term": L("Host address"), "open": True},
            ]),
            prose(L("Liability"), [
                {"type": "p", "text": L("This site describes programmes at different stages. A listing is not an offer, and a draft is not a product. Check the status and the date on every item before relying on it.")},
            ]),
            cta(L("Texts attributed to Maher are his authorship.", ), [
                {"label": L("Licence"), "href": "page:license", "primary": True},
                {"label": L("Privacy"), "href": "page:privacy"},
            ]),
        ],
    },
    {
        "path": "/404/",
        "kind": "page",
        "nav_group": "utility",
        "audience": "public",
        "title": L("Page not found", "Page introuvable", "الصفحة غير موجودة"),
        "nav_label": L("Not found", "Introuvable", "غير موجود"),
        "summary": L("This page does not exist.", "Cette page n'existe pas.", "هذه الصفحة غير موجودة."),
        "description": L("It may have moved, or it may never have been written."),
        "blocks": [
            {"type": "hero", "id": "top", "data": {
                "eyebrow": L("Error 404"),
                "grand": L("This page does not exist."),
                "lead": L("It may have moved, or it may never have been written. Try the status register or the home page."),
                "split": False,
                "actions": [
                    {"label": L("Home"), "href": "page:index", "primary": True},
                    {"label": L("Status register"), "href": "page:status"},
                ],
            }},
        ],
    },
]


def main() -> None:
    for page in PAGES:
        record_id = page["path"].strip("/").replace("/", "-") or "index"
        write("pages", record_id, dict(page))
    print(f"pages-b: wrote {len(PAGES)} pages")


if __name__ == "__main__":
    main()
