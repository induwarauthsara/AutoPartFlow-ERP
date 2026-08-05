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
    tax_number         VARCHAR(50)  DEFAULT NULL,
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
    tax_rate        DECIMAL(5,2)  NOT NULL DEFAULT 0.00 COMMENT 'VAT/GST percentage',
    warranty_months TINYINT UNSIGNED DEFAULT 0,
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
    tax_amount      DECIMAL(14,2) NOT NULL DEFAULT 0.00,
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
    tax_rate          DECIMAL(5,2) NOT NULL DEFAULT 0.00,
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
    tax_amount      DECIMAL(14,2) NOT NULL DEFAULT 0.00,
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
    tax_rate        DECIMAL(5,2)  NOT NULL DEFAULT 0.00,
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
    tax_amount      DECIMAL(14,2) NOT NULL DEFAULT 0.00,
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
    tax_rate        DECIMAL(5,2)  NOT NULL DEFAULT 0.00,
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
-- SEED DATA
-- =============================================================================

-- Roles (RBAC)
INSERT INTO roles (name, slug, description, permissions) VALUES
('Business Owner',   'owner',         'Full system access',                    '{"all": true}'),
('Sales Representative', 'sales_rep', 'Mobile POS, orders, customers',       '{"sales": true, "customers": true, "orders": true, "pos": true}'),
('Store Manager',    'store_manager', 'Inventory, purchasing, products',     '{"inventory": true, "purchasing": true, "products": true}'),
('Shop Customer',    'shop_customer', 'B2B portal: catalog, orders',         '{"catalog": true, "orders": true}');

-- Auto-number sequences
INSERT INTO sequences (seq_type, prefix, current_value, pad_length) VALUES
('invoice',        'INV-', 0, 6),
('product_code',   'PRD-', 0, 5),
('purchase_order', 'PO-',  0, 5),
('order',          'ORD-', 0, 5),
('sale',           'SL-',  0, 5),
('payment',        'PAY-', 0, 5),
('delivery',       'DLV-', 0, 5),
('return',         'RET-', 0, 5);

-- Business settings
INSERT INTO settings (setting_key, setting_value, setting_group, description) VALUES
('business_name',    'SmartAuto Spare Parts', 'general',  'Company display name'),
('business_address', '123 Industrial Zone, Colombo', 'general', 'Business address'),
('business_phone',   '+94 11 234 5678', 'general',  'Main contact number'),
('business_email',   'info@smartauto.lk', 'general',  'Main contact email'),
('tax_rate',         '18.00', 'tax',      'Default VAT/GST percentage'),
('currency',         'LKR', 'general',  'Currency code'),
('currency_symbol',  'Rs.', 'general',  'Currency display symbol'),
('invoice_footer',   'Thank you for your business!', 'invoice', 'Invoice footer text'),
('low_stock_alert',  '1', 'inventory', 'Enable low stock notifications');

-- Default admin user (password: admin123 — change immediately in production)
INSERT INTO users (role_id, username, email, password_hash, full_name, phone) VALUES
(1, 'admin', 'admin@smartauto.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', '+94 77 000 0001');

INSERT INTO employees (user_id, employee_code, designation, department, hire_date, commission_rate) VALUES
(1, 'EMP-001', 'Owner / Administrator', 'admin', CURDATE(), 0.00);

-- Product categories
INSERT INTO categories (name, slug, description) VALUES
('Brakes',       'brakes',       'Brake pads, discs, calipers, fluids'),
('Filters',      'filters',      'Oil, air, fuel, cabin filters'),
('Engine Parts', 'engine-parts', 'Pistons, gaskets, belts, pumps'),
('Electrical',   'electrical',   'Alternators, starters, sensors, bulbs'),
('Suspension',   'suspension',   'Shocks, struts, bushings, springs'),
('Body Parts',   'body-parts',   'Bumpers, mirrors, fenders, lights'),
('Fluids',       'fluids',       'Engine oil, coolant, brake fluid'),
('Ignition',     'ignition',     'Spark plugs, coils, distributors');

-- Product brands (aftermarket manufacturers)
INSERT INTO brands (name, slug, country) VALUES
('Bosch',    'bosch',    'Germany'),
('NGK',      'ngk',      'Japan'),
('Denso',    'denso',    'Japan'),
('Brembo',   'brembo',   'Italy'),
('Mann',     'mann',     'Germany'),
('KYB',      'kyb',      'Japan'),
('Gates',    'gates',    'USA'),
('Exide',    'exide',    'India');

-- Vehicle brands
INSERT INTO vehicle_brands (name, country) VALUES
('Toyota',  'Japan'),
('Honda',   'Japan'),
('Nissan',  'Japan'),
('Suzuki',  'Japan'),
('Mitsubishi', 'Japan'),
('Hyundai', 'South Korea'),
('Kia',     'South Korea'),
('BMW',     'Germany');

-- Vehicle models
INSERT INTO vehicle_models (vehicle_brand_id, name, body_type) VALUES
(1, 'Corolla',  'sedan'),
(1, 'Hilux',    'pickup'),
(1, 'RAV4',     'suv'),
(2, 'Civic',    'sedan'),
(2, 'Fit',      'hatchback'),
(2, 'CR-V',     'suv'),
(3, 'Sunny',    'sedan'),
(3, 'Navara',   'pickup'),
(4, 'Alto',     'hatchback'),
(4, 'Wagon R',  'hatchback'),
(6, 'Elantra',  'sedan'),
(6, 'Tucson',   'suv');

-- Vehicle engines
INSERT INTO vehicle_engines (vehicle_model_id, engine_code, displacement_cc, fuel_type, transmission, year_from, year_to) VALUES
(1,  '1NZ-FE', 1497, 'petrol', 'automatic', 2014, 2019),
(1,  '2NR-FKE', 1496, 'petrol', 'cvt',       2020, NULL),
(2,  '2GD-FTV', 2393, 'diesel', 'manual',    2016, NULL),
(3,  '3ZR-FAE', 1987, 'petrol', 'cvt',       2015, NULL),
(4,  'R18Z',    1799, 'petrol', 'manual',    2012, 2016),
(4,  'L15B',    1498, 'petrol', 'cvt',       2017, NULL),
(5,  'L15A',    1496, 'petrol', 'cvt',       2014, 2020),
(6,  'K24W',    2356, 'petrol', 'cvt',       2015, NULL),
(7,  'HR16DE',  1598, 'petrol', 'manual',    2012, 2018),
(9,  'K10B',     998, 'petrol', 'manual',    2014, NULL),
(11, 'Nu MPI',  1999, 'petrol', 'automatic', 2016, NULL),
(12, 'Theta II',1998, 'petrol', 'automatic', 2015, NULL);

-- Sample products
INSERT INTO products (product_code, barcode, name, description, category_id, brand_id, cost_price, selling_price, wholesale_price, tax_rate, warranty_months) VALUES
('PRD-00001', '8901234567001', 'Front Brake Pad Set - Corolla',    'Ceramic brake pads for Toyota Corolla 2014-2019', 1, 4, 3200.00, 4500.00, 4000.00, 18.00, 6),
('PRD-00002', '8901234567002', 'Oil Filter - Universal',           'Spin-on oil filter compatible with most Japanese cars', 2, 6, 450.00, 750.00, 650.00, 18.00, 0),
('PRD-00003', '8901234567003', 'Spark Plug Iridium (Set of 4)',    'NGK iridium spark plugs set', 8, 2, 2800.00, 4200.00, 3800.00, 18.00, 12),
('PRD-00004', '8901234567004', 'Alternator 90A - Honda Civic',     'Remanufactured alternator for Honda Civic R18', 4, 1, 12500.00, 18900.00, 17000.00, 18.00, 12),
('PRD-00005', '8901234567005', 'Front Shock Absorber - Hilux',     'Gas-filled front shock for Toyota Hilux 2016+', 5, 6, 5800.00, 8500.00, 7800.00, 18.00, 12),
('PRD-00006', '8901234567006', 'Air Filter - Suzuki Alto',         'Panel air filter for Suzuki Alto K10', 2, 6, 350.00, 600.00, 520.00, 18.00, 0),
('PRD-00007', '8901234567007', 'Timing Belt Kit - Hyundai Elantra','Timing belt with tensioner and water pump', 3, 7, 8200.00, 12500.00, 11000.00, 18.00, 12),
('PRD-00008', '8901234567008', 'Engine Oil 5W-30 (4L)',            'Semi-synthetic engine oil 4 litre pack', 7, 1, 3200.00, 4800.00, 4400.00, 18.00, 0);

UPDATE sequences SET current_value = 8 WHERE seq_type = 'product_code';

-- Inventory for all products
INSERT INTO inventory (product_id, quantity_on_hand, reorder_level, reorder_quantity) VALUES
(1, 85,  15, 50),
(2, 320, 30, 100),
(3, 48,  20, 50),
(4, 12,  5,  10),
(5, 36,  10, 20),
(6, 200, 25, 80),
(7, 18,  8,  15),
(8, 95,  20, 50);

-- Product-vehicle compatibility
INSERT INTO product_compatibility (product_id, vehicle_brand_id, vehicle_model_id, vehicle_engine_id, year_from, year_to, notes) VALUES
(1, 1, 1, 1, 2014, 2019, 'Direct fit for 1NZ-FE engine'),
(1, 1, 1, 2, 2020, NULL, 'Direct fit for 2NR-FKE engine'),
(3, 2, 4, 5, 2012, 2016, 'R18Z engine spark plugs'),
(3, 2, 4, 6, 2017, NULL, 'L15B turbo engine spark plugs'),
(4, 2, 4, 5, 2012, 2016, 'Honda Civic R18 alternator'),
(5, 1, 2, 3, 2016, NULL, 'Hilux 2GD diesel front shock'),
(6, 4, 9, 10, 2014, NULL, 'Suzuki Alto K10 air filter'),
(7, 6, 11, 11, 2016, NULL, 'Hyundai Elantra timing kit');

-- Suppliers
INSERT INTO suppliers (supplier_code, company_name, contact_person, phone, email, city, payment_terms) VALUES
('SUP-001', 'Lanka Auto Imports Pvt Ltd',  'Mr. Perera',  '+94 11 555 1001', 'orders@lankaauto.lk',  'Colombo', 'Net 30 days'),
('SUP-002', 'Japan Parts Wholesale',       'Mr. Tanaka',  '+94 11 555 1002', 'sales@jpparts.lk',     'Kelaniya', 'Net 45 days'),
('SUP-003', 'Ceylon Motor Supplies',       'Ms. Fernando','+94 11 555 1003', 'info@ceylonmotor.lk',  'Negombo', 'Net 30 days');

INSERT INTO supplier_products (supplier_id, product_id, supplier_sku, cost_price, lead_time_days, is_preferred) VALUES
(1, 1, 'LA-BRK-COR-001', 3200.00, 7,  1),
(1, 5, 'LA-SHK-HIL-001', 5800.00, 10, 1),
(2, 2, 'JP-FLT-UNI-001', 450.00,  5,  1),
(2, 3, 'JP-SPK-NGK-004', 2800.00, 7,  1),
(2, 4, 'JP-ALT-CIV-90A', 12500.00,14, 1),
(3, 6, 'CM-FLT-ALT-001', 350.00,  3,  1),
(3, 7, 'CM-TBK-ELN-001', 8200.00, 10, 1),
(3, 8, 'CM-OIL-5W30-4L', 3200.00, 3,  1);

-- Sample B2B shop customers
INSERT INTO customers (customer_code, customer_type, name, contact_person, phone, email, address, city) VALUES
('CUS-00001', 'shop', 'City Auto Works',       'Mr. Silva',   '+94 77 111 0001', 'cityauto@gmail.com',    '45 Galle Road, Dehiwala',     'Dehiwala'),
('CUS-00002', 'shop', 'Highway Garage & Parts', 'Mr. Jayawardena', '+94 77 111 0002', 'highway@garage.lk', 'Km 12, Colombo-Kandy Road', 'Kadawatha'),
('CUS-00003', 'shop', 'Nuwara Motors',          'Mr. Kumara',  '+94 77 111 0003', 'nuwara@motors.lk',      'Main Street, Nuwara Eliya',   'Nuwara Eliya'),
('CUS-00004', 'walking', 'Walk-in Customer',    NULL,          NULL,              NULL,                    NULL,                          NULL);

INSERT INTO shops (customer_id, shop_name, credit_limit, payment_terms_days, assigned_rep_id) VALUES
(1, 'City Auto Works',       500000.00, 30, NULL),
(2, 'Highway Garage & Parts',750000.00, 45, NULL),
(3, 'Nuwara Motors',         300000.00, 30, NULL);

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
    SUM(s.tax_amount) AS total_tax,
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
