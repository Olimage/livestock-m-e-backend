### Task 2: Departments, bond deliverables, baselines (`mems`)

**Files:**
- Create: `app/Http/Controllers/Api/AdminEntityApiController.php`
- Modify: `routes/v1/admin-crud.php`
- Create: `tests/Feature/AdminEntityApiTest.php`

**Interfaces:**
- Consumes: same envelope and middleware as Task 1.
- Produces:
```text
POST   /api/v1/departments              {name,is_technical?,is_agency?,parent_id?}  → 201
PUT    /api/v1/departments/{department} {name,is_technical?,is_agency?,parent_id?}  → 200
DELETE /api/v1/departments/{department}                → 200, or 422 if it has children
POST   /api/v1/bond-deliverables        {code,deliverable,indicator_ids?[]}         → 201
PUT    /api/v1/bond-deliverables/{bondDeliverable}                                  → 200
DELETE /api/v1/bond-deliverables/{bondDeliverable}     → 200 (detaches pivot first)
GET    /api/v1/indicator-baselines?indicatorable_type=&indicatorable_id=  → [{...}]
POST   /api/v1/indicator-baselines                                                  → 201
PUT    /api/v1/indicator-baselines/{baseline}                                       → 200
DELETE /api/v1/indicator-baselines/{baseline}                                       → 200
```

Note the existing `GET /api/v1/departments` and `GET /api/v1/bond-deliverables` are **read-only endpoints that already exist and are already consumed**. Add writes only; do not touch either index.

- [ ] **Step 1: Read the sources**

`app/Http/Controllers/DepartmentController.php` (validation, the `Str::slug` derivation, the `children()->exists()` guard), `app/Http/Controllers/BondDeliverableController.php` (the `unique:…,{id}` update rule and the `detach()` before delete), `ProgramController::storeBaseline`/`updateBaseline`/`destroyBaseline`, `app/Support/ResultChainIndicators.php` (the `TYPES` whitelist), and `app/Models/{Department,BondDeliverable,IndicatorBaselineYear}.php`.

- [ ] **Step 2: Write the failing test**

`tests/Feature/AdminEntityApiTest.php`. Cover at minimum:

1. Create a department; assert `slug` is derived from `name` and that a client-supplied `slug` is **ignored**, not persisted.
2. Delete a department with children → **422** with `{status:false}` and a clear message; the department still exists.
3. Delete a childless department → 200.
4. `parent_id` pointing at a non-existent department → 422.
5. Create a bond deliverable with `indicator_ids` → 201 and the pivot rows exist.
6. Update it keeping the same `code` → 200 (proves the `unique:…,{id}` exception works and does not self-collide).
7. Delete it → 200, and the pivot rows are gone.
8. Create a baseline with a valid `indicatorable_type` → 201.
9. Create one with a type **outside** `ResultChainIndicators::TYPES` → 422.
10. `GET /api/v1/indicator-baselines?indicatorable_type=…&indicatorable_id=…` returns only matching rows.
11. Writes require `manage-settings` → 403 without it.

- [ ] **Step 3: Run, expect failure. Step 4: Implement. Step 5: Verify** — same commands as Task 1, filtered to `AdminEntityApiTest`, plus `php artisan test` overall and Pint.

The type whitelist must be enforced with a validation rule or an explicit check that produces **422**, not `abort(422)` bare — the frontend needs a message. `ProgramController` uses `abort_unless(..., 422)`, which yields no body; improve on it here and say so in the report.

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

