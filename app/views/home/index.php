<main>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h2>Bienvenidos a Mini Chic</h2>
                <p>La mejor ropa para tu bebé con amor y cuidado</p>
                <a href="/productos" class="btn btn-primary">Ver Productos</a>
            </div>
        </div>
    </section>

    <!-- Productos Destacados -->
    <section class="featured-products">
        <div class="container">
            <h2>Productos Destacados</h2>
            <div class="products-grid">
                <?php if (!empty($productos_destacados)): ?>
                    <?php foreach ($productos_destacados as $producto): ?>
                        <div class="product-card">
                            <img src="public/uploads/productos/<?php echo htmlspecialchars($producto['imagen']); ?>" 
                                 alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                                <p class="price">Bs. <?php echo number_format($producto['precio'], 2); ?></p>
                                <div class="product-actions">
                                    <a href="/producto/<?php echo $producto['id']; ?>" 
                                       class="btn btn-secondary">Ver Detalles</a>
                                    <?php if (isLoggedIn()): ?>
                                        <form method="POST" action="/agregar-carrito" style="display: inline;">
                                            <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-cart-plus"></i> Agregar
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <a href="/login" class="btn btn-primary">Inicia sesión para comprar</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No hay productos destacados disponibles.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>
