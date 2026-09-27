-- =============================================================================
-- SmartAuto ERP — Full Database Schema
-- Automobile Spare Parts Distribution & Sales Management System
-- Tech: MySQL 8+ / MariaDB 10.3+ | No ORM | Prepared statements ready
-- Tables: 31 | Entities cover all 18 core modules
-- =============================================================================

CREATE DATABASE IF NOT EXISTS smartauto_erp
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE smartauto_erp;

SET FOREIGN_KEY_CHECKS = 0;

-- Drop Views (if exist)
DROP VIEW IF EXISTS v_vehicle_parts_finder;
DROP VIEW IF EXISTS v_employee_sales_performance;
DROP VIEW IF EXISTS v_daily_sales_summary;
DROP VIEW IF EXISTS v_product_stock;
DROP VIEW IF EXISTS v_low_stock_products;

DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS deliveries;
DROP TABLE IF EXISTS sale_return_items;
DROP TABLE IF EXISTS sale_returns;
DROP TABLE IF EXISTS sale_items;
DROP TABLE IF EXISTS sales;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS purchase_order_items;
DROP TABLE IF EXISTS purchase_orders;
DROP TABLE IF EXISTS stock_movements;
DROP TABLE IF EXISTS inventory;
DROP TABLE IF EXISTS product_compatibility;
DROP TABLE IF EXISTS vehicle_engines;
DROP TABLE IF EXISTS vehicle_models;
DROP TABLE IF EXISTS vehicle_brands;
DROP TABLE IF EXISTS supplier_products;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS brands;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS suppliers;
DROP TABLE IF EXISTS shops;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS employee_sales_targets;
DROP TABLE IF EXISTS employee_attendance;
DROP TABLE IF EXISTS employees;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS sequences;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- 1. AUTHENTICATION & USER MANAGEMENT
-- =============================================================================

CREATE TABLE roles (
    id          TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50)  NOT NULL UNIQUE,
    slug        VARCHAR(50)  NOT NULL UNIQUE,
    description VARCHAR(255) DEFAULT NULL,
    permissions JSON         DEFAULT NULL COMMENT 'Module-level permission flags',
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id             TINYINT UNSIGNED NOT NULL,
    username            VARCHAR(60)  NOT NULL UNIQUE,
    email               VARCHAR(150) NOT NULL UNIQUE,
    password_hash       VARCHAR(255) NOT NULL,
    full_name           VARCHAR(150) NOT NULL,
    phone               VARCHAR(20)  DEFAULT NULL,
    avatar              VARCHAR(255) DEFAULT NULL,
    is_active           TINYINT(1)   NOT NULL DEFAULT 1,
    last_login_at       DATETIME     DEFAULT NULL,
    password_changed_at DATETIME     DEFAULT NULL,
    deleted_at          DATETIME     DEFAULT NULL,
    created_at          TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

CREATE TABLE employees (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id           INT UNSIGNED NOT NULL UNIQUE,
    employee_code     VARCHAR(20)  NOT NULL UNIQUE,
    designation       VARCHAR(100) NOT NULL,
    department        ENUM('sales','store','admin','delivery') NOT NULL DEFAULT 'sales',
    hire_date         DATE         NOT NULL,
    base_salary       DECIMAL(12,2) DEFAULT 0.00,
    commission_rate   DECIMAL(5,2)  DEFAULT 0.00 COMMENT 'Percentage commission on sales',
    address           TEXT         DEFAULT NULL,
    emergency_contact VARCHAR(150) DEFAULT NULL,
    emergency_phone   VARCHAR(20)  DEFAULT NULL,
    deleted_at        DATETIME     DEFAULT NULL,
    created_at        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_employees_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE employee_attendance (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,
    attend_date DATE         NOT NULL,
    check_in    TIME         DEFAULT NULL,
    check_out   TIME         DEFAULT NULL,
    status      ENUM('present','absent','half_day','leave') NOT NULL DEFAULT 'present',
    notes       VARCHAR(255) DEFAULT NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_employee_date (employee_id, attend_date),
    CONSTRAINT fk_attendance_employee FOREIGN KEY (employee_id) REFERENCES employees(id)
) ENGINE=InnoDB;

CREATE TABLE employee_sales_targets (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id     INT UNSIGNED NOT NULL,
    target_month    TINYINT UNSIGNED NOT NULL COMMENT '1-12',
    target_year     SMALLINT UNSIGNED NOT NULL,
    target_amount   DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    achieved_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_employee_target (employee_id, target_month, target_year),
    CONSTRAINT fk_targets_employee FOREIGN KEY (employee_id) REFERENCES employees(id)
) ENGINE=InnoDB;

CREATE TABLE activity_logs (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED DEFAULT NULL,
    action      VARCHAR(100) NOT NULL,
    module      VARCHAR(50)  NOT NULL,
    record_type VARCHAR(50)  DEFAULT NULL,
    record_id   INT UNSIGNED DEFAULT NULL,
    description TEXT         DEFAULT NULL,
    ip_address  VARCHAR(45)  DEFAULT NULL,
    user_agent  VARCHAR(255) DEFAULT NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_logs_user (user_id),
    INDEX idx_logs_module (module, created_at),
    CONSTRAINT fk_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE settings (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key   VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT         NOT NULL,
    setting_group VARCHAR(50)  NOT NULL DEFAULT 'general',
    description   VARCHAR(255) DEFAULT NULL,
    updated_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE sequences (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seq_type      VARCHAR(50) NOT NULL UNIQUE COMMENT 'invoice, product_code, purchase_order, order, sale, payment',
    prefix        VARCHAR(20) NOT NULL DEFAULT '',
    current_value INT UNSIGNED NOT NULL DEFAULT 0,
    pad_length    TINYINT UNSIGNED NOT NULL DEFAULT 5,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================================================
-- 2. CUSTOMERS & SHOPS
-- =============================================================================

CREATE TABLE customers (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_code  VARCHAR(20)  NOT NULL UNIQUE,
    customer_type  ENUM('shop','walking') NOT NULL DEFAULT 'shop',
    name           VARCHAR(150) NOT NULL,
    contact_person VARCHAR(150) DEFAULT NULL,
    phone          VARCHAR(20)  DEFAULT NULL,
    email          VARCHAR(150) DEFAULT NULL,
    address        TEXT         DEFAULT NULL,
    city           VARCHAR(100) DEFAULT NULL,
    notes          TEXT         DEFAULT NULL,
    is_active      TINYINT(1)   NOT NULL DEFAULT 1,
    deleted_at     DATETIME     DEFAULT NULL,
    created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customers_type (customer_type),
    INDEX idx_customers_name (name)
) ENGINE=InnoDB;

CREATE TABLE shops (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id        INT UNSIGNED NOT NULL UNIQUE,
    shop_name          VARCHAR(150) NOT NULL,
    registration_no    VARCHAR(50)  DEFAULT NULL,
    credit_limit       DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    credit_balance     DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Outstanding credit owed',
    payment_terms_days TINYINT UNSIGNED NOT NULL DEFAULT 30,
    assigned_rep_id    INT UNSIGNED DEFAULT NULL COMMENT 'Assigned sales representative',
    deleted_at         DATETIME     DEFAULT NULL,
    created_at         TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_shops_rep (assigned_rep_id),
    CONSTRAINT fk_shops_customer FOREIGN KEY (customer_id) REFERENCES customers(id),
    CONSTRAINT fk_shops_rep FOREIGN KEY (assigned_rep_id) REFERENCES employees(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =============================================================================
-- 3. SUPPLIERS
-- =============================================================================

CREATE TABLE suppliers (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_code       VARCHAR(20)  NOT NULL UNIQUE,
    company_name        VARCHAR(150) NOT NULL,
    contact_person      VARCHAR(150) DEFAULT NULL,
    phone               VARCHAR(20)  NOT NULL,
    email               VARCHAR(150) DEFAULT NULL,
    address             TEXT         DEFAULT NULL,
    city                VARCHAR(100) DEFAULT NULL,
    payment_terms       VARCHAR(100) DEFAULT NULL,
    outstanding_balance DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    is_active           TINYINT(1)   NOT NULL DEFAULT 1,
    notes               TEXT         DEFAULT NULL,
    deleted_at          DATETIME     DEFAULT NULL,
    created_at          TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================================================
-- 4. PRODUCT CATALOG
-- =============================================================================

CREATE TABLE categories (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL UNIQUE,
    slug        VARCHAR(100) NOT NULL UNIQUE,
    description TEXT         DEFAULT NULL,
    parent_id   INT UNSIGNED DEFAULT NULL,
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    deleted_at  DATETIME     DEFAULT NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE brands (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL UNIQUE,
    slug       VARCHAR(100) NOT NULL UNIQUE,
    country    VARCHAR(100) DEFAULT NULL,
    is_active  TINYINT(1)   NOT NULL DEFAULT 1,
    deleted_at DATETIME     DEFAULT NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE products (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_code    VARCHAR(30)  NOT NULL UNIQUE,
    barcode         VARCHAR(50)  DEFAULT NULL UNIQUE,
    name            VARCHAR(200) NOT NULL,
    description     TEXT         DEFAULT NULL,
    category_id     INT UNSIGNED NOT NULL,
    brand_id        INT UNSIGNED DEFAULT NULL,
    unit            VARCHAR(20)  NOT NULL DEFAULT 'pcs',
    cost_price      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    selling_price   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    wholesale_price DECIMAL(12,2) DEFAULT NULL,
    image_path      VARCHAR(255) DEFAULT NULL,
    specifications  JSON         DEFAULT NULL COMMENT 'Free-form specs: dimensions, material, etc.',
    is_active       TINYINT(1)   NOT NULL DEFAULT 1,
    deleted_at      DATETIME     DEFAULT NULL,
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_products_category (category_id),
    INDEX idx_products_brand (brand_id),
    INDEX idx_products_name (name),
    FULLTEXT INDEX ft_products_search (name, product_code, barcode, description),
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id),
    CONSTRAINT fk_products_brand FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE supplier_products (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    supplier_id    INT UNSIGNED NOT NULL,
    product_id     INT UNSIGNED NOT NULL,
    supplier_sku   VARCHAR(50)  DEFAULT NULL,
    cost_price     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    lead_time_days TINYINT UNSIGNED DEFAULT NULL,
    is_preferred   TINYINT(1) NOT NULL DEFAULT 0,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_supplier_product (supplier_id, product_id),
    CONSTRAINT fk_sp_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    CONSTRAINT fk_sp_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

-- =============================================================================
-- 5. VEHICLE COMPATIBILITY
-- =============================================================================

CREATE TABLE vehicle_brands (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL UNIQUE,
    country    VARCHAR(100) DEFAULT NULL,
    is_active  TINYINT(1)   NOT NULL DEFAULT 1,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE vehicle_models (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vehicle_brand_id INT UNSIGNED NOT NULL,
    name             VARCHAR(100) NOT NULL,
    body_type        VARCHAR(50)  DEFAULT NULL COMMENT 'sedan, suv, hatchback, etc.',
    is_active        TINYINT(1)   NOT NULL DEFAULT 1,
    created_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_brand_model (vehicle_brand_id, name),
    CONSTRAINT fk_vmodels_brand FOREIGN KEY (vehicle_brand_id) REFERENCES vehicle_brands(id)
) ENGINE=InnoDB;

CREATE TABLE vehicle_engines (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vehicle_model_id INT UNSIGNED NOT NULL,
    engine_code      VARCHAR(50)  DEFAULT NULL,
    displacement_cc  INT UNSIGNED DEFAULT NULL,
    fuel_type        ENUM('petrol','diesel','hybrid','electric','lpg','cng') NOT NULL DEFAULT 'petrol',
    transmission     ENUM('manual','automatic','cvt','dct') DEFAULT NULL,
    year_from        SMALLINT UNSIGNED NOT NULL,
    year_to          SMALLINT UNSIGNED DEFAULT NULL COMMENT 'NULL = still in production',
    is_active        TINYINT(1) NOT NULL DEFAULT 1,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_engine_model (vehicle_model_id),
    INDEX idx_engine_years (year_from, year_to),
    CONSTRAINT fk_engines_model FOREIGN KEY (vehicle_model_id) REFERENCES vehicle_models(id)
) ENGINE=InnoDB;

CREATE TABLE product_compatibility (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id        INT UNSIGNED NOT NULL,
    vehicle_brand_id  INT UNSIGNED DEFAULT NULL COMMENT 'NULL = all brands (universal part)',
    vehicle_model_id  INT UNSIGNED DEFAULT NULL COMMENT 'NULL = all models of brand',
    vehicle_engine_id INT UNSIGNED DEFAULT NULL COMMENT 'NULL = all engines of model',
    year_from         SMALLINT UNSIGNED DEFAULT NULL,
    year_to           SMALLINT UNSIGNED DEFAULT NULL,
    notes             VARCHAR(255) DEFAULT NULL,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_compat_product (product_id),
    INDEX idx_compat_vehicle (vehicle_brand_id, vehicle_model_id, vehicle_engine_id),
    CONSTRAINT fk_compat_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_compat_vbrand FOREIGN KEY (vehicle_brand_id) REFERENCES vehicle_brands(id) ON DELETE CASCADE,
    CONSTRAINT fk_compat_vmodel FOREIGN KEY (vehicle_model_id) REFERENCES vehicle_models(id) ON DELETE CASCADE,
    CONSTRAINT fk_compat_engine FOREIGN KEY (vehicle_engine_id) REFERENCES vehicle_engines(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================================================
-- 6. INVENTORY MANAGEMENT
-- =============================================================================

CREATE TABLE inventory (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id        INT UNSIGNED NOT NULL UNIQUE,
    quantity_on_hand  INT NOT NULL DEFAULT 0,
    quantity_reserved INT NOT NULL DEFAULT 0 COMMENT 'Reserved for pending orders',
    quantity_damaged  INT NOT NULL DEFAULT 0,
    reorder_level     INT UNSIGNED NOT NULL DEFAULT 10,
    reorder_quantity  INT UNSIGNED NOT NULL DEFAULT 50,
    last_stock_in_at  DATETIME DEFAULT NULL,
    last_stock_out_at DATETIME DEFAULT NULL,
    updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_inventory_product FOREIGN KEY (product_id) REFERENCES products(id),
    INDEX idx_inventory_low_stock (quantity_on_hand, reorder_level)
) ENGINE=InnoDB;

CREATE TABLE stock_movements (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id      INT UNSIGNED NOT NULL,
    movement_type   ENUM(
                        'purchase_in','sale_out','adjustment_in','adjustment_out',
                        'transfer_in','transfer_out','return_in','return_out','damaged'
                    ) NOT NULL,
    quantity        INT NOT NULL COMMENT 'Positive = in, negative = out',
    quantity_before INT NOT NULL,
    quantity_after  INT NOT NULL,
    unit_cost       DECIMAL(12,2) DEFAULT NULL,
    reference_type  VARCHAR(50)  DEFAULT NULL COMMENT 'sale, purchase_order, order, adjustment',
    reference_id    INT UNSIGNED DEFAULT NULL,
    notes           VARCHAR(255) DEFAULT NULL,
    created_by      INT UNSIGNED DEFAULT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_movements_product (product_id, created_at),
    INDEX idx_movements_ref (reference_type, reference_id),
    CONSTRAINT fk_movements_product FOREIGN KEY (product_id) REFERENCES products(id),
    CONSTRAINT fk_movements_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =============================================================================
-- 7. PURCHASE MANAGEMENT
-- =============================================================================

CREATE TABLE purchase_orders (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    po_number       VARCHAR(30)  NOT NULL UNIQUE,
    supplier_id     INT UNSIGNED NOT NULL,
    order_date      DATE         NOT NULL,
    expected_date   DATE         DEFAULT NULL,
    status          ENUM('draft','pending','partial','received','cancelled') NOT NULL DEFAULT 'draft',
    subtotal        DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    total_amount    DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    amount_paid     DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    notes           TEXT         DEFAULT NULL,
    created_by      INT UNSIGNED DEFAULT NULL,
    received_by     INT UNSIGNED DEFAULT NULL,
    received_at     DATETIME     DEFAULT NULL,
    deleted_at      DATETIME     DEFAULT NULL,
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_po_supplier (supplier_id),
    INDEX idx_po_status (status),
    CONSTRAINT fk_po_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    CONSTRAINT fk_po_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_po_received_by FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE purchase_order_items (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    purchase_order_id INT UNSIGNED NOT NULL,
    product_id        INT UNSIGNED NOT NULL,
    quantity_ordered  INT UNSIGNED NOT NULL,
    quantity_received INT UNSIGNED NOT NULL DEFAULT 0,
    unit_cost         DECIMAL(12,2) NOT NULL,
    line_total        DECIMAL(14,2) NOT NULL,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_poi_po FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_poi_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

-- =============================================================================
-- 8. ORDER MANAGEMENT
-- =============================================================================

CREATE TABLE orders (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_number    VARCHAR(30)  NOT NULL UNIQUE,
    customer_id     INT UNSIGNED NOT NULL,
    sales_rep_id    INT UNSIGNED DEFAULT NULL,
    order_date      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status          ENUM('pending','confirmed','processing','ready','delivered','cancelled') NOT NULL DEFAULT 'pending',
    order_source    ENUM('pos','shop_portal','rep_field','phone','walk_in') NOT NULL DEFAULT 'rep_field',
    subtotal        DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    total_amount    DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    payment_status  ENUM('unpaid','partial','paid','credit') NOT NULL DEFAULT 'unpaid',
    delivery_address TEXT        DEFAULT NULL,
    notes           TEXT         DEFAULT NULL,
    deleted_at      DATETIME     DEFAULT NULL,
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_orders_customer (customer_id),
    INDEX idx_orders_rep (sales_rep_id),
    INDEX idx_orders_status (status, order_date),
    CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES customers(id),
    CONSTRAINT fk_orders_rep FOREIGN KEY (sales_rep_id) REFERENCES employees(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id        INT UNSIGNED NOT NULL,
    product_id      INT UNSIGNED NOT NULL,
    quantity        INT UNSIGNED NOT NULL,
    unit_price      DECIMAL(12,2) NOT NULL,
    discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    line_total      DECIMAL(14,2) NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_oi_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_oi_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

-- =============================================================================
-- 9. SALES MANAGEMENT & INVOICING
-- =============================================================================

CREATE TABLE sales (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_number  VARCHAR(30)  NOT NULL UNIQUE,
    order_id        INT UNSIGNED DEFAULT NULL COMMENT 'Linked order if converted from order',
    customer_id     INT UNSIGNED NOT NULL,
    sales_rep_id    INT UNSIGNED DEFAULT NULL,
    sale_date       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    sale_type       ENUM('pos','invoice','credit') NOT NULL DEFAULT 'pos',
    payment_method  ENUM('cash','card','bank_transfer','credit','mixed') NOT NULL DEFAULT 'cash',
    subtotal        DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    total_amount    DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    amount_paid     DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    change_amount   DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    payment_status  ENUM('paid','partial','unpaid','refunded') NOT NULL DEFAULT 'paid',
    notes           TEXT         DEFAULT NULL,
    deleted_at      DATETIME     DEFAULT NULL,
    created_by      INT UNSIGNED DEFAULT NULL,
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_sales_customer (customer_id),
    INDEX idx_sales_rep (sales_rep_id),
    INDEX idx_sales_date (sale_date),
    CONSTRAINT fk_sales_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    CONSTRAINT fk_sales_customer FOREIGN KEY (customer_id) REFERENCES customers(id),
    CONSTRAINT fk_sales_rep FOREIGN KEY (sales_rep_id) REFERENCES employees(id) ON DELETE SET NULL,
    CONSTRAINT fk_sales_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE sale_items (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sale_id         INT UNSIGNED NOT NULL,
    product_id      INT UNSIGNED NOT NULL,
    quantity        INT UNSIGNED NOT NULL,
    unit_price      DECIMAL(12,2) NOT NULL,
    cost_price      DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Snapshot for profit calc',
    discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    line_total      DECIMAL(14,2) NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_si_sale FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    CONSTRAINT fk_si_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE TABLE sale_returns (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    return_number VARCHAR(30) NOT NULL UNIQUE,
    sale_id       INT UNSIGNED NOT NULL,
    return_date   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reason        TEXT        DEFAULT NULL,
    refund_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    refund_method ENUM('cash','credit','bank_transfer') NOT NULL DEFAULT 'cash',
    status        ENUM('pending','approved','completed','rejected') NOT NULL DEFAULT 'pending',
    processed_by  INT UNSIGNED DEFAULT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_returns_sale FOREIGN KEY (sale_id) REFERENCES sales(id),
    CONSTRAINT fk_returns_processed FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE sale_return_items (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sale_return_id INT UNSIGNED NOT NULL,
    product_id     INT UNSIGNED NOT NULL,
    quantity       INT UNSIGNED NOT NULL,
    unit_price     DECIMAL(12,2) NOT NULL,
    line_total     DECIMAL(14,2) NOT NULL,
    condition_note VARCHAR(255) DEFAULT NULL COMMENT 'resellable, damaged, etc.',
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sri_return FOREIGN KEY (sale_return_id) REFERENCES sale_returns(id) ON DELETE CASCADE,
    CONSTRAINT fk_sri_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

-- =============================================================================
-- 10. DELIVERY MANAGEMENT
-- =============================================================================

CREATE TABLE deliveries (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    delivery_number VARCHAR(30)  NOT NULL UNIQUE,
    order_id        INT UNSIGNED DEFAULT NULL,
    sale_id         INT UNSIGNED DEFAULT NULL,
    customer_id     INT UNSIGNED NOT NULL,
    delivery_rep_id INT UNSIGNED DEFAULT NULL,
    status          ENUM('pending','in_transit','delivered','failed','returned') NOT NULL DEFAULT 'pending',
    scheduled_date  DATE         DEFAULT NULL,
    delivered_at    DATETIME     DEFAULT NULL,
    delivery_address TEXT        DEFAULT NULL,
    recipient_name  VARCHAR(150) DEFAULT NULL,
    recipient_phone VARCHAR(20)  DEFAULT NULL,
    notes           TEXT         DEFAULT NULL,
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_deliveries_status (status),
    INDEX idx_deliveries_rep (delivery_rep_id),
    CONSTRAINT fk_deliveries_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    CONSTRAINT fk_deliveries_sale FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE SET NULL,
    CONSTRAINT fk_deliveries_customer FOREIGN KEY (customer_id) REFERENCES customers(id),
    CONSTRAINT fk_deliveries_rep FOREIGN KEY (delivery_rep_id) REFERENCES employees(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =============================================================================
-- 11. PAYMENTS
-- =============================================================================

CREATE TABLE payments (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_number VARCHAR(30)  NOT NULL UNIQUE,
    payable_type   ENUM('sale','purchase_order','customer_credit','supplier') NOT NULL,
    payable_id     INT UNSIGNED NOT NULL,
    customer_id    INT UNSIGNED DEFAULT NULL,
    supplier_id    INT UNSIGNED DEFAULT NULL,
    payment_date   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    amount         DECIMAL(14,2) NOT NULL,
    payment_method ENUM('cash','card','bank_transfer','cheque','credit') NOT NULL DEFAULT 'cash',
    reference_no   VARCHAR(100) DEFAULT NULL COMMENT 'Cheque no, transaction ID, etc.',
    notes          TEXT         DEFAULT NULL,
    received_by    INT UNSIGNED DEFAULT NULL,
    created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_payments_payable (payable_type, payable_id),
    INDEX idx_payments_date (payment_date),
    CONSTRAINT fk_payments_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    CONSTRAINT fk_payments_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    CONSTRAINT fk_payments_received_by FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =============================================================================
-- 12. NOTIFICATIONS
-- =============================================================================

CREATE TABLE notifications (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        INT UNSIGNED DEFAULT NULL COMMENT 'NULL = broadcast to role/all',
    role_id        TINYINT UNSIGNED DEFAULT NULL,
    type           ENUM(
                       'low_stock','new_order','pending_order','credit_due',
                       'purchase_arrival','delivery_update','system'
                   ) NOT NULL,
    title          VARCHAR(200) NOT NULL,
    message        TEXT         NOT NULL,
    link_url       VARCHAR(255) DEFAULT NULL,
    reference_type VARCHAR(50)  DEFAULT NULL,
    reference_id   INT UNSIGNED DEFAULT NULL,
    is_read        TINYINT(1)   NOT NULL DEFAULT 0,
    read_at        DATETIME     DEFAULT NULL,
    created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notifications_user (user_id, is_read),
    INDEX idx_notifications_type (type, created_at),
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_notifications_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================================================
-- SEED & OPERATIONAL DATA (Merged from production/staging backup)
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- Core RBAC & System Configuration
-- -----------------------------------------------------------------------------

-- Roles (RBAC system baseline) (`roles`)
INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `permissions`, `created_at`, `updated_at`) VALUES
(1, 'Business Owner', 'owner', 'Full system access', '{\"all\": true}', '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(2, 'Sales Representative', 'sales_rep', 'Mobile POS, orders, customers', '{\"sales\": true, \"customers\": true, \"orders\": true, \"pos\": true}', '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(3, 'Store Manager', 'store_manager', 'Inventory, purchasing, products', '{\"inventory\": true, \"purchasing\": true, \"products\": true}', '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(4, 'Shop Customer', 'shop_customer', 'B2B portal: catalog, orders', '{\"catalog\": true, \"orders\": true}', '2026-09-22 19:05:32', '2026-09-22 19:05:32');

-- Auto-numbering sequences (`sequences`)
INSERT INTO `sequences` (`id`, `seq_type`, `prefix`, `current_value`, `pad_length`, `updated_at`) VALUES
(1, 'invoice', 'INV-', 1128, 6, '2026-09-27 16:27:37'),
(2, 'product_code', 'PRD-', 8, 5, '2026-09-22 19:05:32'),
(3, 'purchase_order', 'PO-', 1, 5, '2026-09-27 17:12:34'),
(4, 'order', 'ORD-', 19, 5, '2026-09-27 15:16:15'),
(5, 'sale', 'SL-', 2, 5, '2026-09-27 16:27:37'),
(6, 'payment', 'PAY-', 2, 5, '2026-09-27 16:27:37'),
(7, 'delivery', 'DLV-', 16, 5, '2026-09-27 15:16:15'),
(8, 'return', 'RET-', 0, 5, '2026-09-22 19:05:32');

-- Business & application settings (`settings`)
INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `description`, `updated_at`) VALUES
(1, 'business_name', 'SmartAuto Spare Parts', 'general', 'Company display name', '2026-09-22 19:05:32'),
(2, 'business_address', '123 Industrial Zone, Colombo', 'general', 'Business address', '2026-09-22 19:05:32'),
(3, 'business_phone', '+94 11 234 5678', 'general', 'Main contact number', '2026-09-22 19:05:32'),
(4, 'business_email', 'info@smartauto.lk', 'general', 'Main contact email', '2026-09-22 19:05:32'),
(6, 'currency', 'LKR', 'general', 'Currency code', '2026-09-22 19:05:32'),
(7, 'currency_symbol', 'Rs.', 'general', 'Currency display symbol', '2026-09-22 19:05:32'),
(8, 'invoice_footer', 'Thank you for your business!', 'invoice', 'Invoice footer text', '2026-09-22 19:05:32'),
(9, 'low_stock_alert', '1', 'inventory', 'Enable low stock notifications', '2026-09-22 19:05:32');

-- -----------------------------------------------------------------------------
-- Users & Human Resources
-- -----------------------------------------------------------------------------

-- User accounts (`users`)
INSERT INTO `users` (`id`, `role_id`, `username`, `email`, `password_hash`, `full_name`, `phone`, `avatar`, `is_active`, `last_login_at`, `password_changed_at`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'admin', 'admin@smartauto.lk', '$2y$10$YWrc5sjqHrNNS862m6VmteCEKvrNYCfmvrQOgJj7kB.NgJxdhtGxW', 'System Administrator', '+94 77 000 0001', NULL, 1, '2026-09-27 22:30:54', NULL, NULL, '2026-09-22 19:05:32', '2026-09-27 17:00:54'),
(3, 2, 'salesrep', 'sales@smartauto.lk', '$2y$10$GCob/Ah9jdGdukSAWCHaZuCLXKZGWMtd/Ah/LtwKo7AP0MH/W6PL2', 'Sunil Perera', '+94 77 000 0002', NULL, 1, '2026-09-27 21:56:08', NULL, NULL, '2026-09-26 19:36:20', '2026-09-27 16:26:08'),
(4, 3, 'storemanager', 'store@smartauto.lk', '$2y$10$sb9hfhQpzkoSXWSpM6oBZ.8RrtuHeFioFHxTGthAIWPRfTTBirFQu', 'Kamal Fernando', '+94 77 000 0003', NULL, 1, '2026-09-27 21:59:04', NULL, NULL, '2026-09-26 19:36:20', '2026-09-27 16:29:04'),
(5, 4, 'shopcustomer', 'customer@smartauto.lk', '$2y$10$nnQu349N5qZ9Tnwuc7YsoOHgPpr2g5No43PzUCZNCl..i3j2HdJmy', 'Nimal Jayawardena', '+94 77 111 0001', NULL, 1, '2026-09-27 22:19:53', NULL, NULL, '2026-09-26 19:36:21', '2026-09-27 16:49:53'),
(14, 4, 'test_shop_1790454785', 'test_shop_1790454785@example.com', '$2y$10$Pxe.QVmsOhwFdPZO4YVSJO3wZOVXZrvuNnoQ2GQpDaGhsE/0rXmBm', 'Test Shop Owner', '0719876543', NULL, 1, '2026-09-27 02:03:05', NULL, NULL, '2026-09-26 20:33:05', '2026-09-26 20:33:05'),
(15, 4, 'test_shop_1790454807', 'test_shop_1790454807@example.com', '$2y$10$x0bd4sLONug5TvfUMiOnS.hFgjta25MT5Bdwx3q3kGoafaAdVsVby', 'Test Shop Owner', '0719876543', NULL, 1, '2026-09-27 02:03:28', NULL, NULL, '2026-09-26 20:33:27', '2026-09-26 20:33:28'),
(16, 4, 'test_shop_1790454825', 'test_shop_1790454825@example.com', '$2y$10$aGmBOJRf5KClWnWmGZ1hzeFrDe9gs0gmsVdROUeS1q8WVDXQCSWnq', 'Test Shop Owner', '0719876543', NULL, 1, '2026-09-27 02:03:45', NULL, NULL, '2026-09-26 20:33:45', '2026-09-26 20:33:45'),
(30, 4, 'user_shop_customer_1790521775901', 'user_shop_customer_1790521775901@testapf.lk', '$2y$10$976vi3soUOL3KeeQeNyDEuqOC/2zCUKguClN0PX2rtasi7pbam2la', 'Test Shop customer', '0776491431', NULL, 1, NULL, NULL, NULL, '2026-09-27 15:09:35', '2026-09-27 15:09:35'),
(31, 2, 'user_sales_rep_1790521775901', 'user_sales_rep_1790521775901@testapf.lk', '$2y$10$ZF34ElJe6d5m7XU..vas2.l.0JF3WZtOMk.JWdq2whFp7XWeS8MZW', 'Test Sales rep', '0772107985', NULL, 1, NULL, NULL, NULL, '2026-09-27 15:09:36', '2026-09-27 15:09:36'),
(32, 3, 'user_store_manager_1790521775901', 'user_store_manager_1790521775901@testapf.lk', '$2y$10$2Po/X9OhRF8HN.PWT4kPvukCJuKtldvboTa0KKSwukQ66ZdcS5xmC', 'Test Store manager', '0771750323', NULL, 1, NULL, NULL, NULL, '2026-09-27 15:09:36', '2026-09-27 15:09:36'),
(33, 1, 'user_owner_1790521775901', 'user_owner_1790521775901@testapf.lk', '$2y$10$NWIdS9ESoECTI/ywnwHbpO/.mQgo9v912Gq2.2qhvNHddeawPA6ii', 'Test Owner', '0778741598', NULL, 1, NULL, NULL, NULL, '2026-09-27 15:09:36', '2026-09-27 15:09:36'),
(34, 2, 'user_crud_4098', 'user_crud_4098@testsmartauto.lk', '$2y$10$dakleZBLhjVdarjs7Rksaed3q5IkbSW.p8rzAgJmNpCKXk8d/5.pi', 'Audit Test User', '0775265754', NULL, 1, NULL, NULL, NULL, '2026-09-27 15:12:25', '2026-09-27 15:12:25'),
(35, 2, 'user_crud_9650', 'updated_user_crud_9650@testsmartauto.lk', '$2y$10$qf0i5zo9CLgF4ttXt0zemePaA9EoRiru0HGM3bT1VOHHtw//Zsp3K', 'Audit Test User UPDATED', '0771246100', NULL, 0, NULL, NULL, '2026-09-27 20:43:04', '2026-09-27 15:13:04', '2026-09-27 15:13:04'),
(36, 2, 'user_crud_7457', 'updated_user_crud_7457@testsmartauto.lk', '$2y$10$vmcKD8zT7c9CH65YMaIHIufCb9nnSdHtloITHdp8t2725vNbaPXPi', 'Audit Test User UPDATED', '0772735201', NULL, 0, NULL, NULL, '2026-09-27 20:43:59', '2026-09-27 15:13:59', '2026-09-27 15:13:59'),
(37, 2, 'user_crud_5256', 'updated_user_crud_5256@testsmartauto.lk', '$2y$10$4KdDCxO/0HUNmSFmrHVYQOgX5rFl4eFXeV2NevxqT7BJiiBAlsYYC', 'Audit Test User UPDATED', '0774659025', NULL, 0, NULL, NULL, '2026-09-27 20:44:16', '2026-09-27 15:14:16', '2026-09-27 15:14:16'),
(38, 2, 'user_crud_5680', 'updated_user_crud_5680@testsmartauto.lk', '$2y$10$es/SuR4YL3ijqk0VG2k1P.50ZUvYWMPdNZBLHvKr50rHE7x1O3QYq', 'Audit Test User UPDATED', '0775580939', NULL, 0, NULL, NULL, '2026-09-27 20:44:54', '2026-09-27 15:14:54', '2026-09-27 15:14:54'),
(39, 2, 'user_crud_6531', 'updated_user_crud_6531@testsmartauto.lk', '$2y$10$kvHMByJv29S9SW8qvH/p6e3ty5.BETwIEbPJausQDcj69fMKd9klS', 'Audit Test User UPDATED', '0775347427', NULL, 0, NULL, NULL, '2026-09-27 20:45:42', '2026-09-27 15:15:42', '2026-09-27 15:15:42'),
(40, 2, 'staff_2827', 'staff_2827@smartauto.lk', '$2y$10$VNqpHDZ5u30/QXv1o1mUeO2VeL2symMTkdo8G4AiSriFjkmANPPPG', 'Staff Member 2827', '0774318340', NULL, 0, NULL, NULL, NULL, '2026-09-27 15:15:44', '2026-09-27 15:15:44'),
(41, 4, 'user_shop_customer_1790522164263', 'user_shop_customer_1790522164263@testapf.lk', '$2y$10$H0sNNAO8C7.k19KRf/x83uai.A5BHyH.Ycm79BP7RpXn48o0T.58q', 'Test Shop customer', '0771547425', NULL, 1, NULL, NULL, NULL, '2026-09-27 15:16:04', '2026-09-27 15:16:04'),
(42, 2, 'user_sales_rep_1790522164263', 'user_sales_rep_1790522164263@testapf.lk', '$2y$10$k5tu9CQ4Ol.GS2Q4XRO/dus4fnAGLOulwI0gjm9FdCcc6tBMsbK9y', 'Test Sales rep', '0775930151', NULL, 1, NULL, NULL, NULL, '2026-09-27 15:16:05', '2026-09-27 15:16:05'),
(43, 3, 'user_store_manager_1790522164263', 'user_store_manager_1790522164263@testapf.lk', '$2y$10$Ktg242bHrXn1Zeswia9sg.dI2mrHf9FplGrWsB49ej0NU3WHI8fSS', 'Test Store manager', '0773136667', NULL, 1, NULL, NULL, NULL, '2026-09-27 15:16:05', '2026-09-27 15:16:05'),
(44, 1, 'user_owner_1790522164263', 'user_owner_1790522164263@testapf.lk', '$2y$10$B5X0JVZuMDKjQk/C7bNnvee8A5HSeTzj9QgaDaVdZMVDuk4fMeDI6', 'Test Owner', '0775041295', NULL, 1, NULL, NULL, NULL, '2026-09-27 15:16:05', '2026-09-27 15:16:05'),
(45, 2, 'user_crud_3409', 'updated_user_crud_3409@testsmartauto.lk', '$2y$10$O4fKUWGf/SJnQ1GmL7hbfu1LEEKqF2cy5DI2.mLe./8WpTYMW9jZW', 'Audit Test User UPDATED', '0775558622', NULL, 0, NULL, NULL, '2026-09-27 20:46:14', '2026-09-27 15:16:14', '2026-09-27 15:16:14');

-- Employee profiles & sales representatives (`employees`)
INSERT INTO `employees` (`id`, `user_id`, `employee_code`, `designation`, `department`, `hire_date`, `base_salary`, `commission_rate`, `address`, `emergency_contact`, `emergency_phone`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'EMP-001', 'Owner / Administrator', 'admin', '2026-09-23', 0.00, 0.00, NULL, NULL, NULL, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(2, 3, 'EMP-002', 'Sales Representative', 'sales', '2026-09-27', 0.00, 0.00, NULL, NULL, NULL, NULL, '2026-09-26 19:36:20', '2026-09-26 19:36:20'),
(3, 4, 'EMP-003', 'Store Manager', 'store', '2026-09-27', 0.00, 0.00, NULL, NULL, NULL, NULL, '2026-09-26 19:36:20', '2026-09-26 19:36:20'),
(14, 31, 'EMP-00031', 'Sales Representative', 'sales', '2026-09-27', 0.00, 0.00, NULL, NULL, NULL, NULL, '2026-09-27 15:09:36', '2026-09-27 15:09:36'),
(15, 32, 'EMP-00032', 'Store Manager', 'store', '2026-09-27', 0.00, 0.00, NULL, NULL, NULL, NULL, '2026-09-27 15:09:36', '2026-09-27 15:09:36'),
(16, 33, 'EMP-00033', 'Business Owner', 'admin', '2026-09-27', 0.00, 0.00, NULL, NULL, NULL, NULL, '2026-09-27 15:09:36', '2026-09-27 15:09:36'),
(17, 34, 'EMP-00034', 'Staff Member', 'sales', '2026-09-27', 0.00, 0.00, NULL, NULL, NULL, NULL, '2026-09-27 15:12:25', '2026-09-27 15:12:25'),
(18, 35, 'EMP-00035', 'Staff Member', 'sales', '2026-09-27', 0.00, 0.00, NULL, NULL, NULL, '2026-09-27 20:43:04', '2026-09-27 15:13:04', '2026-09-27 15:13:04'),
(19, 36, 'EMP-00036', 'Staff Member', 'sales', '2026-09-27', 0.00, 0.00, NULL, NULL, NULL, '2026-09-27 20:43:59', '2026-09-27 15:13:59', '2026-09-27 15:13:59'),
(20, 37, 'EMP-00037', 'Staff Member', 'sales', '2026-09-27', 0.00, 0.00, NULL, NULL, NULL, '2026-09-27 20:44:16', '2026-09-27 15:14:16', '2026-09-27 15:14:16'),
(21, 38, 'EMP-00038', 'Staff Member', 'sales', '2026-09-27', 0.00, 0.00, NULL, NULL, NULL, '2026-09-27 20:44:54', '2026-09-27 15:14:54', '2026-09-27 15:14:54'),
(22, 39, 'EMP-00039', 'Staff Member', 'sales', '2026-09-27', 0.00, 0.00, NULL, NULL, NULL, '2026-09-27 20:45:42', '2026-09-27 15:15:42', '2026-09-27 15:15:42'),
(23, 40, 'EMP-02827', 'Sales Associate', 'sales', '2026-09-27', 65000.00, 2.50, NULL, NULL, NULL, '2026-09-27 20:45:44', '2026-09-27 15:15:44', '2026-09-27 15:15:44'),
(24, 42, 'EMP-02828', 'Sales Representative', 'sales', '2026-09-27', 0.00, 0.00, NULL, NULL, NULL, NULL, '2026-09-27 15:16:05', '2026-09-27 15:16:05'),
(25, 43, 'EMP-02829', 'Store Manager', 'store', '2026-09-27', 0.00, 0.00, NULL, NULL, NULL, NULL, '2026-09-27 15:16:05', '2026-09-27 15:16:05'),
(26, 44, 'EMP-02830', 'Business Owner', 'admin', '2026-09-27', 0.00, 0.00, NULL, NULL, NULL, NULL, '2026-09-27 15:16:05', '2026-09-27 15:16:05'),
(27, 45, 'EMP-00045', 'Staff Member', 'sales', '2026-09-27', 0.00, 0.00, NULL, NULL, NULL, '2026-09-27 20:46:14', '2026-09-27 15:16:14', '2026-09-27 15:16:14');

-- Audit activity logs (`activity_logs`)
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `module`, `record_type`, `record_id`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'update', 'users', 'user', 1, 'Updated user account: admin@smartauto.lk', '::1', NULL, '2026-09-24 14:08:01'),
(2, 1, 'create', 'users', 'user', 34, 'Created new user account: user_crud_4098@testsmartauto.lk (user_crud_4098)', '::1', NULL, '2026-09-27 15:12:25'),
(3, 1, 'create', 'users', 'user', 35, 'Created new user account: user_crud_9650@testsmartauto.lk (user_crud_9650)', '::1', NULL, '2026-09-27 15:13:04'),
(4, 1, 'update', 'users', 'user', 35, 'Updated user account: updated_user_crud_9650@testsmartauto.lk', '::1', NULL, '2026-09-27 15:13:04'),
(5, 1, 'delete', 'users', 'user', 35, 'Deactivated/deleted user updated_user_crud_9650@testsmartauto.lk', '::1', NULL, '2026-09-27 15:13:04'),
(6, 1, 'create', 'users', 'user', 36, 'Created new user account: user_crud_7457@testsmartauto.lk (user_crud_7457)', '::1', NULL, '2026-09-27 15:13:59'),
(7, 1, 'update', 'users', 'user', 36, 'Updated user account: updated_user_crud_7457@testsmartauto.lk', '::1', NULL, '2026-09-27 15:13:59'),
(8, 1, 'delete', 'users', 'user', 36, 'Deactivated/deleted user updated_user_crud_7457@testsmartauto.lk', '::1', NULL, '2026-09-27 15:13:59'),
(9, 1, 'create', 'users', 'user', 37, 'Created new user account: user_crud_5256@testsmartauto.lk (user_crud_5256)', '::1', NULL, '2026-09-27 15:14:16'),
(10, 1, 'update', 'users', 'user', 37, 'Updated user account: updated_user_crud_5256@testsmartauto.lk', '::1', NULL, '2026-09-27 15:14:16'),
(11, 1, 'delete', 'users', 'user', 37, 'Deactivated/deleted user updated_user_crud_5256@testsmartauto.lk', '::1', NULL, '2026-09-27 15:14:16'),
(12, 1, 'create', 'users', 'user', 38, 'Created new user account: user_crud_5680@testsmartauto.lk (user_crud_5680)', '::1', NULL, '2026-09-27 15:14:54'),
(13, 1, 'update', 'users', 'user', 38, 'Updated user account: updated_user_crud_5680@testsmartauto.lk', '::1', NULL, '2026-09-27 15:14:54'),
(14, 1, 'delete', 'users', 'user', 38, 'Deactivated/deleted user updated_user_crud_5680@testsmartauto.lk', '::1', NULL, '2026-09-27 15:14:54'),
(15, 1, 'create', 'users', 'user', 39, 'Created new user account: user_crud_6531@testsmartauto.lk (user_crud_6531)', '::1', NULL, '2026-09-27 15:15:42'),
(16, 1, 'update', 'users', 'user', 39, 'Updated user account: updated_user_crud_6531@testsmartauto.lk', '::1', NULL, '2026-09-27 15:15:42'),
(17, 1, 'delete', 'users', 'user', 39, 'Deactivated/deleted user updated_user_crud_6531@testsmartauto.lk', '::1', NULL, '2026-09-27 15:15:42'),
(18, 1, 'create', 'users', 'user', 45, 'Created new user account: user_crud_3409@testsmartauto.lk (user_crud_3409)', '::1', NULL, '2026-09-27 15:16:14'),
(19, 1, 'update', 'users', 'user', 45, 'Updated user account: updated_user_crud_3409@testsmartauto.lk', '::1', NULL, '2026-09-27 15:16:14'),
(20, 1, 'delete', 'users', 'user', 45, 'Deactivated/deleted user updated_user_crud_3409@testsmartauto.lk', '::1', NULL, '2026-09-27 15:16:14');

-- -----------------------------------------------------------------------------
-- Master Product Catalog & Compatibility
-- -----------------------------------------------------------------------------

-- Product categories (`categories`)
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `parent_id`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Brakes', 'brakes', 'Brake pads, discs, calipers, fluids', NULL, 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(2, 'Filters', 'filters', 'Oil, air, fuel, cabin filters', NULL, 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(3, 'Engine Parts', 'engine-parts', 'Pistons, gaskets, belts, pumps', NULL, 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(4, 'Electrical', 'electrical', 'Alternators, starters, sensors, bulbs', NULL, 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(5, 'Suspension', 'suspension', 'Shocks, struts, bushings, springs', NULL, 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(6, 'Body Parts', 'body-parts', 'Bumpers, mirrors, fenders, lights', NULL, 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(7, 'Fluids', 'fluids', 'Engine oil, coolant, brake fluid', NULL, 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(8, 'Ignition', 'ignition', 'Spark plugs, coils, distributors', NULL, 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32');

-- Product aftermarket brands (`brands`)
INSERT INTO `brands` (`id`, `name`, `slug`, `country`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Bosch', 'bosch', 'Germany', 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(2, 'NGK', 'ngk', 'Japan', 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(3, 'Denso', 'denso', 'Japan', 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(4, 'Brembo', 'brembo', 'Italy', 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(5, 'Mann', 'mann', 'Germany', 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(6, 'KYB', 'kyb', 'Japan', 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(7, 'Gates', 'gates', 'USA', 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(8, 'Exide', 'exide', 'India', 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32');

-- Suppliers (`suppliers`)
INSERT INTO `suppliers` (`id`, `supplier_code`, `company_name`, `contact_person`, `phone`, `email`, `address`, `city`, `payment_terms`, `outstanding_balance`, `is_active`, `notes`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'SUP-001', 'Lanka Auto Imports Pvt Ltd', 'Mr. Perera', '+94 11 555 1001', 'orders@lankaauto.lk', NULL, 'Colombo', 'Net 30 days', 0.00, 1, NULL, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(2, 'SUP-002', 'Japan Parts Wholesale', 'Mr. Tanaka', '+94 11 555 1002', 'sales@jpparts.lk', NULL, 'Kelaniya', 'Net 45 days', 0.00, 1, NULL, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(3, 'SUP-003', 'Ceylon Motor Supplies', 'Ms. Fernando', '+94 11 555 1003', 'info@ceylonmotor.lk', NULL, 'Negombo', 'Net 30 days', 0.00, 1, NULL, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32');

-- B2B shop & retail customers (`customers`)
INSERT INTO `customers` (`id`, `customer_code`, `customer_type`, `name`, `contact_person`, `phone`, `email`, `address`, `city`, `notes`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'CUS-00001', 'shop', 'City Auto Works', 'Mr. Silva', '+94771110001', 'customer@smartauto.lk', '78 Temple Road, Colombo 03', 'Dehiwala', NULL, 1, NULL, '2026-09-22 19:05:32', '2026-09-27 15:16:15'),
(2, 'CUS-00002', 'shop', 'Highway Garage & Parts', 'Mr. Jayawardena', '+94 77 111 0002', 'highway@garage.lk', 'Km 12, Colombo-Kandy Road', 'Kadawatha', NULL, 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(3, 'CUS-00003', 'shop', 'Nuwara Motors', 'Mr. Kumara', '+94 77 111 0003', 'nuwara@motors.lk', 'Main Street, Nuwara Eliya', 'Nuwara Eliya', NULL, 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(4, 'CUS-00004', 'walking', 'Walk-in Customer', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(6, 'CUS-00005', 'walking', 'czxczxc', NULL, '0712345678', NULL, 'zxzxczxcxz', NULL, NULL, 1, NULL, '2026-09-24 14:16:55', '2026-09-24 14:16:55'),
(9, 'CUS-00014', 'shop', 'Test Shop Owner', 'Test Shop Owner', '0719876543', 'test_shop_1790454785@example.com', NULL, NULL, NULL, 1, NULL, '2026-09-26 20:33:05', '2026-09-26 20:33:05'),
(10, 'CUS-00015', 'shop', 'Test Shop Owner', 'Test Shop Owner', '0719876543', 'test_shop_1790454807@example.com', NULL, NULL, NULL, 1, NULL, '2026-09-26 20:33:27', '2026-09-26 20:33:27'),
(11, 'CUS-00016', 'shop', 'Test Shop Owner', 'Test Shop Owner', '0719876543', 'test_shop_1790454825@example.com', '123 Kandy Road, Colombo 03', NULL, NULL, 1, NULL, '2026-09-26 20:33:45', '2026-09-26 20:33:45'),
(16, 'CUS-00012', 'walking', 'Test Customer', NULL, '+94 77 123 9999', 'test@example.com', '123 Sample St', NULL, NULL, 1, NULL, '2026-09-27 09:12:29', '2026-09-27 09:12:29'),
(25, 'CUS-00030', 'shop', 'Test Shop customer', 'Test Shop customer', '0776491431', 'user_shop_customer_1790521775901@testapf.lk', NULL, NULL, NULL, 1, NULL, '2026-09-27 15:09:35', '2026-09-27 15:09:35'),
(26, 'CUS-00026', 'shop', 'AutoTest Garage 2519', NULL, '0711610411', 'garage2190@testapf.lk', '123 Kandy Road', 'Kadawatha', NULL, 1, NULL, '2026-09-27 15:10:40', '2026-09-27 15:10:40'),
(27, 'CUS-00027', 'shop', 'AutoTest Garage UPDATED', NULL, '0719558842', 'garage_updated@testapf.lk', '456 Colombo Road', 'Kelaniya', NULL, 1, NULL, '2026-09-27 15:10:41', '2026-09-27 15:10:41'),
(28, 'CUS-00028', 'shop', 'AutoTest Garage 8064', NULL, '0716298505', 'garage2136@testapf.lk', '123 Kandy Road', 'Kadawatha', NULL, 1, NULL, '2026-09-27 15:12:23', '2026-09-27 15:12:23'),
(29, 'CUS-00029', 'shop', 'AutoTest Garage UPDATED', NULL, '0717582541', 'garage_updated@testapf.lk', '456 Colombo Road', 'Kelaniya', NULL, 1, NULL, '2026-09-27 15:12:23', '2026-09-27 15:12:23'),
(32, 'CUS-00031', 'shop', 'AutoTest Garage 5300', NULL, '0719380294', 'garage5829@testapf.lk', '123 Kandy Road', 'Kadawatha', NULL, 1, NULL, '2026-09-27 15:13:58', '2026-09-27 15:13:58'),
(33, 'CUS-00033', 'shop', 'AutoTest Garage UPDATED', NULL, '0715349990', 'garage_updated@testapf.lk', '456 Colombo Road', 'Kelaniya', NULL, 1, NULL, '2026-09-27 15:13:58', '2026-09-27 15:13:58'),
(34, 'CUS-00034', 'shop', 'AutoTest Garage 5205', NULL, '0718992782', 'garage8386@testapf.lk', '123 Kandy Road', 'Kadawatha', NULL, 1, NULL, '2026-09-27 15:14:14', '2026-09-27 15:14:14'),
(35, 'CUS-00035', 'shop', 'AutoTest Garage UPDATED', NULL, '0719992627', 'garage_updated@testapf.lk', '456 Colombo Road', 'Kelaniya', NULL, 1, NULL, '2026-09-27 15:14:14', '2026-09-27 15:14:14'),
(36, 'CUS-00036', 'shop', 'AutoTest Garage 2778', NULL, '0718135138', 'garage5663@testapf.lk', '123 Kandy Road', 'Kadawatha', NULL, 1, NULL, '2026-09-27 15:14:52', '2026-09-27 15:14:52'),
(37, 'CUS-00037', 'shop', 'AutoTest Garage UPDATED', NULL, '0713952801', 'garage_updated@testapf.lk', '456 Colombo Road', 'Kelaniya', NULL, 1, NULL, '2026-09-27 15:14:52', '2026-09-27 15:14:52'),
(38, 'CUS-00038', 'shop', 'AutoTest Garage 1313', NULL, '0717490214', 'garage1984@testapf.lk', '123 Kandy Road', 'Kadawatha', NULL, 1, NULL, '2026-09-27 15:15:39', '2026-09-27 15:15:39'),
(39, 'CUS-00039', 'shop', 'AutoTest Garage UPDATED', NULL, '0717128139', 'garage_updated@testapf.lk', '456 Colombo Road', 'Kelaniya', NULL, 1, NULL, '2026-09-27 15:15:40', '2026-09-27 15:15:40'),
(40, 'CUS-00041', 'shop', 'Test Shop customer', 'Test Shop customer', '0771547425', 'user_shop_customer_1790522164263@testapf.lk', NULL, NULL, NULL, 1, NULL, '2026-09-27 15:16:04', '2026-09-27 15:16:04'),
(41, 'CUS-00042', 'shop', 'AutoTest Garage 3104', NULL, '0715857549', 'garage7799@testapf.lk', '123 Kandy Road', 'Kadawatha', NULL, 1, NULL, '2026-09-27 15:16:12', '2026-09-27 15:16:12'),
(42, 'CUS-00043', 'shop', 'AutoTest Garage UPDATED', NULL, '0714706885', 'garage_updated@testapf.lk', '456 Colombo Road', 'Kelaniya', NULL, 1, NULL, '2026-09-27 15:16:13', '2026-09-27 15:16:13');

-- B2B shops & credit profiles (`shops`)
INSERT INTO `shops` (`id`, `customer_id`, `shop_name`, `registration_no`, `credit_limit`, `credit_balance`, `payment_terms_days`, `assigned_rep_id`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'City Auto Works', NULL, 500000.00, 0.00, 30, NULL, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(2, 2, 'Highway Garage & Parts', NULL, 750000.00, 0.00, 45, NULL, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(3, 3, 'Nuwara Motors', NULL, 300000.00, 0.00, 30, NULL, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(6, 9, 'Test Shop Owner', NULL, 0.00, 0.00, 30, NULL, NULL, '2026-09-26 20:33:05', '2026-09-26 20:33:05'),
(7, 10, 'Test Shop Owner', NULL, 0.00, 0.00, 30, NULL, NULL, '2026-09-26 20:33:27', '2026-09-26 20:33:27'),
(8, 11, 'Test Shop Owner', NULL, 0.00, 0.00, 30, NULL, NULL, '2026-09-26 20:33:45', '2026-09-26 20:33:45'),
(18, 25, 'Test Shop customer', NULL, 0.00, 0.00, 30, NULL, NULL, '2026-09-27 15:09:35', '2026-09-27 15:09:35'),
(19, 26, 'AutoTest Garage 2519', NULL, 0.00, 0.00, 30, NULL, NULL, '2026-09-27 15:10:40', '2026-09-27 15:10:40'),
(20, 27, 'AutoTest Garage UPDATED', NULL, 0.00, 0.00, 30, NULL, NULL, '2026-09-27 15:10:41', '2026-09-27 15:10:41'),
(21, 28, 'AutoTest Garage 8064', NULL, 0.00, 0.00, 30, NULL, NULL, '2026-09-27 15:12:23', '2026-09-27 15:12:23'),
(22, 29, 'AutoTest Garage UPDATED', NULL, 0.00, 0.00, 30, NULL, NULL, '2026-09-27 15:12:23', '2026-09-27 15:12:23'),
(23, 32, 'AutoTest Garage 5300', NULL, 0.00, 0.00, 30, NULL, NULL, '2026-09-27 15:13:58', '2026-09-27 15:13:58'),
(24, 33, 'AutoTest Garage UPDATED', NULL, 0.00, 0.00, 30, NULL, NULL, '2026-09-27 15:13:58', '2026-09-27 15:13:58'),
(25, 34, 'AutoTest Garage 5205', NULL, 0.00, 0.00, 30, NULL, NULL, '2026-09-27 15:14:14', '2026-09-27 15:14:14'),
(26, 35, 'AutoTest Garage UPDATED', NULL, 0.00, 0.00, 30, NULL, NULL, '2026-09-27 15:14:14', '2026-09-27 15:14:14'),
(27, 36, 'AutoTest Garage 2778', NULL, 0.00, 0.00, 30, NULL, NULL, '2026-09-27 15:14:52', '2026-09-27 15:14:52'),
(28, 37, 'AutoTest Garage UPDATED', NULL, 0.00, 0.00, 30, NULL, NULL, '2026-09-27 15:14:52', '2026-09-27 15:14:52'),
(29, 38, 'AutoTest Garage 1313', NULL, 0.00, 0.00, 30, NULL, NULL, '2026-09-27 15:15:39', '2026-09-27 15:15:39'),
(30, 39, 'AutoTest Garage UPDATED', NULL, 0.00, 0.00, 30, NULL, NULL, '2026-09-27 15:15:40', '2026-09-27 15:15:40'),
(31, 40, 'Test Shop customer', NULL, 0.00, 0.00, 30, NULL, NULL, '2026-09-27 15:16:04', '2026-09-27 15:16:04'),
(32, 41, 'AutoTest Garage 3104', NULL, 0.00, 0.00, 30, NULL, NULL, '2026-09-27 15:16:12', '2026-09-27 15:16:12'),
(33, 42, 'AutoTest Garage UPDATED', NULL, 0.00, 0.00, 30, NULL, NULL, '2026-09-27 15:16:13', '2026-09-27 15:16:13');

-- Vehicle manufacturers (`vehicle_brands`)
INSERT INTO `vehicle_brands` (`id`, `name`, `country`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Toyota', 'Japan', 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(2, 'Honda', 'Japan', 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(3, 'Nissan', 'Japan', 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(4, 'Suzuki', 'Japan', 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(5, 'Mitsubishi', 'Japan', 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(6, 'Hyundai', 'South Korea', 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(7, 'Kia', 'South Korea', 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(8, 'BMW', 'Germany', 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32');

-- Vehicle models (`vehicle_models`)
INSERT INTO `vehicle_models` (`id`, `vehicle_brand_id`, `name`, `body_type`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Corolla', 'sedan', 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(2, 1, 'Hilux', 'pickup', 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(3, 1, 'RAV4', 'suv', 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(4, 2, 'Civic', 'sedan', 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(5, 2, 'Fit', 'hatchback', 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(6, 2, 'CR-V', 'suv', 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(7, 3, 'Sunny', 'sedan', 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(8, 3, 'Navara', 'pickup', 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(9, 4, 'Alto', 'hatchback', 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(10, 4, 'Wagon R', 'hatchback', 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(11, 6, 'Elantra', 'sedan', 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(12, 6, 'Tucson', 'suv', 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32');

-- Vehicle engine specifications (`vehicle_engines`)
INSERT INTO `vehicle_engines` (`id`, `vehicle_model_id`, `engine_code`, `displacement_cc`, `fuel_type`, `transmission`, `year_from`, `year_to`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, '1NZ-FE', 1497, 'petrol', 'automatic', 2014, 2019, 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(2, 1, '2NR-FKE', 1496, 'petrol', 'cvt', 2020, NULL, 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(3, 2, '2GD-FTV', 2393, 'diesel', 'manual', 2016, NULL, 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(4, 3, '3ZR-FAE', 1987, 'petrol', 'cvt', 2015, NULL, 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(5, 4, 'R18Z', 1799, 'petrol', 'manual', 2012, 2016, 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(6, 4, 'L15B', 1498, 'petrol', 'cvt', 2017, NULL, 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(7, 5, 'L15A', 1496, 'petrol', 'cvt', 2014, 2020, 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(8, 6, 'K24W', 2356, 'petrol', 'cvt', 2015, NULL, 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(9, 7, 'HR16DE', 1598, 'petrol', 'manual', 2012, 2018, 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(10, 9, 'K10B', 998, 'petrol', 'manual', 2014, NULL, 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(11, 11, 'Nu MPI', 1999, 'petrol', 'automatic', 2016, NULL, 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(12, 12, 'Theta II', 1998, 'petrol', 'automatic', 2015, NULL, 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32');

-- Spare part catalog items (`products`)
INSERT INTO `products` (`id`, `product_code`, `barcode`, `name`, `description`, `category_id`, `brand_id`, `unit`, `cost_price`, `selling_price`, `wholesale_price`, `image_path`, `specifications`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'PRD-00001', '8901234567001', 'Front Brake Pad Set - Corolla', 'Ceramic brake pads for Toyota Corolla 2014-2019', 1, 4, 'pcs', 3200.00, 4500.00, 4000.00, NULL, NULL, 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(2, 'PRD-00002', '8901234567002', 'Oil Filter - Universal', 'Spin-on oil filter compatible with most Japanese cars', 2, 6, 'pcs', 450.00, 750.00, 650.00, NULL, NULL, 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(3, 'PRD-00003', '8901234567003', 'Spark Plug Iridium (Set of 4)', 'NGK iridium spark plugs set', 8, 2, 'pcs', 2800.00, 4200.00, 3800.00, NULL, NULL, 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(4, 'PRD-00004', '8901234567004', 'Alternator 90A - Honda Civic', 'Remanufactured alternator for Honda Civic R18', 4, 1, 'pcs', 12500.00, 18900.00, 17000.00, NULL, NULL, 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(5, 'PRD-00005', '8901234567005', 'Front Shock Absorber - Hilux', 'Gas-filled front shock for Toyota Hilux 2016+', 5, 6, 'pcs', 5800.00, 8500.00, 7800.00, NULL, NULL, 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(6, 'PRD-00006', '8901234567006', 'Air Filter - Suzuki Alto', 'Panel air filter for Suzuki Alto K10', 2, 6, 'pcs', 350.00, 600.00, 520.00, NULL, NULL, 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(7, 'PRD-00007', '8901234567007', 'Timing Belt Kit - Hyundai Elantra', 'Timing belt with tensioner and water pump', 3, 7, 'pcs', 8200.00, 12500.00, 11000.00, NULL, NULL, 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(8, 'PRD-00008', '8901234567008', 'Engine Oil 5W-30 (4L)', 'Semi-synthetic engine oil 4 litre pack', 7, 1, 'pcs', 3200.00, 4800.00, 4400.00, NULL, NULL, 1, NULL, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(10, 'TEST-PART-1997', NULL, 'High Performance Brake Rotor UPDATED', 'Updated slotted brake rotor description', 1, 1, 'pcs', 10000.00, 16500.00, 13500.00, NULL, NULL, 0, '2026-09-27 20:40:41', '2026-09-27 15:10:41', '2026-09-27 15:10:41'),
(11, 'TEST-PART-2389', NULL, 'High Performance Brake Rotor UPDATED', 'Updated slotted brake rotor description', 1, 1, 'pcs', 10000.00, 16500.00, 13500.00, NULL, NULL, 0, '2026-09-27 20:42:24', '2026-09-27 15:12:24', '2026-09-27 15:12:24'),
(12, 'TEST-PART-9984', NULL, 'High Performance Brake Rotor UPDATED', 'Updated slotted brake rotor description', 1, 1, 'pcs', 10000.00, 16500.00, 13500.00, NULL, NULL, 0, '2026-09-27 20:43:03', '2026-09-27 15:13:03', '2026-09-27 15:13:03'),
(13, 'TEST-PART-2630', NULL, 'High Performance Brake Rotor UPDATED', 'Updated slotted brake rotor description', 1, 1, 'pcs', 10000.00, 16500.00, 13500.00, NULL, NULL, 0, '2026-09-27 20:43:59', '2026-09-27 15:13:59', '2026-09-27 15:13:59'),
(14, 'TEST-PART-4926', NULL, 'High Performance Brake Rotor UPDATED', 'Updated slotted brake rotor description', 1, 1, 'pcs', 10000.00, 16500.00, 13500.00, NULL, NULL, 0, '2026-09-27 20:44:15', '2026-09-27 15:14:15', '2026-09-27 15:14:15'),
(15, 'TEST-PART-6518', NULL, 'High Performance Brake Rotor UPDATED', 'Updated slotted brake rotor description', 1, 1, 'pcs', 10000.00, 16500.00, 13500.00, NULL, NULL, 0, '2026-09-27 20:44:53', '2026-09-27 15:14:53', '2026-09-27 15:14:53'),
(16, 'INV-ITEM-3666', NULL, 'Synthetic Gear Oil 75W-90', NULL, 1, NULL, 'pcs', 2800.00, 4500.00, NULL, NULL, NULL, 1, NULL, '2026-09-27 15:14:56', '2026-09-27 15:14:56'),
(17, 'TEST-PART-7800', NULL, 'High Performance Brake Rotor UPDATED', 'Updated slotted brake rotor description', 1, 1, 'pcs', 10000.00, 16500.00, 13500.00, NULL, NULL, 0, '2026-09-27 20:45:41', '2026-09-27 15:15:40', '2026-09-27 15:15:41'),
(18, 'INV-ITEM-7777', NULL, 'Synthetic Gear Oil 75W-90', NULL, 1, NULL, 'pcs', 2800.00, 4500.00, NULL, NULL, NULL, 1, '2026-09-27 20:45:45', '2026-09-27 15:15:44', '2026-09-27 15:15:45'),
(19, 'TEST-PART-9963', NULL, 'High Performance Brake Rotor UPDATED', 'Updated slotted brake rotor description', 1, 1, 'pcs', 10000.00, 16500.00, 13500.00, NULL, NULL, 0, '2026-09-27 20:46:14', '2026-09-27 15:16:14', '2026-09-27 15:16:14');

-- Inventory stock levels (`inventory`)
INSERT INTO `inventory` (`id`, `product_id`, `quantity_on_hand`, `quantity_reserved`, `quantity_damaged`, `reorder_level`, `reorder_quantity`, `last_stock_in_at`, `last_stock_out_at`, `updated_at`) VALUES
(1, 1, 165, 8, 0, 15, 50, '2026-09-27 20:46:14', NULL, '2026-09-27 15:16:15'),
(2, 2, 320, 0, 0, 30, 100, NULL, NULL, '2026-09-22 19:05:32'),
(3, 3, 47, 0, 0, 20, 50, NULL, NULL, '2026-09-27 16:27:37'),
(4, 4, 10, 0, 0, 5, 10, NULL, NULL, '2026-09-27 16:27:37'),
(5, 5, 36, 0, 0, 10, 20, NULL, NULL, '2026-09-22 19:05:32'),
(6, 6, 198, 0, 0, 25, 80, NULL, NULL, '2026-09-27 16:27:37'),
(7, 7, 18, 0, 0, 8, 15, NULL, NULL, '2026-09-22 19:05:32'),
(8, 8, 94, 0, 0, 20, 50, NULL, NULL, '2026-09-27 16:27:37'),
(10, 10, 25, 0, 0, 5, 50, '2026-09-27 20:40:41', NULL, '2026-09-27 15:10:41'),
(11, 11, 25, 0, 0, 5, 50, '2026-09-27 20:42:24', NULL, '2026-09-27 15:12:24'),
(12, 12, 25, 0, 0, 5, 50, '2026-09-27 20:43:03', NULL, '2026-09-27 15:13:03'),
(13, 13, 25, 0, 0, 5, 50, '2026-09-27 20:43:59', NULL, '2026-09-27 15:13:59'),
(14, 14, 25, 0, 0, 5, 50, '2026-09-27 20:44:15', NULL, '2026-09-27 15:14:15'),
(15, 15, 25, 0, 0, 5, 50, '2026-09-27 20:44:53', NULL, '2026-09-27 15:14:53'),
(16, 16, 0, 0, 0, 10, 50, NULL, NULL, '2026-09-27 15:14:56'),
(17, 17, 25, 0, 0, 5, 50, '2026-09-27 20:45:40', NULL, '2026-09-27 15:15:40'),
(18, 18, 0, 0, 0, 10, 50, NULL, NULL, '2026-09-27 15:15:44'),
(19, 19, 25, 0, 0, 5, 50, '2026-09-27 20:46:14', NULL, '2026-09-27 15:16:14');

-- Vehicle-part fitment mappings (`product_compatibility`)
INSERT INTO `product_compatibility` (`id`, `product_id`, `vehicle_brand_id`, `vehicle_model_id`, `vehicle_engine_id`, `year_from`, `year_to`, `notes`, `created_at`) VALUES
(1, 1, 1, 1, 1, 2014, 2019, 'Direct fit for 1NZ-FE engine', '2026-09-22 19:05:32'),
(2, 1, 1, 1, 2, 2020, NULL, 'Direct fit for 2NR-FKE engine', '2026-09-22 19:05:32'),
(3, 3, 2, 4, 5, 2012, 2016, 'R18Z engine spark plugs', '2026-09-22 19:05:32'),
(4, 3, 2, 4, 6, 2017, NULL, 'L15B turbo engine spark plugs', '2026-09-22 19:05:32'),
(5, 4, 2, 4, 5, 2012, 2016, 'Honda Civic R18 alternator', '2026-09-22 19:05:32'),
(6, 5, 1, 2, 3, 2016, NULL, 'Hilux 2GD diesel front shock', '2026-09-22 19:05:32'),
(7, 6, 4, 9, 10, 2014, NULL, 'Suzuki Alto K10 air filter', '2026-09-22 19:05:32'),
(8, 7, 6, 11, 11, 2016, NULL, 'Hyundai Elantra timing kit', '2026-09-22 19:05:32');

-- Supplier SKU & pricing links (`supplier_products`)
INSERT INTO `supplier_products` (`id`, `supplier_id`, `product_id`, `supplier_sku`, `cost_price`, `lead_time_days`, `is_preferred`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'LA-BRK-COR-001', 3200.00, 7, 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(2, 1, 5, 'LA-SHK-HIL-001', 5800.00, 10, 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(3, 2, 2, 'JP-FLT-UNI-001', 450.00, 5, 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(4, 2, 3, 'JP-SPK-NGK-004', 2800.00, 7, 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(5, 2, 4, 'JP-ALT-CIV-90A', 12500.00, 14, 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(6, 3, 6, 'CM-FLT-ALT-001', 350.00, 3, 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(7, 3, 7, 'CM-TBK-ELN-001', 8200.00, 10, 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32'),
(8, 3, 8, 'CM-OIL-5W30-4L', 3200.00, 3, 1, '2026-09-22 19:05:32', '2026-09-22 19:05:32');

-- -----------------------------------------------------------------------------
-- Procurement & Stock Operations
-- -----------------------------------------------------------------------------

-- Purchase orders (`purchase_orders`)
INSERT INTO `purchase_orders` (`id`, `po_number`, `supplier_id`, `order_date`, `expected_date`, `status`, `subtotal`, `discount_amount`, `total_amount`, `amount_paid`, `notes`, `created_by`, `received_by`, `received_at`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'PO-00001', 1, '2026-09-27', '2026-10-04', 'pending', 43250.00, 0.00, 43250.00, 0.00, 'Automated test purchase order', 1, NULL, NULL, NULL, '2026-09-27 17:12:34', '2026-09-27 17:12:34');

-- Purchase order line items (`purchase_order_items`)
INSERT INTO `purchase_order_items` (`id`, `purchase_order_id`, `product_id`, `quantity_ordered`, `quantity_received`, `unit_cost`, `line_total`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 10, 0, 3200.00, 32000.00, '2026-09-27 17:12:34', '2026-09-27 17:12:34'),
(2, 1, 2, 25, 0, 450.00, 11250.00, '2026-09-27 17:12:34', '2026-09-27 17:12:34');

-- Inventory movement ledger (`stock_movements`)
INSERT INTO `stock_movements` (`id`, `product_id`, `movement_type`, `quantity`, `quantity_before`, `quantity_after`, `unit_cost`, `reference_type`, `reference_id`, `notes`, `created_by`, `created_at`) VALUES
(1, 1, 'adjustment_in', 10, 85, 95, 3200.00, 'manual_adjustment', NULL, 'Received regular supplier shipment batch', 4, '2026-09-27 15:10:41'),
(2, 1, 'adjustment_in', 10, 95, 105, 3200.00, 'manual_adjustment', NULL, 'Received regular supplier shipment batch', 4, '2026-09-27 15:12:24'),
(3, 1, 'adjustment_in', 10, 105, 115, 3200.00, 'manual_adjustment', NULL, 'Received regular supplier shipment batch', 4, '2026-09-27 15:13:03'),
(4, 1, 'adjustment_in', 10, 115, 125, 3200.00, 'manual_adjustment', NULL, 'Received regular supplier shipment batch', 4, '2026-09-27 15:13:59'),
(5, 1, 'adjustment_in', 10, 125, 135, 3200.00, 'manual_adjustment', NULL, 'Received regular supplier shipment batch', 4, '2026-09-27 15:14:15'),
(6, 1, 'adjustment_in', 10, 135, 145, 3200.00, 'manual_adjustment', NULL, 'Received regular supplier shipment batch', 4, '2026-09-27 15:14:53'),
(7, 1, 'adjustment_in', 10, 145, 155, 3200.00, 'manual_adjustment', NULL, 'Received regular supplier shipment batch', 4, '2026-09-27 15:15:41'),
(8, 18, 'adjustment_in', 0, 0, 0, NULL, 'adjustment', NULL, 'Physical stock count adjustment', 4, '2026-09-27 15:15:45'),
(9, 1, 'adjustment_in', 10, 155, 165, 3200.00, 'manual_adjustment', NULL, 'Received regular supplier shipment batch', 4, '2026-09-27 15:16:14');

-- -----------------------------------------------------------------------------
-- Sales, Orders & Fulfillment
-- -----------------------------------------------------------------------------

-- Customer & B2B portal orders (`orders`)
INSERT INTO `orders` (`id`, `order_number`, `customer_id`, `sales_rep_id`, `order_date`, `status`, `order_source`, `subtotal`, `discount_amount`, `total_amount`, `payment_status`, `delivery_address`, `notes`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'ORD-00001', 6, NULL, '2026-09-24 19:46:55', 'pending', 'shop_portal', 1500.00, 0.00, 1850.00, 'unpaid', 'zxzxczxcxz', 'Payment Method: Cash on Delivery', NULL, '2026-09-24 14:16:55', '2026-09-24 14:16:55'),
(2, 'ORD-00002', 11, NULL, '2026-09-27 02:03:45', 'pending', 'shop_portal', 4500.00, 0.00, 4850.00, 'unpaid', '123 Kandy Road, Colombo 03', 'Payment Method: Cash on Delivery', NULL, '2026-09-26 20:33:45', '2026-09-26 20:33:45'),
(5, 'ORD-00005', 16, NULL, '2026-09-27 14:42:29', 'pending', 'shop_portal', 4500.00, 0.00, 4850.00, 'unpaid', '123 Sample St', 'Payment Method: Cash on Delivery', NULL, '2026-09-27 09:12:29', '2026-09-27 09:12:29'),
(12, 'ORD-00012', 1, NULL, '2026-09-27 20:43:04', 'pending', 'shop_portal', 9000.00, 0.00, 9350.00, 'unpaid', '78 Temple Road, Colombo 03', 'Payment Method: Cash on Delivery', NULL, '2026-09-27 15:13:04', '2026-09-27 15:13:04'),
(13, 'ORD-00013', 1, NULL, '2026-09-27 20:44:00', 'cancelled', 'shop_portal', 9000.00, 0.00, 9350.00, 'unpaid', '99 Marine Drive, Colombo 03 (UPDATED)', 'Payment Method: Cash on Delivery', NULL, '2026-09-27 15:14:00', '2026-09-27 15:14:00'),
(14, 'ORD-00014', 1, NULL, '2026-09-27 20:44:17', 'cancelled', 'shop_portal', 9000.00, 0.00, 9350.00, 'unpaid', '99 Marine Drive, Colombo 03 (UPDATED)', 'Payment Method: Cash on Delivery', NULL, '2026-09-27 15:14:17', '2026-09-27 15:14:17'),
(15, 'ORD-00015', 1, NULL, '2026-09-27 20:44:54', 'cancelled', 'shop_portal', 9000.00, 0.00, 9350.00, 'unpaid', '99 Marine Drive, Colombo 03 (UPDATED)', 'Payment Method: Cash on Delivery', NULL, '2026-09-27 15:14:54', '2026-09-27 15:14:55'),
(16, 'ORD-00016', 1, 2, '2026-09-27 20:44:55', 'pending', 'rep_field', 4500.00, 0.00, 4500.00, 'unpaid', '78 Galle Road, Colombo', 'Urgent workshop repair parts', NULL, '2026-09-27 15:14:55', '2026-09-27 15:14:55'),
(17, 'ORD-00017', 1, NULL, '2026-09-27 20:45:43', 'cancelled', 'shop_portal', 9000.00, 0.00, 9350.00, 'unpaid', '99 Marine Drive, Colombo 03 (UPDATED)', 'Payment Method: Cash on Delivery', NULL, '2026-09-27 15:15:43', '2026-09-27 15:15:43'),
(18, 'ORD-00018', 1, 2, '2026-09-27 20:45:43', 'cancelled', 'rep_field', 4500.00, 0.00, 4500.00, 'unpaid', '100 Galle Road, Colombo (UPDATED)', 'Urgent workshop repair parts', NULL, '2026-09-27 15:15:43', '2026-09-27 15:15:43'),
(19, 'ORD-00019', 1, NULL, '2026-09-27 20:46:15', 'cancelled', 'shop_portal', 9000.00, 0.00, 9350.00, 'unpaid', '99 Marine Drive, Colombo 03 (UPDATED)', 'Payment Method: Cash on Delivery', NULL, '2026-09-27 15:16:15', '2026-09-27 15:16:15');

-- Order line items (`order_items`)
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `unit_price`, `discount_amount`, `line_total`, `created_at`) VALUES
(1, 1, 2, 2, 750.00, 0.00, 1500.00, '2026-09-24 14:16:55'),
(5, 5, 1, 1, 4500.00, 0.00, 4500.00, '2026-09-27 09:12:29'),
(12, 12, 1, 2, 4500.00, 0.00, 9000.00, '2026-09-27 15:13:04'),
(13, 13, 1, 2, 4500.00, 0.00, 9000.00, '2026-09-27 15:14:00'),
(14, 14, 1, 2, 4500.00, 0.00, 9000.00, '2026-09-27 15:14:17'),
(15, 15, 1, 2, 4500.00, 0.00, 9000.00, '2026-09-27 15:14:54'),
(16, 16, 1, 1, 4500.00, 0.00, 4500.00, '2026-09-27 15:14:55'),
(17, 17, 1, 2, 4500.00, 0.00, 9000.00, '2026-09-27 15:15:43'),
(18, 18, 1, 1, 4500.00, 0.00, 4500.00, '2026-09-27 15:15:43'),
(19, 19, 1, 2, 4500.00, 0.00, 9000.00, '2026-09-27 15:16:15');

-- Point of sale & wholesale invoices (`sales`)
INSERT INTO `sales` (`id`, `invoice_number`, `order_id`, `customer_id`, `sales_rep_id`, `sale_date`, `sale_type`, `payment_method`, `subtotal`, `discount_amount`, `total_amount`, `amount_paid`, `change_amount`, `payment_status`, `notes`, `deleted_at`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'INV-001001', NULL, 11, 1, '2025-01-13 08:17:31', 'pos', 'cash', 35760.00, 0.00, 35760.00, 35760.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-01-13 08:17:31', '2026-09-28 01:03:42'),
(2, 'INV-001002', NULL, 6, 3, '2025-01-27 18:18:22', 'pos', 'card', 1950.00, 0.00, 1950.00, 1950.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-01-27 18:18:22', '2026-09-28 01:03:42'),
(3, 'INV-001003', NULL, 11, 2, '2025-01-14 14:26:29', 'invoice', 'cash', 71820.00, 0.00, 71820.00, 71820.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-01-14 14:26:29', '2026-09-28 01:03:42'),
(4, 'INV-001004', NULL, 1, 3, '2025-01-03 08:28:57', 'credit', 'bank_transfer', 13590.00, 271.80, 13318.20, 13318.20, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-01-03 08:28:57', '2026-09-28 01:03:42'),
(5, 'INV-001005', NULL, 11, 2, '2025-02-16 16:24:50', 'invoice', 'cash', 47400.00, 0.00, 47400.00, 47400.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-02-16 16:24:50', '2026-09-28 01:03:42'),
(6, 'INV-001006', NULL, 3, 3, '2025-02-04 12:27:35', 'credit', 'card', 85200.00, 0.00, 85200.00, 85200.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-02-04 12:27:35', '2026-09-28 01:03:42'),
(7, 'INV-001007', NULL, 1, 1, '2025-02-21 14:59:29', 'invoice', 'credit', 141900.00, 0.00, 141900.00, 0.00, 0.00, 'unpaid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-02-21 14:59:29', '2026-09-28 01:03:42'),
(8, 'INV-001008', NULL, 11, 3, '2025-02-03 09:57:29', 'pos', 'credit', 64637.50, 0.00, 64637.50, 0.00, 0.00, 'unpaid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-02-03 09:57:29', '2026-09-28 01:03:42'),
(9, 'INV-001009', NULL, 6, 1, '2025-03-05 13:56:19', 'invoice', 'credit', 56700.00, 0.00, 56700.00, 22680.00, 0.00, 'partial', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-03-05 13:56:19', '2026-09-28 01:03:42'),
(10, 'INV-001010', NULL, 10, 1, '2025-03-21 11:56:12', 'credit', 'bank_transfer', 75900.00, 1518.00, 74382.00, 74382.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-03-21 11:56:12', '2026-09-28 01:03:42'),
(11, 'INV-001011', NULL, 9, 1, '2025-03-23 18:35:16', 'pos', 'cash', 36400.00, 0.00, 36400.00, 36400.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-03-23 18:35:16', '2026-09-28 01:03:42'),
(12, 'INV-001012', NULL, 4, 1, '2025-03-09 18:52:49', 'invoice', 'bank_transfer', 25500.00, 0.00, 25500.00, 25500.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-03-09 18:52:49', '2026-09-28 01:03:42'),
(13, 'INV-001013', NULL, 9, 2, '2025-04-27 17:03:06', 'credit', 'cash', 10475.00, 0.00, 10475.00, 10475.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-04-27 17:03:06', '2026-09-28 01:03:42'),
(14, 'INV-001014', NULL, 6, 2, '2025-04-02 17:51:37', 'pos', 'cash', 75300.00, 0.00, 75300.00, 75300.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-04-02 17:51:37', '2026-09-28 01:03:42'),
(15, 'INV-001015', NULL, 11, 1, '2025-04-28 11:10:31', 'invoice', 'credit', 42300.00, 0.00, 42300.00, 16920.00, 0.00, 'partial', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-04-28 11:10:31', '2026-09-28 01:03:42'),
(16, 'INV-001016', NULL, 6, 2, '2025-04-26 09:20:08', 'credit', 'bank_transfer', 37800.00, 0.00, 37800.00, 37800.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-04-26 09:20:08', '2026-09-28 01:03:42'),
(17, 'INV-001017', NULL, 3, 3, '2025-05-26 15:03:09', 'credit', 'card', 66400.00, 1328.00, 65072.00, 65072.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-05-26 15:03:09', '2026-09-28 01:03:42'),
(18, 'INV-001018', NULL, 1, 2, '2025-05-21 10:35:59', 'credit', 'cash', 51600.00, 0.00, 51600.00, 51600.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-05-21 10:35:59', '2026-09-28 01:03:42'),
(19, 'INV-001019', NULL, 6, 1, '2025-05-03 09:41:21', 'invoice', 'cash', 32100.00, 0.00, 32100.00, 32100.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-05-03 09:41:21', '2026-09-28 01:03:42'),
(20, 'INV-001020', NULL, 9, 1, '2025-05-22 16:19:48', 'pos', 'cash', 23400.00, 0.00, 23400.00, 23400.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-05-22 16:19:48', '2026-09-28 01:03:42'),
(21, 'INV-001021', NULL, 11, 2, '2025-05-04 11:54:09', 'invoice', 'card', 47500.00, 0.00, 47500.00, 47500.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-05-04 11:54:09', '2026-09-28 01:03:42'),
(22, 'INV-001022', NULL, 10, 1, '2025-05-23 14:53:35', 'pos', 'cash', 76100.00, 0.00, 76100.00, 76100.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-05-23 14:53:35', '2026-09-28 01:03:42'),
(23, 'INV-001023', NULL, 9, 1, '2025-06-07 11:56:26', 'credit', 'card', 36200.00, 0.00, 36200.00, 36200.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-06-07 11:56:26', '2026-09-28 01:03:42'),
(24, 'INV-001024', NULL, 2, 1, '2025-06-24 12:04:39', 'pos', 'credit', 61225.00, 0.00, 61225.00, 0.00, 0.00, 'unpaid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-06-24 12:04:39', '2026-09-28 01:03:42'),
(25, 'INV-001025', NULL, 11, 3, '2025-06-28 13:43:06', 'credit', 'cash', 102500.00, 0.00, 102500.00, 102500.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-06-28 13:43:06', '2026-09-28 01:03:42'),
(26, 'INV-001026', NULL, 3, 3, '2025-06-26 13:29:05', 'pos', 'credit', 44400.00, 0.00, 44400.00, 0.00, 0.00, 'unpaid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-06-26 13:29:05', '2026-09-28 01:03:42'),
(27, 'INV-001027', NULL, 10, 2, '2025-06-18 11:59:47', 'credit', 'bank_transfer', 4500.00, 0.00, 4500.00, 4500.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-06-18 11:59:47', '2026-09-28 01:03:42'),
(28, 'INV-001028', NULL, 2, 3, '2025-06-14 16:02:44', 'pos', 'credit', 63480.00, 0.00, 63480.00, 0.00, 0.00, 'unpaid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-06-14 16:02:44', '2026-09-28 01:03:42'),
(29, 'INV-001029', NULL, 10, 3, '2025-07-05 18:31:58', 'invoice', 'credit', 2250.00, 0.00, 2250.00, 0.00, 0.00, 'unpaid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-07-05 18:31:58', '2026-09-28 01:03:42'),
(30, 'INV-001030', NULL, 9, 3, '2025-07-05 17:12:09', 'credit', 'cash', 65920.00, 0.00, 65920.00, 65920.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-07-05 17:12:09', '2026-09-28 01:03:42'),
(31, 'INV-001031', NULL, 6, 2, '2025-07-23 13:49:56', 'invoice', 'credit', 50950.00, 0.00, 50950.00, 20380.00, 0.00, 'partial', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-07-23 13:49:56', '2026-09-28 01:03:42'),
(32, 'INV-001032', NULL, 4, 1, '2025-07-16 18:19:28', 'pos', 'bank_transfer', 33000.00, 0.00, 33000.00, 33000.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-07-16 18:19:28', '2026-09-28 01:03:42'),
(33, 'INV-001033', NULL, 1, 3, '2025-07-13 08:18:37', 'credit', 'bank_transfer', 17000.00, 0.00, 17000.00, 17000.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-07-13 08:18:37', '2026-09-28 01:03:42'),
(34, 'INV-001034', NULL, 6, 2, '2025-07-23 09:57:56', 'pos', 'card', 19800.00, 0.00, 19800.00, 19800.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-07-23 09:57:56', '2026-09-28 01:03:42'),
(35, 'INV-001035', NULL, 1, 2, '2025-08-11 10:32:52', 'pos', 'credit', 37500.00, 750.00, 36750.00, 0.00, 0.00, 'unpaid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-08-11 10:32:52', '2026-09-28 01:03:42'),
(36, 'INV-001036', NULL, 9, 1, '2025-08-10 18:34:39', 'invoice', 'cash', 19200.00, 0.00, 19200.00, 19200.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-08-10 18:34:39', '2026-09-28 01:03:42'),
(37, 'INV-001037', NULL, 6, 3, '2025-08-07 15:00:18', 'invoice', 'cash', 44600.00, 0.00, 44600.00, 44600.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-08-07 15:00:18', '2026-09-28 01:03:42'),
(38, 'INV-001038', NULL, 9, 1, '2025-08-06 17:58:17', 'invoice', 'bank_transfer', 34625.00, 0.00, 34625.00, 34625.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-08-06 17:58:17', '2026-09-28 01:03:42'),
(39, 'INV-001039', NULL, 1, 1, '2025-08-27 11:28:56', 'credit', 'card', 5400.00, 0.00, 5400.00, 5400.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-08-27 11:28:56', '2026-09-28 01:03:42'),
(40, 'INV-001040', NULL, 1, 2, '2025-08-12 14:08:51', 'invoice', 'card', 96200.00, 0.00, 96200.00, 96200.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-08-12 14:08:51', '2026-09-28 01:03:42'),
(41, 'INV-001041', NULL, 6, 3, '2025-09-03 15:18:39', 'credit', 'credit', 32700.00, 0.00, 32700.00, 13080.00, 0.00, 'partial', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-09-03 15:18:39', '2026-09-28 01:03:42'),
(42, 'INV-001042', NULL, 10, 2, '2025-09-14 12:44:33', 'credit', 'card', 18000.00, 0.00, 18000.00, 18000.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-09-14 12:44:33', '2026-09-28 01:03:42'),
(43, 'INV-001043', NULL, 9, 1, '2025-09-19 18:49:13', 'pos', 'card', 9600.00, 0.00, 9600.00, 9600.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-09-19 18:49:13', '2026-09-28 01:03:42'),
(44, 'INV-001044', NULL, 4, 3, '2025-09-07 12:05:51', 'credit', 'bank_transfer', 38800.00, 0.00, 38800.00, 38800.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-09-07 12:05:51', '2026-09-28 01:03:42'),
(45, 'INV-001045', NULL, 1, 3, '2025-09-24 16:18:30', 'pos', 'bank_transfer', 19000.00, 0.00, 19000.00, 19000.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-09-24 16:18:30', '2026-09-28 01:03:42'),
(46, 'INV-001046', NULL, 4, 2, '2025-09-06 13:39:33', 'invoice', 'cash', 18900.00, 0.00, 18900.00, 18900.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-09-06 13:39:33', '2026-09-28 01:03:42'),
(47, 'INV-001047', NULL, 6, 1, '2025-10-03 12:50:25', 'credit', 'cash', 3000.00, 0.00, 3000.00, 3000.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-10-03 12:50:25', '2026-09-28 01:03:42'),
(48, 'INV-001048', NULL, 2, 1, '2025-10-10 08:53:08', 'credit', 'card', 8400.00, 0.00, 8400.00, 8400.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-10-10 08:53:08', '2026-09-28 01:03:42'),
(49, 'INV-001049', NULL, 6, 3, '2025-10-04 11:10:44', 'credit', 'bank_transfer', 34080.00, 0.00, 34080.00, 34080.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-10-04 11:10:44', '2026-09-28 01:03:42'),
(50, 'INV-001050', NULL, 10, 3, '2025-10-24 14:05:54', 'invoice', 'card', 1500.00, 0.00, 1500.00, 1500.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-10-24 14:05:54', '2026-09-28 01:03:42'),
(51, 'INV-001051', NULL, 10, 1, '2025-10-22 13:19:15', 'invoice', 'bank_transfer', 21000.00, 0.00, 21000.00, 21000.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-10-22 13:19:15', '2026-09-28 01:03:42'),
(52, 'INV-001052', NULL, 11, 3, '2025-11-04 13:09:22', 'credit', 'credit', 36250.00, 0.00, 36250.00, 0.00, 0.00, 'unpaid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-11-04 13:09:22', '2026-09-28 01:03:42'),
(53, 'INV-001053', NULL, 10, 3, '2025-11-02 08:24:17', 'invoice', 'cash', 14400.00, 0.00, 14400.00, 14400.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-11-02 08:24:17', '2026-09-28 01:03:42'),
(54, 'INV-001054', NULL, 1, 1, '2025-11-08 12:08:51', 'invoice', 'bank_transfer', 26250.00, 0.00, 26250.00, 26250.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-11-08 12:08:51', '2026-09-28 01:03:42'),
(55, 'INV-001055', NULL, 6, 2, '2025-11-11 14:45:12', 'invoice', 'credit', 4500.00, 0.00, 4500.00, 1800.00, 0.00, 'partial', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-11-11 14:45:12', '2026-09-28 01:03:42'),
(56, 'INV-001056', NULL, 11, 3, '2025-11-05 16:44:49', 'invoice', 'bank_transfer', 50000.00, 0.00, 50000.00, 50000.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-11-05 16:44:49', '2026-09-28 01:03:42'),
(57, 'INV-001057', NULL, 10, 1, '2025-12-15 13:26:27', 'credit', 'card', 16900.00, 0.00, 16900.00, 16900.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-12-15 13:26:27', '2026-09-28 01:03:42'),
(58, 'INV-001058', NULL, 9, 1, '2025-12-12 14:51:38', 'invoice', 'cash', 13500.00, 0.00, 13500.00, 13500.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-12-12 14:51:38', '2026-09-28 01:03:42'),
(59, 'INV-001059', NULL, 9, 3, '2025-12-22 12:23:59', 'credit', 'cash', 13800.00, 0.00, 13800.00, 13800.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-12-22 12:23:59', '2026-09-28 01:03:42'),
(60, 'INV-001060', NULL, 10, 1, '2025-12-28 18:55:29', 'invoice', 'cash', 3600.00, 0.00, 3600.00, 3600.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-12-28 18:55:29', '2026-09-28 01:03:42'),
(61, 'INV-001061', NULL, 4, 3, '2025-12-05 17:21:36', 'invoice', 'cash', 62400.00, 0.00, 62400.00, 62400.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2025-12-05 17:21:36', '2026-09-28 01:03:42'),
(62, 'INV-001062', NULL, 9, 3, '2026-01-26 15:59:31', 'pos', 'card', 86500.00, 1730.00, 84770.00, 84770.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-01-26 15:59:31', '2026-09-28 01:03:42'),
(63, 'INV-001063', NULL, 9, 2, '2026-01-06 13:43:06', 'credit', 'cash', 70000.00, 0.00, 70000.00, 70000.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-01-06 13:43:06', '2026-09-28 01:03:42'),
(64, 'INV-001064', NULL, 9, 1, '2026-01-20 11:11:06', 'credit', 'credit', 39975.00, 0.00, 39975.00, 0.00, 0.00, 'unpaid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-01-20 11:11:06', '2026-09-28 01:03:42'),
(65, 'INV-001065', NULL, 3, 3, '2026-01-15 08:57:05', 'invoice', 'cash', 31200.00, 0.00, 31200.00, 31200.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-01-15 08:57:05', '2026-09-28 01:03:42'),
(66, 'INV-001066', NULL, 2, 2, '2026-01-10 17:39:19', 'credit', 'bank_transfer', 32250.00, 645.00, 31605.00, 31605.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-01-10 17:39:19', '2026-09-28 01:03:42'),
(67, 'INV-001067', NULL, 6, 1, '2026-02-28 11:29:35', 'credit', 'cash', 19200.00, 0.00, 19200.00, 19200.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-02-28 11:29:35', '2026-09-28 01:03:42'),
(68, 'INV-001068', NULL, 1, 3, '2026-02-04 08:48:41', 'credit', 'bank_transfer', 41355.00, 0.00, 41355.00, 41355.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-02-04 08:48:41', '2026-09-28 01:03:42'),
(69, 'INV-001069', NULL, 10, 1, '2026-02-08 11:41:11', 'credit', 'credit', 4800.00, 0.00, 4800.00, 0.00, 0.00, 'unpaid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-02-08 11:41:11', '2026-09-28 01:03:42'),
(70, 'INV-001070', NULL, 10, 1, '2026-02-22 11:49:23', 'credit', 'card', 82125.00, 0.00, 82125.00, 82125.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-02-22 11:49:23', '2026-09-28 01:03:42'),
(71, 'INV-001071', NULL, 11, 1, '2026-02-19 16:54:30', 'credit', 'bank_transfer', 105700.00, 2114.00, 103586.00, 103586.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-02-19 16:54:30', '2026-09-28 01:03:42'),
(72, 'INV-001072', NULL, 1, 2, '2026-03-14 12:58:44', 'credit', 'cash', 15000.00, 0.00, 15000.00, 15000.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-03-14 12:58:44', '2026-09-28 01:03:42'),
(73, 'INV-001073', NULL, 10, 2, '2026-03-14 12:10:04', 'credit', 'credit', 141900.00, 0.00, 141900.00, 0.00, 0.00, 'unpaid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-03-14 12:10:04', '2026-09-28 01:03:42'),
(74, 'INV-001074', NULL, 3, 1, '2026-03-16 08:07:22', 'credit', 'card', 9600.00, 0.00, 9600.00, 9600.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-03-16 08:07:22', '2026-09-28 01:03:42'),
(75, 'INV-001075', NULL, 9, 3, '2026-03-04 12:36:41', 'pos', 'cash', 25200.00, 0.00, 25200.00, 25200.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-03-04 12:36:41', '2026-09-28 01:03:42'),
(76, 'INV-001076', NULL, 9, 3, '2026-03-25 09:31:04', 'pos', 'bank_transfer', 103520.00, 0.00, 103520.00, 103520.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-03-25 09:31:04', '2026-09-28 01:03:42'),
(77, 'INV-001077', NULL, 4, 3, '2026-04-03 15:39:02', 'credit', 'credit', 47040.00, 0.00, 47040.00, 0.00, 0.00, 'unpaid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-04-03 15:39:02', '2026-09-28 01:03:42'),
(78, 'INV-001078', NULL, 11, 2, '2026-04-15 15:28:48', 'invoice', 'bank_transfer', 18210.00, 0.00, 18210.00, 18210.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-04-15 15:28:48', '2026-09-28 01:03:42'),
(79, 'INV-001079', NULL, 6, 1, '2026-04-24 17:52:34', 'invoice', 'cash', 158755.00, 0.00, 158755.00, 158755.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-04-24 17:52:34', '2026-09-28 01:03:42'),
(80, 'INV-001080', NULL, 11, 2, '2026-04-10 18:27:44', 'pos', 'credit', 6510.00, 0.00, 6510.00, 0.00, 0.00, 'unpaid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-04-10 18:27:44', '2026-09-28 01:03:42'),
(81, 'INV-001081', NULL, 1, 2, '2026-04-23 13:35:43', 'credit', 'card', 20700.00, 0.00, 20700.00, 20700.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-04-23 13:35:43', '2026-09-28 01:03:42'),
(82, 'INV-001082', NULL, 10, 1, '2026-05-05 10:58:10', 'invoice', 'card', 23400.00, 0.00, 23400.00, 23400.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-05-05 10:58:10', '2026-09-28 01:03:42'),
(83, 'INV-001083', NULL, 11, 1, '2026-05-27 11:58:37', 'invoice', 'credit', 27600.00, 0.00, 27600.00, 0.00, 0.00, 'unpaid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-05-27 11:58:37', '2026-09-28 01:03:42'),
(84, 'INV-001084', NULL, 4, 3, '2026-05-06 12:17:32', 'pos', 'card', 14400.00, 0.00, 14400.00, 14400.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-05-06 12:17:32', '2026-09-28 01:03:42'),
(85, 'INV-001085', NULL, 9, 3, '2026-05-26 14:29:22', 'pos', 'credit', 34000.00, 680.00, 33320.00, 0.00, 0.00, 'unpaid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-05-26 14:29:22', '2026-09-28 01:03:42'),
(86, 'INV-001086', NULL, 1, 3, '2026-05-14 08:15:51', 'pos', 'card', 37500.00, 0.00, 37500.00, 37500.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-05-14 08:15:51', '2026-09-28 01:03:42'),
(87, 'INV-001087', NULL, 2, 1, '2026-05-14 09:14:43', 'credit', 'credit', 43175.00, 0.00, 43175.00, 0.00, 0.00, 'unpaid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-05-14 09:14:43', '2026-09-28 01:03:42'),
(88, 'INV-001088', NULL, 3, 3, '2026-05-05 15:37:46', 'credit', 'credit', 44500.00, 0.00, 44500.00, 0.00, 0.00, 'unpaid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-05-05 15:37:46', '2026-09-28 01:03:42'),
(89, 'INV-001089', NULL, 10, 3, '2026-05-03 11:56:45', 'invoice', 'bank_transfer', 41625.00, 0.00, 41625.00, 41625.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-05-03 11:56:45', '2026-09-28 01:03:42'),
(90, 'INV-001090', NULL, 4, 1, '2026-06-18 11:05:41', 'pos', 'cash', 750.00, 0.00, 750.00, 750.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-06-18 11:05:41', '2026-09-28 01:03:42'),
(91, 'INV-001091', NULL, 4, 1, '2026-06-14 18:22:01', 'pos', 'card', 64800.00, 0.00, 64800.00, 64800.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-06-14 18:22:01', '2026-09-28 01:03:42'),
(92, 'INV-001092', NULL, 6, 2, '2026-06-09 09:43:50', 'pos', 'card', 9600.00, 0.00, 9600.00, 9600.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-06-09 09:43:50', '2026-09-28 01:03:42'),
(93, 'INV-001093', NULL, 9, 1, '2026-06-28 18:29:55', 'credit', 'cash', 122360.00, 0.00, 122360.00, 122360.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-06-28 18:29:55', '2026-09-28 01:03:42'),
(94, 'INV-001094', NULL, 6, 3, '2026-06-26 15:58:15', 'credit', 'bank_transfer', 9600.00, 0.00, 9600.00, 9600.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-06-26 15:58:15', '2026-09-28 01:03:42'),
(95, 'INV-001095', NULL, 2, 1, '2026-06-05 18:45:13', 'invoice', 'credit', 33975.00, 0.00, 33975.00, 0.00, 0.00, 'unpaid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-06-05 18:45:13', '2026-09-28 01:03:42'),
(96, 'INV-001096', NULL, 11, 2, '2026-06-10 09:18:14', 'pos', 'bank_transfer', 43500.00, 0.00, 43500.00, 43500.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-06-10 09:18:14', '2026-09-28 01:03:42'),
(97, 'INV-001097', NULL, 9, 3, '2026-06-17 11:53:27', 'invoice', 'bank_transfer', 41640.00, 832.80, 40807.20, 40807.20, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-06-17 11:53:27', '2026-09-28 01:03:42'),
(98, 'INV-001098', NULL, 3, 3, '2026-07-05 08:13:58', 'invoice', 'card', 27000.00, 0.00, 27000.00, 27000.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-07-05 08:13:58', '2026-09-28 01:03:42'),
(99, 'INV-001099', NULL, 10, 3, '2026-07-07 11:00:56', 'invoice', 'credit', 39420.00, 0.00, 39420.00, 0.00, 0.00, 'unpaid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-07-07 11:00:56', '2026-09-28 01:03:42'),
(100, 'INV-001100', NULL, 9, 2, '2026-07-18 11:43:09', 'credit', 'card', 9000.00, 0.00, 9000.00, 9000.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-07-18 11:43:09', '2026-09-28 01:03:42'),
(101, 'INV-001101', NULL, 1, 2, '2026-07-21 12:23:54', 'invoice', 'card', 22600.00, 0.00, 22600.00, 22600.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-07-21 12:23:54', '2026-09-28 01:03:42'),
(102, 'INV-001102', NULL, 3, 3, '2026-07-07 12:10:50', 'invoice', 'bank_transfer', 9300.00, 0.00, 9300.00, 9300.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-07-07 12:10:50', '2026-09-28 01:03:42'),
(103, 'INV-001103', NULL, 9, 3, '2026-07-20 09:09:05', 'pos', 'card', 22000.00, 0.00, 22000.00, 22000.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-07-20 09:09:05', '2026-09-28 01:03:42'),
(104, 'INV-001104', NULL, 3, 3, '2026-07-19 18:37:38', 'credit', 'cash', 43500.00, 0.00, 43500.00, 43500.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-07-19 18:37:38', '2026-09-28 01:03:42'),
(105, 'INV-001105', NULL, 2, 2, '2026-07-24 16:36:19', 'credit', 'card', 45400.00, 0.00, 45400.00, 45400.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-07-24 16:36:19', '2026-09-28 01:03:42'),
(106, 'INV-001106', NULL, 11, 1, '2026-08-05 16:11:43', 'invoice', 'card', 44000.00, 0.00, 44000.00, 44000.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-08-05 16:11:43', '2026-09-28 01:03:42'),
(107, 'INV-001107', NULL, 9, 3, '2026-08-20 17:52:53', 'credit', 'credit', 112700.00, 0.00, 112700.00, 0.00, 0.00, 'unpaid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-08-20 17:52:53', '2026-09-28 01:03:42'),
(108, 'INV-001108', NULL, 9, 1, '2026-08-18 13:21:51', 'pos', 'cash', 600.00, 0.00, 600.00, 600.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-08-18 13:21:51', '2026-09-28 01:03:42'),
(109, 'INV-001109', NULL, 11, 3, '2026-08-28 10:45:54', 'credit', 'cash', 18000.00, 0.00, 18000.00, 18000.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-08-28 10:45:54', '2026-09-28 01:03:42'),
(110, 'INV-001110', NULL, 6, 1, '2026-08-28 16:38:36', 'pos', 'card', 47500.00, 950.00, 46550.00, 46550.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-08-28 16:38:36', '2026-09-28 01:03:42'),
(111, 'INV-001111', NULL, 6, 3, '2026-08-18 12:15:37', 'credit', 'card', 48400.00, 0.00, 48400.00, 48400.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-08-18 12:15:37', '2026-09-28 01:03:42'),
(112, 'INV-001112', NULL, 6, 1, '2026-08-03 08:18:04', 'pos', 'bank_transfer', 39000.00, 0.00, 39000.00, 39000.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-08-03 08:18:04', '2026-09-28 01:03:42'),
(113, 'INV-001113', NULL, 10, 1, '2026-08-03 11:26:20', 'credit', 'credit', 14200.00, 284.00, 13916.00, 0.00, 0.00, 'unpaid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-08-03 11:26:20', '2026-09-28 01:03:42'),
(114, 'INV-001114', NULL, 2, 2, '2026-09-04 09:47:58', 'credit', 'cash', 18000.00, 360.00, 17640.00, 17640.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-09-04 09:47:58', '2026-09-28 01:03:42'),
(115, 'INV-001115', NULL, 9, 2, '2026-09-07 18:57:02', 'invoice', 'bank_transfer', 50300.00, 1006.00, 49294.00, 49294.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-09-07 18:57:02', '2026-09-28 01:03:42'),
(116, 'INV-001116', NULL, 1, 1, '2026-09-15 15:22:03', 'pos', 'credit', 34600.00, 0.00, 34600.00, 0.00, 0.00, 'unpaid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-09-15 15:22:03', '2026-09-28 01:03:42'),
(117, 'INV-001117', NULL, 9, 3, '2026-09-14 12:53:58', 'invoice', 'card', 14400.00, 0.00, 14400.00, 14400.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-09-14 12:53:58', '2026-09-28 01:03:42'),
(118, 'INV-001118', NULL, 11, 1, '2026-09-27 16:33:04', 'invoice', 'card', 74000.00, 0.00, 74000.00, 74000.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-09-27 16:33:04', '2026-09-28 01:03:42'),
(119, 'INV-001119', NULL, 10, 2, '2026-09-06 15:14:07', 'pos', 'cash', 35500.00, 0.00, 35500.00, 35500.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-09-06 15:14:07', '2026-09-28 01:03:42'),
(120, 'INV-001120', NULL, 2, 1, '2026-09-23 18:42:15', 'credit', 'card', 9600.00, 0.00, 9600.00, 9600.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-09-23 18:42:15', '2026-09-28 01:03:42'),
(121, 'INV-001121', NULL, 4, 1, '2026-09-13 14:44:11', 'credit', 'card', 4200.00, 0.00, 4200.00, 4200.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-09-13 14:44:11', '2026-09-28 01:03:42'),
(122, 'INV-001122', NULL, 2, 3, '2026-09-27 13:18:30', 'invoice', 'bank_transfer', 18900.00, 0.00, 18900.00, 18900.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-09-27 13:18:30', '2026-09-28 01:03:42'),
(123, 'INV-001123', NULL, 10, 3, '2026-09-27 17:02:54', 'pos', 'cash', 52300.00, 0.00, 52300.00, 52300.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-09-27 17:02:54', '2026-09-28 01:03:42'),
(124, 'INV-001124', NULL, 9, 1, '2026-09-26 09:19:12', 'pos', 'bank_transfer', 25000.00, 500.00, 24500.00, 24500.00, 0.00, 'paid', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-09-26 09:19:12', '2026-09-28 01:03:42'),
(125, 'INV-001125', NULL, 6, 3, '2026-09-25 13:17:09', 'pos', 'credit', 4200.00, 0.00, 4200.00, 1680.00, 0.00, 'partial', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-09-25 13:17:09', '2026-09-28 01:03:42'),
(126, 'INV-001126', NULL, 6, 2, '2026-09-24 11:58:48', 'invoice', 'credit', 7200.00, 0.00, 7200.00, 2880.00, 0.00, 'partial', 'Seasonal retail/wholesale invoice', NULL, 1, '2026-09-24 11:58:48', '2026-09-28 01:03:42'),
(128, 'INV-001128', NULL, 40, 2, '2026-09-27 21:57:37', 'credit', 'credit', 48000.00, 0.00, 48000.00, 48000.00, 0.00, 'paid', NULL, NULL, 3, '2026-09-27 21:57:37', '2026-09-27 21:57:37');

-- Sales invoice line items (`sale_items`)
INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `unit_price`, `cost_price`, `discount_amount`, `line_total`, `created_at`) VALUES
(1, 1, 6, 4, 600.00, 350.00, 0.00, 2400.00, '2025-01-13 02:47:31'),
(2, 1, 3, 4, 4200.00, 2800.00, 840.00, 15960.00, '2025-01-13 02:47:31'),
(3, 1, 6, 1, 600.00, 350.00, 0.00, 600.00, '2025-01-13 02:47:31'),
(4, 1, 3, 4, 4200.00, 2800.00, 0.00, 16800.00, '2025-01-13 02:47:31'),
(5, 2, 2, 1, 750.00, 450.00, 0.00, 750.00, '2025-01-27 12:48:22'),
(6, 2, 6, 2, 600.00, 350.00, 0.00, 1200.00, '2025-01-27 12:48:22'),
(7, 3, 4, 4, 18900.00, 12500.00, 3780.00, 71820.00, '2025-01-14 08:56:29'),
(8, 4, 3, 1, 4200.00, 2800.00, 210.00, 3990.00, '2025-01-03 02:58:57'),
(9, 4, 8, 2, 4800.00, 3200.00, 0.00, 9600.00, '2025-01-03 02:58:57'),
(10, 5, 8, 2, 4800.00, 3200.00, 0.00, 9600.00, '2025-02-16 10:54:50'),
(11, 5, 4, 2, 18900.00, 12500.00, 0.00, 37800.00, '2025-02-16 10:54:50'),
(12, 6, 4, 1, 18900.00, 12500.00, 0.00, 18900.00, '2025-02-04 06:57:35'),
(13, 6, 4, 3, 18900.00, 12500.00, 0.00, 56700.00, '2025-02-04 06:57:35'),
(14, 6, 8, 1, 4800.00, 3200.00, 0.00, 4800.00, '2025-02-04 06:57:35'),
(15, 6, 8, 1, 4800.00, 3200.00, 0.00, 4800.00, '2025-02-04 06:57:35'),
(16, 7, 8, 2, 4800.00, 3200.00, 0.00, 9600.00, '2025-02-21 09:29:29'),
(17, 7, 4, 4, 18900.00, 12500.00, 0.00, 75600.00, '2025-02-21 09:29:29'),
(18, 7, 4, 2, 18900.00, 12500.00, 0.00, 37800.00, '2025-02-21 09:29:29'),
(19, 7, 4, 1, 18900.00, 12500.00, 0.00, 18900.00, '2025-02-21 09:29:29'),
(20, 8, 7, 1, 12500.00, 8200.00, 0.00, 12500.00, '2025-02-03 04:27:29'),
(21, 8, 7, 4, 12500.00, 8200.00, 0.00, 50000.00, '2025-02-03 04:27:29'),
(22, 8, 2, 3, 750.00, 450.00, 112.50, 2137.50, '2025-02-03 04:27:29'),
(23, 9, 4, 3, 18900.00, 12500.00, 0.00, 56700.00, '2025-03-05 08:26:19'),
(24, 10, 8, 3, 4800.00, 3200.00, 0.00, 14400.00, '2025-03-21 06:26:12'),
(25, 10, 4, 3, 18900.00, 12500.00, 0.00, 56700.00, '2025-03-21 06:26:12'),
(26, 10, 8, 1, 4800.00, 3200.00, 0.00, 4800.00, '2025-03-21 06:26:12'),
(27, 11, 8, 2, 4800.00, 3200.00, 0.00, 9600.00, '2025-03-23 13:05:16'),
(28, 11, 6, 3, 600.00, 350.00, 0.00, 1800.00, '2025-03-23 13:05:16'),
(29, 11, 7, 2, 12500.00, 8200.00, 0.00, 25000.00, '2025-03-23 13:05:16'),
(30, 12, 5, 3, 8500.00, 5800.00, 0.00, 25500.00, '2025-03-09 13:22:49'),
(31, 13, 5, 1, 8500.00, 5800.00, 425.00, 8075.00, '2025-04-27 11:33:06'),
(32, 13, 6, 4, 600.00, 350.00, 0.00, 2400.00, '2025-04-27 11:33:06'),
(33, 14, 4, 2, 18900.00, 12500.00, 0.00, 37800.00, '2025-04-02 12:21:37'),
(34, 14, 7, 3, 12500.00, 8200.00, 0.00, 37500.00, '2025-04-02 12:21:37'),
(35, 15, 7, 2, 12500.00, 8200.00, 0.00, 25000.00, '2025-04-28 05:40:31'),
(36, 15, 7, 1, 12500.00, 8200.00, 0.00, 12500.00, '2025-04-28 05:40:31'),
(37, 15, 8, 1, 4800.00, 3200.00, 0.00, 4800.00, '2025-04-28 05:40:31'),
(38, 16, 4, 2, 18900.00, 12500.00, 0.00, 37800.00, '2025-04-26 03:50:08'),
(39, 17, 5, 4, 8500.00, 5800.00, 1700.00, 32300.00, '2025-05-26 09:33:09'),
(40, 17, 6, 4, 600.00, 350.00, 0.00, 2400.00, '2025-05-26 09:33:09'),
(41, 17, 8, 4, 4800.00, 3200.00, 0.00, 19200.00, '2025-05-26 09:33:09'),
(42, 17, 7, 1, 12500.00, 8200.00, 0.00, 12500.00, '2025-05-26 09:33:09'),
(43, 18, 8, 2, 4800.00, 3200.00, 0.00, 9600.00, '2025-05-21 05:05:59'),
(44, 18, 7, 3, 12500.00, 8200.00, 0.00, 37500.00, '2025-05-21 05:05:59'),
(45, 18, 1, 1, 4500.00, 3200.00, 0.00, 4500.00, '2025-05-21 05:05:59'),
(46, 19, 1, 3, 4500.00, 3200.00, 0.00, 13500.00, '2025-05-03 04:11:21'),
(47, 19, 8, 2, 4800.00, 3200.00, 0.00, 9600.00, '2025-05-03 04:11:21'),
(48, 19, 1, 2, 4500.00, 3200.00, 0.00, 9000.00, '2025-05-03 04:11:21'),
(49, 20, 1, 2, 4500.00, 3200.00, 0.00, 9000.00, '2025-05-22 10:49:48'),
(50, 20, 8, 3, 4800.00, 3200.00, 0.00, 14400.00, '2025-05-22 10:49:48'),
(51, 21, 7, 4, 12500.00, 8200.00, 2500.00, 47500.00, '2025-05-04 06:24:09'),
(52, 22, 1, 3, 4500.00, 3200.00, 0.00, 13500.00, '2025-05-23 09:23:35'),
(53, 22, 1, 3, 4500.00, 3200.00, 0.00, 13500.00, '2025-05-23 09:23:35'),
(54, 22, 5, 4, 8500.00, 5800.00, 1700.00, 32300.00, '2025-05-23 09:23:35'),
(55, 22, 3, 4, 4200.00, 2800.00, 0.00, 16800.00, '2025-05-23 09:23:35'),
(56, 23, 8, 4, 4800.00, 3200.00, 0.00, 19200.00, '2025-06-07 06:26:26'),
(57, 23, 5, 2, 8500.00, 5800.00, 0.00, 17000.00, '2025-06-07 06:26:26'),
(58, 24, 5, 4, 8500.00, 5800.00, 0.00, 34000.00, '2025-06-24 06:34:39'),
(59, 24, 5, 3, 8500.00, 5800.00, 1275.00, 24225.00, '2025-06-24 06:34:39'),
(60, 24, 2, 4, 750.00, 450.00, 0.00, 3000.00, '2025-06-24 06:34:39'),
(61, 25, 8, 4, 4800.00, 3200.00, 0.00, 19200.00, '2025-06-28 08:13:06'),
(62, 25, 5, 4, 8500.00, 5800.00, 0.00, 34000.00, '2025-06-28 08:13:06'),
(63, 25, 5, 2, 8500.00, 5800.00, 0.00, 17000.00, '2025-06-28 08:13:06'),
(64, 25, 5, 4, 8500.00, 5800.00, 1700.00, 32300.00, '2025-06-28 08:13:06'),
(65, 26, 8, 2, 4800.00, 3200.00, 0.00, 9600.00, '2025-06-26 07:59:05'),
(66, 26, 1, 1, 4500.00, 3200.00, 0.00, 4500.00, '2025-06-26 07:59:05'),
(67, 26, 8, 1, 4800.00, 3200.00, 0.00, 4800.00, '2025-06-26 07:59:05'),
(68, 26, 5, 3, 8500.00, 5800.00, 0.00, 25500.00, '2025-06-26 07:59:05'),
(69, 27, 1, 1, 4500.00, 3200.00, 0.00, 4500.00, '2025-06-18 06:29:47'),
(70, 28, 1, 1, 4500.00, 3200.00, 0.00, 4500.00, '2025-06-14 10:32:44'),
(71, 28, 1, 3, 4500.00, 3200.00, 0.00, 13500.00, '2025-06-14 10:32:44'),
(72, 28, 3, 2, 4200.00, 2800.00, 420.00, 7980.00, '2025-06-14 10:32:44'),
(73, 28, 7, 3, 12500.00, 8200.00, 0.00, 37500.00, '2025-06-14 10:32:44'),
(74, 29, 2, 3, 750.00, 450.00, 0.00, 2250.00, '2025-07-05 13:01:58'),
(75, 30, 5, 4, 8500.00, 5800.00, 0.00, 34000.00, '2025-07-05 11:42:09'),
(76, 30, 1, 4, 4500.00, 3200.00, 0.00, 18000.00, '2025-07-05 11:42:09'),
(77, 30, 8, 2, 4800.00, 3200.00, 480.00, 9120.00, '2025-07-05 11:42:09'),
(78, 30, 8, 1, 4800.00, 3200.00, 0.00, 4800.00, '2025-07-05 11:42:09'),
(79, 31, 1, 1, 4500.00, 3200.00, 0.00, 4500.00, '2025-07-23 08:19:56'),
(80, 31, 5, 2, 8500.00, 5800.00, 850.00, 16150.00, '2025-07-23 08:19:56'),
(81, 31, 8, 1, 4800.00, 3200.00, 0.00, 4800.00, '2025-07-23 08:19:56'),
(82, 31, 5, 3, 8500.00, 5800.00, 0.00, 25500.00, '2025-07-23 08:19:56'),
(83, 32, 8, 1, 4800.00, 3200.00, 0.00, 4800.00, '2025-07-16 12:49:28'),
(84, 32, 8, 4, 4800.00, 3200.00, 0.00, 19200.00, '2025-07-16 12:49:28'),
(85, 32, 1, 2, 4500.00, 3200.00, 0.00, 9000.00, '2025-07-16 12:49:28'),
(86, 33, 5, 2, 8500.00, 5800.00, 0.00, 17000.00, '2025-07-13 02:48:37'),
(87, 34, 1, 4, 4500.00, 3200.00, 0.00, 18000.00, '2025-07-23 04:27:56'),
(88, 34, 6, 3, 600.00, 350.00, 0.00, 1800.00, '2025-07-23 04:27:56'),
(89, 35, 1, 3, 4500.00, 3200.00, 0.00, 13500.00, '2025-08-11 05:02:52'),
(90, 35, 8, 4, 4800.00, 3200.00, 0.00, 19200.00, '2025-08-11 05:02:52'),
(91, 35, 8, 1, 4800.00, 3200.00, 0.00, 4800.00, '2025-08-11 05:02:52'),
(92, 36, 8, 4, 4800.00, 3200.00, 0.00, 19200.00, '2025-08-10 13:04:39'),
(93, 37, 1, 1, 4500.00, 3200.00, 0.00, 4500.00, '2025-08-07 09:30:18'),
(94, 37, 5, 2, 8500.00, 5800.00, 0.00, 17000.00, '2025-08-07 09:30:18'),
(95, 37, 1, 3, 4500.00, 3200.00, 0.00, 13500.00, '2025-08-07 09:30:18'),
(96, 37, 8, 2, 4800.00, 3200.00, 0.00, 9600.00, '2025-08-07 09:30:18'),
(97, 38, 5, 2, 8500.00, 5800.00, 0.00, 17000.00, '2025-08-06 12:28:17'),
(98, 38, 1, 3, 4500.00, 3200.00, 675.00, 12825.00, '2025-08-06 12:28:17'),
(99, 38, 8, 1, 4800.00, 3200.00, 0.00, 4800.00, '2025-08-06 12:28:17'),
(100, 39, 6, 1, 600.00, 350.00, 0.00, 600.00, '2025-08-27 05:58:56'),
(101, 39, 8, 1, 4800.00, 3200.00, 0.00, 4800.00, '2025-08-27 05:58:56'),
(102, 40, 7, 4, 12500.00, 8200.00, 0.00, 50000.00, '2025-08-12 08:38:51'),
(103, 40, 8, 4, 4800.00, 3200.00, 0.00, 19200.00, '2025-08-12 08:38:51'),
(104, 40, 1, 3, 4500.00, 3200.00, 0.00, 13500.00, '2025-08-12 08:38:51'),
(105, 40, 1, 3, 4500.00, 3200.00, 0.00, 13500.00, '2025-08-12 08:38:51'),
(106, 41, 8, 2, 4800.00, 3200.00, 0.00, 9600.00, '2025-09-03 09:48:39'),
(107, 41, 1, 3, 4500.00, 3200.00, 0.00, 13500.00, '2025-09-03 09:48:39'),
(108, 41, 8, 2, 4800.00, 3200.00, 0.00, 9600.00, '2025-09-03 09:48:39'),
(109, 42, 1, 4, 4500.00, 3200.00, 0.00, 18000.00, '2025-09-14 07:14:33'),
(110, 43, 6, 1, 600.00, 350.00, 0.00, 600.00, '2025-09-19 13:19:13'),
(111, 43, 1, 2, 4500.00, 3200.00, 0.00, 9000.00, '2025-09-19 13:19:13'),
(112, 44, 5, 4, 8500.00, 5800.00, 0.00, 34000.00, '2025-09-07 06:35:51'),
(113, 44, 8, 1, 4800.00, 3200.00, 0.00, 4800.00, '2025-09-07 06:35:51'),
(114, 45, 1, 1, 4500.00, 3200.00, 0.00, 4500.00, '2025-09-24 10:48:30'),
(115, 45, 1, 1, 4500.00, 3200.00, 0.00, 4500.00, '2025-09-24 10:48:30'),
(116, 45, 2, 2, 750.00, 450.00, 0.00, 1500.00, '2025-09-24 10:48:30'),
(117, 45, 5, 1, 8500.00, 5800.00, 0.00, 8500.00, '2025-09-24 10:48:30'),
(118, 46, 4, 1, 18900.00, 12500.00, 0.00, 18900.00, '2025-09-06 08:09:33'),
(119, 47, 2, 4, 750.00, 450.00, 0.00, 3000.00, '2025-10-03 07:20:25'),
(120, 48, 3, 2, 4200.00, 2800.00, 0.00, 8400.00, '2025-10-10 03:23:08'),
(121, 49, 2, 4, 750.00, 450.00, 0.00, 3000.00, '2025-10-04 05:40:44'),
(122, 49, 8, 3, 4800.00, 3200.00, 0.00, 14400.00, '2025-10-04 05:40:44'),
(123, 49, 8, 3, 4800.00, 3200.00, 0.00, 14400.00, '2025-10-04 05:40:44'),
(124, 49, 6, 4, 600.00, 350.00, 120.00, 2280.00, '2025-10-04 05:40:44'),
(125, 50, 2, 2, 750.00, 450.00, 0.00, 1500.00, '2025-10-24 08:35:54'),
(126, 51, 3, 3, 4200.00, 2800.00, 0.00, 12600.00, '2025-10-22 07:49:15'),
(127, 51, 3, 2, 4200.00, 2800.00, 0.00, 8400.00, '2025-10-22 07:49:15'),
(128, 52, 3, 3, 4200.00, 2800.00, 0.00, 12600.00, '2025-11-04 07:39:22'),
(129, 52, 2, 1, 750.00, 450.00, 0.00, 750.00, '2025-11-04 07:39:22'),
(130, 52, 8, 3, 4800.00, 3200.00, 0.00, 14400.00, '2025-11-04 07:39:22'),
(131, 52, 5, 1, 8500.00, 5800.00, 0.00, 8500.00, '2025-11-04 07:39:22'),
(132, 53, 8, 3, 4800.00, 3200.00, 0.00, 14400.00, '2025-11-02 02:54:17'),
(133, 54, 8, 1, 4800.00, 3200.00, 0.00, 4800.00, '2025-11-08 06:38:51'),
(134, 54, 8, 4, 4800.00, 3200.00, 0.00, 19200.00, '2025-11-08 06:38:51'),
(135, 54, 2, 3, 750.00, 450.00, 0.00, 2250.00, '2025-11-08 06:38:51'),
(136, 55, 1, 1, 4500.00, 3200.00, 0.00, 4500.00, '2025-11-11 09:15:12'),
(137, 56, 7, 4, 12500.00, 8200.00, 0.00, 50000.00, '2025-11-05 11:14:49'),
(138, 57, 5, 1, 8500.00, 5800.00, 0.00, 8500.00, '2025-12-15 07:56:27'),
(139, 57, 3, 2, 4200.00, 2800.00, 0.00, 8400.00, '2025-12-15 07:56:27'),
(140, 58, 1, 3, 4500.00, 3200.00, 0.00, 13500.00, '2025-12-12 09:21:38'),
(141, 59, 1, 2, 4500.00, 3200.00, 0.00, 9000.00, '2025-12-22 06:53:59'),
(142, 59, 8, 1, 4800.00, 3200.00, 0.00, 4800.00, '2025-12-22 06:53:59'),
(143, 60, 6, 2, 600.00, 350.00, 0.00, 1200.00, '2025-12-28 13:25:29'),
(144, 60, 6, 2, 600.00, 350.00, 0.00, 1200.00, '2025-12-28 13:25:29'),
(145, 60, 6, 2, 600.00, 350.00, 0.00, 1200.00, '2025-12-28 13:25:29'),
(146, 61, 1, 2, 4500.00, 3200.00, 0.00, 9000.00, '2025-12-05 11:51:36'),
(147, 61, 4, 2, 18900.00, 12500.00, 0.00, 37800.00, '2025-12-05 11:51:36'),
(148, 61, 8, 3, 4800.00, 3200.00, 0.00, 14400.00, '2025-12-05 11:51:36'),
(149, 61, 6, 2, 600.00, 350.00, 0.00, 1200.00, '2025-12-05 11:51:36'),
(150, 62, 5, 1, 8500.00, 5800.00, 0.00, 8500.00, '2026-01-26 10:29:31'),
(151, 62, 4, 4, 18900.00, 12500.00, 0.00, 75600.00, '2026-01-26 10:29:31'),
(152, 62, 6, 4, 600.00, 350.00, 0.00, 2400.00, '2026-01-26 10:29:31'),
(153, 63, 5, 2, 8500.00, 5800.00, 0.00, 17000.00, '2026-01-06 08:13:06'),
(154, 63, 7, 3, 12500.00, 8200.00, 0.00, 37500.00, '2026-01-06 08:13:06'),
(155, 63, 2, 4, 750.00, 450.00, 0.00, 3000.00, '2026-01-06 08:13:06'),
(156, 63, 7, 1, 12500.00, 8200.00, 0.00, 12500.00, '2026-01-06 08:13:06'),
(157, 64, 5, 3, 8500.00, 5800.00, 1275.00, 24225.00, '2026-01-20 05:41:06'),
(158, 64, 1, 3, 4500.00, 3200.00, 0.00, 13500.00, '2026-01-20 05:41:06'),
(159, 64, 2, 3, 750.00, 450.00, 0.00, 2250.00, '2026-01-20 05:41:06'),
(160, 65, 6, 1, 600.00, 350.00, 0.00, 600.00, '2026-01-15 03:27:05'),
(161, 65, 3, 3, 4200.00, 2800.00, 0.00, 12600.00, '2026-01-15 03:27:05'),
(162, 65, 1, 4, 4500.00, 3200.00, 0.00, 18000.00, '2026-01-15 03:27:05'),
(163, 66, 1, 1, 4500.00, 3200.00, 0.00, 4500.00, '2026-01-10 12:09:19'),
(164, 66, 5, 3, 8500.00, 5800.00, 0.00, 25500.00, '2026-01-10 12:09:19'),
(165, 66, 2, 3, 750.00, 450.00, 0.00, 2250.00, '2026-01-10 12:09:19'),
(166, 67, 8, 4, 4800.00, 3200.00, 0.00, 19200.00, '2026-02-28 05:59:35'),
(167, 68, 2, 4, 750.00, 450.00, 0.00, 3000.00, '2026-02-04 03:18:41'),
(168, 68, 6, 2, 600.00, 350.00, 0.00, 1200.00, '2026-02-04 03:18:41'),
(169, 68, 8, 4, 4800.00, 3200.00, 0.00, 19200.00, '2026-02-04 03:18:41'),
(170, 68, 4, 1, 18900.00, 12500.00, 945.00, 17955.00, '2026-02-04 03:18:41'),
(171, 69, 8, 1, 4800.00, 3200.00, 0.00, 4800.00, '2026-02-08 06:11:11'),
(172, 70, 7, 3, 12500.00, 8200.00, 1875.00, 35625.00, '2026-02-22 06:19:23'),
(173, 70, 3, 2, 4200.00, 2800.00, 0.00, 8400.00, '2026-02-22 06:19:23'),
(174, 70, 4, 1, 18900.00, 12500.00, 0.00, 18900.00, '2026-02-22 06:19:23'),
(175, 70, 8, 4, 4800.00, 3200.00, 0.00, 19200.00, '2026-02-22 06:19:23'),
(176, 71, 4, 3, 18900.00, 12500.00, 0.00, 56700.00, '2026-02-19 11:24:30'),
(177, 71, 8, 3, 4800.00, 3200.00, 0.00, 14400.00, '2026-02-19 11:24:30'),
(178, 71, 8, 2, 4800.00, 3200.00, 0.00, 9600.00, '2026-02-19 11:24:30'),
(179, 71, 7, 2, 12500.00, 8200.00, 0.00, 25000.00, '2026-02-19 11:24:30'),
(180, 72, 8, 2, 4800.00, 3200.00, 0.00, 9600.00, '2026-03-14 07:28:44'),
(181, 72, 8, 1, 4800.00, 3200.00, 0.00, 4800.00, '2026-03-14 07:28:44'),
(182, 72, 6, 1, 600.00, 350.00, 0.00, 600.00, '2026-03-14 07:28:44'),
(183, 73, 8, 2, 4800.00, 3200.00, 0.00, 9600.00, '2026-03-14 06:40:04'),
(184, 73, 4, 3, 18900.00, 12500.00, 0.00, 56700.00, '2026-03-14 06:40:04'),
(185, 73, 4, 4, 18900.00, 12500.00, 0.00, 75600.00, '2026-03-14 06:40:04'),
(186, 74, 8, 2, 4800.00, 3200.00, 0.00, 9600.00, '2026-03-16 02:37:22'),
(187, 75, 6, 3, 600.00, 350.00, 0.00, 1800.00, '2026-03-04 07:06:41'),
(188, 75, 8, 3, 4800.00, 3200.00, 0.00, 14400.00, '2026-03-04 07:06:41'),
(189, 75, 1, 2, 4500.00, 3200.00, 0.00, 9000.00, '2026-03-04 07:06:41'),
(190, 76, 8, 4, 4800.00, 3200.00, 0.00, 19200.00, '2026-03-25 04:01:04'),
(191, 76, 7, 1, 12500.00, 8200.00, 0.00, 12500.00, '2026-03-25 04:01:04'),
(192, 76, 4, 4, 18900.00, 12500.00, 3780.00, 71820.00, '2026-03-25 04:01:04'),
(193, 77, 8, 3, 4800.00, 3200.00, 0.00, 14400.00, '2026-04-03 10:09:02'),
(194, 77, 8, 3, 4800.00, 3200.00, 0.00, 14400.00, '2026-04-03 10:09:02'),
(195, 77, 8, 2, 4800.00, 3200.00, 480.00, 9120.00, '2026-04-03 10:09:02'),
(196, 77, 8, 2, 4800.00, 3200.00, 480.00, 9120.00, '2026-04-03 10:09:02'),
(197, 78, 2, 3, 750.00, 450.00, 0.00, 2250.00, '2026-04-15 09:58:48'),
(198, 78, 3, 4, 4200.00, 2800.00, 840.00, 15960.00, '2026-04-15 09:58:48'),
(199, 79, 4, 4, 18900.00, 12500.00, 0.00, 75600.00, '2026-04-24 12:22:34'),
(200, 79, 4, 3, 18900.00, 12500.00, 0.00, 56700.00, '2026-04-24 12:22:34'),
(201, 79, 5, 1, 8500.00, 5800.00, 0.00, 8500.00, '2026-04-24 12:22:34'),
(202, 79, 4, 1, 18900.00, 12500.00, 945.00, 17955.00, '2026-04-24 12:22:34'),
(203, 80, 6, 3, 600.00, 350.00, 90.00, 1710.00, '2026-04-10 12:57:44'),
(204, 80, 8, 1, 4800.00, 3200.00, 0.00, 4800.00, '2026-04-10 12:57:44'),
(205, 81, 6, 3, 600.00, 350.00, 0.00, 1800.00, '2026-04-23 08:05:43'),
(206, 81, 4, 1, 18900.00, 12500.00, 0.00, 18900.00, '2026-04-23 08:05:43'),
(207, 82, 1, 2, 4500.00, 3200.00, 0.00, 9000.00, '2026-05-05 05:28:10'),
(208, 82, 8, 3, 4800.00, 3200.00, 0.00, 14400.00, '2026-05-05 05:28:10'),
(209, 83, 1, 2, 4500.00, 3200.00, 0.00, 9000.00, '2026-05-27 06:28:37'),
(210, 83, 8, 2, 4800.00, 3200.00, 0.00, 9600.00, '2026-05-27 06:28:37'),
(211, 83, 1, 2, 4500.00, 3200.00, 0.00, 9000.00, '2026-05-27 06:28:37'),
(212, 84, 8, 3, 4800.00, 3200.00, 0.00, 14400.00, '2026-05-06 06:47:32'),
(213, 85, 5, 4, 8500.00, 5800.00, 0.00, 34000.00, '2026-05-26 08:59:22'),
(214, 86, 7, 3, 12500.00, 8200.00, 0.00, 37500.00, '2026-05-14 02:45:51'),
(215, 87, 5, 3, 8500.00, 5800.00, 0.00, 25500.00, '2026-05-14 03:44:43'),
(216, 87, 8, 1, 4800.00, 3200.00, 0.00, 4800.00, '2026-05-14 03:44:43'),
(217, 87, 5, 1, 8500.00, 5800.00, 425.00, 8075.00, '2026-05-14 03:44:43'),
(218, 87, 8, 1, 4800.00, 3200.00, 0.00, 4800.00, '2026-05-14 03:44:43'),
(219, 88, 5, 1, 8500.00, 5800.00, 0.00, 8500.00, '2026-05-05 10:07:46'),
(220, 88, 8, 4, 4800.00, 3200.00, 0.00, 19200.00, '2026-05-05 10:07:46'),
(221, 88, 8, 3, 4800.00, 3200.00, 0.00, 14400.00, '2026-05-05 10:07:46'),
(222, 88, 6, 4, 600.00, 350.00, 0.00, 2400.00, '2026-05-05 10:07:46'),
(223, 89, 8, 4, 4800.00, 3200.00, 0.00, 19200.00, '2026-05-03 06:26:45'),
(224, 89, 1, 3, 4500.00, 3200.00, 675.00, 12825.00, '2026-05-03 06:26:45'),
(225, 89, 8, 2, 4800.00, 3200.00, 0.00, 9600.00, '2026-05-03 06:26:45'),
(226, 90, 2, 1, 750.00, 450.00, 0.00, 750.00, '2026-06-18 05:35:41'),
(227, 91, 5, 3, 8500.00, 5800.00, 0.00, 25500.00, '2026-06-14 12:52:01'),
(228, 91, 3, 4, 4200.00, 2800.00, 0.00, 16800.00, '2026-06-14 12:52:01'),
(229, 91, 1, 1, 4500.00, 3200.00, 0.00, 4500.00, '2026-06-14 12:52:01'),
(230, 91, 1, 4, 4500.00, 3200.00, 0.00, 18000.00, '2026-06-14 12:52:01'),
(231, 92, 8, 2, 4800.00, 3200.00, 0.00, 9600.00, '2026-06-09 04:13:50'),
(232, 93, 4, 4, 18900.00, 12500.00, 3780.00, 71820.00, '2026-06-28 12:59:55'),
(233, 93, 8, 2, 4800.00, 3200.00, 480.00, 9120.00, '2026-06-28 12:59:55'),
(234, 93, 8, 2, 4800.00, 3200.00, 480.00, 9120.00, '2026-06-28 12:59:55'),
(235, 93, 5, 4, 8500.00, 5800.00, 1700.00, 32300.00, '2026-06-28 12:59:55'),
(236, 94, 8, 2, 4800.00, 3200.00, 0.00, 9600.00, '2026-06-26 10:28:15'),
(237, 95, 8, 1, 4800.00, 3200.00, 0.00, 4800.00, '2026-06-05 13:15:13'),
(238, 95, 5, 3, 8500.00, 5800.00, 1275.00, 24225.00, '2026-06-05 13:15:13'),
(239, 95, 2, 1, 750.00, 450.00, 0.00, 750.00, '2026-06-05 13:15:13'),
(240, 95, 3, 1, 4200.00, 2800.00, 0.00, 4200.00, '2026-06-05 13:15:13'),
(241, 96, 5, 3, 8500.00, 5800.00, 0.00, 25500.00, '2026-06-10 03:48:14'),
(242, 96, 1, 4, 4500.00, 3200.00, 0.00, 18000.00, '2026-06-10 03:48:14'),
(243, 97, 1, 1, 4500.00, 3200.00, 0.00, 4500.00, '2026-06-17 06:23:27'),
(244, 97, 8, 3, 4800.00, 3200.00, 0.00, 14400.00, '2026-06-17 06:23:27'),
(245, 97, 8, 4, 4800.00, 3200.00, 960.00, 18240.00, '2026-06-17 06:23:27'),
(246, 97, 1, 1, 4500.00, 3200.00, 0.00, 4500.00, '2026-06-17 06:23:27'),
(247, 98, 5, 1, 8500.00, 5800.00, 0.00, 8500.00, '2026-07-05 02:43:58'),
(248, 98, 5, 2, 8500.00, 5800.00, 0.00, 17000.00, '2026-07-05 02:43:58'),
(249, 98, 2, 2, 750.00, 450.00, 0.00, 1500.00, '2026-07-05 02:43:58'),
(250, 99, 8, 2, 4800.00, 3200.00, 480.00, 9120.00, '2026-07-07 05:30:56'),
(251, 99, 5, 1, 8500.00, 5800.00, 0.00, 8500.00, '2026-07-07 05:30:56'),
(252, 99, 5, 2, 8500.00, 5800.00, 0.00, 17000.00, '2026-07-07 05:30:56'),
(253, 99, 8, 1, 4800.00, 3200.00, 0.00, 4800.00, '2026-07-07 05:30:56'),
(254, 100, 1, 2, 4500.00, 3200.00, 0.00, 9000.00, '2026-07-18 06:13:09'),
(255, 101, 8, 2, 4800.00, 3200.00, 0.00, 9600.00, '2026-07-21 06:53:54'),
(256, 101, 1, 1, 4500.00, 3200.00, 0.00, 4500.00, '2026-07-21 06:53:54'),
(257, 101, 5, 1, 8500.00, 5800.00, 0.00, 8500.00, '2026-07-21 06:53:54'),
(258, 102, 8, 1, 4800.00, 3200.00, 0.00, 4800.00, '2026-07-07 06:40:50'),
(259, 102, 1, 1, 4500.00, 3200.00, 0.00, 4500.00, '2026-07-07 06:40:50'),
(260, 103, 5, 1, 8500.00, 5800.00, 0.00, 8500.00, '2026-07-20 03:39:05'),
(261, 103, 1, 3, 4500.00, 3200.00, 0.00, 13500.00, '2026-07-20 03:39:05'),
(262, 104, 1, 2, 4500.00, 3200.00, 0.00, 9000.00, '2026-07-19 13:07:38'),
(263, 104, 5, 2, 8500.00, 5800.00, 0.00, 17000.00, '2026-07-19 13:07:38'),
(264, 104, 5, 1, 8500.00, 5800.00, 0.00, 8500.00, '2026-07-19 13:07:38'),
(265, 104, 1, 2, 4500.00, 3200.00, 0.00, 9000.00, '2026-07-19 13:07:38'),
(266, 105, 5, 4, 8500.00, 5800.00, 0.00, 34000.00, '2026-07-24 11:06:19'),
(267, 105, 6, 4, 600.00, 350.00, 120.00, 2280.00, '2026-07-24 11:06:19'),
(268, 105, 8, 2, 4800.00, 3200.00, 480.00, 9120.00, '2026-07-24 11:06:19'),
(269, 106, 1, 3, 4500.00, 3200.00, 0.00, 13500.00, '2026-08-05 10:41:43'),
(270, 106, 1, 3, 4500.00, 3200.00, 0.00, 13500.00, '2026-08-05 10:41:43'),
(271, 106, 5, 1, 8500.00, 5800.00, 0.00, 8500.00, '2026-08-05 10:41:43'),
(272, 106, 5, 1, 8500.00, 5800.00, 0.00, 8500.00, '2026-08-05 10:41:43'),
(273, 107, 8, 4, 4800.00, 3200.00, 0.00, 19200.00, '2026-08-20 12:22:53'),
(274, 107, 5, 4, 8500.00, 5800.00, 0.00, 34000.00, '2026-08-20 12:22:53'),
(275, 107, 5, 3, 8500.00, 5800.00, 0.00, 25500.00, '2026-08-20 12:22:53'),
(276, 107, 5, 4, 8500.00, 5800.00, 0.00, 34000.00, '2026-08-20 12:22:53'),
(277, 108, 6, 1, 600.00, 350.00, 0.00, 600.00, '2026-08-18 07:51:51'),
(278, 109, 1, 4, 4500.00, 3200.00, 0.00, 18000.00, '2026-08-28 05:15:54'),
(279, 110, 5, 4, 8500.00, 5800.00, 0.00, 34000.00, '2026-08-28 11:08:36'),
(280, 110, 1, 3, 4500.00, 3200.00, 0.00, 13500.00, '2026-08-28 11:08:36'),
(281, 111, 5, 4, 8500.00, 5800.00, 0.00, 34000.00, '2026-08-18 06:45:37'),
(282, 111, 8, 3, 4800.00, 3200.00, 0.00, 14400.00, '2026-08-18 06:45:37'),
(283, 112, 5, 3, 8500.00, 5800.00, 0.00, 25500.00, '2026-08-03 02:48:04'),
(284, 112, 1, 2, 4500.00, 3200.00, 0.00, 9000.00, '2026-08-03 02:48:04'),
(285, 112, 1, 1, 4500.00, 3200.00, 0.00, 4500.00, '2026-08-03 02:48:04'),
(286, 113, 1, 1, 4500.00, 3200.00, 0.00, 4500.00, '2026-08-03 05:56:20'),
(287, 113, 5, 1, 8500.00, 5800.00, 0.00, 8500.00, '2026-08-03 05:56:20'),
(288, 113, 6, 2, 600.00, 350.00, 0.00, 1200.00, '2026-08-03 05:56:20'),
(289, 114, 1, 4, 4500.00, 3200.00, 0.00, 18000.00, '2026-09-04 04:17:58'),
(290, 115, 5, 4, 8500.00, 5800.00, 1700.00, 32300.00, '2026-09-07 13:27:02'),
(291, 115, 1, 4, 4500.00, 3200.00, 0.00, 18000.00, '2026-09-07 13:27:02'),
(292, 116, 5, 4, 8500.00, 5800.00, 0.00, 34000.00, '2026-09-15 09:52:03'),
(293, 116, 6, 1, 600.00, 350.00, 0.00, 600.00, '2026-09-15 09:52:03'),
(294, 117, 8, 3, 4800.00, 3200.00, 0.00, 14400.00, '2026-09-14 07:23:58'),
(295, 118, 5, 2, 8500.00, 5800.00, 0.00, 17000.00, '2026-09-27 11:03:04'),
(296, 118, 5, 3, 8500.00, 5800.00, 0.00, 25500.00, '2026-09-27 11:03:04'),
(297, 118, 1, 3, 4500.00, 3200.00, 0.00, 13500.00, '2026-09-27 11:03:04'),
(298, 118, 1, 4, 4500.00, 3200.00, 0.00, 18000.00, '2026-09-27 11:03:04'),
(299, 119, 1, 3, 4500.00, 3200.00, 0.00, 13500.00, '2026-09-06 09:44:07'),
(300, 119, 5, 1, 8500.00, 5800.00, 0.00, 8500.00, '2026-09-06 09:44:07'),
(301, 119, 1, 2, 4500.00, 3200.00, 0.00, 9000.00, '2026-09-06 09:44:07'),
(302, 119, 1, 1, 4500.00, 3200.00, 0.00, 4500.00, '2026-09-06 09:44:07'),
(303, 120, 8, 2, 4800.00, 3200.00, 0.00, 9600.00, '2026-09-23 13:12:15'),
(304, 121, 6, 4, 600.00, 350.00, 0.00, 2400.00, '2026-09-13 09:14:11'),
(305, 121, 6, 3, 600.00, 350.00, 0.00, 1800.00, '2026-09-13 09:14:11'),
(306, 122, 8, 1, 4800.00, 3200.00, 0.00, 4800.00, '2026-09-27 07:48:30'),
(307, 122, 1, 1, 4500.00, 3200.00, 0.00, 4500.00, '2026-09-27 07:48:30'),
(308, 122, 8, 2, 4800.00, 3200.00, 0.00, 9600.00, '2026-09-27 07:48:30'),
(309, 123, 5, 2, 8500.00, 5800.00, 0.00, 17000.00, '2026-09-27 11:32:54'),
(310, 123, 5, 4, 8500.00, 5800.00, 1700.00, 32300.00, '2026-09-27 11:32:54'),
(311, 123, 2, 4, 750.00, 450.00, 0.00, 3000.00, '2026-09-27 11:32:54'),
(312, 124, 7, 2, 12500.00, 8200.00, 0.00, 25000.00, '2026-09-26 03:49:12'),
(313, 125, 3, 1, 4200.00, 2800.00, 0.00, 4200.00, '2026-09-25 07:47:09'),
(314, 126, 2, 4, 750.00, 450.00, 0.00, 3000.00, '2026-09-24 06:28:48'),
(315, 126, 3, 1, 4200.00, 2800.00, 0.00, 4200.00, '2026-09-24 06:28:48'),
(317, 128, 6, 2, 600.00, 350.00, 0.00, 1200.00, '2026-09-27 16:27:37'),
(318, 128, 4, 2, 18900.00, 12500.00, 0.00, 37800.00, '2026-09-27 16:27:37'),
(319, 128, 8, 1, 4800.00, 3200.00, 0.00, 4800.00, '2026-09-27 16:27:37'),
(320, 128, 3, 1, 4200.00, 2800.00, 0.00, 4200.00, '2026-09-27 16:27:37');

-- Delivery dispatches & tracking (`deliveries`)
INSERT INTO `deliveries` (`id`, `delivery_number`, `order_id`, `sale_id`, `customer_id`, `delivery_rep_id`, `status`, `scheduled_date`, `delivered_at`, `delivery_address`, `recipient_name`, `recipient_phone`, `notes`, `created_at`, `updated_at`) VALUES
(4, 'DLV-00004', 5, NULL, 16, NULL, 'pending', NULL, NULL, '123 Sample St', 'Test Customer', '+94 77 123 9999', 'Created automatically for online customer order.', '2026-09-27 09:12:29', '2026-09-27 09:12:29'),
(11, 'DLV-00011', 12, NULL, 1, NULL, 'pending', NULL, NULL, '78 Temple Road, Colombo 03', 'City Auto Works', '+94771110001', 'Created automatically for online customer order.', '2026-09-27 15:13:04', '2026-09-27 15:13:04'),
(12, 'DLV-00012', 13, NULL, 1, NULL, 'returned', NULL, NULL, '99 Marine Drive, Colombo 03 (UPDATED)', 'City Auto Works', '+94771110001', 'Created automatically for online customer order.', '2026-09-27 15:14:00', '2026-09-27 15:14:00'),
(13, 'DLV-00013', 14, NULL, 1, NULL, 'returned', NULL, NULL, '99 Marine Drive, Colombo 03 (UPDATED)', 'City Auto Works', '+94771110001', 'Created automatically for online customer order.', '2026-09-27 15:14:17', '2026-09-27 15:14:17'),
(14, 'DLV-00014', 15, NULL, 1, NULL, 'returned', NULL, NULL, '99 Marine Drive, Colombo 03 (UPDATED)', 'City Auto Works', '+94771110001', 'Created automatically for online customer order.', '2026-09-27 15:14:54', '2026-09-27 15:14:55'),
(15, 'DLV-00015', 17, NULL, 1, NULL, 'returned', NULL, NULL, '99 Marine Drive, Colombo 03 (UPDATED)', 'City Auto Works', '+94771110001', 'Created automatically for online customer order.', '2026-09-27 15:15:43', '2026-09-27 15:15:43'),
(16, 'DLV-00016', 19, NULL, 1, NULL, 'returned', NULL, NULL, '99 Marine Drive, Colombo 03 (UPDATED)', 'City Auto Works', '+94771110001', 'Created automatically for online customer order.', '2026-09-27 15:16:15', '2026-09-27 15:16:15');

-- Customer payment receipts (`payments`)
INSERT INTO `payments` (`id`, `payment_number`, `payable_type`, `payable_id`, `customer_id`, `supplier_id`, `payment_date`, `amount`, `payment_method`, `reference_no`, `notes`, `received_by`, `created_at`) VALUES
(2, 'PAY-00002', 'sale', 128, 40, NULL, '2026-09-27 21:57:37', 48000.00, 'credit', NULL, NULL, 3, '2026-09-27 16:27:37');

-- -----------------------------------------------------------------------------
-- Auto-Increment Resets (ensures continuous sequencing after import)
-- -----------------------------------------------------------------------------

ALTER TABLE `activity_logs` AUTO_INCREMENT = 21;
ALTER TABLE `brands` AUTO_INCREMENT = 9;
ALTER TABLE `categories` AUTO_INCREMENT = 9;
ALTER TABLE `customers` AUTO_INCREMENT = 43;
ALTER TABLE `deliveries` AUTO_INCREMENT = 17;
ALTER TABLE `employees` AUTO_INCREMENT = 28;
ALTER TABLE `inventory` AUTO_INCREMENT = 20;
ALTER TABLE `order_items` AUTO_INCREMENT = 20;
ALTER TABLE `orders` AUTO_INCREMENT = 20;
ALTER TABLE `payments` AUTO_INCREMENT = 3;
ALTER TABLE `product_compatibility` AUTO_INCREMENT = 9;
ALTER TABLE `products` AUTO_INCREMENT = 20;
ALTER TABLE `purchase_order_items` AUTO_INCREMENT = 3;
ALTER TABLE `purchase_orders` AUTO_INCREMENT = 2;
ALTER TABLE `roles` AUTO_INCREMENT = 5;
ALTER TABLE `sale_items` AUTO_INCREMENT = 321;
ALTER TABLE `sales` AUTO_INCREMENT = 129;
ALTER TABLE `sequences` AUTO_INCREMENT = 9;
ALTER TABLE `settings` AUTO_INCREMENT = 10;
ALTER TABLE `shops` AUTO_INCREMENT = 34;
ALTER TABLE `stock_movements` AUTO_INCREMENT = 10;
ALTER TABLE `supplier_products` AUTO_INCREMENT = 9;
ALTER TABLE `suppliers` AUTO_INCREMENT = 4;
ALTER TABLE `users` AUTO_INCREMENT = 46;
ALTER TABLE `vehicle_brands` AUTO_INCREMENT = 9;
ALTER TABLE `vehicle_engines` AUTO_INCREMENT = 13;
ALTER TABLE `vehicle_models` AUTO_INCREMENT = 13;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- USEFUL VIEWS (for reports & dashboards)
-- =============================================================================

CREATE OR REPLACE VIEW v_low_stock_products AS
SELECT
    p.id,
    p.product_code,
    p.name,
    c.name AS category,
    i.quantity_on_hand,
    i.reorder_level,
    i.reorder_quantity,
    p.selling_price
FROM inventory i
JOIN products p ON p.id = i.product_id
JOIN categories c ON c.id = p.category_id
WHERE i.quantity_on_hand <= i.reorder_level
  AND p.deleted_at IS NULL
  AND p.is_active = 1;

CREATE OR REPLACE VIEW v_product_stock AS
SELECT
    p.id,
    p.product_code,
    p.barcode,
    p.name,
    c.name AS category,
    b.name AS brand,
    p.cost_price,
    p.selling_price,
    i.quantity_on_hand,
    i.quantity_reserved,
    (i.quantity_on_hand - i.quantity_reserved) AS available_quantity,
    i.quantity_damaged,
    i.reorder_level,
    (i.quantity_on_hand * p.cost_price) AS stock_value
FROM products p
JOIN inventory i ON i.product_id = p.id
JOIN categories c ON c.id = p.category_id
LEFT JOIN brands b ON b.id = p.brand_id
WHERE p.deleted_at IS NULL;

CREATE OR REPLACE VIEW v_daily_sales_summary AS
SELECT
    DATE(s.sale_date) AS sale_day,
    COUNT(s.id) AS total_transactions,
    SUM(s.total_amount) AS gross_sales,
    SUM(s.discount_amount) AS total_discounts,
    SUM(s.amount_paid) AS total_collected
FROM sales s
WHERE s.deleted_at IS NULL
GROUP BY DATE(s.sale_date);

CREATE OR REPLACE VIEW v_employee_sales_performance AS
SELECT
    e.id AS employee_id,
    e.employee_code,
    u.full_name,
    COUNT(s.id) AS total_sales,
    COALESCE(SUM(s.total_amount), 0) AS total_revenue,
    COALESCE(SUM(s.total_amount * e.commission_rate / 100), 0) AS commission_earned
FROM employees e
JOIN users u ON u.id = e.user_id
LEFT JOIN sales s ON s.sales_rep_id = e.id AND s.deleted_at IS NULL
WHERE e.deleted_at IS NULL
GROUP BY e.id, e.employee_code, u.full_name, e.commission_rate;

CREATE OR REPLACE VIEW v_vehicle_parts_finder AS
SELECT
    p.id AS product_id,
    p.product_code,
    p.name AS product_name,
    p.selling_price,
    p.image_path,
    c.name AS category,
    b.name AS brand,
    vb.name AS vehicle_brand,
    vm.name AS vehicle_model,
    ve.engine_code,
    ve.fuel_type,
    ve.transmission,
    COALESCE(pc.year_from, ve.year_from) AS year_from,
    COALESCE(pc.year_to, ve.year_to) AS year_to,
    i.quantity_on_hand,
    (i.quantity_on_hand - i.quantity_reserved) AS available_quantity
FROM product_compatibility pc
JOIN products p ON p.id = pc.product_id
JOIN categories c ON c.id = p.category_id
LEFT JOIN brands b ON b.id = p.brand_id
JOIN inventory i ON i.product_id = p.id
LEFT JOIN vehicle_brands vb ON vb.id = pc.vehicle_brand_id
LEFT JOIN vehicle_models vm ON vm.id = pc.vehicle_model_id
LEFT JOIN vehicle_engines ve ON ve.id = pc.vehicle_engine_id
WHERE p.deleted_at IS NULL AND p.is_active = 1;

-- =============================================================================
-- END OF SCHEMA — 31 tables + 5 views
-- =============================================================================
