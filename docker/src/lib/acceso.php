<?php
require_once __DIR__ . '/sesion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php', true, 303);
    exit;
}