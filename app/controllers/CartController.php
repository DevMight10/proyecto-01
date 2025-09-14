<?php
require_once 'app/models/Product.php';

class CartController {
    private $productModel;
    
    public function __construct($pdo) {
        $this->productModel = new Product($pdo);
    }
    
    public function index() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /login?redirect=/carrito');
            exit();
        }
        
        $carrito = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : [];
        $total = $this->getCartTotal();
        
        $data = [
            'page_title' => 'Carrito de Compras',
            'carrito' => $carrito,
            'total' => $total
        ];
        
        $this->loadView('cart/index', $data);
    }
    
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $producto_id = $_POST['producto_id'];
            $cantidad = (int)$_POST['cantidad'];
            
            if ($cantidad > 0) {
                $_SESSION['carrito'][$producto_id]['cantidad'] = $cantidad;
            } else {
                unset($_SESSION['carrito'][$producto_id]);
            }
        }
        
        header('Location: /carrito');
        exit();
    }
    
    public function remove() {
        if (isset($_GET['id'])) {
            $producto_id = $_GET['id'];
            unset($_SESSION['carrito'][$producto_id]);
        }
        
        header('Location: /carrito');
        exit();
    }
    
    public function clear() {
        unset($_SESSION['carrito']);
        header('Location: /carrito');
        exit();
    }
    
    private function getCartTotal() {
        $total = 0;
        if (isset($_SESSION['carrito'])) {
            foreach ($_SESSION['carrito'] as $item) {
                $total += $item['precio'] * $item['cantidad'];
            }
        }
        return $total;
    }
    
    private function loadView($view, $data = []) {
        extract($data);
        require_once "app/views/layouts/header.php";
        require_once "app/views/$view.php";
        require_once "app/views/layouts/footer.php";
    }
}
?>
