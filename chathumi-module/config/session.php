<?php
/**
 * Central session bootstrap. Include this before any output.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,   // JS can't read the cookie -> mitigates XSS session theft
        'samesite' => 'Lax',
    ]);
    session_start();
}
