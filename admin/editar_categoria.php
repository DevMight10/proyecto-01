<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireAdmin();
$page_title = 'Editar Categoría';

// Validar que se haya pasado un ID
if (!isset($_GET['id'])) {
    header("Location: categorias.php");
    exit;
}
$id = $_GET['id'];

// Obtener datos de la categoría
$stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = ?");
$stmt->execute([$id]);
$categoria = $stmt->fetch();

if (!$categoria) {
    header("Location: categorias.php?error=Categoría no encontrada");
    exit;
}

// Procesar el formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';

    if (empty($nombre)) {
        $error = "El nombre no puede estar vacío.";
    } else {
        $stmt = $pdo->prepare("UPDATE categorias SET nombre = ? WHERE id = ?");
        if ($stmt->execute([$nombre, $id])) {
            header("Location: categorias.php?mensaje=Categoría actualizada con éxito");
            exit;
        } else {
            $error = "Error al actualizar la categoría.";
        }
    }
}

include 'includes/admin_header.php';
?>

<main class="container">
    <h1>Editar Categoría</h1>

    <a href="categorias.php" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Volver a Categorías
    </a>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="form-container">
        <form action="editar_categoria.php?id=<?php echo $categoria['id']; ?>" method="POST">
            <div class="form-group">
                <label for="nombre">Nombre de la Categoría</label>
                <input type="text" id="nombre" name="nombre" class="form-control" value="<?php echo htmlspecialchars($categoria['nombre']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </form>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
