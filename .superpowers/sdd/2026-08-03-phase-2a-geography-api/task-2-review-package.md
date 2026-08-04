# Task 2 review package

No git diff — this project forbids agent git usage, nothing is committed, no commit range
exists. Review by reading files. Absence of commits is **not** a defect.

Working directory: `d:\DAVID\New folder\Olimage\FMLD_PROJECT\mems`

## Files created

| Path | Purpose |
|---|---|
| `app/Http/Controllers/Api/GeographyApiController.php` | 9 JSON write endpoints. Read in full. |
| `routes/v1/admin-crud.php` | Route registration. Will grow in Phases 2b/2c; geography is its first tenant. |
| `tests/Feature/GeographyApiWriteTest.php` | 8 tests. Read in full. |

## Files modified

| Path | Change |
|---|---|
| `routes/api.php` | One line: `require __DIR__.'/v1/admin-crud.php';` (line 16). |
| `database/seeders/DashboardPermissionsSeeder.php` | Added `'manage-settings' => 'Manage Settings'` to `KEYS` (line 32). It did not previously exist — without it the permission middleware would reject everyone. |

## The endpoints

All behind `jwt.verify` + `permission:manage-settings`, all `{status, message, data}`:

```text
POST   /api/v1/locations/zones            {name, code?}    → 201
PUT    /api/v1/locations/zones/{zone}     {name, code?}    → 200
DELETE /api/v1/locations/zones/{zone}                      → 200, or 422 if it has states
POST   /api/v1/locations/states           {name, zone_id}  → 201
PUT    /api/v1/locations/states/{state}   {name, zone_id}  → 200
DELETE /api/v1/locations/states/{state}                    → 200, or 422 if it has LGAs
POST   /api/v1/locations/lgas             {name, state_id} → 201
PUT    /api/v1/locations/lgas/{lga}       {name, state_id} → 200
DELETE /api/v1/locations/lgas/{lga}                        → 200
```

Validation and the two delete guards mirror `ZoneController` / `StateController` /
`LgaController`, which serve the same models over session-authenticated Inertia routes. The
implementer read all three before writing and reports they matched the brief verbatim.

## Out of scope — must be untouched

- The Inertia controllers (`ZoneController`, `StateController`, `LgaController`).
  `tests/Feature/GeographyControllerTest.php` passing unchanged is the regression check.
- `LocationController` and the read routes from Task 1.
- `routes/web.php`.

## Implementer's flagged observation, for your assessment

`CheckPermission` denies with `abort(403, 'Unauthorized.')`, which produces Laravel's default
error shape — **not** the `{status, message, data}` envelope this controller's 422 `refuse()`
uses. The implementer noted it and correctly did not change shared middleware unscoped.

Assess whether that inconsistency actually breaks the frontend contract. Relevant: the frontend
axios interceptor (`mne_frontend/src/services/apiService.js`, response interceptor) reads
`error.response?.data?.message` for any non-2xx, while `unwrap` only inspects the body of a 2xx.
Say whether a 403 still surfaces a usable message, or whether this needs fixing before Task 4
wires the UI.

## Controller's own verification (already performed — do not re-run)

- `routes/v1/admin-crud.php` read in full: the middleware array is `['jwt.verify', 'permission:manage-settings']`.
- `manage-settings` confirmed present at `DashboardPermissionsSeeder.php:32`.
- `routes/api.php:16` confirmed to require the new file.
- Implementer reported `php artisan test` → 123/123 (was 115), `route:list --path=locations` → 12
  routes with the 9 writes carrying `VerifyJWTToken` + `CheckPermission:manage-settings`, Pint clean.

## What I most want checked

1. **The delete guards.** Confirm both are checked *before* the delete, that they return 422 with
   `status: false`, and that the message is clear enough to show a user verbatim — Task 4 surfaces
   it as a toast, so the text is user-facing copy.
2. **`is_admin` and the 403 test.** Check how `User::hasPermission()` treats `is_admin`. If admins
   bypass permission checks, confirm `test_writes_require_the_manage_settings_permission` uses a
   user that genuinely lacks it, so the test really exercises `CheckPermission` rather than
   passing for an unrelated reason.
3. **Mass-assignment.** The controller passes validated arrays straight to `create()`/`update()`.
   Confirm against each model's `$fillable` that nothing unintended can be set, and that the
   auto-assigned `uuid` still lands on create.
4. **Validation parity with the Inertia controllers.** Any drift between the two paths is a future
   bug — confirm the rules match exactly, or name where they differ.
5. **Test quality.** Would these 8 tests fail against a missing guard or a missing permission
   check, or do any pass trivially?
