<?php

session_start();

require_once __DIR__ . '/conexion.php';

$usuario_autenticado = isset($_SESSION['usuario_id']);

if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$cantidad_carrito = array_sum(array_map('intval', $_SESSION['carrito']));

$mensaje_exito = "";
$mensaje_error = "";

if (
    isset($_GET['sesion']) &&
    $_GET['sesion'] === 'cerrada'
) {
    $mensaje_exito = 'La sesión se cerró correctamente.';
}
if (isset($_GET['status']) && $_GET['status'] === 'success') {
    $mensaje_exito = "¡Compra realizada con éxito! Su pedido ha sido registrado correctamente.";
}
if (isset($_GET['carrito']) && $_GET['carrito'] === 'agregado') {
    $mensaje_exito = 'Producto añadido al carrito correctamente.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_carrito'])) {
    if (!$usuario_autenticado) {
        header('Location: login.php?acceso=requerido');
        exit;
    }

    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $mensaje_error = 'La solicitud no es válida. Actualiza la página e inténtalo nuevamente.';
    } else {
        $producto_id = filter_var($_POST['producto_id'] ?? 0, FILTER_VALIDATE_INT);

        if ($producto_id === false || $producto_id <= 0) {
            $mensaje_error = 'El producto seleccionado no es válido.';
        } else {
            $stmt_producto = $pdo->prepare('SELECT id FROM productos WHERE id = ?');
            $stmt_producto->execute([$producto_id]);

            if (!$stmt_producto->fetch()) {
                $mensaje_error = 'El producto seleccionado ya no está disponible.';
            } else {
                $cantidad_actual = (int) ($_SESSION['carrito'][$producto_id] ?? 0);
                $_SESSION['carrito'][$producto_id] = min($cantidad_actual + 1, 10);
                header('Location: index.php?carrito=agregado');
                exit;
            }
        }
    }
}

$productos = [];
try {
    $stmt = $pdo->query("SELECT p.*, v.nombre AS vendedor_nombre FROM productos p JOIN vendedores v ON p.vendedor_id = v.id");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $mensaje_error = "No se pudo cargar el catálogo de productos.";
}
$productos_destacados = array_slice($productos, 0, 3);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="UBE Store, tienda académica de productos tecnológicos.">
    <title>UBE Store | Tecnología para cada día</title>
    <link rel="stylesheet" href="css/style.css?v=15">
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="index.php" aria-label="UBE, inicio">
                <span class="brand-symbol">U</span>
                <span class="brand-name">UBE<span>STORE</span></span>
            </a>
            <div class="header-actions">
                <nav class="site-nav" aria-label="Navegación principal">
                    <a href="#inicio">Inicio</a>
                    <a href="productos.php">Productos</a>
                    <a href="nosotros.php">Nosotros</a>
                    <a href="equipo.php">Equipo</a>
                </nav>

                <nav class="auth-nav" aria-label="Navegación de usuario">
                    <?php if ($usuario_autenticado): ?>
                        <a href="carrito.php" class="cart-link" aria-label="Ver carrito">
                            Carrito
                            <span class="cart-count"><?= (int) $cantidad_carrito ?></span>
                        </a>
                        <span class="user-greeting">
                            Hola, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?>
                        </span>
                        <a href="logout.php" class="auth-button auth-button-outline">Cerrar sesión</a>
                    <?php else: ?>
                        <a href="login.php" class="auth-button auth-button-outline">Iniciar sesión</a>
                        <a href="registro.php" class="auth-button auth-button-primary">Crear cuenta</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>

        <div class="hero" id="inicio">
            <div class="hero-copy">
                <p class="eyebrow">TECNOLOGÍA PARA TU DÍA A DÍA</p>
                <h1>Todo lo que buscas,<br><span>en un solo lugar.</span></h1>
                <p>Descubre equipos y accesorios seleccionados para estudiar, trabajar y disfrutar.</p>
                <div class="hero-actions">
                    <a class="hero-link hero-link-primary" href="productos.php">
                        Explorar productos <span aria-hidden="true">→</span>
                    </a>
                    <a class="hero-link hero-link-secondary" href="nosotros.php">Sobre nosotros <span aria-hidden="true">→</span></a>
                </div>
                <div class="hero-points" aria-label="Beneficios de la tienda">
                    <span>✓ Compra protegida</span>
                    <span>✓ Catálogo actualizado</span>
                    <span>✓ Diseño responsive</span>
                </div>
            </div>

            <aside class="featured-showcase" aria-label="Producto destacado">
                <span class="featured-label">PRODUCTO DESTACADO</span>
                <div class="featured-image-wrap">
                    <img src="img/productos/laptop-lenovo-ideapad.jpeg" alt="Laptop Lenovo IdeaPad">
                </div>
                <div class="featured-info">
                    <div>
                        <span>Ideal para estudio y trabajo</span>
                        <h2>Laptop Lenovo IdeaPad</h2>
                    </div>
                    <strong>$649.99</strong>
                </div>
            </aside>
        </div>

        <div class="hero-orb hero-orb-one"></div>
        <div class="hero-orb hero-orb-two"></div>
    </header>

    <main>
        <?php if (!empty($mensaje_exito)): ?>
            <div class="toast-notification toast-success" data-toast role="status">
                <span class="toast-icon" aria-hidden="true">✓</span>
                <p><?php echo htmlspecialchars($mensaje_exito); ?></p>
                <button type="button" data-toast-close aria-label="Cerrar notificación">×</button>
            </div>
        <?php endif; ?>
        <?php if (!empty($mensaje_error)): ?>
            <div class="toast-notification toast-danger" data-toast role="alert">
                <span class="toast-icon" aria-hidden="true">!</span>
                <p><?php echo htmlspecialchars($mensaje_error); ?></p>
                <button type="button" data-toast-close aria-label="Cerrar notificación">×</button>
            </div>
        <?php endif; ?>

        <section class="store-highlights" aria-label="Ventajas de UBE Store">
            <div class="container highlights-grid">
                <div><span class="highlight-icon">✦</span><p><strong>Variedad tecnológica</strong><small>Productos para diferentes necesidades.</small></p></div>
                <div><span class="highlight-icon">⌁</span><p><strong>Estilo e innovación</strong><small>Tecnología con una identidad moderna.</small></p></div>
                <div><span class="highlight-icon">◎</span><p><strong>Experiencia sencilla</strong><small>Explora y compra en un solo lugar.</small></p></div>
            </div>
        </section>

        <section class="catalog-section container" id="destacados" aria-labelledby="catalog-title">
            <div class="section-topline">
                <div>
                    <p class="eyebrow eyebrow-purple">SELECCIÓN DISPONIBLE</p>
                    <h2 id="catalog-title">Productos destacados</h2>
                    <p class="section-subtitle">Una selección para comenzar a explorar UBE Store.</p>
                </div>
                <?php if (!empty($productos)): ?>
                    <a class="catalog-count" href="productos.php"><?php echo count($productos); ?> productos en catálogo →</a>
                <?php endif; ?>
            </div>

            <?php if (empty($productos)): ?>
                <div class="empty-state">
                    <span class="empty-symbol" aria-hidden="true">◇</span>
                    <h3>El catálogo está en preparación</h3>
                    <p>Por el momento no hay productos disponibles. Vuelve a consultar más tarde.</p>
                </div>
            <?php else: ?>
                <div class="products-grid featured-products-grid">
                    <?php foreach ($productos_destacados as $prod):
                        $categoria = match ((int) $prod['id']) {
                            1, 2, 6 => 'computadoras',
                            3, 7 => 'audio',
                            4, 5 => 'gaming',
                            8 => 'wearables',
                            9 => 'almacenamiento',
                            default => 'all'
                        };
                        $categoria_nombre = match ($categoria) {
                            'computadoras' => 'COMPUTACIÓN',
                            'audio' => 'AUDIO Y SONIDO',
                            'gaming' => 'GAMING',
                            'wearables' => 'WEARABLES',
                            'almacenamiento' => 'ALMACENAMIENTO',
                            default => 'TECNOLOGÍA'
                        };
                    ?>
                        <article
                            class="product-card"
                            data-category="<?= htmlspecialchars($categoria) ?>"
                            data-search="<?php echo htmlspecialchars(mb_strtolower($prod['nombre'] . ' ' . $prod['descripcion'] . ' ' . $prod['vendedor_nombre'], 'UTF-8')); ?>"
                        >
                            <div class="product-media">
                                <img
                                    src="<?php echo htmlspecialchars($prod['imagen']); ?>"
                                    alt="<?php echo htmlspecialchars($prod['nombre']); ?>"
                                    class="product-img"
                                    loading="lazy"
                                    onerror="this.onerror=null; this.src='img/productos/sin-imagen.svg';">
                                <span class="image-label">DISPONIBLE</span>
                            </div>
                            <div class="product-body">
                                <p class="product-category"><?= htmlspecialchars($categoria_nombre) ?></p>
                                <h3 class="product-title"><?php echo htmlspecialchars($prod['nombre']); ?></h3>
                                <p class="product-desc"><?php echo htmlspecialchars($prod['descripcion']); ?></p>
                                <p class="product-seller">Vendedor <strong><?php echo htmlspecialchars($prod['vendedor_nombre']); ?></strong></p>
                                <div class="product-footer">
                                    <div class="price-wrap">
                                        <span class="price-caption">Precio</span>
                                        <span class="product-price">$<?php echo number_format((float)$prod['precio'], 2); ?></span>
                                    </div>
                                    <?php if ($usuario_autenticado): ?>
                                        <form method="POST" class="add-cart-form">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                            <input type="hidden" name="producto_id" value="<?= (int) $prod['id'] ?>">
                                            <button type="submit" name="agregar_carrito" class="btn-comprar">
                                                Añadir al carrito <span aria-hidden="true">＋</span>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <a href="login.php?acceso=requerido" class="btn-comprar">
                                            Iniciar sesión para comprar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="catalog-cta">
                    <a class="outline-link light-outline" href="productos.php">Ver catálogo completo <span aria-hidden="true">→</span></a>
                </div>
            <?php endif; ?>
        </section>

    </main>

    <footer class="site-footer">
        <div class="footer-main container">
            <div class="footer-about">
                <a class="brand" href="#inicio" aria-label="UBE Store, volver al inicio">
                    <span class="brand-symbol">U</span>
                    <span class="brand-name">UBE<span>STORE</span></span>
                </a>
                <p>Tecnología, conexión y soluciones para estudiar, trabajar y disfrutar.</p>
            </div>
            <div class="footer-column">
                <h3>Explora</h3>
                <a href="#inicio">Inicio</a>
                <a href="productos.php">Todos los productos</a>
            </div>
            <div class="footer-column">
                <h3>UBE Store</h3>
                <a href="nosotros.php">Sobre nosotros</a>
                <a href="equipo.php">Nuestro equipo</a>
                <?php if ($usuario_autenticado): ?>
                    <a href="carrito.php">Carrito de compras</a>
                <?php else: ?>
                    <a href="login.php">Iniciar sesión</a>
                <?php endif; ?>
            </div>
            <div class="footer-column footer-contact">
                <h3>Proyecto académico</h3>
                <p>Conoce a las seis personas que forman parte de esta Clase Práctica 4.</p>
                <a href="equipo.php">Conocer al equipo →</a>
            </div>
        </div>
        <div class="footer-bottom container">
            <span>© 2026 UBE Store · Proyecto académico de demostración.</span>
        </div>
    </footer>

    <script src="js/script.js?v=5"></script>
</body>
</html>
