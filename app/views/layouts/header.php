<?php
require_once 'includes/auth.php';

function getCartItemCount() {
    $count = 0;
    if (isset($_SESSION['carrito'])) {
        foreach ($_SESSION['carrito'] as $item) {
            $count += $item['cantidad'];
        }
    }
    return $count;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Mini Chic - Ropa de Bebé</title>
    
    <!-- CSS Modular -->
    <link rel="stylesheet" href="public/css/base.css">
    <link rel="stylesheet" href="public/css/header.css">
    <link rel="stylesheet" href="public/css/products.css">
    <link rel="stylesheet" href="public/css/forms.css">
    <link rel="stylesheet" href="public/css/cart.css">
    <link rel="stylesheet" href="public/css/responsive.css">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="nav-brand">
                <h1><i class="fas fa-baby"></i> Mini Chic</h1>
                <p>Ropa adorable para tu bebé</p>
            </div>
            <nav class="nav-menu">
                <a href="/">Inicio</a>
                <a href="/productos">Productos</a>
                <a href="/contacto">Contacto</a>
                <?php if (isLoggedIn()): ?>
                    <a href="/carrito">
                        <i class="fas fa-shopping-cart"></i> 
                        Carrito (<?php echo getCartItemCount(); ?>)
                    </a>
                    <a href="/mis-pedidos">Mis Pedidos</a>
                    <?php if (isAdmin()): ?>
                        <a href="/admin">Admin</a>
                    <?php endif; ?>
                    <a href="/logout">Salir</a>
                <?php else: ?>
                    <a href="/login">Iniciar Sesión</a>
                    <a href="/registro">Registrarse</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
