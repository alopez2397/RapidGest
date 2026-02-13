<?php
require_once "config/config.php";
require_once "config/auth.php";

// Si ya está logueado, redirigir
if (Auth::check()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (Auth::login($username, $password)) {
        redirect('index.php');
    } else {
        $error = 'Usuario o contraseña incorrectos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RapidGest</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-4">
            
            <div class="card shadow">
                <div class="card-body p-5">
                    
                    <h3 class="text-center mb-4">🍕 RapidGest</h3>
                    <h5 class="text-center text-muted mb-4">Pizzeria La Fuente</h5>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            ❌ <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" name="username" class="form-control" 
                                   required autofocus>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control" 
                                   required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            Iniciar sesión
                        </button>
                    </form>
                    
                    <hr class="my-4">
                    
                    
                </div>
            </div>
            
        </div>
    </div>
</div>

</body>
</html>