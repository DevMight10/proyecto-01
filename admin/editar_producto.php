<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireAdmin();

// Generar un token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$page_title = 'Editar Producto';

// Validar que se haya pasado un ID
if (!isset($_GET['id'])) {
    header("Location: productos.php");
    exit;
}

$producto_id = $_GET['id'];

// Obtener datos del producto
$stmt_prod = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
$stmt_prod->execute([$producto_id]);
$producto = $stmt_prod->fetch();

if (!$producto) {
    header("Location: productos.php?mensaje=Producto no encontrado");
    exit;
}

// Obtener categorías
$stmt_cat = $pdo->query("SELECT * FROM categorias ORDER BY nombre");
$categorias = $stmt_cat->fetchAll();

// Procesar el formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Error de validación CSRF.');
    }

    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $categoria_id = $_POST['categoria_id'];
    $stock = $_POST['stock'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    $imagen = $producto['imagen']; // Mantener la imagen actual por defecto
    
    // Si se sube una nueva imagen, aplicar validación
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        if (!isset($error)) { // Proceder solo si no hay errores previos
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
                        // Eliminar la imagen anterior si existe
                        if (!empty($producto['imagen']) && file_exists('../public/' . $producto['imagen'])) {
                            unlink('../public/' . $producto['imagen']);
                        }
                        $imagen = $nombre_archivo;
                    } else {
                        $error = "Error al mover el archivo subido.";
                    }
                }
            }
        }
    }

    // Actualizar en la base de datos
    $stmt = $pdo->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, categoria_id = ?, stock = ?, activo = ?, imagen = ? WHERE id = ?");
    
    if ($stmt->execute([$nombre, $descripcion, $precio, $categoria_id, $stock, $activo, $imagen, $producto_id])) {
        header("Location: productos.php?mensaje=Producto actualizado con éxito");
        exit;
    } else {
        $error = "Error al actualizar el producto.";
    }
}

include 'includes/admin_header.php';
?>

<link rel="stylesheet" href="/proyecto-01/admin/styles/editar.css">

<main class="container">
    <h1>Editar Producto</h1>
    
    <a href="productos.php" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Volver a Productos
    </a>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="editar_producto.php?id=<?php echo $producto_id; ?>" method="POST" enctype="multipart/form-data" class="form-container">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <div class="form-group">
            <label for="nombre">Nombre del Producto</label>
            <input type="text" id="nombre" name="nombre" class="form-control" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" class="form-control" rows="4" required><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="precio">Precio (Bs.)</label>
            <input type="number" id="precio" name="precio" class="form-control" step="0.01" value="<?php echo htmlspecialchars($producto['precio']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="categoria_id">Categoría</label>
            <select id="categoria_id" name="categoria_id" class="form-control" required>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?php echo $categoria['id']; ?>" <?php echo ($producto['categoria_id'] == $categoria['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="stock">Stock</label>
            <input type="number" id="stock" name="stock" class="form-control" value="<?php echo htmlspecialchars($producto['stock']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="imagen">Imagen Actual</label>
            <div>
                <?php if (!empty($producto['imagen'])): ?>
                    <img src="../public/<?php echo htmlspecialchars($producto['imagen']); ?>" alt="Imagen actual" width="100">
                <?php else: ?>
                    <p>No hay imagen asignada.</p>
                <?php endif; ?>
            </div>
            <label for="imagen" class="mt-2">Subir Nueva Imagen (opcional)</label>
            <input type="file" id="imagen" name="imagen" class="form-control">
        </div>
        
        <div class="form-group form-check">
            <input type="checkbox" id="activo" name="activo" class="form-check-input" value="1" <?php echo $producto['activo'] ? 'checked' : ''; ?>>
            <label for="activo" class="form-check-label">Producto Activo</label>
        </div>
        
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    </form>
</main>

<?php include 'includes/admin_footer.php'; ?>  

