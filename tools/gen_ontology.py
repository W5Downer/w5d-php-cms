#!/usr/bin/env python3
"""Generate the six-layer stacked ontology (§4) as first-class content.

Writes:
  data/content/layers/{key}.json    — one file per layer, L0 … L5
  data/content/pages/ontology.json  — the stack page that draws all six

The governing sentence, repeated verbatim in the page and in the docs:

  Projects instantiate Meta Solutions; Meta Solutions operate on Macro Works;
  Macro Works contain Services; Services are delivered by Expert Teams;
  Academies form the competence that Expert Teams require.

Run:  python3 tools/gen_ontology.py
"""
from __future__ import annotations

import os
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from gen_common import (  # noqa: E402
    L, action, cards, cta, definitions, kpis, notice, prose, steps, table, write,
)

# ── Layer order, bottom (L0) to top (L5) ───────────────────────────────────
# Each tuple: key, number, English title, French, Arabic, question, body,
#             count token, examples
LAYERS = [
    (
        "academies", 0, "Academies", "Académies", "الأكاديميات",
        "Who forms the competence?",
        "MetaX Academy and the Dyon certification profiles. Academies produce the assessed competence that expert teams consume: entry competence before assignment, continuing competence throughout.",
        "{{ counts.pillars.value }} pillars · certification profiles proposed",
        ["MetaX Academy", "Dyon certification profiles", "Continuing modules"],
        "academies",
    ),
    (
        "expert-teams", 1, "Expert Teams", "Équipes d'experts", "فرق الخبراء",
        "Who does the work?",
        "The service-level delivery unit: one manager plus about ten experts, producing accepted outputs against a service-specific rubric. Not a cross-service entity.",
        "{{ counts.expert_teams_total.value }} teams · {{ caps.manager_experts_soft.value }} experts each",
        ["1 manager", "~10 experts", "Cluster leads (optional)"],
        "expert-teams",
    ),
    (
        "services", 2, "Services", "Services", "الخدمات",
        "What is commissioned?",
        "The atomic, contractible unit of work. Each service carries a canonical ID, a macro placement, a proposed Dyon profile, an expert degree-field cluster, a weekly deliverable set with evidence rules, and declared cross-macro edges.",
        "{{ counts.services.value }} services · {{ counts.services_per_macro.value }} per macro",
        ["#076 STEM Education", "#111 Digital Identity", "#105 Software Engineering"],
        "services",
    ),
    (
        "macro-works", 3, "Macro Works", "Œuvres macros", "الأعمال الكبرى",
        "What domain of accountability?",
        "The top-level partition of the demand space. Each macro answers a distinct domain question — meaning, shelter, knowledge, rules, deployable technique — and carries a distinct Real/Virtual placement.",
        "{{ counts.macros.value }} macros",
        ["Cultures", "Built Environment", "Sciences", "Governance", "Technology"],
        "macro-works",
    ),
    (
        "meta-solutions", 4, "Meta Solutions", "Méta-solutions", "الحلول الفوقية",
        "What coordinates across domains?",
        "Reusable instruments any service can invoke: identity, credentialing, workflow, evidence, sampling, audit, publication. Deliberately NOT a sixth macro — the meta layer sits above the macro partition so it never becomes a domain.",
        "{{ counts.meta_solutions.value }} instruments · cross-cutting",
        ["Dyon", "Weekly work engine", "Split DB / rollup", "Sampling", "KernID", "SelfAI"],
        "meta-solutions",
    ),
    (
        "projects", 5, "Projects", "Projets", "المشاريع",
        "What is being built, for whom, by when?",
        "A time-bound instantiation. A project may consume several services, invoke several meta-solutions, and produce a concrete artefact or programme. It is not the same kind of entity as a service.",
        "{{ counts.projects.value }} in the register",
        ["MetaX Academy", "KernID", "SelfAI", "كن أعلم", "WebBooks", "Studio", "2030B"],
        "projects",
    ),
]

# ── §4.5 — project → meta-solution → services consumed ─────────────────────
PROJECT_MAP = [
    (
        "metax-academy", "MetaX Academy", "academy-layer",
        "Academy and credentialing meta-layer",
        ["stem-education", "education-culture", "construction-training"],
        "STEM Education (#076), Education through Culture (#023), Construction Workforce Training (#039)",
    ),
    (
        "kernid", "KernID (concept)", "kernid",
        "Identity and credential meta-layer",
        ["digital-identity", "data-protection", "cultural-data"],
        "Digital Identity (#111), Data Protection (#084), Cultural Data Governance (#026)",
    ),
    (
        "selfai", "SelfAI (concept)", "selfai",
        "Self-training AI agent meta-layer",
        ["ai-ml", "platform-ai-governance", "bioinformatics"],
        "AI & Machine Learning (#109), Platform & AI Governance (#093), Bioinformatics (#060)",
    ),
    (
        "ontology-department", "Ontology Department", "ontology",
        "The five-macro / 130-service ontology itself",
        [],
        "Cross-cutting; no single service",
    ),
    (
        "kon-aalam", "كن أعلم (draft)", "knowledge-ar",
        "Arabic-language knowledge and capability meta-layer",
        ["translation-hubs", "science-comm", "education-governance"],
        "Translation Hubs (#006), Science Communication (#062), Education Governance (#098)",
    ),
    (
        "webbooks", "WebBooks", "publication",
        "Publication and dissemination meta-layer",
        ["publishing", "digital-humanities"],
        "Publishing & WebBooks (#009), Digital Humanities (#007)",
    ),
    (
        "studio", "Studio", "static-delivery",
        "Static-first delivery meta-layer",
        ["software-eng", "platform-eng", "cloud-infra"],
        "Software Engineering (#105), Platform Engineering (#112), Cloud & Infrastructure (#107)",
    ),
    (
        "2030b", "2030B", "long-horizon",
        "Long-horizon strategic frame",
        [],
        "All macros; no single service",
    ),
]


def stack_items() -> list[dict]:
    """The diagram items, top layer first so the page reads L5 → L0."""
    items = []
    for key, number, en, fr, ar, question, body, count, examples, _ in reversed(LAYERS):
        items.append({
            "layer": key,
            "number": number,
            "title": L(en, fr, ar),
            "question": L(question),
            "body": L(body),
            "count": count,
            "examples": examples,
            "href": "layer:" + key,
        })
    return items


def layer_page(key: str) -> dict:
    """One layer record: its own JSON, its own page, no shared config."""
    for entry in LAYERS:
        lkey, number, en, fr, ar, question, body, count, examples, _ = entry
        if lkey != key:
            continue

        blocks: list[dict] = [
            {
                "type": "hero",
                "id": "top",
                "data": {
                    "eyebrow": L("Ontology · L%d" % number),
                    "grand": L(en, fr, ar),
                    "lead": L(body),
                    "split": False,
                    "meta": [
                        {"label": L("Layer"), "value": "L%d" % number},
                        {"label": L("Question"), "value": question},
                    ],
                },
            },
        ]

        # The layer's own question, stated as the section that answers it.
        blocks.append(prose(
            title=L("The question this layer answers"),
            lead=L(question),
            body=[L(
                "%s This is why the layer exists: it answers a question no other layer answers, "
                "and it constrains the layer directly beneath it." % body
            )],
        ))

        blocks.append(definitions(
            title=L("Layer specification"),
            items=[
                {"term": L("Layer"), "value": "L%d" % number},
                {"term": L("Name"), "value": L(en, fr, ar)},
                {"term": L("Question"), "value": L(question)},
                {"term": L("Scale"), "value": count},
                {"term": L("Dependency"), "value": L(DEPENDENCY.get(lkey, ""))},
            ],
        ))

        # Where this layer sits in the whole stack.
        blocks.append({
            "type": "stack",
            "title": L("Where this layer sits"),
            "data": {
                "lead": L("The same six layers, every time. The current layer is marked."),
                "direction": "down",
                "items": stack_items(),
                "feedback": L(
                    "Feedback flows upward: acceptance data from a service team informs academy "
                    "curriculum; a project's delivery experience revises a meta-solution; a macro's "
                    "cross-dependency friction revises the ontology. The constitutive arrows, "
                    "however, point downward."
                ),
            },
        })

        blocks.append(note_body(lkey))

        write("layers", key, {
            "slug": key,
            "order": number,
            "ontology_layer": key,
            "status": LAYER_STATUS.get(lkey, "draft") if isinstance(LAYER_STATUS.get(lkey), str) else "draft",
            "audience": "public",
            "nav_group": "ecosystem",
            "kind": "detail",
            "title": L(en, fr, ar),
            "nav_label": L(en, fr, ar),
            "summary": L(question),
            "description": L(body),
            "blocks": blocks,
        })
        return {"key": key}
    raise SystemExit("unknown layer: " + key)


DEPENDENCY = {
    "academies": "Provides entry and continuing competence to Expert Teams. Nothing below can be assigned without it.",
    "expert-teams": "Delivers Services. A service without an assigned team is a design, not a delivery.",
    "services": "Contained by Macro Works. The contractible unit; no service exists outside a macro.",
    "macro-works": "Contains 26 services each. The domain of accountability.",
    "meta-solutions": "Operates across Services as instruments. Deliberately above the macro partition.",
    "projects": "Consumes Services and instantiates Meta Solutions. Downstream of both.",
}

LAYER_STATUS = {
    "academies": "live",
    "expert-teams": "specification",
    "services": "draft",
    "macro-works": "draft",
    "meta-solutions": "specification",
    "projects": "draft",
}


def note_body(key: str) -> dict:
    """The §4.7 caveat that belongs to each layer."""
    caveats = {
        "academies": (
            "Dyon enforcement is proposed, not verified.",
            "The README publishes no competency levels and no proof that service activation is "
            "blocked without certification. The Academy layer is live as a programme; the "
            "certification gate it is meant to feed is still a proposal.",
        ),
        "expert-teams": (
            "130,000 is a design equation, not a headcount.",
            "The 26 developers in Tunisia are the real delivery footprint today. The 1,000-experts-"
            "per-service figure is a design ceiling, not a hiring minimum, and places are filled "
            "only against approved work and available supervision.",
        ),
        "services": (
            "The register is an ontology, not a catalogue.",
            "Each service declares cross-macro input and feed edges. In a collapsed service-only "
            "graph those universal dependencies cannot be acyclic; only the time-unrolled delivery "
            "graph is certified acyclic.",
        ),
        "macro-works": (
            "Five macros partition the demand space; none is merely a label.",
            "A macro without services would be a heading. The 26 services per macro are what make "
            "the partition substantive, and each macro carries a distinct Real/Virtual placement.",
        ),
        "meta-solutions": (
            "Meta Solutions are instruments, not a sixth macro.",
            "Dyon, the weekly work engine, split databases with rollup-first dashboards and "
            "sampling are all cross-cutting. Adding them as a domain would break the partition, "
            "which is why the dependency runs Meta Solutions → Services and never the reverse.",
        ),
        "projects": (
            "\"Live\" does not mean 1,000 experts are employed.",
            "No project on the site is yet a financed hiring promise. \"Live\" means operational as "
            "a programme or a site. A project consumes services; it does not staff them by existing.",
        ),
    }
    title, body = caveats[key]
    return notice(
        title=L(title),
        body=[L(body)],
        tone="limits",
    )


def project_map_table() -> dict:
    return table(
        title=L("Projects, meta-solutions and the services they consume"),
        lead=L(
            "A project is not the same kind of entity as a service. A service is a repeatable unit "
            "of commissioned work; a project is a time-bound instantiation that may consume several "
            "services, invoke several meta-solutions and produce a concrete artefact or programme."
        ),
        head=[L("Project"), L("Meta-solution it instantiates"), L("Services it most directly consumes")],
        rows=[
            [row[1], row[3], row[5]] for row in PROJECT_MAP
        ],
        compact=True,
    )


def ontology_index_page() -> dict:
    """The /ontology/ page: the full stack in one view."""
    blocks: list[dict] = [
        {
            "type": "hero",
            "id": "top",
            "data": {
                "eyebrow": L("Ontology"),
                "grand": L("Six layers, one stack."),
                "lead": L(
                    "Projects instantiate Meta Solutions; Meta Solutions operate on Macro Works; "
                    "Macro Works contain Services; Services are delivered by Expert Teams; "
                    "Academies form the competence that Expert Teams require."
                ),
                "split": False,
            },
        },
        {
            "type": "stack",
            "id": "stack",
            "title": L("The full stack in one view"),
            "data": {
                "lead": L(
                    "Each layer answers a different question and constrains the one below it. "
                    "Read top-down: what is built, what coordinates it, what contains it, what is "
                    "commissioned, who delivers it, who is competent to deliver."
                ),
                "direction": "down",
                "items": stack_items(),
                "feedback": L(
                    "Feedback flows upward — a service team's acceptance data can inform academy "
                    "curriculum; a project's delivery experience can revise a meta-solution; a "
                    "macro's cross-dependency friction can revise the ontology. The constitutive "
                    "relationships — what makes what possible — flow downward."
                ),
                "note": L(
                    "Without academies, no assessed teams; without teams, no delivered services; "
                    "without services, no macro substance; without meta-solutions, no coherent "
                    "cross-service coordination; without projects, no concrete instantiation."
                ),
            },
        },
        kpis(
            title=L("The stack in figures"),
            items=[
                {"var": "counts.ontology_layers", "label": L("Layers")},
                {"var": "counts.macros", "label": L("Macro works")},
                {"var": "counts.services", "label": L("Services")},
                {"var": "counts.expert_teams_total", "label": L("Expert teams (design)")},
                {"var": "counts.meta_solutions", "label": L("Meta solutions")},
                {"var": "counts.projects", "label": L("Projects in the register")},
            ],
            note=L("Every figure is a variable in config/variables.php; none is typed into a page."),
        ),
        definitions(
            title=L("The dependency direction at each step"),
            lead=L("The arrows are not all one-directional in practice, but the constitutive ones are."),
            items=[
                {"term": "L0 → L1", "value": L("Academies → Expert Teams. Academies produce the assessed competence; teams consume it.")},
                {"term": "L1 → L2", "value": L("Services → Expert Teams (constitutive). The service is the work definition; the team is the accountable producer.")},
                {"term": "L2 → L3", "value": L("Macro Works → Services. No service exists outside a macro; no macro is merely a label without services.")},
                {"term": "L4 → L2", "value": L("Meta Solutions → Services (as instruments), not Services → Meta Solutions.")},
                {"term": "L5 → L4+L2", "value": L("Projects → Meta Solutions + Services. Projects are downstream of both.")},
            ],
        ),
        project_map_table(),
        steps(
            title=L("How to read the stack"),
            items=[
                {"title": L("Start at the top"), "body": L("A project is the only layer that answers \"what is being built, for whom, by when?\". Everything else is what makes that answer possible.")},
                {"title": L("Drop one layer and state what breaks"), "body": L("Remove a layer and the layer below loses its precondition. That test is how the ontology is checked, not how it is asserted.")},
                {"title": L("Keep the meta layer above the partition"), "body": L("Meta Solutions are instruments. If they became a sixth macro, the five-macro partition would stop being a partition.")},
                {"title": L("Read the caveats as conditions"), "body": L("The load-bearing gaps below are what make this relationship credible rather than aspirational.")},
            ],
        ),
        notice(
            title=L("The load-bearing gaps"),
            body=[L("These are not footnotes. They are the conditions under which the stack is credible.")],
            items=[
                {"title": L("130,000 is a design equation, not a headcount."), "body": L("The website's 26 developers in Tunisia are the real delivery footprint today.")},
                {"title": L("Dyon enforcement is proposed, not verified."), "body": L("The README publishes no competency levels and no proof that activation is blocked without certification.")},
                {"title": L("5% sampling is a dossier recommendation, not a README rule."), "body": L("The README's leader schedule specifies {{ doctrine.sampling_readme_per_service.value }} sampled reports per service, which is {{ doctrine.sampling_readme_percent.value }} of a 1,000-expert team, not {{ doctrine.quality_sample.value }}.")},
                {"title": L("Universal dependencies cannot be acyclic."), "body": L("In a collapsed service-only graph the universal incoming/outgoing edges cannot be acyclic; only the time-unrolled delivery graph is certified acyclic.")},
                {"title": L("No project is yet a financed hiring promise."), "body": L("\"Live\" means operational as a programme or a site, not \"1,000 experts employed\".")},
            ],
            tone="limits",
        ),
        cards(
            title=L("Explore each layer"),
            items=[
                {
                    "title": L(en, fr, ar),
                    "summary": L(question),
                    "href": "layer:" + key,
                    "meta": [{"label": L("Layer"), "value": "L%d" % number}],
                }
                for key, number, en, fr, ar, question, body, count, examples, _ in LAYERS
            ],
            columns=3,
        ),
        cta(L("The stack is a check, not a claim. Each layer is falsifiable on its own page."), [
            action(L("Read the services register"), "services:index"),
            action(L("Read the project register"), "ecosystem:index", primary=True),
        ]),
    ]

    return {
        "id": "ontology",
        "path": "/ontology/",
        "kind": "index",
        "nav_group": "ecosystem",
        "audience": "public",
        "title": L("The Six-Layer Stack", "La pile à six couches", "الطبقات الست"),
        "nav_label": L("Ontology stack", "Pile ontologique", "البنية الطبقية"),
        "summary": L(
            "Academies form competence; Expert Teams deliver Services; Macro Works contain them; "
            "Meta Solutions coordinate across them; Projects instantiate the whole."
        ),
        "description": L(
            "The six feature labels are not a flat list. They form a stacked ontology in which each "
            "layer answers a different question and each layer constrains the one below it."
        ),
        "blocks": blocks,
    }


def main() -> None:
    for key, *_ in LAYERS:
        layer_page(key)
        print("wrote layer:", key)

    write("pages", "ontology", ontology_index_page())
    print("wrote page: ontology")


if __name__ == "__main__":
    main()
