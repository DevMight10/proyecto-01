<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/functions.php';

// 1. Validar que el usuario haya iniciado sesión
requireLogin();

// 2. Determinar el método de la petición y obtener los datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Petición desde el formulario de detalle del producto
    $producto_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
    $return_url = isset($_POST['return_url']) ? $_POST['return_url'] : 'productos.php';
} else {
    // Petición desde el enlace en la lista de productos
    $producto_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $cantidad = 1;
    $return_url = 'productos.php';
}

// 3. Validar el ID del producto
if ($producto_id <= 0) {
    header('Location: productos.php');
    exit;
}

// 4. Obtener los datos del producto desde la BD
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ? AND activo = 1");
$stmt->execute([$producto_id]);
$producto = $stmt->fetch();


// 5. Si el producto existe, verificar stock y añadirlo al carrito
if ($producto) {
    $cantidad_en_carrito = isset($_SESSION['carrito'][$producto_id]['cantidad']) ? $_SESSION['carrito'][$producto_id]['cantidad'] : 0;

    if (($cantidad_en_carrito + $cantidad) > $producto['stock']) {
        $mensaje = "No hay suficiente stock para agregar la cantidad solicitada.";
    } else {
        addToCart($producto['id'], $producto['nombre'], $producto['precio'], $producto['imagen'], $producto['stock'], $cantidad);

        // --- Sincronizar con la base de datos ---
        $usuario_id = $_SESSION['usuario_id'];
        $nueva_cantidad = $_SESSION['carrito'][$producto_id]['cantidad'];

        $sql_sync = "INSERT INTO carrito_items (usuario_id, producto_id, cantidad) VALUES (?, ?, ?)
                     ON DUPLICATE KEY UPDATE cantidad = ?";
        $stmt_sync = $pdo->prepare($sql_sync);
        $stmt_sync->execute([$usuario_id, $producto_id, $nueva_cantidad, $nueva_cantidad]);
        // --- Fin de la sincronización ---

        $mensaje = "Producto agregado al carrito";
    }
} else {
    $mensaje = "El producto no existe o no está disponible";
}

// 6. Redirigir al usuario de vuelta
// Añadimos un parámetro de mensaje a la URL de retorno
if (strpos($return_url, '?') === false) {
    $return_url .= '?mensaje=' . urlencode($mensaje);
} else {
    $return_url .= '&mensaje=' . urlencode($mensaje);
}

header("Location: " . $return_url);
exit;
?>