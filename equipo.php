<?php

session_start();

$usuario_autenticado = isset($_SESSION['usuario_id']);
$carrito = isset($_SESSION['carrito']) && is_array($_SESSION['carrito']) ? $_SESSION['carrito'] : [];
$cantidad_carrito = array_sum(array_map('intval', $carrito));

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Integrantes de la Clase Práctica 4 de Programación Web Avanzada.">
    <title>Equipo | UBE Store</title>
    <link rel="stylesheet" href="css/style.css?v=16">
</head>
<body class="team-page">
    <header class="site-header compact-header">
        <div class="header-inner">
            <a class="brand" href="index.php" aria-label="UBE Store, inicio"><span class="brand-symbol">U</span><span class="brand-name">UBE<span>STORE</span></span></a>
            <div class="header-actions">
                <nav class="site-nav" aria-label="Navegación principal">
                    <a href="index.php">Inicio</a>
                    <a href="productos.php">Productos</a>
                    <a href="nosotros.php">Nosotros</a>
                    <a href="equipo.php">Equipo</a>
                </nav>
                <nav class="auth-nav" aria-label="Navegación de usuario">
                    <?php if ($usuario_autenticado): ?>
                        <a href="carrito.php" class="cart-link">Carrito <span class="cart-count"><?= (int) $cantidad_carrito ?></span></a>
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
        <section class="team-page-hero">
            <div class="container team-page-intro">
                <div>
                    <p class="eyebrow">EQUIPO UBE STORE</p>
                    <h1>Un proyecto.<br><span>Seis integrantes.</span></h1>
                    <p>Seis estudiantes trabajando de forma colaborativa para construir una experiencia de tienda completa con HTML, CSS, JavaScript, PHP y MySQL.</p>
                    <a class="outline-link" href="#integrantes">Conocer integrantes <span aria-hidden="true">↓</span></a>
                </div>
                <div class="team-number" aria-label="Seis integrantes"><strong>06</strong><span>INTEGRANTES</span></div>
            </div>
        </section>

        <section class="team-section container" id="integrantes" aria-labelledby="team-title">
            <div class="team-heading">
                <div><p class="eyebrow eyebrow-purple">CLASE PRÁCTICA 4</p><h2 id="team-title">Integrantes del proyecto</h2></div>
                <p>Trabajo académico desarrollado de manera colaborativa por estudiantes de Programación Web Avanzada.</p>
            </div>
            <div class="team-grid team-grid-detailed">
                <article class="team-card"><span class="team-avatar">JF</span><div><h3>Jesús Rafael Fernández Pérez</h3><p>Integrante del proyecto</p></div></article>
                <article class="team-card"><span class="team-avatar">JM</span><div><h3>José Andrés Merlin Gamboa</h3><p>Integrante del proyecto</p></div></article>
                <article class="team-card"><span class="team-avatar">JR</span><div><h3>José Alberto Rosado Domínguez</h3><p>Integrante del proyecto</p></div></article>
                <article class="team-card"><span class="team-avatar">TP</span><div><h3>Terry Steven Ponce Farías</h3><p>Integrante del proyecto</p></div></article>
                <article class="team-card"><span class="team-avatar">EV</span><div><h3>Elkin Daniel Velastegui Zambrano</h3><p>Integrante del proyecto</p></div></article>
                <article class="team-card"><span class="team-avatar">ZZ</span><div><h3>Zahid Joel Zambrano Moreira</h3><p>Integrante del proyecto</p></div></article>
            </div>
        </section>

        <section class="team-cta">
            <div class="container"><div><p class="eyebrow">CONOCE EL RESULTADO</p><h2>Una tienda creada como equipo.</h2></div><a class="hero-link hero-link-primary" href="productos.php">Explorar productos →</a></div>
        </section>
    </main>

    <footer class="site-footer simple-store-footer">
        <div class="footer-bottom container"><span>© 2026 UBE Store · Clase Práctica 4.</span></div>
    </footer>
    <script src="js/script.js?v=5"></script>
</body>
</html>
