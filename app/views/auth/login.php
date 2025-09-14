<main>
    <div class="container">
        <div class="auth-form">
            <h1>Iniciar Sesión</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-large">Iniciar Sesión</button>
            </form>
            
            <div class="auth-links">
                <p>¿No tienes cuenta? <a href="/registro">Regístrate aquí</a></p>
            </div>
        </div>
    </div>
</main>
