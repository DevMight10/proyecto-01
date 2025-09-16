<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/functions.php';

requireLogin();

$page_title = 'Carrito de Compras';

// Manejar acciones del carrito y sincronizar con la BD
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario_id = $_SESSION['usuario_id'];

    // --- Acción: Actualizar Cantidad ---
    if (isset($_POST['update_quantity'])) {
        $producto_id = $_POST['producto_id'];
        $nueva_cantidad = (int)$_POST['cantidad'];

        // Obtener stock del producto
        $stmt_stock = $pdo->prepare("SELECT stock FROM productos WHERE id = ?");
        $stmt_stock->execute([$producto_id]);
        $stock_disponible = $stmt_stock->fetchColumn();

        if ($nueva_cantidad > $stock_disponible) {
            $_SESSION['mensaje_error'] = "No hay suficiente stock para \"{$_SESSION['carrito'][$producto_id]['nombre']}\". Disponibles: {$stock_disponible}.";
        } elseif ($nueva_cantidad > 0) {
            $_SESSION['carrito'][$producto_id]['cantidad'] = $nueva_cantidad;
            // Sincronizar con BD
            $stmt = $pdo->prepare("UPDATE carrito_items SET cantidad = ? WHERE usuario_id = ? AND producto_id = ?");
            $stmt->execute([$nueva_cantidad, $usuario_id, $producto_id]);

            $_SESSION['mensaje'] = "Se ha actualizado la cantidad de \"{$_SESSION['carrito'][$producto_id]['nombre']}\" a {$nueva_cantidad} unidades.";
        } else {
            unset($_SESSION['carrito'][$producto_id]);
            // Sincronizar con BD
            $stmt = $pdo->prepare("DELETE FROM carrito_items WHERE usuario_id = ? AND producto_id = ?");
            $stmt->execute([$usuario_id, $producto_id]);
        }
    }

    // --- Acción: Eliminar Producto ---

    if (isset($_POST['remove_item'])) {
        $producto_id = $_POST['producto_id'];

        // Guardar el nombre antes de eliminar
        $nombre_producto = $_SESSION['carrito'][$producto_id]['nombre'] ?? 'Producto';

        unset($_SESSION['carrito'][$producto_id]);

        // Sincronizar con BD
        $stmt = $pdo->prepare("DELETE FROM carrito_items WHERE usuario_id = ? AND producto_id = ?");
        $stmt->execute([$usuario_id, $producto_id]);

        $_SESSION['mensaje'] = "El producto \"{$nombre_producto}\" se ha eliminado del carrito.";
    }


    // --- Acción: Vaciar Carrito ---

    if (isset($_POST['clear_cart'])) {
        unset($_SESSION['carrito']);

        // Sincronizar con BD
        $stmt = $pdo->prepare("DELETE FROM carrito_items WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);

        $_SESSION['mensaje'] = "Se ha vaciado todo el carrito.";
    }


    // Redirigir a la misma página para evitar reenvío de formulario
    header('Location: carrito.php');
    exit;
}

$carrito_vacio = !isset($_SESSION['carrito']) || empty($_SESSION['carrito']);

// Obtener stock para los productos en el carrito
if (!$carrito_vacio) {
    $product_ids = array_keys($_SESSION['carrito']);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));

    $stmt_stocks = $pdo->prepare("SELECT id, stock FROM productos WHERE id IN ($placeholders)");
    $stmt_stocks->execute($product_ids);
    $stocks = $stmt_stocks->fetchAll(PDO::FETCH_KEY_PAIR);
}


include 'includes/header.php';
?>

<main>
    <div class="container">
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['mensaje']; ?>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>

        <h1>Carrito de Compras</h1>

        <?php if (isset($_SESSION['mensaje_error'])):
        ?><div class="alert alert-danger"><?php echo $_SESSION['mensaje_error']; ?></div>
            <?php unset($_SESSION['mensaje_error']); ?>
        <?php endif; ?>



        <?php if ($carrito_vacio): ?>
            <div class="empty-cart">
                <p>Tu carrito está vacío</p>
                <a href="productos.php" class="btn btn-primary">Continuar Comprando</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($_SESSION['carrito'] as $producto_id => $item):
                    // Check if stock information is available for the current product
                    $max_quantity = $stocks[$producto_id] ?? 1; // Default to 1 if stock not found
                ?>
                    <div class="cart-item">
                        <img src="public/<?php echo $item['imagen']; ?>"
                            alt="<?php echo htmlspecialchars($item['nombre']); ?>">

                        <div class="cart-item-info">
                            <h3><?php echo htmlspecialchars($item['nombre']); ?></h3>
                            <p class="price"><?php echo formatPrice($item['precio']); ?></p>
                        </div>

                        <div class="cart-item-actions">
                            <form method="POST" class="quantity-form">
                                <input type="hidden" name="producto_id" value="<?php echo $producto_id; ?>">
                                <div class="quantity-controls">
                                    <input type="number" name="cantidad" value="<?php echo $item['cantidad']; ?>"
                                        min="1" max="<?php echo $max_quantity; ?>">
                                    <button type="submit" name="update_quantity" class="btn btn-secondary">
                                        Actualizar
                                    </button>
                                </div>
                            </form>

                            <form method="POST" class="remove-form">
                                <input type="hidden" name="producto_id" value="<?php echo $producto_id; ?>">
                                <button type="submit" name="remove_item" class="btn btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>

                        <div class="item-total">
                            <?php echo formatPrice($item['precio'] * $item['cantidad']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <div class="cart-total">
                    <h3>Total: <?php echo formatPrice(getCartTotal()); ?></h3>
                </div>

                <div class="cart-actions">
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="clear_cart" class="btn btn-secondary">
                            Vaciar Carrito
                        </button>
                    </form>

                    <a href="confirmar_pedido.php" class="btn btn-primary">
                        Continuar
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
    .empty-cart {
        text-align: center;
        padding: 3rem;
    }

    .cart-items {
        margin-bottom: 2rem;
    }

    .cart-item {
        display: flex;
        align-items: center;
        background: white;
        padding: 1rem;
        border-radius: 10px;
        margin-bottom: 1rem;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .cart-item img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 10px;
        margin-right: 1rem;
    }

    .cart-item-info {
        flex: 1;
    }

    .cart-item-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .quantity-controls {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .quantity-controls input {
        width: 60px;
        padding: 0.25rem;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .item-total {
        font-weight: bold;
        color: var(--primary-color);
        margin-left: 1rem;
    }

    .cart-summary {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .cart-total {
        text-align: center;
        margin-bottom: 1rem;
    }

    .cart-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
    }

    .btn-danger {
        background-color: var(--error);
        color: white;
    }

    .btn-danger:hover {
        background-color: #c82333;
    }

    .alert {
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 5px;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
</style>

<?php include 'includes/footer.php'; ?>