<main>
    <div class="container">
        <div class="product-detail">
            <div class="product-image">
                <img src="public/uploads/productos/<?php echo htmlspecialchars($producto['imagen']); ?>" 
                     alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
            </div>
            <div class="product-info">
                <h1><?php echo htmlspecialchars($producto['nombre']); ?></h1>
                <p class="category">Categoría: <?php echo htmlspecialchars($producto['categoria_nombre']); ?></p>
                <p class="price">Bs. <?php echo number_format($producto['precio'], 2); ?></p>
                <div class="description">
                    <h3>Descripción</h3>
                    <p><?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?></p>
                </div>
                
                <?php if (isLoggedIn()): ?>
                    <form method="POST" action="/agregar-carrito" class="add-to-cart-form">
                        <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                        <div class="quantity-selector">
                            <label for="cantidad">Cantidad:</label>
                            <input type="number" id="cantidad" name="cantidad" value="1" min="1" max="10">
                        </div>
                        <button type="submit" class="btn btn-primary btn-large">
                            <i class="fas fa-cart-plus"></i> Agregar al Carrito
                        </button>
                    </form>
                <?php else: ?>
                    <div class="login-prompt">
                        <p>Para comprar este producto necesitas iniciar sesión</p>
                        <a href="/login?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                           class="btn btn-primary btn-large">Iniciar Sesión</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="back-link">
            <a href="/productos" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Productos
            </a>
        </div>
    </div>
</main>
