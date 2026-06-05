<?php
session_start();
require_once "includes/db.php";

if(!isset($_SESSION['id_reset'])){
    header("Location: recuperar.php");
    exit();
}

$msg = '';
$paso = isset($_SESSION['codigo_verificado']) ? 2 : 1;

// PASO 1: VERIFICAR CÓDIGO
if(isset($_POST['verificar'])){
    $codigo = trim($_POST['codigo']);
    $id_user = $_SESSION['id_reset'];
    
    $stmt = $conn->prepare("SELECT * FROM codigos_reset WHERE id_usuario = ? AND codigo = ? AND expira > NOW()");
    $stmt->bind_param("is", $id_user, $codigo);
    $stmt->execute();
    
    if($stmt->get_result()->num_rows > 0){
        $_SESSION['codigo_verificado'] = true;
        $paso = 2;
    } else {
        $msg = '<div class="alert alert-danger">Código incorrecto o expirado. Intenta de nuevo.</div>';
    }
}

// PASO 2: CAMBIAR CONTRASEÑA
if(isset($_POST['cambiar']) && isset($_SESSION['codigo_verificado'])){
    $pass1 = $_POST['password1'];
    $pass2 = $_POST['password2'];
    
    if(empty($pass1) || empty($pass2)){
        $msg = '<div class="alert alert-danger">Completa ambos campos</div>';
    } elseif($pass1 !== $pass2){
        $msg = '<div class="alert alert-danger">Las contraseñas no coinciden</div>';
    } elseif(strlen($pass1) < 6){
        $msg = '<div class="alert alert-danger">La contraseña debe tener mínimo 6 caracteres</div>';
    } else {
        $pass_hash = password_hash($pass1, PASSWORD_DEFAULT);
        $id_user = $_SESSION['id_reset'];
        
        // Actualizar contraseña
        $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $pass_hash, $id_user);
        $stmt->execute();
        
        // Borrar código usado
        $conn->query("DELETE FROM codigos_reset WHERE id_usuario = $id_user");
        
        // Limpiar sesión y redirigir
        session_destroy();
        header("Location: login.php?reset=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verificar Código</title>
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
        
        <?php if($paso == 1): ?>
        <!-- PASO 1: INGRESAR CÓDIGO -->
        <h2 class="text-center mb-1 fw-bold">Verificar Código</h2>
        <p class="text-center text-muted mb-4">Ingresa el código de 6 dígitos</p>
        
        <?php echo $msg; ?>
        
        <?php if(isset($_SESSION['codigo_temp'])): ?>
        <div class="alert alert-warning">
            <strong>MODO PRUEBA:</strong> Tu código es <strong><?php echo $_SESSION['codigo_temp']; ?></strong>
            <br><small>En producción esto se envía por SMS</small>
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Código de verificación</label>
                <input type="text" name="codigo" class="form-control text-center fs-4" maxlength="6" placeholder="000000" required autofocus>
                <small class="text-muted">Enviado a: <?php echo $_SESSION['telefono_reset']; ?></small>
            </div>
            
            <button type="submit" name="verificar" class="btn btn-dark w-100 fw-bold mb-3">
                <i class="bi bi-check-circle"></i> Verificar Código
            </button>
            
            <div class="text-center">
                <a href="recuperar.php" class="text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Cambiar número
                </a>
            </div>
        </form>
        
        <?php else: ?>
        <!-- PASO 2: NUEVA CONTRASEÑA -->
        <h2 class="text-center mb-1 fw-bold">Nueva Contraseña</h2>
        <p class="text-center text-muted mb-4">Hola <?php echo $_SESSION['nombre_reset']; ?>, crea tu nueva contraseña</p>
        
        <?php echo $msg; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nueva Contraseña</label>
                <input type="password" name="password1" class="form-control" minlength="6" placeholder="Mínimo 6 caracteres" required autofocus>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Confirmar Contraseña</label>
                <input type="password" name="password2" class="form-control" minlength="6" placeholder="Repite la contraseña" required>
            </div>
            
            <button type="submit" name="cambiar" class="btn btn-success w-100 fw-bold mb-3">
                <i class="bi bi-check-lg"></i> Cambiar Contraseña
            </button>
        </form>
        <?php endif; ?>
        
    </div>
</div>

</body>
</html>