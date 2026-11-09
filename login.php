<?php

session_start();

require_once __DIR__ . '/conexion.php';

$mensaje_error = '';
$mensaje_exito = '';
$correo = '';

if (
    isset($_GET['logout']) &&
    $_GET['logout'] === 'exitoso'
) {
    $mensaje_exito = 'La sesión se cerró correctamente.';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje_error = 'Ingrese un correo electrónico válido.';
    } elseif ($password === '') {
        $mensaje_error = 'Ingrese su contraseña.';
    } else {
        try {
            $consulta = $pdo->prepare(
                'SELECT
                    id,
                    nombre,
                    correo,
                    password,
                    rol
                 FROM usuarios
                 WHERE correo = :correo
                 LIMIT 1'
            );

            $consulta->execute([
                'correo' => $correo
            ]);

            $usuario = $consulta->fetch();

            if (
                $usuario &&
                password_verify($password, $usuario['password'])
            ) {
                session_regenerate_id(true);

                $_SESSION['usuario_id'] = (int) $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_correo'] = $usuario['correo'];
                $_SESSION['usuario_rol'] = $usuario['rol'];

                header('Location: index.php');
                exit;
            }

            $mensaje_error = 'El correo o la contraseña son incorrectos.';
        } catch (PDOException $e) {
            error_log($e->getMessage());

            $mensaje_error = 'No se pudo iniciar sesión. Inténtelo nuevamente.';
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

    <title>Iniciar sesión | UBE Store</title>

    <link rel="stylesheet" href="css/style.css?v=16">
</head>
<body class="auth-page">

    <main class="auth-container">
        <section class="auth-card">
            <p class="eyebrow eyebrow-purple">
                BIENVENIDO
            </p>

            <h1>Iniciar sesión</h1>

            <p class="auth-description">
                Acceso de demostración académica. No se procesan pagos reales ni se solicitan datos bancarios.
            </p>

            <?php if ($mensaje_exito !== ''): ?>
    <div class="alert alert-success" role="status">
        <?= htmlspecialchars($mensaje_exito) ?>
    </div>
<?php endif; ?>

            <?php if ($mensaje_error !== ''): ?>
                <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($mensaje_error) ?>
            </div>
<?php endif; ?>

            <form method="POST" class="auth-form">
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
                        maxlength="72"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button type="submit" class="btn-submit">
                    Iniciar sesión
                </button>
            </form>

            <p class="auth-link">
                ¿No tienes una cuenta?
                <a href="registro.php">Crear cuenta</a>
            </p>

            <p class="auth-link">
                <a href="index.php">Volver al catálogo</a>
            </p>
        </section>
    </main>

</body>
</html>
