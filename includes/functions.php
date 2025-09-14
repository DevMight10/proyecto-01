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
    $count = 0;
    if (isset($_SESSION['carrito'])) {
        foreach ($_SESSION['carrito'] as $item) {
            $count += $item['cantidad'];
        }
    }
    return $count;
}

function addToCart($producto_id, $nombre, $precio, $imagen, $cantidad = 1) {
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
            'cantidad' => $cantidad
        );
    }
}
?>
