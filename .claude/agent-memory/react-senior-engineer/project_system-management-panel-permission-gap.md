---
name: system-management-panel-permission-gap
description: SystemManagementPanel in mne_frontend's IndicatorReportsPage still gates writes on isPrs (module identity) not manage-settings, deliberately left unfixed
metadata:
  type: project
---

`mne_frontend/src/components/Data&Reporting/IndicatorReports/SystemManagement/SystemManagementPanel.jsx`
is rendered from `IndicatorReportsPage.jsx` with `canManage={isPrs}` (module identity only), same
pattern `GeographyPanel` had before the 2026-08-03 fix (see final-fix-report.md in
`mems/.superpowers/sdd/2026-08-03-phase-2a-geography-api/`). `GeographyPanel` was fixed to
`canManage={isPrs && usePermission('manage-settings')}` because its Geography (Zone/State/LGA)
backend now enforces `permission:manage-settings` on all 9 write endpoints. `SystemManagementPanel`
was deliberately **not** fixed the same way in that pass.

**Why:** per `SystemManagementPanel.jsx`'s own header comment, only Departments/Bond
Deliverables/Sectoral Goals have a real read-only `/api/v1` GET — every write in every sub-tab
(Departments, Disaggregation, NL-GAS Pillars, Bond Deliverables, Sectoral Goals Management,
Indicator Baseline) is still `useLocalWriteOverlay` or `createMockCrudStore` (no real JSON API
yet, only session-authenticated Inertia `web.php` routes). There is no backend permission to
mismatch against today, so gating on `manage-settings` would be gating against nothing.

**How to apply:** when any of `SystemManagementPanel`'s sub-tabs get a real write API in `mems`
(check for a new `permission:manage-settings`-gated route group analogous to
`routes/v1/admin-crud.php`), apply the same fix pattern used for `GeographyPanel`:
`usePermission('manage-settings')` from `src/auth/usePermission.js`, combined with `isPrs`. Don't
assume it's already done — re-check the panel's imports/props before recommending, since this
memory describes a gap that was open as of 2026-08-03, not a fix that was applied there.
