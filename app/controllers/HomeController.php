<?php
require_once 'app/models/Product.php';

class HomeController {
    private $productModel;
    
    public function __construct($pdo) {
        $this->productModel = new Product($pdo);
    }
    
    public function index() {
        $productos_destacados = $this->productModel->getFeatured(6);
        
        $data = [
            'page_title' => 'Inicio',
            'productos_destacados' => $productos_destacados
        ];
        
        $this->loadView('home/index', $data);
    }
    
    private function loadView($view, $data = []) {
        extract($data);
        require_once "app/views/layouts/header.php";
        require_once "app/views/$view.php";
        require_once "app/views/layouts/footer.php";
    }
}
?>
