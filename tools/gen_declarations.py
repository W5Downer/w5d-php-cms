#!/usr/bin/env python3
"""The seven founder declarations (index + records)."""
from gen_common import L, cta, note_text, prose, write

DECLS = [
    ("fifth-dimension", 1, "The Fifth Dimension I Seek",
     "Why time is not the fourth dimension, and what the fifth is.",
     "An ontological prolegomenon in the first person: the argument that time measures change rather than constituting a thing, and that Possibility/Modality is the horizon of the not-yet.",
     ["Time is a relational metric, not an ontological axis.", "The fourth dimension is Information/Structure: the architecture of the actual.", "The fifth is Possibility/Modality: the horizon of what is not yet but could be."]),
    ("meta-macro-beyond", 2, "The Official Wording of Meta, Macro, and Beyond",
     "Three operators in one framework.",
     "The wording that defines meta, macro and beyond, with the reasoning behind each and the errors each one avoids.",
     ["Meta is reflexive: a system aware of its own structure.", "Macro is systemic: technology as a large-scale ensemble.", "Beyond is generative: the layer from which technology emerges."]),
    ("kernid-aevum-meridian-uuid", 3, "The Mission of KernID, Aevum, Meridian, and UUID",
     "Identity, record, engagement, and a portable genome.",
     "Four infrastructure claims: one sign-in, a system of record, a system of engagement, and a long-form identifier.",
     ["KernID authenticates without taking custody of passwords or private keys.", "Meridian is the system of record; Aevum the system of engagement.", "The W5D UUID makes the identifier self-describing; its costs are unmeasured."]),
    ("nation-country", 4, 'How L4 "Nation" Differs from L6 "Country"',
     "Two orders of reality, not two scales.",
     "A position paper arguing that nation is generative and prior to borders, and that country is an administrative artefact.",
     ["Nation is generative and fifth-dimensional.", "Country is administrative and fourth-dimensional.", "The target of three macro-regional identities is a design goal, and falsifiable."]),
    ("2030b-principles", 5, "The 2030B Declaration",
     "Seven principles, ten dimensions, one complete human.",
     "The declaration behind 2030B: an equation of ten dimensions, one per project, and an eleventh under construction.",
     ["Complete Human = Smart + Honest + Healthy + Creative + Kind + Brave + Wise + Fair + Free + Grateful.", "The ten are not ranked.", "An earlier draft said eleven principles; the list states seven."]),
    ("bcia", 6, "The Beyond: Why BCIA Comes First",
     "The first research programme from the beyond.",
     "An argument that technology is a dependent entity, and that a research programme on living substrates belongs at the generative layer.",
     ["Technology does not self-originate.", "BCIA is a programme, not an achievement: no findings are reported.", "Work on living substrates requires a consent policy that is not yet published."]),
    ("w5d-uuid", 7, "The W5D UUID: an Identifier That Carries Its Genome",
     "A pointer that becomes a genome.",
     "A research direction: a long-form identifier of 8 to 16 MB that embeds structure, provenance and possibility.",
     ["A conventional UUID is 16 bytes and points.", "The W5D UUID is unrelated to RFC 9562 and must not be used where a 16-byte UUID is expected.", "Storage, transfer and verification costs are unmeasured."]),
]

for slug, n, title, grand, summary, points in DECLS:
    blocks = [
        {"type": "hero", "id": "top", "data": {
            "eyebrow": L("Declaration " + str(n) + " of 7"),
            "grand": L(grand),
            "lead": L(summary),
            "split": True,
        }},
        prose(L("In brief"), [{"type": "ul", "items": [L(p) for p in points]}]),
        prose(L("The text"), [{"type": "p", "text": L("The founder's full text is written in the first person and has not been reproduced here. This record states what the declaration claims, where it is used on this site, and what would refute it.")}]),
        note_text(L("Declared limits"), [L("A declaration is a working framework, not a final definition. Each claim above is stated so that a reader can check it and file a correction.")]),
        cta(L("Read the other declarations, or check the register.", ), [
            {"label": L("All declarations"), "href": "page:declarations", "primary": True},
            {"label": L("Status register"), "href": "page:status"},
        ]),
    ]
    data = {
        "slug": slug,
        "order": n,
        "audience": "public",
        "nav_group": "ecosystem",
        "kind": "detail",
        "title": L(title),
        "grand_title": L(grand),
        "summary": L(summary),
        "description": L(summary),
        "date": "2026-10-10",
        "version": "1.0",
        "blocks": blocks,
    }
    if slug == "nation-country":
        data["blocks"].insert(2, prose(L("The distinction"), [{"type": "p", "text": L("Nation is prior to any border; country is a bordered administrative artefact created by a political ideology. The claim that L4 is prior to L6 is ontological, not chronological.")}]))
    write("declarations", slug, data)
print("declarations: wrote", len(DECLS))
