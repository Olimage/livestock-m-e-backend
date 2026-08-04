### Task 1: Turn the `/locations` selectors into real list endpoints

`LocationController::ApiGetStates` and `::ApiGetLgas` currently `validate` their parent id as
`required|exists:…`, so there is no way to ask for all states or all LGAs. Their payloads are
`id`/`name`/parent-FK only — no zone code, no parent name, no child counts. The Geography tab
needs all of that.

This task also moves the whole `/locations` group behind `jwt.verify`. It is currently
unauthenticated because the retired `enum_flutter` field app called it without a token. That app
no longer exists in the workspace; its replacement `nlims_flutter` targets a different backend
(`nlims_be`, per `nlims_flutter/lib/core/config.dart`); and a workspace-wide search finds no
remaining caller. `mne_frontend` always sends a bearer token.

**Files:**
- Modify: `mems/app/Http/Controllers/LocationController.php` — `ApiGetZones`, `ApiGetStates`, `ApiGetLgas`
- Modify: `mems/routes/api.php` — the `Route::prefix('locations')` group (currently outside the `jwt.verify` group)
- Test: `mems/tests/Feature/LocationApiTest.php` (create)

**Interfaces:**
- Produces three endpoints, all `{status, message, data}`:
  - `GET /api/v1/locations/zones` → `[{ id, uuid, name, code, states_count }]`
  - `GET /api/v1/locations/states?zone_id=&search=&per_page=` → `[{ id, uuid, name, zone_id, zone_code, zone_name, lgas_count }]`
  - `GET /api/v1/locations/lgas?state_id=&zone_id=&search=&per_page=` → `[{ id, uuid, name, state_id, state_name, zone_id, zone_code, zone_name }]`
- All filters optional. `per_page` absent → a plain array; `per_page` present → a Laravel paginator object under `data`.
- Task 3 consumes these. Task 2 adds writes at the same prefix.

- [ ] **Step 1: Read the current code before changing it**

Read `mems/app/Http/Controllers/LocationController.php` in full, and the `locations` group in
`mems/routes/api.php`. Confirm for yourself that `zone_id`/`state_id` are `required` today and
that the group sits outside `jwt.verify`. Also read `app/Models/{Zone,State,Lga}.php` — note
`Zone::$fillable` is `['name','code']`, `State` `['name','zone_id']`, `Lga` `['name','state_id']`,
and all three assign a `uuid` on `creating`.

- [ ] **Step 2: Write the failing test**

Create `mems/tests/Feature/LocationApiTest.php`. Follow the conventions in the existing
`tests/Feature/GeographyControllerTest.php` (`RefreshDatabase`, `User::create([...])`).
Authenticate with JWT rather than a session, matching how the API guard works:

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

class LocationApiTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::create([
            'full_name' => 'Admin',
            'email' => uniqid().'@example.test',
            'password' => bcrypt('secret'),
            'is_admin' => true,
        ]);
    }

    private function authHeaders(?User $user = null): array
    {
        $token = JWTAuth::fromUser($user ?? $this->user());

        return ['Authorization' => "Bearer {$token}"];
    }

    private function seedGeography(): array
    {
        $nw = Zone::create(['name' => 'North West', 'code' => 'NW']);
        $nc = Zone::create(['name' => 'North Central', 'code' => 'NC']);
        $kano = State::create(['name' => 'Kano', 'zone_id' => $nw->id]);
        $benue = State::create(['name' => 'Benue', 'zone_id' => $nc->id]);
        Lga::create(['name' => 'Nassarawa', 'state_id' => $kano->id]);
        Lga::create(['name' => 'Dala', 'state_id' => $kano->id]);
        Lga::create(['name' => 'Makurdi', 'state_id' => $benue->id]);

        return compact('nw', 'nc', 'kano', 'benue');
    }

    public function test_locations_require_authentication(): void
    {
        $this->getJson('/api/v1/locations/zones')->assertStatus(401);
        $this->getJson('/api/v1/locations/states')->assertStatus(401);
        $this->getJson('/api/v1/locations/lgas')->assertStatus(401);
    }

    public function test_zones_include_a_state_count(): void
    {
        $this->seedGeography();

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/locations/zones')
            ->assertOk()
            ->assertJsonPath('status', true);

        $zones = collect($response->json('data'));
        $this->assertSame(2, $zones->count());

        $nw = $zones->firstWhere('code', 'NW');
        $this->assertSame('North West', $nw['name']);
        $this->assertSame(1, $nw['states_count']);
        $this->assertArrayHasKey('uuid', $nw);
    }

    // The whole point of this task: the old endpoint required zone_id.
    public function test_states_list_everything_when_no_zone_id_is_given(): void
    {
        $this->seedGeography();

        $states = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/locations/states')
            ->assertOk()
            ->json('data');

        $this->assertCount(2, $states);
    }

    public function test_states_carry_their_zone_code_and_lga_count(): void
    {
        $this->seedGeography();

        $states = collect($this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/locations/states')->json('data'));

        $kano = $states->firstWhere('name', 'Kano');
        $this->assertSame('NW', $kano['zone_code']);
        $this->assertSame('North West', $kano['zone_name']);
        $this->assertSame(2, $kano['lgas_count']);
    }

    public function test_states_still_filter_by_zone_id_when_it_is_supplied(): void
    {
        $seed = $this->seedGeography();

        $states = $this->withHeaders($this->authHeaders())
            ->getJson("/api/v1/locations/states?zone_id={$seed['nw']->id}")
            ->assertOk()
            ->json('data');

        $this->assertCount(1, $states);
        $this->assertSame('Kano', $states[0]['name']);
    }

    public function test_lgas_list_everything_and_carry_their_parents(): void
    {
        $this->seedGeography();

        $lgas = collect($this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/locations/lgas')->json('data'));

        $this->assertSame(3, $lgas->count());

        $nassarawa = $lgas->firstWhere('name', 'Nassarawa');
        $this->assertSame('Kano', $nassarawa['state_name']);
        $this->assertSame('NW', $nassarawa['zone_code']);
    }

    public function test_lgas_filter_by_state_and_by_zone(): void
    {
        $seed = $this->seedGeography();

        $byState = $this->withHeaders($this->authHeaders())
            ->getJson("/api/v1/locations/lgas?state_id={$seed['kano']->id}")->json('data');
        $this->assertCount(2, $byState);

        $byZone = $this->withHeaders($this->authHeaders())
            ->getJson("/api/v1/locations/lgas?zone_id={$seed['nc']->id}")->json('data');
        $this->assertCount(1, $byZone);
        $this->assertSame('Makurdi', $byZone[0]['name']);
    }

    public function test_search_matches_on_name(): void
    {
        $this->seedGeography();

        $lgas = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/locations/lgas?search=maku')->json('data');

        $this->assertCount(1, $lgas);
        $this->assertSame('Makurdi', $lgas[0]['name']);
    }

    public function test_per_page_switches_to_a_paginator(): void
    {
        $this->seedGeography();

        $body = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/locations/lgas?per_page=2')->json('data');

        $this->assertArrayHasKey('data', $body);
        $this->assertArrayHasKey('total', $body);
        $this->assertCount(2, $body['data']);
        $this->assertSame(3, $body['total']);
    }

    public function test_an_unknown_zone_id_is_rejected_rather_than_ignored(): void
    {
        $this->seedGeography();

        $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/locations/states?zone_id=999999')
            ->assertStatus(422);
    }
}
```

- [ ] **Step 3: Run the test to verify it fails**

Run from `mems`: `php artisan test --filter=LocationApiTest`
Expected: FAIL. The auth test fails (endpoints are currently public), the no-parent-id tests
fail with 422, and the shape assertions fail on missing keys.

- [ ] **Step 4: Rewrite the three controller methods**

In `mems/app/Http/Controllers/LocationController.php`, replace the three `Api*` methods. Keep
the existing try/catch + `{status, message, data}` envelope style already used in that file.

```php
    public function ApiGetZones(Request $request)
    {
        try {
            $zones = Zone::query()
                ->withCount('states')
                ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
                ->orderBy('name')
                ->get(['id', 'uuid', 'name', 'code']);

            return response()->json([
                'status' => true,
                'message' => 'Zones fetched successfully',
                'data' => $this->maybePaginate($zones, $request),
            ], 200);
        } catch (\Exception $e) {
            return $this->failure($e);
        }
    }

    public function ApiGetStates(Request $request)
    {
        try {
            // Optional now, not required — the management table needs the full
            // list. Passing zone_id still filters exactly as it did when this
            // was a cascading selector, so existing callers are unaffected.
            $request->validate(['zone_id' => 'nullable|exists:zones,id']);

            $states = State::query()
                ->with('zone:id,name,code')
                ->withCount('lgas')
                ->when($request->zone_id, fn ($q) => $q->where('zone_id', $request->zone_id))
                ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
                ->orderBy('name')
                ->get(['id', 'uuid', 'name', 'zone_id'])
                ->map(fn (State $state) => [
                    'id' => $state->id,
                    'uuid' => $state->uuid,
                    'name' => $state->name,
                    'zone_id' => $state->zone_id,
                    'zone_code' => $state->zone?->code,
                    'zone_name' => $state->zone?->name,
                    'lgas_count' => $state->lgas_count,
                ]);

            return response()->json([
                'status' => true,
                'message' => 'States fetched successfully',
                'data' => $this->maybePaginate($states, $request),
            ], 200);
        } catch (\Exception $e) {
            return $this->failure($e);
        }
    }

    public function ApiGetLgas(Request $request)
    {
        try {
            $request->validate([
                'state_id' => 'nullable|exists:states,id',
                'zone_id' => 'nullable|exists:zones,id',
            ]);

            $lgas = Lga::query()
                ->with('state:id,name,zone_id', 'state.zone:id,name,code')
                ->when($request->state_id, fn ($q) => $q->where('state_id', $request->state_id))
                ->when($request->zone_id, fn ($q) => $q->whereHas(
                    'state',
                    fn ($s) => $s->where('zone_id', $request->zone_id)
                ))
                ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
                ->orderBy('name')
                ->get(['id', 'uuid', 'name', 'state_id'])
                ->map(fn (Lga $lga) => [
                    'id' => $lga->id,
                    'uuid' => $lga->uuid,
                    'name' => $lga->name,
                    'state_id' => $lga->state_id,
                    'state_name' => $lga->state?->name,
                    'zone_id' => $lga->state?->zone_id,
                    'zone_code' => $lga->state?->zone?->code,
                    'zone_name' => $lga->state?->zone?->name,
                ]);

            return response()->json([
                'status' => true,
                'message' => 'Lgas fetched successfully',
                'data' => $this->maybePaginate($lgas, $request),
            ], 200);
        } catch (\Exception $e) {
            return $this->failure($e);
        }
    }
```

Add these two private helpers to the same class:

```php
    /**
     * `per_page` absent → the full collection (the geography admin tables filter
     * client-side, and 774 LGAs is ~60 KB). `per_page` present → a paginator, so
     * a future consumer can page without an API change.
     */
    private function maybePaginate(\Illuminate\Support\Collection $items, Request $request)
    {
        if (! $request->filled('per_page')) {
            return $items->values();
        }

        $perPage = max(1, (int) $request->per_page);
        $page = max(1, (int) $request->get('page', 1));

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page
        );
    }

    private function failure(\Throwable $e)
    {
        return response()->json([
            'status' => false,
            'message' => 'An error occurred while fetching data',
            'error' => $e->getMessage(),
        ], 500);
    }
```

Two traps in the above, both of which will bite silently if ignored:

**1. `ValidationException` must not be swallowed.** `$request->validate()` throws it and Laravel
renders it as 422, but it extends `Exception`, so the existing `catch (\Exception)` would turn a
422 into a 500. Re-throw it as the first catch clause in each method:

```php
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->failure($e);
        }
```

**2. `withCount()` plus `get([columns])` does not do what it looks like.** `withCount` sets
`$query->columns` (to `['zones.*', <count subquery>]`), and `Builder::get($columns)` only applies
its argument when `columns` is still null. So the explicit list in `get(['id','uuid','name','code'])`
is **ignored** and the response carries every column plus `states_count` — including
`created_at`/`updated_at`. That is harmless here (the zone payload is small and the test asserts
the keys it needs are present, not that others are absent), but do not "fix" the test when you
see extra keys, and do not assume the column list is trimming anything. If you want a trimmed
zone payload, `->map()` it explicitly the way `ApiGetStates` does.

- [ ] **Step 5: Move the `locations` group behind `jwt.verify`**

In `mems/routes/api.php`, the `Route::prefix('locations')` group currently sits in the outer
group (only `json-response` + `cors`). Add `jwt.verify`:

```php
    Route::prefix('locations')->name('api.locations.')->middleware('jwt.verify')->group(function () {
        Route::get('/zones', [App\Http\Controllers\LocationController::class, 'ApiGetZones'])->name('zones');
        Route::get('/states', [App\Http\Controllers\LocationController::class, 'ApiGetStates'])->name('states');
        Route::get('/lgas', [App\Http\Controllers\LocationController::class, 'ApiGetLgas'])->name('lgas');
    });
```

Leave the session-authenticated `location.*` routes in `routes/web.php` alone — they serve the
Inertia frontend and are a separate surface.

- [ ] **Step 6: Run the test to verify it passes**

Run: `php artisan test --filter=LocationApiTest`
Expected: PASS, 10 tests.

- [ ] **Step 7: Verify the whole backend still works**

Run: `php artisan test`
Expected: PASS — including the pre-existing `GeographyControllerTest`, which exercises the
Inertia routes and must be unaffected.

Run: `php artisan route:list --path=locations`
Expected: the three routes listed, each showing `jwt.verify` in its middleware.

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

