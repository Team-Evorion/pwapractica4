<?php
require_once 'conexion.php';

$mensaje_exito = "";
$mensaje_error = "";

if (isset($_GET['status']) && $_GET['status'] === 'success') {
    $mensaje_exito = "¡Compra realizada con éxito! Su pedido ha sido registrado correctamente.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comprar'])) {
    $nombre_cliente = trim($_POST['nombre'] ?? '');
    $correo_cliente = trim($_POST['correo'] ?? '');
    $direccion_cliente = trim($_POST['direccion'] ?? '');
    $producto_id = filter_var($_POST['producto_id'] ?? 0, FILTER_VALIDATE_INT);

    if (empty($nombre_cliente) || empty($correo_cliente) || empty($direccion_cliente) || $producto_id === false || $producto_id <= 0) {
        $mensaje_error = "Por favor, complete todos los campos obligatorios con información válida.";
    } elseif (!filter_var($correo_cliente, FILTER_VALIDATE_EMAIL)) {
        $mensaje_error = "El formato del correo electrónico no es válido.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt_prod = $pdo->prepare("SELECT id FROM productos WHERE id = ?");
            $stmt_prod->execute([$producto_id]);
            if (!$stmt_prod->fetch()) {
                throw new Exception("El producto seleccionado no existe en el sistema.");
            }

            $stmt_cli = $pdo->prepare("SELECT id FROM clientes WHERE correo = ?");
            $stmt_cli->execute([$correo_cliente]);
            $cliente = $stmt_cli->fetch(PDO::FETCH_ASSOC);

            if ($cliente) {
                $cliente_id = $cliente['id'];
                $stmt_upd = $pdo->prepare("UPDATE clientes SET nombre = ?, direccion = ? WHERE id = ?");
                $stmt_upd->execute([$nombre_cliente, $direccion_cliente, $cliente_id]);
            } else {
                $stmt_ins = $pdo->prepare("INSERT INTO clientes (nombre, correo, direccion) VALUES (?, ?, ?)");
                $stmt_ins->execute([$nombre_cliente, $correo_cliente, $direccion_cliente]);
                $cliente_id = $pdo->lastInsertId();
            }

            $stmt_pedido = $pdo->prepare("INSERT INTO pedidos (cliente_id, producto_id) VALUES (?, ?)");
            $stmt_pedido->execute([$cliente_id, $producto_id]);

            $pdo->commit();
            header("Location: index.php?status=success");
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $mensaje_error = "Ocurrió un error al procesar su solicitud. Por favor, inténtelo de nuevo más tarde.";
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Catálogo de productos de la práctica de Programación Web UBE.">
    <title>Catálogo de productos | UBE</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="index.php" aria-label="UBE, inicio">
                <span class="brand-symbol">U</span>
                <span class="brand-name">UBE<span>STORE</span></span>
            </a>
            <div class="header-note">PRÁCTICA DE PROGRAMACIÓN WEB</div>
        </div>
        <div class="hero">
            <p class="eyebrow">UNA EXPERIENCIA DE COMPRA SENCILLA</p>
            <h1>Encuentra lo que<br><span>estás buscando.</span></h1>
            <p>Explora nuestro catálogo y conoce las opciones disponibles.</p>
            <a class="hero-link" href="#catalogo">Explorar catálogo <span aria-hidden="true">↓</span></a>
        </div>
        <div class="hero-orb hero-orb-one"></div>
        <div class="hero-orb hero-orb-two"></div>
    </header>

    <main class="container" id="catalogo">
        <?php if (!empty($mensaje_exito)): ?>
            <div class="alert alert-success" role="status"><?php echo htmlspecialchars($mensaje_exito); ?></div>
        <?php endif; ?>
        <?php if (!empty($mensaje_error)): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($mensaje_error); ?></div>
        <?php endif; ?>

        <section class="catalog-section" aria-labelledby="catalog-title">
            <div class="section-topline">
                <div>
                    <p class="eyebrow eyebrow-purple">SELECCIÓN DISPONIBLE</p>
                    <h2 id="catalog-title">Catálogo de productos</h2>
                    <p class="section-subtitle">Productos publicados por nuestros vendedores.</p>
                </div>
                <?php if (!empty($productos)): ?>
                    <span class="catalog-count"><?php echo count($productos); ?> producto(s)</span>
                <?php endif; ?>
            </div>

            <?php if (empty($productos)): ?>
                <div class="empty-state">
                    <span class="empty-symbol" aria-hidden="true">◇</span>
                    <h3>El catálogo está en preparación</h3>
                    <p>Por el momento no hay productos disponibles. Vuelve a consultar más tarde.</p>
                </div>
            <?php else: ?>
                <div class="catalog-tools">
                    <label class="search-box" for="productSearch">
                        <span aria-hidden="true">⌕</span>
                        <input type="search" id="productSearch" placeholder="Buscar un producto..." autocomplete="off">
                    </label>
                    <span class="search-feedback" id="searchFeedback" aria-live="polite"></span>
                </div>
                <div class="products-grid" id="productsGrid">
                    <?php foreach ($productos as $prod): ?>
                        <article class="product-card" data-search="<?php echo htmlspecialchars(mb_strtolower($prod['nombre'] . ' ' . $prod['descripcion'] . ' ' . $prod['vendedor_nombre'], 'UTF-8')); ?>">
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
                                <p class="product-category">PRODUCTO DESTACADO</p>
                                <h3 class="product-title"><?php echo htmlspecialchars($prod['nombre']); ?></h3>
                                <p class="product-desc"><?php echo htmlspecialchars($prod['descripcion']); ?></p>
                                <p class="product-seller">Vendedor <strong><?php echo htmlspecialchars($prod['vendedor_nombre']); ?></strong></p>
                                <div class="product-footer">
                                    <div class="price-wrap">
                                        <span class="price-caption">Precio</span>
                                        <span class="product-price">$<?php echo number_format((float)$prod['precio'], 2); ?></span>
                                    </div>
                                    <button
                                        type="button"
                                        class="btn-comprar"
                                        onclick='abrirModal(<?php echo (int)$prod['id']; ?>, <?php echo json_encode($prod['nombre'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>, <?php echo json_encode((float)$prod['precio']); ?>)'>
                                        Comprar <span aria-hidden="true">↗</span>
                                    </button>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="no-search-results" id="noSearchResults" hidden>
                    <h3>No encontramos coincidencias</h3>
                    <p>Prueba con otro nombre de producto o vendedor.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer class="site-footer">
        <div class="footer-inner">
            <span class="footer-brand">UBE STORE</span>
            <span>Proyecto académico · Programación Web</span>
        </div>
    </footer>

    <div id="modalCompra" class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title" aria-hidden="true">
        <div class="modal-content">
            <button type="button" class="close-btn" onclick="cerrarModal()" aria-label="Cerrar ventana">&times;</button>
            <p class="eyebrow eyebrow-purple">CONFIRMACIÓN DE PEDIDO</p>
            <h2 id="modal-title">Finalizar compra</h2>
            <p class="modal-intro">Completa tus datos para registrar el pedido.</p>
            <p id="infoProductoSeleccionado"></p>
            <form id="formCompra" action="" method="POST" onsubmit="return validarFormulario()">
                <input type="hidden" id="producto_id" name="producto_id">
                <div class="form-group">
                    <label for="nombre">Nombre completo</label>
                    <input type="text" id="nombre" name="nombre" required placeholder="Ej. Juan Pérez" autocomplete="name">
                </div>
                <div class="form-group">
                    <label for="correo">Correo electrónico</label>
                    <input type="email" id="correo" name="correo" required placeholder="correo@ejemplo.com" autocomplete="email">
                </div>
                <div class="form-group">
                    <label for="direccion">Dirección de entrega</label>
                    <textarea id="direccion" name="direccion" rows="3" required placeholder="Ciudad, calle principal y secundaria" autocomplete="street-address"></textarea>
                </div>
                <button type="submit" name="comprar" class="btn-submit">Confirmar y registrar pedido <span aria-hidden="true">→</span></button>
                <p class="form-note">Verifica que tus datos sean correctos antes de enviar.</p>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
