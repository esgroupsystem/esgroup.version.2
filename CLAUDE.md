# CLAUDE.md

Jell Group ERP (ES Group): HR, biometrics, payroll, IT, fleet, maintenance and inventory.
Branch `feature/react-shadcn` holds the ongoing move from Blade to React + shadcn/ui.

## Stack

- Laravel 12, PHP 8.2, MariaDB (XAMPP on Windows, git-bash shell), spatie/laravel-permission.
- Inertia v3 + React 19 + TypeScript, Tailwind v4, shadcn/ui (new-york style), lucide-react icons, recharts (via the shadcn `chart` component), Vite 7.
- React code lives in `resources/js/react/` (`pages/`, `components/`, `components/ui/`, `lib/`, `layouts/`, `types/`). The only Vite CSS entry is `resources/css/react.css`. `public/assets/img` holds just the few images in use: favicons, the bus photo (login), the seat plan and `no-image-default`.

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
  - An animated top-down bus with seats 1-52 (1 = driver, 48-52 = the back row). The layout is the old `seat_arrangement.png`.
  - The animation starts when the map scrolls into view: the bus drives in, the roof lifts, then the seats pop in. It respects reduced motion, and there is a Replay button.
  - You can select several seats; the value is `"12, 13"`. `readOnly` shows the saved seats.
  - The server side is `App\Support\IT\SeatNumbers` (`normalize` + `rules`), used by the job order store and update requests.
- Domain components: `components/hr/*`, `components/inventory/*`, `components/maintenance/*`, `components/biometrics/*`.

### Design rules

- shadcn look: cards, outline badges with tone classes, tables with an empty-state row, dialogs for create/edit, `AlertDialog` confirmations for destructive actions.
- Every overlay (Dialog, AlertDialog, Sheet in `components/ui/`) has a blurred background: `bg-black/40 backdrop-blur-sm`. Keep this if a shadcn component is re-added.
- Modals fit their data: `components/modal/use-fit-width.ts` (used by `modal-stack.tsx`) measures each table's natural width and widens the modal just enough that no table scrolls sideways, capped at 96vw. Phones keep the normal full-width dialog.
  - While a modal is open it only grows, by at least 8px, to an even whole-pixel width. The modal body reserves its scrollbar space (`[scrollbar-gutter:stable]`). Letting it shrink back made modals wobble a few pixels every frame (width changes wrapping, wrapping changes the scrollbar, the scrollbar changes the width). Keep all three rules.
  - `size` is only the starting width. Use `'xl'` for pages with tables and let the auto-fit decide. `'full'` (always 96vw) is for special cases only.
  - Long-text cells: put a fixed-width `<div className="w-72 whitespace-normal">` inside the cell (a `max-w` on a `<td>` is ignored). Otherwise the text is measured unwrapped and pushes the modal to 96vw.
- Dialogs with long content use `max-h-[90vh] overflow-y-auto`. A sticky footer inside one uses `sticky -bottom-6 -mx-6 -mb-6`.
- Search boxes that are kept in local state must re-sync from the server filter (`useEffect(() => setSearch(filters.q), [filters.q])`).
- Numbers use `Intl.NumberFormat('en-US')`, money uses `peso()` from `lib/format.ts`, and chart axes use compact ticks.
- Layouts must work at phone width with no sideways scroll. Use `minmax(0, 1fr)` grid columns and `min(…, 100%)` widths.

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
- Read-only queries against the dev DB (e.g. `SHOW COLUMNS`) are fine.
- The Developer role gets every permission via `Gate::before`. The Admin role deliberately does not.

## Checks before reporting done

1. `vendor/bin/pint <changed php files>`
2. `php artisan config:clear && php artisan test`: all green.
3. `npx tsc --noEmit -p .` and `npm run build`: clean.
4. Browser check on the scratch server (Playwright + Edge, headless) at desktop and phone widths, with no console errors.

## Known gotchas

- The user's long-running `npm run dev` can serve a stale `react.css` that lacks newly used arbitrary classes (e.g. `max-h-[90vh]`). The production build is correct. For browser checks, route `react.css` to the built CSS file in `public/build/assets/`, and tell the user to restart `npm run dev`.
- The shadcn CLI here writes `import { cn } from "cn"` and installs a bogus `cn` package. After any `shadcn add`: fix imports to `@/lib/utils`, then `npm uninstall cn next-themes`. Prefer copying the registry file over overwriting existing components.
- Lazy loading is disabled outside production (`Model::preventLazyLoading()`), so eager-load relations.
- `Collection::groupBy` keys that look numeric become ints. Under `strict_types`, type such map callbacks as `int|string`.
- Shell: heredocs and `sed` with backslashes or `$` often break. Prefer the Edit/Write tools, or a small `node -e` script, for code edits.
