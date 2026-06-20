-- ================================================================
-- IIMS v2.0 Migration - Billing & Inventory Enhancement
-- Run this migration on existing databases to add new features
-- ================================================================

-- ================= ALTER EXISTING TABLES =================

-- Products: Add HSN code and batch tracking
ALTER TABLE products ADD COLUMN hsn_code VARCHAR(20) NULL AFTER barcode;
ALTER TABLE products ADD COLUMN batch_tracking TINYINT DEFAULT 0 AFTER minimum_stock;

-- Invoices: Add GST breakdown, due date, coupon, loyalty, notes
ALTER TABLE invoices ADD COLUMN due_date DATE NULL AFTER invoice_date;
ALTER TABLE invoices ADD COLUMN cgst_amount DECIMAL(15,2) DEFAULT 0.00 AFTER gst_amount;
ALTER TABLE invoices ADD COLUMN sgst_amount DECIMAL(15,2) DEFAULT 0.00 AFTER cgst_amount;
ALTER TABLE invoices ADD COLUMN igst_amount DECIMAL(15,2) DEFAULT 0.00 AFTER sgst_amount;
ALTER TABLE invoices ADD COLUMN is_igst TINYINT DEFAULT 0 AFTER igst_amount;
ALTER TABLE invoices ADD COLUMN coupon_id INT NULL AFTER is_igst;
ALTER TABLE invoices ADD COLUMN coupon_discount DECIMAL(15,2) DEFAULT 0.00 AFTER coupon_id;
ALTER TABLE invoices ADD COLUMN loyalty_points_earned INT DEFAULT 0 AFTER coupon_discount;
ALTER TABLE invoices ADD COLUMN loyalty_points_redeemed INT DEFAULT 0 AFTER loyalty_points_earned;
ALTER TABLE invoices ADD COLUMN notes TEXT NULL AFTER loyalty_points_redeemed;

-- Invoice Items: Add HSN and GST split
ALTER TABLE invoice_items ADD COLUMN hsn_code VARCHAR(20) NULL AFTER product_id;
ALTER TABLE invoice_items ADD COLUMN cgst DECIMAL(5,2) DEFAULT 0.00 AFTER gst;
ALTER TABLE invoice_items ADD COLUMN sgst DECIMAL(5,2) DEFAULT 0.00 AFTER cgst;
ALTER TABLE invoice_items ADD COLUMN igst DECIMAL(5,2) DEFAULT 0.00 AFTER sgst;

-- Customers: Add loyalty points and group
ALTER TABLE customers ADD COLUMN loyalty_points INT DEFAULT 0 AFTER credit_limit;
ALTER TABLE customers ADD COLUMN customer_group VARCHAR(50) DEFAULT 'GENERAL' AFTER loyalty_points;

-- Company Settings: Add state, loyalty config, invoice template
ALTER TABLE company_settings ADD COLUMN state_code VARCHAR(5) NULL AFTER state;
ALTER TABLE company_settings ADD COLUMN loyalty_enabled TINYINT DEFAULT 0 AFTER invoice_terms;
ALTER TABLE company_settings ADD COLUMN loyalty_points_per_100 INT DEFAULT 1 AFTER loyalty_enabled;
ALTER TABLE company_settings ADD COLUMN loyalty_redeem_value DECIMAL(5,2) DEFAULT 1.00 AFTER loyalty_points_per_100;
ALTER TABLE company_settings ADD COLUMN thermal_width VARCHAR(10) DEFAULT '80mm' AFTER loyalty_redeem_value;
ALTER TABLE company_settings ADD COLUMN invoice_template VARCHAR(20) DEFAULT 'standard' AFTER thermal_width;
ALTER TABLE company_settings ADD COLUMN bank_name VARCHAR(100) NULL AFTER invoice_template;
ALTER TABLE company_settings ADD COLUMN bank_account_no VARCHAR(50) NULL AFTER bank_name;
ALTER TABLE company_settings ADD COLUMN bank_ifsc VARCHAR(20) NULL AFTER bank_account_no;
ALTER TABLE company_settings ADD COLUMN bank_branch VARCHAR(100) NULL AFTER bank_ifsc;
ALTER TABLE company_settings ADD COLUMN upi_id VARCHAR(100) NULL AFTER bank_branch;

-- ================= NEW TABLES =================

-- Held Bills (Hold & Recall)
CREATE TABLE IF NOT EXISTS held_bills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NULL,
    bill_note VARCHAR(255) NULL,
    cart_data TEXT NOT NULL,
    subtotal DECIMAL(15,2) DEFAULT 0.00,
    invoice_type VARCHAR(50) DEFAULT 'RETAIL',
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Invoice Payments (Split Payment)
CREATE TABLE IF NOT EXISTS invoice_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    reference_no VARCHAR(100) NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Quotations
CREATE TABLE IF NOT EXISTS quotations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quotation_no VARCHAR(50) NOT NULL UNIQUE,
    customer_id INT NULL,
    quotation_date DATE NOT NULL,
    valid_until DATE NULL,
    subtotal DECIMAL(15,2) DEFAULT 0.00,
    discount DECIMAL(15,2) DEFAULT 0.00,
    gst_amount DECIMAL(15,2) DEFAULT 0.00,
    grand_total DECIMAL(15,2) DEFAULT 0.00,
    notes TEXT NULL,
    converted_invoice_id INT NULL,
    status VARCHAR(20) DEFAULT 'DRAFT',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Quotation Items
CREATE TABLE IF NOT EXISTS quotation_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quotation_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(15,2) NOT NULL,
    rate DECIMAL(15,2) NOT NULL,
    gst DECIMAL(5,2) DEFAULT 0.00,
    discount DECIMAL(5,2) DEFAULT 0.00,
    amount DECIMAL(15,2) NOT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Delivery Challans
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

-- Challan Items
CREATE TABLE IF NOT EXISTS challan_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    challan_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(15,2) NOT NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (challan_id) REFERENCES challans(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Product Batches (Batch & Expiry Tracking)
CREATE TABLE IF NOT EXISTS product_batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    batch_no VARCHAR(100) NOT NULL,
    mfg_date DATE NULL,
    expiry_date DATE NULL,
    quantity DECIMAL(15,2) DEFAULT 0.00,
    cost_price DECIMAL(15,2) DEFAULT 0.00,
    selling_price DECIMAL(15,2) DEFAULT 0.00,
    status VARCHAR(20) DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Discount Coupons
CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coupon_code VARCHAR(50) NOT NULL UNIQUE,
    coupon_name VARCHAR(100) NOT NULL,
    discount_type VARCHAR(20) NOT NULL,
    discount_value DECIMAL(15,2) NOT NULL,
    min_order_amount DECIMAL(15,2) DEFAULT 0.00,
    max_discount DECIMAL(15,2) NULL,
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

-- Loyalty Transactions
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

-- ================= NEW INDEXES =================
CREATE INDEX idx_held_bills_created_by ON held_bills(created_by);
CREATE INDEX idx_invoice_payments_invoice_id ON invoice_payments(invoice_id);
CREATE INDEX idx_quotations_no ON quotations(quotation_no);
CREATE INDEX idx_challans_no ON challans(challan_no);
CREATE INDEX idx_product_batches_product ON product_batches(product_id);
CREATE INDEX idx_product_batches_expiry ON product_batches(expiry_date);
CREATE INDEX idx_coupons_code ON coupons(coupon_code);
CREATE INDEX idx_loyalty_customer ON loyalty_transactions(customer_id);

-- ================= NEW PERMISSIONS =================
INSERT IGNORE INTO permissions (permission_name, module, status) VALUES
('Manage Quotations', 'quotations', 'ACTIVE'),
('Manage Challans', 'challans', 'ACTIVE'),
('Manage Coupons', 'coupons', 'ACTIVE'),
('View Day End Report', 'billing', 'ACTIVE');
