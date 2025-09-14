<?php
require_once 'app/models/Product.php';
require_once 'app/models/Category.php';

class ProductController {
    private $productModel;
    private $categoryModel;
    
    public function __construct($pdo) {
        $this->productModel = new Product($pdo);
        $this->categoryModel = new Category($pdo);
    }
    
    public function index() {
        $categoria_filtro = isset($_GET['categoria']) ? $_GET['categoria'] : null;
        $productos = $this->productModel->getAll($categoria_filtro);
        $categorias = $this->categoryModel->getAll();
        
        $data = [
            'page_title' => 'Productos',
            'productos' => $productos,
            'categorias' => $categorias,
            'categoria_filtro' => $categoria_filtro
        ];
        
        $this->loadView('products/index', $data);
    }
    
    public function detail() {
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            header('Location: /productos');
            exit();
        }
        
        $producto = $this->productModel->getById($id);
        
        if (!$producto) {
            header('Location: /productos');
            exit();
        }
        
        $data = [
            'page_title' => $producto['nombre'],
            'producto' => $producto
        ];
        
        $this->loadView('products/detail', $data);
    }
    
    public function addToCart() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $producto_id = $_POST['producto_id'];
            $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
            
            $producto = $this->productModel->getById($producto_id);
            
            if ($producto) {
                $this->addToCartSession($producto_id, $producto['nombre'], $producto['precio'], $producto['imagen'], $cantidad);
                header('Location: /carrito');
                exit();
            }
        }
        
        header('Location: /productos');
        exit();
    }
    
    private function addToCartSession($producto_id, $nombre, $precio, $imagen, $cantidad = 1) {
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = array();
        }
        
        if (isset($_SESSION['carrito'][$producto_id])) {
            $_SESSION['carrito'][$producto_id]['cantidad'] += $cantidad;
        } else {
            $_SESSION['carrito'][$producto_id] = array(
                'nombre' => $nombre,
                'precio' => $precio,
                'imagen' => $imagen,
                'cantidad' => $cantidad
            );
        }
    }
    
    private function loadView($view, $data = []) {
        extract($data);
        require_once "app/views/layouts/header.php";
        require_once "app/views/$view.php";
        require_once "app/views/layouts/footer.php";
    }
}
?>
