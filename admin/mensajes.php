<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

$page_title = 'Ver Mensajes';

// Lógica para eliminar un mensaje
if (isset($_GET['eliminar']) && isset($_GET['id'])) {
    $id_a_eliminar = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM mensajes WHERE id = ?");
    if ($stmt->execute([$id_a_eliminar])) {
        header("Location: mensajes.php?mensaje=Mensaje eliminado con éxito");
        exit;
    }
}

// Lógica para marcar como leído/no leído
if (isset($_GET['toggle_leido']) && isset($_GET['id'])) {
    $id_a_marcar = $_GET['id'];
    $estado_actual = $_GET['estado'];
    $nuevo_estado = $estado_actual ? 0 : 1;
    
    $stmt = $pdo->prepare("UPDATE mensajes SET leido = ? WHERE id = ?");
    if ($stmt->execute([$nuevo_estado, $id_a_marcar])) {
        header("Location: mensajes.php");
        exit;
    }
}


// Obtener todos los mensajes
$stmt = $pdo->query("SELECT * FROM mensajes ORDER BY fecha_envio DESC");
$mensajes = $stmt->fetchAll();

include 'includes/admin_header.php';
?>

<link rel="stylesheet" href="/proyecto-01/admin/styles/mensaje.css">

<main class="container">
    <h1>Mensajes de Contacto</h1>
    
    <?php if (isset($_GET['mensaje'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_GET['mensaje']); ?>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>De</th>
                    <th>Asunto</th>
                    <th>Mensaje</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mensajes as $mensaje): ?>
                    <tr class="<?php echo $mensaje['leido'] ? 'mensaje-leido' : 'mensaje-nuevo'; ?>">
                        <td>
                            <?php echo htmlspecialchars($mensaje['nombre']); ?><br>
                            <small><?php echo htmlspecialchars($mensaje['email']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($mensaje['asunto']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($mensaje['mensaje'])); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($mensaje['fecha_envio'])); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $mensaje['leido'] ? 'secondary' : 'success'; ?>">
                                <?php echo $mensaje['leido'] ? 'Leído' : 'Nuevo'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="mensajes.php?toggle_leido=1&id=<?php echo $mensaje['id']; ?>&estado=<?php echo $mensaje['leido']; ?>" class="btn btn-sm btn-info" title="Marcar como leído/no leído">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="mensajes.php?eliminar=1&id=<?php echo $mensaje['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de que quieres eliminar este mensaje?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>


<?php include 'includes/admin_footer.php'; ?>  

