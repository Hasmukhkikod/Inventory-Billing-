# Grovixo - Invoice & Inventory Management System (IIMS)

Grovixo is a modern, responsive, and secure **Invoice & Inventory Management System (IIMS)**. Built using PHP, PDO, Bootstrap 5, and jQuery, Grovixo provides a comprehensive solution for managing inventory tracking, real-time POS billing, sales/purchase ledgers, expense tracking, and system configuration.

---

## 1. System Architecture & Tech Stack

Grovixo uses an organized Model-View-Controller (MVC) structure to separate concern areas:
* **Core Language**: PHP (support for PHP 8+)
* **Database Layer**: PDO wrapper supporting **MySQL** (primary) and **SQLite** (automatic fallback and testing database).
* **Front-end UI**: Bootstrap 5, Vanilla CSS, FontAwesome 6, and Outfit & Inter typography.
* **Interactions**: jQuery, DataTables (interactive client-side listings), Chart.js (dashboard visualizations), and SweetAlert 2 (modern popups).
* **Backup Engine**: Automated database dump generation for disaster recovery.

---

## 2. Key Modules & Features

### 🔐 Authentication & Role-Based Access Control (RBAC)
* **Secure Login**: Password encryption using bcrypt (`PASSWORD_BCRYPT`).
* **Active Session Management**: Auto-logout after 30 minutes of inactivity.
* **Role Permissions**: Dynamic database-driven access control. Predefined roles include:
  1. **Super Admin**: Complete control (User management, settings, backups).
  2. **Admin**: Inventory, invoicing, custom CRM, and billing logs.
  3. **Staff / Cashier**: Fast POS checkout and customer lists.
  4. **Accountant**: Financial statements, expenses, and analytics reports.

### 📊 Performance Dashboard
* **KPI Metric Cards**: Real-time sales, purchases, expenses, and net profit calculations.
* **Analytics Trends**: 
  * Weekly revenue trends chart (Line Chart).
  * 30-day expense breakdown by category (Pie Chart).
  * 6-month monthly sales trend comparison (Bar Chart).
* **KPI Trackers**: Real-time counters showing total active customers, suppliers, active inventory SKU count, and critical low-stock items alerts.

### 📦 Inventory & Stock Adjustments
* **Detailed Inventory**: Standard management of SKU, EAN/UPC barcode, brands, measurement units, purchase/cost pricing, GST tax slabs (0%, 5%, 12%, 18%, 28%), and low-stock limits.
* **Audit Trail Ledger**: Interactive stock adjustments (stock increases, decreases, damages, expirations, or losses) logged for audit trails.

### 🛒 Real-time Billing POS Terminal
* **Interactive Cart**: Search items by name, barcode scan, or SKU.
* **Automatic Tax Slabs & Discounts**: Auto-calculates base pricing, row-level tax rate calculations, flat transaction discounts, and round-offs.
* **Customer Ledgers**: Walk-in checkout or mapping to outstanding credit accounts.
* **Printable Invoices**: Fully styled invoices optimized for paper and POS thermal printer output.

---

## 3. Responsive Web Design Strategy

Grovixo implements a custom mobile and tablet responsive interface layout:

* **Slide-In Navigation Drawer**: On tablet and mobile viewports (`< 992px`), the sidebar collapses offscreen and slides in smoothly with a glassmorphism blurred backdrop overlay (`.sidebar-backdrop`).
* **Flexible Bottom Navigation Bar**: Immediate touch access to high-frequency actions (Dashboard, Inventory, POS Terminal, CRM) and a menu toggle button to slide in the navigation drawer.
* **Auto-Generated Table Cards**: On mobile screens, long horizontal tables are dynamically transformed into stacked card layouts via an automatic JS injector in `footer.php`. This inserts descriptive labels before cell contents on-the-fly.
* **Fluid Search & Inputs**: All inputs and search blocks adapt fluidly, collapsing/expanding according to screen dimensions.

---

## 4. Database Schema Setup

The system handles database initialization and migrations automatically on startup. The SQL schemas are detailed inside [database/schema.sql](file:///Users/hasmukh/dev/invoices/database/schema.sql) and default values inside [database/seed.sql](file:///Users/hasmukh/dev/invoices/database/seed.sql).

### Key System Entities:
* `users` / `roles` / `permissions` / `role_permissions`
* `products` / `categories` / `brands` / `units`
* `stock_transactions` (Stock correction ledgers)
* `customers` / `customer_payments` / `invoices` / `invoice_items`
* `suppliers` / `supplier_payments` / `purchases` / `purchase_items`
* `expenses` / `expense_categories` / `payments`
* `login_logs` / `activity_logs` / `backup_logs`

---

## 5. Local Setup & Credentials

### Installation
1. Ensure a PHP development server and MySQL are installed.
2. Clone the repository inside your local directory.
3. Configure your database details inside [config/database.php](file:///Users/hasmukh/dev/invoices/config/database.php) (MySQL port, username, password).
4. Run the local development server:
   ```bash
   php -S localhost:8000
   ```
5. Open your web browser and navigate to `http://localhost:8000/login.php`.

### Default Administrator Credentials
* **Email:** `hasmukhkikod@gmail.com`
* **Password:** `admin123`

---

## 6. Testing & Verifications

The system includes automated tests suite under the `tests/` directory:
* [tests/run_tests.php](file:///Users/hasmukh/dev/invoices/tests/run_tests.php): Validates security configurations, database connections, schema structures, password decryptions, XSS filters, and CSRF token protections.
* [tests/verify_flow.php](file:///Users/hasmukh/dev/invoices/tests/verify_flow.php): Simulates headless E2E flow calculations (stock adjustments, checkout totals, sales revenue, and COGS/profit accuracy checks).

To execute the test suite:
```bash
php tests/run_tests.php
php tests/verify_flow.php
```
