<?php
$unreadCount = 0;
if (isset($db) && !empty($currentUser['user_id'])) {
    $row = $db->selectOne(
        "SELECT COUNT(*) AS c FROM notifications WHERE is_read = 0 AND (user_id IS NULL OR user_id = ?)",
        'i',
        [$currentUser['user_id']]
    );
    $unreadCount = (int) ($row['c'] ?? 0);
}
?>
<header class="topbar">
    <div class="search-box">
        <?= icon('search') ?>
        <input type="text" placeholder="Search inventory, orders, customers...">
    </div>
    <div class="topbar-icons">
        <a class="icon-btn" href="/views/notifications.php" title="Notifications">
            <?= icon('bell') ?>
            <?php if ($unreadCount > 0): ?><span class="dot"></span><?php endif; ?>
        </a>
        <span class="icon-btn" title="Calendar"><?= icon('calendar') ?></span>
        <?php
        $initials = '';
        if (!empty($currentUser['full_name'])) {
            $parts = explode(' ', trim($currentUser['full_name']));
            $initials = strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1] ?? '', 0, 1));
        }
        ?>
        <div class="avatar"><?= htmlspecialchars($initials ?: 'U') ?></div>
    </div>
</header>
