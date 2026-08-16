<?php
/**
 * Application-wide constants for the Administration & BI module.
 */
define('APP_NAME', 'AutoPartFlow');
define('BASE_URL', '/'); // change if the app lives in a subfolder, e.g. '/smartauto-erp/'
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', BASE_URL . 'uploads/');

// Role IDs — must match the `roles` table seed order
define('ROLE_ADMIN', 1);
define('ROLE_OWNER', 2);
define('ROLE_STORE_MANAGER', 3);
define('ROLE_SALES_REP', 4);
define('ROLE_WAREHOUSE_STAFF', 5);

// Roles allowed into this module's pages (Admin/Owner area)
define('ADMIN_MODULE_ROLES', [ROLE_ADMIN, ROLE_OWNER]);

// Password reset link validity (minutes)
define('RESET_TOKEN_TTL_MINUTES', 30);

// "Remember me" cookie validity (days)
define('REMEMBER_ME_DAYS', 30);
