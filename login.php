<?php
session_start();
require_once "includes/db.php";

if(isset($_SESSION['rol'])){
    if($_SESSION['rol'] == 'admin'){
        header("Location: admin/inventario.php");
    } elseif($_SESSION['rol'] == 'vendedor'){
        header("Location: vendedor/punto_venta.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

function generarPassword($longitud = 12) {
    $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
    return substr(str_shuffle($caracteres), 0, $longitud);
}

$error = '';
$vista = $_GET['vista'] ?? 'login';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    
    // REGISTRAR CLIENTE - SOLO NOMBRE, USUARIO, TELEFONO, PASSWORD
    if(isset($_POST['registrar'])){
        $nombre = trim($_POST['nombre']);
        $usuario = trim($_POST['usuario']);
        $telefono = trim($_POST['telefono']);
        $password = trim($_POST['password']);
        
        if(empty($nombre) || empty($usuario) || empty($telefono) || empty($password)){
            $error = 'Completa todos los campos';
            $vista = 'registro';
        } elseif(strlen($password) < 6){
            $error = 'La contraseña debe tener mínimo 6 caracteres';
            $vista = 'registro';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuarios (usuario, password, rol, nombre, telefono) VALUES (?, ?, 'cliente', ?, ?)");
            $stmt->bind_param("ssss", $usuario, $password_hash, $nombre, $telefono);
            
            if($stmt->execute()){
                $_SESSION['id_usuario'] = $conn->insert_id;
                $_SESSION['usuario'] = $usuario;
                $_SESSION['rol'] = 'cliente';
                $_SESSION['nombre'] = $nombre;
                header("Location: index.php");
                exit();
            } else {
                $error = 'El usuario ya existe';
                $vista = 'registro';
            }
        }
    }
    
    // LOGIN
    if(isset($_POST['entrar'])){
        $usuario = trim($_POST['usuario']);
        $password = trim($_POST['password']);
        
        // BYPASS ESPECIAL SOLO PARA admin1
        if($usuario == 'admin1'){
    $stmt = $conn->prepare("SELECT id, usuario, password, rol, nombre, activo FROM usuarios WHERE usuario = 'admin1'");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($user = $result->fetch_assoc()){
        if(!$user['activo']){
            $error = 'Usuario desactivado';
        } elseif(password_verify($password, $user['password'])){
            // ✓ Contraseña correcta → entra normal
            $_SESSION['id_usuario'] = $user['id'];
            $_SESSION['usuario']    = $user['usuario'];
            $_SESSION['rol']        = $user['rol'];
            $_SESSION['nombre']     = $user['nombre'];
            header("Location: admin/inventario.php");
            exit();
        } else {
            $error = 'Contraseña incorrecta';
        }
    }
}
        
        // LOGIN NORMAL PARA TODOS LOS DEMÁS
        else {
            $stmt = $conn->prepare("SELECT id, usuario, password, rol, nombre, activo FROM usuarios WHERE usuario = ?");
            $stmt->bind_param("s", $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if($user = $result->fetch_assoc()){
                if(!$user['activo']){
                    $error = 'Usuario desactivado';
                } elseif(password_verify($password, $user['password'])){
                    $_SESSION['id_usuario'] = $user['id'];
                    $_SESSION['usuario'] = $user['usuario'];
                    $_SESSION['rol'] = $user['rol'];
                    $_SESSION['nombre'] = $user['nombre'];
                    
                    if($user['rol'] == 'admin') header("Location: admin/inventario.php");
                    elseif($user['rol'] == 'vendedor') header("Location: vendedor/punto_venta.php");
                    else header("Location: index.php");
                    exit();
                } else {
                    $error = 'Contraseña incorrecta';
                }
            } else {
                $error = 'Usuario no encontrado';
            }
        }
    }
} 
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Novedades Economica</title>
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
        
        <?php if($vista == 'login'): ?>
        <!-- LOGIN -->
        <h2 class="text-center mb-1 fw-bold">Bienvenido</h2>
        <p class="text-center text-muted mb-4">Inicia sesión en tu cuenta</p>
        
        <?php if(isset($_GET['reset'])): ?>
        <div class="alert alert-success">Contraseña actualizada correctamente. Ya puedes iniciar sesión.</div>
        <?php endif; ?>
        
        <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

       <form method="POST">
            <div class="mb-3">
                <label class="form-label">Usuario</label>
                <input type="text" name="usuario" class="form-control" placeholder="Nombre de usuario" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" placeholder="Tu contraseña" required>
            </div>

            <button type="submit" name="entrar" class="btn btn-dark w-100 fw-bold mb-3">Entrar</button>
            
            <div class="text-center mb-3">
                <a href="recuperar.php" class="text-decoration-none">¿Olvidaste tu contraseña?</a>
            </div>
            
            <div class="text-center">
                <p class="mb-0">¿No tienes cuenta? 
                    <a href="?vista=registro" class="text-decoration-none fw-bold">Regístrate aquí</a>
                </p>
            </div>
        </form>

        <?php else: ?>
        <!-- REGISTRO CLIENTE - SOLO 4 CAMPOS -->
        <h2 class="text-center mb-1 fw-bold">Crear Cuenta</h2>
        <p class="text-center text-muted mb-4">Regístrate y empieza a comprar</p>
        
        <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nombre completo</label>
                <input type="text" name="nombre" class="form-control" placeholder="Ej: Juan Pérez" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Usuario</label>
                <input type="text" name="usuario" class="form-control" placeholder="Ej: juan123" required>
                <small class="text-muted">Este será tu nombre para iniciar sesión</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Número de teléfono</label>
                <input type="tel" name="telefono" class="form-control" placeholder="6120000000" required>
                <small class="text-muted">Lo usarás para recuperar tu contraseña</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" required>
            </div>

            <button type="submit" name="registrar" class="btn btn-success w-100 fw-bold mb-3">
                <i class="bi bi-person-plus"></i> Crear mi cuenta
            </button>
            
            <div class="text-center">
                <p class="mb-2">¿Ya tienes cuenta?</p>
                <a href="?vista=login" class="btn btn-outline-dark w-100">Iniciar Sesión</a>
            </div>
        </form>
        <?php endif; ?>

    </div>
</div>

</body>
</html>