<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/functions.php';

requireLogin();

$page_title = 'Carrito de Compras';

// Manejar acciones del carrito
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_quantity'])) {
        $producto_id = $_POST['producto_id'];
        $nueva_cantidad = (int)$_POST['cantidad'];
        
        if ($nueva_cantidad > 0) {
            $_SESSION['carrito'][$producto_id]['cantidad'] = $nueva_cantidad;
        } else {
            unset($_SESSION['carrito'][$producto_id]);
        }
    }
    
    if (isset($_POST['remove_item'])) {
        $producto_id = $_POST['producto_id'];
        unset($_SESSION['carrito'][$producto_id]);
    }
    
    if (isset($_POST['clear_cart'])) {
        unset($_SESSION['carrito']);
    }
}

$carrito_vacio = !isset($_SESSION['carrito']) || empty($_SESSION['carrito']);

include 'includes/header.php';
?>

<main>
    <div class="container">
        <h1>Carrito de Compras</h1>
        
        <?php if ($carrito_vacio): ?>
            <div class="empty-cart">
                <p>Tu carrito está vacío</p>
                <a href="productos.php" class="btn btn-primary">Continuar Comprando</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($_SESSION['carrito'] as $producto_id => $item): ?>
                    <div class="cart-item">
                        <img src="uploads/productos/<?php echo $item['imagen']; ?>" 
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
                                           min="1" max="10">
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
                        Confirmar Pedido
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
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
</style>

<?php include 'includes/footer.php'; ?>
