<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireAdmin();

$page_title = 'Panel de Administración';

// --- Estadísticas básicas ---
$total_productos = $pdo->query("SELECT COUNT(*) FROM productos WHERE activo = 1")->fetchColumn();
$pedidos_pendientes = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente'")->fetchColumn();
$mensajes_nuevos = $pdo->query("SELECT COUNT(*) FROM mensajes WHERE leido = 0")->fetchColumn();

// --- Ventas últimos 7 días ---
$ventas_stmt = $pdo->query("
    SELECT DATE(fecha_pedido) AS dia, SUM(total) AS total_dia
    FROM pedidos
    WHERE fecha_pedido >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(fecha_pedido)
    ORDER BY DATE(fecha_pedido) ASC
");
$ventas = [];
$fechas = [];
while ($row = $ventas_stmt->fetch(PDO::FETCH_ASSOC)) {
    $fechas[] = $row['dia'];
    $ventas[] = $row['total_dia'];
}

// --- Últimos 5 pedidos ---
$pedidos_stmt = $pdo->query("
    SELECT p.id, p.numero_pedido, p.fecha_pedido, p.total, p.estado, u.nombre as cliente
    FROM pedidos p
    JOIN usuarios u ON p.usuario_id = u.id
    ORDER BY p.fecha_pedido DESC
    LIMIT 5
");
$ultimos_pedidos = $pedidos_stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/admin_header.php';
?>

<main class="container">
    <h1>Panel de Administración</h1>

    <div class="admin-stats">
        <div class="stat-card">
            <h3><?= count($ultimos_pedidos) ?></h3>
            <p>Categorías</p>
            <a href="categorias.php" class="btn btn-primary">Ver Categorías</a>
        </div>
        <div class="stat-card">
            <h3><?= $total_productos ?></h3>
            <p>Productos Activos</p>
            <a href="productos.php" class="btn btn-primary">Gestionar</a>
        </div>
        <div class="stat-card">
            <h3><?= $pedidos_pendientes ?></h3>
            <p>Pedidos Pendientes</p>
            <a href="pedidos.php" class="btn btn-primary">Ver Pedidos</a>
        </div>
        <div class="stat-card">
            <h3><?= $mensajes_nuevos ?></h3>
            <p>Mensajes Nuevos</p>
            <a href="mensajes.php" class="btn btn-primary">Ver Mensajes</a>
        </div>
    </div>

    <div class="chart-container">
        <h2>Ventas Últimos 7 Días</h2>
        <canvas id="ventasChart"></canvas>
    </div>

    <div class="recent-orders">
        <h2>Últimos 5 Pedidos</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ultimos_pedidos as $pedido): ?>
                    <tr>
                        <td><?= htmlspecialchars($pedido['numero_pedido']) ?></td>
                        <td><?= htmlspecialchars($pedido['cliente']) ?></td>
                        <td><?= date('d/m/Y', strtotime($pedido['fecha_pedido'])) ?></td>
                        <td>$<?= number_format($pedido['total'], 2) ?></td>
                        <td>
                            <span class="badge badge-<?= $pedido['estado'] ?>"><?= htmlspecialchars(formatOrderStatus($pedido['estado'])) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .chart-container {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
    }

    .recent-orders table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }

    .recent-orders th,
    .recent-orders td {
        padding: 0.75rem;
        border-bottom: 1px solid #eee;
    }

    .badge-pendiente {
        background-color: #ffc107;
        color: #333;
        padding: 0.3em 0.6em;
        border-radius: 0.25rem;
    }

    .badge-en_proceso {
        background-color: #17a2b8;
        color: white;
        padding: 0.3em 0.6em;
        border-radius: 0.25rem;
    }

    .badge-entregado {
        background-color: #28a745;
        color: white;
        padding: 0.3em 0.6em;
        border-radius: 0.25rem;
    }

    .badge-cancelado {
        background-color: #6c757d;
        color: white;
        padding: 0.3em 0.6em;
        border-radius: 0.25rem;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('ventasChart').getContext('2d');
    const ventasChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($fechas) ?>,
            datasets: [{
                label: 'Ventas ($)',
                data: <?= json_encode($ventas) ?>,
                backgroundColor: 'rgba(40, 167, 69, 0.7)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

<?php include '../includes/footer.php'; ?>