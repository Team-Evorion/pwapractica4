<?php
require_once 'conexion.php';

$mensaje_exito = "";
$mensaje_error = "";

// Mostrar mensaje de éxito mediante parámetro GET (evita reenvíos al actualizar)
if (isset($_GET['status']) && $_GET['status'] === 'success') {
    $mensaje_exito = "¡Compra realizada con éxito! Su pedido ha sido registrado correctamente.";
}

// Procesamiento del formulario de compra (Backend PHP seguro)
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

            // 2. Comprobar en PHP que el producto seleccionado exista
            $stmt_prod = $pdo->prepare("SELECT id FROM productos WHERE id = ?");
            $stmt_prod->execute([$producto_id]);
            if (!$stmt_prod->fetch()) {
                throw new Exception("El producto seleccionado no existe en el sistema.");
            }

            // 5. Buscar al cliente por correo antes de registrarlo nuevamente
            $stmt_cli = $pdo->prepare("SELECT id FROM clientes WHERE correo = ?");
            $stmt_cli->execute([$correo_cliente]);
            $cliente = $stmt_cli->fetch(PDO::FETCH_ASSOC);

            if ($cliente) {
                $cliente_id = $cliente['id'];
                // Opcional: Actualizar datos del cliente si es necesario
                $stmt_upd = $pdo->prepare("UPDATE clientes SET nombre = ?, direccion = ? WHERE id = ?");
                $stmt_upd->execute([$nombre_cliente, $direccion_cliente, $cliente_id]);
            } else {
                $stmt_ins = $pdo->prepare("INSERT INTO clientes (nombre, correo, direccion) VALUES (?, ?, ?)");
                $stmt_ins->execute([$nombre_cliente, $correo_cliente, $direccion_cliente]);
                $cliente_id = $pdo->lastInsertId();
            }

            // Registrar el pedido
            $stmt_pedido = $pdo->prepare("INSERT INTO pedidos (cliente_id, producto_id) VALUES (?, ?)");
            $stmt_pedido->execute([$cliente_id, $producto_id]);

            $pdo->commit();
            
            // 1. Redirigir después de registrar la compra (Patrón PRG)
            header("Location: index.php?status=success");
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            // 3. No mostrar al usuario los errores internos de MySQL (se muestra un mensaje genérico seguro)
            $mensaje_error = "Ocurrió un error al procesar su solicitud. Por favor, inténtelo de nuevo más tarde.";
        }
    }
}

// Obtener listado de productos y vendedores
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
    <title>Sistema de Venta de Productos - UBE</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <h1>Sistema de Venta de Productos (U.B.E.)</h1>
        <p>Desarrollado para Programación Web - Práctica 4</p>
    </header>

    <div class="container">
        <?php if (!empty($mensaje_exito)): ?>
            <div class="alert alert-success"><?php echo $mensaje_exito; ?></div>
        <?php endif; ?>
        <?php if (!empty($mensaje_error)): ?>
            <div class="alert alert-danger"><?php echo $mensaje_error; ?></div>
        <?php endif; ?>

        <h2>Catálogo de Productos Disponibles</h2>

        <?php if (empty($productos)): ?>
            <p>No hay productos registrados en la base de datos o verifique la conexión con MySQL.</p>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($productos as $prod): ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($prod['imagen']); ?>" alt="Imagen" class="product-img" onerror="this.src='https://via.placeholder.com/280x180?text=Sin+Imagen'">
                        <div class="product-body">
                            <h3 class="product-title"><?php echo htmlspecialchars($prod['nombre']); ?></h3>
                            <p class="product-desc"><?php echo htmlspecialchars($prod['descripcion']); ?></p>
                            <p style="font-size: 0.85rem; color: #555; margin-bottom: 0.5rem;">Vendedor: <strong><?php echo htmlspecialchars($prod['vendedor_nombre']); ?></strong></p>
                            <div class="product-price">$<?php echo number_format($prod['precio'], 2); ?></div>
                            
                            <!-- 6. Uso de json_encode para evitar que apóstrofes rompan el JavaScript -->
                            <button class="btn-comprar" onclick='abrirModal(<?php echo $prod['id']; ?>, <?php echo json_encode($prod['nombre'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>, <?php echo $prod['precio']; ?>)'>Comprar</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Formulario de Compra -->
    <div id="modalCompra" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="cerrarModal()">&times;</span>
            <h2>Finalizar Compra</h2>
            <p id="infoProductoSeleccionado" style="font-weight: bold; color: var(--primary);"></p>
            
            <form id="formCompra" action="" method="POST" onsubmit="return validarFormulario()">
                <input type="hidden" id="producto_id" name="producto_id">
                
                <div class="form-group">
                    <label for="nombre">Nombre Completo:</label>
                    <input type="text" id="nombre" name="nombre" required placeholder="Ej. Juan Pérez">
                </div>
                
                <div class="form-group">
                    <label for="correo">Correo Electrónico:</label>
                    <input type="email" id="correo" name="correo" required placeholder="correo@ejemplo.com">
                </div>
                
                <div class="form-group">
                    <label for="direccion">Dirección de Entrega:</label>
                    <textarea id="direccion" name="direccion" rows="3" required placeholder="Ciudad, Calle Principal y Secundaria"></textarea>
                </div>
                
                <button type="submit" name="comprar" class="btn-submit">Confirmar y Registrar Pedido</button>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>