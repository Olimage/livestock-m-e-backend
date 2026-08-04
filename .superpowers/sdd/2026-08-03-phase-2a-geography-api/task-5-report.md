# Task 5 Report — Clear two data-mislabelling issues from Phase 1

## Files touched

- **Modified**: `mne_frontend/src/components/SectorialOutcome/SectorMap/data/Pillar_data.js`
  — deleted `PILLAR_PROGRAMS` (the hand-authored pillar × state matrix of invented program
  names / `lastUpdate` dates), its three read-only helpers (`getStatesForPillar`,
  `getActiveStatesCount`, `getAllStates`), the unused local `ALL_STATES` array, and the unused
  `import { active } from "d3"`. Kept `PILLARS`, `PILLAR_COLORS`, `getPillarColor`, and the
  default export (now only exposing those three). Rewrote the file header to describe what the
  file actually contains and to point at `useSectorMap()` as the real source of pillar/program
  data.
- **Modified**: `mne_frontend/src/components/SectorialOutcome/SectorMap/SectorMapFooter.jsx`
  — removed the hardcoded `Data: 2025 / Confidence: Moderate` block (the "Right" section of the
  footer). Did not replace it with any other string or wire a new endpoint. The footer did not
  become empty — the "Performance Index" label and the High/Medium/Low/Minimal color legend
  remain, so no element was removed.

## Line counts

`Pillar_data.js`: **2576 → 47** lines (before/after; brief's "~2,576" description confirmed).
`SectorMapFooter.jsx`: 44 → 39 lines.

## Step 1 — establishing what is actually used (verification)

Grepped `src/` for every import of the file (both the `./data/Pillar_data` module path and any
default-import usage):

```
ProgramAll.jsx:9:   import { PILLARS, PILLAR_COLORS, getPillarColor } from './data/Pillar_data';
SectorialMap.jsx:9: import { getPillarColor } from './data/Pillar_data';
```

No other file imports this module (grepped for `Pillar_data` and `PILLAR_DATA` across all of
`src/`), and no file imports the default export.

Then grepped for `PILLAR_PROGRAMS`, `getStatesForPillar`, `getActiveStatesCount`, `getAllStates`,
and `ALL_STATES` individually across `src/` to check both importers and internal-only consumers
(the exact trap flagged in the brief):

- `PILLAR_PROGRAMS` — only referenced inside `Pillar_data.js` itself (its own declaration and
  the three helpers built on it) plus two **comments** in unrelated files
  (`utils/mockSustainability.js:71`, `hooks/useDashboard.js:37`) that mention it descriptively;
  neither imports or reads it.
- `getStatesForPillar`, `getActiveStatesCount`, `getAllStates` — zero references anywhere
  outside their own declarations in `Pillar_data.js`. Not imported by `ProgramAll.jsx` or
  `SectorialMap.jsx`.
- `ALL_STATES` — declared but never referenced anywhere, including inside `Pillar_data.js`
  itself (it wasn't even used to build `PILLAR_PROGRAMS` — that object was hand-typed per
  state). Deleted as dead code alongside `PILLAR_PROGRAMS`.

I additionally read `ProgramAll.jsx` and `SectorialMap.jsx` in full to confirm the shape of their
actual data flow, since the brief specifically warns that a symbol can have zero importers but
still have a live *internal* consumer. Confirmed: `ProgramAll.jsx` gets pillar/program data from
`useSectorMap()` (`hooks/useDashboard.js` → `dashboardApi.sectorMap()` → `GET /sector-map`, a
real backend-backed endpoint per `mems/app/Http/Controllers/Api/SectorMapApiController.php`) and
passes it down as the `pillarPrograms` prop to `SectorialMap.jsx`. Neither component ever reads
`PILLAR_PROGRAMS` from `Pillar_data.js` — the API response is a same-shaped but entirely
independent object. So deleting `PILLAR_PROGRAMS` and its helpers removes zero live behavior.

## Step 2 — deletion

Rewrote `Pillar_data.js` to keep only `PILLARS`, `PILLAR_COLORS`, `getPillarColor`, and a default
export of those three. New header comment explicitly states the file no longer contains program
data, names `useSectorMap()` as the real source, and records that `PILLAR_PROGRAMS` was removed
because nothing imported it.

## Step 3 — hardcoded provenance claim

Removed the `Data: 2025 / Confidence: Moderate` `<div>` from `SectorMapFooter.jsx`. Did not
invent a replacement string and did not wire a new endpoint, per the brief. The footer still
renders content (Performance Index label + 4-item color legend), so no blank bar was left behind
and no need to remove the component/element entirely.

**Judgement call (footer replacement text) — flagged rather than decided unilaterally:**
I checked whether a more honest option existed — e.g., surfacing a real reporting period already
present in data the map has access to. Read `dashboardApi.js` (`sectorMap: () =>
axiosInstance.get('/sector-map').then(unwrap)`) and the backend controller
(`mems/app/Http/Controllers/Api/SectorMapApiController.php`): the `/sector-map` response is
`{ [pillarTitle]: { [stateName]: { active, programs[], output } } }` — built from each program's
`coverage_states`, with **no timestamp, `updated_at`, or period field anywhere in the payload or
in the `NlgasPillar`/program query**. So there is no real "as-of" data available to `ProgramAll`/
`SectorialMap`/`SectorMapFooter` today that could honestly replace the fabricated date/confidence
line — surfacing one would require a new backend field, which the brief explicitly rules out for
this task. Plain removal (what I did) is the only honest option available without expanding scope.
If a real "last synced" timestamp is wanted later, it would need to come from the backend (e.g. a
`MAX(programs.updated_at)` per pillar) and is a separate, scoped task.

## Verification commands run (real output)

**`npm test -- --run`** (from `mne_frontend/`):
```
 Test Files  12 passed (12)
      Tests  86 passed (86)
```
All 12 pre-existing files still pass, including `src/mocks.guard.test.js` (unchanged, per
instructions) and `GeographyPanel.test.jsx` from Task 4. No new test file was added — this task
had no new importable behavior to test (pure deletions + a static markup removal).

**`npm run build`**:
```
✓ 1606 modules transformed.
...
✓ built in 6.96s
```
Succeeded, confirming no broken import after pruning `Pillar_data.js`'s exports.

**Grep sweep for the two target strings across `src/`:**
- `PILLAR_PROGRAMS` — 4 matches, all comments (2 pre-existing in unrelated files, 2 new
  explanatory comments in the rewritten `Pillar_data.js` itself describing the removal). Zero
  code references.
- `Confidence: Moderate` — 0 matches.
- `getStatesForPillar` / `getActiveStatesCount` / `getAllStates` / `ALL_STATES` — 0 matches.

## What I refused to do, and why

Nothing — Step 1's investigation confirmed the brief's "stop and report" trigger condition
(a live consumer of `PILLAR_PROGRAMS`) did not hold, so deletion per Step 2 was correct to
proceed. The one open judgement call (footer text) is reported above rather than acted on
unilaterally, though in this case the honest answer was "there is nothing safer to show, so
remove it" — which is what the brief already directed.

## Self-review

- Scope held to exactly the two files named in the brief's Files list.
- `PILLARS`, `PILLAR_COLORS`, `getPillarColor` keep their exact names/shapes; both live
  importers (`SectorialMap.jsx:9`, `ProgramAll.jsx:9`) were re-checked after the edit and still
  resolve (build succeeded, which would fail on a missing named export).
- Also removed `ALL_STATES` and the unused `import { active } from "d3"` since both were dead
  code touching only the part of the file being rewritten, and leaving them would have
  contradicted the new header's claim that the file only contains what's listed. Flagging this
  here since it's a small addition beyond the literal "delete `PILLAR_PROGRAMS` and its helpers"
  instruction, though it's within "Modify: Pillar_data.js" scope and the same class of cleanup.
- No git commands were run at any point.
- No `npx rg` used — all searches via the Grep tool.
- Did not touch `src/mocks.guard.test.js`'s `ALLOWED` list.
