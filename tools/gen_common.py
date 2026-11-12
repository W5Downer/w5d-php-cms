#!/usr/bin/env python3
"""Shared helpers for the content generators.

The generators write the JSON files under data/content/. They are NOT part of the
runtime: the site is built from the JSON, and the generators only exist so the
130 services and 25 register entries can be re-derived from source documents
(data/services.md, the dossiers) without hand-editing 200 files.

Run:  python3 tools/generate.py
"""
from __future__ import annotations

import json
import os
import re
from typing import Any

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
CONTENT = os.path.join(ROOT, "data", "content")


def write(kind: str, record_id: str, data: dict[str, Any]) -> str:
    """Write one content file, pretty-printed, key order preserved."""
    folder = os.path.join(CONTENT, kind)
    os.makedirs(folder, exist_ok=True)
    record_id = slugify(record_id)
    data = {"id": record_id, **data}
    path = os.path.join(folder, record_id + ".json")
    with open(path, "w", encoding="utf-8") as handle:
        json.dump(data, handle, ensure_ascii=False, indent=2)
        handle.write("\n")
    return path


def slugify(value: str) -> str:
    value = value.strip().lower()
    repl = {
        "à": "a", "á": "a", "â": "a", "ä": "a", "ã": "a", "å": "a",
        "é": "e", "è": "e", "ê": "e", "ë": "e",
        "í": "i", "ì": "i", "î": "i", "ï": "i",
        "ó": "o", "ò": "o", "ô": "o", "ö": "o", "õ": "o",
        "ú": "u", "ù": "u", "û": "u", "ü": "u",
        "ç": "c", "ñ": "n", "ß": "ss",
    }
    for src, dst in repl.items():
        value = value.replace(src, dst)
    value = re.sub(r"[^a-z0-9]+", "-", value)
    return value.strip("-")


def L(en: str, fr: str | None = None, ar: str | None = None) -> dict[str, str]:
    """A localised string. Missing languages fall back to English by design."""
    out = {"en": en}
    if fr:
        out["fr"] = fr
    if ar:
        out["ar"] = ar
    return out


def prose(title=None, lead=None, body=None, note=None, layout=None, aside=None):
    block: dict[str, Any] = {"type": "prose"}
    if title is not None:
        block["title"] = title
    data: dict[str, Any] = {}
    if aside is not None:
        data["aside"] = aside
    if lead is not None:
        data["lead"] = lead
    if body is not None:
        data["body"] = body if isinstance(body, list) else [body]
    if note is not None:
        data["note"] = note
    if layout is not None:
        data["layout"] = layout
    if data:
        block["data"] = data
    return block


def notice(title=None, body=None, items=None, tone="limits"):
    block: dict[str, Any] = {"type": "notice", "data": {"tone": tone}}
    if title is not None:
        block["title"] = title
    if body is not None:
        block["data"]["body"] = body if isinstance(body, list) else [body]
    if items is not None:
        block["data"]["items"] = items
    return block


def definitions(title, groups_or_items=None, lead=None, items=None):
    """Definitions list. Accepts a flat item list, a grouped list, or items=."""
    if items is not None:
        groups_or_items = items
    groups_or_items = groups_or_items or []
    block: dict[str, Any] = {"type": "definitions", "title": title}
    data: dict[str, Any] = {}
    if lead:
        data["lead"] = lead
    if groups_or_items and isinstance(groups_or_items[0], dict) and "items" in groups_or_items[0]:
        data["groups"] = groups_or_items
    else:
        data["items"] = groups_or_items
    block["data"] = data
    return block


def cards(title, items=None, source=None, lead=None, columns=3, numbered=False):
    block: dict[str, Any] = {"type": "cards", "title": title, "data": {}}
    if lead:
        block["data"]["lead"] = lead
    if source:
        block["data"]["source"] = source
    if items is not None:
        block["data"]["items"] = items
    if columns != 3:
        block["data"]["columns"] = columns
    if numbered:
        block["data"]["numbered"] = True
    return block


def table(title, head=None, rows=None, source=None, lead=None, note=None, compact=False, caption=None):
    block: dict[str, Any] = {"type": "table", "title": title, "data": {}}
    if head is not None:
        block["data"]["head"] = head
    if rows is not None:
        block["data"]["rows"] = rows
    if source:
        block["data"]["source"] = source
    if lead:
        block["data"]["lead"] = lead
    if note:
        block["data"]["note"] = note
    if compact:
        block["data"]["compact"] = True
    if caption:
        block["data"]["caption"] = caption
    return block


def kpis(title=None, items=None, group=None, lead=None, note=None):
    block: dict[str, Any] = {"type": "kpis", "data": {}}
    if title:
        block["title"] = title
    if lead:
        block["data"]["lead"] = lead
    if group:
        block["data"]["group"] = group
    if items is not None:
        block["data"]["items"] = items
    if note:
        block["data"]["note"] = note
    return block


def steps(title, items, lead=None, ordered=True):
    block: dict[str, Any] = {"type": "steps", "title": title, "data": {"items": items}}
    if lead:
        block["data"]["lead"] = lead
    if not ordered:
        block["data"]["ordered"] = False
    return block


def timeline(title, items, lead=None, open_note=None):
    block: dict[str, Any] = {"type": "timeline", "title": title, "data": {"items": items}}
    if lead:
        block["data"]["lead"] = lead
    if open_note:
        block["data"]["open"] = open_note
    return block


def cta(text, actions):
    return {"type": "cta", "data": {"text": text, "actions": actions}}


def action(label, href, primary=False):
    item = {"label": label, "href": href}
    if primary:
        item["primary"] = True
    return item


def quote(text, cite=None, source=None, href=None):
    data: dict[str, Any] = {"text": text}
    if cite:
        data["cite"] = cite
    if source:
        data["source"] = source
    if href:
        data["href"] = href
    return {"type": "quote", "data": data}


def bars(title, items, lead=None, max_value=None, note=None):
    block: dict[str, Any] = {"type": "bars", "title": title, "data": {"items": items}}
    if lead:
        block["data"]["lead"] = lead
    if max_value is not None:
        block["data"]["max"] = max_value
    if note:
        block["data"]["note"] = note
    return block


def carousel(title, slides, label=None):
    data: dict[str, Any] = {"slides": slides}
    if label:
        data["label"] = label
    return {"type": "carousel", "title": title, "data": data}


def note_text(title=None, body=None, items=None, tone="limits"):
    """A notice block aliased for readability in the generators."""
    return notice(title=title, body=body, items=items, tone=tone)
