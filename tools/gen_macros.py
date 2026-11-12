#!/usr/bin/env python3
"""Generate the five macro-work records.

Output: data/content/macros/{slug}.json

Each macro file owns its summary, its attribute table, its declared boundary and
its own service list — derived from data/services.md through gen_services, so the
macro page and the catalogue can never disagree about which service belongs where.

Usage:  python3 tools/gen_macros.py
"""
from __future__ import annotations

from gen_common import L, cards, cta, definitions, note_text, prose, write
from gen_services import MACROS, MACRO_CONTEXT, parse_services

MACRO_META = {
    "cultures": {
        "order": 1,
        "placement": "real",
        "question": L(
            "How do societies remember, interpret, translate and transmit meaning?",
            "Comment les sociétés se souviennent-elles, interprètent-elles, traduisent-elles et transmettent-elles le sens ?",
            "كيف تتذكّر المجتمعات المعنى وتفسّره وتترجمه وتنقله؟",
        ),
        "fields": L("archaeology, linguistics, museum studies, translation, film preservation, conservation, cultural economics"),
        "feeds": L("Built Environment (heritage-built), Sciences (research data), Governance (municipal memory)"),
        "range": L("001–026"),
        "intro": L(
            "This macro is not an IT-only demand map. Heritage conservation combines materials science, building safety and digital access, and translation hubs feed science communication and digital governance.",
        ),
    },
    "built": {
        "order": 2,
        "placement": "real",
        "question": L(
            "How are shelter, infrastructure and inhabited space designed, delivered and maintained?",
            "Comment l'abri, l'infrastructure et l'espace habité sont-ils conçus, livrés et entretenus ?",
            "كيف يُصمَّم المأوى والبنية التحتية والفضاء المسكون ويُسلَّم ويُصان؟",
        ),
        "fields": L("civil engineering, architecture, urban planning, surveying, building services, hydraulics"),
        "feeds": L("Sciences (EIA, climate modelling), Governance (municipal capacity, utility regulation), Technology (BIM, renewable systems)"),
        "range": L("027–052"),
        "intro": L(
            "This macro carries the heaviest physical-infrastructure load and the most explicit dependence on measured water losses and building-safety findings.",
        ),
    },
    "sciences": {
        "order": 3,
        "placement": "between",
        "question": L(
            "How is reliable knowledge produced, measured, validated and translated into decision-grade evidence?",
            "Comment un savoir fiable est-il produit, mesuré, validé et traduit en preuve utile à la décision ?",
            "كيف يُنتَج العلم الموثوق ويُقاس ويُتحقَّق منه ويُحوَّل إلى دليل صالح للقرار؟",
        ),
        "fields": L("epidemiology, analytical chemistry, agronomy, marine biology, materials engineering, metrology, biostatistics"),
        "feeds": L("every other macro, most critically Governance (health, climate, utility regulation) and Technology (biotech, medical devices, renewables)"),
        "range": L("053–078"),
        "intro": L(
            "The 'Between 1.0' placement reflects the macro's laboratory-and-field hybrid character: many outputs are data and method, but samples, field stations and accredited facilities are load-bearing.",
        ),
    },
    "governance": {
        "order": 4,
        "placement": "between",
        "question": L(
            "How are rules made, legitimate decisions taken, public money allocated and accountability maintained?",
            "Comment les règles sont-elles faites, les décisions légitimes prises, l'argent public alloué et la responsabilité maintenue ?",
            "كيف تُصنع القواعد وتُتخذ القرارات المشروعة وتُخصَّص الأموال العامة وتُصان المساءلة؟",
        ),
        "fields": L("law, public finance, regulatory economics, statistics, public administration, data protection"),
        "feeds": L("Technology (digital services, data protection), Sciences (statistics, evaluation), Built Environment (procurement, zoning)"),
        "range": L("079–104"),
        "intro": L(
            "Governance services advise, draft and evaluate. They never exercise a public power that belongs to an elected or statutory body.",
        ),
    },
    "technology": {
        "order": 5,
        "placement": "virtual-core",
        "question": L(
            "How are systems engineered, deployed and operated at scale?",
            "Comment les systèmes sont-ils conçus, déployés et exploités à l'échelle ?",
            "كيف تُهندَس الأنظمة وتُنشَر وتُشغَّل على نطاق واسع؟",
        ),
        "fields": L("software engineering, data engineering, infrastructure, cybersecurity, industrial systems"),
        "feeds": L("every other macro: the delivery layer for data, publishing, measurement and public services"),
        "range": L("105–130"),
        "intro": L(
            "Technology is the delivery layer, not the whole ontology. Its services are held to the same rule as the rest: every claim is dated, and a draft is never presented as a product.",
        ),
    },
}


def build(record: dict, all_records: list[dict]) -> dict:
    slug = record["macro"]
    meta = MACRO_META[slug]
    context = MACRO_CONTEXT[slug]
    mine = [item for item in all_records if item["macro"] == slug]

    service_cards = [
        {
            "title": item["title"],
            "summary": item["short"],
            "href": "service:" + item["slug"],
            "meta": [{"label": "No.", "value": f'{item["number"]:03d}'}],
        }
        for item in mine
    ]

    blocks = [
        {
            "type": "hero",
            "id": "top",
            "data": {
                "eyebrow": "Macro work · " + meta["range"]["en"],
                "grand": record["title"],
                "lead": meta["question"],
                "split": True,
                "meta": [
                    {"label": "Placement", "value": record["placement_raw"]},
                    {"label": "Registered services", "value": str(len(mine))},
                    {"label": "Service numbers", "value": meta["range"]},
                    {"label": "Designed capacity", "var": "counts.service_capacity"},
                ],
            },
        },
        prose(title="The domain question", lead=None, body=[meta["question"], meta["intro"]]),
        definitions(
            title="Macro record",
            items=[
                {"term": "Placement", "value": record["placement_raw"]},
                {"term": "Registered services", "value": str(len(mine))},
                {"term": "Expert fields", "value": meta["fields"]},
                {"term": "Characteristic output unit", "value": context["unit"]},
                {"term": "Feeds", "value": meta["feeds"]},
            ],
        ),
        prose(
            title="How the services relate",
            body=[
                {"type": "p", "text": meta["intro"]},
                {"type": "ul", "items": [
                    "Each service below is a registered entry with its own page, placement and declared boundary.",
                    "The list is generated from the service records, so adding or retiring a service updates this page on the next build.",
                    "No service page claims more than its own record states.",
                ]},
            ],
        ),
        cards(
            title="Registered services",
            items=service_cards,
            columns=2,
        ),
        note_text(title="Declared limits", body=[context["boundary"]]),
        cta(
            "The whole register is one artefact. Read it once, and every macro page is a view of the same data.",
            [
                {"label": "All {{ counts.services }} services", "href": "page:services", "primary": True},
                {"label": "The status register", "href": "page:status"},
            ],
        ),
    ]

    return {
        "slug": slug,
        "order": meta["order"],
        "placement": record["placement"],
        "status": "building",
        "audience": "public",
        "nav_group": "services",
        "kind": "detail",
        "title": record["title"],
        "short": record["short"],
        "summary": record["short"],
        "description": meta["question"],
        "blocks": blocks,
    }


def main() -> None:
    records = parse_services()
    by_macro = {}
    for item in records:
        by_macro.setdefault(item["macro"], []).append(item)

    for slug, info in MACROS.items():
        first = by_macro[slug][0]
        record = {
            "macro": slug,
            "title": info["title"],
            "short": info["short"],
            "placement": MACRO_META[slug]["placement"],
            "placement_raw": first["placement_raw"],
        }
        write("macros", slug, build(record, records))

    print(f"macros: wrote {len(MACROS)} records")


if __name__ == "__main__":
    main()
