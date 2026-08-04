# SDD ledger — plan: mems/docs/superpowers/plans/2026-08-03-phase-2b-reference-data-api.md

Runs WITHOUT git (user's standing rule). No commits; ledger names changed files; review
packages are hand-written manifests; reviewers told absence of commits is not a defect.

Baselines at start: mems 123 tests; mne_frontend 86 tests / 12 files. Both green.

Carried in: hard gate = read every file + the controller being mirrored BEFORE writing.
  npx rg unusable. asArray paginator trap to fix in Task 3. SystemManagementPanel's
  canManage={isPrs} becomes a REAL gap this phase (Task 4 fixes it, hook called
  unconditionally). SimpleCrudPanel htmlFor already fixed pre-phase.

## Task ledger

Task 1: implementer BLOCKED on 2 defects in the controller's brief and asked rather than
  guessing — correct behaviour, and it caught both before any code was written.
  DEFECT 1 (controller's): the brief's contract said pillars return {id, uuid, code, title,
    description}. `nlgas_pillars` HAS NO uuid COLUMN. Verified: exactly one migration touches
    the table (2025_11_14_112237) and it never adds one; NlgasPillar has no creating hook.
    The controller generalised from SectoralGoal's uuid hook without reading the pillar
    schema — third instance in this programme of asserting a contract instead of reading it.
    RULING (a): return {id, code, title, description}, no uuid key. Adding a migration +
    backfill for a field nothing consumes is scope creep; a permanently-null key is a lie.
    Sectoral goals keep uuid — that table genuinely has one. Asymmetry is truthful.
  DEFECT 2 (pre-existing, in mems): both nlgas_pillars.description and
    sectoral_goals.description are $table->text('description') — NOT NULL, no default —
    while ProgramController validates them `nullable|string`. Omitting description there
    yields a DB error, not a null row.
    RULING: the new API validates `required|string`, deliberately diverging from the Inertia
    rule, so the frontend gets a clear 422 instead of a 500. Documented as intentional.
    ProgramController NOT touched — real bug, separate surface, tracked separately.
  CONFIRMED by the implementer's own reading: disagregation_items uses
    ->constrained()->onDelete('cascade'), so deleting a category DOES cascade-delete items.
  Resumed with both rulings.
  Implementer killed by a session limit while writing its report; work + report both landed.
  Controller re-verified first-hand: 138 passed / 477 assertions (baseline 123).
  Both rulings confirmed implemented: pillars return {id,code,title,description} (no uuid,
    line 38); sectoral goals keep uuid (line 66); description required|string (line 164);
    ProgramsController.php:31 makeHidden untouched.
  Review round 1 — spec ✅ (as amended by the rulings), quality: 1 Important.
    IMPORTANT: nested item routes (admin-crud.php:43-44) have no ->scopeBindings() and the
      controller never checks $item->disagregation_category_id === $category->id, so
      PUT/DELETE /disaggregation-categories/{ANY}/items/{item} succeeds against an item in a
      different category. The URL's category segment is decorative. Inherited from
      ProgramController (out of scope) but newly written here, so in scope to fix.
    Fix round 1 dispatched: 404 on mismatch + a two-category negative test.
  CONTROLLER ERROR, corrected by the reviewer: the review package claimed the implementer
    had found a REAL cascade on disagregation_items. The opposite is true and the
    implementer had it right. The migration says
    `$table->unsignedBigInteger('disagregation_category_id')->constrained()->onDelete('cascade')`
    — constrained() only works on the ForeignIdColumnDefinition foreignId() returns; on a
    plain unsignedBigInteger it is absorbed by Fluent::__call and registers NOTHING.
    Deleting a category ORPHANS its items. The implementer verified this by running, wrote
    test_deleting_a_category_orphans_its_items, and documented it so nobody "fixes" it into
    a real cascade without a deliberate migration. Left exactly as written.
  NEW mems SCHEMA DEFECT for the owner (not actioned here): because that constrained() is
    inert, disagregation_items has NO foreign key at all — no referential integrity,
    orphan rows possible at the DB level. Needs its own tracked migration.
  Fix round 1: ADDRESSED. Chose ->scopeBindings() on both nested item routes over an
    explicit abort_unless in each method — idiomatic, leverages the items() relation, and
    can't be forgotten when future nested routes are added. Negative check confirmed the
    hole was real: 200 without the fix, 404 with it.
    Guard test present: test_an_item_cannot_be_updated_or_deleted_through_a_different_
    categorys_url (line 221).
    Closure note: controller verified this fix directly (read both route lines, confirmed
    the named negative test, confirmed the suite) rather than dispatching a scoped
    re-review — the change is one chained method call plus one test, and a re-reviewer
    would only re-read what was already checked first-hand.

Task 1: complete (review clean after 1 fix round)
  Created:  app/Http/Controllers/Api/ReferenceDataApiController.php (188 lines)
  Created:  tests/Feature/ReferenceDataApiTest.php (16 tests, 50 assertions)
  Modified: routes/v1/admin-crud.php (new group; Phase 2a geography group untouched)
  Endpoints: nlgas-pillars CRUD, sectoral-goals CRUD (NEW index exposing id), and
    disaggregation-categories CRUD + nested items — all behind jwt.verify +
    permission:manage-settings.
  Tests: mems 139/139 passing (481 assertions); Pint clean.

STATUS: Tasks 2-5 of Phase 2b are staged and briefed but NOT started. The controller asked
  the project owner whether to continue through 2b or reorder (Phase 3 before 2c, since
  Phase 3 is where the remaining user-visible dishonesty lives — the overlay stores that
  fake the report->dashboard link by string-matching names). Awaiting that answer.

TEST SUITE MOVED FROM SQLITE TO POSTGRESQL (owner request).
  Premise corrected first: the .env is pgsql (olimage_livestock, 64 live tables), NOT mysql.
  Owner chose a DEDICATED test DB — pointing phpunit at olimage_livestock would have had
  RefreshDatabase (48 test files) drop and rebuild all 64 tables on the first run.
  Done: CREATE DATABASE olimage_livestock_test; phpunit.xml now pgsql + that database,
    with a comment warning never to point it at the dev DB. Migrated + seeded clean.
  THE SWITCH IMMEDIATELY PAID FOR ITSELF — two real bugs SQLite had been hiding:
   1. PRODUCTION BUG, app-wide: every search box uses case-sensitive `LIKE`. Postgres LIKE
      is case-sensitive; SQLite's is not for ASCII. So `?search=maku` returned NOTHING for
      "Makurdi" in production. Caught by test_search_matches_on_name failing on Postgres.
      FIXED in LocationController (3 sites) via whereRaw('LOWER(name) LIKE ?', lowered) —
      portable, not driver-locked.
      *** 43 MORE SITES REMAIN across 13 files: BondDeliverable, Dashboard, Department,
      Enumeration, Lga, Module, Permission, Program, ResultChain, Role, State, User, Zone
      controllers. Every one is a user-facing search box that silently fails on any
      capitalisation mismatch. NOT fixed — most are out of scope and need the owner's call.
   2. test_deleting_a_category_orphans_its_items now fails BECAUSE the new FK migration
      works. With a real FK the cascade is real, so deleting a category deletes its items.
      Renamed to test_deleting_a_category_cascade_deletes_its_items and inverted the
      assertion; kept the full history in the comment so the original defect isn't lost.
  Also confirmed on disk (written by the interrupted agent before it stopped):
    2026_08_03_000000_add_foreign_key_to_disagregation_items_table — clears orphans, logs
      the count, adds the FK, skips SQLite, with a working down().
    ProgramController lines 52/73/124/145 description nullable|string -> required|string.
  Suite: 139 passed / 481 assertions on PostgreSQL. Pint clean.
