<?php
define('RUTA_CANCIONES_JSON', '/var/www/data/canciones.json');
define('RUTA_USUARIOS_JSON', '/var/www/data/usuarios.json');

function leerJSON(string $ruta): array {
    if (!file_exists($ruta)) {
        return [];
    }

    $f = fopen($ruta, 'rb');
    if (!$f) {
        throw new Exception("No se pudo abrir el archivo para lectura: {$ruta}");
    }

    flock($f, LOCK_SH);
    $contenido = stream_get_contents($f);
    flock($f, LOCK_UN);
    fclose($f);

    if ($contenido === false || trim($contenido) === '') {
        return [];
    }

    return json_decode($contenido, true, 512, JSON_THROW_ON_ERROR) ?? [];
}

function actualizarJSON(string $ruta, callable $callback): bool {
    if (!file_exists($ruta)) {
        file_put_contents($ruta, '[]');
    }

    $f = fopen($ruta, 'c+b');
    if (!$f) {
        return false;
    }

    if (!flock($f, LOCK_EX)) {
        fclose($f);
        return false;
    }

    $contenido = stream_get_contents($f);
    $datos = [];
    if ($contenido !== false && trim($contenido) !== '') {
        try {
            $datos = json_decode($contenido, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            flock($f, LOCK_UN);
            fclose($f);
            return false;
        }
    }

    $nuevosDatos = $callback($datos);
    if ($nuevosDatos === false) {
        flock($f, LOCK_UN);
        fclose($f);
        return false;
    }

    $jsonTexto = json_encode($nuevosDatos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

    rewind($f);
    ftruncate($f, 0);
    $escritos = fwrite($f, $jsonTexto);
    fflush($f);

    flock($f, LOCK_UN);
    fclose($f);

    return $escritos !== false;
}

function duracionLegible(int $total): string {
    $minutos = intdiv($total, 60);
    $segundos = $total % 60;
    return "{$minutos} min {$segundos} s";
}