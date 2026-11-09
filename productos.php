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
$mensaje_exito = isset($_GET['carrito']) && $_GET['carrito'] === 'agregado'
    ? 'Producto añadido al carrito correctamente.'
    : '';
$mensaje_error = '';

function categoriaProducto(int $producto_id): string
{
    return match ($producto_id) {
        1, 2, 6 => 'computadoras',
        3, 7 => 'audio',
        4, 5 => 'gaming',
        8 => 'wearables',
        9 => 'almacenamiento',
        default => 'all'
    };
}

function nombreCategoria(string $categoria): string
{
    return match ($categoria) {
        'computadoras' => 'COMPUTACIÓN',
        'audio' => 'AUDIO Y SONIDO',
        'gaming' => 'GAMING',
        'wearables' => 'WEARABLES',
        'almacenamiento' => 'ALMACENAMIENTO',
        default => 'TECNOLOGÍA'
    };
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
            $consulta = $pdo->prepare('SELECT id FROM productos WHERE id = ?');
            $consulta->execute([$producto_id]);
            if (!$consulta->fetch()) {
                $mensaje_error = 'El producto seleccionado ya no está disponible.';
            } else {
                $cantidad_actual = (int) ($_SESSION['carrito'][$producto_id] ?? 0);
                $_SESSION['carrito'][$producto_id] = min($cantidad_actual + 1, 10);
                $categoria_redireccion = trim($_POST['categoria_actual'] ?? 'all');
                $sufijo = $categoria_redireccion !== 'all'
                    ? '?categoria=' . rawurlencode($categoria_redireccion) . '&carrito=agregado'
                    : '?carrito=agregado';
                header('Location: productos.php' . $sufijo);
                exit;
            }
        }
    }
}

$productos = [];
try {
    $consulta_productos = $pdo->query(
        'SELECT p.*, v.nombre AS vendedor_nombre
         FROM productos p
         JOIN vendedores v ON v.id = p.vendedor_id
         ORDER BY p.id'
    );
    $productos = $consulta_productos->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $error) {
    $mensaje_error = 'No se pudo cargar el catálogo de productos.';
}

$categorias_validas = ['all', 'computadoras', 'audio', 'gaming', 'wearables', 'almacenamiento'];
$categoria_actual = $_GET['categoria'] ?? 'all';
if (!in_array($categoria_actual, $categorias_validas, true)) {
    $categoria_actual = 'all';
}

$productos_mostrados = array_values(array_filter(
    $productos,
    static fn (array $producto): bool =>
        $categoria_actual === 'all' || categoriaProducto((int) $producto['id']) === $categoria_actual
));

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Catálogo completo de productos tecnológicos de UBE Store.">
    <title>Productos | UBE Store</title>
    <link rel="stylesheet" href="css/style.css?v=15">
</head>
<body>
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
        <div class="products-page-hero">
            <div>
                <p class="eyebrow">SELECCIÓN UBE STORE</p>
                <h1>Tecnología para tu día a día.</h1>
            </div>
            <p>Dispositivos y accesorios para acompañarte en cada momento.</p>
        </div>
    </header>

    <main class="container products-page">
        <?php if ($mensaje_exito !== ''): ?>
            <div class="toast-notification toast-success" data-toast role="status"><span class="toast-icon" aria-hidden="true">✓</span><p><?= htmlspecialchars($mensaje_exito) ?></p><button type="button" data-toast-close aria-label="Cerrar notificación">×</button></div>
        <?php endif; ?>
        <?php if ($mensaje_error !== ''): ?>
            <div class="toast-notification toast-danger" data-toast role="alert"><span class="toast-icon" aria-hidden="true">!</span><p><?= htmlspecialchars($mensaje_error) ?></p><button type="button" data-toast-close aria-label="Cerrar notificación">×</button></div>
        <?php endif; ?>

        <nav class="category-pills" aria-label="Categorías de productos">
            <a class="<?= $categoria_actual === 'all' ? 'active' : '' ?>" href="productos.php">Todos</a>
            <a class="<?= $categoria_actual === 'computadoras' ? 'active' : '' ?>" href="productos.php?categoria=computadoras">Computación</a>
            <a class="<?= $categoria_actual === 'audio' ? 'active' : '' ?>" href="productos.php?categoria=audio">Audio</a>
            <a class="<?= $categoria_actual === 'gaming' ? 'active' : '' ?>" href="productos.php?categoria=gaming">Gaming</a>
            <a class="<?= $categoria_actual === 'wearables' ? 'active' : '' ?>" href="productos.php?categoria=wearables">Wearables</a>
            <a class="<?= $categoria_actual === 'almacenamiento' ? 'active' : '' ?>" href="productos.php?categoria=almacenamiento">Almacenamiento</a>
        </nav>

        <div class="catalog-tools">
            <label class="search-box" for="productSearch"><span aria-hidden="true">⌕</span><input type="search" id="productSearch" placeholder="Buscar un producto..." autocomplete="off"></label>
            <span class="search-feedback" id="searchFeedback" aria-live="polite"><?= count($productos_mostrados) ?> producto(s)</span>
        </div>

        <?php if (empty($productos_mostrados)): ?>
            <div class="empty-state"><span class="empty-symbol">◇</span><h2>No hay productos en esta categoría</h2><a class="btn-comprar" href="productos.php">Ver todos</a></div>
        <?php else: ?>
            <div class="products-grid" id="productsGrid">
                <?php foreach ($productos_mostrados as $producto):
                    $categoria_producto = categoriaProducto((int) $producto['id']);
                ?>
                    <article class="product-card" data-category="<?= htmlspecialchars($categoria_producto) ?>" data-search="<?= htmlspecialchars(mb_strtolower($producto['nombre'] . ' ' . $producto['descripcion'] . ' ' . $producto['vendedor_nombre'], 'UTF-8')) ?>">
                        <div class="product-media">
                            <img src="<?= htmlspecialchars($producto['imagen']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" class="product-img" loading="lazy" onerror="this.onerror=null;this.src='img/productos/sin-imagen.svg';">
                            <span class="image-label">DISPONIBLE</span>
                        </div>
                        <div class="product-body">
                            <p class="product-category"><?= htmlspecialchars(nombreCategoria($categoria_producto)) ?></p>
                            <h2 class="product-title"><?= htmlspecialchars($producto['nombre']) ?></h2>
                            <p class="product-desc"><?= htmlspecialchars($producto['descripcion']) ?></p>
                            <p class="product-seller">Vendedor <strong><?= htmlspecialchars($producto['vendedor_nombre']) ?></strong></p>
                            <div class="product-footer">
                                <div class="price-wrap"><span class="price-caption">Precio</span><span class="product-price">$<?= number_format((float) $producto['precio'], 2) ?></span></div>
                                <?php if ($usuario_autenticado): ?>
                                    <form method="POST" class="add-cart-form">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="producto_id" value="<?= (int) $producto['id'] ?>">
                                        <input type="hidden" name="categoria_actual" value="<?= htmlspecialchars($categoria_actual) ?>">
                                        <button type="submit" name="agregar_carrito" class="btn-comprar">Añadir al carrito <span aria-hidden="true">＋</span></button>
                                    </form>
                                <?php else: ?>
                                    <a href="login.php?acceso=requerido" class="btn-comprar">Iniciar sesión para comprar</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="no-search-results" id="noSearchResults" hidden><h2>No encontramos coincidencias</h2><p>Prueba con otra palabra.</p></div>
        <?php endif; ?>
    </main>

    <footer class="site-footer simple-store-footer">
        <div class="footer-bottom container">
            <span>© 2026 UBE Store · Catálogo tecnológico.</span>
        </div>
    </footer>
    <script src="js/script.js?v=5"></script>
</body>
</html>
