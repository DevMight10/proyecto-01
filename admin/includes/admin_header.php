<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Admin - Mini Chic</title>

    <!-- Header específico para admin con rutas relativas correctas -->
    <link rel="stylesheet" href="/proyecto-01/admin/styles/header.css">
    <link rel="stylesheet" href="/proyecto-01/assets/css/global.css">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <header class="header">
        <div class="container">
            <div class="nav-brand">
                <h1>Mini Chic - Admin</h1>
                <p>Panel de Administración</p>
            </div>
            <nav class="nav-menu">
                <a href="/proyecto-01/index.php">Ver Sitio</a>
                <a href="/proyecto-01/admin/index.php">Dashboard</a>
                <a href="/proyecto-01/admin/categorias.php">Categorías</a> 
                <a href="/proyecto-01/admin/productos.php">Productos</a>
                <a href="/proyecto-01/admin/pedidos.php">Pedidos</a>
                <a href="/proyecto-01/admin/mensajes.php">Mensajes</a>
                <a href="/proyecto-01/logout.php">Salir</a>
            </nav>
        </div>
    </header>
</body>