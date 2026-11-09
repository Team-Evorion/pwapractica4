<?php

session_start();

$usuario_autenticado = isset($_SESSION['usuario_id']);
$carrito = isset($_SESSION['carrito']) && is_array($_SESSION['carrito'])
    ? $_SESSION['carrito']
    : [];
$cantidad_carrito = array_sum(array_map('intval', $carrito));

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Conoce la historia y el propósito de UBE Store.">
    <title>Nosotros | UBE Store</title>
    <link rel="stylesheet" href="css/style.css?v=15">
</head>
<body class="about-page">
    <header class="site-header compact-header">
        <div class="header-inner">
            <a class="brand" href="index.php" aria-label="UBE Store, inicio">
                <span class="brand-symbol">U</span>
                <span class="brand-name">UBE<span>STORE</span></span>
            </a>
            <div class="header-actions">
                <nav class="site-nav" aria-label="Navegación principal">
                    <a href="index.php">Inicio</a>
                    <a href="productos.php">Productos</a>
                    <a href="#historia">Nosotros</a>
                    <a href="equipo.php">Equipo</a>
                </nav>
                <nav class="auth-nav" aria-label="Navegación de usuario">
                    <?php if ($usuario_autenticado): ?>
                        <a href="carrito.php" class="cart-link" aria-label="Ver carrito">
                            Carrito
                            <span class="cart-count"><?= (int) $cantidad_carrito ?></span>
                        </a>
                        <span class="user-greeting">Hola, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>
                        <a href="logout.php" class="auth-button auth-button-outline">Cerrar sesión</a>
                    <?php else: ?>
                        <a href="login.php" class="auth-button auth-button-outline">Iniciar sesión</a>
                        <a href="registro.php" class="auth-button auth-button-primary">Crear cuenta</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <section class="about-section" id="historia" aria-labelledby="about-title">
            <div class="container about-layout">
                <div class="about-visual" aria-hidden="true">
                    <div class="about-logo">
                        <span>UBE</span>
                        <strong>TECH STORE</strong>
                        <small>TECNOLOGÍA PARA CADA DÍA</small>
                    </div>
                </div>
                <div class="about-copy">
                    <p class="eyebrow">SOMOS UBE STORE</p>
                    <h1 id="about-title">La tecnología va más allá de una pantalla.</h1>
                    <p>UBE Store nace como una tienda académica para descubrir productos electrónicos y soluciones digitales que acompañan el estudio, el trabajo y el entretenimiento.</p>
                    <p>Nuestro catálogo reúne computación, audio, accesorios, dispositivos inteligentes y almacenamiento en una experiencia clara y responsive.</p>
                    <a class="outline-link" href="equipo.php">Conoce a nuestro equipo <span aria-hidden="true">→</span></a>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="footer-main container">
            <div class="footer-about">
                <a class="brand" href="index.php" aria-label="UBE Store, inicio">
                    <span class="brand-symbol">U</span>
                    <span class="brand-name">UBE<span>STORE</span></span>
                </a>
                <p>Tecnología, conexión y soluciones para estudiar, trabajar y disfrutar.</p>
            </div>
            <div class="footer-column">
                <h3>Explora</h3>
                <a href="index.php">Inicio</a>
                <a href="productos.php">Productos</a>
            </div>
            <div class="footer-column">
                <h3>UBE Store</h3>
                <a href="#historia">Sobre nosotros</a>
                <a href="equipo.php">Nuestro equipo</a>
                <?php if ($usuario_autenticado): ?><a href="carrito.php">Carrito</a><?php else: ?><a href="login.php">Iniciar sesión</a><?php endif; ?>
            </div>
            <div class="footer-column footer-contact">
                <h3>Proyecto académico</h3>
                <p>Clase Práctica 4 de Programación Web Avanzada.</p>
                <a href="equipo.php">Ver integrantes →</a>
            </div>
        </div>
        <div class="footer-bottom container">
            <span>© 2026 UBE Store · Proyecto académico de demostración.</span>
        </div>
    </footer>
    <script src="js/script.js?v=5"></script>
</body>
</html>
