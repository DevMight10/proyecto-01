<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Mini Chic - Ropa de Bebé</title>
    
    <!-- Reemplazando archivo CSS único por archivos modulares -->
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/products.css">
    <link rel="stylesheet" href="assets/css/forms.css">
    <link rel="stylesheet" href="assets/css/cart.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    
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
                <a href="index.php">Inicio</a>
                <a href="productos.php">Productos</a>
                <a href="contacto.php">Contacto</a>
                <?php if (isLoggedIn()): ?>
                    <a href="carrito.php">
                        <i class="fas fa-shopping-cart"></i> 
                        Carrito (<?php echo getCartItemCount(); ?>)
                    </a>
                    <?php if (isAdmin()): ?>
                        <a href="admin/">Admin</a>
                    <?php endif; ?>
                    <a href="admin/logout.php">Salir</a>
                <?php else: ?>
                    <a href="login.php">Iniciar Sesión</a>
                    <a href="registro.php">Registrarse</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
