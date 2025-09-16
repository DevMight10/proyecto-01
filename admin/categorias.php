<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireAdmin();
$page_title = 'Gestionar Categorías';

// Lógica para procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Acción de agregar
    if ($action == 'add') {
        $nombre = $_POST['nombre'] ?? '';
        if (!empty($nombre)) {
            $stmt = $pdo->prepare("INSERT INTO categorias (nombre) VALUES (?)");
            if ($stmt->execute([$nombre])) {
                header("Location: categorias.php?mensaje=Categoría agregada con éxito");
                exit;
            }
        } else {
            $error = "El nombre de la categoría no puede estar vacío.";
        }
    }

    // Acción de eliminar
    if ($action == 'delete') {
        $id = $_POST['id'] ?? 0;
        // Verificar si la categoría está en uso
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE categoria_id = ?");
        $stmt_check->execute([$id]);
        $product_count = $stmt_check->fetchColumn();

        if ($product_count > 0) {
            header("Location: categorias.php?error=No se puede eliminar la categoría porque tiene {$product_count} producto(s) asociado(s).");
            exit;
        } else {
            $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
            if ($stmt->execute([$id])) {
                header("Location: categorias.php?mensaje=Categoría eliminada con éxito");
                exit;
            }
        }
    }
}

// Obtener todas las categorías y contar cuántos productos tiene cada una
$stmt = $pdo->query("
    SELECT c.*, COUNT(p.id) as product_count
    FROM categorias c
    LEFT JOIN productos p ON c.id = p.categoria_id
    GROUP BY c.id
    ORDER BY c.id ASC
");
$categorias = $stmt->fetchAll();

include 'includes/admin_header.php';
?>

<main class="container">
    <h1>Gestión de Categorías</h1>

    <?php if (isset($_GET['mensaje'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['mensaje']); ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <div class="category-grid">
        <div class="form-container">
            <h2>Agregar Nueva Categoría</h2>
            <form action="categorias.php" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="nombre">Nombre de la Categoría</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Agregar Categoría</button>
            </form>
        </div>

        <div class="table-responsive">
            <h2>Categorías Existentes</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Productos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categorias)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">No hay categorías creadas.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categorias as $categoria): ?>
                            <tr>
                                <td><?php echo $categoria['id']; ?></td>
                                <td><?php echo htmlspecialchars($categoria['nombre']); ?></td>
                                <td><?php echo $categoria['product_count']; ?></td>
                                <td class="actions-cell-buttons">
                                    <a href="editar_categoria.php?id=<?php echo $categoria['id']; ?>" class="btn btn-sm btn-secondary" title="Editar">Editar</a>
                                    <form action="categorias.php" method="POST" style="display: inline-block;" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta categoría?');">
                                        <input type="hidden" name="id" value="<?php echo $categoria['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar" <?php echo ($categoria['product_count'] > 0) ? 'disabled' : ''; ?>>Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<style>
/* ====== Admin: Categorías ====== */

/* Grid principal */
.category-grid {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: 2rem;
  align-items: flex-start;
}

/* Caja formulario */
.form-container {
  background: #fff;
  border: 1px solid #E9EFEC;
  border-radius: 16px;
  box-shadow: 0 10px 22px rgba(0,0,0,.06);
  padding: 1.5rem;
}

.form-container h2 {
  margin-bottom: 1rem;
  font-size: 1.1rem;
  color: #2c3e36;
}

/* Formulario */
.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  font-size: .9rem;
  font-weight: 600;
  color: #42524b;
}

.form-control {
  width: 100%;
  padding: .6rem .8rem;
  border: 2px solid #DFE7E4;
  border-radius: 10px;
  background:#fff;
  font-size: .95rem;
}

.form-control:focus {
  outline: none;
  border-color: #007B44;
  box-shadow: 0 0 0 4px rgba(0,123,68,.12);
}

/* Botón principal */
.btn-primary {
  background:#007B44;
  border:0;
  color:#fff;
  padding: .6rem 1rem;
  border-radius: 10px;
  font-weight: 600;
  cursor:pointer;
}
.btn-primary:hover {
  background:#005f32;
}

/* Tabla de categorías */
.table-responsive {
  background: #fff;
  border: 1px solid #E9EFEC;
  border-radius: 16px;
  box-shadow: 0 10px 22px rgba(0,0,0,.06);
  overflow-x: auto;
  padding: 1rem;
}

.table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
}

.table thead th {
  background: #fff;
  padding: 12px 14px;
  font-size: .9rem;
  color: #6d7a73;
  border-bottom: 1px solid #E9EFEC;
  text-align: left;
}

.table tbody td {
  padding: 12px 14px;
  border-bottom: 1px solid #F0F4F2;
}

.table tbody tr:hover {
  background: #F6FAF8;
}

/* Acciones */
.actions-cell-buttons {
  display: flex;
  gap: .5rem;
}

.btn-sm {
  padding: 6px 10px;
  border-radius: 10px;
  font-size: .92rem;
}

/* Variantes */
.btn-secondary {
  background:#EEF3F1;
  color:#1f3029;
  border:1px solid #DFE7E4;
}
.btn-secondary:hover {
  background:#E6EFEB;
}

.btn-danger {
  background:#B00020;
  color:#fff;
  border:0;
}
.btn-danger:hover {
  background:#8E0019;
}

/* Responsive */
@media (max-width: 768px) {
  .category-grid {
    grid-template-columns: 1fr;
  }
}

</style>

<?php include '../includes/footer.php'; ?>
