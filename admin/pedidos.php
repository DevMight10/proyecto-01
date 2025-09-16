 <?php
    require_once '../config/database.php';
    require_once '../config/session.php';
    require_once '../includes/functions.php';

    requireAdmin();

    $page_title = 'Gestionar Pedidos';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pedido_id']) && isset($_POST['estado'])) {
        $pedido_id = $_POST['pedido_id'];
        $nuevo_estado = $_POST['estado'];

        // Obtener el estado actual del pedido para validar la transición
        $stmt_current = $pdo->prepare("SELECT estado FROM pedidos WHERE id = ?");
        $stmt_current->execute([$pedido_id]);
        $estado_actual = $stmt_current->fetchColumn();

        $transicion_valida = false;
        switch ($estado_actual) {
            case 'pendiente':
                if ($nuevo_estado === 'en_proceso') {
                    $transicion_valida = true;
                }
                break;
            case 'en_proceso':
                if ($nuevo_estado === 'entregado') {
                    $transicion_valida = true;
                }
                break;
                // No hay transiciones desde 'entregado' o 'cancelado'
        }

        if ($transicion_valida) {
            $stmt = $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
            if ($stmt->execute([$nuevo_estado, $pedido_id])) {
                // (El resto de esta sección no cambia, solo se añade la validación)
                $query_params = http_build_query([
                    'filtro_estado' => $_GET['filtro_estado'] ?? 'todos',
                    'buscar_codigo' => $_GET['buscar_codigo'] ?? '',
                    'pagina' => $_GET['pagina'] ?? 1
                ]);
                header("Location: pedidos.php?{$query_params}&mensaje=Estado del pedido actualizado.");
                exit;
            } else {
                $error = "Error al actualizar el estado.";
            }
        } else {
            $error = "Transición de estado no permitida. No se puede cambiar de '{$estado_actual}' a '{$nuevo_estado}'.";
        }
    }

    // --- Lógica de Paginación, Búsqueda y Filtrado ---
    $numero_de_pedidos_por_pagina = 5;

    //don't touch this, it's magic
    $pedidos_por_pagina = $numero_de_pedidos_por_pagina + 1;
    $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $offset = ($pagina_actual - 1) * $pedidos_por_pagina;

    $filtro_estado = $_GET['filtro_estado'] ?? 'todos';
    $buscar_codigo = $_GET['buscar_codigo'] ?? '';

    $where_conditions = [];
    $params = [];

    if ($filtro_estado !== 'todos') {
        $where_conditions[] = 'p.estado = ?';
        $params[] = $filtro_estado;
    }

    if (!empty($buscar_codigo)) {
        $where_conditions[] = 'p.numero_pedido LIKE ?';
        $params[] = "%{$buscar_codigo}%";
    }

    $sql_where = '';
    if (!empty($where_conditions)) {
        $sql_where = 'WHERE ' . implode(' AND ', $where_conditions);
    }

    // Contar total de pedidos para la paginación
    $stmt_count = $pdo->prepare("SELECT COUNT(DISTINCT p.id) FROM pedidos p {$sql_where}");
    $stmt_count->execute($params);
    $total_pedidos = $stmt_count->fetchColumn();
    $total_paginas = ceil($total_pedidos / $pedidos_por_pagina);

    // Consulta para traer pedidos de la página actual
    $sql = "
    SELECT 
        p.id as pedido_id, p.numero_pedido, p.fecha_pedido, p.total, p.estado,
        u.nombre as cliente_nombre, u.email as cliente_email,
        pd.cantidad, pd.precio_unitario,
        pr.nombre as producto_nombre, pr.imagen as producto_imagen
    FROM pedidos p
    JOIN usuarios u ON p.usuario_id = u.id
    LEFT JOIN pedido_detalles pd ON p.id = pd.pedido_id
    LEFT JOIN productos pr ON pd.producto_id = pr.id
    {$sql_where}
    GROUP BY p.id, pr.id
    ORDER BY p.fecha_pedido DESC, p.id ASC
    LIMIT {$pedidos_por_pagina} OFFSET {$offset}
";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agrupar productos por pedido
    $pedidos = [];
    foreach ($results as $row) {
        $pedido_id = $row['pedido_id'];
        if (!isset($pedidos[$pedido_id])) {
            $pedidos[$pedido_id] = [
                'id' => $pedido_id,
                'numero_pedido' => $row['numero_pedido'],
                'fecha_pedido' => $row['fecha_pedido'],
                'total' => $row['total'],
                'estado' => $row['estado'],
                'cliente_nombre' => $row['cliente_nombre'],
                'cliente_email' => $row['cliente_email'],
                'productos' => []
            ];
        }
        if ($row['producto_nombre']) {
            $pedidos[$pedido_id]['productos'][] = [
                'nombre' => $row['producto_nombre'],
                'imagen' => $row['producto_imagen'],
                'cantidad' => $row['cantidad'],
                'precio_unitario' => $row['precio_unitario']
            ];
        }
    }

include 'includes/admin_header.php';
?>

<link rel="stylesheet" href="styles/pedidos.css">

 <main class="container">
     <h1>Gestión de Pedidos</h1>

     <div class="filters-bar">
         <div class="filters">
             <?php
                $estados = ['todos', 'pendiente', 'en_proceso', 'entregado', 'cancelado'];
                foreach ($estados as $estado) {
                    $query_params = http_build_query(['filtro_estado' => $estado, 'buscar_codigo' => $buscar_codigo]);
                    $active_class = ($filtro_estado == $estado) ? 'active' : '';
                    echo "<a href=\"?{$query_params}\" class=\"filter-btn {$active_class}\">" . formatOrderStatus($estado) . "</a>";
                }
                ?>
         </div>
         <div class="search-form">
             <form action="" method="GET">
                 <input type="hidden" name="filtro_estado" value="<?php echo htmlspecialchars($filtro_estado); ?>">
                 <input type="text" name="buscar_codigo" placeholder="Buscar por código..." value="<?php echo htmlspecialchars($buscar_codigo); ?>">
                 <button type="submit" class="btn btn-primary">Buscar</button>
             </form>
         </div>
     </div>

     <?php if (isset($_GET['mensaje'])) : ?>
         <div class="alert alert-success"><?php echo htmlspecialchars($_GET['mensaje']); ?></div>
     <?php endif; ?>
     <?php if (isset($error)) : ?>
         <div class="alert alert-danger"><?php echo $error; ?></div>
     <?php endif; ?>

     <!-- NUEVA ESTRUCTURA DE TÍTULOS Y LISTA -->
     <div class="order-list-container">
         <!-- Títulos de la Lista -->
         <div class="order-list-header">
             <span class="col col-pedido">Pedido</span>
             <span class="col col-cliente">Cliente</span>
             <span class="col col-fecha">Fecha</span>
             <span class="col col-estado">Estado</span>
             <span class="col col-total">Total</span>
             <span class="col col-icono"></span>
         </div>

         <div class="accordion">
             <?php if (empty($pedidos)) : ?>
                 <p class="no-orders-found">No se encontraron pedidos que coincidan con los filtros aplicados.</p>
             <?php else : ?>
                 <?php foreach ($pedidos as $pedido) : ?>
                     <div class="accordion-item">
                         <button class="accordion-header">
                             <span class="col col-pedido"><?php echo htmlspecialchars($pedido['numero_pedido']); ?></span>
                             <span class="col col-cliente"><?php echo htmlspecialchars($pedido['cliente_nombre']); ?></span>
                             <span class="col col-fecha"><?php echo date('d/m/Y', strtotime($pedido['fecha_pedido'])); ?></span>
                             <span class="col col-estado">
                                 <span class="badge badge-<?php echo htmlspecialchars($pedido['estado']); ?>">
                                     <?php echo htmlspecialchars(formatOrderStatus($pedido['estado'])); ?>
                                 </span>
                             </span>
                             <span class="col col-total"><?php echo formatPrice($pedido['total']); ?></span>
                             <span class="col col-icono"><i class="fas fa-chevron-down"></i></span>
                         </button>

                         <div class="accordion-content">
                             <div class="order-details-admin">
                                 <h4>Detalles del Pedido</h4>
                                 <div class="customer-details">
                                     <strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['cliente_nombre']); ?><br>
                                     <strong>Email:</strong> <?php echo htmlspecialchars($pedido['cliente_email']); ?>
                                 </div>
                                 <hr>
                                 <h5>Productos</h5>
                                 <?php if (empty($pedido['productos'])) : ?>
                                     <p>Este pedido no tiene productos asociados.</p>
                                 <?php else : ?>
                                     <?php foreach ($pedido['productos'] as $producto) : ?>
                                         <div class="product-item">
                                             <img src="/proyecto-01/public/<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                             <div class="product-info">
                                                 <?php echo htmlspecialchars($producto['nombre']); ?>
                                                 <small>Cantidad: <?php echo $producto['cantidad']; ?></small>
                                             </div>
                                             <div class="product-price">
                                                 <?php echo formatPrice($producto['precio_unitario'] * $producto['cantidad']); ?>
                                             </div>
                                         </div>
                                     <?php endforeach; ?>
                                 <?php endif; ?>
                                 <hr>
                                 <div class="admin-actions">
                                     <h5>Acciones</h5>
                                     <?php if ($pedido['estado'] === 'pendiente'): ?>
                                         <form action="" method="POST">
                                             <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                                             <input type="hidden" name="estado" value="en_proceso">
                                             <button type="submit" class="btn btn-primary">Mover a "En Proceso"</button>
                                         </form>
                                     <?php elseif ($pedido['estado'] === 'en_proceso'): ?>
                                         <form action="" method="POST">
                                             <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                                             <input type="hidden" name="estado" value="entregado">
                                             <button type="submit" class="btn btn-success">Marcar como "Entregado"</button>
                                         </form>
                                     <?php else: ?>
                                         <p>No hay acciones disponibles.</p>
                                     <?php endif; ?>
                                 </div>
                             </div>
                         </div>
                     </div>
                 <?php endforeach; ?>
             <?php endif; ?>
         </div>
     </div>


     <div class="pagination">
         <?php
            if ($total_paginas > 1) {
                $query_params = http_build_query(['filtro_estado' => $filtro_estado, 'buscar_codigo' => $buscar_codigo]);

                // Botón Anterior
                if ($pagina_actual > 1) {
                    echo "<a href=\"?{$query_params}&pagina=" . ($pagina_actual - 1) . "\">Anterior</a>";
                }

                // Números de página
                for ($i = 1; $i <= $total_paginas; $i++) {
                    $active_class = ($i == $pagina_actual) ? 'active' : '';
                    echo "<a href=\"?{$query_params}&pagina={$i}\" class=\"{$active_class}\">{$i}</a>";
                }

                // Botón Siguiente
                if ($pagina_actual < $total_paginas) {
                    echo "<a href=\"?{$query_params}&pagina=" . ($pagina_actual + 1) . "\">Siguiente</a>";
                }
            }
            ?>
     </div>
 </main>

<?php include 'includes/admin_footer.php'; ?>  
 


 <script>
     document.addEventListener('DOMContentLoaded', function() {
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
