#!/usr/bin/env python3
"""Pages: the home page, the company section and the dimensions section.

Each page is one JSON file with its own data AND its own config. Prose is
localised as {en, fr, ar}; where a long passage has only an English original the
engine shows it verbatim and the page marks itself as English-only rather than
inventing a translation.

Usage:  python3 tools/gen_pages_a.py
"""
from __future__ import annotations

from gen_common import (L, action, bars, cards, cta, definitions, kpis, note_text,
                        prose, quote, steps, table, timeline, write)

# ── Home ──────────────────────────────────────────────────────────────────
home = {
    "path": "/",
    "kind": "root",
    "nav_group": "company",
    "audience": "public",
    "title": L("W5D: meta, macro and beyond technology", "W5D : méta, macro et au-delà de la technologie", "W5D: ميتا وماكرو وما بعد التقنية"),
    "nav_label": L("Home", "Accueil", "الرئيسية"),
    "summary": L(
        "World 5 Dimensions works across IT, sciences and technology on a five-dimensional ontology. Every programme is listed with its status and the date it was verified.",
        "World 5 Dimensions œuvre dans l'informatique, les sciences et la technologie sur une ontologie à cinq dimensions. Chaque programme est listé avec son statut et sa date de vérification.",
        "تعمل World 5 Dimensions في تقنية المعلومات والعلوم والتقنية على أنطولوجيا خماسية الأبعاد. كل برنامج مُدرج بحالته وتاريخ التحقق منه.",
    ),
    "description": L("A company, and a working ontology."),
    "blocks": [
        {
            "type": "hero",
            "id": "top",
            "data": {
                "eyebrow": L("Preview · Be Smarter operational · most programmes in draft"),
                "grand": L("We build meta, macro and beyond technology."),
                "lead": L(
                    "A company and a working ontology, operating across IT, sciences and technology. Everything W5D is building is listed here, including what is only a draft.",
                    "Une société et une ontologie en travail, à travers l'informatique, les sciences et la technologie. Tout ce que W5D construit est listé ici, y compris ce qui n'est qu'un brouillon.",
                    "شركة وأنطولوجيا قيد العمل، تعمل عبر تقنية المعلومات والعلوم والتقنية. كل ما تبنيه W5D مُدرج هنا، بما في ذلك ما هو مجرّد مسوّدة.",
                ),
                "split": True,
                "notice": L(
                    "Status first: every programme opens with its status word, an evidence rating and the date it was verified.",
                ),
                "actions": [
                    {"label": L("Start here"), "href": "page:about", "primary": True},
                    {"label": L("See every status"), "href": "page:status"},
                ],
            },
        },
        kpis(
            title=L("Five dimensions, five macro works, one register"),
            lead=L(
                "The numbers below are read from the same registry that every page uses. Change a figure in config/variables.php and this strip changes with it.",
            ),
            items=[
                {"var": "counts.dimensions", "sub": L("space ×3, information, possibility")},
                {"var": "counts.macros", "sub": L("macro works")},
                {"var": "counts.services", "sub": L("registered services")},
                {"var": "counts.developers_current", "sub": L("delivering today")},
            ],
            note=L("Designed capacity and current footprint are different numbers and are never added together."),
        ),
        cards(
            title=L("Three operators, one framework"),
            lead=L(
                "Technology is structured, scaled and generated. The three words name those three conditions.",
            ),
            items=[
                {
                    "title": L("Meta"),
                    "summary": L("The reflexive layer, where a system becomes aware of its own structure. Institution: MetaX Academy."),
                    "href": "page:horizons",
                    "meta": [{"label": L("Dimension"), "value": L("4th — Information/Structure")}],
                },
                {
                    "title": L("Macro"),
                    "summary": L("Technology as a large-scale ensemble rather than an isolated artefact. Institution: MacroLifeTech."),
                    "href": "page:horizons",
                    "meta": [{"label": L("Dimension"), "value": L("5th — Possibility/Modality")}],
                },
                {
                    "title": L("Beyond"),
                    "summary": L("The layer of conditions and intelligences from which technology emerges. Programme: BCIA."),
                    "href": "page:horizons",
                    "meta": [{"label": L("Dimension"), "value": L("the condition of both")}],
                },
            ],
            columns=3,
            numbered=True,
        ),
        prose(
            title=L("What W5D is"),
            body=[
                {
                    "type": "p",
                    "text": L(
                        "World 5 Dimensions was founded by Maher and operates across distinct activities in IT, sciences and technology. In the founder's own words, it is \"not merely a company; it is an experiment in five-dimensional thinking\".",
                    ),
                },
                {
                    "type": "p",
                    "text": L(
                        "The ontology is short. The first three dimensions are spatial extension. The fourth is Information/Structure: the architecture of what is. The fifth is Possibility/Modality: the horizon of what is not yet but could be. Time is kept as a measure of change, not accepted as a dimension of being.",
                    ),
                },
            ],
            aside=[
                {
                    "title": L("Read next"),
                    "body": [
                        {"type": "ul", "items": [
                            "[The five dimensions](page:dimensions)",
                            "[Meta, macro, beyond](page:horizons)",
                            "[The status register](page:status)",
                        ]},
                    ],
                }
            ],
        ),
        {
            "type": "index",
            "title": L("What W5D is not"),
            "data": {
                "items": [
                    {
                        "title": L("Not a list of finished products"),
                        "summary": L("Most of the ecosystem is in draft, and each page says so."),
                    },
                    {
                        "title": L("Not a claim of certifications, clients or audits"),
                        "summary": L("None are shown because none have been supplied. E3 appears nowhere on this site."),
                    },
                    {
                        "title": L("Not a request for trust"),
                        "summary": L("Every substantive claim should carry what would refute it, and every reader can file a correction."),
                    },
                ],
            },
        },
        quote(
            L("To create technology without meta is to create blindly, without macro to create parochially, and without beyond to create idolatrously, treating the created as if it were the creator."),
            cite=L("Maher"),
            source=L("The official wording of meta, macro and beyond"),
            href="page:horizons",
        ),
        cards(
            title=L("Where to go next"),
            items=[
                {"title": L("The ecosystem"), "summary": L("Everything W5D is building, at its true status."), "href": "page:ecosystem"},
                {"title": L("130 services"), "summary": L("Five macro works, 26 registered services each, one catalogue."), "href": "page:services"},
                {"title": L("The 2030B register"), "summary": L("Ten dimensions of the complete human, and an eleventh project."), "href": "project:2030b"},
                {"title": L("W5D Studio"), "summary": L("Static-first sites and platforms, built to be checked."), "href": "page:studio"},
                {"title": L("Trust and doctrine"), "summary": L("Four working principles, a licence and a harms register."), "href": "page:principles"},
                {"title": L("Write to a desk"), "summary": L("One form, six reasons, and the addresses that exist."), "href": "page:contact"},
            ],
            columns=3,
        ),
        cta(
            L("Build something that can be checked."),
            [
                {"label": L("See what exists today"), "href": "page:status", "primary": True},
                {"label": L("Build with W5D"), "href": "page:contact"},
            ],
        ),
    ],
}

# ── About ─────────────────────────────────────────────────────────────────
about = {
    "path": "/about/",
    "kind": "page",
    "nav_group": "company",
    "audience": "public",
    "title": L("About W5D", "À propos de W5D", "عن W5D"),
    "nav_label": L("About W5D", "À propos", "عن W5D"),
    "summary": L("What W5D is, and what it is not.", "Ce qu'est W5D, et ce qu'il n'est pas.", "ما هي W5D، وما ليست عليه."),
    "description": L("A company, and a working ontology."),
    "blocks": [
        {
            "type": "hero",
            "id": "top",
            "data": {
                "eyebrow": L("Company"),
                "grand": L("A company, and a working ontology."),
                "lead": L(
                    "World 5 Dimensions works across IT, sciences and technology. It was founded by Maher on a five-dimensional ontology, and this site lists everything it is building, including what is only a draft.",
                ),
                "split": True,
            },
        },
        prose(
            title=L("What W5D is"),
            body=[
                {"type": "p", "text": L("W5D is World 5 Dimensions, founded by Maher. It operates across distinct activities in IT, sciences and technology. In the founder's own words, it is \"not merely a company; it is an experiment in five-dimensional thinking\".")},
                {"type": "p", "text": L("The working ontology is short. The first three dimensions are spatial extension. The fourth is Information/Structure: the architecture of what is. The fifth is Possibility/Modality: the horizon of what is not yet but could be. Time is kept as a measure of change, not accepted as a dimension of being.")},
            ],
        ),
        prose(
            title=L("What W5D builds"),
            body=[
                {"type": "p", "text": L("W5D describes its work as meta, macro and beyond technology: three operators, each with an institution or programme. [Dimensions](page:dimensions) and [horizons](page:horizons) explain them. The [ecosystem](page:ecosystem) lists every programme and project with one of nine status words, a dated verification, and what exists today.")},
            ],
        ),
        {
            "type": "index",
            "title": L("What W5D is not"),
            "data": {
                "items": [
                    {"title": L("Not a list of finished products"), "summary": L("Most of the ecosystem is in draft, and each page says so.")},
                    {"title": L("Not a claim of certifications, clients or independent audits"), "summary": L("None are shown because none have been supplied.")},
                    {"title": L("Not a request for blind trust"), "summary": L("Every substantive claim should carry what would refute it, and every reader can file a correction.")},
                ],
            },
        },
        steps(
            title=L("How to read this site"),
            items=[
                {"title": L("Status first"), "text": L("Every programme opens with its status, evidence rating and last-verified date.")},
                {"title": L("Founder's texts"), "text": L("Seven declarations, each with declared limits and a corrections link.")},
                {"title": L("Corrections"), "text": L("What changed on this site, and why. Accepted and declined corrections are both recorded.")},
            ],
            ordered=False,
        ),
        cta(L("The ontology is one page away.",), [
            {"label": L("The five dimensions"), "href": "page:dimensions", "primary": True},
            {"label": L("Who decides"), "href": "page:governance"},
        ]),
    ],
}

# ── Maher ─────────────────────────────────────────────────────────────────
maher = {
    "path": "/maher/",
    "kind": "page",
    "nav_group": "company",
    "audience": "public",
    "title": L("Maher", "Maher", "ماهر"),
    "nav_label": L("Maher", "Maher", "ماهر"),
    "summary": L("Founder of World 5 Dimensions and author of the seven declarations.", "Fondateur de World 5 Dimensions et auteur des sept déclarations.", "مؤسس World 5 Dimensions ومؤلف الإعلانات السبعة."),
    "description": L("Founder of World 5 Dimensions, creator of 2030B."),
    "blocks": [
        {
            "type": "hero",
            "id": "top",
            "data": {
                "eyebrow": L("Company"),
                "grand": L("Maher"),
                "lead": L("Founder of World 5 Dimensions, creator of 2030B, and author of the seven declarations published here."),
                "split": False,
            },
        },
        prose(
            title=L("Role"),
            body=[
                {"type": "p", "text": L("Maher founded World 5 Dimensions and writes its foundational texts in the first person. The texts present themselves as working frameworks, not final definitions.")},
            ],
        ),
        {
            "type": "roster",
            "title": L("Responsibilities"),
            "data": {
                "items": [
                    {"name": L("World 5 Dimensions (W5D)"), "role": L("Founder")},
                    {"name": L("2030B"), "role": L("Creator"), "note": L("A long-term educational and innovation platform whose stated aim is \"a complete human by 2030\".")},
                    {"name": L("KernID, Aevum, Meridian, W5D UUID"), "role": L("Curator")},
                    {"name": L("The seven declarations"), "role": L("Author"), "note": L("Also the specification of the Aevum and Meridian architectures.")},
                    {"name": L("Ontology Department"), "role": L("Established within W5D")},
                ],
            },
        },
        note_text(
            title=L("Orientation, stated openly"),
            body=[L("The 2030B principles include \"God first, then human\", and the Ontology Department orients its work toward al-Ḥaqq, the Real. W5D states this openly rather than leaving it implicit. It is not a claim that the five-dimensional ontology depends on it.")],
            tone="info",
        ),
        definitions(
            title=L("Photograph and biography"),
            items=[
                {"term": L("Photograph"), "open": True},
                {"term": L("Longer biography"), "open": True, "note": L("Not supplied. None is invented here.")},
            ],
        ),
        cta(L("Writing to Maher: use the contact page. Press requests go to the press desk."), [
            {"label": L("Contact"), "href": "page:contact", "primary": True},
            {"label": L("Press"), "href": "page:press"},
        ]),
    ],
}

# ── Governance ─────────────────────────────────────────────────────────
governance = {
    "path": "/governance/",
    "kind": "page",
    "nav_group": "company",
    "audience": "public",
    "title": L("Governance", "Gouvernance", "الحوكمة"),
    "nav_label": L("Governance", "Gouvernance", "الحوكمة"),
    "summary": L("Who decides, and how corrections happen.", "Qui décide, et comment les corrections ont lieu.", "من يقرّر، وكيف تحدث التصحيحات."),
    "description": L("Decision rights and the correction process."),
    "blocks": [
        {
            "type": "hero",
            "id": "top",
            "data": {
                "eyebrow": L("Company"),
                "grand": L("Who decides, and how corrections happen."),
                "lead": L("W5D is curated by its founder. This page states what is decided where, and how anyone can challenge it."),
                "split": True,
            },
        },
        definitions(
            title=L("Decision rights"),
            items=[
                {"term": L("Doctrine and wording"), "value": L("Decided by Maher. The seven declarations carry his name and are versioned.")},
                {"term": L("Status words"), "value": L("Assigned from the fixed nine-word vocabulary on the status register and dated when verified.")},
                {"term": L("Corrections"), "value": L("Published with reasons on the corrections page.")},
            ],
        ),
        steps(
            title=L("How a correction happens"),
            items=[
                {"title": L("File a defect"), "text": L("Anyone files a defect through the contact page with the reason set to \"Correction\".")},
                {"title": L("Record it"), "text": L("The correction desk records it. The desk address is still to be confirmed."), "meta": L("Desk: to be confirmed")},
                {"title": L("Change or decline"), "text": L("If accepted, the text changes and the change is listed with its date and reason. If declined, the reason is recorded.")},
            ],
        ),
        definitions(
            title=L("What is not yet settled"),
            items=[
                {"term": L("Legal form"), "open": True},
                {"term": L("Succession arrangements"), "open": True},
                {"term": L("Publication director"), "open": True},
            ],
            lead=L("These are listed with every other open item on the status register."),
        ),
        cta(L("A correction is an ordinary event, not a scandal.",), [
            {"label": L("Corrections record"), "href": "page:corrections", "primary": True},
            {"label": L("File a correction"), "href": "page:contact"},
        ]),
    ],
}

# ── Timeline ───────────────────────────────────────────────────────────
def _timeline_items():
    items = [
        {"date": "2025", "kind": "roadmap", "title": L("Be Smarter"), "chip": "operational", "text": L("Published as operational.")},
        {"date": "2026", "kind": "roadmap", "title": L("Be Honester"), "chip": "building", "text": L("Published as building.")},
        {"date": "2027", "kind": "roadmap", "title": L("Be Healthier"), "chip": "planned", "text": L("Published as planned.")},
        {"date": "2028", "kind": "roadmap", "title": L("Be Creater"), "chip": "planned", "text": L("Published as planned.")},
        {"date": "2029", "kind": "roadmap", "title": L("Be Kinder"), "chip": "planned", "text": L("Published as planned.")},
        {"date": "2030", "kind": "roadmap", "title": L("Full 2030B ecosystem"), "text": L("Published as the target.")},
        {"date": "2026-10-03", "kind": "event", "title": L("Gloulou case"), "text": L("A blocking page loader removed from a client site.")},
        {"date": "2026-10-10", "kind": "event", "title": L("MetaX Academy demo build v2026.10.1"), "text": L("Observed live; first status verification of the register.")},
        {"date": "2026-10-10", "kind": "event", "title": L("This site specified and built in preview mode")},
    ]
    return items


timeline_page = {
    "path": "/timeline/",
    "kind": "page",
    "nav_group": "company",
    "audience": "public",
    "title": L("Timeline", "Calendrier", "الجدول الزمني"),
    "nav_label": L("Timeline", "Calendrier", "الجدول الزمني"),
    "summary": L("Dated milestones and roadmap.", "Jalons datés et feuille de route.", "محطات مؤرَّخة وخريطة طريق."),
    "description": L("Roadmap years come from the published 2030B roadmap."),
    "blocks": [
        {
            "type": "hero",
            "id": "top",
            "data": {
                "eyebrow": L("Company"),
                "grand": L("Dated milestones and roadmap."),
                "lead": L("Roadmap years come from the published 2030B roadmap. Events carry the date they were recorded."),
                "split": True,
            },
        },
        timeline(
            L("Milestones"),
            _timeline_items(),
            open_note=L("The published roadmap shows five projects across 2025 to 2029 and a full ecosystem in 2030, but 2030B documents ten projects. The schedule for projects 6 to 10 is still to be confirmed."),
        ),
        cta(L("Every date on this site is either a recorded event or a published roadmap year.",), [
            {"label": L("Status register"), "href": "page:status", "primary": True},
            {"label": L("Corrections"), "href": "page:corrections"},
        ]),
    ],
}

# ── Careers ────────────────────────────────────────────────────────────
careers = {
    "path": "/careers/",
    "kind": "page",
    "nav_group": "company",
    "audience": "candidate",
    "title": L("Careers", "Carrières", "الوظائف"),
    "nav_label": L("Careers", "Carrières", "الوظائف"),
    "summary": L("How to work with W5D.", "Comment travailler avec W5D.", "كيف تعمل مع W5D."),
    "description": L("How to work with W5D."),
    "blocks": [
        {
            "type": "hero",
            "id": "top",
            "data": {
                "eyebrow": L("Company"),
                "grand": L("How to work with W5D."),
                "lead": L("W5D is designed for a large expert workforce and is delivered today by a small one. Both numbers are published, and neither is inflated."),
                "split": True,
            },
        },
        kpis(
            title=L("Two numbers, kept apart"),
            items=[
                {"var": "counts.developers_current", "sub": L("real delivery capacity today")},
                {"var": "counts.experts_target", "sub": L("design ceiling, not headcount")},
            ],
            note=L("The 130,000 figure is an organisational design target. No expert employee is onboarded at the time of this build."),
        ),
        prose(
            title=L("What working here involves"),
            body=[
                {"type": "p", "text": L("Work is organised in weekly cycles: directives on Monday, assignments the same day, reports on Friday, approvals and sign-off on Friday afternoon, and rollups computed overnight.")},
                {"type": "ul", "items": [
                    L("Every deliverable is recorded with evidence, and every week is scored against a published rubric."),
                    L("A blocker has 24 hours at manager level and 48 at leader level before it escalates."),
                    L("Nothing is asked of a candidate that the site does not state publicly."),
                ]},
            ],
        ),
        definitions(
            title=L("Open items"),
            items=[
                {"term": L("Open roles"), "open": True, "note": L("No vacancy list has been supplied.")},
                {"term": L("Careers desk address"), "open": True},
                {"term": L("Salary and contract terms"), "value": L("600 TND/month for a certified post is the published model; contract terms are not supplied."), "note": L("Modelled figure, not an offer.")},
            ],
        ),
        cta(L("Write to the careers desk. Until it has an address, the contact page carries the reason code.",), [
            {"label": L("Contact"), "href": "page:contact", "primary": True},
            {"label": L("The weekly engine"), "href": "page:about"},
        ]),
    ],
}

# ── Press ──────────────────────────────────────────────────────────────
press = {
    "path": "/press/",
    "kind": "page",
    "nav_group": "company",
    "audience": "press",
    "title": L("Press", "Presse", "الصحافة"),
    "nav_label": L("Press", "Presse", "الصحافة"),
    "summary": L("Facts, wording and contacts for journalists.", "Faits, formulations et contacts pour les journalistes.", "وقائع وصياغات وجهات اتصال للصحافيين."),
    "description": L("Facts, wording and contacts for journalists."),
    "blocks": [
        {
            "type": "hero",
            "id": "top",
            "data": {
                "eyebrow": L("Company"),
                "grand": L("Facts, wording and contacts for journalists."),
                "lead": L("Use the wording on this page. Where a figure is unknown, say that it is unknown rather than estimating it."),
                "split": True,
            },
        },
        definitions(
            title=L("How to describe W5D"),
            items=[
                {"term": L("Name"), "value": L("World 5 Dimensions (W5D).")},
                {"term": L("One line"), "value": L("W5D is a company and a working ontology operating across IT, sciences and technology.")},
                {"term": L("Founder"), "value": L("Maher.")},
                {"term": L("Scale today"), "value": L("Delivered by 26 developers in Tunisia; the 130,000-expert figure is a design ceiling.")},
                {"term": L("Status of the site"), "value": L("A preview build. Most programmes are in draft, and each carries a dated status.")},
            ],
        ),
        note_text(
            title=L("What not to write"),
            body=[L("Do not describe a draft as a product, an E1 claim as independently verified, or the 130,000 figure as current headcount. Do not attribute a certification to W5D: none is held.")],
            tone="warn",
        ),
        cta(L("Press desk address and assets are still to be confirmed.",), [
            {"label": L("Contact"), "href": "page:contact", "primary": True},
            {"label": L("Status register"), "href": "page:status"},
        ]),
    ],
}

# ── Contact ────────────────────────────────────────────────────────────
contact = {
    "path": "/contact/",
    "kind": "page",
    "nav_group": "company",
    "audience": "public",
    "title": L("Contact", "Contact", "اتصال"),
    "nav_label": L("Contact", "Contact", "اتصال"),
    "summary": L("Write to the right desk.", "Écrire au bon bureau.", "اكتب إلى المكتب المناسب."),
    "description": L("One form, six reasons, and a direct address for each desk."),
    "blocks": [
        {
            "type": "hero",
            "id": "top",
            "data": {
                "eyebrow": L("Company"),
                "grand": L("Write to the right desk."),
                "lead": L("One form, six reasons, and a direct address for each desk. A desk whose address has not been supplied is shown as open, not invented."),
                "split": True,
            },
        },
        {
            "type": "form",
            "title": L("Send a message"),
            "data": {
                "reasons": ["build", "press", "careers", "correction", "security", "other"],
                "address": "",
                "endpoint": "",
            },
        },
        {
            "type": "contact-desks",
            "title": L("Desks and addresses"),
            "data": {
                "desks": [
                    {"label": L("General"), "address": ""},
                    {"label": L("Studio"), "address": ""},
                    {"label": L("Press"), "address": ""},
                    {"label": L("Careers"), "address": ""},
                    {"label": L("Corrections"), "address": ""},
                    {"label": L("Security"), "address": ""},
                    {"label": L("Privacy"), "address": ""},
                ],
                "items": [
                    {"label": L("Telephone"), "open": True},
                    {"label": L("Office hours"), "open": True},
                    {"label": L("Address"), "value": "{{ site.address }}"},
                ],
            },
        },
        note_text(
            title=L("Security reports"),
            body=[L("Report vulnerabilities to the security desk. The address is still to be confirmed. See the security policy for how this site is built.")],
            tone="info",
        ),
        cta(L("A correction is always welcome, and it is recorded whether accepted or declined.",), [
            {"label": L("Corrections record"), "href": "page:corrections", "primary": True},
            {"label": L("Security policy"), "href": "page:security"},
        ]),
    ],
}

# ── Dimensions ─────────────────────────────────────────────────────────
dimensions = {
    "path": "/dimensions/",
    "kind": "page",
    "nav_group": "dimensions",
    "audience": "public",
    "title": L("The five dimensions", "Les cinq dimensions", "الأبعاد الخمسة"),
    "nav_label": L("The five dimensions", "Les cinq dimensions", "الأبعاد الخمسة"),
    "summary": L("Space ×3, Information/Structure, Possibility/Modality — with time kept as a measure.", "Espace ×3, Information/Structure, Possibilité/Modalité — le temps restant une mesure.", "الفضاء ×3، المعلومة/البنية، الإمكان/الجهة — ويبقى الزمن مقياسًا."),
    "description": L("The official definition W5D adopts, offered as a working ontology."),
    "blocks": [
        {
            "type": "hero",
            "id": "top",
            "data": {
                "eyebrow": L("Dimensions"),
                "grand": L("Five dimensions, with time kept as a measure."),
                "lead": L("The first three are space. The fourth is Information/Structure. The fifth is Possibility/Modality. This is the definition W5D adopts, offered as a working ontology and not as a universal claim."),
                "split": True,
            },
        },
        prose(
            title=L("Why not time?"),
            body=[
                {"type": "p", "text": L("The founder was taught that space is three-dimensional and time is the fourth. On inspection that looked like a convention of calculation rather than a description of being: time measures change, it does not constitute a thing. W5D therefore treats time as \"a relational metric, not an ontological axis\". It is not denied as a parameter of measurement.")},
            ],
        ),
        prose(
            title=L("The fourth: Information/Structure"),
            body=[
                {"type": "p", "text": L("Every entity, process or system has an architecture: a code, a pattern, a law, a protocol. Matter without structure is noise; energy without information is chaos. Information is not only communication but ontological: it determines what can exist, how it interacts and what it can become.")},
                {"type": "p", "text": L("In IT this is data structures, algorithms and protocols. In sciences it is laws, symmetries, constants and models. In technology it is the blueprint that precedes actualisation. The fourth dimension is the architecture of the actual.")},
            ],
        ),
        prose(
            title=L("The fifth: Possibility/Modality"),
            body=[
                {"type": "p", "text": L("A system is never only what it is; it is also what it can become. The fifth dimension is the space of possible states, variations and outcomes. It resonates with the philosophical position called Five-Dimensionalism, in which objects have modal parts stretched across possible worlds.")},
                {"type": "p", "text": L("It is not fantasy. It is constrained by the fourth, because structure defines the boundaries of possibility. But possibility also exceeds structure, because new structures can emerge. It is the horizon of the not-yet.")},
            ],
        ),
        table(
            L("One map"),
            head=[L("No."), L("Dimension"), L("What it is"), L("Where it shows")],
            rows=[
                ["1", L("Length"), L("Spatial extension."), L("Geometry, the first axis of space.")],
                ["2", L("Width"), L("Spatial extension."), L("Geometry, the second axis of space.")],
                ["3", L("Depth"), L("Spatial extension."), L("Geometry, the third axis of space.")],
                ["4", L("Information/Structure"), L("The architecture of the actual: code, pattern, law, protocol."), L("IT: data structures, algorithms, protocols. Sciences: laws, symmetries, models. Technology: the blueprint.")],
                ["5", L("Possibility/Modality"), L("The horizon of the not-yet: states, variations and outcomes a system could reach."), L("Sciences map possibility spaces; technology actualises selected possibilities into new structures.")],
            ],
            lead=L("Every dimension claim on this site resolves to the table below. Where other mappings appear in older drafts, this one wins."),
        ),
        table(
            L("The three operators"),
            head=[L("Operator"), L("Layer"), L("Dimension in operation"), L("Institution or programme")],
            rows=[
                [L("Meta"), L("Reflexive"), L("4th: Information/Structure"), L("MetaX Academy")],
                [L("Macro"), L("Systemic"), L("5th: Possibility/Modality, at scale"), L("MacroLifeTech (Meta-X)")],
                [L("Beyond"), L("Generative"), L("The condition of possibility for both"), L("BCIA, the first research programme from the beyond")],
            ],
        ),
        note_text(
            title=L("Declared limits"),
            body=[L("This is the definition W5D adopts. It is not presented as the universal definition of dimension, and it is open to correction.")],
        ),
        cta(L("The reasoning is set out in the first person in the first declaration.",), [
            {"label": L("The Fifth Dimension I Seek"), "href": "declaration:fifth-dimension", "primary": True},
            {"label": L("Meta, macro, beyond"), "href": "page:horizons"},
        ]),
    ],
}

# ── Horizons ───────────────────────────────────────────────────────────
horizons = {
    "path": "/horizons/",
    "kind": "page",
    "nav_group": "dimensions",
    "audience": "public",
    "title": L("Meta, macro and beyond", "Méta, macro et au-delà", "ميتا وماكرو وما بعد"),
    "nav_label": L("Meta, macro, beyond", "Méta, macro, au-delà", "ميتا، ماكرو، ما بعد"),
    "summary": L("Three operators, not slogans.", "Trois opérateurs, pas des slogans.", "ثلاثة عوامل، لا شعارات."),
    "description": L("Meta, macro and beyond are operators in one framework."),
    "blocks": [
        {
            "type": "hero",
            "id": "top",
            "data": {
                "eyebrow": L("Dimensions"),
                "grand": L("Meta, macro and beyond are operators, not slogans."),
                "lead": L("W5D creates meta, macro and beyond technology. The phrase is an ontological programme: technology is structured, scaled and generated."),
                "split": True,
            },
        },
        prose(
            title=L("The problem"),
            body=[{"type": "p", "text": L("Technology is commonly treated as a neutral instrument or as a self-generating force. W5D holds both views to be inadequate. Technology is structured, scaled and generated, and the three words name those three conditions.")}],
        ),
        steps(
            title=L("The three layers"),
            items=[
                {"title": L("Meta: the reflexive layer"), "text": L("The layer at which a system becomes aware of its own structure. Its institution is MetaX Academy, built on one constraint: every substantive claim carries what a stranger needs in order to find out that it is wrong. In operation it is the fourth dimension, Information/Structure.")},
                {"title": L("Macro: the systemic layer"), "text": L("Technology understood as a large-scale ensemble rather than an isolated artefact: a macrosystem. Its institution is MacroLifeTech (Meta-X), a research-preview umbrella. In operation it is the fifth dimension, Possibility/Modality, extended across large-scale systems.")},
                {"title": L("Beyond: the generative layer"), "text": L("The layer of creators, conditions and intelligences from which technology emerges. It is an ontological claim about priority: technology is a dependent entity and does not self-originate. It is neither a spatial nor a temporal beyond. BCIA is the first research programme from the beyond.")},
            ],
            ordered=False,
        ),
        quote(
            L("To create technology without meta is to create blindly, without macro to create parochially, and without beyond to create idolatrously."),
            cite=L("W5D official wording"),
            source=L("The official wording of meta, macro and beyond"),
        ),
        note_text(
            title=L("Declared limits"),
            body=[L("The mapping of meta to the fourth dimension and macro to the fifth is the W5D mapping. Other mappings exist in the wider literature and are not adopted here.")],
        ),
        cta(L("Each layer has an institution, and each institution has a dated status.",), [
            {"label": L("The ecosystem"), "href": "page:ecosystem", "primary": True},
            {"label": L("Architecture L1–L8"), "href": "page:architecture"},
        ]),
    ],
}

# ── Architecture ───────────────────────────────────────────────────────
architecture = {
    "path": "/architecture/",
    "kind": "page",
    "nav_group": "dimensions",
    "audience": "public",
    "title": L("Architecture L1–L8", "Architecture L1–L8", "المعمار L1–L8"),
    "nav_label": L("Architecture L1–L8", "Architecture L1–L8", "المعمار L1–L8"),
    "summary": L("A containment hierarchy, L1 to L8.", "Une hiérarchie de contenance, de L1 à L8.", "هرمية احتواء من L1 إلى L8."),
    "description": L("Only the levels that a supplied text defines are described here."),
    "blocks": [
        {
            "type": "hero",
            "id": "top",
            "data": {
                "eyebrow": L("Dimensions"),
                "grand": L("A containment hierarchy, L1 to L8."),
                "lead": L("The architecture runs from L1 to L8. Only the levels that a supplied text defines are described here; the rest are marked as open."),
                "split": True,
            },
        },
        table(
            L("What is defined"),
            head=[L("Level"), L("Name"), L("Meaning")],
            rows=[
                ["L1", L("Level 1"), L("to be confirmed")],
                ["L2", L("Programme (register use)"), L("In the W5D register, an L2 item is a programme under W5D, such as 2030B or MetaX.")],
                ["L3", L("Project (register use)"), L("In the W5D register, an L3 item is a project under a programme, such as Be Smarter under 2030B.")],
                ["L4", L("Nation"), L("The supranational layer: macro-regional identity, three of which together complete the one human identity.")],
                ["L5", L("Continent"), L("The continental layer: bioregional and macro-systemic coordination, already factual before it is legal.")],
                ["L6", L("Country"), L("The bordered artefact: a historically recent term, created by a political ideology to make borders.")],
                ["L7", L("Level 7"), L("to be confirmed")],
                ["L8", L("Level 8"), L("to be confirmed")],
            ],
        ),
        note_text(
            title=L("Two uses of \"L\""),
            body=[L("The programme register uses L2 for a programme and L3 for a project. The nation and country declaration uses L4 for nation, L5 for continent and L6 for country. No supplied text says whether these are one ladder. Definitions for L1, L7 and L8, and the reconciliation, are to be confirmed.")],
        ),
        prose(
            title=L("Worked example"),
            body=[
                {"type": "p", "text": L("The identity infrastructure shows how one concept maps across the three operators:")},
                {"type": "ul", "items": [
                    L("Meta — KernID is the meta-layer of identity: identity aware of its own structure. The W5D UUID is the meta-layer of data: data aware of its own genome."),
                    L("Macro — Aevum and Meridian operate at civilisational economic scale."),
                    L("Beyond — all four presuppose the generative layer of creators, conditions and intelligences from which technology emerges."),
                ]},
            ],
        ),
        cards(
            title=L("Read the programmes"),
            items=[
                {"title": L("KernID"), "summary": L("The identity kernel."), "href": "project:kernid", "chip": "concept"},
                {"title": L("Aevum and Meridian"), "summary": L("Systems of engagement and record."), "href": "project:aevum", "chip": "specification"},
                {"title": L("W5D UUID"), "summary": L("An identifier that carries its own digital DNA."), "href": "project:w5d-uuid", "chip": "research-preview"},
            ],
        ),
        cta(L("L4 and L6 are different orders of reality.",), [
            {"label": L("Nation versus country"), "href": "page:nation-country", "primary": True},
            {"label": L("The five dimensions"), "href": "page:dimensions"},
        ]),
    ],
}

# ── Nation vs country ──────────────────────────────────────────────────
nation = {
    "path": "/nation-country/",
    "kind": "page",
    "nav_group": "dimensions",
    "audience": "public",
    "title": L("Nation versus country", "Nation et pays", "الأمة والبلد"),
    "nav_label": L("Nation vs country", "Nation et pays", "الأمة والبلد"),
    "summary": L("L4 and L6 are different orders of reality.", "L4 et L6 sont d'ordres de réalité différents.", "L4 وL6 رتبتان مختلفتان من الواقع."),
    "description": L("A position paper, not a settled finding."),
    "blocks": [
        {
            "type": "hero",
            "id": "top",
            "data": {
                "eyebrow": L("Dimensions"),
                "grand": L("L4 nation and L6 country are different orders of reality."),
                "lead": L("A summary of the founder's declaration, with its declared limits shown open. This is a position paper, not a settled finding."),
                "split": True,
            },
        },
        definitions(
            title=L("The distinction"),
            items=[
                {"term": L("L4 nation"), "value": L("The supranational layer: a form of belonging, prior to any border. W5D's stated goal is to develop it and to arrive at three macro-regional identities that together complete the one human identity.")},
                {"term": L("L6 country"), "value": L("A bordered administrative artefact. The founder's position is that the term is historically recent and was created by a political ideology to make borders.")},
            ],
        ),
        quote(L("Nation is generative and fifth-dimensional. Country is administrative and fourth-dimensional.")),
        definitions(
            title=L("The three identities"),
            items=[
                {"term": L("Macro-regional identity 1"), "open": True},
                {"term": L("Macro-regional identity 2"), "open": True},
                {"term": L("Macro-regional identity 3"), "open": True},
            ],
            lead=L("The target is three macro-regional identities. None has been named in the supplied material."),
        ),
        note_text(
            title=L("Declared limits of the thesis"),
            body=[
                L("(1) The claim that \"country\" is a recent term created by a political ideology to make borders is a position, not a settled finding; the historiography of the state system is contested."),
                L("(2) The target of three macro-regional identities is a design goal, not a prediction, and it is falsifiable: if two, four or seven prove more coherent, the framework must be corrected."),
                L("(3) The claim that L4 is prior to L6 is ontological, not chronological."),
                L("(4) The link between borders and punishment is an analogy of invariant, not an identity of mechanism."),
            ],
        ),
        cta(L("Transition: borders may be superseded by a layer that makes them redundant, not abolished by force.",), [
            {"label": L("Architecture L1–L8"), "href": "page:architecture", "primary": True},
            {"label": L("Principles"), "href": "page:principles"},
        ]),
    ],
}

PAGES = [home, about, maher, governance, timeline_page, careers, press, contact,
         dimensions, horizons, architecture, nation]


def main() -> None:
    for page in PAGES:
        record_id = page["path"].strip("/") or "index"
        record_id = record_id.replace("/", "-")
        data = dict(page)
        write("pages", record_id, data)
    print(f"pages-a: wrote {len(PAGES)} pages")


if __name__ == "__main__":
    main()
