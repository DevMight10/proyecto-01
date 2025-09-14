<?php
require_once 'config/database.php';
require_once 'config/session.php';

$page_title = 'Iniciar Sesión';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();
    
    if ($usuario && password_verify($password, $usuario['password'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['tipo'] = $usuario['tipo'];
        
        // Redirigir a la página anterior o al inicio
        $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
        header("Location: $redirect");
        exit();
    } else {
        $error = 'Credenciales inválidas';
    }
}

include 'includes/header.php';
?>

<main>
    <div class="container">
        <div class="auth-form">
            <h1>Iniciar Sesión</h1>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
            </form>
            
            <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
