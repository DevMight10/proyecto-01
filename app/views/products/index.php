<main>
    <div class="container">
        <h1>Nuestros Productos</h1>
        
        <!-- Filtros -->
        <div class="filters">
            <a href="/productos" class="filter-btn <?php echo !$categoria_filtro ? 'active' : ''; ?>">
                Todos
            </a>
            <?php foreach ($categorias as $categoria): ?>
                <a href="/productos?categoria=<?php echo $categoria['id']; ?>" 
                   class="filter-btn <?php echo $categoria_filtro == $categoria['id'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Grid de productos -->
        <div class="products-grid">
            <?php if (!empty($productos)): ?>
                <?php foreach ($productos as $producto): ?>
                    <div class="product-card">
                        <img src="public/uploads/productos/<?php echo htmlspecialchars($producto['imagen']); ?>" 
                             alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                            <p class="category"><?php echo htmlspecialchars($producto['categoria_nombre']); ?></p>
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
                <p>No hay productos disponibles en esta categoría.</p>
            <?php endif; ?>
        </div>
    </div>
</main>
