<?php

session_start();

require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php?acceso=requerido');
    exit;
}

if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$mensaje_exito = '';
$mensaje_error = '';
$error_direccion = '';

function cargarProductosCarrito(PDO $pdo, array $carrito): array
{
    $ids = array_values(array_filter(
        array_map('intval', array_keys($carrito)),
        static fn (int $id): bool => $id > 0
    ));

    if (empty($ids)) {
        return [];
    }

    $marcadores = implode(',', array_fill(0, count($ids), '?'));
    $consulta = $pdo->prepare(
        "SELECT p.id, p.nombre, p.descripcion, p.precio, p.imagen, v.nombre AS vendedor_nombre
         FROM productos p
         JOIN vendedores v ON v.id = p.vendedor_id
         WHERE p.id IN ($marcadores)
         ORDER BY p.nombre"
    );
    $consulta->execute($ids);
    $productos = $consulta->fetchAll(PDO::FETCH_ASSOC);

    foreach ($productos as &$producto) {
        $cantidad = max(1, min(10, (int) ($carrito[$producto['id']] ?? 1)));
        $producto['cantidad'] = $cantidad;
        $producto['subtotal'] = (float) $producto['precio'] * $cantidad;
    }
    unset($producto);

    return $productos;
}

if (isset($_GET['estado']) && $_GET['estado'] === 'actualizado') {
    $mensaje_exito = 'La cantidad se actualizó correctamente.';
}
if (isset($_GET['estado']) && $_GET['estado'] === 'eliminado') {
    $mensaje_exito = 'El producto se eliminó del carrito.';
}
if (isset($_GET['estado']) && $_GET['estado'] === 'vacio') {
    $mensaje_exito = 'El carrito se vació correctamente.';
}
if (isset($_GET['estado']) && $_GET['estado'] === 'comprado') {
    $mensaje_exito = '¡Compra realizada con éxito! Todos los productos fueron registrados.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $mensaje_error = 'La solicitud no es válida. Actualiza la página e inténtalo nuevamente.';
    } elseif (isset($_POST['actualizar_carrito'])) {
        $cantidades = $_POST['cantidades'] ?? [];

        if (is_array($cantidades)) {
            foreach ($cantidades as $producto_id => $cantidad) {
                $producto_id = filter_var($producto_id, FILTER_VALIDATE_INT);
                $cantidad = filter_var($cantidad, FILTER_VALIDATE_INT);

                if ($producto_id === false || !isset($_SESSION['carrito'][$producto_id])) {
                    continue;
                }

                if ($cantidad === false || $cantidad <= 0) {
                    unset($_SESSION['carrito'][$producto_id]);
                } else {
                    $_SESSION['carrito'][$producto_id] = min($cantidad, 10);
                }
            }
        }

        header('Location: carrito.php?estado=actualizado');
        exit;
    } elseif (isset($_POST['eliminar_producto'])) {
        $producto_id = filter_var($_POST['producto_id'] ?? 0, FILTER_VALIDATE_INT);
        if ($producto_id !== false) {
            unset($_SESSION['carrito'][$producto_id]);
        }
        header('Location: carrito.php?estado=eliminado');
        exit;
    } elseif (isset($_POST['vaciar_carrito'])) {
        $_SESSION['carrito'] = [];
        header('Location: carrito.php?estado=vacio');
        exit;
    } elseif (isset($_POST['finalizar_compra'])) {
        $direccion = trim($_POST['direccion'] ?? '');
        $nombre = trim($_SESSION['usuario_nombre'] ?? '');
        $correo = trim($_SESSION['usuario_correo'] ?? '');
        $productos_compra = cargarProductosCarrito($pdo, $_SESSION['carrito']);

        if (empty($productos_compra)) {
            $mensaje_error = 'El carrito está vacío.';
        } elseif (mb_strlen($direccion) < 5 || mb_strlen($direccion) > 255) {
            $error_direccion = 'Escribe una dirección válida de entre 5 y 255 caracteres.';
        } elseif ($nombre === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $mensaje_error = 'Los datos de la cuenta no son válidos. Vuelve a iniciar sesión.';
        } else {
            try {
                $pdo->beginTransaction();

                $consulta_cliente = $pdo->prepare('SELECT id FROM clientes WHERE correo = ?');
                $consulta_cliente->execute([$correo]);
                $cliente = $consulta_cliente->fetch(PDO::FETCH_ASSOC);

                if ($cliente) {
                    $cliente_id = (int) $cliente['id'];
                    $actualizar_cliente = $pdo->prepare(
                        'UPDATE clientes SET nombre = ?, direccion = ? WHERE id = ?'
                    );
                    $actualizar_cliente->execute([$nombre, $direccion, $cliente_id]);
                } else {
                    $crear_cliente = $pdo->prepare(
                        'INSERT INTO clientes (nombre, correo, direccion) VALUES (?, ?, ?)'
                    );
                    $crear_cliente->execute([$nombre, $correo, $direccion]);
                    $cliente_id = (int) $pdo->lastInsertId();
                }

                $crear_pedido = $pdo->prepare(
                    'INSERT INTO pedidos (cliente_id, producto_id, cantidad) VALUES (?, ?, ?)'
                );

                foreach ($productos_compra as $producto) {
                    $crear_pedido->execute([
                        $cliente_id,
                        (int) $producto['id'],
                        (int) $producto['cantidad']
                    ]);
                }

                $pdo->commit();
                $_SESSION['carrito'] = [];
                header('Location: carrito.php?estado=comprado');
                exit;
            } catch (Throwable $error) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log($error->getMessage());
                $mensaje_error = 'No se pudo registrar la compra. Inténtalo nuevamente.';
            }
        }
    }
}

$productos = cargarProductosCarrito($pdo, $_SESSION['carrito']);
$cantidad_carrito = array_sum(array_column($productos, 'cantidad'));
$total = array_sum(array_column($productos, 'subtotal'));

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Carrito de compras de UBE Store.">
    <title>Carrito de compras | UBE Store</title>
    <link rel="stylesheet" href="css/style.css?v=16">
</head>
<body>
    <header class="site-header cart-header">
        <div class="header-inner">
            <a class="brand" href="index.php" aria-label="UBE Store, inicio">
                <span class="brand-symbol">U</span>
                <span class="brand-name">UBE<span>STORE</span></span>
            </a>
            <div class="header-actions">
                <span class="user-greeting">
                    Hola, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?>
                </span>
                <a href="productos.php" class="auth-button auth-button-outline">Seguir comprando</a>
                <a href="logout.php" class="auth-button auth-button-outline">Cerrar sesión</a>
            </div>
        </div>
        <div class="cart-hero">
            <p class="eyebrow">TU SELECCIÓN</p>
            <h1>Carrito de compras</h1>
            <p>Revisa los productos antes de confirmar el pedido.</p>
        </div>
    </header>

    <main class="container cart-page">
        <?php if ($mensaje_exito !== ''): ?>
            <div class="toast-notification toast-success" data-toast role="status"><span class="toast-icon" aria-hidden="true">✓</span><p><?= htmlspecialchars($mensaje_exito) ?></p><button type="button" data-toast-close aria-label="Cerrar notificación">×</button></div>
        <?php endif; ?>
        <?php if ($mensaje_error !== ''): ?>
            <div class="toast-notification toast-danger" data-toast role="alert"><span class="toast-icon" aria-hidden="true">!</span><p><?= htmlspecialchars($mensaje_error) ?></p><button type="button" data-toast-close aria-label="Cerrar notificación">×</button></div>
        <?php endif; ?>

        <?php if (empty($productos)): ?>
            <section class="empty-state cart-empty">
                <span class="empty-symbol" aria-hidden="true">◇</span>
                <h2>Tu carrito está vacío</h2>
                <p>Explora el catálogo y añade los productos que quieras comprar.</p>
                <a href="productos.php" class="btn-comprar">Explorar productos</a>
            </section>
        <?php else: ?>
            <div class="cart-layout">
                <section class="cart-list" aria-labelledby="cart-title">
                    <div class="cart-section-heading">
                        <div>
                            <p class="eyebrow eyebrow-purple">PRODUCTOS AÑADIDOS</p>
                            <h2 id="cart-title"><?= (int) $cantidad_carrito ?> artículo(s)</h2>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <button type="submit" name="vaciar_carrito" class="text-button">Vaciar carrito</button>
                        </form>
                    </div>

                    <?php foreach ($productos as $producto): ?>
                        <article class="cart-item">
                            <img
                                src="<?= htmlspecialchars($producto['imagen']) ?>"
                                alt="<?= htmlspecialchars($producto['nombre']) ?>"
                                onerror="this.onerror=null; this.src='img/productos/sin-imagen.svg';"
                            >
                            <div class="cart-item-info">
                                <span><?= htmlspecialchars($producto['vendedor_nombre']) ?></span>
                                <h3><?= htmlspecialchars($producto['nombre']) ?></h3>
                                <p>$<?= number_format((float) $producto['precio'], 2) ?> por unidad</p>
                            </div>
                            <form method="POST" class="quantity-form">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                <label for="cantidad-<?= (int) $producto['id'] ?>">Cantidad</label>
                                <div>
                                    <input
                                        type="number"
                                        id="cantidad-<?= (int) $producto['id'] ?>"
                                        name="cantidades[<?= (int) $producto['id'] ?>]"
                                        value="<?= (int) $producto['cantidad'] ?>"
                                        min="0"
                                        max="10"
                                    >
                                    <button type="submit" name="actualizar_carrito">Actualizar</button>
                                </div>
                            </form>
                            <div class="cart-item-total">
                                <strong>$<?= number_format((float) $producto['subtotal'], 2) ?></strong>
                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="producto_id" value="<?= (int) $producto['id'] ?>">
                                    <button type="submit" name="eliminar_producto" class="remove-button">Eliminar</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>

                <aside class="checkout-card">
                    <p class="eyebrow eyebrow-purple">RESUMEN DEL PEDIDO</p>
                    <h2>Total</h2>
                    <div class="checkout-total">
                        <span><?= (int) $cantidad_carrito ?> artículo(s)</span>
                        <strong>$<?= number_format((float) $total, 2) ?></strong>
                    </div>
                    <form method="POST" class="checkout-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <div class="form-group">
                            <label for="cliente">Nombre completo</label>
                            <input id="cliente" type="text" value="<?= htmlspecialchars($_SESSION['usuario_nombre']) ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="correo">Correo electrónico</label>
                            <input id="correo" type="email" value="<?= htmlspecialchars($_SESSION['usuario_correo']) ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="direccion">Dirección de entrega</label>
                            <textarea
                                id="direccion"
                                name="direccion"
                                rows="3"
                                minlength="5"
                                maxlength="255"
                                required
                                placeholder="Ciudad, calle principal y secundaria"
                                autocomplete="street-address"
                                <?= $error_direccion !== '' ? 'class="input-error" aria-invalid="true" aria-describedby="direccion-error"' : '' ?>
                            ><?= htmlspecialchars($_POST['direccion'] ?? '') ?></textarea>
                            <?php if ($error_direccion !== ''): ?>
                                <small id="direccion-error" class="field-error" role="alert"><?= htmlspecialchars($error_direccion) ?></small>
                            <?php endif; ?>
                        </div>
                        <button type="submit" name="finalizar_compra" class="btn-submit">
                            Confirmar compra <span aria-hidden="true">→</span>
                        </button>
                    </form>
                </aside>
            </div>
        <?php endif; ?>
    </main>

    <footer class="site-footer">
        <div class="footer-inner">
            <span class="footer-brand">UBE STORE</span>
            <span>Proyecto académico · Programación Web</span>
        </div>
    </footer>
    <script src="js/script.js?v=5"></script>
</body>
</html>
