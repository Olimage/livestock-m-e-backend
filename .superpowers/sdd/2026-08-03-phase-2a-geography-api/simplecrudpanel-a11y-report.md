# SimpleCrudPanel a11y fix — report

## What changed

`src/components/Data&Reporting/IndicatorReports/SystemManagement/SimpleCrudPanel.jsx`:

- Added a `slugify()` helper (lowercase, non-alphanumerics → `-`, trim leading/trailing `-`).
- In the `fields.map(...)` render, compute `fieldId = \`${slugify(title)}-${slugify(field.key)}\`` per field — unique per panel instance because the panel's own `title` prop is part of the id, so two `SimpleCrudPanel`s on the same page (e.g. Disaggregation Categories + Disaggregation Items) can never collide.
- Non-toggle branch: `<label htmlFor={fieldId}>` and pass `id={fieldId}` into `FieldInput`.
- `FieldInput`: threaded the new `id` prop onto the three genuinely labelable controls — `<textarea>`, `<select>`, and the default `<input>`. `toggle` branch untouched (composite `ToggleInput`, already excluded from this branch).
- Toggle branch left exactly as before (still a `<span>` label, no htmlFor — `ToggleInput` has no single labelable element).

`src/components/Data&Reporting/IndicatorReports/SystemManagement/GeographyPanel.test.jsx`:

- Create-state test: replaced `screen.getByRole('textbox')` + `getAllByRole('combobox')` positional lookup with `screen.getByLabelText(/state name/i)` and `screen.getByLabelText(/^zone/i)`.
- Update-zone test: replaced the `getAllByRole('textbox')` destructure with `screen.getByLabelText(/^code/i)`.
- Confirmed actual rendered label text before writing matchers: required fields append `*` with no space (e.g. `"State name*"`, `"Zone*"`, `"Code*"`), hence the regex matchers rather than exact strings.

## checklist / itemList decision

Left both **without** an id/htmlFor association (label renders with `htmlFor={undefined}`), because neither has a single control that represents the field's value: `ChecklistInput`'s visible `<input>` is a filter/search box (not the value), and `ItemListInput`'s `<input>` is a scratch pad for the next item to add (also not the value) — wiring either would mislabel it, which is worse than the current no-op. Added a comment in the code explaining this so it doesn't get "fixed" back into a wrong association later.

## Consumers checked

Confirmed `SimpleCrudPanel`'s only two consumers are `GeographyPanel.jsx` and `SystemManagementPanel.jsx` (grep). Read `SystemManagementPanel.jsx` in full — it uses `checklist` (Bond Deliverables' Linked Indicators) and `itemList` (Disaggregation Categories' Items) alongside `text`/`textarea`/`select`/`toggle`/`number`. No prop or behavior changes beyond id/htmlFor; `fields` shape and every field's `type`/`options`/`placeholder`/`blankLabel`/`belowHint`/`hint` usage is untouched.

## Test/build output

`npm test -- --run`:
```
Test Files  12 passed (12)
     Tests  86 passed (86)
```
Matches the stated baseline (86 across 12 files) — `GeographyPanel.test.jsx` (9 tests) included and green.

`npm run build`: succeeded (`✓ built in 6.29s`). Pre-existing "chunk larger than 500kB" warning on `index-*.js`, unrelated to this change (no new imports added).

## Collision check

All `title` values passed to `SimpleCrudPanel` across both consumers are distinct strings (`Total States`, `Total Zones`, `LGAs` in GeographyPanel; `NLGAS Pillars`, `Bond Deliverables`, `Sectoral Goals Management`, `Disaggregation Categories`, `Disaggregation Items`, `Departments`, `Indicator Baseline` in SystemManagementPanel), so `slugify(title)` prefixes never collide even when multiple panels are mounted on the same page simultaneously (Disaggregation tab renders two at once).

## Self-review

- Did not touch the toggle branch, `ChecklistInput`, or `ItemListInput` internals — only read them.
- Did not change any prop names, field schema, or component signatures — `FieldInput` gained one new optional prop (`id`), backward compatible.
- Verified label text before writing test regexes rather than guessing.
- No git commands run.
