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
.category-grid {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 2rem;
    align-items: flex-start;
}
.actions-cell-buttons {
    display: flex;
    gap: 0.5rem;
}

@media (max-width: 768px) {
    .category-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
