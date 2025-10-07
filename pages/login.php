<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Liga Panteras</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            text-align: center;
            color: white;
        }
        .login-header i {
            font-size: 60px;
            margin-bottom: 15px;
        }
        .login-body {
            padding: 40px 30px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: bold;
        }
        .btn-login:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card">
                    <div class="login-header">
                        <i class="fas fa-shield-alt"></i>
                        <h2 class="mb-0">Liga Panteras</h2>
                        <p class="mb-0">Sistema de Gestión</p>
                    </div>
                    
                    <div class="login-body">
                        <form id="form-login">
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-envelope me-2"></i>Correo Electrónico</label>
                                <input type="email" class="form-control" id="correo" placeholder="correo@ejemplo.com" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label"><i class="fas fa-lock me-2"></i>Contraseña</label>
                                <input type="password" class="form-control" id="password" placeholder="••••••••" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-login">
                                    <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <a href="dashboard_publico.php" class="text-muted text-decoration-none">
                                <i class="fas fa-globe me-2"></i>Ver Dashboard Público
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <p class="text-white">
                        <small>&copy; 2025 Liga Panteras. Todos los derechos reservados.</small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Login JS -->
    <script src="../js/login.js"></script>
</body>
</html>