<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireAdmin();

$page_title = 'Agregar Producto';

// Obtener categorías para el selector
$stmt_cat = $pdo->query("SELECT * FROM categorias ORDER BY nombre");
$categorias = $stmt_cat->fetchAll();

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = $_POST['precio'] ?? 0;
    $categoria_id = $_POST['categoria_id'] ?? null;
    $stock = $_POST['stock'] ?? 0;
    $activo = isset($_POST['activo']) ? 1 : 0;
    $destacado = isset($_POST['destacado']) ? 1 : 0;

    // --- Validación básica ---
    if (empty($nombre) || empty($categoria_id) || $precio <= 0) {
        $error = "Por favor, complete los campos obligatorios (Nombre, Categoría y Precio).";
    } else {
        $imagen = '';
        // Lógica de subida de imagen segura
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png'];
            $max_size = 5 * 1024 * 1024; // 5 MB

            // Validar tamaño
            if ($_FILES['imagen']['size'] > $max_size) {
                $error = "El archivo es demasiado grande. El tamaño máximo permitido es 5 MB.";
            } else {
                // Validar tipo de archivo
                $file_info = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($file_info, $_FILES['imagen']['tmp_name']);
                finfo_close($file_info);

                if (!in_array($mime_type, $allowed_types)) {
                    $error = "Tipo de archivo no permitido. Solo se aceptan imágenes JPG y PNG.";
                } else {
                    // Crear un nombre de archivo único y seguro
                    $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                    $nombre_archivo = uniqid('prod_') . '.' . strtolower($extension);
                    $ruta_archivo = '../public/' . $nombre_archivo;

                    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_archivo)) {
                        $imagen = $nombre_archivo;
                    } else {
                        $error = "Error al mover el archivo subido.";
                    }
                }
            }
        }

        if (!isset($error)) {
            // --- CORRECCIÓN: Usar INSERT en lugar de UPDATE ---
            $sql = "INSERT INTO productos (nombre, descripcion, precio, categoria_id, stock, activo, destacado, imagen, fecha_creacion) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);

            if ($stmt->execute([$nombre, $descripcion, $precio, $categoria_id, $stock, $activo, $destacado, $imagen])) {
                header("Location: productos.php?mensaje=Producto agregado con éxito");
                exit;
            } else {
                $error = "Error al guardar el producto en la base de datos.";
            }
        }
    }
}
include 'includes/admin_header.php';
?>

<link rel="stylesheet" href="/proyecto-01/admin/styles/agregar_producto.css">

<main class="container">
    <h1>Agregar Nuevo Producto</h1>

    <a href="productos.php" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Volver a Productos
    </a>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="agregar_producto.php" method="POST" enctype="multipart/form-data" class="form-container">
        <div class="form-group">
            <label for="nombre">Nombre del Producto</label>
            <input type="text" id="nombre" name="nombre" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" class="form-control" rows="4" required></textarea>
        </div>

        <div class="form-group">
            <label for="precio">Precio (Bs.)</label>
            <input type="number" id="precio" name="precio" class="form-control" step="0.01" required>
        </div>

        <div class="form-group">
            <label for="categoria_id">Categoría</label>
            <select id="categoria_id" name="categoria_id" class="form-control" required>
                <option value="">Seleccione una categoría</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?php echo $categoria['id']; ?>">
                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="stock">Stock</label>
            <input type="number" id="stock" name="stock" class="form-control" value="0" required>
        </div>

        <div class="form-group">
            <label for="imagen">Imagen del Producto</label>
            <input type="file" id="imagen" name="imagen" class="form-control">
        </div>

        <div class="form-group form-check">
            <input type="checkbox" id="activo" name="activo" class="form-check-input" value="1" checked>
            <label for="activo" class="form-check-label">Producto Activo</label>
        </div>

        <div class="form-group form-check">
            <input type="checkbox" id="destacado" name="destacado" class="form-check-input" value="1">
            <label for="destacado" class="form-check-label">Marcar como Producto Destacado</label>
        </div>

        <button type="submit" class="btn btn-primary">Guardar Producto</button>
    </form>
</main>

<?php include 'includes/admin_footer.php'; ?>  

