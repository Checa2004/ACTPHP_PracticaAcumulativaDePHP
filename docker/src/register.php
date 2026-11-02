<?php
require_once __DIR__ . '/lib/sesion.php';
require_once __DIR__ . '/lib/almacen-json.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: home.php', true, 303);
    exit;
}

$errores = [];
$alias = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCSRF();

    $aliasInput = trim($_POST['alias'] ?? '');
    $emailInput = strtolower(trim($_POST['email'] ?? ''));
    $passInput = $_POST['password'] ?? '';
    $passConfirm = $_POST['password_confirm'] ?? '';

    if (mb_strlen($aliasInput) < 2 || mb_strlen($aliasInput) > 30) {
        $errores[] = 'El alias debe tener entre 2 y 30 caracteres.';
    } else {
        $alias = $aliasInput;
    }

    if (!filter_var($emailInput, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico no es válido.';
    } else {
        $email = $emailInput;
    }

    if (mb_strlen($passInput) < 12 || mb_strlen($passInput) > 64 || strlen($passInput) > 72) {
        $errores[] = 'La contraseña debe tener entre 12 y 64 caracteres.';
    } elseif ($passInput !== $passConfirm) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    if (empty($errores)) {
        $ok = actualizarJSON(RUTA_USUARIOS_JSON, function(array $usuarios) use ($alias, $email, $passInput, &$errores) {
            foreach ($usuarios as $u) {
                if (isset($u['email']) && strtolower($u['email']) === $email) {
                    $errores[] = 'El correo ya está registrado.';
                    return false;
                }
            }

            $maxId = 0;
            foreach ($usuarios as $u) {
                if (isset($u['id']) && $u['id'] > $maxId) {
                    $maxId = $u['id'];
                }
            }

            $usuarios[] = [
                'id' => $maxId + 1,
                'alias' => $alias,
                'email' => $email,
                'password_hash' => password_hash($passInput, PASSWORD_DEFAULT)
            ];

            return $usuarios;
        });

        if ($ok && empty($errores)) {
            header('Location: login.php', true, 303);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Jukebox</title>
    <style>
        body { font-family: sans-serif; margin: 2rem; background: #f5f5f5; }
        .box { max-width: 380px; margin: auto; padding: 1.5rem; background: #fff; border: 1px solid #ccc; border-radius: 5px; }
        .error { color: #b94a48; background: #f2dede; padding: 0.5rem; border-radius: 3px; margin-bottom: 1rem; font-size: 0.9rem; }
        .field { margin-bottom: 1rem; }
        label { display: block; font-weight: bold; margin-bottom: 0.3rem; }
        input { width: 100%; padding: 0.4rem; box-sizing: border-box; }
        button { width: 100%; padding: 0.6rem; background: #337ab7; color: white; border: none; border-radius: 3px; cursor: pointer; }
    </style>
</head>
<body>
<div class="box">
    <h2>Registro de usuario</h2>

    <?php if (!empty($errores)): ?>
        <div class="error">
            <ul style="margin:0; padding-left: 1.2rem;">
                <?php foreach ($errores as $e): ?>
                    <li><?= htmlspecialchars($e, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="register.php">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        
        <div class="field">
            <label for="alias">Alias:</label>
            <input type="text" id="alias" name="alias" value="<?= htmlspecialchars($alias, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>
        </div>

        <div class="field">
            <label for="email">Correo:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>
        </div>

        <div class="field">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="field">
            <label for="password_confirm">Repite contraseña:</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
        </div>

        <button type="submit">Crear cuenta</button>
    </form>
    <p style="font-size: 0.9rem;"><a href="login.php">¿Ya tienes cuenta? Inicia sesión</a></p>
</div>
</body>
</html>