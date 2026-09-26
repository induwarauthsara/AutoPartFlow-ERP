<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = [], ?string $layout = 'main'): void
    {
        extract($data, EXTR_SKIP);

        $viewPath = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        if ($layout !== null) {
            $layoutPath = APP_PATH . '/Views/layouts/' . $layout . '.php';

            if (!file_exists($layoutPath)) {
                throw new \RuntimeException("Layout not found: {$layout}");
            }

            require $layoutPath;
            return;
        }

        echo $content;
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . BASE_URL . ltrim($url, '/'));
        exit;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    protected function getFlash(): ?array
    {
        if (!isset($_SESSION['flash'])) {
            return null;
        }

        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);

        return $flash;
    }

    /**
     * Require authenticated session. Redirects to /login if not signed in.
     */
    protected function requireAuth(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->setFlash('error', 'Please sign in to access that page.');
            $this->redirect('/login');
            return;
        }
    }

    /**
     * Require specific role(s). If authenticated with wrong role, redirects to own dashboard.
     *
     * @param string|array $roles Single role slug or array of allowed role slugs
     */
    protected function requireRole(string|array $roles, string $deniedMessage = 'Access denied. You do not have permission to access that workspace.'): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->setFlash('error', 'Please sign in to access that page.');
            $this->redirect('/login');
            return;
        }

        $allowed = is_array($roles) ? $roles : [$roles];
        $currentRole = (string) ($_SESSION['role_slug'] ?? '');

        if (!in_array($currentRole, $allowed, true)) {
            $this->setFlash('error', $deniedMessage);
            $this->redirect($this->dashboardUrlForRole($currentRole));
            return;
        }
    }

    /**
     * Get dashboard URL for given role.
     */
    protected function dashboardUrlForRole(string|int|null $role): string
    {
        if (is_int($role)) {
            return match ($role) {
                1 => '/admin/dashboard',
                2 => '/sales',
                3 => '/inventory',
                4 => '/customer/dashboard',
                default => '/',
            };
        }

        return match ($role) {
            'owner' => '/admin/dashboard',
            'sales_rep' => '/sales',
            'store_manager' => '/inventory',
            'shop_customer' => '/customer/dashboard',
            default => '/',
        };
    }
}
