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

<link rel="stylesheet" href="assets/css/lista-productos.css">
<link rel="stylesheet" href="assets/css/global.css">

<main>
    <div class="container">
        <?php if (isset($_GET['mensaje'])): ?>
            <?php
            // Si el mensaje contiene "No hay suficiente stock" lo ponemos en rojo
            $tipo = strpos($_GET['mensaje'], 'stock') !== false ? 'error' : 'success';
            ?>
            <div class="notificacion <?php echo $tipo; ?>">
                <?php echo htmlspecialchars($_GET['mensaje']); ?>
            </div>
        <?php endif; ?>

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
                    <img src="public/<?php echo $producto['imagen']; ?>"
                        alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                        <p class="category"><?php echo htmlspecialchars($producto['categoria_nombre']); ?></p>
                        <p class="price"><?php echo formatPrice($producto['precio']); ?></p>
                        <p class="stock <?php echo ($producto['stock'] <= 5 && $producto['stock'] > 0) ? 'low-stock' : ''; ?>">
                            <?php
                            if ($producto['stock'] > 0) {
                                echo 'Disponibles: ' . $producto['stock'];
                            } else {
                                echo '<span class="out-of-stock">Agotado</span>';
                            }
                            ?>
                        </p>
                        <div class="product-actions">
                            <a href="producto_detalle.php?id=<?php echo $producto['id']; ?>"
                                class="btn btn-secondary">Ver Detalles</a>
                            <?php if (isLoggedIn()): ?>
                                <?php if ($producto['stock'] > 0): ?>
                                    <a href="agregar_al_carrito.php?id=<?php echo $producto['id']; ?>"
                                        class="btn btn-primary">
                                        <i class="fas fa-cart-plus"></i> Agregar
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-primary" disabled>Agotado</button>
                                <?php endif; ?>
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

<?php
// Mostrar mensaje de carrito si existe en la URL
if (isset($_GET['mensaje'])) {
    $mensaje = $_GET['mensaje'];

    // Determinar tipo: rojo si contiene "stock", verde si contiene "agregado"
    $tipo = (stripos($mensaje, 'stock') !== false || stripos($mensaje, 'no hay suficiente') !== false) ? 'error' : 'success';

    echo '<div class="notificacion ' . $tipo . '">';
    echo htmlspecialchars($mensaje);
    echo '</div>';
}


?>

<style>
    .notificacion {
        padding: 1rem 1.5rem;
        margin: 1rem 0;
        border-radius: 5px;
        font-weight: bold;
    }

    .notificacion.success {
        background-color: #28a745;
        /* verde */
        color: #fff;
    }

    .notificacion.error {
        background-color: #dc3545;
        /* rojo */
        color: #fff;
    }
</style>