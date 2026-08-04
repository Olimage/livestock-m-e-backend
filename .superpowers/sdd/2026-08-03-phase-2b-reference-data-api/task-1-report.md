# Task 1 report — Pillars, sectoral goals, and disaggregations (`mems`)

## Files touched

- Created: `app/Http/Controllers/Api/ReferenceDataApiController.php`
- Modified: `routes/v1/admin-crud.php` (added a second `Route::middleware(['jwt.verify','permission:manage-settings'])` group below the untouched geography group; geography group is byte-for-byte as it was)
- Created: `tests/Feature/ReferenceDataApiTest.php` (15 tests)

`ProgramController`, `ProgramsController`, and `routes/web.php` were not touched.

## Decisions made mid-task (escalated, then implemented per coordinator's answers)

### 1. NL-GAS Pillars have no `uuid` — response contract updated

`nlgas_pillars` (`database/migrations/2025_11_14_112237_create_nlgas_pillars_table.php`) never adds a `uuid` column, and `App\Models\NlgasPillar` has no `creating` hook for one — only `SectoralGoal` does (model hook + `2026_01_16_093423_add_uuid_to_sectoral_goals_table.php`). The brief's assumption that both entities have a working `uuid` was wrong.

**Implemented contract (corrected from the brief):**
```
GET  /api/v1/nlgas-pillars   → [{id, code, title, description}]        # no uuid key
GET  /api/v1/sectoral-goals  → [{id, uuid, code, title, description}]   # uuid present
```
This asymmetry is intentional and documented in a class-level PHPDoc block on `ReferenceDataApiController`. Do not add a `uuid` column/migration for `NlgasPillar` to "fix" this — it was a deliberate call (adding one would be a schema change serving no consumer; route binding and the frontend both address rows by `id`).

### 2. `description` is `required|string` in the new API — not `nullable` as `ProgramController` validates it

Both `nlgas_pillars.description` and `sectoral_goals.description` are `$table->text('description')` — **NOT NULL, no default**. `ProgramController::storeNlgasPillar`/`storeSectoralGoal` validate `description` as `nullable|string`, which is wrong about the schema it writes to: omitting the field there throws a DB exception, not a graceful null (tests run on SQLite in-memory per `phpunit.xml`, which enforces `NOT NULL` strictly).

`ReferenceDataApiController` validates `description` as `required|string` for both pillars and goals, so a missing field returns 422 with a usable message instead of a 500. This is a deliberate, documented divergence from `ProgramController` (see the controller's class PHPDoc) — per instruction, `ProgramController` itself was not touched; its own bug is out of scope for this task.

Tests `test_creating_a_pillar_without_description_is_a_422_not_a_500` and the sectoral-goal equivalent assert this.

## Disaggregation-category delete — what it actually does to items, and how I found out

**It orphans them. It does not cascade.**

`disagregation_items.disagregation_category_id` is declared as:
```php
$table->unsignedBigInteger('disagregation_category_id')->constrained()->onDelete('cascade');
```
This *looks* like a cascading FK, but `constrained()` is only a real method on the `ForeignIdColumnDefinition` returned by `$table->foreignId(...)`. Called on a plain `unsignedBigInteger()` column (a generic `ColumnDefinition`/`Fluent`), it's absorbed by `Fluent`'s magic `__call` as an inert attribute — no foreign key is ever registered with the schema builder, and no `ON DELETE CASCADE` exists in the actual database.

I did not take this on faith from reading the migration. I wrote the test assuming cascade first (per the brief's suggestion to consider it), ran it, and it failed: `assertFalse(DisagregationItem::whereKey($item->id)->exists())` returned `true` (item still there). That failure is the proof the FK isn't real. I flipped the test to `test_deleting_a_category_orphans_its_items`, asserting the category is gone and the item row still exists, with a comment explaining why, so a future reader doesn't try to "fix" the test back into a cascade assumption.

## Commands run and real output

**New test file:**
```
php artisan test --filter=ReferenceDataApiTest --compact
Tests:    15 passed (46 assertions)
Duration: 1.62s
```
(First run had 1 failure — the cascade assumption above — fixed by correcting the assertion to match observed behavior, then reran to green.)

**Full suite:**
```
php artisan test --compact
Tests:    138 passed (477 assertions)
Duration: 9.22s
```
Baseline was 123; 123 + 15 new = 138. Nothing broke.

**Route verification** (`route:list --path=...` plus a JSON dump to confirm middleware, since the default table view truncates the middleware column):
- `--path=nlgas-pillars`: all 4 `api/v1/nlgas-pillars*` routes carry `App\Http\Middleware\VerifyJWTToken` + `App\Http\Middleware\CheckPermission:manage-settings` (confirmed via `route:list --json`).
- `--path=disaggregation`: all 7 `api/v1/disaggregation-categories*` routes carry the same two middleware, confirmed directly in the `-v` output.
- Sectoral-goals routes sit in the same middleware group as pillars/disaggregation, so they inherit identically (not spot-checked individually beyond the group structure, since the group is one unbroken `Route::middleware([...])->group()` block).

**Pint:**
```
vendor/bin/pint --dirty
PASS ... 9 files
```
No fixes needed.

## Self-review

- Controller mirrors `GeographyApiController`'s `{status, message, data}` envelope and its single `ok()` helper. I did not add a `refuse()` helper — none of the four entities in this task has a delete guard (the validation table says "none" for all), so an unused helper would be dead code. If a future task adds a guard (e.g., "don't delete a pillar with programs"), add `refuse()` then.
- `/api/v1/programs/sectoral-goals` (`ProgramsController::getSectoralGoals`) is confirmed byte-for-byte untouched; `test_legacy_programs_sectoral_goals_endpoint_still_omits_id` hits it directly and asserts `id` is absent while `uuid` is present, alongside `test_new_sectoral_goals_endpoint_includes_id` for the new endpoint.
- One thing I did not independently verify: whether any other route elsewhere in `routes/api.php` or `routes/web.php` already claims the URIs `nlgas-pillars`/`sectoral-goals`/`disaggregation-categories` under `/api/v1` outside of what I read (I did check `routes/api.php` and confirmed only `programs/sectoral-goals` exists there, no bare `sectoral-goals` or `nlgas-pillars` at the v1 root before my change) — route:list showed no duplicate URIs, so I'm confident there's no collision, but flagging the check I made rather than assuming.

## Fix round 1 — unscoped nested item binding (review finding, Important)

**The problem.** `PUT`/`DELETE /api/v1/disaggregation-categories/{category}/items/{item}` bound `{item}` by primary key with no check that it belongs to `{category}`. Neither the route nor `updateDisagregationItem`/`destroyDisagregationItem` verified the parent-child relationship, so an item could be updated or deleted through any category's URL, not just its own.

**Approach chosen: `->scopeBindings()` on both routes, not an explicit `abort_unless` in the controller.**

Reasoning: `DisagregationCategory` already has an `items()` `hasMany` relation, which is exactly the convention Laravel's implicit-binding scoping looks for (it resolves the child route parameter through the parent model's relationship method named after the pluralized child parameter — `items`). That means `scopeBindings()` requires zero controller changes, can't be forgotten on a future third nested route, and keeps the "is this item mine" logic in one declarative place (the route file) rather than duplicated across every method that receives both models. An explicit `abort_unless($item->disagregation_category_id === $category->id, 404)` would work too, but it's logic a future method could omit; scoping it at the route level makes the omission impossible.

**Negative check (test fails without the fix).** Wrote `test_an_item_cannot_be_updated_or_deleted_through_a_different_categorys_url` (two categories, item created on the first, then hit via the second category's URL). Temporarily removed `->scopeBindings()` from both routes and reran just that test:
```
php artisan test --filter=test_an_item_cannot_be_updated_or_deleted_through_a_different_categorys_url --compact
FAILED > an item cannot be updated or deleted through a different category's url
Expected response status code [404] but received 200.
Failed asserting that 200 is identical to 404.
Tests: 1 failed (1 assertions)
```
Confirms the hole was real and the test catches it. Restored `->scopeBindings()` on both routes.

**Verification after the fix:**
```
php artisan test --filter=ReferenceDataApiTest --compact
Tests:    16 passed (50 assertions)
Duration: 1.67s

php artisan test --compact
Tests:    139 passed (481 assertions)
Duration: 8.47s

vendor/bin/pint --dirty
PASS ... 9 files
```
Baseline going into this round was 138 (from the first round); 138 + 1 new test = 139. Nothing else broke.

**Files touched in this round:** `routes/v1/admin-crud.php` (added `->scopeBindings()` to the two nested item routes), `tests/Feature/ReferenceDataApiTest.php` (added the cross-category regression test).

**Noted but explicitly not acted on, per the coordinator's instruction:** `disagregation_items` has no real foreign key constraint at all (the inert `constrained()` call), so there's no DB-level referential integrity for that table. That's a separate, pre-existing schema defect being tracked by the project owner outside this task — no migration was added here.
