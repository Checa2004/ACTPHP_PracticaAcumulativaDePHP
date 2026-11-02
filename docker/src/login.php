<?php
require_once __DIR__ . '/lib/sesion.php';
require_once __DIR__ . '/lib/almacen-json.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: home.php', true, 303);
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCSRF();

    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password)) {
        $error = 'Credenciales no válidas.';
    } else {
        $usuarios = leerJSON(RUTA_USUARIOS_JSON);
        $userFound = null;

        foreach ($usuarios as $u) {
            if (isset($u['email']) && strtolower($u['email']) === $email) {
                $userFound = $u;
                break;
            }
        }

        if ($userFound && password_verify($password, $userFound['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = $userFound['id'];
            $_SESSION['alias'] = $userFound['alias'];

            header('Location: home.php', true, 303);
            exit;
        } else {
            $error = 'Credenciales no válidas.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Jukebox</title>
    <style>
        body { font-family: sans-serif; margin: 2rem; background: #f5f5f5; }
        .box { max-width: 380px; margin: auto; padding: 1.5rem; background: #fff; border: 1px solid #ccc; border-radius: 5px; }
        .error { color: #b94a48; background: #f2dede; padding: 0.5rem; border-radius: 3px; margin-bottom: 1rem; font-size: 0.9rem; }
        .field { margin-bottom: 1rem; }
        label { display: block; font-weight: bold; margin-bottom: 0.3rem; }
        input { width: 100%; padding: 0.4rem; box-sizing: border-box; }
        button { width: 100%; padding: 0.6rem; background: #5cb85c; color: white; border: none; border-radius: 3px; cursor: pointer; }
    </style>
</head>
<body>
<div class="box">
    <h2>Iniciar sesión</h2>

    <?php if ($error !== ''): ?>
        <div class="error"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">

        <div class="field">
            <label for="email">Correo:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>
        </div>

        <div class="field">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit">Entrar</button>
    </form>
    <p style="font-size: 0.9rem;"><a href="register.php">¿No tienes cuenta? Regístrate</a></p>
</div>
</body>
</html>