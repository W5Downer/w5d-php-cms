# PROMPT — Build the W5D Admin Dashboard and Role Workbook Suite

You are a senior system architect and full-stack PHP developer. Your task is to produce (a) a complete, implementation-ready specification and scaffold for the W5D admin dashboard, and (b) the generation logic for eight role-scoped workbooks, all governed by one owner-rooted ACL.

Deliver the output as: (1) a completed README.md section set for the W5D repo; (2) SQL schema for split SQLite databases; (3) a directory/file scaffold; (4) the admin dashboard page inventory; (5) the role-workbook export specification; (6) the open owner decisions list. Do not invent operational data. Every figure must be a formula, traceable to a named setting, or explicitly marked PROPOSED / UNVERIFIED / TBC.

---

## 0. SOURCE OF TRUTH AND CONSTRAINTS

Read the joined `services-dossier.md` fully before writing anything. It is the canonical register of 130 services, five macro works, the role model, the weekly engine and the epistemic rules.

Mandatory constraints from the W5D README as summarized in the dossier:
- Runtime: PHP 8.x with `pdo_sqlite` and `sqlite3`.
- Storage: split SQLite databases — one content/users database plus five macro-work databases; WAL mode, busy timeouts, short transactions, indexes, rollup-only dashboards.
- Eight account roles with scoped authority; team caps; weekly work engine.
- Dyon QR identity references, certification status, permissions, continuing modules.
- Leader schedule: 20 sampled reports per service per week.
- NOT specified in the README: any framework, ORM, admin package, or named dashboard repository. Do not assume one. Do not require Laravel, Symfony, Filament, Livewire, Eloquent, Composer, or any named admin package. The admin dashboard must be specified as framework-agnostic plain PHP. If the actual README names a dashboard repository, apply the five compatibility checks in Section 9 and report the result; otherwise build plain PHP.

Epistemic rules (non-negotiable):
- The 130,000 expert figure is a DESIGN CEILING, never a current headcount.
- Dyon enforcement, 5% sampling, numerical RAG thresholds and activation gates are PROPOSED unless the README states them.
- The 20 February 2026 Ministry/ANETI register, the ~23% Order engineer estimate, the ~15,000 agricultural engineer figure and the “95% IT engineers abroad” headline are UNVERIFIED. Do not use them as data.
- Every non-source figure carries a Status column value of PROPOSED, UNVERIFIED or TBC.
- Surface every ambiguity as a CONFIRM WITH OWNER item rather than guessing silently.

---

## 1. BUSINESS RULES TO ENCODE

1. Part unit price: 10 TND per day. One part = 10 TND/day of investment.
2. A part must be purchased every calendar day to keep the contract active.
3. Missed-day penalty: if a calendar day passes without a confirmed purchase, all previous part payments are returned (refund = cumulative paid to date), the contract status becomes Deleted, and the consecutive-day streak resets to 0.
4. Eligibility threshold: after 100 consecutive days of purchasing parts, the contract becomes Eligible.
5. Payout (eligible contracts only): monthly payout = (daily_part_amount_TND / 10) × 600 TND. Examples: 10 TND/day → 600 TND/month; 20 TND/day → 1,200 TND/month.
6. Payout timing: first payout in the month following the 100th consecutive day; subsequent payouts monthly while the contract remains Active and Eligible. Mark PROPOSED pending owner confirmation.
7. Funding purpose: total parts invested fund the production of solutions delivered by a design ceiling of 130,000 expert team members (26 services × 5 macro works × 1,000 experts per service). Treat as DESIGN CEILING only.
8. Refund treatment: default full refund of part payments; payouts already received are not clawed back. Mark CONFIRM WITH OWNER.
9. Design-vs-actual separation: current developers (reported as 26 on the website) and funded slots must never be conflated with the 130,000 design ceiling.

---

## 2. ARCHITECTURAL POSITION

The eight role workbooks are views over one authoritative data model, not eight independent spreadsheets. The admin dashboard is the write surface and reconciliation engine; the workbooks are read-only projections.

Rule: One data model → one admin control plane → eight role-scoped projections.

The owner ACL is the root of the permission tree. Every other role inherits a subset of the owner’s authority, never a parallel authority.

Rollups must never double-count. If an expert’s output is accepted by a team manager and counted at super-manager level, the super-manager rollup references the team-manager accepted count, not the raw reports.

---

## 3. MANDATORY COMPONENTS (NO FRAMEWORK REQUIRED)

| Component | Requirement |
|---|---|
| Runtime | PHP 8.x with pdo_sqlite and sqlite3 |
| Storage | SQLite split per README; WAL; busy timeout; short transactions |
| Auth | Server-side session authentication; password_hash / password_verify; session fixation protection on login; secure HTTP-only cookies over HTTPS |
| RBAC | Tables users, roles, permissions, role_permissions, user_roles; optional resource scoping via resource_type and resource_id |
| Policy layer | One central can($actor, $action, $resource) function; every page and every write calls it; no bypass path |
| Audit | One append-only audit_log table written by the same function that performs writes; application user has INSERT and SELECT only on this table |
| Scheduler | Cron or scheduler script at 00:00 Africa/Tunis to detect missed-day contract breaches |
| Export | Read-only SQL views per role; export to .xlsx or .csv |
| Routing | Minimal front controller (index.php + small router) or a PHP framework of the owner’s choice without changing the data model, ACL tables, policy layer or audit log |

---

## 4. ROLE HIERARCHY

| Level | Role | Scope | Inheritance |
|---|---|---|---|
| L0 | Owner | Global — all records, settings, financial models, deletions | Root; no parent |
| L1 | Department CEO | One department (16 departments), its macro(s), its super managers | Read/write within department; no cross-department finance |
| L2 | Super Manager | One cluster of team managers and their experts | Read/write within cluster; no department settings |
| L3 | Team Manager | One service team (~10 experts) | Read/write within team; approve/reject reports; no payroll settings |
| L4 | Expert | Own record, assignments, evidence submissions | Read own; write own reports/evidence; no approval rights |
| External | Investor | Own contract, parts, payout record | Read own; write own payment events via payment gateway only |
| External | Customer | Own orders, acceptance records, invoices | Read own; write acceptance sign-off only |
| External | Affiliate | Own referral ledger, commission record | Read own; no access to referred parties’ records |

Permission primitives are pairs: resource.action (e.g., service.update, contract.delete, payout.approve, evidence.verify). Owner holds *. Every other role has an explicit allow-list.

Field-level ACL required on financial and personal data: an investor sees own part amounts only; a team manager sees expert acceptance rates but not expert payout amounts unless the expert is also an investor and has consented.

---

## 5. PROTECTED OPERATIONS (OWNER-ONLY)

Enforce in the policy layer and re-check on every write, not only at page load:
- Changing PART_PRICE_TND_PER_DAY (10), STREAK_THRESHOLD_DAYS (100), PAYOUT_PER_10_TND_PER_MONTH (600).
- Changing the missed-day refund rule.
- Deleting a contract, overriding a refund, or restoring a deleted contract.
- Approving a payout run.
- Changing the 130,000 design-ceiling configuration or the service/macro ontology.
- Exporting the full investor ledger or the full expert personal-data set.
- Granting or revoking any role above L3.

Owner must re-authenticate for these operations.

---

## 6. CANONICAL DATA MODEL

Produce SQL for the following entities. Each row in the schema must include primary key, foreign keys, created_at, updated_at, and a status field using a closed list.

| Entity | Key attributes | Access |
|---|---|---|
| Account | id, role, parent_id, status, Dyon_id | Owner all; others scoped |
| Department | id, name, macro_ids, ceo_account_id | Owner all; CEO own |
| Service | id (001–130), macro, slug, competency_profile | Owner all; others scoped |
| Team | id, service_id, manager_id, expert_ids | Manager own; super manager cluster; CEO department |
| Contract | id, investor_id, daily_part_amount, start_date, streak, status, cumulative_paid | Owner all; investor own |
| Payout | id, contract_id, month, amount, status | Owner approve; investor read own |
| PartPayment | id, contract_id, date, amount | System; owner read all |
| EvidenceRecord | id, report_id, artifact_hash, storage_ref | Manager verify; owner audit |
| Report | id, expert_id, week, service_id, accepted, rework_count | Manager approve; expert write own |
| Order | id, customer_id, service_id, acceptance_status | Customer sign-off; owner read all |
| Invoice | id, order_id, amount, payment_status | Owner all; customer own |
| Referral | id, affiliate_id, referred_id, qualifying_event | Affiliate own; owner all |
| Commission | id, affiliate_id, amount, status | Owner approve; affiliate read own |
| Settings | key, value, unit, source, status | Owner only for protected keys |
| AuditLog | id, timestamp, actor_id, actor_role, action, resource_type, resource_id, before_hash, after_hash, ip, session_id | Append-only |

Split databases: content/users database holds Account, Department, Service, Team, Order, Invoice, Referral, Commission, Settings, AuditLog, EvidenceRecord metadata. Macro-work databases hold Service-specific work records per the README split. Cross-database reads use views; writes occur only through the content database with audit.

---

## 7. ADMIN DASHBOARD MODULES

A. Owner Command Center
- Portfolio KPIs: total parts raised, active contracts, eligible contracts, deleted contracts, total refunds, payouts due vs paid, funded expert slots vs 130,000 ceiling, monthly payroll obligation (600 × funded experts), monthly payout obligation, customer revenue, net monthly position.
- Macro rollup (5 macros): services active, experts funded, accepted outputs, blockers, parts allocated.
- Department rollup (16 departments): CEO, super managers, team managers, experts, funded slots, funding gap.
- Risk register: contract deletion rate, missed-day events, funding-vs-payroll gap, investor concentration, unverified claims outstanding.
- Owner decisions pending: all CONFIRM WITH OWNER items surfaced from every module.

B. Contract & Part Ledger
- Daily part-payment recording; automatic streak increment; automatic missed-day detection at 00:00 Africa/Tunis.
- On missed day: compute refund = cumulative_paid, set status = Deleted, reset streak = 0, write audit entry, notify investor and owner.
- Eligibility: eligible = (streak >= 100 AND status = Active).
- Payout projection: projected_monthly = eligible ? (daily_part_amount / 10) * 600 : 0.
- Owner-only actions: manual override, refund adjustment, contract restoration.

C. Service & Team Operations
- Service registry (130 rows) with macro, slug, competency profile, evidence rule.
- Team composition: manager + experts, Dyon status, statutory-licence flags (clinical, engineering, legal — separate from Dyon).
- Weekly report approvals: manager accepts/rejects; super manager sees rollup; CEO sees department rollup; owner sees all.
- Evidence verification: hash, storage reference, acceptance rubric. QR check-in is not accepted as sole proof.

D. Financial Control
- Payroll obligation: funded expert slots × 600 TND.
- Payout runs: aggregate eligible contracts; owner approves; payment status tracked.
- Customer invoicing: order → invoice → payment; acceptance evidence attached.
- Affiliate commission: qualifying event → commission → approval → payment.
- Funding-vs-payroll lock: activation of a new expert slot requires a demonstrated funded slot; block if funded_slots < payroll_obligation / 600.

E. Evidence & Audit
- Append-only audit log viewer (owner all; CEO read-only on own department).
- Evidence artifact browser with hash verification.
- Sampling module: proposed 5% random re-performance per service, marked PROPOSED; note that the README leader schedule currently specifies 20 reports per service (2%).

F. Configuration (owner-only)
- Constants: part price, streak threshold, payout multiplier, design ceiling, services per macro, experts per service, managers per service.
- Role and permission editor (writes to roles, permissions, role_permissions, user_roles).
- Department and macro mapping.
- Project-to-service mapping (MetaX Academy, KernID, SelfAI, Ontology Department, كن أعلم, WebBooks, Studio, 2030B).

---

## 8. ROLE WORKBOOK EXPORT SPECIFICATION

Each workbook is a read-only projection of the dashboard, generated from SQL views. Every workbook has a Registry sheet, a per-instance template sheet, and a Change_Log sheet.

| Workbook | Source module | Refresh | Key columns |
|---|---|---|---|
| Owner | Command Center + all modules | Live | All KPIs, all rollups, risk register, decisions pending |
| Department CEO | Department rollup + service RAG | Daily | Department, macro, super managers, team managers, experts, funded slots, payroll obligation, payout obligation, weekly accepted outputs, RAG per service, blockers |
| Super Manager | Cluster rollup + team manager roster | Daily | Super manager ID, macro, services, team managers, experts, funded slots, accepted outputs, rework, blockers, funding rollup, review log |
| Team Manager | Team roster + report approvals | Weekly (Friday lock) | Manager ID, service, expert roster (Dyon status, streak, part amount if also investor, eligibility, reports submitted/accepted/rework, KPI flag), team KPI block, blockers |
| Expert | Own record + activity + investment block | Weekly | Expert ID, Dyon ID, service, team manager, macro, department, competency assessment status, statutory-licence flags, continuing-module status, days active, consecutive streak, reports submitted/accepted/rework, evidence refs, investment block (daily part, total paid, streak, eligibility, projected payout, actual payouts), acceptance rate, rework rate, blockers raised/resolved |
| Investor | Own contract + ledger + payout | Daily | Investor ID, contract ID, start date, status (Active/At risk/Eligible/Deleted), date, daily part amount, amount paid, cumulative paid, consecutive day count, days remaining to 100, eligible Y/N, first payout month, projected monthly payout, actual payouts, cumulative payouts, contract terms acknowledgment checkboxes |
| Customer | Own orders + acceptance + invoices | On order change | Customer ID, organisation, sector, macro(s) of interest, order ID, service ID, macro, deliverable, quantity, unit, quoted price, acceptance status, acceptance evidence reference, invoice ID, payment status, satisfaction note, renewal decision, next order date, dispute log |
| Affiliate | Own referral ledger + commissions | On referral event | Affiliate ID, type, agreement reference, referred entity ID, entity type, date, status, qualifying event, commission rate, earned to date, paid to date, pending, consent status, disclosure status, conflict-of-interest flag, payout schedule |

Formatting rules for every workbook:
- One tab colour per role group.
- Freeze header rows; filters on registry tables.
- Conditional formatting: Active green, At risk amber, Deleted red, Eligible blue, PROPOSED/UNVERIFIED grey.
- Protect formula ranges; leave input cells unlocked and highlighted.
- Every imported figure carries a Source column with its dossier tag (R01, G01, W01, etc.) or PROPOSED / UNVERIFIED.

---

## 9. DASHBOARD REPOSITORY COMPATIBILITY CHECK

If the W5D README names a specific admin dashboard repository, apply these five checks before adopting it. Report each as pass/fail with evidence. If any check fails, treat the repo as a reference pattern, not a drop-in.

1. Language/runtime: runs on the README’s PHP version and SQLite.
2. Storage: accepts split SQLite, or documents the migration path; does not require a single database.
3. ACL model: supports owner-rooted roles with resource-scoped grants.
4. Audit: writes an append-only log.
5. Workflow: can express the Monday→Friday→Saturday cycle and the 20-per-service sampling schedule.

If the README does not name a repository, state that plainly and build plain PHP.

---

## 10. SECURITY AND COMPLIANCE REQUIREMENTS

- Owner re-authentication for protected operations.
- Field-level redaction on financial and personal data.
- Session integrity: secure HTTP-only cookies over HTTPS; server-side sessions; fixation protection.
- Database separation: content/users database separate from the five macro-work databases; dashboard connects to all but writes only through the content database with audit.
- Backup and recovery: every database backed up; restore tested; audit log backed up separately.
- No double counting: rollup queries reference accepted counts at team level and aggregate upward; never re-add raw reports.
- No unverified claim as data: every imported figure carries a Source tag; untraceable figures carry PROPOSED or UNVERIFIED.
- Append-only audit: no UPDATE/DELETE code path on audit_log; application database user has INSERT and SELECT only on that table.

---

## 11. CONFIRM WITH OWNER ITEMS

Surface these in the Owner Command Center and in the README as open decisions:

1. Refund rule on missed day: full, partial, or net of payouts received?
2. Payout duration: indefinite, fixed term, or until contract termination?
3. Grace window: is a purchase required exactly every calendar day, or is a grace window permitted?
4. Expert-as-investor: are experts required to invest parts, or are the roles independent?
5. Affiliate commission: rate and qualifying event?
6. Department mapping: what are the 16 departments, and how do the five macros map onto them?
7. Project-to-service mapping: which website projects link to which services in the customer and investor modules?
8. Admin dashboard implementation approach: plain PHP (default, closest to README) or a PHP framework of the owner’s choice; if the README names a dashboard repo, the five-check result from Section 9.

Until confirmed, implement the default assumptions in Section 1 and mark every affected field CONFIRM WITH OWNER.

---

## 12. DELIVERY SEQUENCE

1. Week 1–2: SQL schema for all databases; Settings and Registry; owner ACL root and role hierarchy; dashboard implementation decision recorded.
2. Week 3–4: Contract and part ledger; missed-day scheduler; eligibility and payout engine; audit log.
3. Week 5–6: Service/team operations; report approvals; evidence verification; rollup views.
4. Week 7–8: Financial control; customer orders/invoices; affiliate commissions; workbook export layer.
5. Week 9: Owner Command Center; risk register; CONFIRM WITH OWNER surfacing; security hardening.
6. Week 10: Pilot with one department and one service; reconcile workbooks against dashboard; fix double-counting.

---

## 13. ACCEPTANCE CHECKLIST

- [ ] README.md section set completed, framework-agnostic, plain PHP assumed.
- [ ] SQL schema produced for all databases with status columns and closed lists.
- [ ] Directory/file scaffold produced.
- [ ] Admin dashboard page inventory produced, one page per module, each page calling the central policy layer.
- [ ] Owner ACL root and role hierarchy enforced in the policy layer, not only at page load.
- [ ] Protected operations listed and gated with owner re-authentication.
- [ ] Missed-day logic resets streak, deletes contract, computes refund, writes audit entry, notifies investor and owner.
- [ ] Eligibility and payout formula verified for 10 and 20 TND/day examples.
- [ ] 130,000 labelled DESIGN CEILING everywhere it appears.
- [ ] Every imported figure carries a Source tag or PROPOSED / UNVERIFIED.
- [ ] Rollups do not double-count accepted outputs.
- [ ] Workbook export specification covers all eight roles, including Registry and Change_Log sheets.
- [ ] CONFIRM WITH OWNER items collected and surfaced.
- [ ] If the README names a dashboard repo, the five-check result is reported.
- [ ] No framework, ORM, admin package or repository is required or assumed.

Produce the output now.