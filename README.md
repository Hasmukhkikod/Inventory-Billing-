# Grovixo IIMS v2.0

**Invoice & Inventory Management System** — A complete billing, inventory, and business management platform for Indian businesses.

Built with PHP 8+, Bootstrap 5, jQuery, Chart.js, and SQLite/MySQL.

---

## Features

**POS Billing**
- Barcode scan / search by name, SKU
- Hold & Recall bills (F3/F5) for multi-customer checkout
- Split payment (Cash + UPI + Card on same invoice)
- GST compliance: auto CGST/SGST (intra-state) or IGST (inter-state)
- HSN/SAC code support on products and invoices
- Discount coupons with validation rules
- Customer loyalty points (earn & redeem)
- Keyboard shortcuts (F2-F6, ESC)

**Modules**
- Inventory with categories, brands, units, barcode generation
- Purchase orders from suppliers with stock auto-update
- Quotations/Estimates with convert-to-invoice
- Delivery challans for goods dispatch
- Sales & Purchase returns with credit/debit notes
- Customer CRM with ledgers, credit limits, loyalty
- Supplier directory with payables tracking
- Expense tracking with categories
- User management with 4 roles, 15 permissions

**Reports & Analytics**
- Dashboard with KPI cards, charts, top products
- P&L summary, sales ledger, stock valuation
- GST report (CGST/SGST/IGST breakdown)
- Overdue invoices report
- Customer receivables & supplier payables
- Day-end cash register reconciliation
- CSV export on all reports

**Print Templates**
- Professional A4 invoice (branded, GST columns, bank details)
- Thermal 80mm POS receipt (auto-print)
- Quotation & Delivery Challan print
- Barcode label printing (Code128, configurable grid)
- WhatsApp invoice sharing

---

## Quick Start

```bash
# 1. Clone
git clone https://github.com/Hasmukhkikod/Inventory-Billing-.git
cd Inventory-Billing-

# 2. Install dependencies
composer install

# 3. Configure database
cp .env.example .env
# Edit .env: set DB_DRIVER=sqlite (or mysql with credentials)

# 4. Run
php -S localhost:8000 server.php

# 5. Open http://localhost:8000
```

**Default login:** `hasmukhkikod@gmail.com` / `admin123`

Database tables and seed data are auto-created on first run.

---

## Desktop Distribution

For clients who need a one-click desktop install (no XAMPP/MySQL):

1. Copy project to a Windows PC
2. Run `desktop/BUILD.bat`
3. Send the generated ZIP to client
4. Client extracts and double-clicks "Start Grovixo.bat"

See `desktop/BUILD_INSTRUCTIONS.txt` for details.

---

## Tech Stack

| Component | Technology |
|-----------|-----------|
| Backend | PHP 8.0+ |
| Database | MySQL 5.7+ / SQLite 3 |
| Routing | nikic/fast-route |
| Frontend | Bootstrap 5.3, jQuery 3.6 |
| Charts | Chart.js |
| Tables | DataTables 1.13 |
| Alerts | SweetAlert2 |
| Icons | Font Awesome 6.4 |
| Barcodes | JsBarcode |

---

## Project Structure

```
├── api/                    # 18 AJAX API endpoints
├── application/
│   ├── controllers/        # 13 MVC controllers
│   ├── models/             # Database, Auth, Helpers
│   └── views/              # PHP view templates (13 modules)
├── assets/                 # CSS, JS
├── database/               # Schema, seed, migrations
├── desktop/                # Client distribution scripts
├── config/                 # App & database config
├── invoice_print.php       # A4 invoice template
├── invoice_thermal.php     # Thermal receipt template
├── quotation_print.php     # Quotation print
├── challan_print.php       # Delivery challan print
├── barcode_print.php       # Barcode label printing
└── DOCUMENTATION.md        # Complete system documentation
```

---

## License

All rights reserved. Grovixo IIMS v2.0.
