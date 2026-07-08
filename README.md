# Grovixo Invoice & Inventory Management System (IIMS) v2.0

A complete, modern, and highly responsive **Billing & Inventory Management System** built with a robust PHP backend and a dynamic Bootstrap 5 interface.

---

## 🌟 Key Features

* **POS & Billing:** Lightning-fast POS terminal with barcode scanning, bill holding, split payments, and thermal printer integration.
* **Full Sales Cycle:** Manage Quotations, Delivery Challans, Sales Returns, and Final Invoices.
* **GST & Tax Compliance:** Built-in support for CGST/SGST/IGST, HSN codes, and customizable tax slabs.
* **Inventory Management:** Real-time stock tracking, automated deductions upon sale, and stock alerts.
* **Purchases & Suppliers:** Track supplier invoices, manage payments, and automatically update inventory on purchase.
* **CRM & Loyalty:** Customer management with integrated loyalty points, redemption rules, and discount coupon codes.
* **Financial Reports:** Comprehensive reporting including Sales, P&L, GST Returns, Stock valuation, and Overdue payments.
* **Multi-User & Security:** Deep Role-Based Access Control (RBAC) to restrict staff permissions securely.
* **System Settings:** Fully customizable branding (logo, business name, invoice prefixes, themes).
* **Automated Backups:** One-click database backups straight from the settings panel.
* **Multi-Platform:** Fully responsive mobile-first UI for mobile, tablet, and desktop usage.

---

## 💻 Tech Stack

* **Backend:** PHP 8.0+ (Custom MVC-like routing architecture)
* **Database:** MySQL / MariaDB (Primary) or SQLite (Portable fallback)
* **Frontend UI:** Bootstrap 5, Custom Vanilla CSS, HTML5
* **Frontend Logic:** jQuery, AJAX (for seamless transitions without page reloads)
* **Libraries:** Chart.js (Analytics), DataTables (Grids), SweetAlert2 (Popups), Select2 (Searchable Dropdowns)

---

## 📂 Project Structure

```text
/invoices
├── api/                  # Backend AJAX endpoints (Business Logic & Database interactions)
├── application/
│   ├── Models/           # Database wrapper, Auth classes, and Helper utilities
│   └── views/            # Frontend UI templates (HTML/PHP)
├── assets/               # CSS, JS, and Images
├── backups/              # Auto-generated database SQL dumps
├── config/               # System configuration & Database fallbacks
├── database/             # SQLite portable database & SQL schemas
├── uploads/              # Uploaded logos, profile pictures, and attachments
└── .env                  # Local Environment Variables
```

---

## 🚀 Installation & Deployment

### Option 1: Local Standalone (Windows PC)
Ideal for offline shop installations. No technical setup required.
1. Download and extract the project.
2. Double-click **`START.bat`**.
3. The server will start and your browser will open automatically. Data is stored locally.

### Option 2: Local Development (Mac / Linux)
Ideal for developers modifying the codebase.
1. Install dependencies: `composer install`
2. Create your environment file: `cp .env.example .env`
3. Configure your local MySQL credentials in the `.env` file.
4. Run the built-in server:
   ```bash
   php -S localhost:8000 server.php
   ```

### Option 3: Production Web Hosting (Hostinger / cPanel)
Ideal for cloud access across multiple branches.
1. Create a MySQL database and user in your hosting control panel.
2. Import the `dummy_data_hostinger.sql` (or `schema.sql`) file into your new database via phpMyAdmin.
3. Upload the project files to your `public_html` directory.
4. **Important:** By default, `.env` files are ignored by git. We have configured `config/database.php` to automatically detect your production domain (e.g., `billingdemo.grovixo.com`) and apply your production database credentials securely, ignoring local variables.

---

## 🔐 Default Credentials

After initial installation/import, you can log in to the Super Admin account using:
* **Email:** `admin@admin.com`
* **Password:** `123456`

*(Please change this password immediately in the User Settings after your first login).*

---

Grovixo IIMS v2.0 — All rights reserved.
