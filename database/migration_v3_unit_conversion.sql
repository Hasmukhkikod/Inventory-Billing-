-- ================================================================
-- IIMS v3.0 Migration - Unit Conversion Feature
-- Run this migration on existing databases to add unit conversion
-- ================================================================

-- ================= ALTER EXISTING TABLES =================

-- Products: Add secondary unit and conversion factor
ALTER TABLE products ADD COLUMN secondary_unit_id INT NULL AFTER unit_id;
ALTER TABLE products ADD COLUMN conversion_factor DECIMAL(15,4) NULL AFTER secondary_unit_id;

-- Invoice Items: Add billing unit info
ALTER TABLE invoice_items ADD COLUMN billing_unit_id INT NULL AFTER product_id;
ALTER TABLE invoice_items ADD COLUMN billing_unit_name VARCHAR(10) NULL AFTER billing_unit_id;
ALTER TABLE invoice_items ADD COLUMN primary_qty DECIMAL(15,2) NULL AFTER quantity;

-- Quotation Items: Add billing unit info
ALTER TABLE quotation_items ADD COLUMN billing_unit_id INT NULL AFTER product_id;
ALTER TABLE quotation_items ADD COLUMN billing_unit_name VARCHAR(10) NULL AFTER billing_unit_id;
ALTER TABLE quotation_items ADD COLUMN primary_qty DECIMAL(15,2) NULL AFTER quantity;

-- Purchase Items: Add billing unit info
ALTER TABLE purchase_items ADD COLUMN billing_unit_id INT NULL AFTER product_id;
ALTER TABLE purchase_items ADD COLUMN billing_unit_name VARCHAR(10) NULL AFTER billing_unit_id;
ALTER TABLE purchase_items ADD COLUMN primary_qty DECIMAL(15,2) NULL AFTER quantity;

-- Challan Items: Add billing unit info
ALTER TABLE challan_items ADD COLUMN billing_unit_id INT NULL AFTER product_id;
ALTER TABLE challan_items ADD COLUMN billing_unit_name VARCHAR(10) NULL AFTER billing_unit_id;
ALTER TABLE challan_items ADD COLUMN primary_qty DECIMAL(15,2) NULL AFTER quantity;

-- ================= NEW TABLES =================

-- Unit Conversions (Master table for reusable conversion pairs)
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

-- ================= NEW INDEXES =================
CREATE INDEX idx_unit_conv_primary ON unit_conversions(primary_unit_id);
CREATE INDEX idx_unit_conv_secondary ON unit_conversions(secondary_unit_id);
