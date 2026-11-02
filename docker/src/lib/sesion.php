<?php
ini_set('session.use_strict_mode', '1');
session_name('JUKEBOXSESSID');

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

function verificarCSRF(): void {
    $tokenRecibido = $_POST['csrf'] ?? '';
    if (!is_string($tokenRecibido) || !hash_equals($_SESSION['csrf'], $tokenRecibido)) {
        http_response_code(403);
        exit('Error 403: Token CSRF no válido o ausente.');
    }
}