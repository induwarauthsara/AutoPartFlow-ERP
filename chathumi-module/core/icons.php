<?php
/**
 * Tiny inline-SVG icon helper — avoids pulling in an external icon
 * library (project rule: no external libraries).
 * Usage: <?= icon('dashboard') ?>
 */
function icon(string $name, string $class = 'icon'): string
{
    $stroke = 'currentColor';
    $common = 'fill="none" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"';
    $paths = [
        'dashboard' => '<rect x="3" y="3" width="7" height="9" rx="1.5" ' . $common . '/><rect x="14" y="3" width="7" height="5" rx="1.5" ' . $common . '/><rect x="14" y="12" width="7" height="9" rx="1.5" ' . $common . '/><rect x="3" y="16" width="7" height="5" rx="1.5" ' . $common . '/>',
        'reports' => '<path d="M4 20V10M10 20V4M16 20V13M22 20H2" ' . $common . '/>',
        'products' => '<path d="M21 8l-9-5-9 5 9 5 9-5z" ' . $common . '/><path d="M3 8v8l9 5 9-5V8" ' . $common . '/>',
        'inventory' => '<rect x="3" y="7" width="18" height="14" rx="1.5" ' . $common . '/><path d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2" ' . $common . '/>',
        'sales' => '<circle cx="9" cy="21" r="1.3" fill="' . $stroke . '"/><circle cx="18" cy="21" r="1.3" fill="' . $stroke . '"/><path d="M2.5 3h2.5l2.9 12.4A2 2 0 009.8 17h7.9a2 2 0 002-1.6L21.5 8H6" ' . $common . '/>',
        'customers' => '<circle cx="9" cy="8" r="3.3" ' . $common . '/><path d="M2.5 20c.7-3.5 3.4-5.5 6.5-5.5s5.8 2 6.5 5.5" ' . $common . '/><path d="M16 3.6a3.3 3.3 0 010 8.8M18.5 14.6c2.4.6 4 2.4 4.5 5.4" ' . $common . '/>',
        'employees' => '<circle cx="12" cy="8" r="3.3" ' . $common . '/><path d="M4.5 20c.9-4 3.6-6 7.5-6s6.6 2 7.5 6" ' . $common . '/>',
        'settings' => '<circle cx="12" cy="12" r="3" ' . $common . '/><path d="M19.4 13a1.7 1.7 0 00.3 1.9l.1.1a2 2 0 11-2.9 2.9l-.1-.1a1.7 1.7 0 00-1.9-.3 1.7 1.7 0 00-1 1.5V19a2 2 0 11-4 0v-.2a1.7 1.7 0 00-1-1.6 1.7 1.7 0 00-1.9.3l-.1.1a2 2 0 11-2.9-2.9l.1-.1a1.7 1.7 0 00.3-1.9 1.7 1.7 0 00-1.5-1H4a2 2 0 110-4h.2a1.7 1.7 0 001.5-1 1.7 1.7 0 00-.3-1.9l-.1-.1a2 2 0 112.9-2.9l.1.1a1.7 1.7 0 001.9.3H10a1.7 1.7 0 001-1.5V4a2 2 0 114 0v.2a1.7 1.7 0 001 1.5 1.7 1.7 0 001.9-.3l.1-.1a2 2 0 112.9 2.9l-.1.1a1.7 1.7 0 00-.3 1.9V10a1.7 1.7 0 001.5 1h.2a2 2 0 110 4h-.2a1.7 1.7 0 00-1.5 1z" ' . $common . '/>',
        'search' => '<circle cx="11" cy="11" r="7" ' . $common . '/><path d="M21 21l-4.3-4.3" ' . $common . '/>',
        'bell' => '<path d="M18 8a6 6 0 00-12 0c0 7-3 9-3 9h18s-3-2-3-9" ' . $common . '/><path d="M13.7 21a2 2 0 01-3.4 0" ' . $common . '/>',
        'calendar' => '<rect x="3" y="4.5" width="18" height="16" rx="2" ' . $common . '/><path d="M16 3v3M8 3v3M3 9.5h18" ' . $common . '/>',
        'export' => '<path d="M12 3v13M7 11l5 5 5-5" ' . $common . '/><path d="M4 20h16" ' . $common . '/>',
        'plus' => '<path d="M12 5v14M5 12h14" ' . $common . '/>',
        'eye' => '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7z" ' . $common . '/><circle cx="12" cy="12" r="3" ' . $common . '/>',
        'eye-off' => '<path d="M3 3l18 18" ' . $common . '/><path d="M10.6 5.1A10.7 10.7 0 0112 5c6 0 10 7 10 7a17.6 17.6 0 01-3.2 4M6.6 6.6C4 8.3 2 12 2 12s4 7 10 7c1.4 0 2.7-.3 3.9-.9" ' . $common . '/><path d="M9.9 9.9a3.3 3.3 0 004.2 4.2" ' . $common . '/>',
        'edit' => '<path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" ' . $common . '/><path d="M18.4 2.6a2 2 0 012.9 2.9L12 15l-4 1 1-4 9.4-9.4z" ' . $common . '/>',
        'trash' => '<path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0l-1 14a2 2 0 01-2 2H7a2 2 0 01-2-2L4 6" ' . $common . '/>',
        'lock' => '<rect x="4" y="10.5" width="16" height="10" rx="2" ' . $common . '/><path d="M7.5 10.5V7a4.5 4.5 0 019 0v3.5" ' . $common . '/>',
        'mail' => '<rect x="2.5" y="4.5" width="19" height="15" rx="2" ' . $common . '/><path d="M3 6l9 6.5L21 6" ' . $common . '/>',
        'key' => '<circle cx="8" cy="15" r="4.5" ' . $common . '/><path d="M11.5 11.5L21 2M17 6l3 3M13.5 9.5l2.5 2.5" ' . $common . '/>',
        'arrow-left' => '<path d="M19 12H5M12 19l-7-7 7-7" ' . $common . '/>',
        'check-circle' => '<circle cx="12" cy="12" r="9" ' . $common . '/><path d="M8.5 12.5l2.3 2.3L16 10" ' . $common . '/>',
        'alert-triangle' => '<path d="M12 3.5L1.5 21h21L12 3.5z" ' . $common . '/><path d="M12 9.5v5M12 17.5h.01" ' . $common . '/>',
        'info' => '<circle cx="12" cy="12" r="9" ' . $common . '/><path d="M12 11v6M12 7.5h.01" ' . $common . '/>',
        'filter' => '<path d="M4 4h16l-6.5 8v6l-3 2v-8L4 4z" ' . $common . '/>',
        'upload' => '<path d="M12 21V9M7 13l5-5 5 5" ' . $common . '/><path d="M4 21h16" ' . $common . '/>',
        'building' => '<rect x="4" y="3" width="16" height="18" rx="1" ' . $common . '/><path d="M9 8h.01M15 8h.01M9 12h.01M15 12h.01M9 16h.01M15 16h.01" ' . $common . '/>',
        'shield' => '<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6l8-3z" ' . $common . '/>',
        'download' => '<path d="M12 3v13M7 11l5 5 5-5" ' . $common . '/><path d="M4 20h16" ' . $common . '/>',
        'chevron-down' => '<path d="M6 9l6 6 6-6" ' . $common . '/>',
        'logout' => '<path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" ' . $common . '/><path d="M16 17l5-5-5-5M21 12H9" ' . $common . '/>',
    ];
    $body = $paths[$name] ?? $paths['info'];
    return '<svg class="' . $class . '" viewBox="0 0 24 24" width="17" height="17">' . $body . '</svg>';
}
