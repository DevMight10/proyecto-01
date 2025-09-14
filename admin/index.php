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

include '../includes/header.php';
?>

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

<style>
.admin-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 2rem;
    margin: 2rem 0;
}

.stat-card {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.stat-card h3 {
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.admin-menu {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.admin-menu-item {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    text-decoration: none;
    color: var(--text-color);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s;
}

.admin-menu-item:hover {
    transform: translateY(-5px);
}

.admin-menu-item i {
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.admin-menu-item h3 {
    margin-bottom: 0.5rem;
}
</style>

<?php include '../includes/footer.php'; ?>
