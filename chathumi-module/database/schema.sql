-- ============================================================
-- SmartAuto ERP (AutoPartFlow) - Administration & BI Module
-- Member: Chathumi | Tables: users, roles, employees,
--         notifications, activity_logs, settings
-- ============================================================

CREATE DATABASE IF NOT EXISTS smartauto_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smartauto_erp;

-- ------------------------------------------------------------
-- ROLES
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,       -- Administrator, Owner, Store Manager, Sales Rep, Warehouse Staff
    description VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- USERS  (login accounts / system access)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    status ENUM('active','suspended','inactive') NOT NULL DEFAULT 'active',
    remember_token VARCHAR(255) DEFAULT NULL,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_token_expires DATETIME DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    last_login_ip VARCHAR(45) DEFAULT NULL,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,     -- soft delete
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(role_id)
);

-- ------------------------------------------------------------
-- EMPLOYEES  (extends users with HR / performance data)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS employees (
    employee_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    employee_code VARCHAR(20) NOT NULL UNIQUE,     -- e.g. EMP-1042
    department VARCHAR(50) DEFAULT NULL,           -- Sales, Logistics, Warehouse...
    designation VARCHAR(80) DEFAULT NULL,          -- Senior Sales Rep, Logistics Coordinator...
    phone VARCHAR(20) DEFAULT NULL,
    hire_date DATE DEFAULT NULL,
    sales_target DECIMAL(12,2) DEFAULT 0,
    commission_rate DECIMAL(5,2) DEFAULT 0,
    performance_score DECIMAL(5,2) DEFAULT 0,      -- 0-100
    attendance_status ENUM('present','absent','late','on_leave') DEFAULT 'present',
    employment_status ENUM('active','on_leave','suspended','terminated') NOT NULL DEFAULT 'active',
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- ------------------------------------------------------------
-- ATTENDANCE (supports "Today's Attendance" widget)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS attendance_logs (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    log_date DATE NOT NULL,
    status ENUM('present','absent','late','on_leave') NOT NULL DEFAULT 'present',
    check_in TIME DEFAULT NULL,
    check_out TIME DEFAULT NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    UNIQUE KEY uniq_emp_day (employee_id, log_date)
);

-- ------------------------------------------------------------
-- NOTIFICATIONS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,                      -- NULL = broadcast to all owners/admins
    type ENUM('low_stock','order','payment','purchase','system') NOT NULL DEFAULT 'system',
    severity ENUM('critical','warning','info') NOT NULL DEFAULT 'info',
    title VARCHAR(150) NOT NULL,
    message VARCHAR(500) NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- ------------------------------------------------------------
-- ACTIVITY LOGS (audit trail)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS activity_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    action VARCHAR(150) NOT NULL,                  -- e.g. "Changed permissions for role Warehouse Staff"
    module VARCHAR(50) DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    status ENUM('success','failed') NOT NULL DEFAULT 'success',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- ------------------------------------------------------------
-- SETTINGS (single-row / key-value business configuration)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_group VARCHAR(50) DEFAULT 'general',   -- business_info, invoice, tax, backup, preferences
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- Read-only helper views (data owned by other members' modules,
-- referenced here only for dashboard/report widgets in dev/demo)
-- ------------------------------------------------------------
-- These light-weight tables let this module run standalone for
-- development/demo. In the integrated system they are owned by
-- Member 1 (sales) and Member 2 (inventory) respectively.
CREATE TABLE IF NOT EXISTS demo_sales (
    sale_id INT AUTO_INCREMENT PRIMARY KEY,
    sale_date DATE NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    category VARCHAR(50) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS demo_inventory_alerts (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(100) NOT NULL,
    sku VARCHAR(30) NOT NULL,
    brand VARCHAR(50) DEFAULT NULL,
    stock_qty INT NOT NULL DEFAULT 0,
    min_qty INT NOT NULL DEFAULT 10,
    status ENUM('critical','low','ok') NOT NULL DEFAULT 'ok'
);

-- ------------------------------------------------------------
-- SEED DATA
-- ------------------------------------------------------------
INSERT INTO roles (role_name, description) VALUES
 ('Administrator','Full system access'),
 ('Owner','Business owner - dashboards & reports'),
 ('Store Manager','Inventory & procurement'),
 ('Sales Rep','Mobile POS & customer orders'),
 ('Warehouse Staff','Stock handling');

-- NOTE: the default admin login is NOT inserted here because MySQL cannot
-- generate PHP's password_hash() bcrypt hashes. Run database/seed.php once
-- (php database/seed.php) after importing this file — it creates:
--   email: admin@autopartflow.com   password: Admin@123

INSERT INTO settings (setting_key, setting_value, setting_group) VALUES
('company_name', 'AutoPartFlow Inc.', 'business_info'),
('contact_email', 'admin@autopartflow.com', 'business_info'),
('business_phone', '+1 (555) 123-4567', 'business_info'),
('primary_address', '1234 Logistics Way, Suite 100, Detroit, MI 48201, United States', 'business_info'),
('company_logo', '', 'business_info'),
('currency', 'USD', 'preferences'),
('tax_rate', '8.5', 'tax_config'),
('invoice_prefix', 'INV-', 'invoice_settings');

-- Demo sales figures (7 days) so the Dashboard/Reports charts have data
INSERT INTO demo_sales (sale_date, amount, category) VALUES
(CURDATE() - INTERVAL 6 DAY, 4200, 'Brake Systems'),
(CURDATE() - INTERVAL 5 DAY, 5100, 'Engine Components'),
(CURDATE() - INTERVAL 4 DAY, 3900, 'Suspension'),
(CURDATE() - INTERVAL 3 DAY, 6200, 'Brake Systems'),
(CURDATE() - INTERVAL 2 DAY, 7300, 'Filters & Fluids'),
(CURDATE() - INTERVAL 1 DAY, 8100, 'Engine Components'),
(CURDATE(), 6700, 'Brake Systems');

-- Demo low-stock alerts
INSERT INTO demo_inventory_alerts (item_name, sku, brand, stock_qty, min_qty, status) VALUES
('Ceramic Brake Pads (Front)', 'BP-CER-1042', 'Bosch', 4, 15, 'critical'),
('Synthetic Motor Oil 5W-30', 'OIL-SYN-5W30', 'Mobil 1', 12, 20, 'low'),
('Air Filter Element', 'AF-STD-998', 'K&N', 15, 20, 'low'),
('Spark Plugs (Platinum)', 'SP-PLT-04', 'NGK', 2, 10, 'critical');

-- Sample notifications
INSERT INTO notifications (user_id, type, severity, title, message) VALUES
(NULL, 'low_stock', 'critical', 'Low Stock Alert: Brake Pads (Front)', "Inventory for SKU-BP-104 has dropped below the minimum threshold (Current: 4, Min: 15)."),
(NULL, 'payment', 'critical', 'Payment Failed: Order #8921', "Credit card authorization failed for B2B client 'Apex Motors'. Order status set to On Hold."),
(NULL, 'purchase', 'info', 'Purchase Arrival: PO-4099', "Shipment from supplier 'Global Auto Parts' has arrived at Warehouse A and is pending check-in."),
(NULL, 'order', 'info', 'New Order Received: #8925', "New order placed by 'Midwest Garages' for 12 items. Total: $1,450.00.");

-- NOTE: sample employees can be added after seed.php creates their linked
-- user accounts, e.g.:
--   INSERT INTO users (full_name, email, password_hash, role_id) VALUES ('Sarah Jenkins','sarah@autopartflow.com', <bcrypt hash>, 4);
--   INSERT INTO employees (user_id, employee_code, designation, department, performance_score, sales_target, employment_status)
--   VALUES (LAST_INSERT_ID(), 'EMP-1042', 'Senior Sales Rep', 'Sales', 95, 425000, 'active');
