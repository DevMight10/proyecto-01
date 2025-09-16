<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Mini Chic - Ropa de Bebé</title>
    
    <link rel="stylesheet" href="/proyecto-01/assets/css/header.css">
    <link rel="stylesheet" href="/proyecto-01/assets/css/global.css">

    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">                                                      
                <h3 class="logo-text">Mini Chic</h3>
            </div>

            <div class="nav-menu">
                <a href="/proyecto-01/index.php">Inicio</a>
                <a href="/proyecto-01/productos.php">Productos</a>
                <a href="/proyecto-01/contacto.php">Contacto</a>
            </div>

            <div class="nav-right">
                <?php if (isLoggedIn()): ?>
                    <a href="/proyecto-01/carrito.php" class="opcion">
                        <i class="fas fa-shopping-cart"></i> 
                        Carrito <?php echo getCartItemCount(); ?>
                    </a>
                    <a href="/proyecto-01/mis_pedidos.php" class="opcion">Mis Pedidos</a>
                    <?php if (isAdmin()): ?>
                        <a href="/proyecto-01/admin/index.php" class="opcion">Admin</a>
                    <?php endif; ?>
                    <a href="/proyecto-01/logout.php" class="opcion">Salir</a>
                <?php else: ?>
                    <a href="/proyecto-01/login.php" id="iniciar-sesion">Iniciar Sesión</a>
                    <a href="/proyecto-01/registro.php" id="registro">Registrarse</a>
                <?php endif; ?>

            </div>
        </div>
    </header>
</body>