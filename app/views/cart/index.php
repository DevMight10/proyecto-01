<main>
    <div class="container">
        <h1>Carrito de Compras</h1>
        
        <?php if (!empty($carrito)): ?>
            <div class="cart-items">
                <?php foreach ($carrito as $producto_id => $item): ?>
                    <div class="cart-item">
                        <img src="public/uploads/productos/<?php echo htmlspecialchars($item['imagen']); ?>" 
                             alt="<?php echo htmlspecialchars($item['nombre']); ?>">
                        <div class="cart-item-info">
                            <h3><?php echo htmlspecialchars($item['nombre']); ?></h3>
                            <p class="price">Bs. <?php echo number_format($item['precio'], 2); ?></p>
                        </div>
                        <div class="cart-item-actions">
                            <form method="POST" action="/carrito/actualizar" class="quantity-form">
                                <input type="hidden" name="producto_id" value="<?php echo $producto_id; ?>">
                                <div class="quantity-controls">
                                    <button type="button" class="quantity-btn" onclick="decreaseQuantity(this)">-</button>
                                    <input type="number" name="cantidad" value="<?php echo $item['cantidad']; ?>" 
                                           min="1" max="10" onchange="this.form.submit()">
                                    <button type="button" class="quantity-btn" onclick="increaseQuantity(this)">+</button>
                                </div>
                            </form>
                            <p class="subtotal">Subtotal: Bs. <?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></p>
                            <a href="/carrito/eliminar?id=<?php echo $producto_id; ?>" 
                               class="btn btn-danger btn-small" 
                               onclick="return confirm('¿Eliminar este producto del carrito?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-summary">
                <div class="total">
                    <h3>Total: Bs. <?php echo number_format($total, 2); ?></h3>
                </div>
                <div class="cart-actions">
                    <a href="/productos" class="btn btn-secondary">Seguir Comprando</a>
                    <a href="/carrito/limpiar" class="btn btn-outline" 
                       onclick="return confirm('¿Vaciar todo el carrito?')">Vaciar Carrito</a>
                    <a href="/confirmar-pedido" class="btn btn-primary btn-large">Confirmar Pedido</a>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart empty-cart-icon"></i>
                <h2>Tu carrito está vacío</h2>
                <p>Agrega algunos productos para comenzar tu compra</p>
                <a href="/productos" class="btn btn-primary">Ver Productos</a>
            </div>
        <?php endif; ?>
    </div>
</main>
