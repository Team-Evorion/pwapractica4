<?php

session_start();

require_once __DIR__ . '/conexion.php';

$mensaje_error = '';
$mensaje_exito = '';

$nombre = '';
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';

    if (mb_strlen($nombre) < 3) {
        $mensaje_error = 'El nombre debe tener al menos 3 caracteres.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje_error = 'Ingrese un correo electrónico válido.';
    } elseif (strlen($password) < 8) {
        $mensaje_error = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($password !== $confirmar_password) {
        $mensaje_error = 'Las contraseñas no coinciden.';
    } else {
        try {
            $consulta = $pdo->prepare(
                'SELECT id
                 FROM usuarios
                 WHERE correo = :correo
                 LIMIT 1'
            );

            $consulta->execute([
                'correo' => $correo
            ]);

            if ($consulta->fetch()) {
                $mensaje_error = 'Ya existe una cuenta con ese correo.';
            } else {
                $password_hash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $consulta = $pdo->prepare(
                    'INSERT INTO usuarios (
                        nombre,
                        correo,
                        password
                    ) VALUES (
                        :nombre,
                        :correo,
                        :password
                    )'
                );

                $consulta->execute([
                    'nombre' => $nombre,
                    'correo' => $correo,
                    'password' => $password_hash
                ]);

                $mensaje_exito = 'Usuario registrado correctamente. Ya puede iniciar sesión.';

                $nombre = '';
                $correo = '';
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());

            $mensaje_error = 'No se pudo completar el registro. Inténtelo nuevamente.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Registro de usuario | UBE Store</title>

    <link rel="stylesheet" href="css/style.css?v=15">
</head>
<body class="auth-page">

    <main class="auth-container">
        <section class="auth-card">
            <p class="eyebrow eyebrow-purple">
                CREAR CUENTA
            </p>

            <h1>Registro de usuario</h1>

            <p class="auth-description">
                Regístrate para acceder al catálogo y realizar compras.
            </p>

            <?php if ($mensaje_error !== ''): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($mensaje_error) ?>
                </div>
            <?php endif; ?>

            <?php if ($mensaje_exito !== ''): ?>
                <div class="alert alert-success" role="status">
                    <?= htmlspecialchars($mensaje_exito) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="nombre">
                        Nombre completo
                    </label>

                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        value="<?= htmlspecialchars($nombre) ?>"
                        minlength="3"
                        maxlength="100"
                        autocomplete="name"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="correo">
                        Correo electrónico
                    </label>

                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        value="<?= htmlspecialchars($correo) ?>"
                        maxlength="150"
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">
                        Contraseña
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        minlength="8"
                        maxlength="72"
                        autocomplete="new-password"
                        required
                    >

                    <small>
                        Utiliza al menos 8 caracteres.
                    </small>
                </div>

                <div class="form-group">
                    <label for="confirmar_password">
                        Confirmar contraseña
                    </label>

                    <input
                        type="password"
                        id="confirmar_password"
                        name="confirmar_password"
                        minlength="8"
                        maxlength="72"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button type="submit" class="btn-submit">
                    Crear cuenta
                </button>
            </form>

            <p class="auth-link">
                ¿Ya tienes una cuenta?
                <a href="login.php">Iniciar sesión</a>
            </p>

            <p class="auth-link">
                <a href="index.php">Volver al catálogo</a>
            </p>
        </section>
    </main>

</body>
</html>
