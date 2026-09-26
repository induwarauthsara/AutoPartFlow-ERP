<?php

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function asset(string $path): string
{
    return rtrim(BASE_URL, '/') . '/assets/' . ltrim($path, '/');
}

function url(string $path = ''): string
{
    $trimmedPath = ltrim($path, '/');
    return rtrim(BASE_URL, '/') . '/' . $trimmedPath;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): bool
{
    $token = (string) ($_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
    if ($token === '' || empty($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals((string) $_SESSION['csrf_token'], $token);
}

function auth_check(): bool
{
    return !empty($_SESSION['user_id']);
}

function auth_role(): ?string
{
    return $_SESSION['role_slug'] ?? null;
}

function auth_dashboard_url(): string
{
    $role = auth_role();
    return match ($role) {
        'owner' => url('admin/dashboard'),
        'sales_rep' => url('sales'),
        'store_manager' => url('inventory'),
        'shop_customer' => url('customer/dashboard'),
        default => url('login'),
    };
}

function auth_dashboard_label(): string
{
    $role = auth_role();
    return match ($role) {
        'owner' => 'Admin Dashboard',
        'sales_rep' => 'Sales Dashboard',
        'store_manager' => 'Store Dashboard',
        'shop_customer' => 'Customer Dashboard',
        default => 'Dashboard',
    };
}
