#!/usr/bin/env python3
"""Generate one JSON file per registered service.

Source of truth: data/services.md (the canonical 130-service register).
Output:          data/content/services/{slug}.json  (130 files)

Each record carries its own data AND its own page config, so two services can
never contradict each other and the catalogue index is derived, never typed.

Usage:  python3 tools/gen_services.py
"""
from __future__ import annotations

import os
import re

from gen_common import CONTENT, ROOT, L, cards, definitions, note_text, prose, write

MACROS = {
    "cultures": {
        "title": L("Cultures / Humanities", "Cultures / Humanités", "الثقافات / الإنسانيات"),
        "short": L(
            "How societies remember, interpret, translate and transmit meaning.",
            "Comment les sociétés se souviennent, interprètent, traduisent et transmettent le sens.",
            "كيف تتذكّر المجتمعات المعنى وتفسّره وتترجمه وتنقله.",
        ),
    },
    "built": {
        "title": L("Built Environment", "Cadre bâti", "البيئة العمرانية"),
        "short": L(
            "How shelter, infrastructure and inhabited space are designed, delivered and maintained.",
            "Comment l'abri, l'infrastructure et l'espace habité sont conçus, livrés et entretenus.",
            "كيف يُصمَّم المأوى والبنية التحتية والفضاء المسكون ويُسلَّم ويُصان.",
        ),
    },
    "sciences": {
        "title": L("Sciences", "Sciences", "العلوم"),
        "short": L(
            "How reliable knowledge is produced, measured, validated and turned into decision-grade evidence.",
            "Comment un savoir fiable est produit, mesuré, validé et transformé en preuve décisionnelle.",
            "كيف يُنتَج العلم الموثوق ويُقاس ويُتحقَّق منه ويُحوَّل إلى دليل صالح للقرار.",
        ),
    },
    "governance": {
        "title": L("Governance", "Gouvernance", "الحوكمة"),
        "short": L(
            "How rules are made, legitimate decisions taken, public money allocated and accountability maintained.",
            "Comment les règles sont faites, les décisions légitimes prises, l'argent public alloué et la responsabilité maintenue.",
            "كيف تُصنع القواعد وتُتخذ القرارات المشروعة وتُخصَّص الأموال العامة وتُصان المساءلة.",
        ),
    },
    "technology": {
        "title": L("Technology", "Technologie", "التقنية"),
        "short": L(
            "How systems are engineered, deployed and operated at scale.",
            "Comment les systèmes sont conçus, déployés et exploités à l'échelle.",
            "كيف تُهندَس الأنظمة وتُنشَر وتُشغَّل على نطاق واسع.",
        ),
    },
}

PLACEMENTS = {
    "Real 1.0": "real",
    "Between 1.0": "between",
    "Real instantiation": "real",
    "Virtual core": "virtual-core",
    "Real 0.5 / Virtual 0.5": "virtual-core",
}

# One evidence sentence and one output unit per macro, so the 130 detail pages
# say something true about the kind of work rather than repeating boilerplate.
MACRO_CONTEXT = {
    "cultures": {
        "unit": L("accepted capture packages, annotated records, translated words"),
        "boundary": L(
            "Custodial permission and community consent precede publication. Dyon never substitutes for the statutory heritage authority."
        ),
    },
    "built": {
        "unit": L("design package reviews, asset inspections, survey kilometres"),
        "boundary": L(
            "Affordability includes operating cost, not sale price alone. A draft zoning layer stays advisory until the competent authority validates it."
        ),
    },
    "sciences": {
        "unit": L("accessioned samples, validated assay results, calibration certificates"),
        "boundary": L(
            "A research analysis does not become a clinical diagnosis without authorised validation, and every laboratory publishes its method limits and chain of custody."
        ),
    },
    "governance": {
        "unit": L("regulatory briefs, legislative drafts, published open-data sets"),
        "boundary": L(
            "This service advises and drafts. It does not exercise a public power that belongs to an elected or statutory body."
        ),
    },
    "technology": {
        "unit": L("commits, deployed services, verified uptime windows"),
        "boundary": L(
            "Nothing is deployed to a client environment without a documented rollback path and a named owner."
        ),
    },
}


def parse_services() -> list[dict]:
    source = open(os.path.join(ROOT, "data", "services.md"), encoding="utf-8").read()
    source = source.replace("\r\n", "\n")
    chunks = re.split(r"^### ", source, flags=re.M)[1:]
    records = []

    for chunk in chunks:
        lines = chunk.split("\n")
        header = lines[0].strip()
        match = re.match(r"^(\d{3})\s*·\s*(.+)$", header)
        if not match:
            continue
        number = int(match.group(1))
        title = match.group(2).strip()

        meta_line = ""
        for line in lines[1:]:
            if line.strip():
                meta_line = line.strip()
                break
        meta = re.match(r"^`([^`]+)`\s*·\s*\*\*([^*]+)\*\*\s*·\s*([a-z]+)", meta_line)
        if not meta:
            raise SystemExit(f"Cannot parse service meta: {header} → {meta_line!r}")
        slug, placement_raw, macro = meta.group(1), meta.group(2).strip(), meta.group(3)

        short = ""
        long = ""
        body = "\n".join(lines)
        short_match = re.search(r"\*\*Short:\*\*\s*(.+)", body)
        if short_match:
            short = short_match.group(1).strip()
        long_match = re.search(r"\*\*Long:\*\*\s*(.+?)(?:\n\n|\n---|\Z)", body, re.S)
        if long_match:
            long = re.sub(r"\s+", " ", long_match.group(1).strip())

        records.append(
            {
                "number": number,
                "title": title,
                "slug": slug,
                "placement": PLACEMENTS.get(placement_raw, "real"),
                "placement_raw": placement_raw,
                "macro": macro,
                "short": short,
                "long": long,
            }
        )

    records.sort(key=lambda r: r["number"])
    if len(records) != 130:
        raise SystemExit(f"Expected 130 services, parsed {len(records)}")
    return records


def build_record(service: dict, siblings: list[dict]) -> dict:
    macro = service["macro"]
    macro_title = MACROS[macro]["title"]
    context = MACRO_CONTEXT[macro]
    number_label = f'{service["number"]:03d}'

    adjacent = [
        item
        for item in siblings
        if item["macro"] == macro and item["number"] != service["number"]
    ]
    neighbours = []
    for item in adjacent[:3]:
        neighbours.append(
            {
                "title": item["title"],
                "summary": item["short"],
                "href": "service:" + item["slug"],
                "meta": [{"label": "No.", "value": f'{item["number"]:03d}'}],
            }
        )

    blocks = [
        {
            "type": "hero",
            "id": "top",
            "data": {
                "eyebrow": f'{number_label} · {macro_title["en"]}',
                "grand": service["title"],
                "lead": service["long"] or service["short"],
                "split": False,
                "meta": [
                    {"label": "Service number", "value": number_label},
                    {"label": "Macro work", "value": macro_title},
                    {"label": "Placement", "value": service["placement_raw"]},
                ],
            },
        },
        prose(
            title="What the service does",
            body=[
                {"type": "p", "text": service["long"] or service["short"]},
                {"type": "p", "text": service["short"]},
            ],
        ),
        definitions(
            title="Service record",
            items=[
                {"term": "Registered number", "value": number_label},
                {"term": "Macro work", "value": macro_title},
                {"term": "Placement", "value": service["placement_raw"]},
                {"term": "Designed capacity", "var": "counts.service_capacity"},
                {"term": "Output unit", "value": context["unit"]},
                {"term": "Publication status", "chip": "building"},
            ],
        ),
        prose(
            title="Outputs and boundaries",
            body=[
                {"type": "p", "text": context["unit"]},
                {"type": "ul", "items": [
                    "Deliverables are recorded against the weekly report as evidence, not as a claim.",
                    "Every figure published about this service is dated and carries its source.",
                ]},
            ],
        ),
        note_text(
            title="Declared limits",
            body=[context["boundary"]],
        ),
        cards(
            title="Other services in " + macro_title["en"],
            items=neighbours,
            columns=3,
        ),
        {
            "type": "cta",
            "data": {
                "text": "Read the macro work, or the whole register of {{ counts.services }} services.",
                "actions": [
                    {"label": "The macro work", "href": "macro:" + macro},
                    {"label": "All services", "href": "page:services", "primary": True},
                ],
            },
        },
    ]

    return {
        "slug": service["slug"],
        "number": service["number"],
        "macro": macro,
        "placement": service["placement"],
        "status": "building",
        "audience": "client",
        "nav_group": "services",
        "kind": "detail",
        "title": L(service["title"]),
        "short": L(service["short"]),
        "summary": L(service["short"]),
        "description": L(service["long"] or service["short"]),
        "order": service["number"],
        "blocks": blocks,
    }


def main() -> None:
    records = parse_services()
    for record in records:
        write("services", record["slug"], build_record(record, records))
    print(f"services: wrote {len(records)} records")


if __name__ == "__main__":
    main()
