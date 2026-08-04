### Task 4: Rewire the Geography tab and `StateMultiSelect`

**Files:**
- Modify: `mne_frontend/src/components/Data&Reporting/IndicatorReports/SystemManagement/GeographyPanel.jsx`
- Modify: `mne_frontend/src/components/Data&Reporting/PerformanceReporting/StateMultiSelect.jsx`
- Delete: `mne_frontend/src/utils/mockGeography.js`
- Modify: `mne_frontend/src/mocks.guard.test.js` — remove `'mockGeography'` from `ALLOWED`
- Test: `mne_frontend/src/components/Data&Reporting/IndicatorReports/SystemManagement/GeographyPanel.test.jsx` (create)

**Interfaces:**
- Consumes: `useZones`, `useStates`, `useLgas`, `useZoneMutations`, `useStateMutations`, `useLgaMutations` from Task 3.
- Produces: nothing importable.

- [ ] **Step 1: Read both components and the shared panel first**

Read `GeographyPanel.jsx`, `StateMultiSelect.jsx`, and `SimpleCrudPanel.jsx` (the shared CRUD
table the panel drives). Note precisely what `SimpleCrudPanel` expects for `data`, `columns`,
`fields`, `filters`, `onCreate`, `onUpdate`, `onDelete`, and whether it supports a loading or
error state. Note that the current `GeographyPanel` keys a state's zone by **code**
(`zoneNameByCode[row.zone]`) — the API now supplies `zone_code` and `zone_name` directly, so
that lookup can go.

- [ ] **Step 2: Write the failing test**

Create `GeographyPanel.test.jsx`. Mock the hooks and assert the panel renders live rows, shows
an error branch on failure, and calls the mutations:

```jsx
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen } from '@testing-library/react';

const zonesState = { data: [], isLoading: false, isError: false, error: null };
const statesState = { data: [], isLoading: false, isError: false, error: null };
const lgasState = { data: [], isLoading: false, isError: false, error: null };

const mutation = () => ({ mutateAsync: vi.fn().mockResolvedValue({}), isPending: false });
const zoneMutations = { create: mutation(), update: mutation(), remove: mutation() };
const stateMutations = { create: mutation(), update: mutation(), remove: mutation() };
const lgaMutations = { create: mutation(), update: mutation(), remove: mutation() };

vi.mock('../../../../hooks/useLocations', () => ({
  useZones: () => zonesState,
  useStates: () => statesState,
  useLgas: () => lgasState,
  useZoneMutations: () => zoneMutations,
  useStateMutations: () => stateMutations,
  useLgaMutations: () => lgaMutations,
}));

import GeographyPanel from './GeographyPanel';

describe('GeographyPanel', () => {
  beforeEach(() => {
    zonesState.data = [{ id: 1, name: 'North West', code: 'NW', states_count: 7 }];
    statesState.data = [
      { id: 10, name: 'Kano', zone_id: 1, zone_code: 'NW', zone_name: 'North West', lgas_count: 44 },
    ];
    lgasState.data = [
      { id: 100, name: 'Dala', state_id: 10, state_name: 'Kano', zone_code: 'NW', zone_name: 'North West' },
    ];
    zonesState.isError = false;
    statesState.isError = false;
    lgasState.isError = false;
  });

  it('renders states from the API, with their zone and LGA count', () => {
    render(<GeographyPanel canManage />);

    expect(screen.getByText('Kano')).toBeInTheDocument();
    expect(screen.getByText('North West')).toBeInTheDocument();
    expect(screen.getByText('44')).toBeInTheDocument();
  });

  // A failed fetch must not read as "there are no states".
  it('shows an error instead of an empty table when the fetch fails', () => {
    statesState.isError = true;
    statesState.error = new Error('Cannot reach the server.');
    statesState.data = [];

    render(<GeographyPanel canManage />);

    expect(screen.getByText(/cannot reach the server/i)).toBeInTheDocument();
  });
});
```

- [ ] **Step 3: Run the test to verify it fails**

Run: `npm test -- "src/components/Data&Reporting/IndicatorReports/SystemManagement/GeographyPanel.test.jsx"`
Expected: FAIL — the panel still reads `mockGeography` stores, so the mocked hooks are unused
and the assertions miss.

- [ ] **Step 4: Rewire `GeographyPanel.jsx`**

Replace the mock store usage with the hooks. Keep the three-tab structure and the
`SimpleCrudPanel` configuration; change the data source, the column accessors, and the
create/update/delete handlers.

This is the one step in this plan that specifies changes as prose rather than a complete code
block, because it is a whole-component rewrite of a ~160-line file whose current shape you must
read first. **Rewrite the component in full rather than patching it line by line** — the data
source, three column sets, two form field sets, three handler triples, and the error branches
all change together, and a partial edit will leave the zone-code lookup half-removed. Every
change below is mandatory, not a suggestion:

- Header comment: state that all three tabs are backed by `/locations` (reads from
  `LocationController`, writes from `GeographyApiController`).
- `const zones = useZones();` etc., plus the three mutation hooks.
- Zone filter options come from `zones.data`: `zones.data.map((z) => ({ value: z.id, label: z.name }))`.
  Filter states by `zone_id` rather than by code.
- States columns: `Name` → `row.name`; `Zone` → `row.zone_name`; `LGAs` → `row.lgas_count`.
- Zones columns: `Name`, `Code`, `States` → `row.states_count`.
- LGAs columns: `Name`, `State` → `row.state_name`, `Zone` → `row.zone_name`.
- State form field for zone becomes a select over `{ value: zone.id, label: zone.name }` writing
  `zone_id`. LGA form field becomes a select over `{ value: state.id, label: state.name }`
  writing `state_id`. **Delete `deriveLgaFields`** — zone is no longer derived client-side; the
  API returns it.
- Handlers await the mutations and surface failures. Deleting a zone with states now returns a
  real 422, so this must be visible:

```javascript
  const handleDelete = (remove, label) => async (id) => {
    try {
      await remove.mutateAsync(id);
      showToast.success(`${label} deleted.`);
    } catch (error) {
      showToast.error(error?.message || `Unable to delete this ${label.toLowerCase()}.`);
    }
  };
```

Import `showToast` from `../../../../utils/toast` (check the relative depth against the file's
existing imports).

- Add an error branch above each tab's panel, following the house pattern used in
  `ApprovalsView.jsx` and `SettingsView.jsx` — a red line printing `error?.message`. It must be
  mutually exclusive with the table, so a failure never renders as an empty list.
- Pass the query's `isLoading` into `SimpleCrudPanel` if it supports a pending prop; if it does
  not, render a short "Loading…" line above the table rather than adding a prop to a shared
  component used elsewhere.

- [ ] **Step 5: Rewire `StateMultiSelect.jsx`**

Replace `import { STATES_SEED } from '../../../utils/mockGeography'` and the derived
`STATE_OPTIONS` constant with `useStates()`. The component currently builds a module-level
constant; it must now read from the hook inside the component body.

The selected value is a list of **state names** (both call sites, `ProgramReportModal` and
`CreateProgramModal`, store names). Keep that contract — do not switch to ids in this task, or
you will silently break saved coverage lists. Map to names:

```javascript
  const { data: states = [], isLoading, isError } = useStates();
  const stateOptions = useMemo(() => states.map((state) => state.name), [states]);
```

Render a disabled control with a short message when `isError` is true rather than an empty
dropdown that looks like "no states exist".

- [ ] **Step 6: Delete the mock and tighten the guard**

Delete `src/utils/mockGeography.js`.

In `src/mocks.guard.test.js`, remove the `'mockGeography'` entry from `ALLOWED` along with its
comment. The remaining entries stay.

- [ ] **Step 7: Verify**

Run: `npm test -- "src/components/Data&Reporting/IndicatorReports/SystemManagement/GeographyPanel.test.jsx"`
Expected: PASS.

Search `src/` for `mockGeography`, `STATES_SEED`, `ZONES_SEED`, `zonesStore`, `statesStore`,
`lgasStore` (Grep tool, not `npx rg`).
Expected: no matches.

Run: `npm test -- --run`
Expected: PASS. The guard test must still pass with `mockGeography` removed from `ALLOWED` —
if it fails, something still imports the deleted module.

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

