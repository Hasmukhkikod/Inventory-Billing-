# Grovixo IIMS — Invoice & Inventory Management System

Complete feature reference for this application. Covers every module,
screen, API, and setting currently implemented in the codebase.

---

## Table of Contents
1. [Overview](#1-overview)
2. [Architecture](#2-architecture)
3. [Authentication & Access Control](#3-authentication--access-control)
4. [Dashboard](#4-dashboard)
5. [Products & Inventory](#5-products--inventory-products)
6. [Purchases](#6-purchases-purchases)
7. [Billing / POS](#7-billing--pos-billing)
8. [Quotations](#8-quotations-quotations)
9. [Delivery Challans](#9-delivery-challans-challans)
10. [Thermal / POS Receipt Printing](#10-thermal--pos-receipt-printing)
11. [Returns](#11-returns-returns)
12. [Customers](#12-customers-customers)
13. [Suppliers](#13-suppliers-suppliers)
14. [Expenses](#14-expenses-expenses)
15. [Reports](#15-reports-reports)
16. [Settings](#16-settings-settings)
17. [Notifications](#17-notifications)
18. [Global Search](#18-global-search)
19. [UI/UX Details](#19-uiux-details)
20. [Security](#20-security)
21. [Local Development](#21-local-development)

---

## 1. Overview

Grovixo IIMS is a PHP-based billing, inventory, and business-management
system for retail/wholesale shops. It handles the full cycle: purchasing
stock from suppliers, selling to customers (POS-style billing), quotations,
delivery challans, returns, expenses, customer/supplier ledgers, staff with
role-based permissions, reporting, and direct thermal-printer integration
(USB / Bluetooth / WiFi-LAN). It is fully mobile-responsive end to end.

---

## 2. Architecture

```
index.php                 Front controller: routes URLs to Controllers
application/
  Controllers/             One controller per module (renders pages only)
  Models/                  Database, Auth, Helpers
  views/                   PHP view templates (list/form/view per module)
api/                       One JSON endpoint per module (all data operations)
assets/
  css/style.css            Global styles, mobile responsiveness, themes
  js/                      Shared front-end JS incl. thermal-printer.js
database/                  SQL schema files
docs/                      Feature-specific technical docs (e.g. printing)
uploads/                   Company logo, product images
backups/                   Generated database backup files
config/                    config.php (app constants), database.php (PDO)
```

- **Backend**: PHP 8.x, no framework — a custom front-controller router
  (`index.php`) built on [nikic/FastRoute](https://github.com/nikic/FastRoute),
  PSR-4 autoloading (`App\` → `application/`).
- **Database layer**: custom PDO wrapper (`application/Models/Database.php`)
  with `query()`, `insert()`, and `transaction()` helpers, running against
  MySQL/MariaDB.
- **Frontend**: Bootstrap 5, jQuery, DataTables 1.13.4 (server-driven via
  AJAX) for every listing page, Select2 for searchable dropdowns.
- **Printing**: html2canvas + raw ESC/POS byte encoding for direct thermal
  receipt printing (see §10).

**Request flow**: every module has `<module>/index.php` (list),
`<module>/form.php` (add/edit), and `<module>/view.php` (detail), routed by
`index.php` to a Controller method that just renders the view. All actual
data reads/writes go through `api/<module>.php?action=<verb>` endpoints
called via AJAX/fetch, returning JSON (`{status, message, data}`). Every
state-changing API call is CSRF-protected via a session token
(`Helpers::csrfField()` / `Helpers::verifyCsrf()`).

---

## 3. Authentication & Access Control

- **Login** (`login.php`) — email + password, branded split-panel design
  (brand/illustration panel + form panel) with an animated success state
  before redirecting to the dashboard.
- **Logout** (`logout.php`) — destroys the session.
- **Session-based auth**: `Auth::check()` gates every request;
  `Auth::requirePermission($name)` guards page controllers and API actions
  alike — the UI hiding a button is never the only protection.
- **Login logs & activity logs** — every login attempt and key action is
  recorded (`login_logs`, `activity_logs` tables), viewable per-user from
  the Users module.
- **Roles & Permissions** (`/roles`) — fully custom roles (not hardcoded).
  Each role is a named set of toggleable permissions. Current permission
  set:
  - Access Dashboard
  - Create Invoice
  - Manage Inventory
  - Manage Quotations
  - Manage Challans
  - Manage Customers
  - Manage Suppliers
  - Manage Expenses
  - Manage Coupons
  - Manage Users
  - Manage Settings
  - Run Backups
  - View Reports
- **Users module** (`/users`) — create staff accounts, assign a role,
  activate/deactivate, and view each user's login history and activity log.

---

## 4. Dashboard

`/` (and `/index.php`) — landing page after login, gated by "Access
Dashboard". Live snapshot of business health (sales, purchases, stock
alerts, recent activity) via `api/dashboard.php`.

---

## 5. Products & Inventory (`/products`)

- Full CRUD for products: category, brand, unit of measure, secondary unit
  + conversion factor (e.g. sell in pieces, stock in boxes), SKU,
  **barcode**, HSN code (for GST), cost price, selling price, GST%,
  minimum-stock threshold, and a product image.
- **Stock adjustment** — manual stock in/out with reason tracking, fully
  logged in `stock_transactions`.
- **Low-stock tracking** via each product's minimum-stock threshold,
  surfaced on the dashboard and in reports.
- **Master data**, each with its own CRUD screen:
  - **Categories**
  - **Brands**
  - **Units** and **Unit Conversions** (e.g. 1 Box = 12 Pieces) so stock can
    be tracked and sold in different units.
- **Barcode label printing** (`barcode_print.php`).
- **Bulk actions** on the product list (bulk delete/status change, etc.).
- Listing uses the shared animated skeleton-loader while data fetches.

---

## 6. Purchases (`/purchases`)

- Record stock purchases from suppliers: multi-line item entry with
  quantities, cost prices, GST — auto stock-in on save.
- **Purchase status workflow** (draft/received, etc.).
- Linked to the **Supplier ledger** — every purchase and payment affects
  the supplier's running balance.
- Print-friendly purchase voucher (`purchase_print.php`).
- Bulk actions on the purchase list.

---

## 7. Billing / POS (`/billing`)

The core point-of-sale screen for creating invoices:
- **Product search & barcode scan** — type-ahead search or scan a barcode
  to add a line item instantly.
- **Customer lookup/quick-add** while billing.
- **Cart** with per-line quantity/discount/GST and whole-invoice totals;
  mobile-responsive (the cart table becomes stacked cards on small
  screens).
- **Held bills** — park an in-progress sale and recall it later (or delete
  it), so a cashier can serve another customer without losing the cart.
- **Coupons** — apply a discount coupon at checkout, validated server-side
  against the rules configured in Settings.
- **Loyalty points** — customers earn points per ₹100 spent and can redeem
  them for a discount, if enabled in Settings.
- **Payments** — record invoice payment(s) and view an invoice's payment
  history.
- **Day-End Report** (`/billing/day_end.php`) — cash/sales summary for a
  business day, for shift close-out and reconciliation.
- **Invoice listing** with the standard search/filter DataTable.
- Three ways to hand an invoice to the customer:
  - **A4 print/PDF** (`invoice_print.php`).
  - **Thermal receipt** (`invoice_thermal.php`) — see §10.
  - **WhatsApp share** of the invoice.
- Bulk actions on the invoice list.

---

## 8. Quotations (`/quotations`)

- Create/edit quotations (estimates) using the same line-item/GST engine as
  billing, with an independent numbering sequence (prefix configurable in
  Settings) — quotations do not affect stock.
- **Status workflow** (draft/sent/accepted/rejected, etc.).
- **Convert to Invoice** — one click turns an accepted quotation directly
  into a real invoice, copying all line items.
- Print/PDF view (`quotation_print.php`) and bulk actions.

---

## 9. Delivery Challans (`/challans`)

- Create delivery challans (goods-dispatch documents) independent of
  invoicing, with their own item lines and status workflow.
- Print view (`challan_print.php`) and bulk actions.

---

## 10. Thermal / POS Receipt Printing

Full receipt-printer integration; detailed technical doc at
[`docs/thermal-printing.md`](docs/thermal-printing.md). Highlights:

- **Direct hardware printing** from the browser — no OS print dialog, no
  A4/PDF detour needed for a receipt printer:
  - **USB** via WebUSB (`navigator.usb`) — Chrome/Edge only.
  - **Bluetooth** via Web Bluetooth (BLE only; Classic Bluetooth/SPP is a
    hard browser/platform limitation, not fixable client-side).
  - **WiFi/LAN** via a server-side relay (`api/thermal_print_relay.php`)
    speaking the raw "AppSocket"/JetDirect protocol (port 9100) that most
    network printers use, since browsers have no raw TCP socket API.
- **Pixel-perfect output** — the receipt is rendered to an image with
  html2canvas and sent as a raw ESC/POS raster bit-image, so the ₹ symbol
  and every font render exactly as shown on screen, regardless of the
  printer's built-in character set.
- **Any paper width supported** — 58mm/80mm presets (384/512/576/832 dots
  @ 203dpi) or an exact **Custom** dot width for a specific machine; both
  the on-screen preview and the printed output resize to match, and the
  receipt's table layout stays intact (no column overlap) at every width.
- **Printer Settings** (Settings → Printer Settings tab) — centralized
  printer management:
  - Add/edit/delete named printers (e.g. "Front Counter", "Counter 2").
  - Choose connection type: USB, Bluetooth, or LAN (with IP + port).
  - Set paper width per printer, including Custom.
  - **Pair Now** — test-pair a USB/Bluetooth device directly from Settings
    to confirm it's reachable before relying on it at checkout.
  - Mark one printer as the **default** (star toggle).
- **One-click print confirmation** — when a default printer is configured,
  the thermal receipt page shows a "Print on [Printer Name] ([Type])"
  confirmation card with a document preview, instead of the raw button row;
  "Use a different printer" reveals the full manual USB/Bluetooth/LAN
  option set as a fallback.
- **Auto-print on load** — optionally remember a preferred transport per
  browser/device so opening any receipt sends it straight to the printer
  without an extra click.

---

## 11. Returns (`/returns`)

- **Sales returns** — return items from a previous invoice (choose invoice
  + line items); auto-restocks inventory and adjusts the customer ledger.
- **Purchase returns** — return items to a supplier from a previous
  purchase; auto-adjusts stock and the supplier ledger.
- Separate tabs/lists for sales vs. purchase returns.

---

## 12. Customers (`/customers`)

- Customer CRUD (name, contact, address, GSTIN, etc.).
- **Customer ledger** — full running balance/transaction history across
  invoices, payments, and returns.
- **Receive payment** against an outstanding balance.
- Loyalty point balance tied to each customer (see §7).
- Bulk actions.

---

## 13. Suppliers (`/suppliers`)

- Supplier CRUD.
- **Supplier ledger** — running balance across purchases and payments.
- **Make payment** to a supplier.
- Bulk actions.

---

## 14. Expenses (`/expenses`)

- Record business expenses against categories.
- **Expense categories** — own CRUD (e.g. Rent, Utilities, Salaries).
- Feeds into Reports and Day-End reconciliation.

---

## 15. Reports (`/reports`)

Central reporting dashboard covering:
- **Summary** — headline business metrics for a date range.
- **Sales report** — invoice-level sales breakdown.
- **Purchases report** — purchase-level breakdown.
- **Stock report** — current inventory position/valuation.
- **Expenses report** — expense totals by category/date.
- **Customers report** — per-customer sales/outstanding.
- **Suppliers report** — per-supplier purchases/outstanding.
- **GST report** — tax collected/paid breakdown by slab, for filing.
- **Overdue report** — outstanding customer/supplier balances past due.

All report tables use the same animated skeleton loading indicator as
every other listing page in the system.

---

## 16. Settings (`/settings`)

Tabbed settings screen; each tab saves independently:

1. **Company Details** — company name, GSTIN, email, phone, address, logo
   upload, state code.
2. **Invoice & Tax** — invoice/quotation/purchase/challan number prefixes,
   GST slabs, invoice footer/terms text.
3. **Loyalty & Templates** — enable/disable loyalty points, points earned
   per ₹100, redemption value; plus POS receipt template controls:
   - Toggle logo, cashier name, customer mobile, HSN code, and the full
     GST breakdown (vs. a single collapsed tax line) on/off individually.
   - Custom header and footer text printed on every receipt.
4. **Bank Details** — bank account info printed on invoices for payment.
5. **Coupons** — create/edit/delete discount coupons used at checkout.
6. **Theme & Display** — light/dark theme and display preferences.
7. **Data & Backups** — trigger and manage database backups, restore/
   download.
8. **Printer Settings** — full printer management, described in §10.

---

## 17. Notifications

- In-app notification center — list notifications, mark one or all as
  read. Used for things like low-stock alerts and overdue payments.

---

## 18. Global Search

- Cross-module quick-search endpoint for jumping straight to a
  customer/product/invoice from anywhere in the app.

---

## 19. UI/UX Details

- **Fully mobile responsive** — every page, including all DataTables
  (which collapse into stacked cards on small screens) and the billing
  cart, works on phones/tablets; a fixed bottom navigation bar replaces
  the sidebar on mobile.
- **Animated, branded loading indicators** on every listing page — a
  skeleton-row shimmer overlay appears while a table's data is being
  fetched (with a guaranteed minimum visible duration so it's never an
  imperceptible flicker), replacing DataTables' default spinner.
- **Light/Dark theme** toggle.
- **Branded login experience** — split-panel design with an animated
  success state on successful authentication.

---

## 20. Security

- CSRF tokens required on all state-changing API requests.
- Passwords hashed with PHP's `password_hash()` — never stored plain.
- All database queries use PDO prepared statements.
- Every API action re-checks the relevant permission server-side.
- Login and key actions are audit-logged (`login_logs`, `activity_logs`).
- File uploads (company logo, product images) validate MIME type and size
  before being written to `uploads/`.

---

## 21. Local Development

- Requires PHP 8.x + MySQL. This project is developed against **XAMPP's**
  MySQL on `127.0.0.1:3306`, database `invoices_systeam` — if another
  MySQL (e.g. Homebrew's) is also running locally, make sure only one is
  bound to port 3306 or the app will connect to the wrong instance.
- Run with the built-in PHP server from the project root:
  ```
  php -S localhost:8000
  ```
- Config lives in `config/config.php` (app constants) and
  `config/database.php` (DB credentials/DSN).
- `database/printers_schema.sql` is a standalone schema file for the
  printer-management tables; the rest of the schema is reflected in
  `dummy_data_hostinger.sql` (full reference schema + sample data).
