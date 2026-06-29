-- ================================================================
-- Grovixo IIMS - Full MySQL Installation Script
-- Version: 3.0 (Complete Schema + Seed + Demo Data)
-- Usage: Paste this directly into phpMyAdmin or MySQL CLI
-- ================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ================= TABLE SCHEMA =================

-- 1. Roles
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Permissions
CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    permission_name VARCHAR(100) NOT NULL UNIQUE,
    module VARCHAR(50) NOT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Role Permissions Pivot
CREATE TABLE IF NOT EXISTS role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mobile VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    profile_image VARCHAR(255) NULL,
    last_login TIMESTAMP NULL DEFAULT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Company Settings
CREATE TABLE IF NOT EXISTS company_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(150) NOT NULL,
    company_logo VARCHAR(255) NULL,
    gst_number VARCHAR(20) NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    website VARCHAR(100) NULL,
    address TEXT NULL,
    city VARCHAR(50) NULL,
    state VARCHAR(50) NULL,
    country VARCHAR(50) NULL,
    pincode VARCHAR(15) NULL,
    invoice_prefix VARCHAR(20) DEFAULT 'INV-',
    invoice_start INT DEFAULT 1,
    invoice_end INT DEFAULT 99999,
    quotation_prefix VARCHAR(20) DEFAULT 'QT-',
    quotation_start INT DEFAULT 1,
    quotation_end INT DEFAULT 99999,
    purchase_prefix VARCHAR(20) DEFAULT 'PO-',
    purchase_start INT DEFAULT 1,
    purchase_end INT DEFAULT 99999,
    challan_prefix VARCHAR(20) DEFAULT 'DC-',
    challan_start INT DEFAULT 1,
    challan_end INT DEFAULT 99999,
    currency VARCHAR(10) DEFAULT 'INR',
    timezone VARCHAR(50) DEFAULT 'Asia/Kolkata',
    gst_slabs VARCHAR(100) DEFAULT '0,5,12,18,28',
    invoice_footer VARCHAR(255) DEFAULT 'Thank you for your business!',
    invoice_terms TEXT NULL,
    loyalty_enabled TINYINT DEFAULT 0,
    loyalty_points_per_100 INT DEFAULT 1,
    loyalty_redeem_value DECIMAL(5, 2) DEFAULT 1.00,
    thermal_width VARCHAR(10) DEFAULT '80mm',
    invoice_template VARCHAR(20) DEFAULT 'standard',
    bank_name VARCHAR(100) NULL,
    bank_account_no VARCHAR(50) NULL,
    bank_ifsc VARCHAR(20) NULL,
    bank_branch VARCHAR(100) NULL,
    upi_id VARCHAR(100) NULL,
    state_code VARCHAR(5) NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Categories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    image VARCHAR(255) NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Brands
CREATE TABLE IF NOT EXISTS brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Units
CREATE TABLE IF NOT EXISTS units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_name VARCHAR(50) NOT NULL UNIQUE,
    short_name VARCHAR(10) NOT NULL UNIQUE,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8b. Unit Conversions
CREATE TABLE IF NOT EXISTS unit_conversions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    primary_unit_id INT NOT NULL,
    secondary_unit_id INT NOT NULL,
    conversion_factor DECIMAL(15,4) NOT NULL DEFAULT 1.0000,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (primary_unit_id) REFERENCES units(id) ON DELETE CASCADE,
    FOREIGN KEY (secondary_unit_id) REFERENCES units(id) ON DELETE CASCADE,
    UNIQUE(primary_unit_id, secondary_unit_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Products
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NULL,
    brand_id INT NULL,
    unit_id INT NULL,
    secondary_unit_id INT NULL,
    conversion_factor DECIMAL(15,4) NULL,
    sku VARCHAR(100) NOT NULL UNIQUE,
    barcode VARCHAR(100) NULL UNIQUE,
    hsn_code VARCHAR(20) NULL,
    product_name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    cost_price DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    selling_price DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    gst_percentage DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    opening_stock DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    current_stock DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    minimum_stock DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    batch_tracking TINYINT DEFAULT 0,
    image VARCHAR(255) NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE SET NULL,
    FOREIGN KEY (secondary_unit_id) REFERENCES units(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Product Images
CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. Stock Transactions
CREATE TABLE IF NOT EXISTS stock_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    transaction_type VARCHAR(50) NOT NULL,
    reference_no VARCHAR(100) NULL,
    quantity DECIMAL(15, 2) NOT NULL,
    stock_before DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    stock_after DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    remarks TEXT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. Suppliers
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100) NULL,
    mobile VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(100) NULL,
    gst_number VARCHAR(20) NULL,
    address TEXT NULL,
    city VARCHAR(50) NULL,
    state VARCHAR(50) NULL,
    country VARCHAR(50) NULL,
    opening_balance DECIMAL(15, 2) DEFAULT 0.00,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 13. Supplier Payments
CREATE TABLE IF NOT EXISTS supplier_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    payment_date DATE NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    reference_no VARCHAR(100) NULL,
    notes TEXT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 14. Purchases
CREATE TABLE IF NOT EXISTS purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_no VARCHAR(50) NOT NULL UNIQUE,
    supplier_id INT NOT NULL,
    purchase_date DATE NOT NULL,
    subtotal DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    discount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    gst_amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    payment_status VARCHAR(20) DEFAULT 'UNPAID',
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 15. Purchase Items
CREATE TABLE IF NOT EXISTS purchase_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_id INT NOT NULL,
    product_id INT NOT NULL,
    billing_unit_id INT NULL,
    billing_unit_name VARCHAR(10) NULL,
    quantity DECIMAL(15, 2) NOT NULL,
    primary_qty DECIMAL(15, 2) NULL,
    cost_price DECIMAL(15, 2) NOT NULL,
    gst DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    amount DECIMAL(15, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 16. Customers
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    mobile VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(100) NULL,
    gst_number VARCHAR(20) NULL,
    address TEXT NULL,
    city VARCHAR(50) NULL,
    state VARCHAR(50) NULL,
    country VARCHAR(50) NULL,
    opening_balance DECIMAL(15, 2) DEFAULT 0.00,
    credit_limit DECIMAL(15, 2) DEFAULT 0.00,
    loyalty_points INT DEFAULT 0,
    customer_group VARCHAR(50) DEFAULT 'GENERAL',
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 17. Customer Payments
CREATE TABLE IF NOT EXISTS customer_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    payment_date DATE NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    reference_no VARCHAR(100) NULL,
    notes TEXT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 18. Invoices
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_no VARCHAR(50) NOT NULL UNIQUE,
    customer_id INT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE NULL,
    subtotal DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    discount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    gst_amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    cgst_amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    sgst_amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    igst_amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    is_igst TINYINT DEFAULT 0,
    coupon_id INT NULL,
    coupon_discount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    loyalty_points_earned INT DEFAULT 0,
    loyalty_points_redeemed INT DEFAULT 0,
    round_off DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    grand_total DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    paid_amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    due_amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    payment_method VARCHAR(50) NOT NULL,
    invoice_type VARCHAR(50) DEFAULT 'RETAIL',
    notes TEXT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 19. Invoice Items
CREATE TABLE IF NOT EXISTS invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    product_id INT NOT NULL,
    billing_unit_id INT NULL,
    billing_unit_name VARCHAR(10) NULL,
    hsn_code VARCHAR(20) NULL,
    quantity DECIMAL(15, 2) NOT NULL,
    primary_qty DECIMAL(15, 2) NULL,
    rate DECIMAL(15, 2) NOT NULL,
    gst DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    cgst DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    sgst DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    igst DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    discount DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    amount DECIMAL(15, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 20. Expense Categories
CREATE TABLE IF NOT EXISTS expense_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 21. Expenses
CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    expense_date DATE NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    description TEXT NULL,
    bill_attachment VARCHAR(255) NULL,
    payment_method VARCHAR(50) NOT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (category_id) REFERENCES expense_categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 22. Payments
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_type VARCHAR(50) NOT NULL,
    reference_id INT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    transaction_date DATE NOT NULL,
    remarks TEXT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 23. Report Logs
CREATE TABLE IF NOT EXISTS report_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_name VARCHAR(150) NOT NULL,
    generated_by INT NOT NULL,
    generated_date DATE NOT NULL,
    filters TEXT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (generated_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 24. Notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) NOT NULL,
    user_id INT NULL,
    is_read TINYINT DEFAULT 0,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 25. Activity Logs
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    module VARCHAR(50) NOT NULL,
    action VARCHAR(255) NOT NULL,
    record_id INT NULL,
    ip_address VARCHAR(45) NULL,
    device TEXT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 26. Login Logs
CREATE TABLE IF NOT EXISTS login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout_time TIMESTAMP NULL DEFAULT NULL,
    ip_address VARCHAR(45) NULL,
    device TEXT NULL,
    browser VARCHAR(100) NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 27. Backup Logs
CREATE TABLE IF NOT EXISTS backup_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    backup_file VARCHAR(255) NOT NULL,
    backup_size VARCHAR(50) NOT NULL,
    backup_date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'SUCCESS',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 28. Sales Returns
CREATE TABLE IF NOT EXISTS sales_returns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    customer_id INT NULL,
    return_no VARCHAR(50) NOT NULL UNIQUE,
    return_date DATE NOT NULL,
    total_amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    remarks TEXT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 29. Sales Return Items
CREATE TABLE IF NOT EXISTS sales_return_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sales_return_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(15, 2) NOT NULL,
    rate DECIMAL(15, 2) NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (sales_return_id) REFERENCES sales_returns(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 30. Purchase Returns
CREATE TABLE IF NOT EXISTS purchase_returns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_id INT NOT NULL,
    supplier_id INT NULL,
    return_no VARCHAR(50) NOT NULL UNIQUE,
    return_date DATE NOT NULL,
    total_amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    remarks TEXT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (purchase_id) REFERENCES purchases(id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 31. Purchase Return Items
CREATE TABLE IF NOT EXISTS purchase_return_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_return_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(15, 2) NOT NULL,
    cost_price DECIMAL(15, 2) NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (purchase_return_id) REFERENCES purchase_returns(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 32. Held Bills
CREATE TABLE IF NOT EXISTS held_bills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NULL,
    bill_note VARCHAR(255) NULL,
    cart_data TEXT NOT NULL,
    subtotal DECIMAL(15, 2) DEFAULT 0.00,
    invoice_type VARCHAR(50) DEFAULT 'RETAIL',
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 33. Invoice Payments (Split Payment)
CREATE TABLE IF NOT EXISTS invoice_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    reference_no VARCHAR(100) NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 34. Quotations
CREATE TABLE IF NOT EXISTS quotations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quotation_no VARCHAR(50) NOT NULL UNIQUE,
    customer_id INT NULL,
    quotation_date DATE NOT NULL,
    valid_until DATE NULL,
    subtotal DECIMAL(15, 2) DEFAULT 0.00,
    discount DECIMAL(15, 2) DEFAULT 0.00,
    gst_amount DECIMAL(15, 2) DEFAULT 0.00,
    grand_total DECIMAL(15, 2) DEFAULT 0.00,
    notes TEXT NULL,
    converted_invoice_id INT NULL,
    status VARCHAR(20) DEFAULT 'DRAFT',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 35. Quotation Items
CREATE TABLE IF NOT EXISTS quotation_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quotation_id INT NOT NULL,
    product_id INT NOT NULL,
    billing_unit_id INT NULL,
    billing_unit_name VARCHAR(10) NULL,
    quantity DECIMAL(15, 2) NOT NULL,
    primary_qty DECIMAL(15, 2) NULL,
    rate DECIMAL(15, 2) NOT NULL,
    gst DECIMAL(5, 2) DEFAULT 0.00,
    discount DECIMAL(5, 2) DEFAULT 0.00,
    amount DECIMAL(15, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 36. Delivery Challans
CREATE TABLE IF NOT EXISTS challans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    challan_no VARCHAR(50) NOT NULL UNIQUE,
    customer_id INT NULL,
    invoice_id INT NULL,
    challan_date DATE NOT NULL,
    transport_name VARCHAR(100) NULL,
    vehicle_no VARCHAR(50) NULL,
    notes TEXT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 37. Challan Items
CREATE TABLE IF NOT EXISTS challan_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    challan_id INT NOT NULL,
    product_id INT NOT NULL,
    billing_unit_id INT NULL,
    billing_unit_name VARCHAR(10) NULL,
    quantity DECIMAL(15, 2) NOT NULL,
    primary_qty DECIMAL(15, 2) NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (challan_id) REFERENCES challans(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 38. Product Batches
CREATE TABLE IF NOT EXISTS product_batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    batch_no VARCHAR(100) NOT NULL,
    mfg_date DATE NULL,
    expiry_date DATE NULL,
    quantity DECIMAL(15, 2) DEFAULT 0.00,
    cost_price DECIMAL(15, 2) DEFAULT 0.00,
    selling_price DECIMAL(15, 2) DEFAULT 0.00,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 39. Discount Coupons
CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coupon_code VARCHAR(50) NOT NULL UNIQUE,
    coupon_name VARCHAR(100) NOT NULL,
    discount_type VARCHAR(20) NOT NULL,
    discount_value DECIMAL(15, 2) NOT NULL,
    min_order_amount DECIMAL(15, 2) DEFAULT 0.00,
    max_discount DECIMAL(15, 2) NULL,
    valid_from DATE NULL,
    valid_until DATE NULL,
    usage_limit INT DEFAULT 0,
    used_count INT DEFAULT 0,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 40. Loyalty Transactions
CREATE TABLE IF NOT EXISTS loyalty_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    invoice_id INT NULL,
    points INT NOT NULL,
    type VARCHAR(20) NOT NULL,
    balance_after INT NOT NULL DEFAULT 0,
    remarks VARCHAR(255) NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ================= INDEXES =================
CREATE INDEX idx_products_sku ON products(sku);
CREATE INDEX idx_products_barcode ON products(barcode);
CREATE INDEX idx_customers_mobile ON customers(mobile);
CREATE INDEX idx_suppliers_mobile ON suppliers(mobile);
CREATE INDEX idx_invoices_invoice_no ON invoices(invoice_no);
CREATE INDEX idx_purchases_purchase_no ON purchases(purchase_no);
CREATE INDEX idx_stock_transactions_product_id ON stock_transactions(product_id);
CREATE INDEX idx_payments_reference_id ON payments(reference_id);
CREATE INDEX idx_activity_logs_user_id ON activity_logs(user_id);
CREATE INDEX idx_held_bills_created_by ON held_bills(created_by);
CREATE INDEX idx_invoice_payments_invoice_id ON invoice_payments(invoice_id);
CREATE INDEX idx_quotations_no ON quotations(quotation_no);
CREATE INDEX idx_challans_no ON challans(challan_no);
CREATE INDEX idx_product_batches_product ON product_batches(product_id);
CREATE INDEX idx_product_batches_expiry ON product_batches(expiry_date);
CREATE INDEX idx_coupons_code ON coupons(coupon_code);
CREATE INDEX idx_loyalty_customer ON loyalty_transactions(customer_id);
CREATE INDEX idx_unit_conv_primary ON unit_conversions(primary_unit_id);
CREATE INDEX idx_unit_conv_secondary ON unit_conversions(secondary_unit_id);


-- ================= SEED DATA =================

-- Roles
INSERT IGNORE INTO roles (id, role_name, description, status) VALUES
(1, 'Super Admin', 'Full system access, user management, and settings configuration', 'ACTIVE'),
(2, 'Admin', 'Access to products, invoices, expenses, customers, suppliers, and reports', 'ACTIVE'),
(3, 'Staff / Cashier', 'Access to billing, products, and customer directories', 'ACTIVE'),
(4, 'Accountant', 'Access to expenses, financial entries, and reports', 'ACTIVE');

-- Permissions
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

-- Role Permissions
INSERT IGNORE INTO role_permissions (role_id, permission_id, status) VALUES
(1, 1, 'ACTIVE'), (1, 2, 'ACTIVE'), (1, 3, 'ACTIVE'), (1, 4, 'ACTIVE'), (1, 5, 'ACTIVE'),
(1, 6, 'ACTIVE'), (1, 7, 'ACTIVE'), (1, 8, 'ACTIVE'), (1, 9, 'ACTIVE'), (1, 10, 'ACTIVE'), (1, 11, 'ACTIVE'),
(1, 12, 'ACTIVE'), (1, 13, 'ACTIVE'), (1, 14, 'ACTIVE'), (1, 15, 'ACTIVE'),
(2, 1, 'ACTIVE'), (2, 2, 'ACTIVE'), (2, 3, 'ACTIVE'), (2, 4, 'ACTIVE'), (2, 5, 'ACTIVE'),
(2, 6, 'ACTIVE'), (2, 7, 'ACTIVE'), (2, 8, 'ACTIVE'), (2, 12, 'ACTIVE'), (2, 13, 'ACTIVE'), (2, 14, 'ACTIVE'), (2, 15, 'ACTIVE'),
(3, 1, 'ACTIVE'), (3, 2, 'ACTIVE'), (3, 4, 'ACTIVE'), (3, 6, 'ACTIVE'), (3, 12, 'ACTIVE'), (3, 13, 'ACTIVE'), (3, 15, 'ACTIVE'),
(4, 1, 'ACTIVE'), (4, 7, 'ACTIVE'), (4, 8, 'ACTIVE'), (4, 15, 'ACTIVE');

-- Super Admin User (Email: hasmukhkikod@gmail.com | Password: admin123)
INSERT IGNORE INTO users (id, role_id, name, email, mobile, password, status) VALUES
(1, 1, 'Super Admin User', 'hasmukhkikod@gmail.com', '9876543210', '$2y$12$QxctayrdzNTNi9RUa3sHc.3fym5z8YyTNCebwIjYHsG0VrI163.ue', 'ACTIVE');

-- Demo Staff User (Email: staff@grovixo.com | Password: admin123)
INSERT IGNORE INTO users (id, role_id, name, email, mobile, password, status) VALUES
(2, 3, 'Demo Cashier', 'staff@grovixo.com', '9876543220', '$2y$12$QxctayrdzNTNi9RUa3sHc.3fym5z8YyTNCebwIjYHsG0VrI163.ue', 'ACTIVE');

-- Categories
INSERT IGNORE INTO categories (id, category_name, description, status) VALUES
(1, 'Electronics', 'Phones, laptops, chargers, and general electronic components', 'ACTIVE'),
(2, 'Grocery', 'Daily grocery items, food, and home cleaning products', 'ACTIVE'),
(3, 'Clothing', 'Apparel, garments, shoes, and lifestyle products', 'ACTIVE'),
(4, 'Hardware', 'Tools, fixtures, electrical items, and plumbing materials', 'ACTIVE'),
(5, 'Medicines', 'Prescription drugs, vitamins, and healthcare items', 'ACTIVE');

-- Brands
INSERT IGNORE INTO brands (id, brand_name, description, status) VALUES
(1, 'Samsung', 'Samsung Electronics', 'ACTIVE'),
(2, 'LG', 'LG Corporation', 'ACTIVE'),
(3, 'Sony', 'Sony Group', 'ACTIVE'),
(4, 'Apple', 'Apple Inc', 'ACTIVE'),
(5, 'Nike', 'Nike Inc', 'ACTIVE'),
(6, 'Generic', 'Generic brand', 'ACTIVE');

-- Units
INSERT IGNORE INTO units (id, unit_name, short_name, status) VALUES
(1, 'Pieces', 'Pcs', 'ACTIVE'),
(2, 'Box', 'Box', 'ACTIVE'),
(3, 'Kilograms', 'Kg', 'ACTIVE'),
(4, 'Liters', 'Ltr', 'ACTIVE'),
(5, 'Meters', 'Mtr', 'ACTIVE');

-- Unit Conversions
INSERT IGNORE INTO unit_conversions (primary_unit_id, secondary_unit_id, conversion_factor) VALUES
(2, 1, 12.0000);

-- Expense Categories
INSERT IGNORE INTO expense_categories (id, category_name, description, status) VALUES
(1, 'Rent', 'Office or shop rent', 'ACTIVE'),
(2, 'Electricity', 'Electricity utilities bills', 'ACTIVE'),
(3, 'Salary', 'Employees salary', 'ACTIVE'),
(4, 'Internet', 'Office broadband bills', 'ACTIVE'),
(5, 'Fuel', 'Vehicle fuel costs', 'ACTIVE'),
(6, 'Office Expense', 'Stationery, coffee, printing', 'ACTIVE'),
(7, 'Maintenance', 'Repairs and equipment service', 'ACTIVE');

-- Company Settings
INSERT IGNORE INTO company_settings (id, company_name, company_logo, gst_number, phone, email, website, address, city, state, country, pincode, invoice_prefix, invoice_start, invoice_end, quotation_prefix, quotation_start, quotation_end, purchase_prefix, purchase_start, purchase_end, challan_prefix, challan_start, challan_end, currency, timezone, gst_slabs, invoice_footer, invoice_terms, status, state_code, loyalty_enabled, loyalty_points_per_100, loyalty_redeem_value, thermal_width, invoice_template, bank_name, bank_account_no, bank_ifsc, bank_branch, upi_id) VALUES
(1, 'Grovixo', '', '27AAAAA1234A1Z5', '+91 98765 43210', 'info@grovixo.com', 'www.grovixo.com', '404 Premium Business Tower, Senapati Bapat Marg, Lower Parel', 'Mumbai', 'Maharashtra', 'India', '400013', 'INV-', 1, 99999, 'QT-', 1, 99999, 'PO-', 1, 99999, 'DC-', 1, 99999, 'INR', 'Asia/Kolkata', '0,5,12,18,28', 'Thank you for your business!', '1. Goods once sold will not be taken back or exchanged.\n2. Subject to Mumbai Jurisdiction.', 'ACTIVE', '27', 0, 1, 1.00, '80mm', 'standard', 'State Bank of India', '1234567890123', 'SBIN0001234', 'Lower Parel Branch', 'grovixo@upi');


-- ================= DUMMY / DEMO DATA =================

-- Customers
INSERT IGNORE INTO customers (id, customer_name, mobile, email, gst_number, address, city, state, country, opening_balance, credit_limit) VALUES
(1, 'Rajesh Sharma', '9876543211', 'rajesh@example.com', '27BBBBB1234B1Z5', '12 MG Road, Andheri West', 'Mumbai', 'Maharashtra', 'India', 0, 50000),
(2, 'Priya Patel', '9876543212', 'priya@example.com', NULL, '45 Park Street', 'Delhi', 'Delhi', 'India', 500.00, 25000),
(3, 'Amit Verma', '9876543213', 'amit@example.com', '27CCCCC1234C1Z5', '78 Brigade Road', 'Bangalore', 'Karnataka', 'India', 0, 100000),
(4, 'Sunita Desai', '9876543216', 'sunita@example.com', NULL, '23 Laxmi Nagar', 'Pune', 'Maharashtra', 'India', 1000.00, 30000),
(5, 'Vikram Singh', '9876543217', 'vikram@example.com', '29DDDDD1234D1Z5', '90 Jubilee Hills', 'Hyderabad', 'Telangana', 'India', 0, 75000);

-- Suppliers
INSERT IGNORE INTO suppliers (id, supplier_name, contact_person, mobile, email, gst_number, address, city, state, country, opening_balance) VALUES
(1, 'Tech Supplies India Pvt Ltd', 'Anand Mehta', '9876543214', 'anand@techsupplies.com', '27EEEEE1234E1Z5', '10 Tech Park, Hinjewadi', 'Pune', 'Maharashtra', 'India', 1000.00),
(2, 'Global Groceries Wholesale', 'Deepa Nair', '9876543215', 'deepa@globalgroceries.com', '33FFFFF1234F1Z5', '20 Food Avenue, T Nagar', 'Chennai', 'Tamil Nadu', 'India', 0),
(3, 'Fashion Hub Distributors', 'Karan Shah', '9876543218', 'karan@fashionhub.com', '27GGGGG1234G1Z5', '55 Crawford Market', 'Mumbai', 'Maharashtra', 'India', 2500.00);

-- Products (10 demo products across categories)
INSERT IGNORE INTO products (id, category_id, brand_id, unit_id, sku, barcode, hsn_code, product_name, cost_price, selling_price, gst_percentage, opening_stock, current_stock, minimum_stock) VALUES
(1, 1, 4, 1, 'SKU-001', 'BC-001', '8517', 'Apple iPhone 14', 60000, 75000, 18, 50, 48, 10),
(2, 1, 1, 1, 'SKU-002', 'BC-002', '8517', 'Samsung Galaxy S22', 55000, 70000, 18, 40, 37, 10),
(3, 3, 5, 1, 'SKU-003', 'BC-003', '6403', 'Nike Running Shoes', 3000, 5000, 12, 100, 95, 20),
(4, 2, 6, 3, 'SKU-004', 'BC-004', '1006', 'Organic Basmati Rice 5kg', 400, 600, 5, 200, 185, 50),
(5, 4, 6, 1, 'SKU-005', 'BC-005', '9405', 'Phillips LED Bulb 9W', 80, 120, 18, 500, 480, 100),
(6, 1, 3, 1, 'SKU-006', 'BC-006', '8518', 'Sony WH-1000XM5 Headphones', 22000, 29999, 18, 30, 28, 5),
(7, 2, 6, 3, 'SKU-007', 'BC-007', '0902', 'Tata Gold Tea 500g', 180, 250, 5, 300, 280, 50),
(8, 3, 6, 1, 'SKU-008', 'BC-008', '6109', 'Cotton Round Neck T-Shirt', 250, 499, 5, 200, 188, 30),
(9, 1, 2, 1, 'SKU-009', 'BC-009', '8528', 'LG 43 inch Smart TV', 28000, 35000, 18, 15, 13, 3),
(10, 4, 6, 5, 'SKU-010', 'BC-010', '7408', 'Copper Wire 2.5mm', 45, 65, 18, 1000, 950, 100);

-- Purchases
INSERT IGNORE INTO purchases (id, purchase_no, supplier_id, purchase_date, subtotal, discount, gst_amount, total_amount, payment_status, created_by) VALUES
(1, 'PO-0001', 1, '2026-06-05', 600000, 0, 108000, 708000, 'PAID', 1),
(2, 'PO-0002', 2, '2026-06-08', 72000, 0, 3600, 75600, 'PAID', 1),
(3, 'PO-0003', 3, '2026-06-10', 50000, 0, 2500, 52500, 'PARTIAL', 1);

-- Purchase Items
INSERT IGNORE INTO purchase_items (id, purchase_id, product_id, quantity, cost_price, gst, amount) VALUES
(1, 1, 1, 10, 60000, 18, 708000),
(2, 2, 4, 100, 400, 5, 42000),
(3, 2, 7, 100, 180, 5, 18900),
(4, 3, 3, 10, 3000, 12, 33600),
(5, 3, 8, 50, 250, 5, 13125);

-- Stock Transactions (from purchases)
INSERT IGNORE INTO stock_transactions (id, product_id, transaction_type, reference_no, quantity, stock_before, stock_after, remarks, created_by) VALUES
(1, 1, 'Purchase', 'PO-0001', 10, 40, 50, 'Purchase from Tech Supplies', 1),
(2, 4, 'Purchase', 'PO-0002', 100, 100, 200, 'Purchase from Global Groceries', 1),
(3, 7, 'Purchase', 'PO-0002', 100, 200, 300, 'Purchase from Global Groceries', 1),
(4, 3, 'Purchase', 'PO-0003', 10, 90, 100, 'Purchase from Fashion Hub', 1),
(5, 8, 'Purchase', 'PO-0003', 50, 150, 200, 'Purchase from Fashion Hub', 1);

-- Invoices (5 demo invoices)
INSERT IGNORE INTO invoices (id, invoice_no, customer_id, invoice_date, due_date, subtotal, discount, gst_amount, cgst_amount, sgst_amount, grand_total, paid_amount, due_amount, payment_method, invoice_type, created_by) VALUES
(1, 'INV-0001', 1, '2026-06-20', '2026-06-20', 75000, 0, 13500, 6750, 6750, 88500, 88500, 0, 'CASH', 'RETAIL', 1),
(2, 'INV-0002', 2, '2026-06-21', '2026-07-21', 5000, 0, 600, 300, 300, 5600, 2000, 3600, 'CARD', 'RETAIL', 1),
(3, 'INV-0003', 3, '2026-06-22', '2026-06-22', 29999, 0, 5400, 2700, 2700, 35399, 35399, 0, 'UPI', 'RETAIL', 1),
(4, 'INV-0004', 4, '2026-06-24', '2026-07-24', 3600, 0, 180, 90, 90, 3780, 1000, 2780, 'CASH', 'RETAIL', 1),
(5, 'INV-0005', 5, '2026-06-25', '2026-06-25', 35000, 0, 6300, 3150, 3150, 41300, 41300, 0, 'NET_BANKING', 'WHOLESALE', 1);

-- Invoice Items
INSERT IGNORE INTO invoice_items (id, invoice_id, product_id, hsn_code, quantity, rate, gst, cgst, sgst, discount, amount) VALUES
(1, 1, 1, '8517', 1, 75000, 18, 9, 9, 0, 88500),
(2, 2, 3, '6403', 1, 5000, 12, 6, 6, 0, 5600),
(3, 3, 6, '8518', 1, 29999, 18, 9, 9, 0, 35399),
(4, 4, 4, '1006', 6, 600, 5, 2.5, 2.5, 0, 3780),
(5, 5, 9, '8528', 1, 35000, 18, 9, 9, 0, 41300);

-- Invoice Payments (split payment example)
INSERT IGNORE INTO invoice_payments (id, invoice_id, payment_method, amount, reference_no) VALUES
(1, 1, 'CASH', 88500, NULL),
(2, 2, 'CARD', 2000, 'TXN-98765'),
(3, 3, 'UPI', 35399, 'UPI-12345678'),
(4, 4, 'CASH', 1000, NULL),
(5, 5, 'NET_BANKING', 41300, 'NEFT-87654321');

-- Stock Transactions (from sales)
INSERT IGNORE INTO stock_transactions (id, product_id, transaction_type, reference_no, quantity, stock_before, stock_after, remarks, created_by) VALUES
(6, 1, 'Sale', 'INV-0001', -1, 50, 49, 'Sale to Rajesh Sharma', 1),
(7, 3, 'Sale', 'INV-0002', -1, 100, 99, 'Sale to Priya Patel', 1),
(8, 6, 'Sale', 'INV-0003', -1, 30, 29, 'Sale to Amit Verma', 1),
(9, 4, 'Sale', 'INV-0004', -6, 200, 194, 'Sale to Sunita Desai', 1),
(10, 9, 'Sale', 'INV-0005', -1, 15, 14, 'Sale to Vikram Singh', 1);

-- Quotations
INSERT IGNORE INTO quotations (id, quotation_no, customer_id, quotation_date, valid_until, subtotal, discount, gst_amount, grand_total, notes, status, created_by) VALUES
(1, 'QT-0001', 3, '2026-06-18', '2026-07-18', 145000, 0, 26100, 171100, 'Bulk order quotation for office setup', 'SENT', 1),
(2, 'QT-0002', 5, '2026-06-22', '2026-07-22', 10000, 500, 1140, 10640, 'Shoes for team - corporate order', 'DRAFT', 1);

-- Quotation Items
INSERT IGNORE INTO quotation_items (id, quotation_id, product_id, quantity, rate, gst, discount, amount) VALUES
(1, 1, 2, 2, 70000, 18, 0, 165200),
(2, 1, 5, 50, 120, 18, 0, 7080),
(3, 2, 3, 2, 5000, 12, 5, 10640);

-- Challans
INSERT IGNORE INTO challans (id, challan_no, customer_id, invoice_id, challan_date, transport_name, vehicle_no, notes, status, created_by) VALUES
(1, 'DC-0001', 1, 1, '2026-06-20', 'BlueDart Express', 'MH-02-AB-1234', 'Handle with care - electronics', 'ACTIVE', 1),
(2, 'DC-0002', 5, 5, '2026-06-25', 'DTDC Logistics', 'MH-04-CD-5678', 'Fragile - TV unit', 'ACTIVE', 1);

-- Challan Items
INSERT IGNORE INTO challan_items (id, challan_id, product_id, quantity) VALUES
(1, 1, 1, 1),
(2, 2, 9, 1);

-- Expenses
INSERT IGNORE INTO expenses (id, category_id, expense_date, amount, description, payment_method, created_by) VALUES
(1, 1, '2026-06-01', 25000, 'June Shop Rent - Lower Parel', 'NET_BANKING', 1),
(2, 2, '2026-06-05', 4500, 'Electricity Bill - May 2026', 'UPI', 1),
(3, 3, '2026-06-07', 35000, 'Staff Salary - Cashier June', 'NET_BANKING', 1),
(4, 4, '2026-06-10', 1200, 'Broadband Internet - June', 'UPI', 1),
(5, 6, '2026-06-15', 800, 'Printer Paper & Stationery', 'CASH', 1);

-- Coupons
INSERT IGNORE INTO coupons (id, coupon_code, coupon_name, discount_type, discount_value, min_order_amount, max_discount, valid_from, valid_until, usage_limit, used_count) VALUES
(1, 'WELCOME10', 'Welcome 10% Off', 'PERCENTAGE', 10, 1000, 2000, '2026-01-01', '2026-12-31', 100, 3),
(2, 'FLAT500', 'Flat Rs.500 Off', 'FIXED', 500, 5000, 500, '2026-06-01', '2026-08-31', 50, 1);

-- Customer Payments
INSERT IGNORE INTO customer_payments (id, customer_id, payment_date, amount, payment_method, reference_no, notes) VALUES
(1, 2, '2026-06-28', 1000, 'UPI', 'UPI-99887766', 'Partial payment for INV-0002');

-- Supplier Payments
INSERT IGNORE INTO supplier_payments (id, supplier_id, payment_date, amount, payment_method, reference_no, notes) VALUES
(1, 1, '2026-06-06', 708000, 'NET_BANKING', 'NEFT-11223344', 'Full payment for PO-0001'),
(2, 3, '2026-06-12', 30000, 'UPI', 'UPI-55667788', 'Partial payment for PO-0003');

-- Payments ledger
INSERT IGNORE INTO payments (id, transaction_type, reference_id, payment_method, amount, transaction_date, remarks) VALUES
(1, 'Customer Payment', 1, 'CASH', 88500, '2026-06-20', 'INV-0001 full payment'),
(2, 'Customer Payment', 2, 'CARD', 2000, '2026-06-21', 'INV-0002 partial payment'),
(3, 'Supplier Payment', 1, 'NET_BANKING', 708000, '2026-06-06', 'PO-0001 full payment'),
(4, 'Expense', 1, 'NET_BANKING', 25000, '2026-06-01', 'June Shop Rent');


SET FOREIGN_KEY_CHECKS = 1;

-- ================================================================
-- INSTALLATION COMPLETE
-- Login: hasmukhkikod@gmail.com / admin123
-- Demo Staff: staff@grovixo.com / admin123
-- ================================================================
