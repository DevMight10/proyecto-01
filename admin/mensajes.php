<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireAdmin();

// Generar un token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$page_title = 'Ver Mensajes';

// --- Lógica de Acciones ---

// Acción de eliminar (vía POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'eliminar') {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Error de validación CSRF.');
    }
    if (isset($_POST['id'])) {
        $id_a_eliminar = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM mensajes WHERE id = ?");
        if ($stmt->execute([$id_a_eliminar])) {
            // Redirigir para limpiar la URL de la acción de eliminación
            header("Location: mensajes.php?mensaje=Mensaje eliminado con éxito");
            exit;
        }
    }
}

// Lógica para marcar como leído al expandir (vía GET)
$expanded_id = null;
if (isset($_GET['expand_id'])) {
    $expanded_id = (int)$_GET['expand_id'];
    // Marcar como leído en la BD
    $stmt_mark_read = $pdo->prepare("UPDATE mensajes SET leido = 1 WHERE id = ?");
    $stmt_mark_read->execute([$expanded_id]);
}


// --- Lógica de Paginación, Búsqueda y Filtrado ---
$mensajes_por_pagina = 15;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $mensajes_por_pagina;

$filtro = $_GET['filtro'] ?? 'todos';
$buscar = $_GET['buscar'] ?? '';

// Guardar los parámetros actuales para los enlaces
$current_params = ['filtro' => $filtro, 'buscar' => $buscar, 'pagina' => $pagina_actual];

$where_conditions = [];
$params = [];

if ($filtro === 'nuevos') {
    $where_conditions[] = 'leido = 0';
} elseif ($filtro === 'leidos') {
    $where_conditions[] = 'leido = 1';
}

if (!empty($buscar)) {
    $where_conditions[] = '(nombre LIKE ? OR email LIKE ? OR asunto LIKE ? OR mensaje LIKE ?)';
    for ($i = 0; $i < 4; $i++) {
        $params[] = "%{$buscar}%";
    }
}

$sql_where = '';
if (!empty($where_conditions)) {
    $sql_where = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Contar total de mensajes para la paginación
$stmt_count = $pdo->prepare("SELECT COUNT(*) FROM mensajes {$sql_where}");
$stmt_count->execute($params);
$total_mensajes = $stmt_count->fetchColumn();
$total_paginas = ceil($total_mensajes / $mensajes_por_pagina);

// Obtener los mensajes de la página actual
$sql = "SELECT * FROM mensajes {$sql_where} ORDER BY fecha_envio DESC LIMIT {$mensajes_por_pagina} OFFSET {$offset}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$mensajes = $stmt->fetchAll();

include 'includes/admin_header.php';
?>

<link rel="stylesheet" href="/proyecto-01/admin/styles/mensaje.css">

<main class="container">
    <h1>Mensajes de Contacto</h1>

    <!-- Filtros y Búsqueda -->
    <div class="filters-bar">
        <div class="filters">
            <a href="?filtro=todos&buscar=<?php echo htmlspecialchars($buscar); ?>" class="filter-btn <?php echo ($filtro == 'todos') ? 'active' : ''; ?>">Todos</a>
            <a href="?filtro=nuevos&buscar=<?php echo htmlspecialchars($buscar); ?>" class="filter-btn <?php echo ($filtro == 'nuevos') ? 'active' : ''; ?>">Nuevos</a>
            <a href="?filtro=leidos&buscar=<?php echo htmlspecialchars($buscar); ?>" class="filter-btn <?php echo ($filtro == 'leidos') ? 'active' : ''; ?>">Leídos</a>
        </div>
        <div class="search-form">
            <form action="" method="GET">
                <input type="hidden" name="filtro" value="<?php echo htmlspecialchars($filtro); ?>">
                <input type="text" name="buscar" placeholder="Buscar en mensajes..." value="<?php echo htmlspecialchars($buscar); ?>">
                <button type="submit" class="btn btn-primary">Buscar</button>
            </form>
        </div>
    </div>
    
    <?php if (isset($_GET['mensaje'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['mensaje']); ?></div>
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
                <?php if (empty($mensajes)): ?>
                    <tr><td colspan="6" style="text-align: center;">No hay mensajes que coincidan con los criterios.</td></tr>
                <?php else: ?>
                    <?php foreach ($mensajes as $mensaje): ?>
                        <?php
                            // Determinar si esta fila está expandida
                            $is_expanded = ($expanded_id === (int)$mensaje['id']);
                            // Construir los parámetros para el enlace de "Ver"
                            $view_params = array_merge($current_params, ['expand_id' => $mensaje['id']]);
                            // Si ya está expandido, el enlace lo "cerrará" (recargando sin el expand_id)
                            if ($is_expanded) {
                                unset($view_params['expand_id']);
                            }
                        ?>
                        <tr class="mensaje-summary-row <?php echo $mensaje['leido'] ? 'mensaje-leido' : 'mensaje-nuevo'; ?> <?php echo $is_expanded ? 'expanded' : ''; ?>">
                            <td>
                                <?php echo htmlspecialchars($mensaje['nombre']); ?><br>
                                <small><?php echo htmlspecialchars($mensaje['email']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($mensaje['asunto']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars(substr($mensaje['mensaje'], 0, 70))) . (strlen($mensaje['mensaje']) > 70 ? '...' : ''); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($mensaje['fecha_envio'])); ?></td>
                            <td><span class="badge badge-<?php echo $mensaje['leido'] ? 'leido' : 'nuevo'; ?>"><?php echo $mensaje['leido'] ? 'Leído' : 'Nuevo'; ?></span></td>
                            <td class="actions-cell">
                                <a href="?<?php echo http_build_query($view_params); ?>" class="btn btn-sm btn-primary">
                                    <?php echo $is_expanded ? 'Cerrar' : 'Ver'; ?>
                                </a>
                                <form action="mensajes.php" method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este mensaje?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="id" value="<?php echo $mensaje['id']; ?>">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar mensaje">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php if ($is_expanded): ?>
                            <tr class="mensaje-expandido">
                                <td colspan="6">
                                    <div class="mensaje-contenido-full">
                                        <h4>Mensaje Completo</h4>
                                        <p><strong>De:</strong> <?php echo htmlspecialchars($mensaje['nombre']); ?> (<?php echo htmlspecialchars($mensaje['email']); ?>)</p>
                                        <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($mensaje['fecha_envio'])); ?></p>
                                        <hr>
                                        <p><?php echo nl2br(htmlspecialchars($mensaje['mensaje'])); ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="pagination">
        <?php if ($total_paginas > 1): ?>
            <?php 
                $pagination_params = ['filtro' => $filtro, 'buscar' => $buscar];
            ?>
            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <a href="?pagina=<?php echo $i; ?>&<?php echo http_build_query($pagination_params); ?>" class="<?php echo ($i == $pagina_actual) ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        <?php endif; ?>
    </div>
</main>


<?php include 'includes/admin_footer.php'; ?>  

