<?php
require_once 'config/database.php';
require_once 'config/session.php';

$page_title = 'Registro';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validaciones
    if ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden';
    } else {
        // Verificar si el email ya existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'El correo electrónico ya está registrado';
        } else {
            // Crear usuario
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
            
            if ($stmt->execute([$nombre, $email, $hashed_password])) {
                $success = 'Registro exitoso. Ya puedes iniciar sesión.';
            } else {
                $error = 'Error al crear la cuenta';
            }
        }
    }
}

include 'includes/header.php';
?>

<main>
    <div class="container">
        <div class="auth-form">
            <h1>Registro</h1>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="nombre">Nombre Completo:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Registrarse</button>
            </form>
            
            <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
