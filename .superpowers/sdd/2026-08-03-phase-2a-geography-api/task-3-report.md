# Task 3 Report — Frontend location service and hooks

## Summary

Implemented `locationApi` service, `useLocations` hooks (queries + mutations), and the test
file, following the brief verbatim after confirming its conventions against the existing
`workflowApi.js` / `useWorkflows.js` / `unwrap.js` / `apiConfig.js` precedent. No ambiguities
found — the brief's code matched the shape of the existing patterns exactly, so it was used
as-is.

## Files touched

- **Modified** `mne_frontend/src/config/apiConfig.js` — added a `LOCATIONS` block to `ENDPOINTS`
  (after `DEPARTMENTS`, before `DATA_HEALTH`): `ZONES`, `ZONE_DETAIL(id)`, `STATES`,
  `STATE_DETAIL(id)`, `LGAS`, `LGA_DETAIL(id)`.
- **Created** `mne_frontend/src/services/locationApi.js` — `locationApi.{zones,states,lgas}`
  reads and `{create,update,delete}{Zone,State,Lga}` writes, each routed through
  `axiosInstance` + `ENDPOINTS.LOCATIONS.*` + `unwrap`.
- **Created** `mne_frontend/src/hooks/useLocations.js` — `useZones`/`useStates`/`useLgas`
  react-query hooks with `select: asArray` pagination guard, and
  `useZoneMutations`/`useStateMutations`/`useLgaMutations` each returning
  `{ create, update, remove }` mutations that invalidate the `['locations']` query-key tree
  on success.
- **Created** `mne_frontend/src/hooks/useLocations.test.js` — 6 tests covering: zone list
  unwrap, LGA filter param passthrough, 422 delete rejection reaching the caller, state POST
  path, LGA PUT path, and pagination payload passthrough.

## Commands run (real output)

```
$ npm test -- src/hooks/useLocations.test.js
✓ src/hooks/useLocations.test.js (6 tests) 4ms
Test Files  1 passed (1)
     Tests  6 passed (6)
```

```
$ npm test -- --run
✓ src/services/unwrap.test.js (9 tests)
✓ src/models/auth/RoleMapper.test.js (7 tests)
✓ src/config/apiConfig.test.js (9 tests)
✓ src/utils/departmentScope.test.js (12 tests)
✓ src/hooks/useLocations.test.js (6 tests)
✓ src/mocks.guard.test.js (4 tests)
✓ src/hooks/useIndicatorHealthTabs.test.js (9 tests)
✓ src/hooks/useIndicatorReportRows.test.js (5 tests)
✓ src/hooks/useIndicatorSubmissions.test.js (9 tests)
✓ src/auth/usePermission.test.js (3 tests)
✓ src/components/Data&Reporting/PerformanceReporting/IndicatorReportSubmissions.test.jsx (4 tests)
Test Files  11 passed (11)
     Tests  77 passed (77)
```
(One pre-existing, unrelated `act(...)` warning printed to stderr from
`IndicatorReportSubmissions.test.jsx` — not touched by this task, all its assertions still pass.)

```
$ npm run build
✓ 2171 modules transformed.
✓ built in 8.01s
```
(Pre-existing chunk-size warning for `index-*.js` at ~2.68 MB — unrelated to this task; no new
files were added to that bundle beyond the small service/hook modules.)

## Anything surprising

Nothing. The brief's test file, service, and hooks matched the existing `workflowApi.js` /
`useWorkflows.js` pattern closely enough that no deviation was needed. `unwrap.js` already
handles both `{status}` and `{success}` envelopes and the "422 → thrown Error" behavior the
brief describes, confirming the delete-rejection test needed no special-casing in the service.

## Self-review

- Endpoints added in the exact location specified (after `DEPARTMENTS`), matching existing
  `ENDPOINTS` object formatting (trailing commas, arrow-function detail helpers).
- Service functions are 1:1 with the brief: reads default `params = {}`, all calls piped
  through `.then(unwrap)`, no bypass of the axios/config layer.
- Hooks use query keys exactly as specified: `['locations','zones',params]` etc. Mutations
  invalidate the whole `['locations']` tree rather than fine-grained keys — matches the
  brief's stated reasoning (small lists, infrequent writes, avoids tracking which counts moved).
- `select: asArray` is present on all three query hooks as a guard against a paginated
  payload, consistent with the "pagination is opt-in" deviation documented in the brief's
  spec-reconciliation section.
- No component was modified; `utils/mockGeography.js` untouched, per constraints.
- No git commands were run at any point.
- Test count moved from 71 (10 files) to 77 (11 files) exactly as predicted — no other test
  file changed count, confirming no side effects.
