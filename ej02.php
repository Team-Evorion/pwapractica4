<?php
// Configuración de la conexión a la base de datos (PDO)
$host = 'localhost';
$dbname = 'tienda_ube';
$username = 'root';
$password = '';

$mensaje_exito = "";
$mensaje_error = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $mensaje_error = "Error de conexión: " . $e->getMessage();
}

// Procesar la compra cuando se envíe el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comprar'])) {
    $nombre_cliente = trim($_POST['nombre']);
    $correo_cliente = trim($_POST['correo']);
    $direccion_cliente = trim($_POST['direccion']);
    $producto_id = intval($_POST['producto_id']);

    // Validaciones básicas del lado del servidor
    if (empty($nombre_cliente) || empty($correo_cliente) || empty($direccion_cliente) || $producto_id <= 0) {
        $mensaje_error = "Por favor, complete todos los campos obligatorios del formulario de compra.";
    } elseif (!filter_var($correo_cliente, FILTER_VALIDATE_EMAIL)) {
        $mensaje_error = "El formato del correo electrónico no es válido.";
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Insertar o verificar cliente (aquí lo registramos como nuevo o existente)
            $stmt = $pdo->prepare("INSERT INTO clientes (nombre, correo, direccion) VALUES (?, ?, ?)");
            $stmt->execute([$nombre_cliente, $correo_cliente, $direccion_cliente]);
            $cliente_id = $pdo->lastInsertId();

            // 2. Registrar el pedido en la base de datos
            $stmt_pedido = $pdo->prepare("INSERT INTO pedidos (cliente_id, producto_id) VALUES (?, ?)");
            $stmt_pedido->execute([$cliente_id, $producto_id]);

            $pdo->commit();
            $mensaje_exito = "¡Compra realizada con éxito! Su pedido ha sido registrado correctamente.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $mensaje_error = "Error al procesar la compra en la base de datos: " . $e->getMessage();
        }
    }
}

// Recuperar productos y sus vendedores de la base de datos
$productos = [];
try {
    $stmt = $pdo->query("SELECT p.*, v.nombre AS vendedor_nombre, v.correo AS vendedor_correo FROM productos p JOIN vendedores v ON p.vendedor_id = v.id");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Si la BD está vacía o hay error al consultar
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Venta de Productos - UBE</title>
    <style>
        :root {
            --primary: #0056b3;
            --primary-dark: #004085;
            --accent: #28a745;
            --bg-light: #f8f9fa;
            --text-dark: #333;
            --white: #ffffff;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            margin: 0;
            padding: 0;
        }
        header {
            background-color: var(--primary);
            color: var(--white);
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        .product-card {
            background: var(--white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            transition: transform 0.2s;
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
        .product-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background-color: #e9ecef;
        }
        .product-body {
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .product-title {
            font-size: 1.25rem;
            margin: 0 0 0.5rem 0;
            color: var(--primary);
        }
        .product-desc {
            font-size: 0.95rem;
            color: #6c757d;
            flex-grow: 1;
            margin-bottom: 1rem;
        }
        .product-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--accent);
            margin-bottom: 1rem;
        }
        .btn-comprar {
            background-color: var(--primary);
            color: var(--white);
            border: none;
            padding: 0.75rem;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            text-align: center;
            transition: background 0.2s;
        }
        .btn-comprar:hover {
            background-color: var(--primary-dark);
        }

        /* Modal del Formulario de Compra */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: var(--white);
            padding: 2rem;
            border-radius: 8px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative;
        }
        .close-btn {
            position: absolute;
            top: 10px; right: 15px;
            font-size: 1.5rem;
            cursor: pointer;
            color: #aaa;
        }
        .close-btn:hover { color: var(--text-dark); }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn-submit {
            background-color: var(--accent);
            color: var(--white);
            border: none;
            padding: 0.75rem;
            width: 100%;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-submit:hover { background-color: #218838; }
    </style>
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
                        <img src="<?php echo htmlspecialchars($prod['imagen']); ?>" alt="Imagen de producto" class="product-img" onerror="this.src='https://via.placeholder.com/280x180?text=Sin+Imagen'">
                        <div class="product-body">
                            <h3 class="product-title"><?php echo htmlspecialchars($prod['nombre']); ?></h3>
                            <p class="product-desc"><?php echo htmlspecialchars($prod['descripcion']); ?></p>
                            <p style="font-size: 0.85rem; color: #555; margin-bottom: 0.5rem;">Vendedor: <strong><?php echo htmlspecialchars($prod['vendedor_nombre']); ?></strong></p>
                            <div class="product-price">$<?php echo number_format($prod['precio'], 2); ?></div>
                            <button class="btn-comprar" onclick="abrirModal(<?php echo $prod['id']; ?>, '<?php echo htmlspecialchars($prod['nombre'], ENT_QUOTES); ?>', <?php echo $prod['precio']; ?>)">Comprar</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Formulario de Datos del Cliente -->
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

    <script>
        // Funciones de interacción (JavaScript) para el modal de compra
        function abrirModal(id, nombre, precio) {
            document.getElementById('producto_id').value = id;
            document.getElementById('infoProductoSeleccionado').innerText = `Producto: ${nombre} - $${precio.toFixed(2)}`;
            document.getElementById('modalCompra').style.display = 'flex';
        }

        function cerrarModal() {
            document.getElementById('modalCompra').style.display = 'none';
        }

        // Validación del lado del cliente antes de enviar
        function validarFormulario() {
            const nombre = document.getElementById('nombre').value.trim();
            const correo = document.getElementById('correo').value.trim();
            const direccion = document.getElementById('direccion').value.trim();

            if (nombre === "" || correo === "" || direccion === "") {
                alert("Por favor, complete todos los campos del formulario.");
                return false;
            }

            // Validación simple de correo con JS
            const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!regexCorreo.test(correo)) {
                alert("Por favor, ingrese un correo electrónico válido.");
                return false;
            }

            return true;
        }

        // Cerrar modal haciendo clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById('modalCompra');
            if (event.target == modal) {
                cerrarModal();
            }
        }
    </script>
</body>
</html>