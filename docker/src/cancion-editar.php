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

$errores = [];
$title = $cancion['title'];
$artist = $cancion['artist'];
$category = $cancion['category'];
$duration = $cancion['duration'];
$src = $cancion['src'];

$categoriasPermitidas = ['electronica', 'rock', 'bandas-sonoras'];
$audiosPermitidos = ['audio/demo-01.mp3', 'audio/demo-02.mp3'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCSRF();

    $titleInput = trim($_POST['title'] ?? '');
    $artistInput = trim($_POST['artist'] ?? '');
    $categoryInput = $_POST['category'] ?? '';
    $durationInput = $_POST['duration'] ?? '';
    $srcInput = $_POST['src'] ?? '';

    if ($titleInput === '' || mb_strlen($titleInput) > 80) {
        $errores[] = 'El título es obligatorio.';
    } else {
        $title = $titleInput;
    }

    if ($artistInput === '' || mb_strlen($artistInput) > 80) {
        $errores[] = 'El artista es obligatorio.';
    } else {
        $artist = $artistInput;
    }

    if (!in_array($categoryInput, $categoriasPermitidas, true)) {
        $errores[] = 'Categoría no válida.';
    } else {
        $category = $categoryInput;
    }

    $durValida = filter_var($durationInput, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 1800]]);
    if ($durValida === false) {
        $errores[] = 'Duración no válida.';
    } else {
        $duration = $durValida;
    }

    if (!in_array($srcInput, $audiosPermitidos, true)) {
        $errores[] = 'Audio no permitido.';
    } else {
        $src = $srcInput;
    }

    if (empty($errores)) {
        $ok = actualizarJSON(RUTA_CANCIONES_JSON, function(array $lista) use ($id, $title, $artist, $category, $duration, $src) {
            $found = false;
            foreach ($lista as $pos => $item) {
                if (isset($item['id']) && $item['id'] === $id) {
                    $lista[$pos]['title'] = $title;
                    $lista[$pos]['artist'] = $artist;
                    $lista[$pos]['category'] = $category;
                    $lista[$pos]['duration'] = $duration;
                    $lista[$pos]['src'] = $src;
                    $found = true;
                    break;
                }
            }
            return $found ? $lista : false;
        });

        if ($ok) {
            header('Location: canciones.php', true, 303);
            exit;
        } else {
            $errores[] = 'Error al actualizar.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Canción - Jukebox</title>
    <style>
        body { font-family: sans-serif; margin: 20px; background: #f9f9f9; }
        .box { max-width: 400px; margin: auto; padding: 15px; background: #fff; border: 1px solid #ccc; }
        .error { color: red; margin-bottom: 10px; font-size: 0.9em; }
        .field { margin-bottom: 10px; }
        label { display: block; font-weight: bold; }
        input, select { width: 100%; padding: 5px; box-sizing: border-box; }
        button { padding: 8px; background: #007bff; color: white; border: none; cursor: pointer; width: 100%; }
    </style>
</head>
<body>
<div class="box">
    <h3>Editar Canción</h3>

    <?php if (!empty($errores)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errores as $e): ?>
                    <li><?= htmlspecialchars($e, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="cancion-editar.php?id=<?= $id ?>">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <input type="hidden" name="id" value="<?= $id ?>">

        <div class="field">
            <label>Título:</label>
            <input type="text" name="title" value="<?= htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>
        </div>

        <div class="field">
            <label>Artista:</label>
            <input type="text" name="artist" value="<?= htmlspecialchars($artist, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>
        </div>

        <div class="field">
            <label>Categoría:</label>
            <select name="category">
                <?php foreach ($categoriasPermitidas as $cat): ?>
                    <option value="<?= $cat ?>" <?= $category === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label>Duración (segundos):</label>
            <input type="number" name="duration" value="<?= (int)$duration ?>" required>
        </div>

        <div class="field">
            <label>Audio:</label>
            <select name="src">
                <?php foreach ($audiosPermitidos as $a): ?>
                    <option value="<?= $a ?>" <?= $src === $a ? 'selected' : '' ?>><?= $a ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit">Actualizar</button>
    </form>
    <p><a href="canciones.php">Volver</a></p>
</div>
</body>
</html>