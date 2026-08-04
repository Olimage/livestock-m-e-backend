# Task 2 Report: Geography write endpoints

## Files created/modified

- Created `mems/app/Http/Controllers/Api/GeographyApiController.php` — thin JSON controller with 9 actions (store/update/destroy × zone/state/lga), mirroring `ZoneController`/`StateController`/`LgaController` validation rules and delete guards, using the `{status, message, data}` envelope.
- Created `mems/routes/v1/admin-crud.php` — new route file, `Route::middleware(['jwt.verify', 'permission:manage-settings'])->prefix('locations')->name('api.locations.')`, registering the 9 write routes.
- Modified `mems/routes/api.php` — added exactly one line, `require __DIR__.'/v1/admin-crud.php';`, alongside the other `v1/*` requires inside the existing `/v1` group (so it inherits `json-response`/`cors` and lands at `/api/v1/locations/...`). No other lines touched.
- Modified `mems/database/seeders/DashboardPermissionsSeeder.php` — added `'manage-settings' => 'Manage Settings',` to the `KEYS` constant (see below — it did not exist before).
- Created `mems/tests/Feature/GeographyApiWriteTest.php` — the 8-test file from the brief, copied verbatim.

## Step 1 — source-of-truth confirmation

Read all three Inertia controllers and `CheckPermission`. Everything matched the brief exactly, no deviations:

- `ZoneController`: `name required|string|max:255`, `code nullable|string|max:50`; `destroy` guards on `$zone->states()->exists()`.
- `StateController`: `name required|string|max:255`, `zone_id required|exists:zones,id`; `destroy` guards on `$state->lgas()->exists()`.
- `LgaController`: `name required|string|max:255`, `state_id required|exists:states,id`; `destroy` unguarded.
- `CheckPermission::handle` calls `Auth::user()->hasPermission($permission)`, aborting 403 if absent/false — works identically under JWT since `Auth::user()` resolves whatever guard authenticated the request, and `VerifyJWTToken` runs before it in the group.
- `User::hasPermission()`: `is_admin` short-circuits to `true`; otherwise checks `allPermissionKeys()`. Confirmed the `plainUser()` fixture (`is_admin => false`, no roles/permissions granted) genuinely lacks `manage-settings`, so the 403 test exercises the middleware for real, not just the admin bypass.
- `manage-settings` was **not** present in `DashboardPermissionsSeeder::KEYS` — added it as instructed. The seeder is idempotent (`Permission::updateOrCreate`) and auto-assigns the `Dashboards` module; I did not create a separate module for it since the brief didn't ask for one and the seeder's existing structure handles it fine.
- Route-model binding: none of `Zone`, `State`, `Lga` override `getRouteKeyName()` — confirmed by reading all three models. Default primary-key binding applies, matching brief.

## Step 2–3 — test written, confirmed failing first

Ran `php artisan test --filter=GeographyApiWriteTest` before writing the controller/routes: all 8 tests failed with 404/405 (routes didn't exist), confirming the test genuinely exercises new behavior rather than passing vacuously.

## Step 4–5 — controller and routes

Wrote both files verbatim from the brief (no changes needed — validation, guards, and envelope all matched what Step 1 confirmed).

## Step 6 — verification

```
php artisan test --filter=GeographyApiWriteTest
```
Result: **8 passed (20 assertions)**, 1.31s. All pass: auth required (401), permission required (403, and no zone leaked into DB), full zone create/update/delete cycle (incl. `uuid` present), zone-with-states guard (422 + `status:false`), state-with-lgas guard (422), LGA unguarded delete (200), state creation rejects nonexistent `zone_id` (422), zone creation rejects missing `name` (422).

```
php artisan test
```
Result: **123 passed (431 assertions)**, 8.85s. Baseline was 115 — the 8 new tests are the entire delta; nothing else broke, `GeographyControllerTest` (Inertia regression) untouched and still passing.

```
php artisan route:list --path=locations
```
Result: **12 routes** — 3 reads (`GET zones/states/lgas` → `LocationController@ApiGet*`, no permission middleware) + 9 writes (`POST/PUT/DELETE` × zones/states/lgas → `GeographyApiController@*`). Ran with `-vv` to confirm middleware stacks: every write route shows both `App\Http\Middleware\VerifyJWTToken` and `App\Http\Middleware\CheckPermission:manage-settings`; the 3 read routes show `VerifyJWTToken` only (as Task 1 left them) — no permission leak either direction.

```
vendor/bin/pint --dirty
```
Result: **PASS**, 7 files, no changes needed.

## Whether `manage-settings` already existed

**Did not exist.** Added `'manage-settings' => 'Manage Settings',` to `DashboardPermissionsSeeder::KEYS`, per the brief's Step 1 instruction. Without this, `CheckPermission` would still function correctly for tests (permission existence in the seeder isn't checked by `hasPermission()` — that just checks the user's granted-permission keys against the string), but production admins granting this permission via the UI would have nothing to pick, and the permission catalog would silently omit a key referenced live in routes. This is a real gap the brief anticipated correctly.

## Anything surprising

- Nothing surprising in the controller/routes logic — the brief's prescribed code matched the actual source exactly, so no adaptation was needed.
- One thing worth flagging for the parent task (not acted on, per Constraint 6): `CheckPermission` calls `abort(403, 'Unauthorized.')`, which under the `json-response`/`EnsureJsonResponseMiddleware` stack correctly renders as a JSON 403 in this app already (confirmed by the passing test), so no envelope mismatch — but the message text `'Unauthorized.'` is hardcoded in the middleware and not the `{status, message, data}` shape the rest of this task uses. It's out of scope to change (shared middleware, not part of this task's Files list), but Task 4's toast-surfacing work should know that 403 responses may not carry a `status` key the same way 422 responses from `GeographyApiController::refuse()` do.
- `routes/api.php`'s existing read group for `locations` (Task 1) sits at lines 17–21, *inside* the same outer `/v1` group but as an inline `Route::prefix(...)` call rather than a `require`. My `require __DIR__.'/v1/admin-crud.php';` is a sibling include, not nested inside that inline group, so the two `Route::prefix('locations')` calls (one inline for reads, one via require for writes) merge correctly at the router level — verified by `route:list` showing all 12 as a single flat set under `/api/v1/locations`.

## Self-review

- Read every file in Step 1 before writing code, per Constraint 2/Global Constraint — no guessing from the brief alone.
- Did not modify `LocationController`, the Task-1 read group, or any Inertia controller.
- `routes/api.php` got exactly one line added, placed beside the other `v1/*` requires as instructed.
- Delete guards confirmed to return 422 with `{status: false, message}` (not redirects, not 500) — verified directly via the test assertions and by reading the controller's `refuse()` helper.
- No git commands run at any point.
- Did not touch `mne_frontend` (out of scope for this task).
