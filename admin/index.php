<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireAdmin();

$page_title = 'Panel de Administración';

// Estadísticas básicas
$stmt = $pdo->query("SELECT COUNT(*) as total FROM productos WHERE activo = 1");
$total_productos = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as total FROM pedidos WHERE estado = 'pendiente'");
$pedidos_pendientes = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as total FROM mensajes WHERE leido = 0");
$mensajes_nuevos = $stmt->fetchColumn();

include 'includes/admin_header.php';
?>

<link rel="stylesheet" href="/proyecto-01/admin/styles/index.css">


<main>
    <div class="container">
        <h1>Panel de Administración</h1>
        
        <div class="admin-stats">
            <div class="stat-card">
                <h3><?php echo $total_productos; ?></h3>
                <p>Productos Activos</p>
                <a href="productos.php" class="btn btn-primary">Gestionar</a>
            </div>
            
            <div class="stat-card">
                <h3><?php echo $pedidos_pendientes; ?></h3>
                <p>Pedidos Pendientes</p>
                <a href="pedidos.php" class="btn btn-primary">Ver Pedidos</a>
            </div>
            
            <div class="stat-card">
                <h3><?php echo $mensajes_nuevos; ?></h3>
                <p>Mensajes Nuevos</p>
                <a href="mensajes.php" class="btn btn-primary">Ver Mensajes</a>
            </div>
        </div>
        
        <div class="admin-menu">
            <a href="productos.php" class="admin-menu-item">
                <i class="fas fa-box"></i>
                <h3>Gestionar Productos</h3>
                <p>Agregar, editar y eliminar productos</p>
            </a>
            
            <a href="pedidos.php" class="admin-menu-item">
                <i class="fas fa-shopping-bag"></i>
                <h3>Gestionar Pedidos</h3>
                <p>Ver y actualizar estados de pedidos</p>
            </a>
            
            <a href="mensajes.php" class="admin-menu-item">
                <i class="fas fa-envelope"></i>
                <h3>Mensajes de Contacto</h3>
                <p>Ver consultas y sugerencias</p>
            </a>
        </div>
    </div>
</main>


<?php include 'includes/admin_footer.php'; ?>  
