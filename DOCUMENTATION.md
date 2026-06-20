# Grovixo IIMS v2.0 - Complete System Documentation
## Invoice & Inventory Management System

---

## Table of Contents
1. [System Overview](#1-system-overview)
2. [Technology Stack](#2-technology-stack)
3. [Installation & Setup](#3-installation--setup)
4. [Database Architecture](#4-database-architecture)
5. [Module Guide](#5-module-guide)
6. [API Reference](#6-api-reference)
7. [Feature Documentation](#7-feature-documentation)
8. [User Roles & Permissions](#8-user-roles--permissions)
9. [File Structure](#9-file-structure)
10. [Keyboard Shortcuts](#10-keyboard-shortcuts)

---

## 1. System Overview

Grovixo IIMS is a full-featured Billing and Inventory Management System designed for Indian businesses. It provides POS billing, GST-compliant invoicing, inventory tracking, customer CRM, and comprehensive financial reporting.

### Key Highlights
- POS Terminal with barcode scanning and real-time search
- Hold & Recall bills for multi-customer checkout
- Split payment support (Cash + UPI + Card in single invoice)
- GST compliance with CGST/SGST/IGST auto-calculation
- HSN/SAC code support on products and invoices
- Quotation/Estimate creation and conversion to invoices
- Delivery Challan for goods dispatch
- Discount coupon/promo code system
- Customer loyalty points (earn & redeem)
- Day-end cash register reconciliation report
- Multiple print templates (Professional A4, Thermal 80mm receipt)
- Barcode generation and label printing
- Batch & expiry date tracking for products
- WhatsApp invoice sharing
- Role-based access control (4 roles, 15 permissions)
- Mobile-responsive design with bottom navigation
- Supports both MySQL and SQLite databases

---

## 2. Technology Stack

| Component | Technology |
|-----------|-----------|
| Backend | PHP 8.0+ |
| Database | MySQL 5.7+ / SQLite 3 |
| Routing | nikic/fast-route |
| Environment | vlucas/phpdotenv |
| Frontend | Bootstrap 5.3, jQuery 3.6 |
| Tables | DataTables 1.13 |
| Charts | Chart.js |
| Alerts | SweetAlert2 |
| Icons | Font Awesome 6.4 |
| Fonts | Inter, Outfit (Google Fonts) |
| Barcodes | JsBarcode (CDN) |

---

## 3. Installation & Setup

### Requirements
- PHP 8.0 or higher
- MySQL 5.7+ (or SQLite 3)
- Composer (PHP package manager)

### Fresh Install Steps

```bash
# 1. Clone/download the project
cd /your/project/path

# 2. Install PHP dependencies
composer install

# 3. Configure environment
cp .env.example .env
# Edit .env with your database credentials:
#   DB_DRIVER=mysql
#   DB_HOST=127.0.0.1
#   DB_PORT=3306
#   DB_NAME=invoices_systeam
#   DB_USER=root
#   DB_PASS=

# 4. Start the development server
php -S localhost:8000 server.php

# 5. Open browser to http://localhost:8000
# Database tables and seed data are auto-created on first visit
```

### Default Login Credentials
- **Email:** hasmukhkikod@gmail.com
- **Password:** admin123
- **Role:** Super Admin (full access)

### Upgrading from v1.0
If you have an existing database from v1.0, run the migration:
```bash
# Apply v2.0 migration (adds new tables and columns)
php -r "
require_once 'vendor/autoload.php';
require_once 'config/config.php';
require_once 'config/database.php';
use App\Models\Database;
\$db = new Database();
\$pdo = \$db->getConnection();
\$sql = file_get_contents('database/migration_v2.sql');
\$sql = preg_replace('/--.*\n/', '', \$sql);
\$queries = explode(';', \$sql);
foreach (\$queries as \$q) {
    \$q = trim(\$q);
    if (empty(\$q)) continue;
    try { \$pdo->exec(\$q); } catch (PDOException \$e) { }
}
echo 'Migration complete!';
"
```

---

## 4. Database Architecture

### Tables (40 total)

| # | Table | Purpose |
|---|-------|---------|
| 1 | roles | User roles (Super Admin, Admin, Staff, Accountant) |
| 2 | permissions | System permissions (15 defined) |
| 3 | role_permissions | Role-permission pivot mapping |
| 4 | users | System users with login credentials |
| 5 | company_settings | Business configuration (GST, logo, bank, loyalty) |
| 6 | categories | Product categories |
| 7 | brands | Product brands |
| 8 | units | Measurement units (Pcs, Kg, Ltr, etc.) |
| 9 | products | Product catalog with SKU, HSN, pricing, stock |
| 10 | product_images | Multiple images per product |
| 11 | product_batches | Batch & expiry tracking |
| 12 | stock_transactions | Complete stock movement audit trail |
| 13 | suppliers | Supplier directory |
| 14 | supplier_payments | Payments to suppliers |
| 15 | purchases | Purchase orders from suppliers |
| 16 | purchase_items | Purchase order line items |
| 17 | customers | Customer CRM with loyalty points |
| 18 | customer_payments | Customer payment ledger |
| 19 | invoices | Sales invoices (GST breakdown, coupons, loyalty) |
| 20 | invoice_items | Invoice line items with HSN, CGST/SGST/IGST |
| 21 | invoice_payments | Split payment records per invoice |
| 22 | held_bills | Parked/held bills for later recall |
| 23 | quotations | Quotation/estimate documents |
| 24 | quotation_items | Quotation line items |
| 25 | challans | Delivery challans for goods dispatch |
| 26 | challan_items | Challan line items |
| 27 | coupons | Discount coupon/promo codes |
| 28 | loyalty_transactions | Customer loyalty points ledger |
| 29 | sales_returns | Sales return/credit note headers |
| 30 | sales_return_items | Sales return line items |
| 31 | purchase_returns | Purchase return/debit note headers |
| 32 | purchase_return_items | Purchase return line items |
| 33 | expense_categories | Expense classification |
| 34 | expenses | Business expense records |
| 35 | payments | Unified payment log (all transactions) |
| 36 | report_logs | Report generation audit |
| 37 | notifications | System alerts and notifications |
| 38 | activity_logs | User activity audit trail |
| 39 | login_logs | Login/logout history |
| 40 | backup_logs | Database backup records |

---

## 5. Module Guide

### 5.1 Dashboard (`/index.php`)
- **KPI Cards:** Today's Sales, Purchases, Expenses, Profit
- **Secondary KPIs:** Customers, Suppliers, Products, Low Stock, Overdue Invoices, Held Bills, Outstanding Receivables, Expiring Stock
- **Charts:** Daily Sales (7-day line), Monthly Revenue (6-month bar), Expenses by Category (doughnut), Top 5 Products (horizontal bar), Payment Mode Distribution (doughnut)
- **Tables:** Recent 5 Invoices, Recent 5 Payments
- **Quick Actions:** New Invoice, New Quotation, Day-End Report, Add Product

### 5.2 Inventory / Products (`/products/`)
- Full CRUD with categories, brands, units management
- HSN/SAC code support for GST compliance
- SKU and barcode fields (barcode scanning at POS)
- Cost price, selling price, GST percentage configuration
- Opening stock and minimum stock alerts
- Stock adjustment (IN/OUT) with transaction logging
- Barcode generation and label printing (`/barcode_print.php?id=X`)
- Batch tracking toggle per product
- Image upload support

### 5.3 POS Billing (`/billing/`)
- **POS Terminal** (`/billing/form.php`): Full-screen checkout interface
  - Barcode scan or search by name/SKU
  - Customer selection with quick-add modal
  - Invoice type: Retail, GST, Tax, Proforma
  - Cart with editable quantity, rate, discount per item
  - HSN code display on cart items
  - Auto GST calculation: CGST+SGST (intra-state) or IGST (inter-state)
  - Flat discount input
  - Coupon code application
  - Loyalty points display and redemption
  - Split payment (multiple payment methods per invoice)
  - Due date for credit sales
  - Invoice notes field
  - Hold Bill (F3) and Recall (F5) functionality
  - Sound feedback on item add
- **Invoice Directory** (`/billing/index.php`): DataTable listing of all invoices
- **Invoice View** (`/billing/view.php`): Detailed invoice with GST breakdown, split payments
- **Day-End Report** (`/billing/day_end.php`): Cash register reconciliation

### 5.4 Quotations (`/quotations/`)
- Create estimates/quotations for customers
- Cart-based form similar to POS
- Status workflow: DRAFT -> SENT -> ACCEPTED -> CONVERTED / REJECTED
- Convert accepted quotation to invoice (pre-fills POS)
- Print template with company branding
- WhatsApp sharing

### 5.5 Delivery Challans (`/challans/`)
- Dispatch goods without invoice
- Product selection with quantity only (no pricing)
- Transport name and vehicle number fields
- Status tracking: ACTIVE -> DELIVERED -> CANCELLED
- Print template with receiver signature area

### 5.6 Purchases (`/purchases/`)
- Create purchase orders from suppliers
- Cart-based form with cost price and GST
- Auto stock addition on purchase creation
- Payment status tracking (PAID/PARTIAL/UNPAID)
- Stock transaction logging

### 5.7 Returns (`/returns/`)
- **Sales Returns:** Credit notes for customer returns
  - Select original invoice, choose items and quantities to return
  - Auto stock replenishment
  - Sequential numbering: SR-YYYY-00001
- **Purchase Returns:** Debit notes for supplier returns
  - Select original purchase, return items to supplier
  - Auto stock deduction
  - Sequential numbering: PR-YYYY-00001

### 5.8 Customers (`/customers/`)
- Customer CRM with contact details, GST number, address
- Opening balance and credit limit management
- Loyalty points balance tracking
- Payment ledger and receivables tracking
- State field for CGST/SGST vs IGST determination

### 5.9 Suppliers (`/suppliers/`)
- Supplier directory with contact details, GST number
- Payment tracking and payables ledger
- Opening balance management

### 5.10 Expenses (`/expenses/`)
- Expense recording with category classification
- Payment method tracking
- Bill attachment support
- Category management (Rent, Electricity, Salary, etc.)

### 5.11 Reports (`/reports/`)
- **P&L Summary Cards:** Revenue, COGS, Expenses, Net Profit
- **Sales Ledger:** All invoices with filtering by date range
- **Stock Valuation:** Current inventory at cost and selling price
- **Expenses Report:** All expenses with category breakdown
- **Customer Receivables:** Outstanding balances per customer
- **Supplier Payables:** Outstanding balances per supplier
- **GST Report:** CGST/SGST/IGST breakdown with totals
- **Overdue Invoices:** Past-due invoices with days overdue
- **CSV Export:** All reports exportable to CSV

### 5.12 Settings (`/settings/`)
- **Company Details:** Business name, GST number, contact info, address
- **Invoice & Tax:** Invoice prefix, GST slabs, state code, footer, terms
- **Loyalty & Templates:** Loyalty toggle, points config, invoice template selection, thermal width
- **Bank Details:** Bank name, account number, IFSC, branch, UPI ID
- **Database Backups:** Create and download database snapshots

### 5.13 Users (`/users/`)
- User management with role assignment
- Login history and activity tracking
- Profile management

---

## 6. API Reference

All APIs follow the pattern: `GET/POST /api/{module}.php?action={action_name}`

Response format:
```json
{
  "status": true,
  "message": "Success message",
  "data": { }
}
```

### Billing API (`/api/billing.php`)
| Action | Method | Description |
|--------|--------|-------------|
| search_product | GET | Search products by name, SKU, or barcode |
| get_customers | GET | List active customers with state and loyalty points |
| create_invoice | POST | Create invoice with split payment, GST, coupons, loyalty |
| list_invoices | GET | List all invoices with customer names |
| day_end_report | GET | Day-end cash register summary |
| get_invoice_payments | GET | Get split payment records for an invoice |

### Held Bills API (`/api/held_bills.php`)
| Action | Method | Description |
|--------|--------|-------------|
| hold | POST | Park current cart as held bill |
| list | GET | List all active held bills |
| recall | POST | Recall and delete a held bill |
| delete | POST | Delete a held bill |

### Coupons API (`/api/coupons.php`)
| Action | Method | Description |
|--------|--------|-------------|
| list | GET | List all coupons |
| get | GET | Get single coupon details |
| save | POST | Create or update coupon |
| delete | POST | Soft-delete coupon |
| validate | POST | Validate coupon code against order amount |

### Loyalty API (`/api/loyalty.php`)
| Action | Method | Description |
|--------|--------|-------------|
| balance | GET | Get customer loyalty points balance |
| history | GET | Get loyalty transaction history |
| adjust | POST | Manual points adjustment |

### Quotations API (`/api/quotations.php`)
| Action | Method | Description |
|--------|--------|-------------|
| list | GET | List all quotations |
| get | GET | Get single quotation with items |
| save | POST | Create or update quotation |
| delete | POST | Soft-delete quotation |
| update_status | POST | Change quotation status |
| convert_to_invoice | POST | Mark as converted, return data for POS |

### Challans API (`/api/challans.php`)
| Action | Method | Description |
|--------|--------|-------------|
| list | GET | List all delivery challans |
| get | GET | Get single challan with items |
| save | POST | Create delivery challan |
| delete | POST | Soft-delete challan |
| update_status | POST | Update challan status |

### Products API (`/api/products.php`)
| Action | Method | Description |
|--------|--------|-------------|
| list | GET | List all products with categories/brands/units |
| get | GET | Get single product |
| save | POST | Create or update product (with HSN code) |
| delete | POST | Soft-delete product |
| adjust_stock | POST | Stock IN/OUT adjustment |
| categories_list | GET | List categories |
| category_save | POST | Create/update category |
| brands_list | GET | List brands |
| brand_save | POST | Create/update brand |
| units_list | GET | List units |
| unit_save | POST | Create/update unit |

### Dashboard API (`/api/dashboard.php`)
| Action | Method | Description |
|--------|--------|-------------|
| (default) | GET | All KPIs, charts, and recent activity data |

### Reports API (`/api/reports.php`)
| Action | Method | Description |
|--------|--------|-------------|
| summary | GET | P&L summary metrics |
| sales | GET | Sales invoice records (date filtered) |
| stock | GET | Stock valuation report |
| expenses | GET | Expense records (date filtered) |
| customers | GET | Customer receivables |
| suppliers | GET | Supplier payables |
| gst | GET | GST breakdown (CGST/SGST/IGST) |
| overdue | GET | Overdue invoices with days count |

---

## 7. Feature Documentation

### 7.1 Hold & Recall Bills
- **Hold (F3):** Saves current cart, customer, and invoice type to database. Clears POS for next customer.
- **Recall (F5):** Opens slide panel showing all held bills. Click to recall — restores cart and customer, removes from held list.
- **Use case:** Customer forgot wallet, needs to step away, or cashier serves multiple counters.

### 7.2 Split Payment
- Click "Split" button in checkout sidebar to add payment rows
- Each row: Payment method dropdown + Amount input
- Example: ₹500 Cash + ₹300 UPI + ₹200 Card = ₹1000 total
- Auto-fills remaining balance on new row
- Shows total paid vs change/balance due
- All payment rows recorded in `invoice_payments` table

### 7.3 GST Compliance (CGST/SGST/IGST)
- **Configuration:** Set company state code in Settings -> Invoice & Tax
- **Auto-detection:** Compares company state with customer state
  - Same state = Intra-state = CGST + SGST (each = GST rate / 2)
  - Different state = Inter-state = IGST (full GST rate)
  - Walk-in customer = defaults to intra-state
- **Invoice Print:** Shows HSN/SAC column, CGST/SGST or IGST columns with rate and amount
- **GST Report:** Aggregated CGST/SGST/IGST totals with per-invoice breakdown

### 7.4 Coupon System
- **Create Coupons:** Settings -> Manage Coupons (admin only)
  - Types: FLAT (fixed amount) or PERCENTAGE
  - Rules: Min order amount, max discount cap, validity dates, usage limit
- **Apply at POS:** Enter coupon code in checkout, validates via AJAX
- **Tracking:** Used count auto-increments, expired/depleted coupons rejected

### 7.5 Customer Loyalty Points
- **Enable:** Settings -> Loyalty & Templates -> Enable toggle
- **Configuration:** Points per ₹100 spent, ₹ value per point
- **Earning:** Auto-calculated on invoice creation (floor(total/100) * rate)
- **Redemption:** Toggle in POS checkout, enter points to redeem, converts to discount
- **Tracking:** Full transaction history (EARNED, REDEEMED, ADJUSTED) in `loyalty_transactions`

### 7.6 Day-End Report
- Access: POS & Billing -> Day-End Report button
- Date picker to view any day's summary
- Shows: Invoice count, total sales, received amount, net cash position
- Breakdowns: By payment mode, split payment summary, top 5 products, cashier-wise sales
- Deductions: Returns, expenses, outstanding dues
- Printable format

### 7.7 Print Templates
- **Professional A4** (`/invoice_print.php?id=X`): Branded header, HSN columns, CGST/SGST/IGST breakdown, bank details, split payments, signature area
- **Thermal Receipt** (`/invoice_thermal.php?id=X`): 80mm POS receipt, auto-print on load, compact layout
- **Quotation Print** (`/quotation_print.php?id=X`): Professional quotation with terms
- **Challan Print** (`/challan_print.php?id=X`): Goods dispatch document with receiver signature
- **Barcode Labels** (`/barcode_print.php?id=X&qty=12`): Printable barcode grid with product name and price

### 7.8 Barcode Generation
- Product view page shows barcode button
- Generates Code128 barcodes from SKU or barcode field
- Configurable label quantity (default 12)
- 3-column print grid for label sheets
- Uses JsBarcode library (CDN)

---

## 8. User Roles & Permissions

### Roles
| Role | Access Level |
|------|-------------|
| Super Admin | Full system access (bypasses all permission checks) |
| Admin | All operational modules + quotations, challans, coupons |
| Staff / Cashier | Dashboard, Inventory, Customers, Billing, Quotations, Challans, Day-End Report |
| Accountant | Dashboard, Expenses, Reports, Day-End Report |

### Permissions (15)
| # | Permission | Module |
|---|-----------|--------|
| 1 | Access Dashboard | dashboard |
| 2 | Manage Inventory | inventory |
| 3 | Manage Purchases | purchases |
| 4 | Manage Customers | customers |
| 5 | Manage Suppliers | suppliers |
| 6 | Create Invoice | billing |
| 7 | Manage Expenses | expenses |
| 8 | View Reports | reports |
| 9 | Manage Users | users |
| 10 | Manage Settings | settings |
| 11 | Run Backups | backups |
| 12 | Manage Quotations | quotations |
| 13 | Manage Challans | challans |
| 14 | Manage Coupons | coupons |
| 15 | View Day End Report | billing |

---

## 9. File Structure

```
invoices/
├── index.php                    # FastRoute front controller
├── server.php                   # PHP dev server entry
├── login.php                    # Authentication page
├── logout.php                   # Session destroy
├── .env                         # Database configuration
├── composer.json                # PHP dependencies
│
├── api/                         # AJAX API endpoints
│   ├── billing.php              # POS billing, invoices, day-end
│   ├── held_bills.php           # Hold & recall bills
│   ├── coupons.php              # Coupon management & validation
│   ├── loyalty.php              # Loyalty points
│   ├── quotations.php           # Quotation CRUD
│   ├── challans.php             # Delivery challan CRUD
│   ├── products.php             # Product & meta CRUD
│   ├── customers.php            # Customer CRM
│   ├── suppliers.php            # Supplier directory
│   ├── purchases.php            # Purchase orders
│   ├── returns.php              # Sales & purchase returns
│   ├── expenses.php             # Expense tracking
│   ├── reports.php              # Analytics & reports
│   ├── dashboard.php            # Dashboard KPIs & charts
│   ├── settings.php             # System configuration
│   ├── users.php                # User management
│   ├── search.php               # Global search
│   └── notifications.php        # Alert system
│
├── application/
│   ├── controllers/             # MVC Controllers
│   │   ├── BillingController.php
│   │   ├── QuotationController.php
│   │   ├── ChallanController.php
│   │   ├── ProductController.php
│   │   ├── PurchaseController.php
│   │   ├── CustomerController.php
│   │   ├── SupplierController.php
│   │   ├── ExpenseController.php
│   │   ├── ReturnController.php
│   │   ├── ReportsController.php
│   │   ├── DashboardController.php
│   │   ├── UserController.php
│   │   └── SettingsController.php
│   │
│   ├── models/                  # Core classes
│   │   ├── Database.php         # PDO wrapper (MySQL/SQLite)
│   │   ├── Auth.php             # Authentication & RBAC
│   │   └── Helpers.php          # CSRF, sanitization, logging
│   │
│   └── views/                   # PHP view templates
│       ├── header.php           # Layout header with sidebar
│       ├── footer.php           # Layout footer with scripts
│       ├── bottom_nav.php       # Mobile bottom navigation
│       ├── dashboard.php        # Dashboard view
│       ├── billing/             # POS & invoice views
│       ├── quotations/          # Quotation views
│       ├── challans/            # Challan views
│       ├── products/            # Product views
│       ├── purchases/           # Purchase views
│       ├── returns/             # Returns views
│       ├── customers/           # Customer views
│       ├── suppliers/           # Supplier views
│       ├── expenses/            # Expense views
│       ├── reports/             # Reports view
│       ├── users/               # User views
│       └── settings/            # Settings view
│
├── assets/
│   ├── css/style.css            # Complete stylesheet
│   └── js/
│       ├── billing.js           # POS terminal logic
│       └── dashboard.js         # Dashboard charts
│
├── database/
│   ├── schema.sql               # Complete schema (40 tables)
│   ├── seed.sql                 # Default data & permissions
│   ├── migration_v2.sql         # v1.0 -> v2.0 migration
│   └── migrate_returns.php      # Returns tables migration
│
├── invoice_print.php            # Professional A4 invoice
├── invoice_thermal.php          # Thermal POS receipt
├── quotation_print.php          # Quotation print template
├── challan_print.php            # Delivery challan print
├── barcode_print.php            # Barcode label printing
│
├── config/
│   ├── config.php               # App configuration
│   └── database.php             # DB connection config
│
├── {module}/                    # Route pass-through files
│   ├── index.php                # -> index.php router
│   ├── form.php                 # -> index.php router
│   └── view.php                 # -> index.php router
│
├── uploads/                     # File uploads
├── backups/                     # Database backups
└── logs/                        # Error logs
```

---

## 10. Keyboard Shortcuts (POS Terminal)

| Key | Action |
|-----|--------|
| F2 | Focus product search bar |
| F3 | Hold current bill |
| F4 | Generate/save invoice |
| F5 | Toggle held bills panel |
| F6 | Print last generated invoice |
| ESC | Cancel checkout / close modal |
| Enter | (in search) Add first matching product or show results |

---

## Security Features

- CSRF token protection on all POST operations
- Session-based authentication with 30-minute timeout
- Rate limiting: 5 failed login attempts = 5-minute lockout
- Input sanitization (XSS prevention via `htmlspecialchars`)
- Prepared statements (SQL injection prevention)
- Soft deletes (data recovery possible)
- Activity logging for audit trail
- Role-based access control with permission checks
- HTTP-only session cookies
- Secure cookie flag on HTTPS

---

## Invoice Numbering

| Document | Format | Example |
|----------|--------|---------|
| Invoice | {PREFIX}{YEAR}-{SEQ} | INV-2026-00001 |
| Quotation | QT-{YEAR}-{SEQ} | QT-2026-00001 |
| Delivery Challan | DC-{YEAR}-{SEQ} | DC-2026-00001 |
| Purchase Order | PO-{YEAR}-{SEQ} | PO-2026-00001 |
| Sales Return | SR-{YEAR}-{SEQ} | SR-2026-00001 |
| Purchase Return | PR-{YEAR}-{SEQ} | PR-2026-00001 |

---

*Document generated for Grovixo IIMS v2.0 - June 2026*
