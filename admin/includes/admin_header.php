<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Admin - Mini Chic</title>
    
    <!-- Header específico para admin con rutas relativas correctas -->
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/products.css">
    <link rel="stylesheet" href="../assets/css/forms.css">
    <link rel="stylesheet" href="../assets/css/cart.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="nav-brand">
                <h1><i class="fas fa-baby"></i> Mini Chic - Admin</h1>
                <p>Panel de Administración</p>
            </div>
            <nav class="nav-menu">
                <a href="../index.php">Ver Sitio</a>
                <a href="index.php">Dashboard</a>
                <a href="productos.php">Productos</a>
                <a href="pedidos.php">Pedidos</a>
                <a href="mensajes.php">Mensajes</a>
                <a href="logout.php">Salir</a>
            </nav>
        </div>
    </header>
