<?php
/**
 * Expects: $activePage (string) and $currentUser (assoc array) to be set
 * by the including page before this partial is required.
 */
$initials = '';
if (!empty($currentUser['full_name'])) {
    $parts = explode(' ', trim($currentUser['full_name']));
    $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1] ?? '', 0, 1));
}
$navLink = function (string $key, string $iconName, string $label, string $href) use ($activePage) {
    $active = $activePage === $key ? ' active' : '';
    echo '<a class="nav-link' . $active . '" href="' . $href . '">' . icon($iconName) . '<span>' . $label . '</span></a>';
};
?>
<aside class="sidebar">
    <div class="brand">
        <div class="brand-mark">AP</div>
        <div class="brand-text">
            <div class="brand-title"><?= APP_NAME ?></div>
            <div class="brand-sub">Logistics ERP</div>
        </div>
    </div>

    <nav>
        <div class="nav-group">
            <div class="nav-group-label">Core</div>
            <?php $navLink('dashboard', 'dashboard', 'Dashboard', '/views/dashboard.php'); ?>
            <?php $navLink('reports', 'reports', 'Reports', '/views/reports.php'); ?>
        </div>

        <div class="nav-group">
            <div class="nav-group-label">Administration</div>
            <?php $navLink('users', 'shield', 'User Management', '/views/users.php'); ?>
            <?php $navLink('employees', 'employees', 'Employees', '/views/employees.php'); ?>
            <?php $navLink('notifications', 'bell', 'Notifications', '/views/notifications.php'); ?>
            <?php $navLink('settings', 'settings', 'Settings', '/views/settings.php'); ?>
        </div>
    </nav>

    <div class="sidebar-footer">
        <?php if (!empty($currentUser['profile_image'])): ?>
            <img class="avatar" src="<?= htmlspecialchars($currentUser['profile_image']) ?>" alt="">
        <?php else: ?>
            <div class="avatar"><?= htmlspecialchars($initials ?: 'U') ?></div>
        <?php endif; ?>
        <div class="who">
            <div class="name"><?= htmlspecialchars($currentUser['full_name'] ?? 'User') ?></div>
            <div class="role"><?= htmlspecialchars($currentUser['role_name'] ?? '') ?></div>
        </div>
        <a class="logout-btn" href="/logout.php" title="Log out"><?= icon('logout') ?></a>
    </div>
</aside>
