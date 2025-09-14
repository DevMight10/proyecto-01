<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/functions.php';

requireLogin();

$page_title = 'Confirmar Pedido';

if (empty($_SESSION['carrito'])) {
    header('Location: productos.php');
    exit();
}

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirmar_pedido'])) {
    try {
        $pdo->beginTransaction();
        
        // Crear pedido
        $numero_pedido = generateOrderNumber();
        $total = getCartTotal();
        $usuario_id = $_SESSION['usuario_id'];
        
        $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, numero_pedido, total) VALUES (?, ?, ?)");
        $stmt->execute([$usuario_id, $numero_pedido, $total]);
        $pedido_id = $pdo->lastInsertId();
        
        // Agregar detalles del pedido
        foreach ($_SESSION['carrito'] as $producto_id => $item) {
            $subtotal = $item['precio'] * $item['cantidad'];
            $stmt = $pdo->prepare("INSERT INTO pedido_detalles (pedido_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$pedido_id, $producto_id, $item['cantidad'], $item['precio'], $subtotal]);
        }
        
        $pdo->commit();
        
        // Limpiar carrito
        unset($_SESSION['carrito']);
        
        $mensaje = "Pedido confirmado exitosamente. Número de pedido: $numero_pedido";
        
    } catch (Exception $e) {
        $pdo->rollback();
        $mensaje = "Error al procesar el pedido. Inténtalo nuevamente.";
    }
}

include 'includes/header.php';
?>

<main>
    <div class="container">
        <h1>Confirmar Pedido</h1>
        
        <?php if ($mensaje): ?>
            <div class="alert <?php echo strpos($mensaje, 'exitosamente') !== false ? 'alert-success' : 'alert-error'; ?>">
                <?php echo $mensaje; ?>
            </div>
            
            <?php if (strpos($mensaje, 'exitosamente') !== false): ?>
                <div class="order-success">
                    <h2>¡Gracias por tu pedido!</h2>
                    <p>Nos pondremos en contacto contigo pronto para coordinar el pago y la entrega.</p>
                    <a href="productos.php" class="btn btn-primary">Continuar Comprando</a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="order-summary">
                <h2>Resumen del Pedido</h2>
                
                <div class="order-items">
                    <?php foreach ($_SESSION['carrito'] as $producto_id => $item): ?>
                        <div class="order-item">
                            <img src="uploads/productos/<?php echo $item['imagen']; ?>" 
                                 alt="<?php echo htmlspecialchars($item['nombre']); ?>">
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['nombre']); ?></h3>
                                <p>Cantidad: <?php echo $item['cantidad']; ?></p>
                                <p>Precio: <?php echo formatPrice($item['precio']); ?></p>
                            </div>
                            <div class="item-total">
                                <?php echo formatPrice($item['precio'] * $item['cantidad']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-total">
                    <h3>Total: <?php echo formatPrice(getCartTotal()); ?></h3>
                </div>
                
                <div class="order-info">
                    <h3>Información Importante</h3>
                    <ul>
                        <li>Tu pedido será procesado y nos pondremos en contacto contigo</li>
                        <li>Coordinaremos el método de pago y entrega</li>
                        <li>El estado de tu pedido será actualizado en nuestro sistema</li>
                    </ul>
                </div>
                
                <form method="POST" class="confirm-form">
                    <button type="submit" name="confirmar_pedido" class="btn btn-primary">
                        Confirmar Pedido
                    </button>
                    <a href="carrito.php" class="btn btn-secondary">Volver al Carrito</a>
                </form>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
.order-summary {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.order-items {
    margin: 2rem 0;
}

.order-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #eee;
}

.order-item img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 10px;
    margin-right: 1rem;
}

.item-details {
    flex: 1;
}

.item-total {
    font-weight: bold;
    color: var(--primary-color);
}

.order-total {
    text-align: center;
    padding: 1rem;
    background-color: var(--light-bg);
    border-radius: 10px;
    margin: 2rem 0;
}

.order-info {
    margin: 2rem 0;
}

.order-info ul {
    list-style-type: none;
    padding-left: 0;
}

.order-info li {
    padding: 0.5rem 0;
    border-left: 3px solid var(--primary-color);
    padding-left: 1rem;
    margin-bottom: 0.5rem;
}

.confirm-form {
    text-align: center;
}

.confirm-form .btn {
    margin: 0 0.5rem;
}

.order-success {
    text-align: center;
    padding: 2rem;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
</style>

<?php include 'includes/footer.php'; ?>
