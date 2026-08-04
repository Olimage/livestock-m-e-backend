# Task 1 review package

No git diff — this project forbids agent git usage, nothing is committed, no commit range
exists. Review by reading files. Absence of commits is **not** a defect.

Working directory: `d:\DAVID\New folder\Olimage\FMLD_PROJECT\mems`

## Circumstance

The implementer was killed by an API session limit while writing its report. The work and the
report are both on disk and appear complete; the controller re-ran verification first-hand
(below). Nothing in the implementer's account should be taken on trust that the code does not
independently support.

## Files

| Path | State |
|---|---|
| `app/Http/Controllers/Api/ReferenceDataApiController.php` | Created, 188 lines. Read in full. |
| `tests/Feature/ReferenceDataApiTest.php` | Created, 252 lines. Read in full. |
| `routes/v1/admin-crud.php` | Modified — a **second** route group added; the Phase 2a geography group must be untouched. |

## Two rulings the controller issued mid-task — verify they were implemented as stated

The implementer **stopped and asked** rather than guessing, having found two defects in the
brief. Both were verified by the controller against the migrations before ruling.

**Ruling 1 — NL-GAS Pillars carry no `uuid`.** The brief's contract wrongly specified
`{id, uuid, code, title, description}`. `nlgas_pillars` has exactly one migration
(`2025_11_14_112237`) and it never adds a `uuid` column; `NlgasPillar` has no `creating` hook.
The brief had generalised from `SectoralGoal`, which genuinely does have one.

Ruled: return `{id, code, title, description}` — no `uuid` key at all. A permanently-null key
would be a lie in the payload; a migration + backfill for a field nothing consumes is scope
creep. **Sectoral Goals keep `uuid`, because that table really has one.** The asymmetry is
intentional and truthful.

**Ruling 2 — `description` is `required`, deliberately diverging from the Inertia rule.**
Both `nlgas_pillars.description` and `sectoral_goals.description` are `$table->text('description')`
— NOT NULL, no default — while `ProgramController` validates them `nullable|string`. Omitting
the field there produces a database error, not a null row.

Ruled: the new API validates `required|string`, so the client gets a clear 422 instead of a 500.
`ProgramController` was **not** touched — that is a real pre-existing bug on a separate surface
with its own tests and users, and it is tracked separately.

## The blocker this task worked around

`GET /api/v1/programs/sectoral-goals` does `->makeHidden(['id', ...])`, so it exposes no primary
key — making update/delete impossible. It is consumed by `useSectoralGoals` in the frontend's
`hooks/useReporting.js`, which feeds several reporting screens, so it was **not** modified.
Instead a new `GET /api/v1/sectoral-goals` was added that includes `id`.

## Controller's own verification (already performed — do not re-run)

- `php artisan test` → **138 passed, 477 assertions** (baseline was 123).
- `indexNlgasPillars` confirmed at line 38: `NlgasPillar::get(['id', 'code', 'title', 'description'])` — no `uuid`.
- `indexSectoralGoals` confirmed at line 66: includes `uuid`.
- `'description' => 'required|string'` confirmed at line 164.
- `ProgramsController.php:31` still has `makeHidden(['id', ...])` — the shared endpoint is untouched.

## What I most want checked

1. **Did the Phase 2a geography route group survive intact?** A second group was added to the
   same file. Confirm the first is byte-for-byte unchanged and that no route name or URI now
   collides between the two groups.
2. **Cascade behaviour.** The implementer determined by reading
   `2026_02_23_110450_create_disagregation_items_table.php` that `disagregation_items` uses
   `->constrained()->onDelete('cascade')`, so deleting a category cascade-deletes its items.
   Verify that is what the migration actually says, and that the test asserts the real
   behaviour rather than an assumed one.
3. **Validation parity for everything except `description`.** Ruling 2 is a deliberate,
   documented divergence. Every *other* rule should match `ProgramController` exactly — confirm,
   or name where it drifts.
4. **The `unique` rules on update.** Creating with a duplicate `code` must 422; updating a row
   while keeping its own `code` must **not** self-collide. Confirm the update rules carry the
   `,{id}` exception where needed, and that a test covers the non-self-collision case.
5. **Test quality.** Would these tests fail against a missing permission check, a missing
   `unique` rule, or a reverted ruling? Specifically: is there a test asserting the new
   `/sectoral-goals` includes `id` **and** one asserting `/programs/sectoral-goals` still omits
   it? Those two together are the regression guard for the blocker.
6. **Nested item routes.** `POST/PUT/DELETE /disaggregation-categories/{category}/items/{item}`
   bind two models. Confirm an item belonging to a *different* category cannot be updated or
   deleted through another category's URL — that is the classic nested-binding hole.
