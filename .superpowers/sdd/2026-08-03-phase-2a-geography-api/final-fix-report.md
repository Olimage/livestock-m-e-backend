# Final Whole-Plan Review — Fix Pass Report

Date: 2026-08-03

## Fix round 2 — Rules of Hooks violation in the Finding 1 fix

Coordinator caught a real bug in the Finding 1 fix: `IndicatorReportsPage.jsx:275` had
```js
const canManageGeography = isPrs && usePermission('manage-settings');
```
`&&` short-circuits, so `usePermission` (which calls `useAuth()` → `useContext`) was skipped
entirely on any render where `isPrs` is false — a conditional hook call. Fixed by calling the
hook unconditionally and combining afterward:
```js
const hasManageSettings = usePermission('manage-settings');
const canManageGeography = isPrs && hasManageSettings;
```
Same runtime behavior, no conditional hook call.

**Hook-call sweep of the same file** (`grep -n "use[A-Z]\w*("` plus a scan for early returns):
every `use*` call in `IndicatorReportsPage.jsx` (`useAuth`, `useLocation`, `useQueryClient`,
`usePermission`, `useState` ×8, `useEffect` ×3, `useLocalCatalogDeletion`,
`useReportingObligations`, `useSuppressedIndicators`, `useImpactIndicators`,
`useProgramIndicators`, `useReportingPeriods`, `useDepartments`, `useSectoralGoals`,
`useIndicatorReports` ×2, `useApproveIndicatorReport`, `useMemo` ×many, `useMockCrudStore` ×2,
`useOverlayStore`) is a direct top-level `const x = useXxx(...)` assignment — none behind a
`&&`, ternary, `if`, or loop. Confirmed there is exactly one `return` in the component body (the
final JSX return); every hook call sits above it, so no early-return path can skip a subset of
hooks either. Sweep is clean — no other Rules of Hooks violations found in this file.

**Verification (real output), re-run after the fix:**
```
npm test -- --run   → Test Files 12 passed (12) / Tests 86 passed (86)
npm run build       → ✓ built in 6.79s (same pre-existing >500kB chunk warning, unrelated)
```
**ESLint:** the project has no `eslint` script in `package.json` (`scripts`: dev, start, build,
preview, test — no lint entry), no `eslint`/`eslint-plugin-*` in dependencies or devDependencies,
and no `.eslintrc*`/`eslint.config*` file in `mne_frontend`. There is no lint tooling to run here
— reporting that rather than inventing an ad hoc eslint invocation.

Files touched in this round: `mne_frontend/src/components/Data&Reporting/IndicatorReports/IndicatorReportsPage.jsx` only (two-line change, lines 275–276). No PHP touched, no git command run.

## Finding 1 (Important) — UI gated on module identity, not the backend permission

**Fixed.** File: `mne_frontend/src/components/Data&Reporting/IndicatorReports/IndicatorReportsPage.jsx`

- Imported `usePermission` from `src/auth/usePermission.js`.
- Added a derived flag next to the other role-derived flags (`canEditCatalog`, `canDeleteCatalog`):
  ```js
  const canManageGeography = isPrs && usePermission('manage-settings');
  ```
- Changed `<GeographyPanel canManage={isPrs} />` → `<GeographyPanel canManage={canManageGeography} />`.

**Admin bypass confirmed correct.** Read `src/auth/AuthContext.jsx:116`:
`hasPermission = (key) => !!user?.is_admin || (user?.permissions || []).includes(key)`.
`usePermission` just delegates to this. Admins bypass by design — intended, matches the backend's own admin-bypass semantics — so no special-casing needed on top of it.

**Other panel with the same mismatch (reported, not fixed):** `SystemManagementPanel` at line 840 of the same file also does `canManage={isPrs}` (Departments/Disaggregation/NL-GAS/Bond/Sectoral-Goals/Baseline sub-tabs). Per the brief, this is **not** fixed in this pass — confirmed via `SystemManagementPanel.jsx`'s own header comment that every write path there is either `useLocalWriteOverlay` or `createMockCrudStore` (no real JSON API for any write yet), so there is no backend permission to mismatch against today. Worth applying the same `usePermission('manage-settings')` gate there once its writes move onto real endpoints.

**Test:** Not added. `IndicatorReportsPage.jsx` (1033 lines) has no existing test file, and rendering it requires mocking ~10 modules (`AuthContext`, react-query, react-router, `useDepartments`, `useLocalCatalogDeletion`, `useMockCrudStore`, `useOverlayStore`, five `useReporting` hooks, three `useDashboard` hooks). Standing up a full render harness for one prop-gating line would be disproportionate to the fix. `GeographyPanel.test.jsx` already covers `canManage` behavior at the component level (renders with `canManage` truthy throughout); the bug was purely in what the parent page passed in, which is now a one-line, directly-readable expression (`isPrs && usePermission('manage-settings')`) rather than logic that benefits from a dedicated unit test.

## Finding 2 (Important) — Postman collections documented the pre-auth API

**Fixed** in both files. Verified the real behavior first against source:
- `mems/routes/api.php` (`Route::prefix('locations')...middleware('jwt.verify')`) — confirms the three GETs now require JWT, not public.
- `mems/routes/v1/admin-crud.php` — confirms the nine write routes and `permission:manage-settings`.
- `mems/app/Http/Controllers/LocationController.php` (`ApiGetZones/States/Lgas`) — confirms `zone_id`/`state_id` are optional (`nullable|exists:...`), the real field lists, and the `per_page`/`search` behavior.
- `mems/app/Http/Controllers/Api/GeographyApiController.php` — confirms create/update/delete payloads, fillable fields, and the 422 refusal wording for zones-with-states / states-with-lgas.

### `FMLD-MEMS.postman_collection.json`
Replaced the "Locations (Geography)" folder (previously all three GETs `"auth": {"type":"noauth"}`, `zone_id`/`state_id` marked required, old `{id,name,code}` shape) with:
- The three GETs now inherit the collection-level bearer auth (removed the `noauth` override, matching how every other authenticated folder in this file is written — no `auth` key at request level).
- `zone_id`/`state_id` redocumented as optional filters, `search` and `per_page` documented (with `per_page` presence switching to a paginator).
- Response shapes updated to the real fields: zones `{id, uuid, name, code, states_count}`; states `{id, uuid, name, zone_id, zone_code, zone_name, lgas_count}`; lgas `{id, uuid, name, state_id, state_name, zone_id, zone_code, zone_name}`.
- Added a new sibling folder "Locations (Geography) — Admin Writes" with all nine write endpoints (Create/Update/Delete × Zone/State/LGA), each noting the `manage-settings` requirement and the 422-on-children-present behavior.

### `FMLD_MEMS_API.postman_collection.json`
Same substance, but this file's convention additionally attaches a `response` example (with `originalRequest`/`status`/`code`/`body`) to every request, so I matched that style for both the updated GETs and the nine new write endpoints. This file uses tab indentation throughout; the edit was done with a small Node script that spliced the exact `"name": "Locations"` folder block (old lines 204–298) out and the two new folders in, rather than a manual text edit, specifically to guarantee the tab-exact structural match this file needs — a raw string edit risked silent whitespace drift across ~180 lines of nested JSON.

Both files validated after editing:
```
node -e "JSON.parse(require('fs').readFileSync('FMLD-MEMS.postman_collection.json','utf8')); console.log('ok')"      → ok
node -e "JSON.parse(require('fs').readFileSync('FMLD_MEMS_API.postman_collection.json','utf8')); console.log('ok')" → ok
```
Top-level folder list for the second file after edit (sanity check, unchanged order/count of siblings otherwise): `Auth, User, Locations, Locations — Admin Writes, Programs, Departments, Indicator Forms, Enumeration, Indicator Reporting, Supervisor-Enumerators, Dashboard Analytics, Activity Logs, Dashboard (Session Auth)`.

Left `mems/api_collection/**/Location/*.yml` (Bruno) untouched, as instructed — those already use `auth: inherit`.

No PHP files were read for editing purposes beyond confirming behavior (routes/controllers were only *read*, never modified).

## Finding 3 (Minor) — dangling "Performance Index:" label

**Fixed** by moving the label, not dropping it. File: `mne_frontend/src/components/SectorialOutcome/SectorMap/SectorMapFooter.jsx`.

Removed the now-empty left-hand div and folded the `<span>` label directly into the same flex row as the High/Medium/Low/Minimal swatches, so it reads as "Performance Index: [swatches]" — one row, no orphaned label, no empty sibling div. Also dropped `justify-between` on the outer container since there's now a single flex child (kept `items-center`). Chose "move" over "drop" because the label carries real information (what the colors mean) that the swatches alone don't state.

## Finding 4 (Minor) — stale `PILLAR_PROGRAMS` comment

**Fixed.** File: `mne_frontend/src/hooks/useDashboard.js:37`. Reworded the `useSectorMap` doc comment from referencing the deleted `PILLAR_PROGRAMS` matrix to describing what it actually fetches now: `// Per-state sector performance data for the Nigeria map (`/sector-map`).` — matched against `dashboardApi.sectorMap: () => axiosInstance.get('/sector-map').then(unwrap)` in `services/dashboardApi.js`.

Grep confirms no live references remain: the only two `PILLAR_PROGRAMS` hits left in `mne_frontend` are in `utils/mockSustainability.js:71` and `components/SectorialOutcome/SectorMap/data/Pillar_data.js:6,9` — both are already deliberate historical comments explicitly noting the symbol was removed, not calls to it.

## Verification (real output)

**Tests** — `npm test -- --run` in `mne_frontend`:
```
Test Files  12 passed (12)
     Tests  86 passed (86)
```
Same 12 files, same 86 count as the stated baseline — no regressions, no test added (per Finding 1 reasoning above). One pre-existing `act()` warning in `IndicatorReportSubmissions.test.jsx` (unrelated to this pass — not a new failure, not touched).

**Build** — `npm run build` in `mne_frontend`: succeeded, `✓ built in 7.03s`. Pre-existing chunk-size warning (`index-*.js` ~2.6 MB) is unrelated to this change.

**JSON validation** — both edited Postman files parse cleanly (see commands above).

**PILLAR_PROGRAMS grep** — 2 remaining hits, both historical comments, no live references (see Finding 4).

## Self-review

- Finding 1: gate now matches backend enforcement exactly (`isPrs && usePermission('manage-settings')`); admin bypass verified in source, not assumed. The sibling `SystemManagementPanel` mismatch was found and correctly left alone since its writes are still fully mocked — fixing it now would be gating against a permission the backend doesn't check yet, which is out of scope.
- Finding 2: the two collections previously asserted the geography API was public — the single most misleading artifact given this phase's entire purpose was to stop that. Both now match `routes/api.php`, `routes/v1/admin-crud.php`, `LocationController`, and `GeographyApiController` exactly, including the optional-filter behavior, the real response shapes, and the 9 write endpoints with their permission and 422 semantics. Edited surgically (single named folder in each file), verified both still parse.
- Findings 3 & 4: both small, low-risk, single-file changes; no behavior change, only markup/comment correctness.
- No PHP file was modified (only read for verification). No git command was run at any point.
