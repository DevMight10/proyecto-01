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

<!-- <link rel="stylesheet" href="assets/css/style.css"> -->
<link rel="stylesheet" href="assets/css/home.css">
<!-- <link rel="stylesheet" href="assets/css/global.css"> -->


<main >
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Ropa adorable para tu <h1 style="color : #007B44">pequeno tesoro</h1> </h1>
                <p>Descubre nuestra colección de ropa para bebés, diseñada con amor y fabricada con los materiales más suaves y seguros.</p>
                <div>
                    <a href="productos.php" class="btn">Ver Productos</a>
                </div>

            </div>

            <div class="hero-img">
                <img src="assets/imgs/bebe.jpg" alt="hero">
            </div>
            <!-- <div class="hero-content">
                <h2>Bienvenidos a Mini Chic</h2>
                <p>La mejor ropa para tu bebé con amor y cuidado dsfa </p>
                <a href="productos.php" class="btn btn-primary">Ver Productos</a>
            </div> -->
        </div>
    </section>

    <section class="detalles-home">
        <div class="container features">
            <div class="feature">
                <span class="icon"><i class="fa-solid fa-truck-fast"></i></span>
                <h3>Envío Gratuito</h3>
                <p>En compras mayores a Bs500</p>
            </div>

            <div class="feature">
                <span class="icon"><i class="fa-solid fa-shield"></i></span>
                <h3>Materiales Seguros</h3>
                <p>Algodón orgánico, sin químicos</p>
            </div>

            <div class="feature">
                <span class="icon"><i class="fa-solid fa-clock"></i></span>
                <h3>Atención 24/7</h3>
                <p>Soporte para todas tus consultas</p>
            </div>
        </div>
    </section>


    <!-- Productos Destacados -->
    <section class="featured-products">
        <div class="container">
            <h2 style="text-align: center;">Productos Destacados</h2>
            <div class="products-grid">
                <?php foreach ($productos_destacados as $producto): ?>
                    <div class="product-card">
                        <img src="public/<?php echo $producto['imagen']; ?>" 
                             alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                        <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                        <p class="price"><?php echo formatPrice($producto['precio']); ?></p>
                        <a href="producto_detalle.php?id=<?php echo $producto['id']; ?>" class="btn">Ver Detalles</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
