<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/functions.php';

$page_title = 'Inicio';

// Obtener productos destacados
$stmt = $pdo->query("SELECT * FROM productos WHERE activo = 1 ORDER BY fecha_creacion DESC LIMIT 6");
$productos_destacados = $stmt->fetchAll();

include 'includes/header.php';
?>

<main>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h2>Bienvenidos a Mini Chic</h2>
                <p>La mejor ropa para tu bebé con amor y cuidado</p>
                <a href="productos.php" class="btn btn-primary">Ver Productos</a>
            </div>
        </div>
    </section>

    <!-- Productos Destacados -->
    <section class="featured-products">
        <div class="container">
            <h2>Productos Destacados</h2>
            <div class="products-grid">
                <?php foreach ($productos_destacados as $producto): ?>
                    <div class="product-card">
                        <img src="uploads/productos/<?php echo $producto['imagen']; ?>" 
                             alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                        <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                        <p class="price"><?php echo formatPrice($producto['precio']); ?></p>
                        <a href="producto_detalle.php?id=<?php echo $producto['id']; ?>" 
                           class="btn btn-secondary">Ver Detalles</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
