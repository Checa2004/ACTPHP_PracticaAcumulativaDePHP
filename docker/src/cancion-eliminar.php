<?php
require_once __DIR__ . '/lib/acceso.php';
require_once __DIR__ . '/lib/almacen-json.php';

$id = filter_var($_GET['id'] ?? $_POST['id'] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);

if ($id === false || $id === null) {
    http_response_code(400);
    exit('ID no válido.');
}

$canciones = leerJSON(RUTA_CANCIONES_JSON);
$cancion = null;

foreach ($canciones as $c) {
    if (isset($c['id']) && $c['id'] === $id) {
        $cancion = $c;
        break;
    }
}

if (!$cancion) {
    http_response_code(404);
    exit('Canción no encontrada.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCSRF();

    $ok = actualizarJSON(RUTA_CANCIONES_JSON, function(array $lista) use ($id) {
        $nuevaLista = [];
        $found = false;

        foreach ($lista as $item) {
            if (isset($item['id']) && $item['id'] === $id) {
                $found = true;
                continue;
            }
            $nuevaLista[] = $item;
        }

        return $found ? array_values($nuevaLista) : false;
    });

    if ($ok) {
        header('Location: canciones.php', true, 303);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Eliminar Canción - Jukebox</title>
    <style>
        body { font-family: sans-serif; margin: 20px; background: #f9f9f9; }
        .box { max-width: 400px; margin: auto; padding: 15px; background: #fff; border: 1px solid #ccc; }
        button { padding: 8px 12px; background: #dc3545; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
<div class="box">
    <h3>¿Eliminar esta canción?</h3>
    <p><strong>Título:</strong> <?= htmlspecialchars($cancion['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
    <p><strong>Artista:</strong> <?= htmlspecialchars($cancion['artist'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>

    <form method="post" action="cancion-eliminar.php">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <input type="hidden" name="id" value="<?= $id ?>">
        
        <button type="submit">Sí, eliminar</button>
        <a href="canciones.php" style="margin-left: 10px;">Cancelar</a>
    </form>
</div>
</body>
</html>