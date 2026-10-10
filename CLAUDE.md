# CLAUDE.md

Jell Group ERP (ES Group): HR, biometrics, payroll, IT, fleet, maintenance and inventory.
Branch `feature/react-shadcn` holds the ongoing move from Blade to React + shadcn/ui.

## Stack

- Laravel 12, PHP 8.2, MariaDB (XAMPP on Windows, git-bash shell), spatie/laravel-permission.
- Inertia v3 + React 19 + TypeScript, Tailwind v4, shadcn/ui (new-york style), lucide-react icons, recharts (via the shadcn `chart` component), Vite 7.
- React code lives in `resources/js/react/` (`pages/`, `components/`, `components/ui/`, `lib/`, `layouts/`, `types/`). The only Vite CSS entry is `resources/css/react.css`. `public/assets/img` holds just the few images in use: favicons, the bus photo (login) and `no-image-default`.
- Backend is moving module by module to a **layered structure** (Controller → Service → Repository → Model). See "Backend structure" below. Done for **every sidebar group** (General, Fleet, IT Support, Human Resources, Biometrics, Scheduling & Rates, Payroll, Maintenance, Inventory, Products, Security). `app/Http/Controllers` has only module folders plus `Controller.php`; new code goes into the matching module folder.

## How the user works

- The user sends sidebar screenshots one section at a time. Finish only that section, then stop and report. Do not start the next section until asked.
- UI changes must keep 100% of the existing functionality: same endpoints, validation, permissions, flash messages and redirects. Improve the look, never drop a feature silently.
- Never commit or push unless asked. The user commits.
- When a request is UI-only but you find a real bug (500 error, data loss, broken route, dead link), fix it with a test and list it in the report. Leave larger pre-existing problems alone and report them.
- Do not add fake or placeholder features: no Microsoft sign-in, language picker, fake stats or demo tables. If a reference design shows something the app cannot do, leave it out and say so.
- Branding is always "Jell Group" / "Jell Group of Company" with the existing logo (`public/assets/img/favicons/esgroup-logo180x180.png`). Never copy another company's name or logo from a reference image.
- Final reports: short, plain language. Say what changed, what bugs were fixed, anything left out on purpose, and that nothing is committed.
- **Always keep this file current.** After every task, update CLAUDE.md with any new reusable file, pattern or rule, so the next module reuses it instead of rebuilding it.

## Converting a page to React (the standard pattern)

1. The controller returns `Inertia::render('<area>/<page>', [...])`. Map models to plain arrays (`->through()` for paginators, `->map()` for collections). Never pass Eloquent models straight through.
2. Pass permissions as `can.*` booleans (mirror the route middleware permission exactly) and endpoints as `urls.*` (built with `route()`).
3. Keep the same endpoints. Forms use Inertia `useForm` and post to the same routes. Validation errors show under the fields. `flash()` / `->with('success'|'error')` messages are shared automatically and shown as top-right toasts (see Messages below).
4. State-changing GET routes get converted to POST, PUT or DELETE (keep the same URL and route name) and are tested.
5. Add the route name to `App\Support\Navigation\MainNavigation::INERTIA_ROUTES` so the sidebar link is a client-side visit. Routes not listed there are full page loads into Blade (`HandleInertiaRequests` returns a 409 location for Blade pages).
6. Paginators built from collections with `forPage()` keep their keys. Re-index with `->values()` or page 2+ turns into a JSON object.
7. Add or extend a feature test (`tests/Feature/...`) that renders the component with `assertInertia` and exercises every write action.
8. Printable pages are React too: `components/print/print-shell.tsx` (`PrintShell`, `printTable`) gives the paper look, a Print / Close bar that never prints, the Jell Group header and auto-print (options: `landscape`, `pageSize`, `bare`). Examples: `it/cctv/print`, `it/job-orders/print`, `payroll/benefits-records/print`, `payroll/attendance-summary/export`. Link to them with `<a target="_blank">`. Only server-generated **PDF downloads** (dompdf: payslip, 201 file, ticket export) and CSV/Excel streams stay on the server side.

### Standard list + modal pattern (Payroll is the reference implementation)

Every page under `pages/payroll/` is on this pattern, and so are the whole IT Support group (`pages/it/*`: bus dashboard, tickets, CCTV, inventory), the whole Human Resources group (`pages/hr/*`: employees, departments, offenses, claims, leaves) and the whole Scheduling & Rates group (including `pages/biometrics/employees/*`). That covers Adjustment, Summary, Payroll, Benefits Records and Overall, Transaction Logs, Work Schedule, Employee Rates, Holiday Calendar, Manual Biometrics and Biometric Employees.

Every list page is built the same way. Copy `pages/payroll/payrolls/index.tsx` or `pages/payroll/adjustments/index.tsx`.

1. **Page file = `definePage`** (`lib/define-page.tsx`):
   `export default definePage<Props>({ title, description?, actions?, size?, Content })`.
   - The same file renders as a full page (sidebar and header) or inside a modal.
   - `size` can be `'sm' | 'md' | 'lg' | 'xl' | 'full'`, or a function of props, e.g. `({ items }) => items.length > 8 ? 'full' : 'xl'`.
   - Header actions that need hooks go in their own component (`actions: (p) => <Actions {...p} />`).
   - Hide "Back" links when `useModal().inModal`.
2. **Tables = `DataTable`** (`components/data-table/data-table.tsx`):
   - Search filters as you type (debounced), with no Search button.
   - Column filters sit inside the header row: `filter: { type: 'text' | 'select' | 'date', param }` or `{ type: 'daterange', from, to }`.
   - Active filters show as chips with Clear all. Also included: pagination, an empty state, `rowActions`, `onRowClick`, `footer` and `toolbar`.
   - Use `hideBelow: 'md' | 'lg' | 'xl' | '2xl'` to hide less important columns, so rows are never crowded.
   - **Server mode:** `paginator`, `url` and `filters` (plus `keepParams` for params the table doesn't own). The controller must accept each filter param.
   - **Client mode:** pass `rows` (all data already on the page). `column.value` gives the text used for searching and filtering.
3. **New / View / Edit open in a modal, not a new route:**
   - `<Button asChild><ModalLink href={url} mode="form">New</ModalLink></Button>` opens a create or edit form, which closes after a successful save.
   - `ModalLink` without a mode (`view`) opens a record; actions inside it refresh it.
   - `openModal(url, { mode, size })` does the same from code (e.g. `onRowClick`).
   - Modals stack (a payroll, then an employee inside it). Ctrl-click opens the real page. A ⤢ button opens the full page.
4. **Writes inside page content always go through `useModal().visit()`:** `form.post(url, modal.visit({ ... }))` or `router.post(url, {}, modal.visit({ ... }))`.
   - In a modal, the save stays on the page underneath and the modal closes (form), refreshes (view), or opens the record the controller redirected to.
   - On a full page it's a no-op.
   - GET navigation inside content (filters, paging) uses `modal.get(url, params)`; `DataTable` does this itself.
   - Server side: `app/Http/Middleware/HandleModalRedirects.php` (header `X-Modal-Base`, flash `modal_redirect`, shared as `modal.redirect`). Controllers need no changes.
5. **Details already on the page** (log rows, summary rows, benefit breakdowns) open in `DetailDialog` + `DetailGrid` (`components/modal/detail-dialog.tsx`). No route is needed.
6. **Period controls** sit in the table toolbar:
   - `MonthYearPicker` (`components/data-table/month-year-picker.tsx`);
   - `CutoffToolbar` (`components/data-table/cutoff-toolbar.tsx`, payroll 1st/2nd cutoff).
   Both keep the other filters. When a period picker owns `month`/`year`, pass only the table's own params as `filters` and the period as `keepParams` (see `pages/payroll/holidays/index.tsx`), so Clear all doesn't reset the period.
7. **Form pages** (`holidays/form.tsx`, `employee-salaries/form.tsx`) also use `definePage`, with a `BackLink` hidden in modals, `form.post/put(url, modal.visit(...))` and a Cancel button that calls `modal.close` in a modal. A long form uses `size: 'full'`.
8. **Editable grids** (a table of inputs saved with one button, e.g. `pages/payroll/plotting/index.tsx`):
   - Use a server-mode `DataTable` whose `cell`s render the inputs. Map each row to its form index with a `Map` of ids.
   - Key the editor component on the row data (`key={JSON.stringify(rows...)}`) so filtering, paging or a save remounts `useForm`; otherwise stale edits stay on screen.
   - Send the current filters with the save through `form.transform(...)`, so the redirect keeps them.
   - Put Save in both `toolbar` and `footer`. Show an "Unsaved changes" note from `form.isDirty`. Stack related inputs in one column (shift + hours, time + grace, days off + remarks) instead of scrolling sideways.
9. **Record detail layout (no repeated figures):** the reference is `pages/payroll/items/show.tsx`.
   - One `PayFlow` strip (base → − loss → + additions → = gross → − deductions → = net).
   - Then `Breakdown` cards, each with its total in the header and its own `Line` rows (hint under the label, e.g. "60 min × ₱1.71").
   - Zero lines collapse into one "None this cutoff: …" note.
   - Show each number in exactly one place.
10. **Profile / record pages with many fields** ("data by data", reference `pages/hr/employees/show.tsx`):
   - A summary band with photo, key facts, QR ID and a **completeness bar** listing the missing fields. Each missing chip opens the dialog that fills it.
   - Then `Tabs` with count badges; an amber count means that many fields are missing.
   - Each section is a card of `Field` rows (label left, value right). Empty required values show an amber "Not set"; `optional` fields show "—".
   - Open these pages as a view-mode modal from the list (`openModal(url, { size: 'xl' })`).
   - **Any dialog component that posts from inside a page** (e.g. `components/hr/profile-dialogs.tsx`, `violation-dialog.tsx`) must wrap its options in `useModal().visit(...)`, or the save navigates the page underneath.
11. **Count cards as quick filters:** stat cards above a table can be buttons that set a filter through `modal.get(urls.index, {...filters, key: value})`, with the active one ringed (`Metric` in `pages/biometrics/employees/index.tsx`).
12. **Small create forms** (e.g. a company tag) use a local `Dialog` in the header actions, not an inline collapsible.
13. **Small lists already on the page** (e.g. saved manual logs) use client-mode `DataTable` (`rows`, `column.value`, select filters compare lower-case text).

### Reusable pieces (use these before writing new ones)

- Layout: `layouts/app-layout.tsx` (`<AppLayout title>`, mounts `ModalStack`), `lib/define-page.tsx` (`definePage`, `ModalSize`), `components/page-header.tsx` (`PageHeader`, `StatCard`).
- Modals: `components/modal/modal-link.tsx` (`ModalLink`), `modal-store.ts` (`openModal`, `closeAllModals`), `modal-context.tsx` (`useModal`: `inModal`, `visit`, `get`, `close`, `refresh`), `modal-stack.tsx`, `detail-dialog.tsx` (`DetailDialog`, `DetailGrid`).
- Tables: `components/data-table/data-table.tsx` (`DataTable`, `DataTableColumn`, `ColumnFilter`), `month-year-picker.tsx`, `cutoff-toolbar.tsx`. The older `data-pagination.tsx` is only for pages not yet on `DataTable`.
- **Every DataTable has Export (Excel .xlsx / CSV) and Print** (`components/data-table/data-export.ts`, on by default, `exportable={false}` to turn off).
  - It exports **all rows matching the current search and filters**: server tables fetch every page as Inertia JSON (`fetchInertiaProps` in `modal-store.ts`); client tables use the filtered rows. Capped at 200 pages.
  - Cell text comes from `column.exportValue` → `column.value` → the rendered cell's text (icons, buttons, avatars removed; flex/grid parts joined with " · "). Give `exportValue` to columns of inputs (see `payroll/plotting`), and `exportable: false` to columns that should not print.
  - Excel uses the `write-excel-file` package (lazy-loaded). Print opens a clean printable window with the Jell Group header, active filters and a record count.
- Forms: `form-field.tsx`, `search-select.tsx` (searchable select, `ariaLabel`), `employee-combobox.tsx`, `remote-employee-search.tsx`, `file-drop.tsx`, `cutoff-picker.tsx`, `adjustments/adjustment-editor.tsx` (modal-aware).
- Actions: `confirm-action.tsx` (`ConfirmAction`, `IconButton` with `onClick`). Buttons inside clickable rows must `stopPropagation()`; wrap row actions in a span that stops it, because dialog portals bubble through React.
- Dashboards: `components/dashboard/charts.tsx` (`DashboardCard`, `KpiCard`, `SimpleBarChart`, `SeriesChart`, `DonutChart`, `BreakdownList`).
- Status colours: `lib/employee-status.ts`, `lib/it-status.ts`, `lib/bus-status.ts`, `lib/fleet-status.ts`, `lib/dashboard-tones.ts`.
- Bus seat picker: `components/it/bus-seat-map.tsx` (`BusSeatMap`, `parseSeats`, `formatSeats`).
  - An animated top-down bus with seats 1-52 (1 = driver, 48-52 = the back row). The layout copies the old seat plan image (now deleted).
  - The animation starts when the map scrolls into view: the bus drives in, the roof lifts, then the seats pop in. It respects reduced motion, and there is a Replay button.
  - You can select several seats; the value is `"12, 13"`. `readOnly` shows the saved seats.
  - The server side is `App\Support\IT\SeatNumbers` (`normalize` + `rules`), used by the job order store and update requests.
- Domain components: `components/hr/*`, `components/inventory/*`, `components/maintenance/*`, `components/biometrics/*`.
- Overtime rules (`App\Services\Payroll\OvertimeCheckService`): an OT filing must be covered by the employee's biometric logs that day (first punch ≤ OT start, last punch ≥ OT end, 5-min grace; overnight OT uses the next morning's first punch), be at most 12 h, and not overlap another pending/approved OT. `PayrollAttendanceAdjustmentRequest` blocks the save with it; `GET payroll-attendance-adjustments/overtime-check` feeds the editor's live `OvertimeCheckPanel`.
- OT form upload: required for type `overtime` (`ot_form`, pdf/jpg/png/webp, 5 MB), stored on the private `local` disk under `payroll/ot-forms` (`attachment_*` columns), opened via `payroll-attendance-adjustments.attachment` (view permission). Editing keeps the old file unless a new one is sent. **File uploads on an edit must POST with `_method: 'put'`** (PHP can't read files from a real PUT); see `adjustment-editor.tsx` `submit`.
- Adjustment list: status cards (All / For approval / Approved / Rejected) are quick filters (`stats.status_*` are counted without the status filter); each row shows `decision` = who approved/rejected and when (`approver` / `rejector`). Approve/Reject stay on `payroll.finalize`.
- Employee Rates "Day off" (`payroll_employee_salaries.paid_day_off`, default true; existing daily rows were migrated to false so no pay changed). In `PayrollComputationService::createPayrollItem`: **Not paid** = pay only for days worked (a monthly employee is switched to daily logic with daily rate = monthly × 12 ÷ 365, so a worked 31st counts). **Paid** + daily = `computePaidDayOff()` adds 1 daily rate per unworked scheduled day off into `regular_pay` when `computeRestDayQualification` is qualified (3 valid log days or leave/adjustment). Meta `day_off` feeds the payroll item page (`dayOff` prop). Tests create daily salaries with `paid_day_off=false` unless testing this.
- **Scheduling & Rates → Employees is one profile per person** (sidebar shows only "Employees"; Work Schedule and Employee Rates are hidden from the menu but their pages stay, opened from the "Bulk schedule" / "Rates list" buttons, and keep "Employees" highlighted).
  - `biometrics.employees.show` (`pages/biometrics/employees/show.tsx`, opened as a view modal from the list): summary band + completeness, then tabs **Details** (embeds `BiometricEmployeeEdit` from `edit.tsx`), **Work schedule** (single-row editor posting to `payroll-plotting.save` with `schedule[0]`), **Rates** (embeds `EmployeeSalaryForm` from `employee-salaries/form.tsx`; create mode is prefilled with the person). Each tab is filled only with its own permission; rates also need the employee's payroll group (`PayrollGroupAccessService::allows`).
  - The list, profile and sidebar link open with any of `MainNavigation::EMPLOYEE_PERMISSIONS` (`biometrics.view|payroll-plotting.view|employee-salaries.view`). Sidebar permissions may be `a|b` (any of).
  - **`return_profile`**: the biometric update, Work Schedule save and rate store/update redirect to the profile when the request carries `return_profile` = that employee's id (otherwise the old redirects stay). The embedded forms take a `returnProfile` prop that adds it and hides their Cancel button.
  - Shared schedule inputs: `components/scheduling/schedule-fields.tsx` (`DayOffPicker`, `RowSelect`, `addMinutes`, `STATUS_OPTIONS`, `SHIFT_OPTIONS`, `FLEXIBLE_MODE_OPTIONS`, `TimePresetButtons`). Rate form props: trait `Controllers\Scheduling\Concerns\BuildsEmployeeRateForm`. The rate a profile edits: `EmployeeSalaryRepository::forEmployee` (active first, newest); list rows use `activeSalaryProfile` (the one payroll uses).
- **Different time per day** (Work Schedule): `employee_plotting_schedules.weekly_times` JSON on the permanent row, `{"Monday": {"time_in": "09:00", "time_out": "18:00", "workday_type": "eight_hours"}, ...}`; null = the same time every day (all old rows).
  - **Always read a schedule for a date through `EmployeePlottingSchedule::forDate($date)`.** It returns a read-only copy with that weekday's times and work hours, or the row itself. It is used by `PlottingScheduleRepository::scheduleOn`, `DailyAttendanceSummaryService::resolveScheduleForPersonDate` (so Attendance Summary and payroll get the day's times) and `BiometricsAttendanceService`. Never save the copy.
  - `weeklyTimes()` (valid entries only), `hasWeeklyTimes()`. Saving: `SaveWorkScheduleRequest` checks each day's span like the fixed one (`schedule.N.weekly_times.<Day>.time_out`). `WorkScheduleService::weeklyTimes()` drops days off, and is stored for scheduled Regular Shift and Flexible Shift (Custom).
  - UI: `PatternToggle`, `WeeklyTimesEditor`, `fillWeeklyTimes`, `weeklySummary` in `components/scheduling/schedule-fields.tsx`. The profile tab sends the first working day as the fixed times. The grid carries `weekly_times` through every save, has a "Per day…" dialog per row (Regular Shift only), and Quick Fill clears per-day times.
- **Flexible Shift has two sub-modes** (`employee_plotting_schedules.flexible_mode`, null for Regular Shift; `EmployeePlottingSchedule::FLEXIBLE_MODE_*|FLEXIBLE_MODES`):
  - **Anytime** (legacy default, `flexible_mode` null/`anytime`): no fixed time at all. Payable only on total clock minutes (paid + lunch) completed anywhere in the day.
  - **Custom**: one or more exact shift-time options (`employee_plotting_schedules.flexible_shift_options` JSON, e.g. `[{"time_in":"08:00","time_out":"17:00"},{"time_in":"09:00","time_out":"18:00"}]` — an employee who may clock in for either an 8:00 AM or a 9:00 AM shift). Each option must individually span the workday's exact clock hours (`SaveWorkScheduleRequest`, same span check as Regular Shift). `time_in`/`time_out` mirror the first option for other readers. The employee's actual time in is matched to the **closest** option (`EmployeePlottingSchedule::matchShiftOption`, nearest start time — `BiometricsAttendanceService::decorate()` does the same match for its preview), then late/undertime are computed with `computeRegularShiftDeductions` exactly like Regular Shift. The match happens once, early in `DailyAttendanceSummaryService::storeSummary` (overrides `$scheduledTimeIn`/`$scheduledTimeOut` before the holiday/leave/rest-day branches), so Custom mode then falls through the same `else` branch as Regular Shift — no separate branch needed.
  - **Carbon 3 gotcha:** `diffInMinutes()` now defaults to **signed**, not absolute (a Carbon 2 behaviour change). Matching "closest" always wraps the diff in `abs()` — dropping it silently breaks the match (an earlier option always "wins"). Caught by `tests/Feature/Scheduling/FlexibleShiftModesTest.php`.
  - `WorkScheduleService::savePermanentSchedule` only nulls `time_in`/`time_out`/`flexible_shift_options`/`weekly_times` for Anytime (`$noClock`); `weekly_times` ("different time per day") is Regular Shift only, never Flexible. `EmployeePlottingSchedule::resolvedFlexibleMode()` defaults legacy flexible rows (no saved `flexible_mode`) to Anytime; `resolvedShiftOptions()` falls back to the single `time_in`/`time_out` pair for legacy single-option rows. `DailyAttendanceSummary::requiresFixedScheduleTimes()` is true only for Custom (reads `meta.flexible_mode`).
  - UI: a "Flexible mode" `RowSelect` appears next to the Shift select when Flexible Shift is chosen, in both `pages/payroll/plotting/index.tsx` (grid + Quick Fill) and the profile's `ScheduleEditor` in `pages/biometrics/employees/show.tsx`. For Custom, `ShiftOptionsDialog` (`schedule-fields.tsx`) adds/edits/removes the options (min 1 kept); `TimePresetButtons` inside it adds a preset as a new option rather than overwriting. Quick Fill applies a single option to every visible row (edit individual employees afterward to add alternates).
  - Test reference: `tests/Feature/Scheduling/FlexibleShiftModesTest.php`.
- Employee ↔ biometrics link: `employees.employee_biometric_id` (unique, nullable). `App\Services\HR\BiometricLinkService` builds the option lists (name matches first, marked "Name match") and `assignEmployee()` (queries via `EmployeeRepository::biometricLinkCandidates/moveBiometricLink` and `EmployeeBiometricRepository::linkableToEmployee`); the profile card uses `Resources\HR\BiometricSummaryResource`. HR profile: "Biometrics" card + `components/hr/biometric-link-dialog.tsx` (PUT `employees.biometric-link.update`, `employees.update`). Biometric Employees: "HR: name" under the name in the list, "Linked HR employee" select (`hr_employee_id`) in the edit form. Relations: `Employee::biometric()`, `EmployeeBiometric::hrEmployee()`.

### Design rules

- shadcn look: cards, outline badges with tone classes, tables with an empty-state row, dialogs for create/edit, `AlertDialog` confirmations for destructive actions.
- Every overlay (Dialog, AlertDialog, Sheet in `components/ui/`) has a blurred background: `bg-black/40 backdrop-blur-sm`. Keep this if a shadcn component is re-added.
- **All layers share z-index 50** (modal-stack windows, dialogs, selects, popovers, date pickers); the newest one is added last and sits on top. Never give a stacked modal a higher z-index: `50 + depth × 2` hid every dialog / dropdown opened from a second-level modal (e.g. "File Adjustment" on a payroll item opened from a payroll). `modal-stack.tsx` puts `data-modal-depth` on each window for tests. In browser checks, `document.elementFromPoint` cannot prove what is on top while a dialog is open (layers underneath get `pointer-events: none`); compare screenshots instead.
- Modals fit their data: `components/modal/use-fit-width.ts` (used by `modal-stack.tsx`) measures each table's natural width and widens the modal just enough that no table scrolls sideways, capped at 96vw. Phones keep the normal full-width dialog.
  - While a modal is open it only grows, by at least 8px, to an even whole-pixel width. The modal body reserves its scrollbar space (`[scrollbar-gutter:stable]`). Letting it shrink back made modals wobble a few pixels every frame (width changes wrapping, wrapping changes the scrollbar, the scrollbar changes the width). Keep all three rules.
  - `size` is only the starting width. Use `'xl'` for pages with tables and let the auto-fit decide. `'full'` (always 96vw) is for special cases only.
  - Long-text cells: put a fixed-width `<div className="w-72 whitespace-normal">` inside the cell (a `max-w` on a `<td>` is ignored). Otherwise the text is measured unwrapped and pushes the modal to 96vw.
  - **No text overlap:** `<td>` is `whitespace-nowrap`, so a `max-w-*` / `w-<n>` box inside a cell used to let long text run over the next columns. A base rule in `resources/css/react.css` (`td :where([class*='max-w-'], w-<n>...)`) makes those boxes wrap; add `truncate` or `whitespace-nowrap` on the box when it must stay on one line. Label/value rows use `grid grid-cols-[minmax(0,1fr)_minmax(0,auto)]` with `min-w-0 break-words` on both sides (never `shrink-0` on a long value). Small stat grids start at `grid-cols-2` on phones. Scratchpad `overlap_check.js` scans every page (and stress-tests cell boxes with long text) at 1600 / 1440 / 390.
- Dialogs with long content use `max-h-[90vh] overflow-y-auto`. A sticky footer inside one uses `sticky -bottom-6 -mx-6 -mb-6`.
- Search boxes that are kept in local state must re-sync from the server filter (`useEffect(() => setSearch(filters.q), [filters.q])`).
- Numbers use `Intl.NumberFormat('en-US')`, money uses `peso()` from `lib/format.ts`, and chart axes use compact ticks.
- Layouts must work at phone width with no sideways scroll. Use `minmax(0, 1fr)` grid columns and `min(…, 100%)` widths.
  - Grid/flex items holding a chart or table need `min-w-0` (`DashboardCard` has it), or the chart sets the card wider than the screen.
  - Search rows: `flex w-full gap-2 md:w-auto`, the input wrapper `relative min-w-0 flex-1 sm:flex-none`, the input `w-full sm:w-72` (never a bare fixed width). Long `TabsList`: `max-w-full justify-start overflow-x-auto`.
  - Check every sidebar page at 390px with the scratchpad `all_check.js` (status, console errors, sideways scroll).

## Backend structure (Layered Architecture)

The user sends one sidebar group at a time to convert. **IT Support** (Bus Dashboard, Tickets Job Order, CCTV Concern, IT Inventory) and **Human Resources** (Employee List, Department & Position, HR Offenses, SSS / Maternity / Paternity, Leaves Admin / Driver / Conductor) and **Scheduling & Rates** (Employees, Work Schedule, Employee Rates, Holiday Calendar → `Controllers\Scheduling\BiometricEmployeeController|WorkScheduleController|EmployeeRateController|HolidayController`) and **Payroll** (Adjustment, Summary, Payroll, Benefits Records, Benefits Overall, Payroll Transaction Logs → `Controllers\Payroll\*`) and **Maintenance / Inventory / Products** (Parts Issuance, Maintenance Job Orders, Vehicle History, Bus List, Maintenance Stock, Receiving Area, Stock Transfer, Categories, Products → `Controllers\Maintenance\*`) and **Security** (Users, Roles → `Controllers\Security\*`) are done; copy their files for the next module.

```text
app/Http/Controllers/<Module>/      thin: read request → call service → render / redirect
app/Http/Requests/<Module>/         FormRequest validation (unchanged rules)
app/Http/Resources/<Module>/        model → array for a React page (JsonResource)
app/Services/<Module>/              business rules, DB::transaction, workflow, files, mail/events
app/Repositories/Contracts/<Module>/<Model>RepositoryInterface.php
app/Repositories/<Module>/<Model>Repository.php   every Eloquent query lives here
app/Models/                         relations, casts, scopes, module constants
database/                           migrations, seeders, factories
```

Rules:

- **Controllers** never call `Model::query()`, `DB::` or `Storage::`. They take only services, check role-based authorization (`abort_unless`), and build `can.*` / `urls.*`. Keep the same route names, URLs, flash messages and redirects.
  - A controller does one page or group. Split a mixed controller: the old `CctvController` became `CctvConcernController` + `BusDashboardController`.
- **Services** take repository **interfaces** in the constructor (never `Model::query()`). They own transactions, locks (through `findForUpdate`), status rules, numbering, stock moves, files and notifications, and they return models or collections. They never return Inertia responses or build `route()` URLs.
- **Repositories** only query: find, paginate with filters, create, update, delete and counts. No business rules. They return models, collections or paginators (`->withQueryString()`). Each one is `final`, implements its interface, and is bound in `App\Providers\RepositoryServiceProvider::$singletons` (registered in `bootstrap/providers.php`).
  - Shared models get a repository in their owning module and are reused: `Fleet\BusDetailRepository` (bus options, lock, by body number), `Security\UserRepository` (users by `role` column), `HR\EmployeeRepository` (directory, counts, leave candidates, options, lock, asset row, biometric link), `Biometrics\EmployeeBiometricRepository` (biometric directory, Work Schedule list with the permanent schedule, payroll-active people by allowed groups, counts, groups, chunked loop), `Biometrics\BiometricCompanyRepository`. Add methods there instead of creating a second repository for the same model.
  - Child records reached through a parent get their own small repository that takes the parent: `HR\EmployeeHistoryRepository`, `EmployeeAttachmentRepository`, `EmployeeLogRepository` (`findOrFail($employee, $id)`).
- **Resources** (`app/Http/Resources/<Module>/*Resource.php`, `final`, `@mixin` the model) turn a model into the page's array. Use them in controllers as `->through(fn ($m) => XResource::make($m)->resolve($request))` for paginators and `XResource::collection($c)->resolve($request)` for lists. Shared helper: `Resources\Concerns\FormatsDates::formatDate($value, $pattern)` (null when blank, raw text when unparsable).
- **Model constants** replace lists repeated in controllers and FormRequest rules (`Rule::in(Model::CONST)`): `JobOrder::CATEGORIES`, `JobOrder::NOTE_REASONS`, `CctvConcern::ISSUE_TYPES`, `CctvConcern::DASHBOARD_COLUMNS`, `Employee::COMPANIES|GARAGES|STATUS_TYPES|LAST_PAY_STATUSES|DISCIPLINARY_ACTIONS|DOCUMENTS|GOVERNMENT_IDS`, `HrOffense::TYPES|GRAVITIES`, `Claim::TYPES|STATUSES|DATE_FIELDS`, `LeaveRecord::TYPES|PROOFS`. Small display helpers go on the model: `BusDetail::displayName(withGarage)`, `JobOrder::busLabel()`, `Employee::age()|tenure()`, `LeaveRecord::statusLabel()|statusTone()|proofPath()`. Status groups go on the enum: `CctvConcernStatus::activeValues()` / `completedValues()`.
- **Parallel copies become one implementation + a "kind" enum.** Leaves (Admin / Driver / Conductor) were three copies of every file; now `App\Enums\LeaveKind` holds what differs (model class, route name, permission prefix, noun/title, required position, proof folder, wording). One abstract base model (`Models\LeaveRecord`, the three models only set `$table`), one `LeaveRepository` (every method takes the kind), one `LeaveService` / `LeaveNoticeService` / `LeaveReminderService`, and an abstract `Controllers\HR\LeaveController` with three tiny subclasses that only return their kind (so routes and names stay the same). Routes with `{leave}` use `->whereNumber('leave')` and the controller loads by id through the service. Reuse this pattern for any other A/B/C copies.
- **Private files** (profile photo, 201 documents, notice proofs, attachments): the service checks the path and returns the absolute path (404 when missing); the controller answers `response()->file($path, [inline, private no-store, nosniff headers])` or `response()->download($path, $name)`. `SecureFileController` is gone; each module serves its own files.
- **Scheduled commands** are thin like controllers: same `$signature`/`$description` (the schedule in `routes/console.php` uses them), `handle()` calls one service method and passes a `fn ($level, $message) => $this->{$level}($message)` output callback (see `ProcessDriverLeaveReadyForDuty` → `LeaveReminderService`).
- **Inline `$request->validate()`** becomes a FormRequest (`StoreHrOffenseRequest`, `UpdateEmployeeBiometricLinkRequest`). Requests that were identical copies merge into one (`LeaveActionRequest`).
- **Filters run in the query, never after `paginate()`.** Filtering a page collection afterwards shows short or empty pages and wrong totals (fixed on the Bus Dashboard and Work Schedule). For "default when no row exists" filters, use `whereHas(... = value)` plus `orWhereDoesntHave(...)` when the value is the default (see `EmployeeBiometricRepository::wherePermanentSchedule`, relation `EmployeeBiometric::permanentSchedule()`).
- **Payroll group access:** `payroll.group` middleware puts `"all"` or a list of group numbers in `session('payroll_allowed_groups')`. Controllers read it and pass it to the service/repository (`string|array|null $allowedGroups`); an empty list means no rows.
- **Forms for create and edit** use one Resource that also accepts null for the blank form (`EmployeeRateFormResource::make($salary)`, `HolidayFormResource::make($holiday)`); a sync guarded by a cache lock returns `null` from the service when already running, and the controller shows the warning.
- More constants: `EmployeeBiometric::GROUP_LABELS`, `EmployeePlottingSchedule::STATUSES|SHIFTS|WEEKDAYS|DEFAULT_*`, `PayrollEmployeeSalary::RATE_TYPES|SCHEDULES|LOAN_PREFIXES`, `Holiday::TYPES`.
- **Payroll specifics:**
  - **Payroll-group boundary:** `App\Services\Payroll\PayrollGroupAccessService` (`allowed()`, `allows($group)`, `options()`) is the one place that reads `session('payroll_allowed_groups')`. `PayrollPolicy` checks the payroll's `garage_group` on view / update (recompute) / finalize / delete / export; the payroll list and the group dropdowns only show allowed groups; `GeneratePayrollRequest` only accepts allowed groups; the benefit settlement checks it too. Any new payroll-scoped action must use the policy or this service.
  - Services: `PayrollService` (list, generate, show data, item detail, recompute, delete, exports), `PayrollFinalizationService` (readiness checks throw `ValidationException` key `payroll`, then reconcile + finalize + post Benefits Records in one transaction), `BenefitSettlementService`, `AttendanceAdjustmentService` (CRUD / approve / reject; business refusals throw `ValidationException` so Laravel redirects back with errors), `OffsetCreditService` (offset rules + the live `report()` JSON), `OvertimeCheckService`, `AttendanceSummaryReportService` (Summary page, export, rebuild), `PayrollAuditLogService`, `BenefitRecordsService` (now reads through `BenefitRecordRepository`).
  - Repositories: `Payroll\PayrollRepository` (payrolls and items), `AttendanceAdjustmentRepository`, `AttendanceSummaryRepository` (Summary stats are **one aggregate query**; aliases are prefixed `stat_` because words like `leave` are reserved in MySQL), `PayrollAuditLogRepository`, `BenefitSettlementRepository`, `BenefitRecordRepository`. `PlottingScheduleRepository::scheduleOn($id, $date)` = dated row, else the permanent one.
  - **Wiping payroll test data:** `php artisan payroll:reset-data [--adjustments] [--summaries] [--dry-run]` (`Services\Payroll\PayrollDataResetService` + `PayrollDataResetRepository`, query builder so no audit rows; one transaction; asks to type `DELETE`). Removes payrolls (finalized too), items, Benefits Records, settlements, payment logs (= loan balances restored), report logs and payroll/benefits Transaction Logs, and releases adjustments' `paid_payroll_*`. Keeps settings, rules, Employee Rates, schedules, holidays, employees. `benefit_contribution_records` RESTRICTS deleting payrolls, so delete it first. Test: `tests/Feature/Payroll/ResetPayrollDataTest.php`.
  - **Payroll-scoped settings:** see "Payroll Settings" below before changing any rate, multiplier or contribution.
  - **Not converted yet (on purpose):** the calculation engines `PayrollComputationService`, `DailyAttendanceSummaryService`, `MonthlyGovernmentReconciliationService`, `BenefitContributionPostingService`, `PayrollPayslipService`, `BiometricsProofService` still query Eloquent inside the pay math. Move their reads to repositories only in a dedicated, test-first pass (payroll amounts must not change).
  - The item detail page is `Resources\Payroll\PayrollItemDetailResource` (the former presenter).
  - **SSS (Circular 2024-006)**: `SssContributionService` matches all 61 rows of the circular (`tests/Unit/Services/Payroll/SssCircular2024006Test.php` checks each row at both ends of its range; keep it green when rates change). The pay it is computed from follows Payroll Settings "Computed from" (`payroll.government_basis.*`) **everywhere**: cutoff draft (`PayrollComputationService::resolveGovernmentContributionBasis`), month-end reconciliation / Benefits Records (`MonthlyGovernmentContributionService::basis`) and the Employee Rates preview (`PayrollDeductionService::previewBasis`, TS `contributionBasis` in `lib/salary-preview.ts`). "Actual gross" in the preview = monthly basic + allowance + SIM load (full attendance).
  - The 26-10 cutoff (legacy `second`) only knows half the month, so `monthlyCycleGovernmentBasis` estimates the month as (gross − this cutoff's allowance) × 2 + monthly allowances (`estimated: true` in meta). Finalizing the 11-25 cutoff reconciles to the exact table amount on the real monthly gross minus what 26-10 already deducted.
- **Payroll Settings** (sidebar Payroll → Payroll Settings, permissions `payroll-settings.view` / `payroll-settings.manage`, routes `payroll-settings.*`):
  - **No payroll rate is hard-coded any more.** Every number lives in `App\Support\Payroll\PayrollSettingCatalog` (sections → fields; each field writes one or more `config('payroll.*')` / `config('sss.*')` keys; transforms `value|minutes|map|sum`). `config/payroll.php` + `config/sss.php` are only the **starting values** (the catalog reads the files, not runtime config). A new rate = add the config key + a catalog field, and read it with `config()` in the engine.
  - Values are stored per **effective date** in `payroll_setting_versions` (model `PayrollSettingVersion`, migration seeds "Starting rules" from 2000-01-01; at least one version must stay). `Services\Payroll\PayrollSettingsService` (singleton) applies today's version on every request (`AppServiceProvider` booted hook, ignores a missing table) and `using($date, fn)` / `usingVersion($id, fn)` / `usingValues($values, $label, fn)` apply another one and restore the config afterwards. `PayrollComputationService::generate()` / `recomputeItem()` run inside `using(period_start)`; payroll meta `settings` (+ values) and item meta `settings_version` record what was used. Versions are cached (`forget()` after writes).
  - **Custom rules** (`payroll_rules`, model `PayrollRule`, soft deletes): earnings (added to gross, so they count in the SSS basis) and deductions (taken from net after government). Methods fixed / percent of a money value / amount per a count value / formula. Scope: cutoff, pay type, payroll groups, employees, dates, active, order. `PayrollRuleService::run()` runs them in order and exposes each result to later formulas by its code; item meta `custom_rules.earnings|deductions` (shown on the item page and payslip by name). Values available to formulas: `PayrollRule::VARIABLES` (`stage: deduction` = deduction rules only).
  - Formulas use `App\Support\Payroll\PayrollFormula` (own safe parser, no eval, no package): numbers, names, `+ - * / ^`, postfix `%`, comparisons (single `=` allowed), `AND OR NOT`, `IF MIN MAX ROUND FLOOR CEIL ABS`; case-insensitive, lazy `IF`, division by zero = 0. A runtime formula error gives 0 + an `error` on the line, never a failed payroll.
  - **Test computation** (`PayrollSimulationService`, `POST payroll-settings.test.run`, JSON): `PayrollComputationService::simulateItems()` writes a throw-away payroll inside `DB::beginTransaction()` + `rollBack()`; compare with unsaved values (`compare_values`), another version (`compare_version_id`) or a draft rule (`compare_rule`). Contribution calculator: `POST payroll-settings.test.contributions`. React: `components/payroll/settings/*` (`TestPanel`, `ContributionCalculator`, `SettingInput`, `SettingsTabs`, `types.ts`), pages `pages/payroll/settings/{index,form,rules,rule-form,test}.tsx`.
  - Dialogs opened from a page that has a `<form>` must render **outside** the form element: a submit inside a portal still bubbles to the parent form in React.
- **Maintenance / Inventory / Products specifics:**
  - **All stock changes go through `Maintenance\StockRepository`** (`lockRow`, `lockOrCreateRow`, `setQuantity`, `add`, `remove`, `syncProductTotal` = `products.stock_qty` = sum of the stockroom rows, `recordMovement`, `quantities`). Lock order inside a transaction: stockroom (`LocationRepository::findForUpdate`/`findActiveForUpdate`) → product (`ProductRepository::findForUpdate`) → stock row. Never write a fifth copy of the stock sync.
  - One service per page, create and rollback together: `PartsOutService`, `ReceivingService` (proof photo on the private `local` disk, `proofPath()`), `StockTransferService` (the dead `App\Services\StockTransferService` and the separate Creation/Rollback services are gone), `ProductCatalogService` (items + the product search used by every inventory form), `CategoryService`, `MaintenanceStockService` (stock dashboard, `LOCATION_FILTERS`), `JobOrderMaintenanceService` (`filters(Request)`, list, status cards, create/status/number, `SHOW_RELATIONS`), `JobOrderMaintenanceExportService` (CSV + HTML-table .xls, list and single), `VehicleHistoryService`, `BusListService`.
  - `InventoryDirectoryService` is the shared helper of the inventory pages: `userLocationId()` (a user with `users.location_id` only sees that stockroom), `assertLocationAccess()`, `excludeIds()` (`1,2,3` or `[]` form), `locationOptions()`, `vehicleOptions()`.
  - Stock refusals (not enough stock, already rolled back, inactive stockroom) throw `ValidationException` (keys `product_id`, `rollback_reason`, `rollback_qty`, `from_location_id`); controllers no longer catch `Throwable` and show raw exception text.
  - Repositories: `Maintenance\{Product, Category, Location, Stock, PartsOut, Receiving, StockTransfer, JobOrderMaintenance}Repository`; `Fleet\BusRepository` (the `buses` table used by job orders) next to `Fleet\BusDetailRepository` (the `bus_details` vehicles; `paginate($search, 'latest'|'plate')`, `garages`, `allByPlate`).
  - `AllBusController` → `Maintenance\BusListController` (+ `BusDetailRequest`), `BusDetailController` → `Maintenance\VehicleHistoryController`. Bootstrap `badgeClass()`/`icon()` on `JobOrderStatus`/`JobOrderRepairType` and the model accessors are deleted.
- **Developer role = system role** (`User::DEVELOPER_ROLE`):
  - It holds every permission, old and new. At runtime, `Gate::before` allows a Developer everything; spatie's `permission:` middleware goes through the Gate.
  - The stored role is also kept at 100%: `Permission::created` (AppServiceProvider) adds each new permission to it, `RoleService::syncRoutePermissions` refills it (`RoleRepository::grantAllToRole`), and migration `2026_10_06_100000_grant_all_permissions_to_developer_role` filled the existing ones.
  - It is hidden from Roles (`RoleService::indexData()` never lists it) and cannot be edited, deleted or imitated: `RoleRequest` reserves the name in any letter case.
  - Developer accounts are **never listed** on the Users page, not even for a Developer (`UserManagementService::paginate` → `UserRepository::paginate(..., false)`; matched by spatie role and by the `role` column).
  - The Users page never offers or assigns it (`User::availableAssignableRoles`). A Developer account keeps it (the role field is read-only), and taking it away is refused (`UserManagementService::assertRoleAssignmentAllowed` → `ValidationException` key `role`).
  - The only way to make a Developer: `php artisan security:make-developer {username}` (asks for confirmation).
- **Security specifics:** `Security\UserManagementService` (temporary password = `Str::password(12)` letters+digits, shown once; a user cannot deactivate their own account → `ValidationException` key `account_status`; Developer-only guards), `Security\RoleService` (permission groups, risk levels, route permission sync; role delete refusals become an `error` flash), `RoleRepository`, extended `UserRepository`. `RoleRequest::authorize()` = Developer only. User requests live in `Requests\Security`.
- **General specifics:** `Controllers\General\DashboardController` (home greeting + hidden IT dashboard, `Services\General\DashboardService`), `HrDashboardController` and `HrDataController` (hidden HR dashboard and All Data report) on `Services\General\HrReportService` + read-only `Repositories\General\HrReportRepository` (leave reports take a `LeaveKind`).
- **Fleet specifics:**
  - Odometer Monitoring: `Controllers\Fleet\OdometerMonitoringController`, `Services\Fleet\OdometerService` (km run, km/L, change-oil countdown, manual reading between its neighbours; `update()` skips the reading itself when finding neighbours), `OdometerExportService`, `OdometerPeriod` (day / range / month from the request; bad dates fall back to defaults), `OdometerSubmissionRepository`, `DieselStockRepository`. The API `lastOdometer` uses `OdometerService::latestReading`.
  - Bus Analytics / For Sale Units: `BusService` (counts, per garage / company summaries, for-sale summary, create / edit), `FleetFolderDashboardService` (folder tabs; garages and companies grouped **case-insensitively**), `ForSaleUnitService`, `BusForSaleSyncService`, repositories `Fleet\BusRepository` (all bus SQL incl. the for-sale `EXISTS` rules) and `Fleet\BusForSaleRecordRepository`. Shared value rules: `App\Support\Fleet\FleetValue` (`upper`, `text`, `breakdownDays`).
- **Biometrics specifics:** `Controllers\Biometrics\BiometricsSyncController` (mirasol-logs.*, `Services\Biometrics\BiometricsAttendanceService`: people search, per-day schedule vs punches, late / undertime / note; cutoff keys `26_10` / `11_25` mapped onto `PayrollPeriodService`) and `ManualBiometricsController` (`ManualBiometricsService`, `StoreManualBiometricsRequest`). Punches: `Biometrics\BiometricsLogRepository`; schedules: `PlottingScheduleRepository::people / forPeople`. Cutoff math always comes from `PayrollPeriodService` (no private copies).
- Collections: a repository that maps rows to arrays must call `->toBase()` first; `merge()` on an Eloquent collection of arrays fails with "getKey() on array" (was a 500 on Biometrics Sync).
- After deleting or moving classes run `composer dump-autoload` (a stale class map still lists removed files).
- **Exports** (`app/Exports`) take their rows in the constructor from a service (`new JobOrdersExport($service->excelExportRows())`). They never query.
- Other pages that read the module (e.g. `DashboardController::itindex`) switch to the module's service too.
- **Delete what the move replaces**: old controllers, old `*DirectoryService` classes, duplicated namespaces (`IT_Department`, `ITDepartment` → `IT`), dead routes (no-op actions, unused `Route::resource`), unused props and images. Check usages with grep first.
- Tests move to `tests/Feature/<Module>/`. Add a `<Module>LayeredStructureTest` that asserts every interface resolves to its class, that removed routes are gone, and that every write action not already covered works (see `tests/Feature/IT/ItLayeredStructureTest.php`, `tests/Feature/HR/HrLayeredStructureTest.php`). Any test that uploads or deletes files calls `Storage::fake('local')` in `setUp()`, and Excel downloads use `Excel::fake()`, so tests never touch real storage.

## Messages (toasts)

- Every message is a top-right toast that hides after 5 seconds. Never render a message as an inline `<Alert>`.
- React: `resources/js/react/lib/notify.ts` (`installGlobalToasts`, called in `app.tsx`) turns these into toasts automatically:
  - flash `success` / `error` / `warning` / `info`, plus laracasts `flash()` messages;
  - validation errors, even while the fields keep their own error text;
  - HTTP error statuses on Inertia visits;
  - network failures.
  For a toast from your own code, use `notify(level, message)` or `notifyHttpError(status)`. The single `<Toaster>` lives in `app.tsx`, not in the layout.
- `<Alert>` stays for permanent notices only: rules, payroll formula warnings, "rolled back" records, the one-time temporary password.
- HTTP status wording lives in `app/Support/HttpStatusMessage.php` and `resources/js/react/lib/http-status.ts` (keep them identical). Covered: 400, 401, 402, 403, 404, 405, 409, 419, 422, 429, 500, 503.
- Error pages are React: `bootstrap/app.php` (`withExceptions` → `respond`) renders `pages/errors/show.tsx` for failed **browser visits** (status, title, message from `HttpStatusMessage`, a deliberate 403/409/429 abort message, and a Go back / Dashboard / Sign in button). It also toasts. Inertia visits and JSON calls keep the raw status (toast only). Debug-mode 5xx keep Laravel's debug page; if React cannot render, Laravel's plain page is the fallback. There are no Blade error views.
- `HandleInertiaRequests` converts Blade HTML answers to Inertia visits into a 409 full-page load only for status < 400. Errors reach the client as `httpException` and become toasts.

## No Falcon

- The Falcon admin template is fully removed: Bootstrap theme CSS/JS, `public/vendors`, `public/src`, demo images, `layouts.app`, the old Blade pages, `resources/css/app.css`, `resources/js/app.js`, and the Bootstrap / Choices / Flatpickr / Swiper / Chart.js npm packages. Never add them back.
- The only Blade files left (they cannot be React) are:
  - `app-react.blade.php`, the shell every React page loads into;
  - the three dompdf PDF templates (`payroll/payrolls/payslip-pdf`, `hr_department/employees/modals/_employee_201_pdf`, `it_department/export/pdf`);
  - the two mail templates (`emails/*`).
  Login, lock screen, error pages and print pages are all React. Never add a Blade page again.
- The old Purchase Request, Accounting PO approval, PO Receiving, old CCTV page, analytics/CRM demo dashboards and Stock page were deleted on request. Their database tables and models are kept.

## Sidebar

- Defined in `app/Support/Navigation/MainNavigation.php` (groups, links, permissions, active patterns). React renders it in `components/nav-main.tsx`. Icons are mapped in `components/nav-icon.tsx` (add new lucide icons there).
- Current groups: General, Fleet, IT Support, Human Resources, Biometrics, Scheduling & Rates, Payroll, Maintenance, Inventory, Products, Security.
- Dashboard is a single link (not expandable). Odometer Monitoring and Bus Analytics are under Fleet. Maintenance Stock is under Inventory. The All Data / HR / IT dashboards are hidden from the menu (their routes still exist).
- Compact style: 30px items with a 2px gap and small uppercase group labels.

## Login page, lock screen and passwords

- **Login** is React: `pages/auth/login.tsx` (`AuthController::showLogin`). **Its look is fixed and approved by the user; never restyle it.** It uses `pages/auth/login.css`, the exact stylesheet of the former Blade login (`lx-` classes, sized in `em` off `.lx-page`, font-size `clamp(14px, 0.78vw, 20px)`), with the same markup. Do not replace it with Tailwind. Bus photo `groupes.jpg` with a navy wash, hero left, card 32em centred in the right half. Same fields and endpoint (`username`, `password`, `remember`, `cf-turnstile-response` → `login.post`). The lockout countdown comes from the `seconds` prop; `old` refills username/remember; errors and flashes are toasts.
  - Cloudflare Turnstile: `components/auth/turnstile.tsx` (explicit render, script loaded once, `reset()` after every failed attempt because a token works once). Site key from `config('services.turnstile.site_key')`.
  - Kept element ids: `loginForm`, `username`, `password`, `remember`, `togglePassword`, `loginBtn`, `loginBtnText`, `turnstileStatus`.
- **Lock screen** is React: `pages/auth/lock.tsx` (blurred fake app shell + lock icon + password, "Not you? Sign out").
  - The server enforces it: `App\Http\Middleware\ForceLockscreen`. While the session is locked (`unlocked` = false) every page request renders the lock page **in place** (no page data is sent) and stores the URL in `lock_intended`. Writes redirect to `/lockscreen`, and JSON gets 423.
  - `POST /lock` (`lockscreen.lock`) locks from the user menu ("Lock screen") and from the idle timer (`lib/use-idle-lock.ts`, 15 minutes without activity in any tab, used in `AppLayout`).
  - `POST /unlock` checks the password (5 tries / 60 s) and returns to `lock_intended`, same site only (`AuthController::sameSite`), else the dashboard.
- **Password rule** (change password): at least **7 characters with a number and a special character** (`ChangePasswordRequest`: `Password::min(7)->numbers()->symbols()`). `pages/auth/change-password.tsx` shows a live checklist, a show/hide eye and a match hint.

## Safety rules (must follow)

- **Never run tests or browser flows that write data against the dev DB `esgroupsystemv2`.** It is the user's working data, and the user loads real data into it between turns.
- Tests: run `php artisan config:clear` first (a cached config once pointed tests at the dev DB and wiped it). `tests/TestCase.php` refuses any DB not ending in `_test`. Keep that guard.
- Browser checks use the scratch DB `esgroupsystemv2_ui` only. Serve it from `public/`:
  `DB_DATABASE=esgroupsystemv2_ui CACHE_PREFIX=jell_group_ui_ php -S 127.0.0.1:8124 ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php`
  (`php artisan serve` ignores `DB_DATABASE`; the separate `CACHE_PREFIX` stops the dev DB's permission cache from causing 403s.) Before any write, prove the server is on the scratch DB.
  - Login needs Turnstile: add Cloudflare's always-pass test keys to the serve command only (`TURNSTILE_SITE_KEY=1x00000000000000000000AA TURNSTILE_SECRET_KEY=1x0000000000000000000000000000000AA`). Never put them in `.env`.
  - If the scratch DB is missing: `CREATE DATABASE esgroupsystemv2_ui`, export `DB_DATABASE=esgroupsystemv2_ui`, check `php artisan tinker --execute="echo DB::connection()->getDatabaseName();"` prints it, then `php artisan migrate --seed --force`. Create a Developer test user and sample rows with a tinker script that first refuses to run on any other DB.
  - Playwright is not a project dependency. Install it in the scratchpad (`npm i --no-save playwright`) and launch `chromium` with `channel: 'msedge'`.
- If `vendor/` or `node_modules/` is missing or stale (e.g. `Class "Inertia\Middleware" not found`, `'vite' is not recognized`), run `composer install` and `npm ci`. Both install from the lockfiles, so no versions change.
- Read-only queries against the dev DB (e.g. `SHOW COLUMNS`) are fine.
- The Developer role gets every permission via `Gate::before`. The Admin role deliberately does not.

## Checks before reporting done

1. `vendor/bin/pint <changed php files>`: list the files. Running it on a whole folder (e.g. `app/Models`) reformats untouched files; revert those with `git checkout -- <file>`. `vendor/bin/pint --test app routes tests` must be clean.
2. `php artisan config:clear && php artisan test`: all green.
3. `npx tsc --noEmit -p .` and `npm run build`: clean.
4. Browser check on the scratch server (Playwright + Edge, headless) at desktop and phone widths, with no console errors.

## Known gotchas

- **Carbon 3's `diffInMinutes()` (and the other `diffInX()` methods) default to *signed*, not absolute** (a breaking change from Carbon 2). `$a->diffInMinutes($b)` is negative when `$b` is before `$a`. Any "find the closest/smallest difference" comparison must wrap it in `abs()`, or a value on the wrong side silently "wins" by looking like the smallest (most negative) number. This caused a real bug in `EmployeePlottingSchedule::matchShiftOption` (Flexible Shift's nearest-option matching) and would have broken the overnight-OT overlap fix (`OvertimeCheckService`) the same way had it not been caught by `tests/Feature/Scheduling/FlexibleShiftModesTest.php` and `tests/Unit/Services/Payroll/OvertimeOverlapTest.php`.
- The user's long-running `npm run dev` can serve a stale `react.css` that lacks newly used arbitrary classes (e.g. `max-h-[90vh]`). The production build is correct. For browser checks, route `react.css` to the built CSS file in `public/build/assets/`, and tell the user to restart `npm run dev`.
- The shadcn CLI here writes `import { cn } from "cn"` and installs a bogus `cn` package. After any `shadcn add`: fix imports to `@/lib/utils`, then `npm uninstall cn next-themes`. Prefer copying the registry file over overwriting existing components.
- The `users` table has **no `name` column** (it is `full_name`, plus `username`). `User::getNameAttribute()` returns `full_name`, else `username`, so `$user->name` is safe; prefer `full_name` in new code. Before this, every `->name` on a user relation was null ("Encoded by: N/A", "Created by: System").
- Lazy loading is disabled outside production (`Model::preventLazyLoading()`), so eager-load relations.
- `Collection::groupBy` keys that look numeric become ints. Under `strict_types`, type such map callbacks as `int|string`.
- Shell: heredocs and `sed` with backslashes or `$` often break. Prefer the Edit/Write tools, or a small `node -e` script, for code edits.
