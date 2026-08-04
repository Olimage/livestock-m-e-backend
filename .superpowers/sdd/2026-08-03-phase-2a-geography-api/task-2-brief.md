### Task 2: Geography write endpoints

Add JSON create/update/delete for all three levels, mirroring the validation and delete guards
already proven in the Inertia controllers.

**Files:**
- Create: `mems/app/Http/Controllers/Api/GeographyApiController.php`
- Create: `mems/routes/v1/admin-crud.php`
- Modify: `mems/routes/api.php` — `require __DIR__.'/v1/admin-crud.php';` alongside the other `v1/*` includes
- Test: `mems/tests/Feature/GeographyApiWriteTest.php` (create)

**Interfaces:**
- Consumes: nothing from Task 1 (independent), but shares the `/locations` prefix.
- Produces, all `{status, message, data}`, all behind `jwt.verify` + `permission:manage-settings`:

```text
POST   /api/v1/locations/zones            {name, code?}       → 201
PUT    /api/v1/locations/zones/{zone}     {name, code?}       → 200
DELETE /api/v1/locations/zones/{zone}                         → 200, or 422 if it has states
POST   /api/v1/locations/states           {name, zone_id}     → 201
PUT    /api/v1/locations/states/{state}   {name, zone_id}     → 200
DELETE /api/v1/locations/states/{state}                       → 200, or 422 if it has LGAs
POST   /api/v1/locations/lgas             {name, state_id}    → 201
PUT    /api/v1/locations/lgas/{lga}       {name, state_id}    → 200
DELETE /api/v1/locations/lgas/{lga}                           → 200
```

- Task 4 consumes these.

- [ ] **Step 1: Read the source of the rules you are mirroring**

Read `mems/app/Http/Controllers/{ZoneController,StateController,LgaController}.php`. Confirm
the validation rules and the two delete guards for yourself. They are, at time of writing:
zone `name required|string|max:255`, `code nullable|string|max:50`; state and lga
`name required|string|max:255` plus `zone_id required|exists:zones,id` / `state_id required|exists:states,id`;
`Zone::destroy` rejects when `states()->exists()`; `State::destroy` rejects when `lgas()->exists()`;
`Lga::destroy` is unguarded. If any of that has changed, follow the code, not this paragraph.

Also read `mems/app/Http/Middleware/CheckPermission.php` and confirm `permission:<key>` resolves
via `Auth::user()->hasPermission()`. Check whether `manage-settings` exists in
`database/seeders/DashboardPermissionsSeeder.php`'s `KEYS`; if it does not, seed it there
alongside the existing keys as part of this task.

- [ ] **Step 2: Write the failing test**

Create `mems/tests/Feature/GeographyApiWriteTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Lga;
use App\Models\State;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class GeographyApiWriteTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'full_name' => 'Admin',
            'email' => uniqid().'@example.test',
            'password' => bcrypt('secret'),
            'is_admin' => true,
        ]);
    }

    private function plainUser(): User
    {
        return User::create([
            'full_name' => 'Viewer',
            'email' => uniqid().'@example.test',
            'password' => bcrypt('secret'),
            'is_admin' => false,
        ]);
    }

    private function headers(User $user): array
    {
        return ['Authorization' => 'Bearer '.JWTAuth::fromUser($user)];
    }

    public function test_writes_require_authentication(): void
    {
        $this->postJson('/api/v1/locations/zones', ['name' => 'X'])->assertStatus(401);
    }

    public function test_writes_require_the_manage_settings_permission(): void
    {
        $this->withHeaders($this->headers($this->plainUser()))
            ->postJson('/api/v1/locations/zones', ['name' => 'South East', 'code' => 'SE'])
            ->assertStatus(403);

        $this->assertFalse(Zone::where('name', 'South East')->exists());
    }

    public function test_admin_can_create_update_and_delete_a_zone(): void
    {
        $admin = $this->admin();

        $created = $this->withHeaders($this->headers($admin))
            ->postJson('/api/v1/locations/zones', ['name' => 'South East', 'code' => 'SE'])
            ->assertStatus(201)
            ->assertJsonPath('status', true)
            ->json('data');

        $this->assertSame('South East', $created['name']);
        $this->assertNotEmpty($created['uuid']);

        $this->withHeaders($this->headers($admin))
            ->putJson("/api/v1/locations/zones/{$created['id']}", ['name' => 'South-East', 'code' => 'SE'])
            ->assertOk();

        $this->assertSame('South-East', Zone::find($created['id'])->name);

        $this->withHeaders($this->headers($admin))
            ->deleteJson("/api/v1/locations/zones/{$created['id']}")
            ->assertOk();

        $this->assertFalse(Zone::whereKey($created['id'])->exists());
    }

    public function test_a_zone_with_states_cannot_be_deleted(): void
    {
        $zone = Zone::create(['name' => 'NW', 'code' => 'NW']);
        State::create(['name' => 'Kano', 'zone_id' => $zone->id]);

        $this->withHeaders($this->headers($this->admin()))
            ->deleteJson("/api/v1/locations/zones/{$zone->id}")
            ->assertStatus(422)
            ->assertJsonPath('status', false);

        $this->assertTrue(Zone::whereKey($zone->id)->exists());
    }

    public function test_a_state_with_lgas_cannot_be_deleted(): void
    {
        $zone = Zone::create(['name' => 'NW', 'code' => 'NW']);
        $state = State::create(['name' => 'Kano', 'zone_id' => $zone->id]);
        Lga::create(['name' => 'Dala', 'state_id' => $state->id]);

        $this->withHeaders($this->headers($this->admin()))
            ->deleteJson("/api/v1/locations/states/{$state->id}")
            ->assertStatus(422);

        $this->assertTrue(State::whereKey($state->id)->exists());
    }

    public function test_an_lga_with_no_children_deletes_cleanly(): void
    {
        $zone = Zone::create(['name' => 'NW', 'code' => 'NW']);
        $state = State::create(['name' => 'Kano', 'zone_id' => $zone->id]);
        $lga = Lga::create(['name' => 'Dala', 'state_id' => $state->id]);

        $this->withHeaders($this->headers($this->admin()))
            ->deleteJson("/api/v1/locations/lgas/{$lga->id}")
            ->assertOk();

        $this->assertFalse(Lga::whereKey($lga->id)->exists());
    }

    public function test_creating_a_state_requires_a_real_zone(): void
    {
        $this->withHeaders($this->headers($this->admin()))
            ->postJson('/api/v1/locations/states', ['name' => 'Nowhere', 'zone_id' => 999999])
            ->assertStatus(422);
    }

    public function test_creating_a_zone_requires_a_name(): void
    {
        $this->withHeaders($this->headers($this->admin()))
            ->postJson('/api/v1/locations/zones', ['code' => 'XX'])
            ->assertStatus(422);
    }
}
```

- [ ] **Step 3: Run the test to verify it fails**

Run: `php artisan test --filter=GeographyApiWriteTest`
Expected: FAIL — the routes do not exist yet (404s).

- [ ] **Step 4: Write the controller**

Create `mems/app/Http/Controllers/Api/GeographyApiController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lga;
use App\Models\State;
use App\Models\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * JSON write endpoints for the Zone → State → LGA hierarchy, for the
 * JWT-authenticated SPA. Reads live on LocationController alongside the
 * cascading selectors. Validation rules and the two delete guards mirror
 * ZoneController / StateController / LgaController, which serve the same
 * models over session-authenticated Inertia routes.
 */
class GeographyApiController extends Controller
{
    public function storeZone(Request $request): JsonResponse
    {
        $zone = Zone::create($this->validateZone($request));

        return $this->ok($zone->fresh(), 'Zone created.', 201);
    }

    public function updateZone(Request $request, Zone $zone): JsonResponse
    {
        $zone->update($this->validateZone($request));

        return $this->ok($zone->fresh(), 'Zone updated.');
    }

    public function destroyZone(Zone $zone): JsonResponse
    {
        if ($zone->states()->exists()) {
            return $this->refuse('This zone still has states. Move or delete them first.');
        }

        $zone->delete();

        return $this->ok(null, 'Zone deleted.');
    }

    public function storeState(Request $request): JsonResponse
    {
        $state = State::create($this->validateState($request));

        return $this->ok($state->fresh(), 'State created.', 201);
    }

    public function updateState(Request $request, State $state): JsonResponse
    {
        $state->update($this->validateState($request));

        return $this->ok($state->fresh(), 'State updated.');
    }

    public function destroyState(State $state): JsonResponse
    {
        if ($state->lgas()->exists()) {
            return $this->refuse('This state still has LGAs. Move or delete them first.');
        }

        $state->delete();

        return $this->ok(null, 'State deleted.');
    }

    public function storeLga(Request $request): JsonResponse
    {
        $lga = Lga::create($this->validateLga($request));

        return $this->ok($lga->fresh(), 'LGA created.', 201);
    }

    public function updateLga(Request $request, Lga $lga): JsonResponse
    {
        $lga->update($this->validateLga($request));

        return $this->ok($lga->fresh(), 'LGA updated.');
    }

    public function destroyLga(Lga $lga): JsonResponse
    {
        $lga->delete();

        return $this->ok(null, 'LGA deleted.');
    }

    private function validateZone(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
        ]);
    }

    private function validateState(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'zone_id' => 'required|exists:zones,id',
        ]);
    }

    private function validateLga(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'state_id' => 'required|exists:states,id',
        ]);
    }

    private function ok($data, string $message, int $code = 200): JsonResponse
    {
        return response()->json(['status' => true, 'message' => $message, 'data' => $data], $code);
    }

    private function refuse(string $message): JsonResponse
    {
        return response()->json(['status' => false, 'message' => $message], 422);
    }
}
```

- [ ] **Step 5: Register the routes**

Create `mems/routes/v1/admin-crud.php`. This file will grow in Phases 2b and 2c; geography is
its first tenant.

```php
<?php

use App\Http\Controllers\Api\GeographyApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['jwt.verify', 'permission:manage-settings'])
    ->prefix('locations')
    ->name('api.locations.')
    ->group(function () {
        Route::post('zones', [GeographyApiController::class, 'storeZone'])->name('zones.store');
        Route::put('zones/{zone}', [GeographyApiController::class, 'updateZone'])->name('zones.update');
        Route::delete('zones/{zone}', [GeographyApiController::class, 'destroyZone'])->name('zones.destroy');

        Route::post('states', [GeographyApiController::class, 'storeState'])->name('states.store');
        Route::put('states/{state}', [GeographyApiController::class, 'updateState'])->name('states.update');
        Route::delete('states/{state}', [GeographyApiController::class, 'destroyState'])->name('states.destroy');

        Route::post('lgas', [GeographyApiController::class, 'storeLga'])->name('lgas.store');
        Route::put('lgas/{lga}', [GeographyApiController::class, 'updateLga'])->name('lgas.update');
        Route::delete('lgas/{lga}', [GeographyApiController::class, 'destroyLga'])->name('lgas.destroy');
    });
```

In `mems/routes/api.php`, add the include beside the existing `v1/*` requires:

```php
    require __DIR__.'/v1/admin-crud.php';
```

Route-model binding resolves `{zone}`, `{state}`, `{lga}` by primary key, which is what the
frontend sends. None of these models override `getRouteKeyName`, so no `whereNumber` constraint
is needed — but confirm that by reading the models rather than assuming.

- [ ] **Step 6: Run the test to verify it passes**

Run: `php artisan test --filter=GeographyApiWriteTest`
Expected: PASS, 8 tests.

- [ ] **Step 7: Verify**

Run: `php artisan test`
Expected: PASS, all suites.

Run: `php artisan route:list --path=locations`
Expected: 12 routes (3 reads from Task 1, 9 writes). Confirm the writes show both `jwt.verify`
and `permission:manage-settings`.

Run: `vendor/bin/pint --dirty`
Expected: clean.

---

## Global Constraints

- **No git operations.** Version control is handled by the user. Do not run `git add`, `git commit`, `git status`, `git diff`, `git log`, or any other git command. Tasks end with verification, not commits.
- **Do not use `npx rg`** — it fails in this shell (npm-init collision). Use the Grep tool or `grep -rn`.
- **Read every file and controller named in a task's Files list before writing code for it.** Phase 1 shipped four defects, all traceable to describing a contract from memory or from what a mock implied instead of reading the source. This is a hard gate, not advice.
- Two repos. `mems` is at `d:\DAVID\New folder\Olimage\FMLD_PROJECT\mems`; `mne_frontend` at `d:\DAVID\New folder\Olimage\FMLD_PROJECT\mne_frontend`. Both paths contain spaces — quote them.
- `mems`: `php artisan test` (PHPUnit, SQLite `:memory:` per `phpunit.xml`), `vendor/bin/pint --dirty` before finalising, `php artisan route:list` to confirm routes resolve.
- `mne_frontend`: `npm test -- --run`, `npm run build`. Current baseline: **71 tests passing across 10 files**.
- New API endpoints use the `{status, message, data}` envelope — the majority form, and the one `src/services/unwrap.js` error-checks. Do **not** use `{success, …}`.
- Frontend data access follows the established layering: `services/*Api.js` → `hooks/use*.js` → component. Do not call axios from a component.

## Spec reconciliation

This plan implements the geography row of §4.3 of
`mems/docs/superpowers/specs/2026-08-03-mne-frontend-api-wiring-design.md`, plus §2.5.

**One deliberate deviation from the spec.** §2.5 says "LGAs paginate by default (774 rows)". This
plan makes pagination **opt-in** (`?per_page` present → paginated; absent → the full filtered
set). Reason: `SimpleCrudPanel` — the component that renders all three geography tables —
already does search, filtering, and paging client-side, and 774 LGA rows is roughly 60 KB of
JSON. Defaulting to server pagination would force a rewrite of a working component for no user
benefit. `?per_page` is still supported so a future consumer can page without an API change.

