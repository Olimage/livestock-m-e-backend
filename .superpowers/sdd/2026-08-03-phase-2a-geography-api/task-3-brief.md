### Task 3: Frontend location service and hooks

**Files:**
- Create: `mne_frontend/src/services/locationApi.js`
- Create: `mne_frontend/src/hooks/useLocations.js`
- Create: `mne_frontend/src/hooks/useLocations.test.js`
- Modify: `mne_frontend/src/config/apiConfig.js` — add a `LOCATIONS` block to `ENDPOINTS`

**Interfaces:**
- Consumes: `unwrap` from `src/services/unwrap.js`; `axiosInstance` from `src/services/apiService.js`; the endpoints from Tasks 1 and 2.
- Produces:
  - `locationApi.{zones,states,lgas}(params)` → arrays
  - `locationApi.{createZone,updateZone,deleteZone,createState,updateState,deleteState,createLga,updateLga,deleteLga}`
  - `useZones(params, options)`, `useStates(params, options)`, `useLgas(params, options)` — react-query queries
  - `useZoneMutations()`, `useStateMutations()`, `useLgaMutations()` — each returning `{ create, update, remove }` mutation objects that invalidate the relevant query keys on success
- Query keys: `['locations','zones',params]`, `['locations','states',params]`, `['locations','lgas',params]`
- Task 4 consumes all of the above.

- [ ] **Step 1: Read the patterns you are following**

Read `src/services/workflowApi.js` and `src/hooks/useWorkflows.js` — they are the closest
existing precedent for a service + query/mutation hook pair. Read `src/services/unwrap.js` to
confirm the envelope handling, and `src/config/apiConfig.js` for the `ENDPOINTS` shape.

- [ ] **Step 2: Add the endpoints to `apiConfig.js`**

Add to the `ENDPOINTS` object, after `DEPARTMENTS`:

```javascript
  LOCATIONS: {
    ZONES: '/locations/zones',
    ZONE_DETAIL: (id) => `/locations/zones/${id}`,
    STATES: '/locations/states',
    STATE_DETAIL: (id) => `/locations/states/${id}`,
    LGAS: '/locations/lgas',
    LGA_DETAIL: (id) => `/locations/lgas/${id}`,
  },
```

- [ ] **Step 3: Write the failing test**

Create `src/hooks/useLocations.test.js`. Test the service's param handling and the hooks'
key/invalidation behaviour with axios mocked:

```javascript
import { describe, it, expect, vi, beforeEach } from 'vitest';

const get = vi.fn();
const post = vi.fn();
const put = vi.fn();
const del = vi.fn();

vi.mock('../services/apiService', () => ({
  axiosInstance: {
    get: (...args) => get(...args),
    post: (...args) => post(...args),
    put: (...args) => put(...args),
    delete: (...args) => del(...args),
  },
}));

import { locationApi } from '../services/locationApi';

const envelope = (data) => ({ data: { status: true, message: 'Success', data } });

describe('locationApi', () => {
  beforeEach(() => {
    get.mockReset();
    post.mockReset();
    put.mockReset();
    del.mockReset();
  });

  it('returns the unwrapped zone list', async () => {
    get.mockResolvedValue(envelope([{ id: 1, name: 'North West', code: 'NW', states_count: 7 }]));

    await expect(locationApi.zones()).resolves.toEqual([
      { id: 1, name: 'North West', code: 'NW', states_count: 7 },
    ]);
    expect(get).toHaveBeenCalledWith('/locations/zones', { params: {} });
  });

  it('passes filters through as query params', async () => {
    get.mockResolvedValue(envelope([]));

    await locationApi.lgas({ state_id: 3, search: 'maku' });

    expect(get).toHaveBeenCalledWith('/locations/lgas', { params: { state_id: 3, search: 'maku' } });
  });

  // The server 422s a delete that would orphan children; unwrap turns
  // status:false into a throw, and that must reach the caller.
  it('rejects when the server refuses a delete', async () => {
    del.mockResolvedValue({ data: { status: false, message: 'This zone still has states.' } });

    await expect(locationApi.deleteZone(1)).rejects.toThrow('This zone still has states.');
  });

  it('posts a new state to the right path', async () => {
    post.mockResolvedValue(envelope({ id: 9, name: 'Kano', zone_id: 2 }));

    await locationApi.createState({ name: 'Kano', zone_id: 2 });

    expect(post).toHaveBeenCalledWith('/locations/states', { name: 'Kano', zone_id: 2 });
  });

  it('puts an updated lga to its detail path', async () => {
    put.mockResolvedValue(envelope({ id: 4, name: 'Dala', state_id: 1 }));

    await locationApi.updateLga(4, { name: 'Dala', state_id: 1 });

    expect(put).toHaveBeenCalledWith('/locations/lgas/4', { name: 'Dala', state_id: 1 });
  });

  it('tolerates a paginated payload by returning it untouched', async () => {
    get.mockResolvedValue(envelope({ data: [{ id: 1 }], total: 1 }));

    await expect(locationApi.lgas({ per_page: 25 })).resolves.toEqual({ data: [{ id: 1 }], total: 1 });
  });
});
```

- [ ] **Step 4: Run the test to verify it fails**

Run from `mne_frontend`: `npm test -- src/hooks/useLocations.test.js`
Expected: FAIL — `Failed to resolve import "../services/locationApi"`.

- [ ] **Step 5: Write the service**

Create `src/services/locationApi.js`:

```javascript
// src/services/locationApi.js
// Zone → State → LGA reference data. Reads come from LocationController
// (optional filters, child counts, parent names); writes from
// GeographyApiController. Both live under /locations and both require a JWT.
import { axiosInstance } from './apiService';
import { ENDPOINTS } from '../config/apiConfig';
import { unwrap } from './unwrap';

export const locationApi = {
  zones: (params = {}) => axiosInstance.get(ENDPOINTS.LOCATIONS.ZONES, { params }).then(unwrap),
  states: (params = {}) => axiosInstance.get(ENDPOINTS.LOCATIONS.STATES, { params }).then(unwrap),
  lgas: (params = {}) => axiosInstance.get(ENDPOINTS.LOCATIONS.LGAS, { params }).then(unwrap),

  createZone: (payload) => axiosInstance.post(ENDPOINTS.LOCATIONS.ZONES, payload).then(unwrap),
  updateZone: (id, payload) => axiosInstance.put(ENDPOINTS.LOCATIONS.ZONE_DETAIL(id), payload).then(unwrap),
  deleteZone: (id) => axiosInstance.delete(ENDPOINTS.LOCATIONS.ZONE_DETAIL(id)).then(unwrap),

  createState: (payload) => axiosInstance.post(ENDPOINTS.LOCATIONS.STATES, payload).then(unwrap),
  updateState: (id, payload) => axiosInstance.put(ENDPOINTS.LOCATIONS.STATE_DETAIL(id), payload).then(unwrap),
  deleteState: (id) => axiosInstance.delete(ENDPOINTS.LOCATIONS.STATE_DETAIL(id)).then(unwrap),

  createLga: (payload) => axiosInstance.post(ENDPOINTS.LOCATIONS.LGAS, payload).then(unwrap),
  updateLga: (id, payload) => axiosInstance.put(ENDPOINTS.LOCATIONS.LGA_DETAIL(id), payload).then(unwrap),
  deleteLga: (id) => axiosInstance.delete(ENDPOINTS.LOCATIONS.LGA_DETAIL(id)).then(unwrap),
};

export default locationApi;
```

- [ ] **Step 6: Write the hooks**

Create `src/hooks/useLocations.js`:

```javascript
// src/hooks/useLocations.js
// Queries and mutations for the geography hierarchy. Deleting a zone or state
// that still has children is refused by the server with a 422; unwrap turns
// that into a throw, so callers must catch and surface the message.
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { locationApi } from '../services/locationApi';

const asArray = (value) => (Array.isArray(value) ? value : []);

export const useZones = (params = {}, options = {}) =>
  useQuery({
    queryKey: ['locations', 'zones', params],
    queryFn: () => locationApi.zones(params),
    select: asArray,
    ...options,
  });

export const useStates = (params = {}, options = {}) =>
  useQuery({
    queryKey: ['locations', 'states', params],
    queryFn: () => locationApi.states(params),
    select: asArray,
    ...options,
  });

export const useLgas = (params = {}, options = {}) =>
  useQuery({
    queryKey: ['locations', 'lgas', params],
    queryFn: () => locationApi.lgas(params),
    select: asArray,
    ...options,
  });

// Deleting a zone changes state counts; deleting a state changes LGA counts.
// Invalidating the whole 'locations' tree is cheaper to reason about than
// tracking which counts moved, and these lists are small and rarely written.
const useLocationMutations = ({ create, update, remove }) => {
  const queryClient = useQueryClient();
  const invalidate = () => queryClient.invalidateQueries({ queryKey: ['locations'] });

  return {
    create: useMutation({ mutationFn: create, onSuccess: invalidate }),
    update: useMutation({ mutationFn: ({ id, payload }) => update(id, payload), onSuccess: invalidate }),
    remove: useMutation({ mutationFn: (id) => remove(id), onSuccess: invalidate }),
  };
};

export const useZoneMutations = () =>
  useLocationMutations({
    create: locationApi.createZone,
    update: locationApi.updateZone,
    remove: locationApi.deleteZone,
  });

export const useStateMutations = () =>
  useLocationMutations({
    create: locationApi.createState,
    update: locationApi.updateState,
    remove: locationApi.deleteState,
  });

export const useLgaMutations = () =>
  useLocationMutations({
    create: locationApi.createLga,
    update: locationApi.updateLga,
    remove: locationApi.deleteLga,
  });
```

Note `select: asArray` normalises a paginated payload to `[]` rather than crashing a `.map` —
this plan's endpoints return plain arrays by default, so `select` is a guard, not the happy path.

- [ ] **Step 7: Run the test to verify it passes**

Run: `npm test -- src/hooks/useLocations.test.js`
Expected: PASS, 6 tests.

- [ ] **Step 8: Verify**

Run: `npm test -- --run`
Expected: PASS, 77 tests (71 + 6).

Run: `npm run build`
Expected: success.

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

