<?php
function generateOrderNumber() {
    return 'MC' . date('Ymd') . rand(1000, 9999);
}

function formatPrice($price) {
    return 'Bs. ' . number_format($price, 2);
}

function getCartTotal() {
    $total = 0;
    if (isset($_SESSION['carrito'])) {
        foreach ($_SESSION['carrito'] as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }
    }
    return $total;
}

function getCartItemCount() {
    return isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0;
}

function addToCart($producto_id, $nombre, $precio, $imagen, $stock, $cantidad = 1) {
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = array();
    }
    
    if (isset($_SESSION['carrito'][$producto_id])) {
        $_SESSION['carrito'][$producto_id]['cantidad'] += $cantidad;
    } else {
        $_SESSION['carrito'][$producto_id] = array(
            'nombre' => $nombre,
            'precio' => $precio,
            'imagen' => $imagen,
            'cantidad' => $cantidad,
            'stock' => $stock
        );
    }
}
?>
