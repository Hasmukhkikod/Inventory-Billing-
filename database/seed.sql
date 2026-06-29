-- Seed Data for Invoice & Inventory Management System (IIMS Part 2)

-- 1. Seed Roles
INSERT IGNORE INTO roles (id, role_name, description, status) VALUES
(1, 'Super Admin', 'Full system access, user management, and settings configuration', 'ACTIVE'),
(2, 'Admin', 'Access to products, invoices, expenses, customers, suppliers, and reports', 'ACTIVE'),
(3, 'Staff / Cashier', 'Access to billing, products, and customer directories', 'ACTIVE'),
(4, 'Accountant', 'Access to expenses, financial entries, and reports', 'ACTIVE');

-- 2. Seed Permissions
INSERT IGNORE INTO permissions (id, permission_name, module, status) VALUES
(1, 'Access Dashboard', 'dashboard', 'ACTIVE'),
(2, 'Manage Inventory', 'inventory', 'ACTIVE'),
(3, 'Manage Purchases', 'purchases', 'ACTIVE'),
(4, 'Manage Customers', 'customers', 'ACTIVE'),
(5, 'Manage Suppliers', 'suppliers', 'ACTIVE'),
(6, 'Create Invoice', 'billing', 'ACTIVE'),
(7, 'Manage Expenses', 'expenses', 'ACTIVE'),
(8, 'View Reports', 'reports', 'ACTIVE'),
(9, 'Manage Users', 'users', 'ACTIVE'),
(10, 'Manage Settings', 'settings', 'ACTIVE'),
(11, 'Run Backups', 'backups', 'ACTIVE'),
(12, 'Manage Quotations', 'quotations', 'ACTIVE'),
(13, 'Manage Challans', 'challans', 'ACTIVE'),
(14, 'Manage Coupons', 'coupons', 'ACTIVE'),
(15, 'View Day End Report', 'billing', 'ACTIVE');

-- 3. Seed Role Permissions Pivot Table
INSERT IGNORE INTO role_permissions (role_id, permission_id, status) VALUES
-- Super Admin (All Permissions 1 to 15)
(1, 1, 'ACTIVE'), (1, 2, 'ACTIVE'), (1, 3, 'ACTIVE'), (1, 4, 'ACTIVE'), (1, 5, 'ACTIVE'),
(1, 6, 'ACTIVE'), (1, 7, 'ACTIVE'), (1, 8, 'ACTIVE'), (1, 9, 'ACTIVE'), (1, 10, 'ACTIVE'), (1, 11, 'ACTIVE'),
(1, 12, 'ACTIVE'), (1, 13, 'ACTIVE'), (1, 14, 'ACTIVE'), (1, 15, 'ACTIVE'),
-- Admin (Permissions 1-8, 12-15)
(2, 1, 'ACTIVE'), (2, 2, 'ACTIVE'), (2, 3, 'ACTIVE'), (2, 4, 'ACTIVE'), (2, 5, 'ACTIVE'),
(2, 6, 'ACTIVE'), (2, 7, 'ACTIVE'), (2, 8, 'ACTIVE'), (2, 12, 'ACTIVE'), (2, 13, 'ACTIVE'), (2, 14, 'ACTIVE'), (2, 15, 'ACTIVE'),
-- Staff / Cashier (Permissions 1, 2, 4, 6, 12, 13, 15)
(3, 1, 'ACTIVE'), (3, 2, 'ACTIVE'), (3, 4, 'ACTIVE'), (3, 6, 'ACTIVE'), (3, 12, 'ACTIVE'), (3, 13, 'ACTIVE'), (3, 15, 'ACTIVE'),
-- Accountant (Permissions 1, 7, 8, 15)
(4, 1, 'ACTIVE'), (4, 7, 'ACTIVE'), (4, 8, 'ACTIVE'), (4, 15, 'ACTIVE');

-- 4. Seed default Super Admin User (Password is 'admin123')
-- Logs in via Email: hasmukhkikod@gmail.com
INSERT IGNORE INTO users (id, role_id, name, email, mobile, password, status) VALUES
(1, 1, 'Super Admin User', 'hasmukhkikod@gmail.com', '9876543210', '$2y$12$QxctayrdzNTNi9RUa3sHc.3fym5z8YyTNCebwIjYHsG0VrI163.ue', 'ACTIVE');

-- 5. Seed default Product Categories
INSERT IGNORE INTO categories (id, category_name, description, status) VALUES
(1, 'Electronics', 'Phones, laptops, chargers, and general electronic components', 'ACTIVE'),
(2, 'Grocery', 'Daily grocery items, food, and home cleaning products', 'ACTIVE'),
(3, 'Clothing', 'Apparel, garments, shoes, and lifestyle products', 'ACTIVE'),
(4, 'Hardware', 'Tools, fixtures, electrical items, and plumbing materials', 'ACTIVE'),
(5, 'Medicines', 'Prescription drugs, vitamins, and healthcare items', 'ACTIVE');

-- 6. Seed default Brands
INSERT IGNORE INTO brands (id, brand_name, description, status) VALUES
(1, 'Samsung', 'Samsung Electronics', 'ACTIVE'),
(2, 'LG', 'LG Corporation', 'ACTIVE'),
(3, 'Sony', 'Sony Group', 'ACTIVE'),
(4, 'Apple', 'Apple Inc', 'ACTIVE'),
(5, 'Nike', 'Nike Inc', 'ACTIVE'),
(6, 'Generic', 'Generic brand', 'ACTIVE');

-- 7. Seed default Units
INSERT IGNORE INTO units (id, unit_name, short_name, status) VALUES
(1, 'Pieces', 'Pcs', 'ACTIVE'),
(2, 'Box', 'Box', 'ACTIVE'),
(3, 'Kilograms', 'Kg', 'ACTIVE'),
(4, 'Liters', 'Ltr', 'ACTIVE'),
(5, 'Meters', 'Mtr', 'ACTIVE');

-- 8. Seed default Expense Categories
INSERT IGNORE INTO expense_categories (id, category_name, description, status) VALUES
(1, 'Rent', 'Office or shop rent', 'ACTIVE'),
(2, 'Electricity', 'Electricity utilities bills', 'ACTIVE'),
(3, 'Salary', 'Employees salary', 'ACTIVE'),
(4, 'Internet', 'Office broadband bills', 'ACTIVE'),
(5, 'Fuel', 'Vehicle fuel costs', 'ACTIVE'),
(6, 'Office Expense', 'Stationery, coffee, printing', 'ACTIVE'),
(7, 'Maintenance', 'Repairs and equipment service', 'ACTIVE');

-- 9. Seed Company Settings
INSERT IGNORE INTO company_settings (id, company_name, company_logo, gst_number, phone, email, website, address, city, state, country, pincode, invoice_prefix, invoice_start, invoice_end, quotation_prefix, quotation_start, quotation_end, purchase_prefix, purchase_start, purchase_end, challan_prefix, challan_start, challan_end, currency, timezone, gst_slabs, invoice_footer, invoice_terms, status, state_code, loyalty_enabled, loyalty_points_per_100, loyalty_redeem_value, thermal_width, invoice_template, bank_name, bank_account_no, bank_ifsc, bank_branch, upi_id) VALUES
(1, 'Grovixo', '', '27AAAAA1234A1Z5', '+91 98765 43210', 'info@grovixo.com', 'www.grovixo.com', '404 Premium Business Tower, Senapati Bapat Marg, Lower Parel', 'Mumbai', 'Maharashtra', 'India', '400013', 'INV-', 1, 99999, 'QT-', 1, 99999, 'PO-', 1, 99999, 'DC-', 1, 99999, 'INR', 'Asia/Kolkata', '0,5,12,18,28', 'Thank you for your business!', '1. Goods once sold will not be taken back or exchanged.\n2. Subject to Mumbai Jurisdiction.', 'ACTIVE', '27', 0, 1, 1.00, '80mm', 'standard', '', '', '', '', '');
