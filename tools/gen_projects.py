#!/usr/bin/env python3
"""Generate the 25 entries of the W5D register.

Source: the public status register at w5d.metax.academy/trust/status.html, read on
the verification date recorded in config/variables.php (`time.registry_verified`).
Each entry keeps the site's own status word, evidence rating and "what exists"
sentence. Nothing is upgraded: an E1 stays E1.

Output: data/content/projects/{slug}.json

Usage:  python3 tools/gen_projects.py
"""
from __future__ import annotations

from gen_common import L, cards, cta, definitions, note_text, prose, write

L2 = "L2"
L3 = "L3"
PILLAR = "pillar"

PROJECTS = [
    {
        "slug": "2030b",
        "order": 1,
        "layer": L2,
        "status": "building",
        "evidence": "E2",
        "stage": 56,
        "horizon": "macro",
        "dimension": L("5th (Possibility/Modality)", "5e (Possibilité/Modalité)", "الخامس (الإمكان/الجهة)"),
        "group": L("Formation", "Formation", "التكوين"),
        "title": L("2030B", "2030B", "2030B"),
        "subtitle": L("Ten dimensions of the complete human.", "Dix dimensions de l'humain complet.", "عشر أبعاد للإنسان المكتمل."),
        "short": L("Ten programmes, one per dimension, and an eleventh under construction."),
        "exists": L("Ten project pages and a draft site exist; Be Smarter is operational."),
        "missing": L("Projects 3 to 10 have book indexes in draft and no public product. The eleventh project is not listed on the 2030B site. Currencies and SelfAI modules are defined, not issued or running."),
        "idea": L(
            "2030B is a W5D programme created by Maher, whose stated aim is \"a complete human by 2030\". Ten projects, one per dimension, and an eleventh under construction. The letter B is read as Being: it is not a year marker."
        ),
        "equation": "Complete Human = Smart + Honest + Healthy + Creative + Kind + Brave + Wise + Fair + Free + Grateful",
        "limits": L(
            "Committing to a dated horizon is a claim about intent, not a forecast. The roadmap shown comes from the published 2030B roadmap, and the schedule for projects 6 to 10 has not been supplied."
        ),
        "link": {"label": L("2030b.metax.academy"), "href": "https://2030b.metax.academy/", "state": "draft site"},
    },
    {
        "slug": "metax-academy",
        "order": 2,
        "layer": L2,
        "status": "live",
        "evidence": "E2",
        "stage": 100,
        "horizon": "meta",
        "dimension": L("4th (Information/Structure)", "4e (Information/Structure)", "الرابع (المعلومة/البنية)"),
        "group": L("Formation", "Formation", "التكوين"),
        "title": L("MetaX Academy", "MetaX Academy", "أكاديمية ميتاكس"),
        "subtitle": L("A professional and scientific education estate.", "Un domaine de formation professionnelle et scientifique.", "منظومة تعليم مهني وعلمي."),
        "short": L("The meta-layer institution: nine pillars, licence ML-2.3, and a public register of harms."),
        "exists": L("A live demo build (v2026.10.1) with nine pillars and a public corrections record."),
        "missing": L("Enrolment, accreditation and pricing have not been published. The pillar count is nine; the harms register carries 25 entries."),
        "idea": L(
            "MetaX Academy is the reflexive layer of the ecosystem: it exists so that every substantive claim carries what a stranger would need in order to find out that it is wrong."
        ),
        "limits": L(
            "A live build is not an accreditation. No certificate, enrolment figure or graduate outcome is claimed on this site."
        ),
        "link": {"label": L("metax.academy"), "href": "https://metax.academy/", "state": "live"},
    },
    {
        "slug": "kernid",
        "order": 3,
        "layer": L2,
        "status": "concept",
        "evidence": "E1",
        "stage": 11,
        "horizon": "meta",
        "dimension": L("4th–5th boundary", "Frontière 4e–5e", "الحدّ بين الرابع والخامس"),
        "group": L("Infrastructure", "Infrastructure", "البنية التحتية"),
        "title": L("KernID", "KernID", "KernID"),
        "subtitle": L("The identity kernel.", "Le noyau d'identité.", "نواة الهوية."),
        "short": L("One sign-in, with profiles, events and transactions in a vault the user controls."),
        "exists": L("A written description. No specification, audit or implementation is published."),
        "missing": L("No specification, threat model, audit or implementation is published. It has not been independently audited."),
        "idea": L(
            "KernID is described as a personal identity kernel that unifies a person's digital life under one authentication system: a person signs in once and reaches accounts, digital events and transaction history through a single encrypted vault. The user decides what to share, with whom, and for how long."
        ),
        "limits": L(
            "Earlier copy called KernID \"bank-grade\" and \"zero-knowledge\". Neither is shown. KernID is designed on zero-knowledge principles and has not yet been independently audited."
        ),
        "correction": L("Wording corrected before publication: \"bank-grade\" and \"zero-knowledge\" removed; designed-on-zero-knowledge-principles substituted."),
        "link": {"label": L("to be confirmed"), "href": "", "state": ""},
    },
    {
        "slug": "selfai",
        "order": 4,
        "layer": L2,
        "status": "concept",
        "evidence": "E1",
        "stage": 11,
        "horizon": "beyond",
        "dimension": L("4th–5th boundary", "Frontière 4e–5e", "الحدّ بين الرابع والخامس"),
        "group": L("Research", "Recherche", "البحث"),
        "title": L("SelfAI", "SelfAI", "SelfAI"),
        "subtitle": L("The cloned self in the digital world.", "Le soi cloné dans le monde numérique.", "الذات المستنسخة في العالم الرقمي."),
        "short": L("Defined scope and per-project module codes. No product."),
        "exists": L("A defined scope and per-project module codes in drafts. No product."),
        "missing": L("No model, dataset, evaluation or service exists. Module codes are defined per 2030B project; none is running."),
        "idea": L(
            "SelfAI is described as a cloned self that acts for a person in the digital world. Each 2030B project names a SelfAI module code, which is the only concrete artefact at present."
        ),
        "limits": L(
            "A clone of a person raises consent, identity and harm questions that the register treats as sensitive. No consent policy or independent review is published for SelfAI."
        ),
        "link": {"label": L("to be confirmed"), "href": "", "state": ""},
    },
    {
        "slug": "ontology-department",
        "order": 5,
        "layer": L2,
        "status": "draft",
        "evidence": "E2",
        "stage": 44,
        "horizon": "meta",
        "dimension": L("4th (Information/Structure)", "4e (Information/Structure)", "الرابع (المعلومة/البنية)"),
        "group": L("Formation", "Formation", "التكوين"),
        "title": L("Ontology Department", "Département d'ontologie", "قسم الأنطولوجيا"),
        "subtitle": L("The engine room of 2030B.", "La salle des machines de 2030B.", "غرفة محرّكات 2030B."),
        "short": L("A draft site with manifesto, path, library, credits and agent pages."),
        "exists": L("A draft site with manifesto, path, library, credits and agent pages."),
        "missing": L("The department's formal mandate, staffing and outputs have not been published. The site remains a draft."),
        "idea": L(
            "The Ontology Department is the organisational home of the five-macro, 130-service ontology. It describes an AI agent trained on first principles and allowed to answer \"I do not know yet\"."
        ),
        "limits": L(
            "The agent's behaviour is not evaluated here, and the department makes no published finding. A draft site is not an operating department."
        ),
        "link": {"label": L("to be confirmed"), "href": "", "state": ""},
    },
    # ── L3 projects under 2030B ───────────────────────────────────────────
    {
        "slug": "be-smarter",
        "order": 10,
        "layer": L3,
        "status": "operational",
        "evidence": "E1",
        "stage": 89,
        "horizon": "macro",
        "parent": "2030b",
        "group": L("2030B · dimension 1", "2030B · dimension 1", "2030B · البُعد الأول"),
        "title": L("Be Smarter", "Be Smarter", "كن أذكى"),
        "subtitle": L("Smart · العقل", "Smart · العقل", "الذكاء · العقل"),
        "short": L("Operational (2025 in the published roadmap)."),
        "exists": L("Operational (2025 in the published roadmap)."),
        "missing": L("Reader numbers, completion rates and outcomes have not been published."),
        "idea": L("The first of the ten 2030B dimensions, with its own books, a currency code (CTC) and a SelfAI module (SelfAI-S)."),
        "limits": L("Operational means in use for its stated purpose, with known limits. It is not evidence of learning outcomes."),
        "facts": [
            {"term": "Dimension", "value": L("Smart · العقل")},
            {"term": "Currency code", "value": "CTC"},
            {"term": "SelfAI module", "value": "SelfAI-S"},
            {"term": "Books in the draft index", "value": "3"},
            {"term": "Published roadmap year", "value": "2025"},
        ],
        "link": {"label": L("to be confirmed"), "href": "", "state": ""},
    },
    {
        "slug": "be-honester",
        "order": 11,
        "layer": L3,
        "status": "building",
        "evidence": "E1",
        "stage": 56,
        "horizon": "macro",
        "parent": "2030b",
        "group": L("2030B · dimension 2", "2030B · dimension 2", "2030B · البُعد الثاني"),
        "title": L("Be Honester", "Be Honester", "كن أصدق"),
        "subtitle": L("Honest · الصدق", "Honest · الصدق", "الصدق · الصدق"),
        "short": L("Building (2026 in the published roadmap)."),
        "exists": L("Building (2026 in the published roadmap)."),
        "missing": L("No public product page or book release is confirmed."),
        "idea": L("The second 2030B dimension, with currency code TIC and SelfAI module SelfAI-H."),
        "limits": L("The project name bends English on purpose; the misspelling is deliberate."),
        "facts": [
            {"term": "Dimension", "value": L("Honest · الصدق")},
            {"term": "Currency code", "value": "TIC"},
            {"term": "SelfAI module", "value": "SelfAI-H"},
            {"term": "Books in the draft index", "value": "3"},
            {"term": "Published roadmap year", "value": "2026"},
        ],
        "link": {"label": L("to be confirmed"), "href": "", "state": ""},
    },
]

# The remaining 2030B dimensions share one shape; only the values differ.
DIMENSIONS = [
    ("be-healthier", "Be Healthier", "كن أصح", "Healthy", "الصحة", "VTC", "SelfAI-B", 2027, 3, 12),
    ("be-creater", "Be Creater", "كن أبدع", "Creative", "الإبداع", "INC", "SelfAI-C", 2028, 4, 13),
    ("be-kinder", "Be Kinder", "كن أرحم", "Kind", None, "SCC", "SelfAI-K", 2029, 5, 14),
    ("be-braver", "Be Braver", "كن أشجع", "Brave", "الشجاعة", "WPC", "SelfAI-V", None, 6, 15),
    ("be-wiser", "Be Wiser", "كن أحكم", "Wise", "الحكمة", "WDC", "SelfAI-W", None, 7, 16),
    ("be-fairer", "Be Fairer", "كن أعدل", "Fair", "العدل", "JEC", "SelfAI-J", None, 8, 17),
    ("be-freer", "Be Freer", "كن أحرر", "Free", "الحرية", "FLC", "SelfAI-F", None, 9, 18),
    ("be-grateful", "Be Grateful", "كن أشكر", "Grateful", "الشكر", "GRC", "SelfAI-G", None, 10, 19),
]

for slug, en, ar, dim_en, dim_ar, currency, module, year, index, order in DIMENSIONS:
    PROJECTS.append(
        {
            "slug": slug,
            "order": order,
            "layer": L3,
            "status": "planned",
            "evidence": "E1",
            "stage": 22,
            "horizon": "macro",
            "parent": "2030b",
            "group": L(f"2030B · dimension {index}", f"2030B · dimension {index}", f"2030B · البُعد {index}"),
            "title": L(en, en, ar),
            "subtitle": L(f"{dim_en} · {dim_ar or 'to be confirmed'}", f"{dim_en} · {dim_ar or 'à confirmer'}", f"{ar} · {dim_ar or 'سيُحدَّد لاحقًا'}"),
            "short": L("Planned. Book index drafted."),
            "exists": L("Planned. Book index drafted."),
            "missing": L("No public product. The book index exists as a draft only, and the schedule has not been supplied."),
            "idea": L(f"The {index}th 2030B dimension, with currency code {currency} and SelfAI module {module}."),
            "limits": L("Planned means intended and named, with no build that can be shown."),
            "facts": [
                {"term": "Dimension", "value": L(f"{dim_en} · {dim_ar or 'to be confirmed'}")},
                {"term": "Currency code", "value": currency},
                {"term": "SelfAI module", "value": module},
                {"term": "Books in the draft index", "value": "3"},
                {"term": "Published roadmap year", "value": str(year) if year else L("to be confirmed")},
            ],
            "link": {"label": L("to be confirmed"), "href": "", "state": ""},
        }
    )

PROJECTS += [
    {
        "slug": "kon-aalam",
        "order": 20,
        "layer": L3,
        "status": "building",
        "evidence": "E1",
        "stage": 45,
        "horizon": "macro",
        "parent": "2030b",
        "group": L("2030B · eleventh project", "2030B · onzième projet", "2030B · المشروع الحادي عشر"),
        "title": L("كن أعلم", "كن أعلم", "كن أعلم"),
        "subtitle": L("The eleventh project.", "Le onzième projet.", "المشروع الحادي عشر."),
        "short": L("Under construction; not publicly listed on the 2030B site."),
        "exists": L("Under construction; not publicly listed on the 2030B site."),
        "missing": L("No public page, book index or product is published on the 2030B site."),
        "idea": L("\"Be more knowing\": an Arabic-language knowledge programme. Its scope beyond the name has not been supplied."),
        "limits": L("The project is named and under construction. No further claim is made about it."),
        "link": {"label": L("to be confirmed"), "href": "", "state": ""},
    },
    {
        "slug": "webbooks",
        "order": 21,
        "layer": L3,
        "status": "draft",
        "evidence": "E1",
        "stage": 44,
        "horizon": "macro",
        "parent": "2030b",
        "group": L("2030B · publishing", "2030B · édition", "2030B · النشر"),
        "title": L("WebBooks", "WebBooks", "WebBooks"),
        "subtitle": L("A reader in the repository.", "Un lecteur dans le dépôt.", "قارئ في المستودع."),
        "short": L("A reader page exists in the repository. No public reader URL is confirmed."),
        "exists": L("A reader page exists in the repository. No public reader URL is confirmed."),
        "missing": L("No confirmed public reader URL. No catalogue, licence or distribution arrangement is published."),
        "idea": L("WebBooks is the publication layer for the 2030B books. It connects to the registered service Publishing & WebBooks (#009)."),
        "limits": L("A page in a repository is a draft, not a product."),
        "link": {"label": L("to be confirmed"), "href": "", "state": ""},
    },
    {
        "slug": "departments-registry",
        "order": 22,
        "layer": L3,
        "status": "draft",
        "evidence": "E1",
        "stage": 44,
        "horizon": "macro",
        "parent": "2030b",
        "group": L("2030B · organisation", "2030B · organisation", "2030B · التنظيم"),
        "title": L("2030B departments registry", "Registre des départements 2030B", "سجل أقسام 2030B"),
        "subtitle": L("Sixteen departments documented by slug.", "Seize départements documentés par slug.", "ستة عشر قسمًا موثّقًا بالمعرّف."),
        "short": L("Sixteen departments are documented by slug; names, levels and mandates are not yet published here."),
        "exists": L("Sixteen departments are documented by slug; names, levels and mandates are not yet published here."),
        "missing": L("Names, levels and mandates for the 16 departments have not been supplied."),
        "idea": L("The administrative structure around the 2030B ontology. The count is 16 and is stored as a variable in config/variables.php."),
        "limits": L("A slug is not a mandate. The registry lists identifiers only."),
        "link": {"label": L("to be confirmed"), "href": "", "state": ""},
    },
    {
        "slug": "metax-academy-site",
        "order": 23,
        "layer": L3,
        "status": "live",
        "evidence": "E2",
        "stage": 100,
        "horizon": "meta",
        "parent": "metax-academy",
        "group": L("MetaX Academy · public site", "MetaX Academy · site public", "أكاديمية ميتاكس · الموقع"),
        "title": L("MetaX Academy site", "Site MetaX Academy", "موقع أكاديمية ميتاكس"),
        "subtitle": L("Nine pillars, licence ML-2.3, 25 harms.", "Neuf piliers, licence ML-2.3, 25 préjudices.", "تسع ركائز، رخصة ML-2.3، 25 ضررًا."),
        "short": L("Live: nine pillars, licence ML-2.3 and a register of 25 structural harms."),
        "exists": L("Live: nine pillars, licence ML-2.3 and a register of 25 structural harms."),
        "missing": L("Enrolment, accreditation and pricing are not published on the site."),
        "idea": L("The public artefact that makes MetaX Academy checkable: its pillars, its licence and its harms register are all reachable."),
        "limits": L("Observed live on the verification date. Observation is not endorsement."),
        "link": {"label": L("metax.academy"), "href": "https://metax.academy/", "state": "live"},
    },
    {
        "slug": "macrolifetech",
        "order": 24,
        "layer": PILLAR,
        "status": "research-preview",
        "evidence": "E2",
        "stage": 50,
        "horizon": "macro",
        "parent": "metax-academy",
        "group": L("MetaX · pillar", "MetaX · pilier", "ميتاكس · ركيزة"),
        "title": L("MacroLifeTech", "MacroLifeTech", "MacroLifeTech"),
        "subtitle": L("The macro research umbrella.", "L'ombrelle de recherche macro.", "مظلّة البحث الكلي."),
        "short": L("A research-preview umbrella on MetaX; three hub groups are research-preview and two are gated."),
        "exists": L("A research-preview umbrella on MetaX; three of its hub groups are research-preview and two are gated."),
        "missing": L("Findings from the hub groups have not been published."),
        "idea": L("MacroLifeTech (Meta-X) is the systemic layer: technology understood as a large-scale ensemble rather than an isolated artefact."),
        "limits": L("A research preview is open for scrutiny, with claims dated and limits declared. It is not settled doctrine."),
        "link": {"label": L("to be confirmed"), "href": "", "state": ""},
    },
    {
        "slug": "bcia",
        "order": 25,
        "layer": L3,
        "status": "gated",
        "evidence": "E1",
        "stage": 30,
        "horizon": "beyond",
        "parent": "macrolifetech",
        "group": L("Research · beyond", "Recherche · au-delà", "البحث · ما بعد"),
        "title": L("BCIA", "BCIA", "BCIA"),
        "subtitle": L("Computation in living substrates.", "Le calcul dans des substrats vivants.", "الحوسبة في ركائز حيّة."),
        "short": L("A gated, pre-publication research programme. No findings are reported."),
        "exists": L("A gated, pre-publication research programme. No findings are reported."),
        "missing": L("No findings, methods, protocol or ethics review is published."),
        "idea": L("BCIA is described as the first research programme from the beyond: the layer of conditions and intelligences from which technology emerges."),
        "limits": L(
            "Earlier wording called BCIA a research \"achievement\". MetaX reports no findings, so this site says programme. Work on living substrates raises consent and harm questions that the register treats as sensitive."
        ),
        "correction": L("Corrected before publication: \"first research achievement\" → \"first research programme\"."),
        "link": {"label": L("to be confirmed"), "href": "", "state": ""},
    },
    {
        "slug": "metax-estates",
        "order": 26,
        "layer": L3,
        "status": "building",
        "evidence": "E0",
        "stage": 45,
        "horizon": "macro",
        "parent": "metax-academy",
        "group": L("MetaX · estates", "MetaX · estates", "ميتاكس · العقارات"),
        "title": L("MetaX Estates", "MetaX Estates", "MetaX Estates"),
        "subtitle": L("Estate websites for Tunisian developers.", "Sites immobiliers pour promoteurs tunisiens.", "مواقع عقارية للمطوّرين التونسيين."),
        "short": L("Described only. estates.metax.academy did not resolve on the verification date."),
        "exists": L("Described only. estates.metax.academy did not resolve on the verification date."),
        "missing": L("The subdomain did not resolve. No developer site, listing or deliverable is public."),
        "idea": L("Estate websites for Tunisian developers, with 26 developers declared as the delivery footprint."),
        "limits": L("This entry carries E0: the claim is stated and nothing checkable is shown. The Studio's Estates service is related but makes no claim about these sites."),
        "link": {"label": L("estates.metax.academy"), "href": "", "state": "did not resolve"},
    },
    {
        "slug": "meridian",
        "order": 27,
        "layer": L3,
        "status": "specification",
        "evidence": "E1",
        "stage": 60,
        "horizon": "macro",
        "parent": "kernid",
        "group": L("Infrastructure · identity", "Infrastructure · identité", "البنية التحتية · الهوية"),
        "title": L("Meridian", "Meridian", "Meridian"),
        "subtitle": L("The system of record.", "Le système d'enregistrement.", "نظام السجل."),
        "short": L("A specification. The repository is not public."),
        "exists": L("A specification (system of record). The repository is not public."),
        "missing": L("No public repository, implementation or audit."),
        "idea": L("Meridian is described as the system of record in the identity infrastructure, alongside Aevum as the system of engagement."),
        "limits": L("Written down as a design. No public implementation is claimed."),
        "link": {"label": L("to be confirmed"), "href": "", "state": ""},
    },
    {
        "slug": "aevum",
        "order": 28,
        "layer": L3,
        "status": "specification",
        "evidence": "E1",
        "stage": 60,
        "horizon": "macro",
        "parent": "kernid",
        "group": L("Infrastructure · identity", "Infrastructure · identité", "البنية التحتية · الهوية"),
        "title": L("Aevum", "Aevum", "Aevum"),
        "subtitle": L("The system of engagement.", "Le système d'engagement.", "نظام التفاعل."),
        "short": L("A specification. The repository is not public."),
        "exists": L("A specification (system of engagement). The repository is not public."),
        "missing": L("No public repository, implementation or audit."),
        "idea": L("Aevum is described as the system of engagement in the identity infrastructure, at civilisational economic scale."),
        "limits": L("Written down as a design. No public implementation is claimed."),
        "link": {"label": L("to be confirmed"), "href": "", "state": ""},
    },
    {
        "slug": "w5d-uuid",
        "order": 29,
        "layer": L3,
        "status": "research-preview",
        "evidence": "E1",
        "stage": 67,
        "horizon": "meta",
        "parent": "kernid",
        "group": L("Infrastructure · identity", "Infrastructure · identité", "البنية التحتية · الهوية"),
        "title": L("W5D UUID", "W5D UUID", "W5D UUID"),
        "subtitle": L("An identifier that carries its own digital DNA.", "Un identifiant porteur de son propre ADN numérique.", "معرّف يحمل حمضه النووي الرقمي."),
        "short": L("A research direction described in a founder declaration. No specification is published."),
        "exists": L("A research direction described in a founder declaration. No specification is published."),
        "missing": L("No specification, reference implementation or storage and transfer measurements are published."),
        "idea": L(
            "A conventional UUID is 128 bits that point at an object. The W5D UUID is described as a long-form identifier, possibly 8 to 16 MB, whose embedded structure turns the identifier from a pointer into a genome."
        ),
        "limits": L(
            "An identifier of 8 to 16 MB has storage, transfer, indexing and verification costs that have not been measured. It is unrelated to the standard UUID formats in RFC 9562 and must not be used where a 16-byte UUID is expected."
        ),
        "link": {"label": L("to be confirmed"), "href": "", "state": ""},
    },
]


def build(project: dict) -> dict:
    facts = project.get("facts") or []
    record_items = [
        {"term": "Status", "chip": project["status"]},
        {"term": "Evidence", "value": project["evidence"], "badge": True},
        {"term": "Layer", "value": {"L2": "Programme", "L3": "Project", "pillar": "Pillar"}[project["layer"]]},
        {"term": "Horizon", "value": project["horizon"]},
        {"term": "Dimension", "value": project.get("dimension", L("to be confirmed"))},
        {"term": "Last verified", "value": "{{ time.registry_verified }}"},
    ] + facts

    link = project.get("link") or {}
    link_label = link.get("label")
    link_href = link.get("href") or ""
    if link_href and not str(link_href).startswith("http"):
        link_href = ""

    blocks = [
        {
            "type": "hero",
            "id": "top",
            "data": {
                "eyebrow": ("Ecosystem · " + ("L2 programme" if project["layer"] == L2 else "L3 project" if project["layer"] == L3 else "Pillar")),
                "grand": project["title"],
                "lead": project.get("subtitle"),
                "split": True,
                "meta": [
                    {"label": "Status", "chip": project["status"]},
                    {"label": "Evidence", "value": project["evidence"], "sub": "observed on the verification date" if project["evidence"] == "E2" else None},
                    {"label": "Layer", "value": {"L2": "L2", "L3": "L3", "pillar": "Pillar"}[project["layer"]]},
                    {"label": "Last verified", "value": "{{ time.registry_verified }}"},
                ],
                "actions": [
                    {"label": "Status register", "href": "page:status"},
                    {"label": "The ecosystem", "href": "page:ecosystem"},
                ],
            },
        },
        prose(
            title="What it is meant to be",
            body=[project["idea"]],
        ),
        definitions(title="Register record", items=record_items),
        {
            "type": "table",
            "title": "What exists, and what does not",
            "data": {
                "head": ["", ""],
                "rows": [
                    [{"badge": "✓"}, project["exists"]],
                    [{"badge": "○"}, project["missing"]],
                ],
                "compact": True,
            },
        },
        note_text(title="Declared limits", body=[project["limits"]]),
    ]

    if project.get("equation"):
        blocks.insert(2, prose(title="The equation", body=[{"type": "code", "text": project["equation"]}]))

    if project.get("correction"):
        blocks.append(note_text(title="Correction", body=[project["correction"]], tone="info"))

    if link_label:
        blocks.append(
            definitions(
                title="Link",
                items=[
                    {
                        "term": "Public address",
                        "value": link_label,
                        "href": link_href if link_href else None,
                        "open": not link_href,
                    },
                    {"term": "State", "value": link.get("state") or L("to be confirmed"), "open": not link.get("state")},
                ],
            )
        )

    blocks.append(
        cards(
            title="Elsewhere in the register",
            items=[
                {"title": "The status register", "summary": "Every programme and project, with its status word and dated verification.", "href": "page:status"},
                {"title": "The ecosystem", "summary": "Everything W5D is building, at its true status.", "href": "page:ecosystem"},
                {"title": "Corrections", "summary": "What this site changed, when and why.", "href": "page:corrections"},
            ],
            columns=3,
        )
    )
    blocks.append(
        cta(
            "Read a claim here, then check it. If something is wrong, file a correction.",
            [
                {"label": "File a correction", "href": "page:contact", "primary": True},
                {"label": "How verification works", "href": "page:status"},
            ],
        )
    )

    return {
        "slug": project["slug"],
        "order": project["order"],
        "layer": project["layer"],
        "status": project["status"],
        "evidence": project["evidence"],
        "stage": project["stage"],
        "horizon": project["horizon"],
        "group": project["group"],
        "parent": project.get("parent"),
        "audience": "public",
        "nav_group": "ecosystem",
        "kind": "detail",
        "title": project["title"],
        "subtitle": project["subtitle"],
        "short": project["short"],
        "summary": project["short"],
        "description": project["subtitle"],
        "exists": project["exists"],
        "missing": project["missing"],
        "verified": "{{ time.registry_verified }}",
        "blocks": blocks,
    }


def main() -> None:
    seen = set()
    for project in PROJECTS:
        if project["slug"] in seen:
            raise SystemExit(f"Duplicate project slug: {project['slug']}")
        seen.add(project["slug"])
        write("projects", project["slug"], build(project))
    print(f"projects: wrote {len(PROJECTS)} records")
    if len(PROJECTS) != 25:
        raise SystemExit(f"Expected 25 register entries, got {len(PROJECTS)}")


if __name__ == "__main__":
    main()
