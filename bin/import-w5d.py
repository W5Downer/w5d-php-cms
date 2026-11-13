#!/usr/bin/env python3
"""
Generates the service, macro, project and ontology-layer content files from the
repository sources. Run once per content revision, then review the diff:

    python3 bin/import-w5d.py

Sources (all inside the repository or recoverable from git history):
  data/services.md                              130 service records (title, slug, placement, short, long)
  data/W5D_From-One-Expert-...html              per-service problem / solution / buyer tables, macro sections
  data/macro-works-dossier.md                   macro attribute tables
  git show 16a1226:data/content/projects/*.json legacy project register (25 entries, statuses as recorded)
Output is written to data/content/ only. Nothing is deleted.
"""
import html, json, os, re, subprocess, sys

ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '..'))
os.chdir(ROOT)
CONTENT = os.path.join(ROOT, 'data', 'content')

def read(p):
    with open(p, encoding='utf-8') as f:
        return f.read().replace('\r\n', '\n')

def write_json(p, obj):
    os.makedirs(os.path.dirname(p), exist_ok=True)
    with open(p, 'w', encoding='utf-8') as f:
        json.dump(obj, f, ensure_ascii=False, indent=2)
        f.write('\n')

TODO_NOTE = 'fr/ar are placeholders copied from en; human translation pending'
MACROS = [
    ('cultures', 1, 'Cultures / Humanities', 'Real 1.0'),
    ('built', 2, 'Built Environment', 'Real 1.0'),
    ('sciences', 3, 'Sciences', 'Between 1.0'),
    ('governance', 4, 'Governance', 'Between 1.0'),
    ('technology', 5, 'Technology', 'Real 0.5 / Virtual 0.5'),
]

# ---------------------------------------------------------------- services.md
md = read('data/services.md')
svc = {}
for m in re.finditer(r'### (\d{3}) · (.+?)\n`([a-z0-9-]+)` · \*\*(.+?)\*\* · ([a-z]+)\n(.*?)(?=\n### |\Z)', md, re.S):
    num, title, slug, placement, macro, body = m.groups()
    short = re.search(r'\*\*Short:\*\* (.+)', body).group(1).strip()
    long_ = re.search(r'\*\*Long:\*\* (.+)', body).group(1).strip()
    svc[int(num)] = dict(number=int(num), title=title.strip(), slug=slug, placement=placement, macro=macro,
                         short=short, long=long_)
assert len(svc) == 130, f'services.md parsed {len(svc)} services, expected 130'

# ---------------------------------------------------------------- HTML report
rep = read('data/W5D_From-One-Expert-to-a-Sustainable-130000-Expert-Organization.html')
rows = re.findall(r'<tr><td>(\d{3}) · ([^<]+)</td><td>([^<]*)</td><td>([^<]*)</td><td>([^<]*)</td></tr>', rep)
assert len(rows) == 130, f'report service rows parsed {len(rows)}, expected 130'
mismatch = []
for num, title, problem, solution, buyer in rows:
    n = int(num)
    t = html.unescape(title).strip()
    if svc[n]['title'] != t:
        mismatch.append((n, svc[n]['title'], t))
    svc[n]['problem'] = html.unescape(problem).strip()
    svc[n]['solution'] = html.unescape(solution).strip()
    b = html.unescape(buyer).strip()
    if ':' in b:
        who, rest = b.split(':', 1)
        svc[n]['buyer'] = who.strip()
        svc[n]['acceptance_boundary'] = rest.strip()
    else:
        svc[n]['buyer'] = b
        svc[n]['acceptance_boundary'] = ''
if mismatch:
    print('TITLE MISMATCHES (services.md vs report):', mismatch)
    sys.exit('Resolve title mismatches before generating content.')

# ---------------------------------------------------------------- macro sections of the report
macro_text = {}
for m in re.finditer(r'<h3>4\.([1-5]) [^<]*</h3>(.*?)(?=<h3>|<h2)', rep, re.S):
    idx, body = int(m.group(1)), m.group(2)
    fields = {}
    for key, label in [('problem', 'Problem and reason'), ('premium_offer', 'Premium offer'),
                       ('delivery_economics', 'Delivery and economics')]:
        mm = re.search(r'<strong>' + label + r':</strong>(.*?)</p>', body, re.S)
        fields[key] = html.unescape(re.sub(r'<[^>]+>', '', mm.group(1))).strip() if mm else ''
    macro_text[MACROS[idx - 1][0]] = fields

# ---------------------------------------------------------------- macro dossier tables
dossier = read('data/macro-works-dossier.md')
macro_attr = {}
for part in re.split(r'\n### 2\.\d ', dossier)[1:]:
    name = part.split('\n')[0]
    for key, slug in [('Cultures', 'cultures'), ('Built', 'built'), ('Sciences', 'sciences'),
                      ('Governance', 'governance'), ('Technology', 'technology')]:
        if name.startswith(key):
            attrs = {}
            for r in re.finditer(r'\| \*\*(.+?)\*\* \| (.+?) \|', part):
                attrs[r.group(1).strip()] = r.group(2).strip().replace('**', '')
            macro_attr[slug] = attrs

# ---------------------------------------------------------------- write services
for n, s in sorted(svc.items()):
    rec = dict(
        id=s['slug'], slug=s['slug'], macro=s['macro'], placement=s['placement'],
        status='proposed', evidence='E0',
        title={'en': s['title'], 'fr': s['title'], 'ar': s['title']},
        short={'en': s['short'], 'fr': s['short'], 'ar': s['short']},
        long={'en': s['long'], 'fr': s['long'], 'ar': s['long']},
        problem={'en': s['problem'], 'fr': s['problem'], 'ar': s['problem']},
        solution={'en': s['solution'], 'fr': s['solution'], 'ar': s['solution']},
        likely_buyer={'en': s['buyer'], 'fr': s['buyer'], 'ar': s['buyer']},
        acceptance_boundary={'en': s['acceptance_boundary'], 'fr': s['acceptance_boundary'], 'ar': s['acceptance_boundary']},
        _todo=TODO_NOTE,
    )
    write_json(os.path.join(CONTENT, 'services', s['slug'] + '.json'), rec)

# ---------------------------------------------------------------- write macros
for slug, number, name, placement in MACROS:
    members = [{'number': n, 'slug': svc[n]['slug']} for n in sorted(svc) if svc[n]['macro'] == slug]
    assert len(members) == 26, f'{slug} has {len(members)} services'
    a = macro_attr.get(slug, {})
    t = macro_text.get(slug, {})
    rec = dict(
        id=slug, slug=slug, number=number, placement=placement,
        title={'en': name, 'fr': name, 'ar': name},
        domain_question={'en': a.get('Domain question', '')},
        typical_fields={'en': a.get('Typical expert fields', '')},
        output_unit={'en': a.get('Characteristic output unit', '')},
        recurring_boundary={'en': a.get('Recurring boundary', '')},
        problem={'en': t.get('problem', '')},
        premium_offer={'en': t.get('premium_offer', '')},
        delivery_economics={'en': t.get('delivery_economics', '')},
        services=members,
        _todo=TODO_NOTE,
    )
    write_json(os.path.join(CONTENT, 'macros', slug + '.json'), rec)

# ---------------------------------------------------------------- projects (legacy register at 16a1226)
SERVICES_CONSUMED = {  # from docs/ontology-six-layers.md §4.5 (service numbers)
    'metax-academy': [76, 23, 39], 'kernid': [111, 84, 26], 'selfai': [109, 93, 60],
    'kon-aalam': [6, 62, 98], 'webbooks': [9, 7],
}
legacy_files = subprocess.check_output(['git', 'ls-tree', '--name-only', '16a1226:data/content/projects/'], text=True).split()
projects = []
for fn in legacy_files:
    d = json.loads(subprocess.check_output(['git', 'show', '16a1226:data/content/projects/' + fn], text=True))
    slug = d['slug']
    title = d.get('title') or {'en': slug}
    short = d.get('short') or d.get('summary') or {}
    summary = d.get('summary') or short
    missing = d.get('missing') or {}
    rec = dict(
        id=slug, slug=slug, status=d.get('status', 'draft'), evidence=d.get('evidence', 'E1'),
        meta_solution=d.get('meta_solution'),
        title={k: title.get(k, title.get('en')) for k in ('en', 'fr', 'ar')},
        short={'en': short.get('en', '')},
        summary={'en': summary.get('en', '')},
        evidence_note={'en': missing.get('en', 'Status as recorded in the project register. Not independently verified.')},
        services_consumed=[svc[n]['slug'] for n in SERVICES_CONSUMED.get(slug, [])],
        _source='legacy registry, git 16a1226',
    )
    if slug == 'metax-academy':
        rec['meta_solution'] = 'academy-layer'
    projects.append(rec)
    write_json(os.path.join(CONTENT, 'projects', slug + '.json'), rec)
assert len(projects) == 25, f'projects: {len(projects)}'

# ---------------------------------------------------------------- ontology layers
LAYERS = [
    ('academies', 'L0', 'Academies', 'What competence must exist before work starts?',
     'MetaX Academy and assessment profiles form the competence that expert teams require. Internal credentials are never statutory licences or accredited degrees.'),
    ('expert-teams', 'L1', 'Expert teams', 'Who does the work, under which supervision?',
     'Teams deliver services. Team size is a design convention, not a headcount. Managers supervise a bounded number of experts; the hard cap is 12 experts per manager.'),
    ('services', 'L2', 'Services', 'What outcome is sold, and how is it accepted?',
     'Each service declares a buyer problem, a deliverable, an acceptance test and a boundary. Services are proposed until a buyer, price, legal scope and cash are established.'),
    ('macro-works', 'L3', 'Macro works', 'Which domain does a service belong to?',
     'Five macro works group the 130 services: Cultures/Humanities, Built Environment, Sciences, Governance and Technology.'),
    ('meta-solutions', 'L4', 'Meta solutions', 'Which cross-cutting instruments operate across macro works?',
     'Instruments such as the weekly work engine, the 5% independent sample and the identity kernel. They serve services; services do not serve them.'),
    ('projects', 'L5', 'Projects', 'What is being built, for whom, and at what evidence level?',
     'The 25-entry project register. A register entry is not a funded business. Each carries its recorded status and evidence level.'),
]
for key, layer, title, question, text in LAYERS:
    write_json(os.path.join(CONTENT, 'layers', key + '.json'), dict(
        id=key, key=key, layer=layer, ontology_layer=key,
        title={'en': title, 'fr': title, 'ar': title},
        question={'en': question}, summary={'en': text}, _todo=TODO_NOTE))

META = [
    ('dyon', 'Dyon competency records', 'Practical assessment and credential records. Enforcement is proposed, not verified: no competency levels are published yet.', 'proposed'),
    ('weekly-work-engine', 'Weekly work engine', 'Directives, reports, approvals and a Friday lock. Designed; not yet operating for any paid team.', 'proposed'),
    ('split-db-rollup', 'Rollup-only dashboards', 'Dashboards read rollups_daily, never raw rows. Implemented in this release (bin/rollup.php).', 'implemented'),
    ('sampling', 'Fixed 5% independent sample', 'Five percent of submitted weekly reports are re-reviewed independently. Fixed by the licence doctrine; not configurable in the UI.', 'implemented'),
    ('kernid', 'KernID identity kernel', 'Identity and credential verification layer. Concept only; no audit or implementation is published.', 'concept'),
    ('selfai', 'SelfAI assistance', 'Internal documentation and analysis assistance with human review. Concept only; no autonomous credential award.', 'concept'),
]
write_json(os.path.join(CONTENT, 'layers', 'meta-solutions.json'), dict(
    id='meta-solutions', key='meta-solutions', layer='L4', ontology_layer='meta-solutions',
    title={'en': 'Meta solutions', 'fr': 'Meta solutions', 'ar': 'Meta solutions'},
    question={'en': 'Which cross-cutting instruments operate across macro works?'},
    summary={'en': 'Six instruments, each with its own evidence status.'},
    items=[dict(slug=s, title={'en': t}, text={'en': x}, status=st) for s, t, x, st in META],
    _todo=TODO_NOTE))

print(f'services={len(svc)} macros={len(MACROS)} projects={len(projects)} layers={len(LAYERS)} meta={len(META)}')
print('macro text keys:', {k: all(v.values()) for k, v in macro_text.items()})
print('macro attr keys:', {k: sorted(v.keys()) for k, v in macro_attr.items()})

# ---------------------------------------------------------------- 188-country appendix
i = rep.find('Appendix A. Observations')
seg = rep[i:rep.find('Sources, scope, and limitations', i)]
srows = re.findall(r'<tr><td>([^<]+)</td><td>(\d{4})</td><td>([\d.]+)</td></tr>', seg)
assert len(srows) == 188, f'appendix rows {len(srows)}'
write_json(os.path.join(CONTENT, 'statistics', 'advanced-education-unemployment.json'), dict(
    id='advanced-education-unemployment', indicator='SL.UEM.ADVN.ZS',
    definition={'en': 'Unemployment among the labour force with advanced education (%). Not a headcount of W5D experts, not a 2026 estimate, not a recruitment quota.'},
    source={'en': 'World Bank WDI indicator SL.UEM.ADVN.ZS (ILO provenance); secondary tabulation reproduced in the W5D assessment, retrieved 12 November 2026.'},
    caveat={'en': 'Observation years differ widely (2000 to 2025). Rows are source-order and are not a current ranking. Countries absent from the table are unobserved here, not zero.'},
    rows=[{'country': html.unescape(c).strip(), 'year': int(y), 'rate': float(r)} for c, y, r in srows],
    tunisia_national={'en': 'Secondary report of INS Q2 2026 figures: overall unemployment 14.9%; graduate unemployment 26.6% (men 14.2%, women 35.6%). Full INS text was not retrievable for the assessment; treat as secondary reporting.'},
))
