<?php
require_once __DIR__ . '/../lib/sesion.php';
require_once __DIR__ . '/../lib/almacen-json.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado. Se requiere iniciar sesión.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $canciones = leerJSON(RUTA_CANCIONES_JSON);
    echo json_encode($canciones, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al leer el catálogo de canciones.'], JSON_UNESCAPED_UNICODE);
}