# Grovixo IIMS — Complete Project Details

**Grovixo Invoice & Inventory Management System (IIMS) v2.0**
A full-cycle Billing, Inventory, and Business Management system built on a
custom PHP MVC-style architecture — no framework, no build step, no SPA
front-end. Every module is server-rendered PHP + Bootstrap 5 + jQuery/AJAX.

This document is a single-file reference to **everything** in the codebase:
tech stack, folder-by-folder layout, routing, every database table, every
API endpoint and action, every controller/method, front-end assets, printing
subsystem, security model, and deployment instructions.

---

## Table of Contents

1. [Tech Stack](#1-tech-stack)
2. [Folder / File Structure](#2-folder--file-structure)
3. [Request Lifecycle & Routing](#3-request-lifecycle--routing)
4. [Core Framework Classes](#4-core-framework-classes)
5. [Database Schema (all 37 tables)](#5-database-schema-all-37-tables)
6. [Modules — Controllers, Views, Features](#6-modules--controllers-views-features)
7. [API Reference (every endpoint & action)](#7-api-reference-every-endpoint--action)
8. [Standalone Print Endpoints](#8-standalone-print-endpoints)
9. [Thermal / Receipt Printing Subsystem](#9-thermal--receipt-printing-subsystem)
10. [Front-End JS Assets](#10-front-end-js-assets)
11. [Roles & Permissions](#11-roles--permissions)
12. [Security Model](#12-security-model)
13. [Configuration & Environment](#13-configuration--environment)
14. [Installation & Deployment](#14-installation--deployment)
15. [Default Credentials](#15-default-credentials)
16. [Global UI Chrome (Sidebar, Header, Footer, Bottom Nav)](#16-global-ui-chrome-sidebar-header-footer-bottom-nav)
17. [Screen-by-Screen UI Reference (Field-Level Detail)](#17-screen-by-screen-ui-reference-field-level-detail)
18. [Known Gaps / Migration Notes](#18-known-gaps--migration-notes)

---

## 1. Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.0+ |
| Routing | [`nikic/fast-route`](https://github.com/nikic/FastRoute) via a custom front controller (`index.php`) |
| Autoloading | Composer PSR-4 (`App\` → `application/`) |
| Env config | `vlucas/phpdotenv` (`.env` file, `Dotenv::createImmutable`) |
| Database | MySQL / MariaDB (primary) via PDO; SQLite fallback (`database/database.sqlite`) |
| Frontend UI | Bootstrap 5, hand-written CSS (`assets/css/style.css`, `templates.css`) |
| Frontend logic | jQuery, vanilla JS, `fetch`/AJAX (no page reloads for data ops) |
| Grids | DataTables 1.13.4 (server-driven listings, mobile card collapse) |
| Dropdowns | Select2 |
| Charts | Chart.js (dashboard analytics) |
| Alerts/modals | SweetAlert2 |
| Receipt rendering | html2canvas (DOM → image) + raw ESC/POS byte encoding |
| Dependency manager | Composer (`composer.json` / `composer.lock`) |
| No build step | No Node/webpack/npm — plain `<script>`/`<link>` tags, no transpilation |

Composer dependencies (`composer.json`): `nikic/fast-route ^1.3`,
`vlucas/phpdotenv ^5.6` (pulls in `graham-campbell/result-type`,
`phpoption/phpoption`, `symfony/polyfill-ctype`, `symfony/polyfill-mbstring`,
`symfony/polyfill-php80`).

---

## 2. Folder / File Structure

```text
/invoices
├── index.php                    Front controller — FastRoute dispatcher
├── server.php                   Router for `php -S` built-in dev server (mirrors .htaccess)
├── .htaccess                    Apache mod_rewrite: clean URLs, .php hiding, front-ctrl fallback
├── login.php / logout.php       Standalone auth pages (not routed through index.php)
├── composer.json / composer.lock
├── schema.txt                   Flat text dump of every table + column (reference)
├── dummy_data_hostinger.sql     Full schema + seed/sample data (used for fresh installs)
├── cookies.txt                  Stray curl cookie-jar file (dev artifact, not app code)
│
├── config/
│   ├── config.php               Session bootstrap, timezone, path constants, CSRF secret init
│   └── database.php             DB driver/env selection (MySQL creds, SQLite fallback path)
│
├── application/                 PSR-4 root for the `App\` namespace
│   ├── Controllers/             One controller per module — renders views only, no business logic
│   ├── Models/                  Database.php, Auth.php, Helpers.php (framework-level only)
│   └── views/                   PHP templates: header/footer/bottom_nav + per-module list/form/view
│
├── api/                         One JSON endpoint per module — ALL business logic & DB writes live here
│
├── <module>/                    Thin routing stubs per module (billing/, products/, customers/, etc.)
│   ├── index.php, form.php, view.php   Each just delegates to the matching Controller method
│
├── assets/
│   ├── css/style.css            Global styles, responsive rules, skeleton loaders, themes
│   ├── css/templates.css        Print/receipt template styling
│   ├── js/billing.js            POS/billing screen logic (790 lines)
│   ├── js/dashboard.js          Dashboard charts/widgets (338 lines)
│   ├── js/thermal-printer.js    USB/Bluetooth/LAN ESC/POS printing engine (303 lines)
│   ├── js/bulk-actions.js       Shared bulk select/delete/status-change for list pages (173 lines)
│   └── images/favicon.png
│
├── database/
│   ├── printers_schema.sql      Standalone schema for the `printers` table
│   └── feedback_schema.sql      Standalone schema for the `feedback` table
│
├── docs/
│   └── thermal-printing.md      Deep technical doc for the printing subsystem
│
├── uploads/                     Company logos, product images (user-uploaded)
├── backups/                     Generated .sql database backup dumps
├── logs/                        Application log directory (created at runtime)
│
├── barcode_print.php            Standalone barcode label print view
├── invoice_print.php            A4/PDF invoice print view
├── invoice_thermal.php          Thermal receipt print/print-transport view (510 lines)
├── purchase_print.php           Purchase voucher print view
├── quotation_print.php          Quotation print view
├── challan_print.php            Delivery challan print view
│
├── README.md                    Quick-start feature summary + install options
├── DOCUMENTATION.md             Full narrative feature reference (module-by-module)
└── projectdetails.md            THIS FILE — exhaustive technical + feature reference
```

Each business module (`billing`, `products`, `purchases`, `customers`,
`suppliers`, `expenses`, `returns`, `quotations`, `challans`, `users`,
`roles`, `settings`, `reports`) follows the same 3-file pattern at the
project root (e.g. `products/index.php`, `products/form.php`,
`products/view.php`) — thin stubs that hand off to
`application/Controllers/<Module>Controller.php`.

---

## 3. Request Lifecycle & Routing

Two parallel routing paths exist depending on how the page was reached:

1. **Front controller (`index.php` + FastRoute)** — the canonical path for
   all module `index`/`form`/`view`/`day_end` GET routes. Builds a
   `Database` + `Auth` instance, instantiates the target Controller, and
   calls the mapped method with any route/query-string `id`.
   - If FastRoute doesn't match, `index.php` falls back to looking for a
     literal file at that path (`__DIR__.$uri`, `$uri.'.php'`, or
     `$uri.'/index.php'`) — a **migration shim** for endpoints not yet
     registered as explicit routes.
   - A global exception handler wraps every request: logs the real error,
     returns a friendly HTML error page for browser requests or
     `{"status":false,"message":"Internal Server Error"}` for JSON/AJAX
     requests, and always sends HTTP 500.

2. **`server.php`** (only used with `php -S localhost:8000 server.php`,
   PHP's built-in dev server) — mirrors `.htaccess`'s Apache rewrite rules
   so local dev behaves identically to production:
   - Redirects visible `.php` URLs to their clean/extensionless form
     (skips `/api/*`, which must stay callable at its literal path).
   - Serves real static files (css/js/images/uploads) as-is.
   - Transparently resolves a clean URL back to its real `.php` file
     (`login` → `login.php`, `billing/form` → `billing/form.php`).
   - Anything else falls through to `index.php`.

3. **`.htaccess`** (production/Apache) — same three rules expressed as
   `mod_rewrite` directives: strip `.php` from the visible URL (301,
   except `/api/`), resolve clean URLs back to the real file, then fall
   back to `index.php` for virtual/front-controller routes.

**Explicit FastRoute routes registered in `index.php`:**

| Method | Route | Controller → Method |
|---|---|---|
| GET | `/`, `/index` | `DashboardController::index` |
| GET | `/products/index` | `ProductController::index` |
| GET | `/products/form` | `ProductController::form` |
| GET | `/products/view` | `ProductController::view` |
| GET | `/purchases/index` | `PurchaseController::index` |
| GET | `/purchases/form` | `PurchaseController::form` |
| GET | `/purchases/view` | `PurchaseController::view` |
| GET | `/billing/index` | `BillingController::index` |
| GET | `/billing/form` | `BillingController::form` |
| GET | `/billing/view` | `BillingController::view` |
| GET | `/billing/day_end` | `BillingController::dayEnd` |
| GET | `/customers/index` | `CustomerController::index` |
| GET | `/customers/form` | `CustomerController::form` |
| GET | `/customers/view` | `CustomerController::view` |
| GET | `/suppliers/index` | `SupplierController::index` |
| GET | `/suppliers/form` | `SupplierController::form` |
| GET | `/suppliers/view` | `SupplierController::view` |
| GET | `/expenses/index` | `ExpenseController::index` |
| GET | `/expenses/form` | `ExpenseController::form` |
| GET | `/expenses/view` | `ExpenseController::view` |
| GET | `/returns/index` | `ReturnController::index` |
| GET | `/returns/form` | `ReturnController::form` |
| GET | `/returns/view` | `ReturnController::view` |
| GET | `/quotations/index` | `QuotationController::index` |
| GET | `/quotations/form` | `QuotationController::form` |
| GET | `/quotations/view` | `QuotationController::view` |
| GET | `/challans/index` | `ChallanController::index` |
| GET | `/challans/form` | `ChallanController::form` |
| GET | `/challans/view` | `ChallanController::view` |
| GET | `/reports/index` | `ReportsController::index` |
| GET | `/users/index` | `UserController::index` |
| GET | `/users/form` | `UserController::form` |
| GET | `/users/view` | `UserController::view` |
| GET | `/roles/index` | `RoleController::index` |
| GET | `/roles/form` | `RoleController::form` |
| GET | `/settings/index` | `SettingsController::index` |

Note: **all data mutations** (create/update/delete/list/search) go through
`api/<module>.php?action=<verb>` via AJAX/fetch — the routes above only
render the page shell; they never touch the database directly beyond what
the Controller needs to bootstrap dropdown/reference data for a form.

---

## 4. Core Framework Classes

### `application/Models/Database.php` — PDO wrapper
- `__construct()` — connects via PDO; driver selected by `DB_DRIVER`
  (`mysql` default, `sqlite` fallback), reads credentials from
  `config/database.php` constants.
- `getConnection(): PDO` — raw PDO handle escape hatch.
- `isDatabaseEmpty(): bool` (private) — detects a fresh DB with no tables.
- `initializeDatabase(): bool` — auto-bootstraps schema from a bundled SQL
  file when the database is empty (self-installing on first run).
- `executeMultiQuery(string $sql): void` (private) — splits/runs a
  multi-statement SQL file for the above.
- `query(string $sql, array $params = []): PDOStatement` — prepared
  statement helper (all queries in the app go through this or `insert()`).
- `insert(string $sql, array $params = []): string` — prepared insert,
  returns `lastInsertId()`.
- `transaction(callable $callback)` — wraps a closure in
  `beginTransaction()/commit()/rollBack()`, used by every multi-table write
  (invoice creation, purchases, returns, etc.).

### `application/Models/Auth.php` — session auth & RBAC
- `login(string $email, string $password): bool` — verifies credentials
  with `password_verify()`, loads the user's role/permissions into the
  session, records a `login_logs` row, updates `users.last_login`.
- `logout(): void` — records logout time, destroys the session.
- `check(): bool` — is there a valid logged-in session.
- `user(): ?array` — current session user record.
- `hasPermission(string $permissionName): bool` — checks the current
  user's role against the `role_permissions` set (or a super-admin bypass).
- `requirePermission(string $permissionName): void` — hard-gates a
  page/API action; redirects (HTML) or returns a JSON 403-style error
  (AJAX) if the permission is missing.
- `isAjaxRequest(): bool` (private) — used by the above to pick the right
  failure response.

### `application/Models/Helpers.php` — shared utilities (all `static`)
- `sanitize($data)` — recursive input trimming/escaping helper.
- `csrfField(): string` — renders a hidden CSRF `<input>` for HTML forms.
- `getCsrfToken(): string` — returns the raw token for JS/AJAX headers.
- `verifyCsrf(): bool` — validates a submitted token against
  `$_SESSION['app_csrf_secret']`; called at the top of every state-changing
  API action.
- `jsonResponse(bool $status, string $message, array $data = []): void` —
  the single standard `{status, message, data}` JSON envelope used by
  every API endpoint, then `exit`s.
- `formatCurrency($amount): string` / `formatDate($date, $format='d-M-Y')` —
  display formatting helpers used across views/prints.
- `assetVersion(string $relativePath): int` — cache-busting helper
  (returns file mtime) used in `<script src="...?v=...">` tags.
- `logActivity(Database $db, string $module, string $action, ?int $recordId=null): bool` —
  writes a row to `activity_logs` (user, module, action, IP, device,
  timestamp) — called after every significant create/update/delete.

---

## 5. Database Schema (all 37 tables)

Full column-level dump lives in `schema.txt` (and a runnable version with
seed data in `dummy_data_hostinger.sql`). Every table has the same audit
columns unless noted: `status`, `created_at`, `updated_at`, `created_by`,
`deleted_at` (soft-delete pattern — nothing is hard-deleted).

| Table | Purpose |
|---|---|
| `users` | Staff accounts — name, email, password hash, role, mobile, profile image, last_login |
| `roles` | Named custom roles (e.g. Admin, Cashier) |
| `permissions` | Master list of togglable permissions (see §11) |
| `role_permissions` | Many-to-many: which permissions each role has |
| `login_logs` | Every login attempt/session — IP, device, browser, login/logout time |
| `activity_logs` | Audit trail of key actions per user/module/record |
| `company_settings` | Single-row config: branding, GST, numbering prefixes, loyalty, thermal/POS template, bank details |
| `categories` / `brands` | Product master data |
| `units` / `unit_conversions` | Units of measure + conversion factors (e.g. 1 Box = 12 Pcs) |
| `products` | Full product master — pricing, GST%, stock, HSN, barcode, batch-tracking flag, image |
| `product_batches` | Batch/lot tracking (mfg/expiry date, per-batch qty & pricing) |
| `product_images` | Additional product images (gallery) |
| `stock_transactions` | Ledger of every stock in/out movement with before/after quantities |
| `suppliers` | Supplier master + opening balance |
| `supplier_payments` | Payments made to suppliers |
| `purchases` / `purchase_items` | Purchase orders/invoices from suppliers + line items |
| `purchase_returns` / `purchase_return_items` | Returns to suppliers |
| `customers` | Customer master — opening balance, credit limit, loyalty points, customer group |
| `customer_payments` | Payments received from customers |
| `invoices` / `invoice_items` | Sales invoices + line items (full GST split: CGST/SGST/IGST, discounts, coupon, loyalty, round-off) |
| `invoice_payments` | Payment records against an invoice (supports partial/split payments) |
| `sales_returns` / `sales_return_items` | Customer returns against a prior invoice |
| `quotations` / `quotation_items` | Estimates — independent numbering, optional `converted_invoice_id` |
| `challans` / `challan_items` | Delivery challans (dispatch docs), can link to an `invoice_id` |
| `held_bills` | Parked in-progress POS carts (JSON `cart_data`) for later recall |
| `coupons` | Discount coupons — fixed/percent, min order, max discount, usage limit/count, validity window |
| `loyalty_transactions` | Ledger of loyalty points earned/redeemed per customer/invoice |
| `expense_categories` / `expenses` | Business expense tracking with bill attachment |
| `payments` | Generic payment ledger (transaction_type + reference_id polymorphic pattern) |
| `notifications` | Per-user/system notifications (title, message, type, read flag) |
| `report_logs` | Record of generated reports (name, filters, generated by/when) |
| `backup_logs` | Record of each DB backup taken (file, size, date, status) |
| `printers` *(printers_schema.sql)* | Named printer profiles — connection type (USB/BLUETOOTH/LAN), IP/port, paper width in dots, default flag |
| `feedback` *(feedback_schema.sql)* | In-app user feedback messages (message, page_url, read flag) |

---

## 6. Modules — Controllers, Views, Features

Every Controller below follows the identical pattern: constructor takes
`(Database $db, Auth $auth)`; `index()` gates on a permission and renders
the list view (data itself is fetched client-side from the matching API);
`form($id = null)` renders add/edit (loads dropdown reference data —
categories, customers, products, etc. — server-side); `view($id)` renders
the read-only detail/print-ready page.

### Dashboard — `DashboardController` (`application/Controllers/DashboardController.php`)
- `index()` — gated by **Access Dashboard**; renders `views/dashboard.php`.
  All widgets (sales/purchase summary, stock alerts, recent activity,
  charts) are populated client-side by `api/dashboard.php` +
  `assets/js/dashboard.js`.

### Products & Inventory — `ProductController` (`/products`)
- `index()` / `form($id=null)` / `view($id)` — gated by **Manage
  Inventory**.
- Features: full product CRUD (category, brand, unit + secondary unit with
  conversion factor, SKU, barcode, HSN code, cost/selling price, GST%,
  minimum-stock threshold, product image, batch tracking toggle); manual
  stock adjustment with reason (logged to `stock_transactions`); low-stock
  surfacing; master-data CRUD for Categories/Brands/Units/Unit Conversions;
  barcode label printing (`barcode_print.php`); bulk actions on the list.

### Purchases — `PurchaseController` (`/purchases`)
- Gated by **Manage Purchases** (referenced as "Manage Inventory"-adjacent
  in some UI copy, but its own permission entry exists — see §11).
- Multi-line purchase entry (qty, cost price, GST) with automatic stock-in
  on save; purchase status workflow; updates the supplier ledger; print
  voucher (`purchase_print.php`); bulk actions.

### Billing / POS — `BillingController` (`/billing`)
- `index()`, `form($id=null)`, `view($id)`, plus `dayEnd()` for the
  Day-End report page. Gated by **Create Invoice** (Day-End additionally
  checked against **View Day End Report**).
- The core POS screen: barcode/type-ahead product search, quick customer
  lookup/add, per-line qty/discount/GST cart with running totals, held
  bills (park/recall/delete a cart), coupon application, loyalty points
  earn/redeem, split/partial payments with payment history, invoice list
  with search/filter, three hand-off paths (A4 print, thermal receipt,
  WhatsApp share), bulk actions.

### Quotations — `QuotationController` (`/quotations`)
- Same line-item/GST engine as billing but with independent numbering and
  no stock impact; status workflow (draft/sent/accepted/rejected); one-click
  **Convert to Invoice** (copies all line items into a new real invoice);
  print view (`quotation_print.php`); bulk actions.

### Delivery Challans — `ChallanController` (`/challans`)
- Goods-dispatch documents, independent of invoicing, own item lines and
  status workflow, optional link back to an invoice; print view
  (`challan_print.php`); bulk actions.

### Returns — `ReturnController` (`/returns`)
- Two flows in one module: **Sales returns** (pick invoice + line items,
  auto-restocks inventory, adjusts customer ledger) and **Purchase
  returns** (pick purchase + line items, auto-adjusts stock and supplier
  ledger); separate tabs/lists for each.

### Customers — `CustomerController` (`/customers`)
- Full CRUD (name, contact, address, GSTIN, credit limit, opening balance,
  customer group); running ledger across invoices/payments/returns;
  receive-payment flow; loyalty point balance display; bulk actions.

### Suppliers — `SupplierController` (`/suppliers`)
- Full CRUD; running ledger across purchases/payments; make-payment flow;
  bulk actions.

### Expenses — `ExpenseController` (`/expenses`)
- Record expenses against categories with optional bill attachment upload;
  own Expense Categories CRUD; feeds Reports and Day-End reconciliation.

### Reports — `ReportsController` (`/reports`)
- `index()` — gated by **View Reports**; single page. A P&L stat-card
  ribbon (Total Revenue, COGS, Total Expenses, Net Profit/Margin) driven by
  `action=summary` sits above 7 tabs: Sales Ledger, Stock Valuation,
  Expenses, Customer Receivables, Supplier Payables, GST Report, Overdue
  Invoices — each backed by the matching `api/reports.php` action and each
  with a client-side "Export CSV" button (no server-side export). See §17
  for the full per-tab column/filter breakdown. Note: `api/reports.php`
  also implements a `purchases` action with no corresponding UI tab
  currently wired up (see §18).

### Users — `UserController` (`/users`)
- `index()`, `form()`, `view($id)` — gated by **Manage Users**.
- Staff account CRUD, role assignment, activate/deactivate, per-user login
  history and activity log viewer.

### Roles — `RoleController` (`/roles`)
- `index()`, `form()` — custom roles with a toggleable permission matrix
  (see §11 for the full permission list).

### Settings — `SettingsController` (`/settings`)
- `index()` — gated by **Manage Settings**; renders the tabbed settings
  shell. There are actually **9 panes** (a "Feedback" inbox exists
  alongside the 8 documented in the README/DOCUMENTATION.md); the first
  four plus part of the sixth share one `#settingsForm` saved via
  `api/settings.php?action=save`, while Coupons, Data & Backups, Printer
  Settings, and Feedback each drive their own independent AJAX CRUD. See
  §17 for the full field-level breakdown of every pane.
  1. **Company Details** — name, GSTIN, email, phone, address, logo
     upload (with live preview + remove button).
  2. **Invoice & Tax** — numbering prefixes/start/end + live "N left"
     usage badges for invoice/quotation/purchase/challan, a dynamic GST
     slab chip-list (with quick-add presets), state code (for CGST/SGST
     vs IGST), invoice footer/terms text.
  3. **Loyalty & Templates** — despite the tab name, only the loyalty
     controls live here (enable toggle, points per ₹100, redemption
     value) — the actual template pickers are under **Theme & Display**.
  4. **Bank Details** — account info printed on invoices, plus UPI ID.
  5. **Coupons** — full coupon CRUD (own modal, own table, own save
     button — outside the shared settings form).
  6. **Theme & Display** — A4 invoice template picker (Standard/
     Professional/Modern/Classic), POS receipt template picker (Thermal
     Standard/Minimal/Bold), system language, POS/barcode-scanner mode
     toggle, and 5 receipt-content toggles (logo/cashier/customer
     mobile/HSN/GST breakdown) + custom header/footer text.
  7. **Data & Backups** — trigger/list/download/delete backups, plus an
     admin-only (`role_id == 1`) "Danger Zone" **Delete All Records**
     purge with a two-step confirm (warning + password re-entry).
  8. **Printer Settings** — printer profile management (see §9).
  9. **Feedback** *(undocumented 9th tab)* — inbox of in-app user
     feedback messages with an unread-count badge, mark-read/delete.

---

## 7. API Reference (every endpoint & action)

All endpoints live in `api/` and share one contract: POST/GET with an
`action` parameter, CSRF-checked on every mutating call via
`Helpers::verifyCsrf()`, permission-checked via `Auth::requirePermission()`
or `Auth::hasPermission()`, and a uniform JSON response
`{status, message, data}` from `Helpers::jsonResponse()`.

### `api/billing.php` (431 lines) — POS/Invoices
`search_product`, `scan_product` (barcode lookup), `get_customers`,
`create_invoice` (the big one — validates cart, computes GST/CGST/SGST/IGST,
applies coupon + loyalty, writes `invoices`+`invoice_items`, decrements
stock via `stock_transactions`, records payment(s)), `list_invoices`,
`day_end_report`, `get_invoice_payments`, `bulk` (bulk status/delete).

### `api/products.php` (461 lines) — Products & master data
`list`, `get`, `save` (create/update), `delete`, `adjust_stock`,
`categories_list` / `category_save` / `category_delete`,
`brands_list` / `brand_save` / `brand_delete`,
`units_list` / `unit_save` / `unit_delete`,
`unit_conversions_list` / `unit_conversion_save` / `unit_conversion_delete` /
`get_conversion`, `bulk`.

### `api/purchases.php` (326 lines)
`list`, `get`, `save` (multi-line purchase + auto stock-in),
`update_status`, `bulk`.

### `api/quotations.php` (357 lines)
`list`, `get`, `save`, `delete`, `update_status`, `convert_to_invoice`,
`bulk`.

### `api/challans.php` (204 lines)
`list`, `get`, `save`, `update_status`, `delete`, `bulk`.

### `api/returns.php` (273 lines)
`list_sales`, `list_purchase`, `get_invoice_items` (for picking return
lines), `get_purchase_items`, `save_sales`, `save_purchase`.

### `api/customers.php` (262 lines)
`list`, `get`, `save`, `receive_payment`, `ledger` (full transaction
history), `delete`, `bulk`.

### `api/suppliers.php` (263 lines)
`list`, `get`, `save`, `make_payment`, `ledger`, `delete`, `bulk`.

### `api/expenses.php` (173 lines)
`list`, `save`, `delete`, `categories_list`, `category_save`, `bulk`.

### `api/reports.php` (217 lines)
`summary`, `sales`, `purchases`, `stock`, `expenses`, `customers`,
`suppliers`, `gst`, `overdue` — each accepts a date-range filter.

### `api/dashboard.php` (273 lines)
No `action` switch — a single aggregate endpoint that returns
sales-today/this-month, stock alerts, recent activity, and chart series in
one payload (handles both MySQL and SQLite date-grouping logic).

### `api/settings.php` (487 lines)
`list` (fetch current `company_settings` row), `save`,
`save_thermal_width` (separate lightweight endpoint for the printer width
control), `backup` (generate a DB dump), `backup_list`, `download_backup`,
`delete_backup`, `purge_all` (danger-zone data wipe). Also defines two
free functions used internally: `formatSize(int $bytes): string` and
`dumpMySQLDatabase(PDO $pdo): string` (hand-rolled `mysqldump`-equivalent
for environments without shell access to the real `mysqldump` binary).

### `api/coupons.php` (136 lines)
`list`, `get`, `save`, `delete`, `validate` (server-side checkout-time
validation against min order amount, validity window, usage limit).

### `api/loyalty.php` (84 lines)
`balance` (customer's current points), `history` (transaction ledger),
`adjust` (manual points correction).

### `api/held_bills.php` (94 lines)
`hold` (park a cart), `list`, `recall`, `delete`.

### `api/users.php` (197 lines)
`list`, `get`, `save`, `delete`, `roles_list` (for the role dropdown),
`activity_logs`, `login_logs`.

### `api/roles.php` (102 lines)
`save` (create/update a role + its permission set), `delete`.

### `api/printers.php` (146 lines)
`list`, `get_default`, `save`, `set_default`, `delete`.

### `api/thermal_print_relay.php` (69 lines)
No `action` switch — single-purpose POST endpoint: takes an IP, port, and
base64-encoded ESC/POS payload, opens a raw TCP socket (`fsockopen`) to the
printer's port 9100 (AppSocket/JetDirect), writes the bytes, and relays
success/failure back to the browser. Validates IP format, port range, and
caps payload size at 5MB.

### `api/notifications.php` (180 lines)
`list`, `mark_all_read`, `mark_read`.

### `api/feedback.php` (83 lines)
`save` (submit feedback), `list`, `mark_read`, `delete`.

### `api/inventory_alerts.php` (90 lines)
No action switch — returns the current low-stock product list directly
(used by the dashboard widget and notification generator).

### `api/search.php` (84 lines)
No action switch — single global-search endpoint; takes a query string
`q` (min length 2) and, permission-gated per section, searches Inventory,
Customers, Suppliers, and Invoices in one pass for the cross-module quick
search.

---

## 8. Standalone Print Endpoints

These are root-level `.php` files rendered directly (not through
`index.php`'s FastRoute table) — pure print/PDF-friendly views, no data
mutation:

| File | Purpose |
|---|---|
| `invoice_print.php` (267 lines) | A4/PDF-style full invoice print layout |
| `invoice_thermal.php` (510 lines) | Thermal receipt page — preview + USB/Bluetooth/LAN print controls (see §9) |
| `barcode_print.php` (197 lines) | Barcode label sheet printing for products |
| `purchase_print.php` (147 lines) | Purchase voucher print layout |
| `quotation_print.php` (152 lines) | Quotation print/PDF layout |
| `challan_print.php` (131 lines) | Delivery challan print layout |

---

## 9. Thermal / Receipt Printing Subsystem

Full technical write-up: [`docs/thermal-printing.md`](docs/thermal-printing.md).
Implementation split across `assets/js/thermal-printer.js` (client) and
`api/thermal_print_relay.php` (server, LAN-only).

**Three transports, no OS print dialog:**
- **USB** — WebUSB (`navigator.usb`); Chrome/Edge only; printer must expose
  a raw USB interface the browser can claim (blocked if an OS print driver
  already claimed it).
- **Bluetooth** — Web Bluetooth; Chrome/Edge only; **BLE only** — Classic
  Bluetooth/SPP (what many cheap "USB+BT" printers actually use) is not
  reachable from a browser at all, a hard platform limitation. Targets the
  common ISSC/Microchip transparent-UART BLE service
  (`49535343-fe7d-...`); writes are chunked at 180 bytes with delays since
  cheap BLE modules drop flooded data.
- **WiFi/LAN** — no raw TCP socket API in browsers, so
  `api/thermal_print_relay.php` relays the raw bytes server-side to the
  printer's port 9100 (AppSocket/JetDirect). Only works if the *web
  server* itself can reach the printer's network — a cloud-hosted install
  cannot print to a printer behind a shop's local router this way.

**Rendering approach:** the receipt DOM is rasterized to an image via
html2canvas, then encoded as a raw ESC/POS raster bit-image and sent byte-
for-byte — this guarantees the ₹ symbol and every font render exactly as
previewed, independent of the printer's built-in character set.

**Paper widths:** 58mm/80mm presets (384/512/576/832 dots @ 203dpi) or an
exact **Custom** dot width; both the live preview and the print output
resize to match with no column overlap.

**Printer Settings (Settings → Printer Settings, `api/printers.php` +
`printers` table):** add/edit/delete named printers, connection type
(USB/Bluetooth/LAN with IP+port), paper width, **Pair Now** test-pairing,
one printer marked **default**. When a default exists, the thermal receipt
page shows a one-click "Print on [Name] ([Type])" confirmation card instead
of the raw transport buttons, with a "use a different printer" fallback.
Optional **auto-print on load** remembers a preferred transport per
browser/device.

---

## 10. Front-End JS Assets

| File | Lines | Role |
|---|---|---|
| `assets/js/billing.js` | 790 | Entire POS/billing screen: product search & barcode add-to-cart, cart math (qty/discount/GST/totals), customer quick-add, held bills, coupon/loyalty application, payment recording, invoice submission via `api/billing.php` |
| `assets/js/dashboard.js` | 338 | Chart.js widgets + summary tiles fed by `api/dashboard.php` |
| `assets/js/thermal-printer.js` | 303 | Shared ESC/POS rasterization + USB/Bluetooth/LAN send logic (used by `invoice_thermal.php`) |
| `assets/js/bulk-actions.js` | 173 | Shared checkbox-select + bulk delete/status-change wiring reused across every module's list page |

`assets/css/style.css` carries global styling, mobile responsiveness
(DataTables → stacked cards, bottom nav on mobile), the animated skeleton
loading overlay used on every listing/report page, and light/dark theme
rules. `assets/css/templates.css` styles the print/receipt templates
specifically.

---

## 11. Roles & Permissions

Roles are fully custom (`roles` table, managed at `/roles`), each a named
bundle of togglable permissions from the fixed master list in `permissions`
(seeded by `dummy_data_hostinger.sql`):

1. Access Dashboard
2. Manage Inventory
3. Manage Purchases
4. Manage Customers
5. Manage Suppliers
6. Create Invoice
7. Manage Expenses
8. View Reports
9. Manage Users
10. Manage Settings
11. Run Backups
12. Manage Quotations
13. Manage Challans
14. Manage Coupons
15. View Day End Report

`role_permissions` is the join table. `Auth::hasPermission()` /
`requirePermission()` enforce these server-side on **every** page
controller and **every** API action — the UI hiding a nav item or button
is never the only gate.

---

## 12. Security Model

- **CSRF**: every state-changing API call requires a token
  (`Helpers::csrfField()` in forms / `Helpers::getCsrfToken()` for AJAX
  headers), verified server-side by `Helpers::verifyCsrf()` against a
  per-session secret generated in `config/config.php`.
- **Passwords**: hashed with PHP's `password_hash()` (bcrypt), verified
  with `password_verify()` — never stored or logged in plaintext.
- **SQL injection**: all queries go through `Database::query()`/`insert()`
  using PDO prepared statements — no raw string interpolation into SQL.
- **AuthZ**: every API action re-checks the relevant permission
  server-side via `Auth`, independent of what the UI shows/hides.
- **Audit trail**: `login_logs` (every login attempt, IP, device, browser,
  session duration) and `activity_logs` (every significant create/
  update/delete, per user/module/record) — both viewable from the Users
  module.
- **Session hardening**: `session.cookie_httponly`, `session.use_only_cookies`,
  and `session.cookie_secure` (when served over HTTPS) set in
  `config/config.php` before `session_start()`.
- **File uploads**: company logo / product images validated for MIME type
  and size before being written to `uploads/`.
- **Soft deletes**: nearly every table uses a `deleted_at` timestamp
  instead of `DELETE` — records are recoverable and never silently lost.

---

## 13. Configuration & Environment

- **`config/config.php`** — loads `.env` (via `phpdotenv`, `safeLoad()` so
  a missing file doesn't hard-fail), starts the session with hardened
  cookie flags, sets timezone to `Asia/Kolkata`, defines path constants
  (`BASE_DIR`, `UPLOAD_DIR`, `BACKUP_DIR`, `LOG_DIR`, `BASE_URL`,
  `APP_PATH`, `UPLOAD_PATH`, `ASSET_PATH`, `COMPANY_NAME`), auto-creates
  `uploads/`/`backups/`/`logs/` if missing, and initializes the CSRF
  secret in `$_SESSION`.
- **`config/database.php`** — `DB_DRIVER` env var selects `mysql` (default)
  or `sqlite`. For MySQL: reads `DB_HOST`/`DB_PORT`/`DB_NAME`/`DB_USER`/
  `DB_PASS` from `.env`, **except** when `HTTP_HOST` is exactly
  `billingdemo.grovixo.com`, in which case hardcoded Hostinger demo
  credentials are forced (ignores `.env` even if one is accidentally
  present on that host). SQLite fallback path:
  `database/database.sqlite`.
- **Environment variables** (`.env`, not committed —`.gitignore`d):
  `DB_DRIVER`, `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`.
- Local dev default: MySQL/MariaDB on `127.0.0.1:3306`, database
  `invoices_systeam` (XAMPP-style) — if another local MySQL (e.g. Homebrew)
  also listens on 3306, only one may be bound or the app connects to the
  wrong instance.

---

## 14. Installation & Deployment

### Local development (Mac/Linux)
```bash
composer install
cp .env.example .env        # then fill in DB credentials
php -S localhost:8000 server.php
```

### Local standalone (Windows)
README references a `START.bat` one-click launcher for offline shop
installs (double-click → server starts → browser opens automatically).
*(Not present in the current file tree — see §18.)*

### Production (Hostinger / cPanel / shared hosting)
1. Create a MySQL database + user in the hosting control panel.
2. Import `dummy_data_hostinger.sql` (full schema + seed data) via
   phpMyAdmin — or let `Database::initializeDatabase()` auto-bootstrap on
   first request against an empty database.
3. Upload the project to `public_html`.
4. `.env` is git-ignored; `config/database.php` auto-detects the
   `billingdemo.grovixo.com` production domain and injects hardcoded
   credentials for that host specifically — for any other production
   domain, set real `.env` values on the server directly.
5. Ensure `uploads/`, `backups/`, and `logs/` are writable by the web
   server user (they're auto-created if missing, but permissions still
   need to allow the PHP process to create/write them).

---

## 15. Default Credentials

After a fresh import/auto-install, the seeded Super Admin account:

- **Email:** `admin@admin.com`
- **Password:** `123456`

Change this immediately after first login (Users module / profile
settings) — especially before exposing the instance publicly.

---

## 16. Global UI Chrome (Sidebar, Header, Footer, Bottom Nav)

Every module page is wrapped by `application/views/header.php` +
`footer.php` (and `bottom_nav.php` on mobile) — this is the chrome that
surrounds every screen in §17.

### `header.php`
- Hard auth gate: redirects to `/login` immediately if `!$auth->check()`.
- Theme bootstrap script sets `data-theme` on `<html>` from
  `localStorage['grovixo-theme']` before paint, avoiding a light/dark flash.
- Loads Bootstrap 5.3.0, FontAwesome 6.4.0, DataTables Bootstrap5 1.13.4,
  SweetAlert2, and Select2 (+ its Bootstrap 5 theme) from CDN, plus the
  local `assets/css/style.css`.
- **Sidebar** (`#app-sidebar`, desktop-fixed / mobile slide-in): company
  logo or a default icon + company name at the top; a single-level,
  flat menu list — no submenus — with each item individually gated by
  `Auth::hasPermission()`:
  - Dashboard (`Access Dashboard`) → `/index`
  - Inventory (`Manage Inventory`) → `/products/index`
  - Purchases (`Manage Inventory`) → `/purchases/index`
  - Billing (`Create Invoice`) → `/billing/index`
  - Returns Log (`Manage Inventory`) → `/returns/index`
  - Quotations (`Manage Quotations`) → `/quotations/index`
  - Delivery Challans (`Manage Challans`) → `/challans/index`
  - Customers (`Manage Customers`) → `/customers/index`
  - Suppliers (`Manage Suppliers`) → `/suppliers/index`
  - Expenses (`Manage Expenses`) → `/expenses/index`
  - Reports (`View Reports`) → `/reports/index`
  - Users (`Manage Users`) → `/users/index`
  - Settings (`Manage Settings`) → `/settings/index`
  - Footer: user avatar (initial), name, role name, and a standalone
    logout icon-button.
- **Top navbar** (hidden on form/view/day_end pages — only the mobile
  hamburger shows there): page title (from a per-module title map) or, on
  the dashboard, a time-of-day greeting ("Good Morning/Afternoon/Evening")
  plus a live-updating clock; a **global search box** (`/` keyboard
  shortcut focuses it) with 300ms-debounced autocomplete against
  `api/search.php`, grouped by Inventory/Customers/Suppliers/Invoices;
  dark/light theme toggle (persisted to `localStorage`); a **notification
  bell** dropdown polling `api/notifications.php` every 30s (icons per
  type: STOCK, PAYMENT, SYSTEM, TAX, INFO); a **user profile dropdown**
  (Settings link + Sign Out).
- Flash alert banner if redirected with `?error=unauthorized`.

### `footer.php`
- Visible copyright bar only ("© 2026 Grovixo…").
- Defines several **global modals**: a barcode-scanner "How to Use" guide
  (`#posGuideModal`), a floating help speed-dial widget (Help Center /
  WhatsApp / Feedback), a **Help Center** modal (4 topic cards), a
  **Feedback** modal (posts to `api/feedback.php?action=save`), and a
  floating **language selector** modal wired to a hidden Google Translate
  widget (13 languages, also auto-enforces the company's configured
  default `system_language` via cookie).
- Loads the shared JS stack: jQuery 3.6.0 (a second time — already loaded
  in `header.php`), Bootstrap bundle, DataTables + its Bootstrap5
  integration, SweetAlert2, Chart.js (unpinned CDN version), Select2, and
  `assets/js/bulk-actions.js`.
- Global inline JS: a `$.ajaxSetup({error:...})` handler that pops a
  SweetAlert2 "System Error" dialog on any failed AJAX call; auto-inits
  Select2 on every `.searchable-select`; sidebar open/close wiring shared
  with `bottom_nav.php`'s hamburger; auto-dismiss for flash alerts; and the
  **skeleton-loader overlay** for every DataTable (`processing.dt` handler
  building shimmering placeholder rows, `$.fn.dataTable.defaults.processing
  = true` globally, with a 450ms minimum visible time so it never flickers
  imperceptibly — this is the "animated branded loading indicator" used on
  every listing page).

### `bottom_nav.php`
- Mobile-only (`<992px`), fixed bottom bar, 5 items, **no permission
  gating at all** (unlike the sidebar, every item always renders): Dashboard,
  Inventory, "Billing POS", "CRM" (labeled differently than the sidebar's
  "Billing"/"Customers"), and a "Menu" item that slides the full sidebar
  out as a drawer rather than navigating anywhere.

---

## 17. Screen-by-Screen UI Reference (Field-Level Detail)

This section documents the **exact fields, columns, and controls** on
every screen, extracted directly from the view templates — for when §6's
narrative summary isn't precise enough (e.g. building a test plan,
onboarding a new dev, or replicating a screen).

### Products (`/products`)

**Add/Edit form** (`products/form.php`) — one section, no tabs: Product
Name\*, SKU\*, Barcode (EAN/UPC), HSN Code, Category (searchable select +
inline "+ Add" modal), Brand (same pattern), Cost Price\* (₹), Selling
Price\* (₹), GST Tax % (options built from the company's saved GST slabs,
defaults 18% for new products), Measurement Unit (+ inline "+ Add"),
Secondary Unit (only shown once a primary unit is picked) + Conversion
Factor (live "1 X = Y Z" preview, auto-fetched if already configured),
Initial Stock Qty (add-mode only), Minimum Stock Alert (default 5),
Product Image (jpg/png/webp, max 2MB, thumbnail preview). Client JS clamps
negative numbers and blocks negative cost/selling price on submit.

**List** (`products/list.php`) — 5 tabs: **Products** (bulk
delete/export; columns: image, name, SKU, HSN, category, selling price,
stock badge — red if ≤ minimum, green otherwise, with secondary-unit
equivalent shown in parens — GST%, status, actions incl. an "Adjust Stock"
modal with Adjustment Type select [Increase/Decrease/Damage/Lost/Expired],
Quantity, and required Audit Remarks), **Categories**, **Brands**,
**Units**, **Unit Conversions** — each of the last 4 a simple inline
form+table CRUD pane.

**View** (`products/view.php`) — Product Card (image, name, SKU, barcode,
HSN, category, brand, unit(s)/conversion, GST%) + Stock Valuation & Ledger
card (current stock badge, cost/selling price, Inventory Value = cost ×
stock, and a Recent Stock Transactions table: date/type/ref
invoice/signed qty change/balance/user).

### Purchases (`/purchases`)

**Form** (`purchases/form.php`) — Supplier (searchable select + inline
"+ Add Supplier" modal), Purchase Date\*, Payment Status (Unpaid/
Partial/Paid), Order Status (Pending/Completed — inventory only updates on
Completed); a custom product-search typeahead (deliberately not Select2,
per an inline code comment, for mobile-touch reasons) adding rows to a
cart table (Qty, Unit [primary/secondary toggle], Cost Price, GST%, Total
— all editable inline); Purchase Notes textarea (present in the UI but not
actually included in the save payload — see §18); Summary panel (Subtotal,
GST, flat Discount ₹, Grand Total).

**List** (`purchases/list.php`) — columns: Purchase No, Supplier, Date,
Payment Status badge, **clickable** Order Status badge (clicking a
"PENDING" badge prompts "Mark as Completed? This will add all items… This
action can be reversed." and posts `update_status`), Subtotal, GST,
Discount, Total, actions (View/Edit).

**View** (`purchases/view.php`) — print-style voucher: supplier header
block, Payment/Order Status badges, "Logged By", items table (product +
SKU, qty w/ secondary-unit equivalent, cost, GST%, amount), totals block.
"Print A4" opens `purchase_print.php`.

### Billing / POS (`/billing`) — the most complex screen in the app

**List** (`billing/index.php`) — Invoice No, Customer (default "Walk-in
Customer"), Date, Invoice Type, Payment Method badge, Total, Paid (green),
Due (red, or a green "Paid" label at zero), actions: View + Print (opens
`invoice_print.php`).

**Form** (`billing/form.php`):
- Customer & invoice info row: Customer (searchable select, default
  "Walk-in Customer", "+" quick-add modal), Invoice Date\*, Due Date,
  Invoice Type (Retail / GST Invoice [default] / Tax Invoice / Proforma),
  Mobile No (readonly, auto-filled from customer selection).
- **POS Scanner** block (only if `pos_mode` is enabled in Settings) — an
  autofocused barcode input plus a "How it works" guide modal.
- **Product search** — same custom typeahead pattern as Purchases/
  Quotations/Challans, hitting `api/billing.php?action=search_product`.
- **Cart table**: #, Product Name, HSN/SAC, Qty, Unit, Price, Discount,
  GST %, Total, Action — with an empty-state placeholder row.
- **Additional Details** column: Discount Coupon code input + Apply
  button; a **Loyalty panel** (only if enabled and the selected customer
  has points) with a redeem toggle, points input, and live "= ₹X" preview;
  **Payment Mode** — one or more split payment rows (method: Cash / UPI /
  Card / Net Banking / Credit + amount), a running "Received" total and
  "Change / Balance" display, and a "Split" button to add more rows.
- **Invoice Summary** column: Subtotal, CGST, SGST, IGST (hidden unless
  applicable), Total Tax; flat Discount input; Coupon row (removable) and
  Loyalty row (both conditional); Round Off; **Grand Total**; action
  buttons **Hold** (F3) and **Generate Invoice** (F4) with a full keyboard
  shortcut legend (F2 search, F3 hold, F4 generate, F5 recall, F6 print,
  Esc cancel).
- **Held Bills** slide-out panel (recall/delete a parked cart).
- Quick-add-customer modal and a Hold-Bill modal (optional note field).

**View** (`billing/view.php`) — invoice document: company header, "Billed
To" block, payment method + status badge + cashier name, items table
(with HSN, qty incl. secondary-unit, rate, GST%, discount, amount), terms/
notes box, totals block including a **split-payments breakdown** (if more
than one active payment), paid/balance due, loyalty earned/redeemed.
Actions: **Print A4**, **Thermal**, **WhatsApp** (pre-filled share
message), Back.

**Day-End Report** (`billing/day_end.php`) — date picker + Print/PDF
(html2pdf.js) buttons; 4 stat cards (Invoice count, Total Sales, Amount
Received, Net Cash Position); 4 detail tables (Payment Mode Breakdown,
Split Payment Summary, Top 5 Products Sold, Cashier-wise Sales); 3
deduction cards (Returns, Expenses, Outstanding Due) — all from
`api/billing.php?action=day_end_report`.

### Quotations (`/quotations`)

**List** — columns: Quotation No, Customer, Date, Valid Until, Grand
Total, Status badge (Draft/Sent/Accepted/Rejected/Converted, each its own
color), actions: View always; Edit and Delete unless status is
`CONVERTED`; a **"Convert"** button appears only when `ACCEPTED` — confirms,
posts `convert_to_invoice`, stashes the result in
`sessionStorage['quotation_cart']`, and redirects into the POS screen.

**Form** — Customer (+ quick-add modal), Quotation Date\*, Valid Until
(defaults today+30 days), a Status dropdown in edit mode that saves
independently of the main Save button (immediate `update_status` call;
disabled/read-only once `CONVERTED`), the same product-search+cart pattern
as Billing but with **Discount** as a per-line percent-or-flat toggle,
Quotation Notes, and a Summary panel (Subtotal, GST, flat Discount, Grand
Total).

**View** — printable document (company header, "Quotation For" block,
items table, notes box, totals). Actions: Print (`quotation_print.php`),
**Convert to Invoice** (only if `ACCEPTED`), WhatsApp share, Back.

### Delivery Challans (`/challans`)

**List** — columns: Challan No, Customer (default "Walk-in"), Date,
Transport, Vehicle No, Status badge (Active/Dispatched/Delivered/
Cancelled). Actions: View always; Edit, **Mark Delivered**, and **Cancel**
only while not yet Delivered/Cancelled; Delete always available.

**Form** — Customer\* (+ quick-add modal — note: sourced from
`api/billing.php?action=get_customers` here, vs. `api/customers.php?action=list`
on the Quotations form, see §18), Challan Date\*, Transport Name, Vehicle
No; the same product-search+cart pattern but **pricing-free** — cart
columns are just #, Product, HSN/SAC, Qty, Unit, Action (no rate/GST/
total, since a challan is a dispatch document, not a sale); Dispatch Notes;
a Dispatch Summary showing Total Items / Total Quantity (no money).

**View** — printable dispatch document: company header, "Dispatch To"
block, "Transport Info" block, items table (quantity only, no pricing),
a total-quantity footer row, notes, and a 3-column signature strip
(Prepared By / Checked By / Received By) with a disclaimer that it is not
a tax invoice. Only a browser-native `window.print()` button — no
dedicated print route, no WhatsApp/convert actions.

### Returns (`/returns`)

**List** — two tabs, Sales Returns (Credit Notes) and Purchase Returns
(Debit Notes), each its own DataTable (Return No, original doc, party,
date, total, remarks, View action only).

**Form** — type is set by a `?type=SALES|PURCHASE` query param: pick the
original Invoice or Purchase Order (searchable select showing
`{no} | {party} | ₹{amount}`), Return Date; an items table populated from
the chosen document with an editable Return Qty per line (clamped to the
originally purchased/sold qty) and a computed Refund Total per row; a
required Return Remarks/Reason textarea; a running Total Refund/Credit and
a Save button.

**View** — read-only voucher (Credit Note / Debit Note), party block,
"Voucher Meta" (original doc no, logged by), items table, remarks box,
total returned value. No edit/delete/print actions on this screen.

### Customers (`/customers`)

**Form** — Full Name\*, Mobile\*, Email, GSTIN, Opening Balance (₹),
Credit Limit (₹, "0 = unlimited" hint), State (all 29 Indian states/UTs,
for CGST/SGST vs IGST logic), Address.

**List** — columns: Name, Mobile, Email, GST Number, Outstanding Credit
badge (red if owed, green at zero). Row actions: **Statement** (→ view),
**Receive** payment (disabled at zero balance, opens a modal: date, Amount
Received\*, Payment Method [Cash/UPI/Card/Net Banking], reference #,
remarks), Edit.

**View** — Customer Profile card (avatar, name, mobile, email, GSTIN,
credit limit, address) + Account Ledger card: opening balance, live net
outstanding credit, and a full ledger table (date, doc type badge
[Invoice/Opening Balance/Payment], doc no, remarks, debit, credit, running
balance).

### Suppliers (`/suppliers`)

Mirrors Customers: **Form** — Business Name\*, Contact Person, Mobile\*,
Email, GSTIN, Opening Balance (add-mode only), Address. **List** —
Name, Contact Person, Mobile, Email, GST Number, Outstanding Payable
badge, actions Statement / **Pay** (modal: date, Amount Paid\*, method,
reference #, remarks) / Edit. **View** — Supplier Profile card + ledger
(debit=paid, credit=billed, running balance).

### Expenses (`/expenses`)

**Form** (multipart) — Expense Category\* (+ inline quick-add modal),
Date\*, Amount\* (₹), Payment Method\* (Cash/UPI/Card/Net Banking),
Description, Attach Bill Copy (PDF/JPG/PNG, max 2MB, with a "View Current
Attachment" link when editing).

**List** — main table (Date, Category, Description, Payment Method, Bill
Document link or "No receipt", Amount in red, actions View/Edit/Delete)
plus a sidebar Categories manager (quick-add form + plain list, no
edit/delete on individual categories from this list).

**View** — a single voucher card: amount (large, red), date, category,
payment method badge, logged-by, description box, and an attached-receipt
block that inlines an image preview or an "Open Bill Document" link.

### Users (`/users`)

**Form** — Full Name\*, Email\*, Mobile\*, System Access Role\* (from the
live roles list), Account Status (Active/Inactive), Password (required
only when creating; "leave blank to keep current" when editing).

**List** — 3 tabs: **System Users** (Name, Role, Email, Mobile, Last
Login, Status, actions incl. a "De-activate" delete with a pointed
SweetAlert warning that it disables login immediately), **Activity Logs**
(Time, User, Module, Action Description, IP), **Login History** (Login
Time, Logout Time [or "Active Session"], User, Browser/Device, IP).

**View** — User Profile card (avatar, name, role badge, email, mobile,
status, last login) + a 2-tab Activity Logs / Login History pair scoped to
that one user.

### Roles (`/roles`)

**List** — ID, Role Name (role id 1 flagged "Super Admin"), Description,
Status, actions — role id 1 shows "System Core" instead of Edit/Delete
(fully protected).

**Form** — Role Name, Status, Description (all read-only/disabled for
role id 1); a **permission matrix** rendered as one card per module (3
columns), each listing its permissions as checkboxes (`permissions[]`);
role id 1 has every checkbox forced checked-and-disabled; a "Select/
Deselect All" toggle for every other role.

### Settings (`/settings`) — 9 tabs, see §6 for the module summary

1. **Company Details** — logo upload w/ live preview + remove button,
   Business Name\*, GSTIN, Email, Phone, Address.
2. **Invoice & Tax** — 4 identical numbering blocks (Invoice/Quotation/
   Purchase/Challan: Prefix, Start, End, and a live "Used: X / Y" badge
   that turns warning-colored at ≤100 remaining and danger-colored at 0);
   a dynamic GST-slab chip list (add via number input or a quick-add
   dropdown: 0/0.25/3/5/12/18/28%); State code (37 GST state/UT codes);
   Invoice Footer text; Terms & Conditions.
3. **Loyalty & Templates** — Enable toggle, Points per ₹100, ₹ value per
   point (template pickers are actually under Theme & Display, see §18).
4. **Bank Details** — Bank Name, Account No, IFSC, Branch, UPI ID.
5. **Coupons** — table (Code, Name, Type, Value, Min Order, Valid Until,
   Used, Status) + modal (Code\*, Name\*, Type\* [Percentage/Flat],
   Value\*, Min Order Amount, Max Discount, Valid From/Until, Usage Limit
   ["0 = unlimited"]).
6. **Theme & Display** — A4 invoice template picker (Standard/
   Professional/Modern/Classic, each with a hover preview mockup); POS
   receipt template picker (Thermal Standard/Minimal/Bold); System
   Language (13 options); POS & Barcode Mode toggle (reveals a "Guide"
   button/modal when enabled); 5 receipt-content toggles (logo, cashier
   name, customer mobile, HSN, GST breakdown — logo/cashier/mobile/GST
   default ON, HSN defaults OFF) + custom receipt header/footer text.
7. **Data & Backups** — backup table (file, size, date, creator, status)
   with Download/Delete; a **"Backup"** button gated on the *Run Backups*
   permission; an admin-only (`role_id==1`) **Danger Zone** — "Delete All
   Records" with a two-step confirm (itemized warning, then a password
   re-entry prompt) before calling `purge_all`.
8. **Printer Settings** — a global default paper-width control (58mm/
   80mm×2/Custom dot presets) plus full printer CRUD: name, connection
   type (USB/Bluetooth/LAN), IP+port (LAN only, conditionally shown),
   paper width, a **"Pair Now"** button that invokes the WebUSB/
   WebBluetooth device picker directly from Settings, and a default-
   printer star toggle. See §9 for the printing subsystem itself.
9. **Feedback** — inbox table (From, Message, Page, Date) with an unread
   badge, mark-read, and delete.

### Reports (`/reports`)

A P&L stat-card ribbon (Total Revenue, COGS, Total Expenses, Net Profit)
above 7 tabs, each with a client-side "Export CSV" button:
- **Sales Ledger** — Invoice No, Customer, Date, Type, Total, Paid, Due,
  Payment Mode (date-range filtered).
- **Stock Valuation** — Product, SKU, Cost, Selling Price, Stock badge
  (red "Low stock" at/under minimum), Value at Cost, Value at Sell.
- **Expenses** — Date, Category, Description, Method, Amount (date-range
  filtered).
- **Customer Receivables** — Name, Mobile, Email, Outstanding Receivable.
- **Supplier Payables** — Name, Contact Person, Mobile, Outstanding
  Payable.
- **GST Report** — 4 summary tiles (Total CGST/SGST/IGST/Tax, computed
  client-side from the visible rows) + a table: Invoice No, Date,
  Customer, Taxable, CGST, SGST, IGST, Total Tax (date-range filtered).
- **Overdue Invoices** — Invoice No, Customer, Invoice Date, Due Date,
  Days Overdue, Total, Due (not date-range filtered).

### Dashboard (`/`)

Hero banner (New Invoice / Day-End / Inventory Alerts quick actions) with
inline Sales/Profit/Receivable metrics; a 4-card KPI grid (Today's Sales,
Purchases, Expenses, Net Profit); 5 quick-action cards (Invoice, Quotation,
Product, Customers, Reports); an 8-tile status grid (Customers, Suppliers,
Products, Low Stock, Overdue, Held Bills, Expiring Stock, Payment Watch);
5 Chart.js charts (Daily Sales — last 7 days, Expense Mix — last 30 days,
Monthly Revenue Trends — last 6 months, Top Selling Products — this month,
Payment Mode Distribution — this month); two recent-activity tables
(Recent Invoices, Recent Payments Flow); and an **Inventory Alerts** modal
with 5 sub-tabs (Low Stock, Out of Stock, Expiry Soon, Fast Moving, Dead
Stock) fed by `api/inventory_alerts.php`.

---

## 18. Known Gaps / Migration Notes

Observed while scanning the current tree — useful context, not necessarily
bugs:

- **`START.bat`** is documented in `README.md` (Windows standalone launcher)
  but does not currently exist in the repo root.
- **Migration shim in `index.php`**: the `NOT_FOUND` branch falls back to
  requiring a literal file path (`$uri`, `$uri.php`, or `$uri/index.php`)
  when no FastRoute route matches — this is how the per-module
  `index.php`/`form.php`/`view.php` stub files, `login.php`, and every
  standalone print file stay reachable without an explicit route entry.
  Only the routes listed in §3 are "real" FastRoute routes; everything
  else resolves through this fallback.
  - `Manage Purchases` and `View Day End Report` are real, separate
  permissions (seeded in `permissions`) even though they're described
  loosely elsewhere; `PurchaseController`/`BillingController::dayEnd()`
  gate on them specifically.
- **`cookies.txt`** at the project root is a stray `curl` cookie-jar file
  from manual testing — not part of the application.
- **Two schema sources exist**: `schema.txt` (flat column reference) and
  `dummy_data_hostinger.sql` (authoritative — actual `CREATE TABLE` +
  sample rows). `printers` and `feedback` tables live only in their own
  standalone schema files under `database/`, not in the main dump — apply
  those separately on a database that predates those features.
- **`api/reports.php`'s `purchases` action has no UI tab.** The Reports
  screen wires up Sales/Stock/Expenses/Customers/Suppliers/GST/Overdue but
  never calls `action=purchases` — either a removed tab or one that was
  never finished.
- **Settings' "Loyalty & Templates" tab doesn't contain templates.** The
  A4/POS receipt template pickers actually live under **Theme & Display**;
  the Loyalty tab only has the points-earning/redemption controls. Likely
  a leftover tab name from before the UI was reorganized.
- **Inconsistent customer-dropdown source between sibling forms**:
  Quotations' form populates its customer select from
  `api/customers.php?action=list`, while Challans' form uses
  `api/billing.php?action=get_customers` for the same purpose — two
  different endpoints serving what should be identical data.
- **`purchases/form.php`'s "Purchase Notes" textarea is not saved** — it's
  present in the UI but not included in the payload posted to
  `api/purchases.php?action=save`, so anything typed there is silently
  dropped.
- **jQuery 3.6.0 is loaded twice** — once inline in `header.php`, again in
  `footer.php` — harmless (later load wins) but worth deduplicating.
- **Settings' Feedback tab and printer-list row actions (set-default,
  delete-printer) reuse the Printer form's CSRF token** (a JS variable
  `printerCsrf`) rather than each reading its own token field — works
  today since both live in one page/session, but couples two otherwise
  unrelated features.
- **The GST state-code list in Settings skips code 28** (Andhra Pradesh's
  original code, since reallocated/split with Telangana) — likely
  intentional given India's post-2014 state reorganization, but worth
  confirming against current GST authority data if state-wise tax
  behavior is ever audited.
- **`roles/form.php`'s permission matrix is fully data-driven** — module
  names, permission names, and grouping all come from the `permissions`
  table via the controller, not from the view template. To see the exact
  literal labels for a specific role's checkboxes, check the `permissions`
  table (§11 lists the 15 seeded permissions) rather than the view file.
