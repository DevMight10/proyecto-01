<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/functions.php';

requireLogin();

$page_title = 'Mis Pedidos';
$usuario_id = $_SESSION['usuario_id'];

if (isset($_POST['cancelar_pedido'])) {
    $pedido_id_a_cancelar = $_POST['pedido_id'];
    $usuario_id = $_SESSION['usuario_id'];

    try {
        $pdo->beginTransaction();

        // 1. Verificar que el pedido pertenece al usuario y está 'pendiente'
        $stmt_check = $pdo->prepare("SELECT id FROM pedidos WHERE id = ? AND usuario_id = ? AND estado = 'pendiente'");
        $stmt_check->execute([$pedido_id_a_cancelar, $usuario_id]);
        
        if (!$stmt_check->fetch()) {
            throw new Exception("No se puede cancelar este pedido.");
        }

        // 2. Obtener los detalles del pedido para reponer stock
        $stmt_detalles = $pdo->prepare("SELECT producto_id, cantidad FROM pedido_detalles WHERE pedido_id = ?");
        $stmt_detalles->execute([$pedido_id_a_cancelar]);
        $detalles = $stmt_detalles->fetchAll();

        // 3. Reponer el stock para cada producto
        foreach ($detalles as $detalle) {
            $stmt_stock = $pdo->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
            $stmt_stock->execute([$detalle['cantidad'], $detalle['producto_id']]);
        }

        // 4. Actualizar el estado del pedido a 'cancelado'
        $stmt_cancel = $pdo->prepare("UPDATE pedidos SET estado = 'cancelado' WHERE id = ?");
        $stmt_cancel->execute([$pedido_id_a_cancelar]);

        $pdo->commit();

        header("Location: mis_pedidos.php?mensaje=Pedido cancelado exitosamente. El stock ha sido restablecido.");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: mis_pedidos.php?error=" . urlencode($e->getMessage()));
        exit;
    }
}

// 1. Consulta mejorada para traer pedidos y sus detalles
$sql = "
    SELECT 
        p.id as pedido_id, p.numero_pedido, p.fecha_pedido, p.total, p.estado,
        pd.cantidad, pd.precio_unitario,
        pr.nombre as producto_nombre, pr.imagen as producto_imagen
    FROM pedidos p
    JOIN pedido_detalles pd ON p.id = pd.pedido_id
    JOIN productos pr ON pd.producto_id = pr.id
    WHERE p.usuario_id = ?
    ORDER BY p.fecha_pedido DESC, p.id ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Agrupar productos por pedido
$pedidos = [];
foreach ($results as $row) {
    $pedido_id = $row['pedido_id'];
    if (!isset($pedidos[$pedido_id])) {
        $pedidos[$pedido_id] = [
            'numero_pedido' => $row['numero_pedido'],
            'fecha_pedido' => $row['fecha_pedido'],
            'total' => $row['total'],
            'estado' => $row['estado'],
            'productos' => []
        ];
    }
    $pedidos[$pedido_id]['productos'][] = [
        'nombre' => $row['producto_nombre'],
        'imagen' => $row['producto_imagen'],
        'cantidad' => $row['cantidad'],
        'precio_unitario' => $row['precio_unitario']
    ];
}

include 'includes/header.php';
?>

<main class="container">
    <h1>Mis Pedidos</h1>

    <?php if (isset($_GET['mensaje'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['mensaje']); ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <div class="accordion">
        <?php if (empty($pedidos)): ?>
            <p>Aún no has realizado ningún pedido.</p>
            <a href="productos.php" class="btn btn-primary">Ver productos</a>
        <?php else: ?>
            <?php foreach ($pedidos as $pedido_id => $pedido): ?>
                <div class="accordion-item">
                    <button class="accordion-header">
                        <div class="order-summary-info">
                            <span class="order-number">Pedido: <?php echo htmlspecialchars($pedido['numero_pedido']); ?></span>
                            <span class="order-date"><?php echo date('d/m/Y', strtotime($pedido['fecha_pedido'])); ?></span>
                        </div>
                        <div class="order-summary-status">
                            <span class="badge badge-<?php echo htmlspecialchars($pedido['estado']); ?>"><?php echo ucfirst(htmlspecialchars($pedido['estado'])); ?></span>
                            <span class="order-total"><?php echo formatPrice($pedido['total']); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </button>
                    <div class="accordion-content">
                        <div class="order-details">
                            <h4>Detalles del Pedido</h4>
                            <?php foreach ($pedido['productos'] as $producto): ?>
                                <div class="product-item">
                                    <img src="public/<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                    <div class="product-info">
                                        <?php echo htmlspecialchars($producto['nombre']); ?>
                                        <small>Cantidad: <?php echo $producto['cantidad']; ?></small>
                                    </div>
                                    <div class="product-price">
                                        <?php echo formatPrice($producto['precio_unitario'] * $producto['cantidad']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if ($pedido['estado'] === 'pendiente'): ?>
                                <div class="cancel-form">
                                    <form method="POST" onsubmit="return confirm('¿Estás seguro de que quieres cancelar este pedido?');">
                                        <input type="hidden" name="pedido_id" value="<?php echo $pedido_id; ?>">
                                        <button type="submit" name="cancelar_pedido" class="btn btn-danger">Cancelar Pedido</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<style>
.accordion { width: 100%; }
.accordion-item { border-bottom: 1px solid #eee; }
.accordion-header { background: #fff; border: none; width: 100%; padding: 1.5rem; text-align: left; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-size: 1rem; }
.accordion-header:hover { background: #f8f9fa; }
.order-summary-info, .order-summary-status { display: flex; align-items: center; gap: 1.5rem; }
.order-number { font-weight: bold; }
.order-total { font-weight: bold; color: var(--primary-color); }
.accordion-header i { transition: transform 0.3s; }
.accordion-header.active i { transform: rotate(180deg); }
.accordion-content { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; background: #fdfdfd; }
.order-details { padding: 1.5rem; }
.product-item { display: flex; align-items: center; gap: 1rem; padding: 1rem 0; border-bottom: 1px solid #f0f0f0; }
.product-item:last-child { border-bottom: none; }
.product-item img { width: 60px; height: 60px; border-radius: 8px; object-fit: cover; }
.product-info { flex-grow: 1; }
.product-info small { display: block; color: #6c757d; }
.cancel-form { text-align: right; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #eee;}
.btn-danger { background-color: #dc3545; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer; }
.btn-danger:hover { background-color: #c82333; }
.badge-pendiente { background-color: #ffc107; color: #333; }
.badge-en_proceso { background-color: #17a2b8; color: white; }
.badge-entregado { background-color: #28a745; color: white; }
.badge-cancelado { background-color: #6c757d; color: white; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const accordionHeaders = document.querySelectorAll('.accordion-header');
    accordionHeaders.forEach(header => {
        header.addEventListener('click', () => {
            const content = header.nextElementSibling;
            header.classList.toggle('active');
            if (content.style.maxHeight) {
                content.style.maxHeight = null;
            } else {
                content.style.maxHeight = content.scrollHeight + 'px';
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>