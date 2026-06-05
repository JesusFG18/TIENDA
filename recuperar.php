<?php
session_start();
require_once "includes/db.php";

$msg = '';

if(isset($_POST['enviar_codigo'])){
    $telefono = trim($_POST['telefono']);
    
    // Buscar usuario con ese teléfono
    $stmt = $conn->prepare("SELECT id, usuario, nombre FROM usuarios WHERE telefono = ? AND activo = 1");
    $stmt->bind_param("s", $telefono);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0){
        $user = $result->fetch_assoc();
        $codigo = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expira = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        // Guardar/actualizar código
        $stmt = $conn->prepare("INSERT INTO codigos_reset (id_usuario, codigo, expira) VALUES (?, ?, ?) 
                               ON DUPLICATE KEY UPDATE codigo = ?, expira = ?");
        $stmt->bind_param("issss", $user['id'], $codigo, $expira, $codigo, $expira);
        $stmt->execute();
        
        // GUARDAR EN SESIÓN PARA EL SIGUIENTE PASO
        $_SESSION['id_reset'] = $user['id'];
        $_SESSION['telefono_reset'] = $telefono;
        $_SESSION['nombre_reset'] = $user['nombre'];
        
        // SOLO PARA PRUEBAS - QUITAR EN PRODUCCIÓN
        $_SESSION['codigo_temp'] = $codigo;
        
        // AQUÍ IRÍA EL ENVÍO DE SMS REAL CON TWILIO
        // Por ahora solo redirige
        
        header("Location: verificar_codigo.php");
        exit();
        
    } else {
        $msg = '<div class="alert alert-danger">No existe ninguna cuenta con ese teléfono</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recuperar Contraseña</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
body{
    background-color: #5e1920;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}
.card-login{
    width: 100%;
    max-width: 450px;
    border-radius: 15px;
}
.logo-tienda{
    font-size: 2rem;
    color: white;
    text-align: center;
    margin-bottom: 20px;
    font-weight: bold;
}
</style>
</head>
<body>

<div class="container">
    <div class="logo-tienda">
        <i class="bi bi-shop"></i> NOVEDADES ECONÓMICA
    </div>
    
    <div class="card card-login p-4 shadow mx-auto">
        <h2 class="text-center mb-1 fw-bold">Recuperar Contraseña</h2>
        <p class="text-center text-muted mb-4">Ingresa tu número de teléfono</p>
        
        <?php echo $msg; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Número de teléfono</label>
                <input type="tel" name="telefono" class="form-control" placeholder="6120000000" required autofocus>
                <small class="text-muted">Te enviaremos un código de 6 dígitos</small>
            </div>
            
            <button type="submit" name="enviar_codigo" class="btn btn-dark w-100 fw-bold mb-3">
                <i class="bi bi-send"></i> Enviar Código
            </button>
            
            <div class="text-center">
                <a href="login.php" class="text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Volver al login
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>