<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/functions.php';

$page_title = 'Productos';

// Filtro por categoría
$categoria_filtro = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$where_clause = "WHERE p.activo = 1";
if ($categoria_filtro) {
    $where_clause .= " AND p.categoria_id = :categoria";
}

$sql = "SELECT p.*, c.nombre as categoria_nombre 
        FROM productos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        $where_clause 
        ORDER BY p.fecha_creacion DESC";

$stmt = $pdo->prepare($sql);
if ($categoria_filtro) {
    $stmt->bindParam(':categoria', $categoria_filtro);
}
$stmt->execute();
$productos = $stmt->fetchAll();

// Obtener categorías para filtro
$stmt_cat = $pdo->query("SELECT * FROM categorias ORDER BY nombre");
$categorias = $stmt_cat->fetchAll();

include 'includes/header.php';
?>

<main>
    <div class="container">
        <h1>Nuestros Productos</h1>
        
        <!-- Filtros -->
        <div class="filters">
            <a href="productos.php" class="filter-btn <?php echo !$categoria_filtro ? 'active' : ''; ?>">
                Todos
            </a>
            <?php foreach ($categorias as $categoria): ?>
                <a href="productos.php?categoria=<?php echo $categoria['id']; ?>" 
                   class="filter-btn <?php echo $categoria_filtro == $categoria['id'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Grid de productos -->
        <div class="products-grid">
            <?php foreach ($productos as $producto): ?>
                <div class="product-card">
                    <img src="uploads/productos/<?php echo $producto['imagen']; ?>" 
                         alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                        <p class="category"><?php echo htmlspecialchars($producto['categoria_nombre']); ?></p>
                        <p class="price"><?php echo formatPrice($producto['precio']); ?></p>
                        <div class="product-actions">
                            <a href="producto_detalle.php?id=<?php echo $producto['id']; ?>" 
                               class="btn btn-secondary">Ver Detalles</a>
                            <?php if (isLoggedIn()): ?>
                                <button onclick="addToCart(<?php echo $producto['id']; ?>)" 
                                        class="btn btn-primary">
                                    <i class="fas fa-cart-plus"></i> Agregar
                                </button>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary">Inicia sesión para comprar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
