### Task 5: Clear two data-mislabelling issues from Phase 1

Both were found during Phase 1's final review, are unrelated to geography, and are small. They
are folded in here so they actually get done rather than sliding indefinitely.

**Files:**
- Modify: `mne_frontend/src/components/SectorialOutcome/SectorMap/data/Pillar_data.js`
- Modify: `mne_frontend/src/components/SectorialOutcome/SectorMap/SectorMapFooter.jsx`

**Interfaces:**
- Consumes: nothing.
- Produces: nothing importable. `PILLARS`, `PILLAR_COLORS`, and `getPillarColor` must keep
  their current names and shapes — `SectorialMap.jsx` and `ProgramAll.jsx` import them.

- [ ] **Step 1: Establish what is actually used**

`Pillar_data.js` is ~2,576 lines. Phase 1's plan described it as "chart colours"; it is mostly
`PILLAR_PROGRAMS`, a hand-authored pillar × state matrix with program names and `lastUpdate`
dates.

Using the Grep tool, find every importer of this file and every symbol they take. At the time
of review the live consumers were `SectorialMap.jsx:9` and `ProgramAll.jsx:9`, taking only
`PILLARS`, `PILLAR_COLORS`, and `getPillarColor`. **Verify this yourself** — including any
internal use of `PILLAR_PROGRAMS` by the helpers that are exported.

- [ ] **Step 2: Delete what is unreferenced, keep what is not**

If `PILLAR_PROGRAMS` (and any helper that exists only to read it) has no live consumer, delete
it, exactly as Phase 1's Task 5 did for `sectorPresentation.jsx`.

**If any live consumer reads it, stop and report rather than deleting** — a rendered
pillar × state matrix of invented program names is a bigger problem than a mislabelled file, and
it needs its own decision about what replaces it.

Either way, rewrite the file header so it describes what the file actually contains. Do not
repeat the "chart colours" characterisation unless that is all that remains.

- [ ] **Step 3: Remove the hardcoded provenance claim**

`SectorMapFooter.jsx` renders `Data: 2025 / Confidence: Moderate` beneath the live Nigeria map
(around line 40). Both are invented assertions about real data — nothing computes them.

Remove them. Do not replace them with a different hardcoded string, and do not wire up a new
endpoint. If the footer becomes empty as a result, remove the element rather than leaving a
blank bar.

- [ ] **Step 4: Verify**

Run: `npm test -- --run`
Expected: PASS.

Run: `npm run build`
Expected: success.

Search `src/` for `PILLAR_PROGRAMS` and `Confidence: Moderate`.
Expected: no matches, or — for `PILLAR_PROGRAMS` — only the live consumers you documented in
Step 1 and reported on.

---

## Global Constraints

- **No git operations.** Version control is handled by the user. Do not run `git add`, `git commit`, `git status`, `git diff`, `git log`, or any other git command. Tasks end with verification, not commits.
- **Do not use `npx rg`** — it fails in this shell (npm-init collision). Use the Grep tool or `grep -rn`.
- **Read every file and controller named in a task's Files list before writing code for it.** Phase 1 shipped four defects, all traceable to describing a contract from memory or from what a mock implied instead of reading the source. This is a hard gate, not advice.
- Two repos. `mems` is at `d:\DAVID\New folder\Olimage\FMLD_PROJECT\mems`; `mne_frontend` at `d:\DAVID\New folder\Olimage\FMLD_PROJECT\mne_frontend`. Both paths contain spaces — quote them.
- `mems`: `php artisan test` (PHPUnit, SQLite `:memory:` per `phpunit.xml`), `vendor/bin/pint --dirty` before finalising, `php artisan route:list` to confirm routes resolve.
- `mne_frontend`: `npm test -- --run`, `npm run build`. Current baseline: **71 tests passing across 10 files**.
- New API endpoints use the `{status, message, data}` envelope — the majority form, and the one `src/services/unwrap.js` error-checks. Do **not** use `{success, …}`.
- Frontend data access follows the established layering: `services/*Api.js` → `hooks/use*.js` → component. Do not call axios from a component.

## Spec reconciliation

This plan implements the geography row of §4.3 of
`mems/docs/superpowers/specs/2026-08-03-mne-frontend-api-wiring-design.md`, plus §2.5.

**One deliberate deviation from the spec.** §2.5 says "LGAs paginate by default (774 rows)". This
plan makes pagination **opt-in** (`?per_page` present → paginated; absent → the full filtered
set). Reason: `SimpleCrudPanel` — the component that renders all three geography tables —
already does search, filtering, and paging client-side, and 774 LGA rows is roughly 60 KB of
JSON. Defaulting to server pagination would force a rewrite of a working component for no user
benefit. `?per_page` is still supported so a future consumer can page without an API change.

