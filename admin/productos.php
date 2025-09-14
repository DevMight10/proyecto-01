<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

// Lógica para procesar la eliminación de un producto
if (isset($_GET['eliminar']) && isset($_GET['id'])) {
    $id_a_eliminar = $_GET['id'];
    
    // Aquí deberías añadir validaciones de seguridad, por ejemplo, verificar que el producto existe
    
    $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
    
    if ($stmt->execute([$id_a_eliminar])) {
        // Opcional: Redirigir para limpiar la URL o mostrar un mensaje de éxito
        header("Location: productos.php?mensaje=Producto eliminado con éxito");
        exit;
    } else {
        $error = "Error al eliminar el producto.";
    }
}


$page_title = 'Gestionar Productos';

// Obtener todos los productos
$stmt = $pdo->query("SELECT p.*, c.nombre as categoria_nombre FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id ORDER BY p.fecha_creacion DESC");
$productos = $stmt->fetchAll();

include 'includes/admin_header.php';
?>

<main class="container">
    <h1>Gestión de Productos</h1>
    
    <a href="agregar_producto.php" class="btn btn-primary mb-3">
        <i class="fas fa-plus"></i> Agregar Nuevo Producto
    </a>
    
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
                    <th>Activo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td><?php echo $producto['id']; ?></td>
                        <td>
                            <img src="../public/<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>" width="50">
                        </td>
                        <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($producto['categoria_nombre']); ?></td>
                        <td><?php echo formatPrice($producto['precio']); ?></td>
                        <td><?php echo $producto['stock']; ?></td>
                        <td><?php echo $producto['activo'] ? 'Sí' : 'No'; ?></td>
                        <td>
                            <a href="editar_producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-sm btn-secondary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="productos.php?eliminar=1&id=<?php echo $producto['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de que quieres eliminar este producto?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
