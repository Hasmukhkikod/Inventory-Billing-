-- MySQL Database Dump
-- Generated: 2026-07-08 09:45:29

SET FOREIGN_KEY_CHECKS=0;

-- Table structure for `activity_logs`
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `module` varchar(50) NOT NULL,
  `action` varchar(255) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `device` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_activity_logs_user_id` (`user_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `activity_logs`
INSERT INTO `activity_logs` (`id`, `user_id`, `module`, `action`, `record_id`, `ip_address`, `device`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', '1', 'auth', 'User login successful', '1', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'ACTIVE', '2026-06-29 21:54:39', '2026-06-29 21:54:39', '1', NULL);
INSERT INTO `activity_logs` (`id`, `user_id`, `module`, `action`, `record_id`, `ip_address`, `device`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', '1', 'billing', 'Created invoice: INV-2026-00006 (₹524)', '6', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'ACTIVE', '2026-06-29 21:57:28', '2026-06-29 21:57:28', '1', NULL);
INSERT INTO `activity_logs` (`id`, `user_id`, `module`, `action`, `record_id`, `ip_address`, `device`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('3', '1', 'auth', 'User login successful', '1', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'ACTIVE', '2026-07-02 22:21:28', '2026-07-02 22:21:28', '1', NULL);
INSERT INTO `activity_logs` (`id`, `user_id`, `module`, `action`, `record_id`, `ip_address`, `device`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('4', '1', 'settings', 'Updated system and company configurations.', NULL, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'ACTIVE', '2026-07-07 21:16:51', '2026-07-07 21:16:51', '1', NULL);
INSERT INTO `activity_logs` (`id`, `user_id`, `module`, `action`, `record_id`, `ip_address`, `device`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('5', '1', 'auth', 'User logout', '1', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'ACTIVE', '2026-07-07 22:27:49', '2026-07-07 22:27:49', '1', NULL);
INSERT INTO `activity_logs` (`id`, `user_id`, `module`, `action`, `record_id`, `ip_address`, `device`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('6', '1', 'auth', 'User login successful', '1', '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'ACTIVE', '2026-07-08 09:43:49', '2026-07-08 09:43:49', '1', NULL);

-- Table structure for `backup_logs`
DROP TABLE IF EXISTS `backup_logs`;
CREATE TABLE `backup_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `backup_file` varchar(255) NOT NULL,
  `backup_size` varchar(50) NOT NULL,
  `backup_date` date NOT NULL,
  `status` varchar(20) DEFAULT 'SUCCESS',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `backup_logs_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `backup_logs`
-- Table structure for `brands`
DROP TABLE IF EXISTS `brands`;
CREATE TABLE `brands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brand_name` (`brand_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `brands`
INSERT INTO `brands` (`id`, `brand_name`, `description`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', 'Samsung', 'Samsung Electronics', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `brands` (`id`, `brand_name`, `description`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', 'LG', 'LG Corporation', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `brands` (`id`, `brand_name`, `description`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('3', 'Sony', 'Sony Group', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `brands` (`id`, `brand_name`, `description`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('4', 'Apple', 'Apple Inc', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `brands` (`id`, `brand_name`, `description`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('5', 'Nike', 'Nike Inc', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `brands` (`id`, `brand_name`, `description`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('6', 'Generic', 'Generic brand', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);

-- Table structure for `categories`
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `categories`
INSERT INTO `categories` (`id`, `category_name`, `description`, `image`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', 'Electronics', 'Phones, laptops, chargers, and general electronic components', NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `categories` (`id`, `category_name`, `description`, `image`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', 'Grocery', 'Daily grocery items, food, and home cleaning products', NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `categories` (`id`, `category_name`, `description`, `image`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('3', 'Clothing', 'Apparel, garments, shoes, and lifestyle products', NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `categories` (`id`, `category_name`, `description`, `image`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('4', 'Hardware', 'Tools, fixtures, electrical items, and plumbing materials', NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `categories` (`id`, `category_name`, `description`, `image`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('5', 'Medicines', 'Prescription drugs, vitamins, and healthcare items', NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);

-- Table structure for `challan_items`
DROP TABLE IF EXISTS `challan_items`;
CREATE TABLE `challan_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `challan_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `billing_unit_id` int(11) DEFAULT NULL,
  `billing_unit_name` varchar(10) DEFAULT NULL,
  `quantity` decimal(15,2) NOT NULL,
  `primary_qty` decimal(15,2) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `challan_id` (`challan_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `challan_items_ibfk_1` FOREIGN KEY (`challan_id`) REFERENCES `challans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `challan_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `challan_items`
INSERT INTO `challan_items` (`id`, `challan_id`, `product_id`, `billing_unit_id`, `billing_unit_name`, `quantity`, `primary_qty`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', '1', '1', NULL, NULL, '1.00', NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `challan_items` (`id`, `challan_id`, `product_id`, `billing_unit_id`, `billing_unit_name`, `quantity`, `primary_qty`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', '2', '9', NULL, NULL, '1.00', NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);

-- Table structure for `challans`
DROP TABLE IF EXISTS `challans`;
CREATE TABLE `challans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `challan_no` varchar(50) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `challan_date` date NOT NULL,
  `transport_name` varchar(100) DEFAULT NULL,
  `vehicle_no` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `challan_no` (`challan_no`),
  KEY `customer_id` (`customer_id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `idx_challans_no` (`challan_no`),
  CONSTRAINT `challans_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `challans_ibfk_2` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `challans`
INSERT INTO `challans` (`id`, `challan_no`, `customer_id`, `invoice_id`, `challan_date`, `transport_name`, `vehicle_no`, `notes`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', 'DC-0001', '1', '1', '2026-06-20', 'BlueDart Express', 'MH-02-AB-1234', 'Handle with care - electronics', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);
INSERT INTO `challans` (`id`, `challan_no`, `customer_id`, `invoice_id`, `challan_date`, `transport_name`, `vehicle_no`, `notes`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', 'DC-0002', '5', '5', '2026-06-25', 'DTDC Logistics', 'MH-04-CD-5678', 'Fragile - TV unit', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);

-- Table structure for `company_settings`
DROP TABLE IF EXISTS `company_settings`;
CREATE TABLE `company_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(150) NOT NULL,
  `company_logo` varchar(255) DEFAULT NULL,
  `gst_number` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `pincode` varchar(15) DEFAULT NULL,
  `invoice_prefix` varchar(20) DEFAULT 'INV-',
  `invoice_start` int(11) DEFAULT 1,
  `invoice_end` int(11) DEFAULT 99999,
  `quotation_prefix` varchar(20) DEFAULT 'QT-',
  `quotation_start` int(11) DEFAULT 1,
  `quotation_end` int(11) DEFAULT 99999,
  `purchase_prefix` varchar(20) DEFAULT 'PO-',
  `purchase_start` int(11) DEFAULT 1,
  `purchase_end` int(11) DEFAULT 99999,
  `challan_prefix` varchar(20) DEFAULT 'DC-',
  `challan_start` int(11) DEFAULT 1,
  `challan_end` int(11) DEFAULT 99999,
  `currency` varchar(10) DEFAULT 'INR',
  `timezone` varchar(50) DEFAULT 'Asia/Kolkata',
  `gst_slabs` varchar(100) DEFAULT '0,5,12,18,28',
  `invoice_footer` varchar(255) DEFAULT 'Thank you for your business!',
  `invoice_terms` text DEFAULT NULL,
  `loyalty_enabled` tinyint(4) DEFAULT 0,
  `loyalty_points_per_100` int(11) DEFAULT 1,
  `loyalty_redeem_value` decimal(5,2) DEFAULT 1.00,
  `thermal_width` varchar(10) DEFAULT '80mm',
  `invoice_template` varchar(20) DEFAULT 'standard',
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_account_no` varchar(50) DEFAULT NULL,
  `bank_ifsc` varchar(20) DEFAULT NULL,
  `bank_branch` varchar(100) DEFAULT NULL,
  `upi_id` varchar(100) DEFAULT NULL,
  `state_code` varchar(5) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `pos_template` varchar(50) DEFAULT 'pos_standard',
  `pos_mode` tinyint(1) NOT NULL DEFAULT 0,
  `system_language` varchar(5) NOT NULL DEFAULT 'en',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `company_settings`
INSERT INTO `company_settings` (`id`, `company_name`, `company_logo`, `gst_number`, `phone`, `email`, `website`, `address`, `city`, `state`, `country`, `pincode`, `invoice_prefix`, `invoice_start`, `invoice_end`, `quotation_prefix`, `quotation_start`, `quotation_end`, `purchase_prefix`, `purchase_start`, `purchase_end`, `challan_prefix`, `challan_start`, `challan_end`, `currency`, `timezone`, `gst_slabs`, `invoice_footer`, `invoice_terms`, `loyalty_enabled`, `loyalty_points_per_100`, `loyalty_redeem_value`, `thermal_width`, `invoice_template`, `bank_name`, `bank_account_no`, `bank_ifsc`, `bank_branch`, `upi_id`, `state_code`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`, `pos_template`, `pos_mode`, `system_language`) VALUES ('1', 'Grovixo', 'company_logo_1783439211.jpg', '27AAAAA1234A1Z5', '+91 98765 43210', 'info@grovixo.com', 'www.grovixo.com', '404 Premium Business Tower, Senapati Bapat Marg, Lower Parel', 'Mumbai', 'Maharashtra', 'India', '400013', 'INV-', '1', '99999', 'QT-', '1', '99999', 'PO-', '1', '99999', 'DC-', '1', '99999', 'INR', 'Asia/Kolkata', '0,5,12,18,28', 'Thank you for your business!', '1. Goods once sold will not be taken back or exchanged.\r\n2. Subject to Mumbai Jurisdiction.', '0', '1', '1.00', '80mm', 'standard', 'State Bank of India', '1234567890123', 'SBIN0001234', 'Lower Parel Branch', 'grovixo@upi', '27', 'ACTIVE', '2026-06-29 20:22:32', '2026-07-07 21:16:51', NULL, NULL, 'pos_standard', '0', 'en');

-- Table structure for `coupons`
DROP TABLE IF EXISTS `coupons`;
CREATE TABLE `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coupon_code` varchar(50) NOT NULL,
  `coupon_name` varchar(100) NOT NULL,
  `discount_type` varchar(20) NOT NULL,
  `discount_value` decimal(15,2) NOT NULL,
  `min_order_amount` decimal(15,2) DEFAULT 0.00,
  `max_discount` decimal(15,2) DEFAULT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `usage_limit` int(11) DEFAULT 0,
  `used_count` int(11) DEFAULT 0,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coupon_code` (`coupon_code`),
  KEY `idx_coupons_code` (`coupon_code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `coupons`
INSERT INTO `coupons` (`id`, `coupon_code`, `coupon_name`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount`, `valid_from`, `valid_until`, `usage_limit`, `used_count`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', 'WELCOME10', 'Welcome 10% Off', 'PERCENTAGE', '10.00', '1000.00', '2000.00', '2026-01-01', '2026-12-31', '100', '3', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `coupons` (`id`, `coupon_code`, `coupon_name`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount`, `valid_from`, `valid_until`, `usage_limit`, `used_count`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', 'FLAT500', 'Flat Rs.500 Off', 'FIXED', '500.00', '5000.00', '500.00', '2026-06-01', '2026-08-31', '50', '1', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);

-- Table structure for `customer_payments`
DROP TABLE IF EXISTS `customer_payments`;
CREATE TABLE `customer_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `customer_payments_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `customer_payments`
INSERT INTO `customer_payments` (`id`, `customer_id`, `payment_date`, `amount`, `payment_method`, `reference_no`, `notes`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', '2', '2026-06-28', '1000.00', 'UPI', 'UPI-99887766', 'Partial payment for INV-0002', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);

-- Table structure for `customers`
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(100) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `gst_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `opening_balance` decimal(15,2) DEFAULT 0.00,
  `credit_limit` decimal(15,2) DEFAULT 0.00,
  `loyalty_points` int(11) DEFAULT 0,
  `customer_group` varchar(50) DEFAULT 'GENERAL',
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mobile` (`mobile`),
  KEY `idx_customers_mobile` (`mobile`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `customers`
INSERT INTO `customers` (`id`, `customer_name`, `mobile`, `email`, `gst_number`, `address`, `city`, `state`, `country`, `opening_balance`, `credit_limit`, `loyalty_points`, `customer_group`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', 'Rajesh Sharma', '9876543211', 'rajesh@example.com', '27BBBBB1234B1Z5', '12 MG Road, Andheri West', 'Mumbai', 'Maharashtra', 'India', '0.00', '50000.00', '0', 'GENERAL', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `customers` (`id`, `customer_name`, `mobile`, `email`, `gst_number`, `address`, `city`, `state`, `country`, `opening_balance`, `credit_limit`, `loyalty_points`, `customer_group`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', 'Priya Patel', '9876543212', 'priya@example.com', NULL, '45 Park Street', 'Delhi', 'Delhi', 'India', '500.00', '25000.00', '0', 'GENERAL', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `customers` (`id`, `customer_name`, `mobile`, `email`, `gst_number`, `address`, `city`, `state`, `country`, `opening_balance`, `credit_limit`, `loyalty_points`, `customer_group`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('3', 'Amit Verma', '9876543213', 'amit@example.com', '27CCCCC1234C1Z5', '78 Brigade Road', 'Bangalore', 'Karnataka', 'India', '0.00', '100000.00', '0', 'GENERAL', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `customers` (`id`, `customer_name`, `mobile`, `email`, `gst_number`, `address`, `city`, `state`, `country`, `opening_balance`, `credit_limit`, `loyalty_points`, `customer_group`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('4', 'Sunita Desai', '9876543216', 'sunita@example.com', NULL, '23 Laxmi Nagar', 'Pune', 'Maharashtra', 'India', '1000.00', '30000.00', '0', 'GENERAL', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `customers` (`id`, `customer_name`, `mobile`, `email`, `gst_number`, `address`, `city`, `state`, `country`, `opening_balance`, `credit_limit`, `loyalty_points`, `customer_group`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('5', 'Vikram Singh', '9876543217', 'vikram@example.com', '29DDDDD1234D1Z5', '90 Jubilee Hills', 'Hyderabad', 'Telangana', 'India', '0.00', '75000.00', '0', 'GENERAL', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);

-- Table structure for `expense_categories`
DROP TABLE IF EXISTS `expense_categories`;
CREATE TABLE `expense_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `expense_categories`
INSERT INTO `expense_categories` (`id`, `category_name`, `description`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', 'Rent', 'Office or shop rent', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `expense_categories` (`id`, `category_name`, `description`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', 'Electricity', 'Electricity utilities bills', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `expense_categories` (`id`, `category_name`, `description`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('3', 'Salary', 'Employees salary', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `expense_categories` (`id`, `category_name`, `description`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('4', 'Internet', 'Office broadband bills', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `expense_categories` (`id`, `category_name`, `description`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('5', 'Fuel', 'Vehicle fuel costs', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `expense_categories` (`id`, `category_name`, `description`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('6', 'Office Expense', 'Stationery, coffee, printing', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `expense_categories` (`id`, `category_name`, `description`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('7', 'Maintenance', 'Repairs and equipment service', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);

-- Table structure for `expenses`
DROP TABLE IF EXISTS `expenses`;
CREATE TABLE `expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `expense_date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` text DEFAULT NULL,
  `bill_attachment` varchar(255) DEFAULT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `expense_categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `expenses`
INSERT INTO `expenses` (`id`, `category_id`, `expense_date`, `amount`, `description`, `bill_attachment`, `payment_method`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', '1', '2026-06-01', '25000.00', 'June Shop Rent - Lower Parel', NULL, 'NET_BANKING', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);
INSERT INTO `expenses` (`id`, `category_id`, `expense_date`, `amount`, `description`, `bill_attachment`, `payment_method`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', '2', '2026-06-05', '4500.00', 'Electricity Bill - May 2026', NULL, 'UPI', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);
INSERT INTO `expenses` (`id`, `category_id`, `expense_date`, `amount`, `description`, `bill_attachment`, `payment_method`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('3', '3', '2026-06-07', '35000.00', 'Staff Salary - Cashier June', NULL, 'NET_BANKING', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);
INSERT INTO `expenses` (`id`, `category_id`, `expense_date`, `amount`, `description`, `bill_attachment`, `payment_method`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('4', '4', '2026-06-10', '1200.00', 'Broadband Internet - June', NULL, 'UPI', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);
INSERT INTO `expenses` (`id`, `category_id`, `expense_date`, `amount`, `description`, `bill_attachment`, `payment_method`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('5', '6', '2026-06-15', '800.00', 'Printer Paper & Stationery', NULL, 'CASH', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);

-- Table structure for `held_bills`
DROP TABLE IF EXISTS `held_bills`;
CREATE TABLE `held_bills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `bill_note` varchar(255) DEFAULT NULL,
  `cart_data` text NOT NULL,
  `subtotal` decimal(15,2) DEFAULT 0.00,
  `invoice_type` varchar(50) DEFAULT 'RETAIL',
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `idx_held_bills_created_by` (`created_by`),
  CONSTRAINT `held_bills_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `held_bills`
-- Table structure for `invoice_items`
DROP TABLE IF EXISTS `invoice_items`;
CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `billing_unit_id` int(11) DEFAULT NULL,
  `billing_unit_name` varchar(10) DEFAULT NULL,
  `hsn_code` varchar(20) DEFAULT NULL,
  `quantity` decimal(15,2) NOT NULL,
  `primary_qty` decimal(15,2) DEFAULT NULL,
  `rate` decimal(15,2) NOT NULL,
  `gst` decimal(5,2) NOT NULL DEFAULT 0.00,
  `cgst` decimal(5,2) NOT NULL DEFAULT 0.00,
  `sgst` decimal(5,2) NOT NULL DEFAULT 0.00,
  `igst` decimal(5,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(5,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(15,2) NOT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoice_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `invoice_items`
INSERT INTO `invoice_items` (`id`, `invoice_id`, `product_id`, `billing_unit_id`, `billing_unit_name`, `hsn_code`, `quantity`, `primary_qty`, `rate`, `gst`, `cgst`, `sgst`, `igst`, `discount`, `amount`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', '1', '1', NULL, NULL, '8517', '1.00', NULL, '75000.00', '18.00', '9.00', '9.00', '0.00', '0.00', '88500.00', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `invoice_items` (`id`, `invoice_id`, `product_id`, `billing_unit_id`, `billing_unit_name`, `hsn_code`, `quantity`, `primary_qty`, `rate`, `gst`, `cgst`, `sgst`, `igst`, `discount`, `amount`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', '2', '3', NULL, NULL, '6403', '1.00', NULL, '5000.00', '12.00', '6.00', '6.00', '0.00', '0.00', '5600.00', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `invoice_items` (`id`, `invoice_id`, `product_id`, `billing_unit_id`, `billing_unit_name`, `hsn_code`, `quantity`, `primary_qty`, `rate`, `gst`, `cgst`, `sgst`, `igst`, `discount`, `amount`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('3', '3', '6', NULL, NULL, '8518', '1.00', NULL, '29999.00', '18.00', '9.00', '9.00', '0.00', '0.00', '35399.00', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `invoice_items` (`id`, `invoice_id`, `product_id`, `billing_unit_id`, `billing_unit_name`, `hsn_code`, `quantity`, `primary_qty`, `rate`, `gst`, `cgst`, `sgst`, `igst`, `discount`, `amount`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('4', '4', '4', NULL, NULL, '1006', '6.00', NULL, '600.00', '5.00', '2.50', '2.50', '0.00', '0.00', '3780.00', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `invoice_items` (`id`, `invoice_id`, `product_id`, `billing_unit_id`, `billing_unit_name`, `hsn_code`, `quantity`, `primary_qty`, `rate`, `gst`, `cgst`, `sgst`, `igst`, `discount`, `amount`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('5', '5', '9', NULL, NULL, '8528', '1.00', NULL, '35000.00', '18.00', '9.00', '9.00', '0.00', '0.00', '41300.00', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `invoice_items` (`id`, `invoice_id`, `product_id`, `billing_unit_id`, `billing_unit_name`, `hsn_code`, `quantity`, `primary_qty`, `rate`, `gst`, `cgst`, `sgst`, `igst`, `discount`, `amount`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('6', '6', '8', '1', 'Pcs', '6109', '1.00', '1.00', '499.00', '5.00', '2.50', '2.50', '0.00', '0.00', '523.95', 'ACTIVE', '2026-06-29 21:57:28', '2026-06-29 21:57:28', NULL, NULL);

-- Table structure for `invoice_payments`
DROP TABLE IF EXISTS `invoice_payments`;
CREATE TABLE `invoice_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_invoice_payments_invoice_id` (`invoice_id`),
  CONSTRAINT `invoice_payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `invoice_payments`
INSERT INTO `invoice_payments` (`id`, `invoice_id`, `payment_method`, `amount`, `reference_no`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', '1', 'CASH', '88500.00', NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `invoice_payments` (`id`, `invoice_id`, `payment_method`, `amount`, `reference_no`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', '2', 'CARD', '2000.00', 'TXN-98765', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `invoice_payments` (`id`, `invoice_id`, `payment_method`, `amount`, `reference_no`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('3', '3', 'UPI', '35399.00', 'UPI-12345678', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `invoice_payments` (`id`, `invoice_id`, `payment_method`, `amount`, `reference_no`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('4', '4', 'CASH', '1000.00', NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `invoice_payments` (`id`, `invoice_id`, `payment_method`, `amount`, `reference_no`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('5', '5', 'NET_BANKING', '41300.00', 'NEFT-87654321', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `invoice_payments` (`id`, `invoice_id`, `payment_method`, `amount`, `reference_no`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('6', '6', 'CASH', '524.00', NULL, 'ACTIVE', '2026-06-29 21:57:28', '2026-06-29 21:57:28', '1', NULL);

-- Table structure for `invoices`
DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_no` varchar(50) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `gst_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `cgst_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sgst_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `igst_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `is_igst` tinyint(4) DEFAULT 0,
  `coupon_id` int(11) DEFAULT NULL,
  `coupon_discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `loyalty_points_earned` int(11) DEFAULT 0,
  `loyalty_points_redeemed` int(11) DEFAULT 0,
  `round_off` decimal(5,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `due_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) NOT NULL,
  `invoice_type` varchar(50) DEFAULT 'RETAIL',
  `notes` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_no` (`invoice_no`),
  KEY `customer_id` (`customer_id`),
  KEY `idx_invoices_invoice_no` (`invoice_no`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `invoices`
INSERT INTO `invoices` (`id`, `invoice_no`, `customer_id`, `invoice_date`, `due_date`, `subtotal`, `discount`, `gst_amount`, `cgst_amount`, `sgst_amount`, `igst_amount`, `is_igst`, `coupon_id`, `coupon_discount`, `loyalty_points_earned`, `loyalty_points_redeemed`, `round_off`, `grand_total`, `paid_amount`, `due_amount`, `payment_method`, `invoice_type`, `notes`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', 'INV-0001', '1', '2026-06-20', '2026-06-20', '75000.00', '0.00', '13500.00', '6750.00', '6750.00', '0.00', '0', NULL, '0.00', '0', '0', '0.00', '88500.00', '88500.00', '0.00', 'CASH', 'RETAIL', NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);
INSERT INTO `invoices` (`id`, `invoice_no`, `customer_id`, `invoice_date`, `due_date`, `subtotal`, `discount`, `gst_amount`, `cgst_amount`, `sgst_amount`, `igst_amount`, `is_igst`, `coupon_id`, `coupon_discount`, `loyalty_points_earned`, `loyalty_points_redeemed`, `round_off`, `grand_total`, `paid_amount`, `due_amount`, `payment_method`, `invoice_type`, `notes`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', 'INV-0002', '2', '2026-06-21', '2026-07-21', '5000.00', '0.00', '600.00', '300.00', '300.00', '0.00', '0', NULL, '0.00', '0', '0', '0.00', '5600.00', '2000.00', '3600.00', 'CARD', 'RETAIL', NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);
INSERT INTO `invoices` (`id`, `invoice_no`, `customer_id`, `invoice_date`, `due_date`, `subtotal`, `discount`, `gst_amount`, `cgst_amount`, `sgst_amount`, `igst_amount`, `is_igst`, `coupon_id`, `coupon_discount`, `loyalty_points_earned`, `loyalty_points_redeemed`, `round_off`, `grand_total`, `paid_amount`, `due_amount`, `payment_method`, `invoice_type`, `notes`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('3', 'INV-0003', '3', '2026-06-22', '2026-06-22', '29999.00', '0.00', '5400.00', '2700.00', '2700.00', '0.00', '0', NULL, '0.00', '0', '0', '0.00', '35399.00', '35399.00', '0.00', 'UPI', 'RETAIL', NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);
INSERT INTO `invoices` (`id`, `invoice_no`, `customer_id`, `invoice_date`, `due_date`, `subtotal`, `discount`, `gst_amount`, `cgst_amount`, `sgst_amount`, `igst_amount`, `is_igst`, `coupon_id`, `coupon_discount`, `loyalty_points_earned`, `loyalty_points_redeemed`, `round_off`, `grand_total`, `paid_amount`, `due_amount`, `payment_method`, `invoice_type`, `notes`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('4', 'INV-0004', '4', '2026-06-24', '2026-07-24', '3600.00', '0.00', '180.00', '90.00', '90.00', '0.00', '0', NULL, '0.00', '0', '0', '0.00', '3780.00', '1000.00', '2780.00', 'CASH', 'RETAIL', NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);
INSERT INTO `invoices` (`id`, `invoice_no`, `customer_id`, `invoice_date`, `due_date`, `subtotal`, `discount`, `gst_amount`, `cgst_amount`, `sgst_amount`, `igst_amount`, `is_igst`, `coupon_id`, `coupon_discount`, `loyalty_points_earned`, `loyalty_points_redeemed`, `round_off`, `grand_total`, `paid_amount`, `due_amount`, `payment_method`, `invoice_type`, `notes`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('5', 'INV-0005', '5', '2026-06-25', '2026-06-25', '35000.00', '0.00', '6300.00', '3150.00', '3150.00', '0.00', '0', NULL, '0.00', '0', '0', '0.00', '41300.00', '41300.00', '0.00', 'NET_BANKING', 'WHOLESALE', NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);
INSERT INTO `invoices` (`id`, `invoice_no`, `customer_id`, `invoice_date`, `due_date`, `subtotal`, `discount`, `gst_amount`, `cgst_amount`, `sgst_amount`, `igst_amount`, `is_igst`, `coupon_id`, `coupon_discount`, `loyalty_points_earned`, `loyalty_points_redeemed`, `round_off`, `grand_total`, `paid_amount`, `due_amount`, `payment_method`, `invoice_type`, `notes`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('6', 'INV-2026-00006', NULL, '2026-06-29', NULL, '499.00', '0.00', '24.95', '12.48', '12.48', '0.00', '0', NULL, '0.00', '0', '0', '0.05', '524.00', '524.00', '0.00', 'CASH', 'GST', '', 'PAID', '2026-06-29 21:57:28', '2026-06-29 21:57:28', '1', NULL);

-- Table structure for `login_logs`
DROP TABLE IF EXISTS `login_logs`;
CREATE TABLE `login_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `logout_time` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `device` text DEFAULT NULL,
  `browser` varchar(100) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `login_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `login_logs`
INSERT INTO `login_logs` (`id`, `user_id`, `login_time`, `logout_time`, `ip_address`, `device`, `browser`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', '1', '2026-06-29 21:54:39', NULL, '::1', NULL, 'Google Chrome (Desktop)', 'SUCCESS', '2026-06-29 21:54:39', '2026-06-29 21:54:39', NULL, NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `login_time`, `logout_time`, `ip_address`, `device`, `browser`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', '1', '2026-07-02 22:21:28', '2026-07-07 22:27:49', '::1', NULL, 'Google Chrome (Desktop)', 'SUCCESS', '2026-07-02 22:21:28', '2026-07-07 22:27:49', NULL, NULL);
INSERT INTO `login_logs` (`id`, `user_id`, `login_time`, `logout_time`, `ip_address`, `device`, `browser`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('3', '1', '2026-07-08 09:43:49', NULL, '::1', NULL, 'Google Chrome (Desktop)', 'SUCCESS', '2026-07-08 09:43:49', '2026-07-08 09:43:49', NULL, NULL);

-- Table structure for `loyalty_transactions`
DROP TABLE IF EXISTS `loyalty_transactions`;
CREATE TABLE `loyalty_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `points` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  `balance_after` int(11) NOT NULL DEFAULT 0,
  `remarks` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `idx_loyalty_customer` (`customer_id`),
  CONSTRAINT `loyalty_transactions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `loyalty_transactions_ibfk_2` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `loyalty_transactions`
-- Table structure for `notifications`
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `is_read` tinyint(4) DEFAULT 0,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `notifications`
INSERT INTO `notifications` (`id`, `title`, `message`, `type`, `user_id`, `is_read`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', 'Invoice Generated', 'Invoice INV-2026-00006 - Total: ₹524.00', 'System', NULL, '0', 'PENDING', '2026-06-29 21:57:28', '2026-06-29 21:57:28', NULL, NULL);

-- Table structure for `payments`
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_type` varchar(50) NOT NULL,
  `reference_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `transaction_date` date NOT NULL,
  `remarks` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_payments_reference_id` (`reference_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `payments`
INSERT INTO `payments` (`id`, `transaction_type`, `reference_id`, `payment_method`, `amount`, `transaction_date`, `remarks`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', 'Customer Payment', '1', 'CASH', '88500.00', '2026-06-20', 'INV-0001 full payment', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `payments` (`id`, `transaction_type`, `reference_id`, `payment_method`, `amount`, `transaction_date`, `remarks`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', 'Customer Payment', '2', 'CARD', '2000.00', '2026-06-21', 'INV-0002 partial payment', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `payments` (`id`, `transaction_type`, `reference_id`, `payment_method`, `amount`, `transaction_date`, `remarks`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('3', 'Supplier Payment', '1', 'NET_BANKING', '708000.00', '2026-06-06', 'PO-0001 full payment', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `payments` (`id`, `transaction_type`, `reference_id`, `payment_method`, `amount`, `transaction_date`, `remarks`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('4', 'Expense', '1', 'NET_BANKING', '25000.00', '2026-06-01', 'June Shop Rent', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `payments` (`id`, `transaction_type`, `reference_id`, `payment_method`, `amount`, `transaction_date`, `remarks`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('5', 'Customer Payment', '6', 'CASH', '524.00', '2026-06-29', 'Invoice: INV-2026-00006', 'ACTIVE', '2026-06-29 21:57:28', '2026-06-29 21:57:28', '1', NULL);

-- Table structure for `permissions`
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_name` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_name` (`permission_name`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `permissions`
INSERT INTO `permissions` (`id`, `permission_name`, `module`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', 'Access Dashboard', 'dashboard', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `permissions` (`id`, `permission_name`, `module`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', 'Manage Inventory', 'inventory', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `permissions` (`id`, `permission_name`, `module`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('3', 'Manage Purchases', 'purchases', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `permissions` (`id`, `permission_name`, `module`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('4', 'Manage Customers', 'customers', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `permissions` (`id`, `permission_name`, `module`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('5', 'Manage Suppliers', 'suppliers', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `permissions` (`id`, `permission_name`, `module`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('6', 'Create Invoice', 'billing', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `permissions` (`id`, `permission_name`, `module`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('7', 'Manage Expenses', 'expenses', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `permissions` (`id`, `permission_name`, `module`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('8', 'View Reports', 'reports', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `permissions` (`id`, `permission_name`, `module`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('9', 'Manage Users', 'users', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `permissions` (`id`, `permission_name`, `module`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('10', 'Manage Settings', 'settings', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `permissions` (`id`, `permission_name`, `module`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('11', 'Run Backups', 'backups', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `permissions` (`id`, `permission_name`, `module`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('12', 'Manage Quotations', 'quotations', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `permissions` (`id`, `permission_name`, `module`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('13', 'Manage Challans', 'challans', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `permissions` (`id`, `permission_name`, `module`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('14', 'Manage Coupons', 'coupons', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `permissions` (`id`, `permission_name`, `module`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('15', 'View Day End Report', 'billing', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);

-- Table structure for `product_batches`
DROP TABLE IF EXISTS `product_batches`;
CREATE TABLE `product_batches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `batch_no` varchar(100) NOT NULL,
  `mfg_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `quantity` decimal(15,2) DEFAULT 0.00,
  `cost_price` decimal(15,2) DEFAULT 0.00,
  `selling_price` decimal(15,2) DEFAULT 0.00,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_product_batches_product` (`product_id`),
  KEY `idx_product_batches_expiry` (`expiry_date`),
  CONSTRAINT `product_batches_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `product_batches`
-- Table structure for `product_images`
DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `product_images`
-- Table structure for `products`
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `secondary_unit_id` int(11) DEFAULT NULL,
  `conversion_factor` decimal(15,4) DEFAULT NULL,
  `sku` varchar(100) NOT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `hsn_code` varchar(20) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `cost_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `selling_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `gst_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `opening_stock` decimal(15,2) NOT NULL DEFAULT 0.00,
  `current_stock` decimal(15,2) NOT NULL DEFAULT 0.00,
  `minimum_stock` decimal(15,2) NOT NULL DEFAULT 0.00,
  `batch_tracking` tinyint(4) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  UNIQUE KEY `barcode` (`barcode`),
  KEY `category_id` (`category_id`),
  KEY `brand_id` (`brand_id`),
  KEY `unit_id` (`unit_id`),
  KEY `secondary_unit_id` (`secondary_unit_id`),
  KEY `idx_products_sku` (`sku`),
  KEY `idx_products_barcode` (`barcode`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_ibfk_2` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_ibfk_3` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_ibfk_4` FOREIGN KEY (`secondary_unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `products`
INSERT INTO `products` (`id`, `category_id`, `brand_id`, `unit_id`, `secondary_unit_id`, `conversion_factor`, `sku`, `barcode`, `hsn_code`, `product_name`, `description`, `cost_price`, `selling_price`, `gst_percentage`, `opening_stock`, `current_stock`, `minimum_stock`, `batch_tracking`, `image`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', '1', '4', '1', NULL, NULL, 'SKU-001', 'BC-001', '8517', 'Apple iPhone 14', NULL, '60000.00', '75000.00', '18.00', '50.00', '48.00', '10.00', '0', NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `products` (`id`, `category_id`, `brand_id`, `unit_id`, `secondary_unit_id`, `conversion_factor`, `sku`, `barcode`, `hsn_code`, `product_name`, `description`, `cost_price`, `selling_price`, `gst_percentage`, `opening_stock`, `current_stock`, `minimum_stock`, `batch_tracking`, `image`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', '1', '1', '1', NULL, NULL, 'SKU-002', 'BC-002', '8517', 'Samsung Galaxy S22', NULL, '55000.00', '70000.00', '18.00', '40.00', '37.00', '10.00', '0', NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `products` (`id`, `category_id`, `brand_id`, `unit_id`, `secondary_unit_id`, `conversion_factor`, `sku`, `barcode`, `hsn_code`, `product_name`, `description`, `cost_price`, `selling_price`, `gst_percentage`, `opening_stock`, `current_stock`, `minimum_stock`, `batch_tracking`, `image`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('3', '3', '5', '1', NULL, NULL, 'SKU-003', 'BC-003', '6403', 'Nike Running Shoes', NULL, '3000.00', '5000.00', '12.00', '100.00', '95.00', '20.00', '0', NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `products` (`id`, `category_id`, `brand_id`, `unit_id`, `secondary_unit_id`, `conversion_factor`, `sku`, `barcode`, `hsn_code`, `product_name`, `description`, `cost_price`, `selling_price`, `gst_percentage`, `opening_stock`, `current_stock`, `minimum_stock`, `batch_tracking`, `image`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('4', '2', '6', '3', NULL, NULL, 'SKU-004', 'BC-004', '1006', 'Organic Basmati Rice 5kg', NULL, '400.00', '600.00', '5.00', '200.00', '185.00', '50.00', '0', NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `products` (`id`, `category_id`, `brand_id`, `unit_id`, `secondary_unit_id`, `conversion_factor`, `sku`, `barcode`, `hsn_code`, `product_name`, `description`, `cost_price`, `selling_price`, `gst_percentage`, `opening_stock`, `current_stock`, `minimum_stock`, `batch_tracking`, `image`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('5', '4', '6', '1', NULL, NULL, 'SKU-005', 'BC-005', '9405', 'Phillips LED Bulb 9W', NULL, '80.00', '120.00', '18.00', '500.00', '480.00', '100.00', '0', NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `products` (`id`, `category_id`, `brand_id`, `unit_id`, `secondary_unit_id`, `conversion_factor`, `sku`, `barcode`, `hsn_code`, `product_name`, `description`, `cost_price`, `selling_price`, `gst_percentage`, `opening_stock`, `current_stock`, `minimum_stock`, `batch_tracking`, `image`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('6', '1', '3', '1', NULL, NULL, 'SKU-006', 'BC-006', '8518', 'Sony WH-1000XM5 Headphones', NULL, '22000.00', '29999.00', '18.00', '30.00', '28.00', '5.00', '0', NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `products` (`id`, `category_id`, `brand_id`, `unit_id`, `secondary_unit_id`, `conversion_factor`, `sku`, `barcode`, `hsn_code`, `product_name`, `description`, `cost_price`, `selling_price`, `gst_percentage`, `opening_stock`, `current_stock`, `minimum_stock`, `batch_tracking`, `image`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('7', '2', '6', '3', NULL, NULL, 'SKU-007', 'BC-007', '0902', 'Tata Gold Tea 500g', NULL, '180.00', '250.00', '5.00', '300.00', '280.00', '50.00', '0', NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `products` (`id`, `category_id`, `brand_id`, `unit_id`, `secondary_unit_id`, `conversion_factor`, `sku`, `barcode`, `hsn_code`, `product_name`, `description`, `cost_price`, `selling_price`, `gst_percentage`, `opening_stock`, `current_stock`, `minimum_stock`, `batch_tracking`, `image`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('8', '3', '6', '1', NULL, NULL, 'SKU-008', 'BC-008', '6109', 'Cotton Round Neck T-Shirt', NULL, '250.00', '499.00', '5.00', '200.00', '187.00', '30.00', '0', NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 21:57:28', NULL, NULL);
INSERT INTO `products` (`id`, `category_id`, `brand_id`, `unit_id`, `secondary_unit_id`, `conversion_factor`, `sku`, `barcode`, `hsn_code`, `product_name`, `description`, `cost_price`, `selling_price`, `gst_percentage`, `opening_stock`, `current_stock`, `minimum_stock`, `batch_tracking`, `image`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('9', '1', '2', '1', NULL, NULL, 'SKU-009', 'BC-009', '8528', 'LG 43 inch Smart TV', NULL, '28000.00', '35000.00', '18.00', '15.00', '13.00', '3.00', '0', NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `products` (`id`, `category_id`, `brand_id`, `unit_id`, `secondary_unit_id`, `conversion_factor`, `sku`, `barcode`, `hsn_code`, `product_name`, `description`, `cost_price`, `selling_price`, `gst_percentage`, `opening_stock`, `current_stock`, `minimum_stock`, `batch_tracking`, `image`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('10', '4', '6', '5', NULL, NULL, 'SKU-010', 'BC-010', '7408', 'Copper Wire 2.5mm', NULL, '45.00', '65.00', '18.00', '1000.00', '950.00', '100.00', '0', NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);

-- Table structure for `purchase_items`
DROP TABLE IF EXISTS `purchase_items`;
CREATE TABLE `purchase_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `purchase_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `billing_unit_id` int(11) DEFAULT NULL,
  `billing_unit_name` varchar(10) DEFAULT NULL,
  `quantity` decimal(15,2) NOT NULL,
  `primary_qty` decimal(15,2) DEFAULT NULL,
  `cost_price` decimal(15,2) NOT NULL,
  `gst` decimal(5,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(15,2) NOT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_id` (`purchase_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `purchase_items_ibfk_1` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `purchase_items`
INSERT INTO `purchase_items` (`id`, `purchase_id`, `product_id`, `billing_unit_id`, `billing_unit_name`, `quantity`, `primary_qty`, `cost_price`, `gst`, `amount`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', '1', '1', NULL, NULL, '10.00', NULL, '60000.00', '18.00', '708000.00', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `purchase_items` (`id`, `purchase_id`, `product_id`, `billing_unit_id`, `billing_unit_name`, `quantity`, `primary_qty`, `cost_price`, `gst`, `amount`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', '2', '4', NULL, NULL, '100.00', NULL, '400.00', '5.00', '42000.00', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `purchase_items` (`id`, `purchase_id`, `product_id`, `billing_unit_id`, `billing_unit_name`, `quantity`, `primary_qty`, `cost_price`, `gst`, `amount`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('3', '2', '7', NULL, NULL, '100.00', NULL, '180.00', '5.00', '18900.00', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `purchase_items` (`id`, `purchase_id`, `product_id`, `billing_unit_id`, `billing_unit_name`, `quantity`, `primary_qty`, `cost_price`, `gst`, `amount`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('4', '3', '3', NULL, NULL, '10.00', NULL, '3000.00', '12.00', '33600.00', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `purchase_items` (`id`, `purchase_id`, `product_id`, `billing_unit_id`, `billing_unit_name`, `quantity`, `primary_qty`, `cost_price`, `gst`, `amount`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('5', '3', '8', NULL, NULL, '50.00', NULL, '250.00', '5.00', '13125.00', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `purchase_items` (`id`, `purchase_id`, `product_id`, `billing_unit_id`, `billing_unit_name`, `quantity`, `primary_qty`, `cost_price`, `gst`, `amount`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('6', '4', '1', '1', 'PCS', '38.00', '38.00', '60000.00', '18.00', '2280000.00', 'ACTIVE', '2026-07-08 09:40:50', '2026-07-08 09:40:50', NULL, NULL);
INSERT INTO `purchase_items` (`id`, `purchase_id`, `product_id`, `billing_unit_id`, `billing_unit_name`, `quantity`, `primary_qty`, `cost_price`, `gst`, `amount`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('7', '4', '2', '1', 'PCS', '27.00', '27.00', '55000.00', '18.00', '1485000.00', 'ACTIVE', '2026-07-08 09:40:50', '2026-07-08 09:40:50', NULL, NULL);
INSERT INTO `purchase_items` (`id`, `purchase_id`, `product_id`, `billing_unit_id`, `billing_unit_name`, `quantity`, `primary_qty`, `cost_price`, `gst`, `amount`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('8', '4', '3', '1', 'PCS', '15.00', '15.00', '3000.00', '18.00', '45000.00', 'ACTIVE', '2026-07-08 09:40:50', '2026-07-08 09:40:50', NULL, NULL);
INSERT INTO `purchase_items` (`id`, `purchase_id`, `product_id`, `billing_unit_id`, `billing_unit_name`, `quantity`, `primary_qty`, `cost_price`, `gst`, `amount`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('9', '5', '1', '1', 'PCS', '88.00', '88.00', '60000.00', '18.00', '5280000.00', 'ACTIVE', '2026-07-08 09:40:50', '2026-07-08 09:40:50', NULL, NULL);
INSERT INTO `purchase_items` (`id`, `purchase_id`, `product_id`, `billing_unit_id`, `billing_unit_name`, `quantity`, `primary_qty`, `cost_price`, `gst`, `amount`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('10', '5', '2', '1', 'PCS', '41.00', '41.00', '55000.00', '18.00', '2255000.00', 'ACTIVE', '2026-07-08 09:40:50', '2026-07-08 09:40:50', NULL, NULL);
INSERT INTO `purchase_items` (`id`, `purchase_id`, `product_id`, `billing_unit_id`, `billing_unit_name`, `quantity`, `primary_qty`, `cost_price`, `gst`, `amount`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('11', '5', '3', '1', 'PCS', '36.00', '36.00', '3000.00', '18.00', '108000.00', 'ACTIVE', '2026-07-08 09:40:50', '2026-07-08 09:40:50', NULL, NULL);

-- Table structure for `purchase_return_items`
DROP TABLE IF EXISTS `purchase_return_items`;
CREATE TABLE `purchase_return_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `purchase_return_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` decimal(15,2) NOT NULL,
  `cost_price` decimal(15,2) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_return_id` (`purchase_return_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `purchase_return_items_ibfk_1` FOREIGN KEY (`purchase_return_id`) REFERENCES `purchase_returns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_return_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `purchase_return_items`
-- Table structure for `purchase_returns`
DROP TABLE IF EXISTS `purchase_returns`;
CREATE TABLE `purchase_returns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `purchase_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `return_no` varchar(50) NOT NULL,
  `return_date` date NOT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `return_no` (`return_no`),
  KEY `purchase_id` (`purchase_id`),
  KEY `supplier_id` (`supplier_id`),
  CONSTRAINT `purchase_returns_ibfk_1` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`),
  CONSTRAINT `purchase_returns_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `purchase_returns`
-- Table structure for `purchases`
DROP TABLE IF EXISTS `purchases`;
CREATE TABLE `purchases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `purchase_no` varchar(50) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `purchase_date` date NOT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `gst_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_status` varchar(20) DEFAULT 'UNPAID',
  `order_status` varchar(20) DEFAULT 'PENDING',
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_no` (`purchase_no`),
  KEY `supplier_id` (`supplier_id`),
  KEY `idx_purchases_purchase_no` (`purchase_no`),
  CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `purchases`
INSERT INTO `purchases` (`id`, `purchase_no`, `supplier_id`, `purchase_date`, `subtotal`, `discount`, `gst_amount`, `total_amount`, `payment_status`, `order_status`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', 'PO-0001', '1', '2026-06-05', '600000.00', '0.00', '108000.00', '708000.00', 'PAID', 'PENDING', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);
INSERT INTO `purchases` (`id`, `purchase_no`, `supplier_id`, `purchase_date`, `subtotal`, `discount`, `gst_amount`, `total_amount`, `payment_status`, `order_status`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', 'PO-0002', '2', '2026-06-08', '72000.00', '0.00', '3600.00', '75600.00', 'PAID', 'PENDING', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);
INSERT INTO `purchases` (`id`, `purchase_no`, `supplier_id`, `purchase_date`, `subtotal`, `discount`, `gst_amount`, `total_amount`, `payment_status`, `order_status`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('3', 'PO-0003', '3', '2026-06-10', '50000.00', '0.00', '2500.00', '52500.00', 'PARTIAL', 'PENDING', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);
INSERT INTO `purchases` (`id`, `purchase_no`, `supplier_id`, `purchase_date`, `subtotal`, `discount`, `gst_amount`, `total_amount`, `payment_status`, `order_status`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('4', 'PO-1001', '1', '2026-07-03', '5000.00', '200.00', '864.00', '5664.00', 'PAID', 'PENDING', 'ACTIVE', '2026-07-08 09:40:50', '2026-07-08 09:40:50', NULL, NULL);
INSERT INTO `purchases` (`id`, `purchase_no`, `supplier_id`, `purchase_date`, `subtotal`, `discount`, `gst_amount`, `total_amount`, `payment_status`, `order_status`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('5', 'PO-1002', '1', '2026-07-06', '15000.00', '0.00', '2700.00', '17700.00', 'PENDING', 'PENDING', 'ACTIVE', '2026-07-08 09:40:50', '2026-07-08 09:40:50', NULL, NULL);

-- Table structure for `quotation_items`
DROP TABLE IF EXISTS `quotation_items`;
CREATE TABLE `quotation_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quotation_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `billing_unit_id` int(11) DEFAULT NULL,
  `billing_unit_name` varchar(10) DEFAULT NULL,
  `quantity` decimal(15,2) NOT NULL,
  `primary_qty` decimal(15,2) DEFAULT NULL,
  `rate` decimal(15,2) NOT NULL,
  `gst` decimal(5,2) DEFAULT 0.00,
  `discount` decimal(5,2) DEFAULT 0.00,
  `amount` decimal(15,2) NOT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quotation_id` (`quotation_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `quotation_items_ibfk_1` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quotation_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `quotation_items`
INSERT INTO `quotation_items` (`id`, `quotation_id`, `product_id`, `billing_unit_id`, `billing_unit_name`, `quantity`, `primary_qty`, `rate`, `gst`, `discount`, `amount`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', '1', '2', NULL, NULL, '2.00', NULL, '70000.00', '18.00', '0.00', '165200.00', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `quotation_items` (`id`, `quotation_id`, `product_id`, `billing_unit_id`, `billing_unit_name`, `quantity`, `primary_qty`, `rate`, `gst`, `discount`, `amount`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', '1', '5', NULL, NULL, '50.00', NULL, '120.00', '18.00', '0.00', '7080.00', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `quotation_items` (`id`, `quotation_id`, `product_id`, `billing_unit_id`, `billing_unit_name`, `quantity`, `primary_qty`, `rate`, `gst`, `discount`, `amount`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('3', '2', '3', NULL, NULL, '2.00', NULL, '5000.00', '12.00', '5.00', '10640.00', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);

-- Table structure for `quotations`
DROP TABLE IF EXISTS `quotations`;
CREATE TABLE `quotations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quotation_no` varchar(50) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `quotation_date` date NOT NULL,
  `valid_until` date DEFAULT NULL,
  `subtotal` decimal(15,2) DEFAULT 0.00,
  `discount` decimal(15,2) DEFAULT 0.00,
  `gst_amount` decimal(15,2) DEFAULT 0.00,
  `grand_total` decimal(15,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `converted_invoice_id` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'DRAFT',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quotation_no` (`quotation_no`),
  KEY `customer_id` (`customer_id`),
  KEY `idx_quotations_no` (`quotation_no`),
  CONSTRAINT `quotations_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `quotations`
INSERT INTO `quotations` (`id`, `quotation_no`, `customer_id`, `quotation_date`, `valid_until`, `subtotal`, `discount`, `gst_amount`, `grand_total`, `notes`, `converted_invoice_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', 'QT-0001', '3', '2026-06-18', '2026-07-18', '145000.00', '0.00', '26100.00', '171100.00', 'Bulk order quotation for office setup', NULL, 'SENT', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);
INSERT INTO `quotations` (`id`, `quotation_no`, `customer_id`, `quotation_date`, `valid_until`, `subtotal`, `discount`, `gst_amount`, `grand_total`, `notes`, `converted_invoice_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', 'QT-0002', '5', '2026-06-22', '2026-07-22', '10000.00', '500.00', '1140.00', '10640.00', 'Shoes for team - corporate order', NULL, 'DRAFT', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);

-- Table structure for `report_logs`
DROP TABLE IF EXISTS `report_logs`;
CREATE TABLE `report_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_name` varchar(150) NOT NULL,
  `generated_by` int(11) NOT NULL,
  `generated_date` date NOT NULL,
  `filters` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `generated_by` (`generated_by`),
  CONSTRAINT `report_logs_ibfk_1` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `report_logs`
-- Table structure for `role_permissions`
DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `role_permissions`
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', '1', '1', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', '1', '2', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('3', '1', '3', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('4', '1', '4', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('5', '1', '5', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('6', '1', '6', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('7', '1', '7', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('8', '1', '8', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('9', '1', '9', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('10', '1', '10', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('11', '1', '11', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('12', '1', '12', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('13', '1', '13', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('14', '1', '14', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('15', '1', '15', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('16', '2', '1', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('17', '2', '2', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('18', '2', '3', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('19', '2', '4', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('20', '2', '5', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('21', '2', '6', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('22', '2', '7', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('23', '2', '8', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('24', '2', '12', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('25', '2', '13', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('26', '2', '14', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('27', '2', '15', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('28', '3', '1', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('29', '3', '2', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('30', '3', '4', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('31', '3', '6', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('32', '3', '12', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('33', '3', '13', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('34', '3', '15', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('35', '4', '1', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('36', '4', '7', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('37', '4', '8', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('38', '4', '15', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);

-- Table structure for `roles`
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `roles`
INSERT INTO `roles` (`id`, `role_name`, `description`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', 'Super Admin', 'Full system access, user management, and settings configuration', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `roles` (`id`, `role_name`, `description`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', 'Admin', 'Access to products, invoices, expenses, customers, suppliers, and reports', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `roles` (`id`, `role_name`, `description`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('3', 'Staff / Cashier', 'Access to billing, products, and customer directories', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `roles` (`id`, `role_name`, `description`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('4', 'Accountant', 'Access to expenses, financial entries, and reports', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);

-- Table structure for `sales_return_items`
DROP TABLE IF EXISTS `sales_return_items`;
CREATE TABLE `sales_return_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sales_return_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` decimal(15,2) NOT NULL,
  `rate` decimal(15,2) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sales_return_id` (`sales_return_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `sales_return_items_ibfk_1` FOREIGN KEY (`sales_return_id`) REFERENCES `sales_returns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_return_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `sales_return_items`
-- Table structure for `sales_returns`
DROP TABLE IF EXISTS `sales_returns`;
CREATE TABLE `sales_returns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `return_no` varchar(50) NOT NULL,
  `return_date` date NOT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `return_no` (`return_no`),
  KEY `invoice_id` (`invoice_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `sales_returns_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  CONSTRAINT `sales_returns_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `sales_returns`
-- Table structure for `stock_transactions`
DROP TABLE IF EXISTS `stock_transactions`;
CREATE TABLE `stock_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `transaction_type` varchar(50) NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `quantity` decimal(15,2) NOT NULL,
  `stock_before` decimal(15,2) NOT NULL DEFAULT 0.00,
  `stock_after` decimal(15,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_stock_transactions_product_id` (`product_id`),
  CONSTRAINT `stock_transactions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `stock_transactions`
INSERT INTO `stock_transactions` (`id`, `product_id`, `transaction_type`, `reference_no`, `quantity`, `stock_before`, `stock_after`, `remarks`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', '1', 'Purchase', 'PO-0001', '10.00', '40.00', '50.00', 'Purchase from Tech Supplies', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);
INSERT INTO `stock_transactions` (`id`, `product_id`, `transaction_type`, `reference_no`, `quantity`, `stock_before`, `stock_after`, `remarks`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', '4', 'Purchase', 'PO-0002', '100.00', '100.00', '200.00', 'Purchase from Global Groceries', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);
INSERT INTO `stock_transactions` (`id`, `product_id`, `transaction_type`, `reference_no`, `quantity`, `stock_before`, `stock_after`, `remarks`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('3', '7', 'Purchase', 'PO-0002', '100.00', '200.00', '300.00', 'Purchase from Global Groceries', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);
INSERT INTO `stock_transactions` (`id`, `product_id`, `transaction_type`, `reference_no`, `quantity`, `stock_before`, `stock_after`, `remarks`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('4', '3', 'Purchase', 'PO-0003', '10.00', '90.00', '100.00', 'Purchase from Fashion Hub', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);
INSERT INTO `stock_transactions` (`id`, `product_id`, `transaction_type`, `reference_no`, `quantity`, `stock_before`, `stock_after`, `remarks`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('5', '8', 'Purchase', 'PO-0003', '50.00', '150.00', '200.00', 'Purchase from Fashion Hub', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);
INSERT INTO `stock_transactions` (`id`, `product_id`, `transaction_type`, `reference_no`, `quantity`, `stock_before`, `stock_after`, `remarks`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('6', '1', 'Sale', 'INV-0001', '-1.00', '50.00', '49.00', 'Sale to Rajesh Sharma', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);
INSERT INTO `stock_transactions` (`id`, `product_id`, `transaction_type`, `reference_no`, `quantity`, `stock_before`, `stock_after`, `remarks`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('7', '3', 'Sale', 'INV-0002', '-1.00', '100.00', '99.00', 'Sale to Priya Patel', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);
INSERT INTO `stock_transactions` (`id`, `product_id`, `transaction_type`, `reference_no`, `quantity`, `stock_before`, `stock_after`, `remarks`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('8', '6', 'Sale', 'INV-0003', '-1.00', '30.00', '29.00', 'Sale to Amit Verma', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);
INSERT INTO `stock_transactions` (`id`, `product_id`, `transaction_type`, `reference_no`, `quantity`, `stock_before`, `stock_after`, `remarks`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('9', '4', 'Sale', 'INV-0004', '-6.00', '200.00', '194.00', 'Sale to Sunita Desai', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);
INSERT INTO `stock_transactions` (`id`, `product_id`, `transaction_type`, `reference_no`, `quantity`, `stock_before`, `stock_after`, `remarks`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('10', '9', 'Sale', 'INV-0005', '-1.00', '15.00', '14.00', 'Sale to Vikram Singh', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', '1', NULL);
INSERT INTO `stock_transactions` (`id`, `product_id`, `transaction_type`, `reference_no`, `quantity`, `stock_before`, `stock_after`, `remarks`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('11', '8', 'Sale', 'INV-2026-00006', '-1.00', '188.00', '187.00', 'Invoice: INV-2026-00006', 'ACTIVE', '2026-06-29 21:57:28', '2026-06-29 21:57:28', '1', NULL);

-- Table structure for `supplier_payments`
DROP TABLE IF EXISTS `supplier_payments`;
CREATE TABLE `supplier_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`),
  CONSTRAINT `supplier_payments_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `supplier_payments`
INSERT INTO `supplier_payments` (`id`, `supplier_id`, `payment_date`, `amount`, `payment_method`, `reference_no`, `notes`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', '1', '2026-06-06', '708000.00', 'NET_BANKING', 'NEFT-11223344', 'Full payment for PO-0001', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `supplier_payments` (`id`, `supplier_id`, `payment_date`, `amount`, `payment_method`, `reference_no`, `notes`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', '3', '2026-06-12', '30000.00', 'UPI', 'UPI-55667788', 'Partial payment for PO-0003', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);

-- Table structure for `suppliers`
DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `mobile` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `gst_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `opening_balance` decimal(15,2) DEFAULT 0.00,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mobile` (`mobile`),
  KEY `idx_suppliers_mobile` (`mobile`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `suppliers`
INSERT INTO `suppliers` (`id`, `supplier_name`, `contact_person`, `mobile`, `email`, `gst_number`, `address`, `city`, `state`, `country`, `opening_balance`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', 'Tech Supplies India Pvt Ltd', 'Anand Mehta', '9876543214', 'anand@techsupplies.com', '27EEEEE1234E1Z5', '10 Tech Park, Hinjewadi', 'Pune', 'Maharashtra', 'India', '1000.00', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `suppliers` (`id`, `supplier_name`, `contact_person`, `mobile`, `email`, `gst_number`, `address`, `city`, `state`, `country`, `opening_balance`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', 'Global Groceries Wholesale', 'Deepa Nair', '9876543215', 'deepa@globalgroceries.com', '33FFFFF1234F1Z5', '20 Food Avenue, T Nagar', 'Chennai', 'Tamil Nadu', 'India', '0.00', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `suppliers` (`id`, `supplier_name`, `contact_person`, `mobile`, `email`, `gst_number`, `address`, `city`, `state`, `country`, `opening_balance`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('3', 'Fashion Hub Distributors', 'Karan Shah', '9876543218', 'karan@fashionhub.com', '27GGGGG1234G1Z5', '55 Crawford Market', 'Mumbai', 'Maharashtra', 'India', '2500.00', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);

-- Table structure for `unit_conversions`
DROP TABLE IF EXISTS `unit_conversions`;
CREATE TABLE `unit_conversions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `primary_unit_id` int(11) NOT NULL,
  `secondary_unit_id` int(11) NOT NULL,
  `conversion_factor` decimal(15,4) NOT NULL DEFAULT 1.0000,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `primary_unit_id` (`primary_unit_id`,`secondary_unit_id`),
  KEY `idx_unit_conv_primary` (`primary_unit_id`),
  KEY `idx_unit_conv_secondary` (`secondary_unit_id`),
  CONSTRAINT `unit_conversions_ibfk_1` FOREIGN KEY (`primary_unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE,
  CONSTRAINT `unit_conversions_ibfk_2` FOREIGN KEY (`secondary_unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `unit_conversions`
INSERT INTO `unit_conversions` (`id`, `primary_unit_id`, `secondary_unit_id`, `conversion_factor`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', '2', '1', '12.0000', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);

-- Table structure for `units`
DROP TABLE IF EXISTS `units`;
CREATE TABLE `units` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_name` varchar(50) NOT NULL,
  `short_name` varchar(10) NOT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unit_name` (`unit_name`),
  UNIQUE KEY `short_name` (`short_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `units`
INSERT INTO `units` (`id`, `unit_name`, `short_name`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', 'Pieces', 'Pcs', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `units` (`id`, `unit_name`, `short_name`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', 'Box', 'Box', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `units` (`id`, `unit_name`, `short_name`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('3', 'Kilograms', 'Kg', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `units` (`id`, `unit_name`, `short_name`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('4', 'Liters', 'Ltr', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);
INSERT INTO `units` (`id`, `unit_name`, `short_name`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('5', 'Meters', 'Mtr', 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);

-- Table structure for `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `status` varchar(20) DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `mobile` (`mobile`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `users`
INSERT INTO `users` (`id`, `role_id`, `name`, `email`, `mobile`, `password`, `profile_image`, `last_login`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('1', '1', 'Super Admin User', 'grovixo@gmail.com', '9876543210', '$2y$12$QxctayrdzNTNi9RUa3sHc.3fym5z8YyTNCebwIjYHsG0VrI163.ue', NULL, NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-07-08 09:43:09', NULL, NULL);
INSERT INTO `users` (`id`, `role_id`, `name`, `email`, `mobile`, `password`, `profile_image`, `last_login`, `status`, `created_at`, `updated_at`, `created_by`, `deleted_at`) VALUES ('2', '3', 'Demo Cashier', 'staff@grovixo.com', '9876543220', '$2y$12$QxctayrdzNTNi9RUa3sHc.3fym5z8YyTNCebwIjYHsG0VrI163.ue', NULL, NULL, 'ACTIVE', '2026-06-29 20:22:32', '2026-06-29 20:22:32', NULL, NULL);

SET FOREIGN_KEY_CHECKS=1;
