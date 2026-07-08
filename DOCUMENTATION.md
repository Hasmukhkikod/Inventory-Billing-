# Grovixo Invoice & Inventory Management System (IIMS) v2.0
## Complete System Documentation

Welcome to the comprehensive documentation for the Grovixo IIMS v2.0. This document covers the entire system architecture, feature modules, operational workflows, and technical details.

---

### Table of Contents
1. [System Overview](#1-system-overview)
2. [Technical Architecture](#2-technical-architecture)
3. [User Roles & Permissions (RBAC)](#3-user-roles--permissions-rbac)
4. [Module Breakdown & Features](#4-module-breakdown--features)
5. [Operational Workflows](#5-operational-workflows)
6. [Security & Backups](#6-security--backups)

---

### 1. System Overview
Grovixo IIMS is a modern, web-based software solution designed to handle end-to-end retail and wholesale business operations. It acts as a centralized hub for point-of-sale (POS) billing, inventory tracking, customer relationship management (CRM), and financial reporting.

The system is designed with a **mobile-first, highly responsive interface**, meaning it functions flawlessly on smartphones, tablets, and desktop computers.

---

### 2. Technical Architecture
The system is built on a custom, lightweight MVC-like architecture. It avoids heavy frameworks, relying on standard, fast-executing PHP logic.

* **Backend:** Vanilla PHP 8.0+
* **Database Layer:** Custom PDO wrapper (`Database.php`) supporting dynamic connections to **MySQL/MariaDB** (for cloud/production) and **SQLite** (for portable standalone use).
* **Frontend:** 
  * **HTML/CSS:** Bootstrap 5 with extensive custom Vanilla CSS overrides for a premium, glassmorphic dark/light UI.
  * **Javascript:** jQuery for DOM manipulation, AJAX for seamless background data fetching without page reloads.
* **Key Libraries:**
  * `Chart.js` for dashboard analytics.
  * `DataTables` for dynamic, searchable, and paginated data grids.
  * `SweetAlert2` for elegant, non-intrusive notifications and confirmation popups.
  * `Select2` for advanced, searchable dropdown menus.

---

### 3. User Roles & Permissions (RBAC)
The system utilizes a strict Role-Based Access Control (RBAC) architecture. Every endpoint and UI button is protected by permission checks.

* **Super Admin:** Hardcoded fallback account with absolute access. Cannot be deleted.
* **Custom Roles:** Business owners can create custom roles (e.g., "Cashier", "Manager", "Stock Clerk").
* **Granular Permissions:** Over 15 specific permissions can be toggled on/off, such as:
  * *Create Invoice*, *Manage Inventory*, *View Reports*, *Manage Settings*, *Run Backups*, etc.
* **Enforcement:** If a user attempts to access a restricted module, the backend instantly rejects the request and the frontend gracefully handles the denial with a warning popup.

---

### 4. Module Breakdown & Features

#### 4.1. Dashboard
* Provides a real-time, birds-eye view of business health.
* Displays Today's Sales, Weekly/Monthly revenue trends (via line charts), low stock alerts, and recent transactions.

#### 4.2. Point of Sale (POS) / Billing
* **Barcode Scanning:** Optimized for instant product lookup via external barcode scanners.
* **Hold & Recall:** Allows cashiers to "park" a bill for a customer, ring up the next person, and "recall" the original bill later.
* **Split Payments:** Customers can pay portions of a bill using different methods (e.g., Cash + UPI + Card).
* **Taxation:** Fully automatic SGST/CGST/IGST calculation based on customer state code and product tax slabs.
* **Printing:** Generates both standard A4 Invoices and 80mm Thermal Receipts.

#### 4.3. Inventory Management (Products)
* **Categorization:** Products are organized by Categories and Brands.
* **Unit Conversions:** Supports multiple measuring units (e.g., buying in "Boxes", selling in "Pieces").
* **Stock Tracking:** Real-time stock increments (on purchase) and deductions (on sale).

#### 4.4. CRM & Customer Loyalty
* **Customer Directory:** Tracks individual customer balances, total spending, and contact details.
* **Loyalty Program:** Customers earn customizable points per ₹100 spent, which can be redeemed on future invoices for discounts.
* **Coupons:** Generate promotional discount codes (Percentage or Flat rate) with expiration dates.

#### 4.5. Purchases & Suppliers
* **Supplier Ledger:** Manage supplier details and track outstanding payments (Accounts Payable).
* **Purchase Orders:** Digitally log incoming stock. Stock quantities automatically adjust when a purchase is finalized.

#### 4.6. Quotations & Delivery Challans
* Generate pre-sale estimates (Quotations) that do not affect stock.
* Convert Quotations directly into live Invoices with a single click.
* Generate Delivery Challans for shipping tracking.

#### 4.7. Financial Reports
* Comprehensive, printable reporting modules.
* **Available Reports:** Sales Report, Profit & Loss (P&L), Expense Report, GST Tax Returns, Low Stock Report, and Customer Ledger.

#### 4.8. Expenses
* Log day-to-day business expenses (Rent, Electricity, Salary) organized by categories.
* Expense amounts are automatically deducted from the Profit & Loss report.

---

### 5. Operational Workflows

#### Typical Sales Workflow
1. Cashier opens the **Billing** terminal.
2. Selects a customer (or leaves as Walk-in).
3. Scans product barcodes or uses the smart search to add items to the cart.
4. (Optional) Applies a Coupon Code or redeems Customer Loyalty points.
5. Clicks "Checkout", records the payment method, and prints the Thermal Receipt.
6. System automatically deducts the sold stock from inventory and logs the financial transaction.

#### Typical Restock Workflow
1. Manager opens the **Purchases** module.
2. Selects a Supplier and adds incoming products.
3. Submits the Purchase Invoice.
4. The system automatically credits the stock quantities in the inventory database and logs the financial obligation to the supplier.

---

### 6. Security & Backups

#### Security
* All AJAX data submissions are protected by **CSRF Tokens**.
* All database queries utilize **PDO Prepared Statements** to absolutely prevent SQL injection attacks.
* Passwords are securely hashed using PHP's native `password_hash()` (BCRYPT).

#### Backups
* The system features an integrated "Data & Backups" panel in the Settings module.
* With a single click, administrators can generate and download a complete `.sql` dump of the entire database.
* This ensures that business owners always have full ownership and physical possession of their data, safeguarding against data loss.
