<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

$page_title = 'Gestionar Pedidos';

// Procesar actualización de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pedido_id']) && isset($_POST['estado'])) {
    $pedido_id = $_POST['pedido_id'];
    $nuevo_estado = $_POST['estado'];

    // El admin solo puede cambiar a "en proceso" o "entregado" o "pendiente"
    if (!in_array($nuevo_estado, ['pendiente', 'en_proceso', 'entregado'])) {
        $error = "Acción no permitida.";
    } else {
        $stmt = $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        if ($stmt->execute([$nuevo_estado, $pedido_id])) {
            header("Location: pedidos.php?mensaje=Estado del pedido actualizado.");
            exit;
        } else {
            $error = "Error al actualizar el estado.";
        }
    }
}

// Obtener todos los pedidos con información del usuario
$stmt = $pdo->query("SELECT p.*, u.nombre as cliente_nombre, u.email as cliente_email FROM pedidos p JOIN usuarios u ON p.usuario_id = u.id ORDER BY p.fecha_pedido DESC");
$pedidos = $stmt->fetchAll();

include 'includes/admin_header.php';
?>

<main class="container">
    <h1>Gestión de Pedidos</h1>
    
    <?php if (isset($_GET['mensaje'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_GET['mensaje']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID Pedido</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $pedido): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pedido['numero_pedido']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($pedido['cliente_nombre']); ?><br>
                            <small><?php echo htmlspecialchars($pedido['cliente_email']); ?></small>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></td>
                        <td><?php echo formatPrice($pedido['total']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo htmlspecialchars($pedido['estado']); ?>">
                                <?php echo ucfirst($pedido['estado']); ?>
                            </span>
                        </td>
                        <td>
                            <!-- Formulario para actualizar estado -->
                            <form action="pedidos.php" method="POST" style="display: inline-flex; align-items: center; gap: 5px;">
                                <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                                <select name="estado" class="form-control form-control-sm">
                                    <option value="pendiente" <?php echo ($pedido['estado'] == 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                    <option value="en_proceso" <?php echo ($pedido['estado'] == 'en_proceso') ? 'selected' : ''; ?>>En Proceso</option>
                                    <option value="entregado" <?php echo ($pedido['estado'] == 'entregado') ? 'selected' : ''; ?>>Entregado</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">Actualizar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
