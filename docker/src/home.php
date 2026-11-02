<?php
require_once __DIR__ . '/lib/acceso.php';
require_once __DIR__ . '/lib/almacen-json.php';

$alias = $_SESSION['alias'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Jukebox - Inicio</title>
    <style>
        body { font-family: sans-serif; margin: 2rem; background: #f5f5f5; }
        .container { max-width: 700px; margin: auto; background: #fff; padding: 1.5rem; border: 1px solid #ccc; border-radius: 5px; }
        header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 1rem; }
        .player-box { margin-top: 1.5rem; padding: 1rem; background: #eef2f5; border-radius: 5px; text-align: center; }
        .controls { margin-top: 1rem; display: flex; justify-content: center; gap: 10px; }
        button, .btn { padding: 0.5rem 1rem; background: #337ab7; color: white; border: none; border-radius: 3px; text-decoration: none; cursor: pointer; display: inline-block; }
        .btn-red { background: #d9534f; }
        .btn-green { background: #5cb85c; }
        #track-info { font-weight: bold; font-size: 1.1rem; margin-bottom: 0.5rem; }
    </style>
</head>
<body>
<div class="container">
    <header>
        <div>
            <h2>Bienvenido, <?= htmlspecialchars($alias, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
            <p style="margin:0;"><a href="canciones.php" class="btn btn-green">Gestionar Catálogo Musical</a></p>
        </div>
        <form method="post" action="logout.php">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            <button type="submit" class="btn btn-red">Cerrar sesión</button>
        </form>
    </header>

    <div class="player-box">
        <h3>Reproductor Jukebox</h3>
        <div id="track-info">Cargando canciones...</div>
        <audio id="audio-player" controls style="width: 100%; margin-top: 0.5rem;"></audio>
        <div class="controls">
            <button id="btn-prev">Anterior</button>
            <button id="btn-next">Siguiente</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const trackInfo = document.getElementById('track-info');
    const audioPlayer = document.getElementById('audio-player');
    const btnPrev = document.getElementById('btn-prev');
    const btnNext = document.getElementById('btn-next');

    let songs = [];
    let currentIndex = 0;

    function loadTrack(index) {
        if (songs.length === 0) {
            trackInfo.textContent = 'No hay canciones disponibles en el catálogo.';
            audioPlayer.src = '';
            return;
        }
        const song = songs[index];
        trackInfo.textContent = `${song.title} - ${song.artist}`;
        audioPlayer.src = song.src;
    }

    try {
        const response = await fetch('./api/canciones.php');
        if (!response.ok) {
            throw new Error('Error al conectar con la API');
        }
        songs = await response.json();
        loadTrack(currentIndex);
    } catch (err) {
        trackInfo.textContent = 'Error al cargar las canciones desde el servidor.';
    }

    btnPrev.addEventListener('click', () => {
        if (songs.length > 0) {
            currentIndex = (currentIndex - 1 + songs.length) % songs.length;
            loadTrack(currentIndex);
            audioPlayer.play();
        }
    });

    btnNext.addEventListener('click', () => {
        if (songs.length > 0) {
            currentIndex = (currentIndex + 1) % songs.length;
            loadTrack(currentIndex);
            audioPlayer.play();
        }
    });
});
</script>
</body>
</html>