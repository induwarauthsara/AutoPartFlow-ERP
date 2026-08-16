# AutoPartFlow — Administration & Business Intelligence Module

This is **Chathumi's part** of the SmartAuto ERP / AutoPartFlow group project
(Member 3 — Administration & Business Intelligence).

Covers: Authentication (login, forgot/reset password), User Management,
Employee Management, Owner Dashboard, Reports & Analytics, Notification
Center, and System Settings — with an activity/audit log running underneath
all of it.

Built with **no frameworks and no external libraries** (PHP 8+, vanilla
JS/CSS, MySQL) per the course's project rules — including the charts, which
are hand-drawn on `<canvas>` instead of using Chart.js.

## 1. Requirements
- PHP 8.0+ with the `mysqli` extension
- MySQL / MariaDB
- Any local server stack (XAMPP / WAMP / Laragon / `php -S`)

## 2. Setup

1. **Create the database**
   Import the schema (creates the DB, tables, roles, settings, and demo
   data for the dashboard/report charts):
   ```
   mysql -u root -p < database/schema.sql
   ```

2. **Configure the DB connection**
   Edit `config/database.php` with your MySQL username/password.

3. **Create the default admin login**
   MySQL can't generate PHP's `password_hash()` output, so run this once:
   ```
   php database/seed.php
   ```
   This creates:
   - email: `admin@autopartflow.com`
   - password: `Admin@123`

   Change the password after first login (via Forgot Password, or add an
   "Edit Profile" flow when you integrate this with the rest of the team's
   modules).

4. **Run the app**
   From the project root:
   ```
   php -S localhost:8000
   ```
   Then open `http://localhost:8000/login.php`

## 3. Folder structure

```
config/      → DB connection, constants, session bootstrap
core/        → Database wrapper, Auth (RBAC + activity logging), Session,
               Validator, inline SVG icon helper
models/      → User, Employee, Notification, ActivityLog, Setting
views/       → dashboard.php, reports.php, users.php, employees.php,
               notifications.php, settings.php + shared sidebar/topbar partials
public/      → css/style.css, js/charts.js (hand-rolled canvas charts)
database/    → schema.sql, seed.php
login.php, forgot_password.php, reset_password.php, logout.php, index.php
```

## 4. Forgot Password flow

Since the project forbids external APIs, there's no SMS/email gateway.
`forgot_password.php` generates a secure, time-limited (30 min) reset token
and — for this dev build — shows the reset link directly on screen instead
of emailing it, so you can test the whole flow end-to-end. Swap that part
out for `mail()`/SMTP once you're ready to wire up a mail server.

## 5. Security notes already built in
- Passwords hashed with `password_hash()` / verified with `password_verify()`
- All queries use `mysqli` prepared statements (no string-built SQL)
- CSRF tokens on every form (login, forgot/reset password, add user, settings)
- Role-based access guard (`Auth::requireRole()`) on every page in this module
- Soft delete on `users` (`is_deleted` flag, never a hard `DELETE`)
- Full activity/audit logging (`activity_logs` table) — logins, failed logins,
  password resets, user creation, status changes, settings updates

## 6. Integrating with your teammates' modules

This module currently reads two small **demo** tables (`demo_sales`,
`demo_inventory_alerts`) to populate the dashboard/report charts and the
low-stock widget, standing in for Member 1's `sales`/`sale_items` and
Member 2's `inventory` tables. When you merge codebases, just point the
queries in `views/dashboard.php` and `views/reports.php` at their real
tables and drop the `demo_*` tables.

## 7. Default login
| Email | Password |
|---|---|
| admin@autopartflow.com | Admin@123 |
