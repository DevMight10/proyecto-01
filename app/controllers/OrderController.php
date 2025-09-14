<?php
require_once 'app/models/Order.php';
require_once 'app/models/User.php';

class OrderController {
    private $orderModel;
    private $userModel;
    
    public function __construct($pdo) {
        $this->orderModel = new Order($pdo);
        $this->userModel = new User($pdo);
    }
    
    public function confirm() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /login?redirect=/confirmar-pedido');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $carrito = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : [];
            
            if (empty($carrito)) {
                header('Location: /carrito');
                exit();
            }
            
            $total = $this->getCartTotal();
            $numero_pedido = $this->orderModel->generateOrderNumber();
            
            // Preparar items para el pedido
            $items = [];
            foreach ($carrito as $producto_id => $item) {
                $items[] = [
                    'producto_id' => $producto_id,
                    'cantidad' => $item['cantidad'],
                    'precio' => $item['precio']
                ];
            }
            
            try {
                $pedido_id = $this->orderModel->create($_SESSION['usuario_id'], $numero_pedido, $total, $items);
                
                // Limpiar carrito
                unset($_SESSION['carrito']);
                
                $data = [
                    'page_title' => 'Pedido Confirmado',
                    'numero_pedido' => $numero_pedido,
                    'pedido_id' => $pedido_id,
                    'total' => $total
                ];
                
                $this->loadView('orders/confirmed', $data);
                return;
                
            } catch (Exception $e) {
                $error = 'Error al procesar el pedido. Intente nuevamente.';
            }
        }
        
        $carrito = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : [];
        $total = $this->getCartTotal();
        
        $data = [
            'page_title' => 'Confirmar Pedido',
            'carrito' => $carrito,
            'total' => $total,
            'error' => isset($error) ? $error : null
        ];
        
        $this->loadView('orders/confirm', $data);
    }
    
    public function myOrders() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /login');
            exit();
        }
        
        $pedidos = $this->orderModel->getByUser($_SESSION['usuario_id']);
        
        $data = [
            'page_title' => 'Mis Pedidos',
            'pedidos' => $pedidos
        ];
        
        $this->loadView('orders/my_orders', $data);
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
