<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/functions.php';

$producto_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Obtener producto
$stmt = $pdo->prepare("SELECT p.*, c.nombre as categoria_nombre 
                       FROM productos p 
                       LEFT JOIN categorias c ON p.categoria_id = c.id 
                       WHERE p.id = ? AND p.activo = 1");
$stmt->execute([$producto_id]);
$producto = $stmt->fetch();

if (!$producto) {
    header('Location: productos.php');
    exit();
}

$page_title = $producto['nombre'];



include 'includes/header.php';
?>

<main>
    <div class="container">
        <?php if (isset($mensaje)): ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <div class="product-detail">
            <div class="product-image">
                <img src="public/<?php echo $producto['imagen']; ?>" 
                     alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
            </div>
            
            <div class="product-info">
                <h1><?php echo htmlspecialchars($producto['nombre']); ?></h1>
                <p class="category">Categoría: <?php echo htmlspecialchars($producto['categoria_nombre']); ?></p>
                <p class="price"><?php echo formatPrice($producto['precio']); ?></p>
                
                <div class="product-description">
                    <h3>Descripción</h3>
                    <p><?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?></p>
                </div>

                <p class="stock <?php echo ($producto['stock'] <= 5 && $producto['stock'] > 0) ? 'low-stock' : ''; ?>">
                    <?php 
                        if ($producto['stock'] > 0) {
                            echo 'Disponibles: ' . $producto['stock'];
                        } else {
                            echo '<span class="out-of-stock">Agotado</span>';
                        }
                    ?>
                </p>
                
                <?php if (isLoggedIn()): ?>
                    <?php if ($producto['stock'] > 0): ?>
                        <form action="agregar_al_carrito.php" method="POST" class="add-to-cart-form">
                            <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
                            <input type="hidden" name="return_url" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
                            <div class="quantity-selector">
                                <label for="cantidad">Cantidad:</label>
                                <input type="number" id="cantidad" name="cantidad" value="1" min="1" max="<?php echo $producto['stock']; ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-cart-plus"></i> Agregar al Carrito
                            </button>
                        </form>
                    <?php else: ?>
                        <p>Este producto no está disponible actualmente.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                       class="btn btn-primary">Inicia sesión para comprar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<style>
.product-detail {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    margin: 2rem 0;
}

.product-image img {
    width: 100%;
    border-radius: 15px;
}

.product-info h1 {
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.product-description {
    margin: 2rem 0;
}

.add-to-cart-form {
    margin-top: 2rem;
}

.quantity-selector {
    margin-bottom: 1rem;
}

.quantity-selector input {
    width: 80px;
    padding: 0.5rem;
    border: 2px solid #e9ecef;
    border-radius: 5px;
    margin-left: 1rem;
}

@media (max-width: 768px) {
    .product-detail {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
