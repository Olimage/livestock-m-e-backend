# SDD ledger — plan: mems/docs/superpowers/plans/2026-08-03-phase-2a-geography-api.md

## Adaptations to the standard SDD process

Runs **without git** — the user's standing rule for FMLD_PROJECT forbids agent git usage;
version control is the user's. Consequently: implementers do not commit; ledger entries name
**changed files** instead of commit SHAs; review packages are hand-written file manifests
rather than diffs; `finishing-a-development-branch` is skipped. Reviewers are told explicitly
not to treat the absence of commits as a defect.

**Two repos.** Tasks 1-2 are `mems` (Laravel 11, PHPUnit on SQLite `:memory:`).
Tasks 3-5 are `mne_frontend` (React 18 + Vite, Vitest). Baselines at start:
`mne_frontend` 71 tests passing across 10 files; `mems` full suite green.

## Carried in from Phase 1

- **Hard gate:** read every file and controller in a task's Files list before writing code.
  Phase 1 shipped four defects, all from describing a contract from memory or from what a mock
  implied. This is now a plan-level Global Constraint.
- `npx rg` fails in this shell (npm-init collision). Use the Grep tool or `grep -rn`.
- Guard test `src/mocks.guard.test.js` `ALLOWED` is the live inventory of remaining mock debt;
  Task 4 removes `mockGeography` from it.

## Task ledger

(entries appended below as tasks complete)

Task 1: complete (review clean, spec ✅ + quality Approved)
  Modified: app/Http/Controllers/LocationController.php (3 Api* methods rewritten + 2 helpers)
  Modified: routes/api.php (locations group now behind jwt.verify)
  Created:  tests/Feature/LocationApiTest.php (10 tests)
  Contract: parent ids now nullable|exists (passing them still filters as before);
    payloads gain zone_code/zone_name/state_name/states_count/lgas_count;
    ?per_page absent -> plain array, present -> paginator (deliberate spec deviation,
    reasoned in the plan's "Spec reconciliation" section).
  Reviewer verified: ValidationException re-throw is FIRST catch in all 3 methods and is
    load-bearing (bootstrap/app.php renders it 422; without the re-throw it becomes a 500);
    no N+1 over 774 LGAs (both state and state.zone eager-loaded, map reads hydrated only);
    zone_id filter uses a single EXISTS subquery and correctly returns 0 rows for an
    empty zone; both new auth/list tests genuinely fail against the pre-change code.
  Known/accepted: ApiGetZones returns extra columns (withCount + get([cols]) interaction).
  Tests: mems 115/115 passing incl. GeographyControllerTest untouched; Pint clean.

Task 2: complete (review clean, spec ✅ + quality Approved, zero findings)
  Created:  app/Http/Controllers/Api/GeographyApiController.php (9 write endpoints)
  Created:  routes/v1/admin-crud.php (first tenant; 2b/2c will add to it)
  Created:  tests/Feature/GeographyApiWriteTest.php (8 tests)
  Modified: routes/api.php (one require line)
  Modified: database/seeders/DashboardPermissionsSeeder.php — 'manage-settings' did NOT
    exist and was added; without it CheckPermission would reject everyone.
  Reviewer verified: both delete guards precede the delete and return 422 {status:false}
    with user-facing copy; validation matches the 3 Inertia controllers key-for-key;
    $fillable matches each validate() array exactly and uuid lands via the creating hook
    (bypasses mass assignment); the 403 test genuinely exercises CheckPermission
    (plainUser has is_admin=false AND no roles/permissions, so hasPermission() really
    returns false — removing the middleware would yield 201 and fail the test).
  RESOLVED: CheckPermission aborts 403 with Laravel's default {message} shape, not the
    {status,message,data} envelope. NOT a problem — apiService's axios interceptor reads
    error.response.data.message for any non-2xx; unwrap only inspects 2xx bodies. A 403
    surfaces "Unauthorized." cleanly. No fix needed before Task 4.
  Tests: mems 123/123 passing (was 115); 12 routes under /locations; Pint clean.

Task 3: complete (review clean, spec ✅ + quality Approved, zero findings)
  Created:  mne_frontend/src/services/locationApi.js (3 reads + 9 writes)
  Created:  mne_frontend/src/hooks/useLocations.js (3 queries + 3 mutation factories)
  Created:  mne_frontend/src/hooks/useLocations.test.js (6 tests)
  Modified: mne_frontend/src/config/apiConfig.js (LOCATIONS block)
  Uses the shared src/services/unwrap.js — no local copy. Mutations invalidate the whole
  ['locations'] tree on success. select: asArray guards a paginated payload.
  Tests: mne_frontend 77/77 across 11 files (was 71/10); build succeeds.

Task 4: implementer KILLED by an API session limit mid-task (37 tool calls, no report).
  Work found COMPLETE on disk. Controller re-ran verification first-hand: 79 tests across
  12 files passing (was 77/11), build succeeds, mockGeography.js deleted, no live refs,
  deriveLgaFields gone, mockGeography dropped from the guard's ALLOWED. Controller wrote a
  clearly-labelled RECONSTRUCTED report and told the reviewer to be extra sceptical since
  no decision was ever explained.
  Review round 1 — spec mostly ✅, quality: 1 Critical + 1 Important.
    CRITICAL: GeographyPanel.jsx:38-56 dereferences zones.data/states.data/lgas.data with
      no default. react-query only runs `select` once data is defined
      (queryObserver.cjs:302 — `options.select && data !== void 0`), so on first mount for a
      cold query key data is undefined and undefined.map() throws DURING RENDER. No
      ErrorBoundary exists anywhere -> blank-screens the whole app, not just the tab.
      CONTROLLER ERROR: the Task 4 dispatch asserted "data is always an array" because the
      hooks use select: asArray. That was never verified. Same failure mode as Phase 1 —
      asserting behaviour instead of reading the source. StateMultiSelect.jsx:15 had it
      right all along (`const { data: states = [] } = useStates()`).
    IMPORTANT: 2 tests for a full-component rewrite. Both pre-seed data:[] / isLoading:false,
      so neither exercises the loading transition — which is why the Critical slipped
      through. Untested: Zones/LGAs tabs, all 3 mutation triples, toasts, the 422 refusal
      path, the zone filter.
    Verified clean by the reviewer: name-vs-id contract intact end-to-end into both call
      sites; no new prop added to the shared SimpleCrudPanel; zone-by-code lookup fully
      gone and the form submits zone_id; apiService's interceptor puts the server's 422
      message on error.message so handleDelete toasts it verbatim.
  Fix round 1/5 dispatched: resumed the original implementer (adfd130ed610c38b9).
  Fix round 1/5: Critical ADDRESSED, Important PARTIAL.
    Added zoneRows/stateRows/lgaRows (?? []) locals; all 4 memos + the 3 data={} props
    rewired. Reviewer swept for remaining `.data` derefs — none. StateMultiSelect already
    safe via its destructuring default. Tests 2 -> 7; implementer verified the
    loading-transition guard by reverting the fix, seeing the exact TypeError, restoring.
    IMPLEMENTER CAUGHT A SECOND BREAK the controller missed: mocks.guard.test.js's own
      "gates strictly on ALLOWED" test hardcoded 'mockGeography' as its allowed-name
      example and would have failed once it left ALLOWED. Swapped to 'mockSustainability';
      reviewer confirmed like-for-like, not a weakening.
    Reviewer singled out the 422-refusal test as high-value (asserts the server's exact
      message reaches showToast.error and that success is NOT called).
    STILL OPEN: zone filter untested; update.mutateAsync untested for every entity. Both
      were named in the original finding, so not waved through at round 1.
  Fix round 2/5 dispatched (bounded: exactly 2 tests — zone filter + one update path).
    Explicitly scoped OUT the full 3x3 mutation matrix as busywork.
  Deferred: SimpleCrudPanel's field <label>s have no htmlFor, so getByLabelText can't find
    its inputs (forced role-based queries in tests). Pre-existing, shared across every
    System Management panel. Own follow-up, not this task.
  Fix round 2/5: both residual gaps ADDRESSED. Zone-filter test asserts a matching row is
    PRESENT and a non-matching row is ABSENT (scoped with within(table), since zone names
    also render as <option>s above). Update test pins {id, payload} exactly:
    {id: 1, payload: {name: 'North West', code: 'NW2'}}. No duplication of the existing 7.

Task 4: complete (review clean after 2 fix rounds)
  Rewritten: GeographyPanel.jsx (full), StateMultiSelect.jsx
  Created:   GeographyPanel.test.jsx (9 tests)
  Modified:  src/mocks.guard.test.js (mockGeography out of ALLOWED + its self-check example)
  DELETED:   src/utils/mockGeography.js  <-- the 774-LGA hardcoded literal is gone
  Contract preserved: StateMultiSelect's `selected` is still a list of state NAMES; both
    call sites (ProgramReportModal, CreateProgramModal) persist names unchanged.
  Tests: mne_frontend 86 passing across 12 files (was 71 at Phase 1 close); build succeeds.

Task 5: complete (controller-verified; pure deletion, no new tests needed)
  Modified: mne_frontend/src/components/SectorialOutcome/SectorMap/data/Pillar_data.js
    2576 -> 47 lines. Deleted PILLAR_PROGRAMS (the hand-authored pillar x state matrix),
    its 3 helpers, unused ALL_STATES, an unused d3 import; header rewritten. Kept exactly
    the 3 symbols with live importers: PILLARS, PILLAR_COLORS, getPillarColor.
  Modified: mne_frontend/src/components/SectorialOutcome/SectorMap/SectorMapFooter.jsx
    Removed the hardcoded "Data: 2025 / Confidence: Moderate" provenance claim.
  JUDGEMENT (asked for, correctly not acted on unilaterally): the implementer checked
    whether /sector-map's payload carries a real timestamp or period to show instead —
    it does not — so plain removal was the only honest option without expanding scope.
    This is the "read the source before asserting" discipline working as intended.
  Controller verified: PILLAR_PROGRAMS / getStatesForPillar / getActiveStatesCount /
    ALL_STATES / "Confidence: Moderate" all have 0 live references; the 2 importers
    (SectorialMap.jsx, ProgramAll.jsx) take only the 3 kept symbols; both components get
    their real pillar/state data from useSectorMap() -> GET /sector-map.
  Tests: 86 passing across 12 files (unchanged — deletion only); build succeeds.

FINAL WHOLE-PLAN REVIEW (opus): goal MET, work sound, nothing Critical.
  Verified independently: mockGeography gone with zero live refs; PILLAR_PROGRAMS has no
  path to a user (ProgramAll gets its real matrix from useSectorMap -> GET /sector-map);
  backend/frontend contract agrees field-by-field incl. write payloads and the 422->toast
  path; jwt.verify move confirmed safe (mems/resources/ contains ZERO occurrences of
  "locations" — the Inertia frontend uses the separate session-auth location.* web routes;
  no Flutter project remains in the workspace).
  MUST FIX (Important x2):
   1. IndicatorReportsPage.jsx:847 passes canManage={isPrs}. UI gates on MODULE IDENTITY,
      backend enforces permission:manage-settings. A non-admin PRS user without the
      permission sees enabled Add/Edit/Delete, gets 403 "Unauthorized." on click, and —
      because SimpleCrudPanel.handleSubmit doesn't await onCreate before closeForm() —
      loses the typed data. Mirror case: a manage-settings holder outside PRS gets a
      read-only table the backend would let them write. usePermission + rbacConfig's
      'settings'->'manage-settings' mapping already exist.
   2. Both mems/postman/*.json collections still document /locations reads as
      "Unauthenticated", auth:noauth, zone_id/state_id REQUIRED, and the pre-change
      payload — and have no entries for the 9 new writes. The entire point of the change
      was to stop the hierarchy being world-readable; the canonical collection now
      asserts the opposite.
  DEFER to 2b: asArray silently turns a paginated payload into [] (will be stepped on
    once 2b paginates); SimpleCrudPanel htmlFor (its absence already forced positional
    test queries that will silently target the wrong control when a field is added —
    do it as its own task BEFORE 2b rewires 5 more panels through that component).
  Minor/cheap: SectorMapFooter left a dangling "Performance Index:" label with an empty
    div; useDashboard.js:37 comment still references the deleted PILLAR_PROGRAMS.
  DEPLOYMENT NOTE: manage-settings is seeded but attached to NO role — on a fresh env only
    is_admin can write geography until someone grants it. Belongs in release notes.
  Fix wave 1 (single dispatch: findings 1, 2, 5, 6) dispatched.
  Fix wave 1: findings 2, 3, 4 verified good by the controller.
    Postman: all 12 locations endpoints now resolve to `inherit` auth (checked
      programmatically by walking both collection trees, not by grepping "noauth" — the
      high noauth counts elsewhere are unrelated public endpoints like sign-in). All 9
      writes documented with manage-settings + 422 refusal semantics. Both files parse.
    SectorMapFooter: "Performance Index:" label moved next to the colour legend it labels.
    useDashboard.js:37 comment reworded off the deleted PILLAR_PROGRAMS.
    Implementer REPORTED (correctly did not fix): SystemManagementPanel at the same file's
      line 840 has the identical isPrs-only mismatch. Its writes are still mocked with no
      real API, so it belongs to the later phase that builds those endpoints.
    No test added for finding 1 — IndicatorReportsPage is 1033 lines with no test file and
      would need ~10 mocked modules; GeographyPanel.test.jsx already covers canManage at
      the component level. Controller accepts that reasoning as proportionate.
  CONTROLLER-FOUND DEFECT in the fix itself (Rules of Hooks):
    IndicatorReportsPage.jsx:275 shipped as `const canManageGeography = isPrs &&
    usePermission('manage-settings');`. usePermission is a real hook (calls useAuth ->
    useContext), and && short-circuits, so the hook is CONDITIONALLY CALLED — hook count
    depends on the `variant` prop. eslint-plugin-react-hooks flags this; if variant ever
    changes for a mounted instance React throws "Rendered fewer hooks than expected."
    Fix round 2 dispatched: call the hook unconditionally, then combine. Also asked for a
    sweep of the file for any other hook behind &&/ternary/if/early-return.
  Fix round 2: ADDRESSED. IndicatorReportsPage.jsx:275-276 now reads
    `const hasManageSettings = usePermission('manage-settings');`
    `const canManageGeography = isPrs && hasManageSettings;`
    Sweep clean — no other use*() behind &&, a ternary, an if, or an early return.
    ESLint: correctly reported as ABSENT from mne_frontend (no script, dep, or config)
    rather than invented.
    Closure note: the controller verified this fix directly (read the exact lines, re-ran
    the conditional-hook grep, confirmed 86/86 + build) instead of dispatching a scoped
    re-review — the change is 2 lines that the controller itself specified, so a
    re-reviewer would only be re-reading what was already verified first-hand.

PHASE 2A COMPLETE.
  mems: 123/123 passing, Pint clean, 12 routes under /locations.
  mne_frontend: 86 passing across 12 files, build succeeds.
  src/utils/mockGeography.js (774 hardcoded LGAs) is GONE; guard test enforces it.
  Not committed — user handles version control.
  Carried into 2b: (a) asArray drops paginated payloads — 2b paginates, so fix it there;
    (b) SimpleCrudPanel htmlFor — do as its own small task BEFORE 2b rewires 5 more panels
    through it, since its absence already forced positional test queries that will
    silently target the wrong control when a field is added;
    (c) SystemManagementPanel gates on isPrs only — becomes a real gap the moment 2b gives
    its writes a real API with permission middleware. Fix it in the same task as its API.
  Release note: manage-settings is seeded but attached to NO role — on a fresh env only
    is_admin can write geography until it is granted.

PRE-2B TASK: SimpleCrudPanel label accessibility — complete.
  Modified: SimpleCrudPanel.jsx (id = slugify(title)-slugify(field.key) threaded to the
    <label htmlFor> and to the 3 labelable controls: textarea, select, default input)
  Modified: GeographyPanel.test.jsx (positional queries -> getByLabelText)
  DECISION (sound): checklist/itemList left unwired — their visible <input>s are a filter
    box and an add-item scratch pad, NOT the field's value, so pointing a label at either
    would mislabel it. Better than forcing an id onto a non-labelable wrapper.
  Collision-checked: every panel title across both consumers is distinct, including the
    two panels mounted simultaneously in the Disaggregation tab.
  Tests: 86 passing across 12 files (unchanged); build succeeds.
  This clears the way for 2b to rewire 5 more panels through SimpleCrudPanel without
  duplicating the brittle positional-query idiom.
