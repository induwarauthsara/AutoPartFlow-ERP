# AutoPartFlow ERP

Pure **HTML, CSS, JavaScript, PHP, and MySQL** MVC application — no frameworks or libraries.

## Project Structure

```
AutoPartFlow-ERP/
├── app/
│   ├── bootstrap.php          # Autoloader & helper functions
│   ├── Core/
│   │   ├── App.php            # Application bootstrap & routes
│   │   ├── Router.php         # URL routing
│   │   ├── Controller.php     # Base controller
│   │   ├── Model.php          # Base model (PDO)
│   │   └── Database.php       # Database connection
│   ├── Controllers/           # Request handlers (C in MVC)
│   ├── Models/                # Data layer (M in MVC)
│   └── Views/                 # HTML templates (V in MVC)
│       ├── layouts/
│       ├── home/
│       ├── parts/
│       └── errors/
├── config/
│   ├── app.php                # App settings
│   └── database.php           # MySQL credentials
├── database/
│   └── schema.sql             # Database setup script
├── public/                    # Web document root
│   ├── index.php              # Front controller (entry point)
│   ├── .htaccess              # URL rewriting
│   └── assets/
│       ├── css/
│       └── js/
└── .htaccess                  # Redirect to public/
```

## MVC Flow

```
Browser Request
      ↓
public/index.php  (Front Controller)
      ↓
App → Router      (Match URL to Controller@method)
      ↓
Controller        (Handle request, call Model)
      ↓
Model             (Database queries via PDO)
      ↓
View              (Render HTML with data)
      ↓
Browser Response
```

## Setup

### 1. Database

```sql
-- Import schema
mysql -u root -p < database/schema.sql
```

Or run `database/schema.sql` in phpMyAdmin / MySQL Workbench.

### 2. Configuration

Edit `config/database.php` with your MySQL credentials:

```php
'host'     => '127.0.0.1',
'dbname'   => 'autopartflow_erp',
'username' => 'root',
'password' => '',
```

Edit `config/app.php` and set `base_url` to match your local path:

```php
'base_url' => '/AutoPartFlow-ERP/public/',
```

### 3. Web Server

**Apache (XAMPP/WAMP):** Place the project in your web root. Ensure `mod_rewrite` is enabled.

**PHP Built-in Server** (development only):

```bash
cd public
php -S localhost:8000
```

Then set `base_url` to `'/'` in `config/app.php`.

## Adding a New Module

1. **Model** — Create `app/Models/YourModel.php` extending `App\Core\Model`
2. **Controller** — Create `app/Controllers/YourController.php` extending `App\Core\Controller`
3. **Views** — Add templates in `app/Views/yourmodule/`
4. **Routes** — Register in `app/Core/App.php`:

```php
$this->router->get('/your-route', 'YourController@index');
$this->router->post('/your-route/store', 'YourController@store');
```

## Features Included

- Front controller pattern
- Custom router with `{param}` support
- PDO database layer with prepared statements
- Layout-based view rendering
- CSRF protection on POST forms
- Flash messages
- XSS escaping via `e()` helper
- Sample Parts CRUD module

## Requirements

- PHP 8.1+
- MySQL 5.7+ / MariaDB 10.3+
- Apache with mod_rewrite (or nginx equivalent)
