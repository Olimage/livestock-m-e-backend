### Task 4: Rewire `SystemManagementPanel`

**Files:**
- Modify: `mne_frontend/src/components/Data&Reporting/IndicatorReports/SystemManagement/SystemManagementPanel.jsx`
- Create: `mne_frontend/src/components/Data&Reporting/IndicatorReports/SystemManagement/SystemManagementPanel.test.jsx`
- Modify: `mne_frontend/src/components/Data&Reporting/IndicatorReports/IndicatorReportsPage.jsx`
- Delete: `mne_frontend/src/utils/mockDisaggregations.js`

**The seven sections** are NLGAS Pillars, Bond Deliverables, Sectoral Goals Management, Disaggregation Categories, Disaggregation Items, Departments, Indicator Baseline. Each currently uses either `useMockCrudStore` or `useLocalWriteOverlay` over a real GET.

- [ ] **Step 1: Read the panel in full** and map each section to its new hook before editing. It is long; do not patch blindly.

- [ ] **Step 2: Fix the permission gate.**

`IndicatorReportsPage.jsx` line ~840 passes `canManage={isPrs}` to `SystemManagementPanel`. Phase 2a's review flagged this as a latent mismatch that becomes real the moment these writes get permission middleware — which this phase does. Reuse the `hasManageSettings` value already computed in that component for `GeographyPanel`:

```jsx
<SystemManagementPanel canManage={isPrs && hasManageSettings} />
```

Call the hook unconditionally; do not write `isPrs && usePermission(...)`, which conditionally calls a hook and violates the Rules of Hooks. That exact bug was shipped and fixed in 2a.

- [ ] **Step 3: Replace `useLocalWriteOverlay` for Departments, Bond Deliverables, Sectoral Goals** with real mutations. Sectoral Goals must read from the **new** `GET /sectoral-goals` (which has `id`), not from `useSectoralGoals` in `useReporting.js`.

- [ ] **Step 4: Replace the mock stores** for Pillars, Disaggregations, and Baselines.

- [ ] **Step 5: Delete `utils/mockDisaggregations.js`** and remove `mockDisaggregations` from `src/mocks.guard.test.js`'s `ALLOWED`. Check the guard test's own self-check does not use it as its example — that trap bit Task 4 of 2a.

- [ ] **Step 6: Tests.** At minimum: a refused department delete (children) surfaces the server's 422 message verbatim; a create submits the right payload shape; the panel does not crash while queries are loading (`data` is `undefined` before react-query's `select` runs — the 2a Critical).

- [ ] **Step 7: Verify** — `npm test -- --run`, `npm run build`, grep for `mockDisaggregations` (expect none).

---


## Global Constraints

- **No git.** Version control is the user's. Do not run `git add`, `git commit`, `git status`, `git diff`, `git log`, or any other git command. Tasks end with verification, not commits.
- **Hard gate — read before you write.** Before writing any code for a task, read every file in that task's Files list, plus the controller or model whose behaviour you are mirroring. Phase 1 shipped four defects and Phase 2a one, all from describing a contract from memory or from what a mock implied. This is not advisory.
- **`npx rg` is unusable in this shell** (npm-init collision). Use the Grep tool or `grep -rn`.
- Response envelope for all new endpoints: `{status, message, data}`. Never `{success, …}`.
- All new endpoints sit behind `jwt.verify` + `permission:manage-settings` in `routes/v1/admin-crud.php`, which already exists and already holds the geography writes.
- Baselines: `mne_frontend` 86 tests across 12 files; `mems` 123 tests. Both green, both build/lint clean.
- Do not modify the Inertia controllers (`ProgramController`, `DepartmentController`, `BondDeliverableController`) or `routes/web.php`. Their existing feature tests passing untouched is the in-scope regression check.

## Blocker found during planning — read this first

**`GET /api/v1/programs/sectoral-goals` hides the primary key.**
`ProgramsController::getSectoralGoals` does:

```php
$sectoralGoals = SectoralGoal::get()->makeHidden(['id', 'created_at', 'updated_at']);
```

So the frontend receives `{uuid, code, title, description}` with **no `id`**. Every update and delete needs to address a specific goal, and `SectoralGoal` does not override `getRouteKeyName()`, so route-model binding resolves by primary key. As things stand, Sectoral Goals CRUD is impossible.

Two ways out, and Task 1 takes the second:

1. Drop `id` from the `makeHidden` list. Smallest change, but silently alters a payload other consumers may already read.
2. **Leave `/programs/sectoral-goals` exactly as it is** and add a new `GET /api/v1/sectoral-goals` alongside the writes, returning `{id, uuid, code, title, description}`. The frontend's admin panel reads the new one; anything still reading the old one is unaffected.

Option 2 is chosen because `/programs/sectoral-goals` is already consumed by `useSectoralGoals` in `hooks/useReporting.js`, which feeds `SystemManagementPanel`'s read path *and* other reporting screens. Changing a shared payload to serve one admin panel is the kind of blast radius this programme keeps getting bitten by.

## What the research established

Validation rules, copied verbatim from the Inertia controllers that own these models today:

| Entity | Store rules | Delete guard |
|---|---|---|
| `NlgasPillar` | `code` required·string·max:255·unique:nlgas_pillars; `title` required·string·max:255; `description` nullable·string | none |
| `SectoralGoal` | `code` required·string·max:255·unique:sectoral_goals; `title` required·string·max:255; `description` nullable·string | none |
| `DisagregationCategory` | `name` required·string·max:255·unique:disagregation_categories,name; `items` nullable·array; `items.*` string·max:255 | none |
| `DisagregationItem` | `name` required·string·max:255 (nested under a category) | none |
| `IndicatorBaselineYear` | `indicatorable_type` required·string **and must be a key of `ResultChainIndicators::TYPES`**; `indicatorable_id` required·integer; `baseline_year`/`target_year` nullable·integer·min:1900·max:2100; `baseline`/`target`/`actual` required·numeric | none |
| `Department` | `name` required·string·max:255; `is_technical` boolean; `is_agency` boolean; `parent_id` nullable·exists:departments,id. **`slug` is derived** — `Str::slug($data['name'])`, never accepted from the client | **rejects if `children()->exists()`** |
| `BondDeliverable` | `code` required·string·max:50·unique (with the `,{id}` exception on update); `deliverable` required·string; `indicator_ids` nullable·array; `indicator_ids.*` exists:bond_output_indicators,id | none, but **must `detach()` the `bondOutputIndicators` pivot before deleting** |

Other facts confirmed by reading, not assumed:

- `Department`'s API GET (`DepartmentController::getDepartments`) **keeps `id`** — there is even a comment explaining why. No change needed there.
- `BondDeliverable`'s API GET (`Api\BondDeliverableApiController::index`) returns `{id, title, code, stats, indicators}` — note it maps `deliverable` to `title`. The write endpoints take `deliverable`; the frontend must not confuse the two.
- Only `Department` has a delete guard. `BondDeliverable::destroy` detaches its pivot first. Everything else deletes freely.
- `SectoralGoal` and `NlgasPillar` both auto-assign `uuid` in a `creating` hook, so `uuid` is never client-supplied.

---

