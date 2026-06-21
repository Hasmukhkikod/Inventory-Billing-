# Grovixo IIMS v2.0

**Billing & Inventory Management System**

---

## How to Use (Windows PC)

**Step 1:** Download this project (Code > Download ZIP) and extract it.

**Step 2:** Double-click **`START.bat`**

**Step 3:** Browser opens automatically. Login and use.

That's it. No XAMPP, no MySQL, no setup needed. Everything installs automatically on first run.

---

## Features

- POS Billing with barcode scanning
- Hold & Recall bills, Split payments
- GST compliant (CGST/SGST/IGST + HSN codes)
- Quotations, Delivery Challans
- Inventory management with stock tracking
- Customer CRM with loyalty points
- Discount coupons / promo codes
- Reports: Sales, P&L, GST, Stock, Overdue
- Day-end cash register report
- Print: A4 invoice, Thermal receipt, Barcodes
- WhatsApp invoice sharing
- Multi-user with role-based access
- Company branding (logo + name in settings)
- Works offline — data stored locally

---

## For Developers

```bash
composer install
cp .env.example .env   # Set DB_DRIVER=sqlite or mysql
php -S localhost:8000 server.php
```

See [DOCUMENTATION.md](DOCUMENTATION.md) for complete system docs.

---

## Tech Stack

PHP 8+ | Bootstrap 5 | jQuery | Chart.js | SQLite/MySQL | DataTables | SweetAlert2

---

Grovixo IIMS v2.0 — All rights reserved.
