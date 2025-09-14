<?php
require_once 'app/models/User.php';

class AuthController {
    private $userModel;
    
    public function __construct($pdo) {
        $this->userModel = new User($pdo);
    }
    
    public function login() {
        if (isset($_SESSION['usuario_id'])) {
            header('Location: /');
            exit();
        }
        
        $error = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            
            if (empty($email) || empty($password)) {
                $error = 'Todos los campos son obligatorios.';
            } else {
                $usuario = $this->userModel->findByEmail($email);
                
                if ($usuario && $this->userModel->verifyPassword($password, $usuario['password'])) {
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_nombre'] = $usuario['nombre'];
                    $_SESSION['tipo'] = $usuario['tipo'];
                    
                    // Redirigir a la página anterior o al inicio
                    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '/';
                    header("Location: $redirect");
                    exit();
                } else {
                    $error = 'Credenciales inválidas.';
                }
            }
        }
        
        $data = [
            'page_title' => 'Iniciar Sesión',
            'error' => $error
        ];
        
        $this->loadView('auth/login', $data);
    }
    
    public function register() {
        if (isset($_SESSION['usuario_id'])) {
            header('Location: /');
            exit();
        }
        
        $error = null;
        $success = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Validaciones
            if (empty($nombre) || empty($email) || empty($password) || empty($confirm_password)) {
                $error = 'Todos los campos son obligatorios.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'El correo electrónico no es válido.';
            } elseif (strlen($password) < 6) {
                $error = 'La contraseña debe tener al menos 6 caracteres.';
            } elseif ($password !== $confirm_password) {
                $error = 'Las contraseñas no coinciden.';
            } elseif ($this->userModel->emailExists($email)) {
                $error = 'El correo electrónico ya está registrado.';
            } else {
                try {
                    $this->userModel->create($nombre, $email, $password);
                    $success = 'Registro exitoso. Ya puedes iniciar sesión.';
                    
                    // Limpiar formulario
                    $_POST = [];
                } catch (Exception $e) {
                    $error = 'Error al registrar usuario. Intente nuevamente.';
                }
            }
        }
        
        $data = [
            'page_title' => 'Registrarse',
            'error' => $error,
            'success' => $success
        ];
        
        $this->loadView('auth/register', $data);
    }
    
    public function logout() {
        session_destroy();
        header('Location: /');
        exit();
    }
    
    private function loadView($view, $data = []) {
        extract($data);
        require_once "app/views/layouts/header.php";
        require_once "app/views/$view.php";
        require_once "app/views/layouts/footer.php";
    }
}
?>
