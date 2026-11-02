<?php
require_once __DIR__ . '/lib/acceso.php';
require_once __DIR__ . '/lib/almacen-json.php';

$canciones = leerJSON(RUTA_CANCIONES_JSON);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo - Jukebox</title>
    <style>
        body { font-family: sans-serif; margin: 20px; background: #f9f9f9; }
        .container { max-width: 750px; margin: auto; background: #fff; padding: 20px; border: 1px solid #ccc; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #eee; }
        a.btn { text-decoration: none; padding: 4px 8px; background: #007bff; color: white; border-radius: 3px; font-size: 0.9em; }
        a.btn-del { background: #dc3545; }
        a.btn-add { background: #28a745; display: inline-block; margin-bottom: 10px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Catálogo Musical</h2>
    <p>
        <a href="home.php" class="btn">Volver al Reproductor</a>
        <a href="cancion-nueva.php" class="btn btn-add">Añadir Canción</a>
    </p>

    <?php if (empty($canciones)): ?>
        <p>Todavía no hay canciones.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Artista</th>
                    <th>Categoría</th>
                    <th>Duración</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($canciones as $c): ?>
                    <tr>
                        <td><?= (int)$c['id'] ?></td>
                        <td><?= htmlspecialchars($c['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($c['artist'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($c['category'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(duracionLegible((int)$c['duration']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td>
                            <a href="cancion-editar.php?id=<?= (int)$c['id'] ?>" class="btn">Editar</a>
                            <a href="cancion-eliminar.php?id=<?= (int)$c['id'] ?>" class="btn btn-del">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>