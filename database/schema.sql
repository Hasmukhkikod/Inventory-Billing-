-- Invoice & Inventory Management System (IIMS) - Part 2 Database Schema
-- Storage Engine: InnoDB | Character Set: utf8mb4 | Driver Support: MySQL & SQLite

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

-- 9. Products
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NULL,
    brand_id INT NULL,
    unit_id INT NULL,
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
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE SET NULL
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
    transaction_type VARCHAR(50) NOT NULL, -- Purchase, Sale, Adjustment, Return, Damage, Transfer
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
    payment_method VARCHAR(50) NOT NULL, -- CASH, UPI, CARD, NET_BANKING
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
    payment_status VARCHAR(20) DEFAULT 'UNPAID', -- PAID, PARTIAL, UNPAID
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
    quantity DECIMAL(15, 2) NOT NULL,
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
    hsn_code VARCHAR(20) NULL,
    quantity DECIMAL(15, 2) NOT NULL,
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
    transaction_type VARCHAR(50) NOT NULL, -- Customer Payment, Supplier Payment, Expense, etc.
    reference_id INT NOT NULL, -- ID of invoices, purchases, or expenses table
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
    type VARCHAR(50) NOT NULL, -- Email, SMS, WhatsApp, System
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

-- 26. Login Logs (Login history)
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

-- 32. Held Bills (Hold & Recall)
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

-- 29. Invoice Payments (Split Payment)
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

-- 30. Quotations
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

-- 31. Quotation Items
CREATE TABLE IF NOT EXISTS quotation_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quotation_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(15, 2) NOT NULL,
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

-- 32. Delivery Challans
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

-- 33. Challan Items
CREATE TABLE IF NOT EXISTS challan_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    challan_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(15, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (challan_id) REFERENCES challans(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 34. Product Batches (Batch & Expiry Tracking)
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

-- 35. Discount Coupons
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

-- 36. Loyalty Transactions
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


-- ================= INDEXING STRATEGY =================
-- For Fast Screen Load and Searches (< 3 Seconds)
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
