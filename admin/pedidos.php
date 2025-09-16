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

 <style>
        .accordion {
        width: 100%;
    }

    .accordion-item {
        border-bottom: 1px solid #eee;
    }

    .accordion-header {
        background: #fff;
        border: none;
        width: 100%;
        padding: 1.5rem;
        text-align: left;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 1rem;
    }

    .accordion-header:hover {
        background: #f8f9fa;
    }

    .order-summary-info,
    .order-summary-status {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .order-number {
        font-weight: bold;
    }

    .order-customer {
        color: #555;
    }

    .order-total {
        font-weight: bold;
        color: var(--primary-color);
    }

    .accordion-header i {
        transition: transform 0.3s;
    }

    .accordion-header.active i {
        transform: rotate(180deg);
    }

    .accordion-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
        background: #fdfdfd;
    }

    .order-details-admin {
        padding: 1.5rem;
    }

    .customer-details {
        margin-bottom: 1rem;
    }

    .product-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .product-item:last-child {
        border-bottom: none;
    }

    .product-item img {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        object-fit: cover;
    }

    .product-info {
        flex-grow: 1;
    }

    .product-info small {
        display: block;
        color: #6c757d;
    }

    .admin-actions {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #eee;
    }

    .admin-actions form {
        display: flex;
        gap: 1rem;
        align-items: center;
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

    .filters-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .filters {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .filter-btn {
        padding: 0.5rem 1rem;
        background-color: #fff;
        color: var(--text-color);
        text-decoration: none;
        border-radius: 20px;
        border: 1px solid #ddd;
        transition: all 0.3s;
    }

    .filter-btn:hover,
    .filter-btn.active {
        background-color: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    .search-form form {
        display: flex;
        gap: 0.5rem;
    }

    .search-form input {
        padding: 0.5rem;
        border-radius: 20px;
        border: 1px solid #ddd;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 2rem;
    }

    .pagination a {
        color: var(--primary-color);
        padding: 0.5rem 1rem;
        text-decoration: none;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .pagination a.active {
        background-color: var(--primary-color);
        color: white;
    }

    .accordion-titles {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 1.5rem;
        background-color: #f0f2f5;
        border-bottom: 2px solid #e9ecef;
        font-weight: 600;
        color: #343a40;
        margin-top: 1.5rem;
    }

    .accordion-titles .order-summary-info,
    .accordion-titles .order-summary-status {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        flex-basis: 50%;
    }

    .accordion-titles .order-summary-status {
        justify-content: flex-end;
    }

    .title-pedido {
        flex: 2;
    }

    .title-cliente {
        flex: 2;
    }

    .title-fecha {
        flex: 1;
        text-align: left;
    }

    .title-estado {
        flex: 1;
        text-align: center;
    }

    .title-total {
        flex: 1;
        text-align: right;
    }

    .title-icono {
        width: 24px;
    }
     /* NUEVOS ESTILOS PARA LA LISTA ALINEADA */
     .order-list-container {
         background-color: #fff;
         border-radius: 8px;
         box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
         overflow: hidden;
     }

     .order-list-header {
         display: flex;
         align-items: center;
         padding: 1rem 1.5rem;
         background-color: #f8f9fa;
         border-bottom: 2px solid #e9ecef;
         font-weight: 600;
         color: #495057;
     }

     .accordion-item {
         border-bottom: 1px solid #eee;
     }

     .accordion-item:last-child {
         border-bottom: none;
     }

     .accordion-header {
         padding: 1rem 1.5rem;
         gap: 0;
     }

     .col {
         display: flex;
         align-items: center;
         padding: 0 10px;
     }

     .col-pedido {
         flex: 1.5;
     }

     .col-cliente {
         flex: 2;
     }

     .col-fecha {
         flex: 1;
     }

     .col-estado {
         flex: 1.2;
         justify-content: center;
     }

     .col-total {
         flex: 1;
         justify-content: flex-end;
         font-weight: bold;
     }

     .col-icono {
         flex: 0 0 40px;
         justify-content: center;
     }

     .no-orders-found {
         padding: 2rem;
         text-align: center;
     }
 </style>



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

 <?php include '../includes/footer.php'; ?>