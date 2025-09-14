<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

$page_title = 'Agregar Producto';

// Obtener categorías para el selector
$stmt_cat = $pdo->query("SELECT * FROM categorias ORDER BY nombre");
$categorias = $stmt_cat->fetchAll();

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $categoria_id = $_POST['categoria_id'];
    $stock = $_POST['stock'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    $imagen = '';
    // Lógica de subida de imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $directorio_subida = '../public/';
        $nombre_archivo = uniqid() . '-' . basename($_FILES['imagen']['name']);
        $ruta_archivo = $directorio_subida . $nombre_archivo;
        
        // Mover el archivo a la carpeta de subidas
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_archivo)) {
            $imagen = $nombre_archivo;
        }
    }

    // Insertar en la base de datos
    $stmt = $pdo->prepare("INSERT INTO productos (nombre, descripcion, precio, categoria_id, stock, activo, imagen) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$nombre, $descripcion, $precio, $categoria_id, $stock, $activo, $imagen])) {
        header("Location: productos.php?mensaje=Producto agregado con éxito");
        exit;
    } else {
        $error = "Error al agregar el producto.";
    }
}

include 'includes/admin_header.php';
?>

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
        
        <button type="submit" class="btn btn-primary">Guardar Producto</button>
    </form>
</main>

<?php include '../includes/footer.php'; ?>
