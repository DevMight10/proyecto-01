<?php
require_once 'app/models/Message.php';

class ContactController {
    private $messageModel;
    
    public function __construct($pdo) {
        $this->messageModel = new Message($pdo);
    }
    
    public function index() {
        $success = null;
        $error = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre']);
            $email = trim($_POST['email']);
            $asunto = trim($_POST['asunto']);
            $mensaje = trim($_POST['mensaje']);
            
            // Validaciones básicas
            if (empty($nombre) || empty($email) || empty($asunto) || empty($mensaje)) {
                $error = 'Todos los campos son obligatorios.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'El correo electrónico no es válido.';
            } else {
                try {
                    $this->messageModel->create($nombre, $email, $asunto, $mensaje);
                    $success = 'Mensaje enviado correctamente. Te contactaremos pronto.';
                    
                    // Limpiar formulario
                    $_POST = [];
                } catch (Exception $e) {
                    $error = 'Error al enviar el mensaje. Intente nuevamente.';
                }
            }
        }
        
        $data = [
            'page_title' => 'Contacto',
            'success' => $success,
            'error' => $error
        ];
        
        $this->loadView('contact/index', $data);
    }
    
    private function loadView($view, $data = []) {
        extract($data);
        require_once "app/views/layouts/header.php";
        require_once "app/views/$view.php";
        require_once "app/views/layouts/footer.php";
    }
}
?>
