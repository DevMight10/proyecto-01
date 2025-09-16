 <?php
  require_once '../config/database.php';
  require_once '../config/session.php';
  require_once '../includes/functions.php';

  requireAdmin();
  $page_title = 'Gestionar Productos';

  // Lógica para cambiar el estado ACTIVO/INACTIVO
  if (isset($_GET['cambiar_estado']) && isset($_GET['id'])) {
      $id_a_cambiar = $_GET['id'];
      $stmt_current = $pdo->prepare("SELECT activo FROM productos WHERE id = ?");
      $stmt_current->execute([$id_a_cambiar]);
      $estado_actual = $stmt_current->fetchColumn();
      if ($estado_actual !== false) {
          $nuevo_estado = ($estado_actual == 1) ? 0 : 1;
          $stmt_update = $pdo->prepare("UPDATE productos SET activo = ? WHERE id = ?");
          if ($stmt_update->execute([$nuevo_estado, $id_a_cambiar])) {
              header("Location: productos.php?mensaje=Estado del producto actualizado con éxito");
              exit;
          } else {
              $error = "Error al actualizar el estado del producto.";
          }
      } else {
          $error = "Producto no encontrado.";
      }
  }

  // LÓGICA PARA CAMBIAR EL ESTADO DESTACADO
  if (isset($_GET['destacar']) && isset($_GET['id'])) {
      $id_a_destacar = $_GET['id'];
      $stmt_current = $pdo->prepare("SELECT destacado FROM productos WHERE id = ?");
      $stmt_current->execute([$id_a_destacar]);
      $estado_actual = $stmt_current->fetchColumn();
      if ($estado_actual !== false) {
          $nuevo_estado = ($estado_actual == 1) ? 0 : 1;
          $stmt_update = $pdo->prepare("UPDATE productos SET destacado = ? WHERE id = ?");
          if ($stmt_update->execute([$nuevo_estado, $id_a_destacar])) {
              header("Location: productos.php?mensaje=Producto actualizado");
              exit;
          } else {
              $error = "Error al actualizar el producto.";
          }
      } else {
          $error = "Producto no encontrado.";
      }
  }

  // --- Lógica de Búsqueda y Filtrado ---
  $filtro_categoria = $_GET['filtro_categoria'] ?? 'todos';
  $buscar = $_GET['buscar'] ?? '';

  // Obtener todas las categorías para el selector del filtro
  $stmt_cat = $pdo->query("SELECT * FROM categorias ORDER BY nombre");
  $categorias = $stmt_cat->fetchAll();

  $where_conditions = [];
  $params = [];

  // Filtro por categoría
  if ($filtro_categoria !== 'todos' && !empty($filtro_categoria)) {
      $where_conditions[] = 'p.categoria_id = ?';
      $params[] = $filtro_categoria;
  }

  // Búsqueda por ID o nombre
  if (!empty($buscar)) {
      $where_conditions[] = '(p.nombre LIKE ? OR p.id = ?)';
      $params[] = "%{$buscar}%";
      $params[] = $buscar;
  }

  $sql_where = '';
  if (!empty($where_conditions)) {
      $sql_where = 'WHERE ' . implode(' AND ', $where_conditions);
  }

  // Obtener los productos aplicando los filtros
  $sql = "SELECT p.*, c.nombre as categoria_nombre
          FROM productos p
          LEFT JOIN categorias c ON p.categoria_id = c.id
          {$sql_where}
          ORDER BY p.fecha_creacion DESC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $productos = $stmt->fetchAll();

  include 'includes/admin_header.php';
  ?>
<link rel="stylesheet" href="/proyecto-01/admin/styles/productos.css">

  <main class="container">
      <h1>Gestión de Productos</h1>

      <a href="agregar_producto.php" class="btn btn-primary mb-3">
          <i class="fas fa-plus"></i> Agregar Nuevo Producto
      </a>

      <!-- Filtros y Búsqueda con Botones -->
      <div class="filters-bar">
          <div class="filters">
              <a href="?filtro_categoria=todos&buscar=<?php echo htmlspecialchars($buscar); ?>"
                 class="filter-btn <?php echo ($filtro_categoria == 'todos') ? 'active' : ''; ?>">Todas</a>
              <?php foreach ($categorias as $categoria): ?>
                  <a href="?filtro_categoria=<?php echo $categoria['id']; ?>&buscar=<?php echo htmlspecialchars($buscar); ?>"
                     class="filter-btn <?php echo ($filtro_categoria == $categoria['id']) ? 'active' : ''; ?>">
                      <?php echo htmlspecialchars($categoria['nombre']); ?>
                  </a>
              <?php endforeach; ?>
          </div>
          <div class="search-form">
              <form action="" method="GET">
                  <input type="hidden" name="filtro_categoria" value="<?php echo htmlspecialchars($filtro_categoria); ?>">
                  <input type="text" name="buscar" placeholder="Buscar por ID o nombre..." value="<?php echo
  htmlspecialchars($buscar); ?>">
                  <button type="submit" class="btn btn-primary">Buscar</button>
              </form>
          </div>
      </div>

      <?php if (isset($_GET['mensaje'])): ?>
          <div class="alert alert-success">
              <?php echo htmlspecialchars($_GET['mensaje']); ?>
          </div>
      <?php endif; ?>

      <?php if (isset($error)): ?>
          <div class="alert alert-danger">
              <?php echo htmlspecialchars($error); ?>
          </div>
      <?php endif; ?>

      <div class="table-responsive">
          <table class="table">
              <thead>
                  <tr>
                      <th>ID</th>
                      <th>Imagen</th>
                      <th>Nombre</th>
                      <th>Categoría</th>
                      <th>Precio</th>
                      <th>Stock</th>
                      <th>Estado</th>
                      <th>Destacado</th>
                      <th>Acciones</th>
                  </tr>
              </thead>
              <tbody>
                  <?php if (empty($productos)): ?>
                      <tr>
                          <td colspan="9" style="text-align: center;">No se encontraron productos que coincidan con los criterios de
  búsqueda.</td>
                      </tr>
                  <?php else: ?>
                      <?php foreach ($productos as $producto): ?>
                          <tr class="<?php echo $producto['activo'] ? '' : 'inactive-row'; ?>">
                              <td><?php echo $producto['id']; ?></td>
                              <td>
                                  <img src="/proyecto-01/public/<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo
  htmlspecialchars($producto['nombre']); ?>" width="50">
                              </td>
                              <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                              <td><?php echo htmlspecialchars($producto['categoria_nombre']); ?></td>
                              <td><?php echo formatPrice($producto['precio']); ?></td>
                              <td><?php echo $producto['stock']; ?></td>
                              <td>
                                  <span class="badge badge-<?php echo $producto['activo'] ? 'activo' : 'inactivo'; ?>">
                                      <?php echo $producto['activo'] ? 'Activo' : 'Inactivo'; ?>
                                  </span>
                              </td>
                              <td>
                                  <a href="productos.php?destacar=1&id=<?php echo $producto['id']; ?>" class="btn-destacar">
                                      <?php if ($producto['destacado']): ?>
                                          <i class="fas fa-star" style="color: #ffc107;"></i>
                                      <?php else: ?>
                                          <i class="far fa-star" style="color: #6c757d;"></i>
                                      <?php endif; ?>
                                  </a>
                              </td>
                              <td>
                                  <a href="editar_producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-sm btn-secondary"
  title="Editar">
                                      <i class="fas fa-edit"></i>
                                  </a>

                                  <?php if ($producto['activo']): ?>
                                      <a href="productos.php?cambiar_estado=1&id=<?php echo $producto['id']; ?>" class="btn btn-sm
  btn-warning" title="Desactivar" onclick="return confirm('¿Estás seguro de que quieres DESACTIVAR este producto? No será visible en la tienda.');">
                                          <i class="fas fa-eye-slash"></i>
                                      </a>
                                  <?php else: ?>
                                      <a href="productos.php?cambiar_estado=1&id=<?php echo $producto['id']; ?>" class="btn btn-sm
  btn-success" title="Activar" onclick="return confirm('¿Estás seguro de que quieres ACTIVAR este producto? Será visible en la tienda.');">
                                          <i class="fas fa-eye"></i>
                                      </a>
                                  <?php endif; ?>
                              </td>
                          </tr>
                      <?php endforeach; ?>
                  <?php endif; ?>
              </tbody>
          </table>
      </div>
  </main>

  <style>
      .inactive-row {
          background-color: #f8f9fa;
          opacity: 0.6;
      }
      .badge-activo { background-color: #28a745; color: white; padding: 0.3em 0.6em; border-radius: 0.25rem; }
      .badge-inactivo { background-color: #6c757d; color: white; padding: 0.3em 0.6em; border-radius: 0.25rem; }
      .btn-destacar i {
          font-size: 1.2rem;
          transition: transform 0.2s;
      }
      .btn-destacar:hover i {
          transform: scale(1.2);
      }

      .filters-bar {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 1.5rem;
          flex-wrap: wrap;
          gap: 1rem;
          background-color: #f8f9fa;
          padding: 1rem;
          border-radius: 8px;
      }
      .filters {
          display: flex;
          gap: 0.5rem;
          flex-wrap: wrap;
      }
      .filter-btn {
          padding: 0.5rem 1rem;
          background-color: #fff;
          color: #343a40;
          text-decoration: none;
          border-radius: 20px;
          border: 1px solid #ddd;
          transition: all 0.3s;
      }
      .filter-btn:hover, .filter-btn.active {
          background-color: var(--primary-color, #007bff);
          color: white;
          border-color: var(--primary-color, #007bff);
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
  </style>

  <?php include '../includes/footer.php'; ?>