### Task 1: Pillars, sectoral goals, and disaggregations (`mems`)

**Files:**
- Create: `app/Http/Controllers/Api/ReferenceDataApiController.php`
- Modify: `routes/v1/admin-crud.php` (add a second group; leave the geography group untouched)
- Create: `tests/Feature/ReferenceDataApiTest.php`

**Interfaces:**
- Consumes: the `permission:manage-settings` key seeded in Phase 2a; the `{status, message, data}` envelope helpers established by `GeographyApiController` — read that file and match its shape.
- Produces:
```text
GET    /api/v1/nlgas-pillars                                    → [{id, uuid, code, title, description}]
POST   /api/v1/nlgas-pillars                {code,title,description?}      → 201
PUT    /api/v1/nlgas-pillars/{pillar}       {code,title,description?}      → 200
DELETE /api/v1/nlgas-pillars/{pillar}                                      → 200
GET    /api/v1/sectoral-goals                                   → [{id, uuid, code, title, description}]
POST   /api/v1/sectoral-goals               {code,title,description?}      → 201
PUT    /api/v1/sectoral-goals/{goal}        {code,title,description?}      → 200
DELETE /api/v1/sectoral-goals/{goal}                                       → 200
GET    /api/v1/disaggregation-categories                        → [{id, name, items:[{id,name}]}]
POST   /api/v1/disaggregation-categories    {name, items?[]}               → 201
PUT    /api/v1/disaggregation-categories/{category}  {name}                → 200
DELETE /api/v1/disaggregation-categories/{category}                        → 200
POST   /api/v1/disaggregation-categories/{category}/items  {name}          → 201
PUT    /api/v1/disaggregation-categories/{category}/items/{item}  {name}   → 200
DELETE /api/v1/disaggregation-categories/{category}/items/{item}           → 200
```

- [ ] **Step 1: Read the sources**

Read, in full: `app/Http/Controllers/Api/GeographyApiController.php` (the pattern to match), `app/Http/Controllers/ProgramController.php`'s `storeSectoralGoal` / `storeNlgasPillar` / `storeDisagregationCategory` / `storeDisagregationItem` / their update and destroy siblings, `app/Models/{NlgasPillar,SectoralGoal,DisagregationCategory,DisagregationItem}.php`, and `routes/v1/admin-crud.php`.

Confirm for yourself: the exact validation strings, that `uuid` is set by a `creating` hook on both `NlgasPillar` and `SectoralGoal`, that neither overrides `getRouteKeyName()`, and that the nested item routes bind both `{category}` and `{item}`. If any differ from the table above, follow the code and report the discrepancy.

- [ ] **Step 2: Write the failing test**

`tests/Feature/ReferenceDataApiTest.php`, modelled on `tests/Feature/GeographyApiWriteTest.php` (read it first for the JWT + permission fixtures). Cover at minimum:

1. Unauthenticated `GET /api/v1/nlgas-pillars` → 401.
2. Authenticated without `manage-settings` → 403 on a write.
3. Create a pillar → 201, row exists, `uuid` populated.
4. Duplicate `code` → 422 (proves the `unique` rule carried over).
5. Update, then delete, a pillar → 200 each.
6. **`GET /api/v1/sectoral-goals` includes `id`** — the explicit regression guard for the blocker above. Assert the key is present.
7. `GET /api/v1/programs/sectoral-goals` still **omits** `id` — proves the existing shared payload was not disturbed.
8. Create a category with `items: ['Male','Female']` → 201 and both items persisted and returned nested.
9. Add an item to an existing category → 201; update it; delete it.
10. Deleting a category removes its items (or leaves them, depending on what the migration's FK actually does — **read the migration and assert the real behaviour**, do not assume cascade).

- [ ] **Step 3: Run the test, expect failure**

`php artisan test --filter=ReferenceDataApiTest` → fails, routes not defined.

- [ ] **Step 4: Implement**

One controller, methods grouped by entity. Mirror `GeographyApiController`'s `refuse()`/`ok()` helpers rather than inventing new ones. For the category `store`, replicate `ProgramController::storeDisagregationCategory`'s behaviour exactly: create the category from `name` only, then `array_filter` the items and `create` each with a trimmed name.

Register the routes in `routes/v1/admin-crud.php` inside a **new** group, leaving the existing geography group as it is.

- [ ] **Step 5: Verify**

- `php artisan test --filter=ReferenceDataApiTest` → all passing
- `php artisan test` → 123 + your new tests, nothing broken
- `php artisan route:list --path=nlgas-pillars` and `--path=disaggregation` → every route shows `jwt.verify` and `CheckPermission:manage-settings`
- `vendor/bin/pint --dirty`

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

