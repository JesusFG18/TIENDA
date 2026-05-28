<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Inicializar array de clientes si no existe
if(!isset($_SESSION['clientes'])){
    $_SESSION['clientes'] = [];
}

// USUARIOS POR DEFECTO PARA PRUEBAS
if(!isset($_SESSION['usuarios_default_creados'])){
    // Cliente de prueba
    $_SESSION['clientes']['Clien1'] = [
        'tel' => '6120000001',
        'pass' => '123'
    ];
    
    $_SESSION['usuarios_default_creados'] = true;
}

// Si ya inició sesión, mándalo según su rol
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
$error = '';
$vista = $_GET['vista'] ?? 'login';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    
    // REGISTRAR CLIENTE NUEVO
    if(isset($_POST['registrar'])){
        $nombre = trim($_POST['nombre']);
        $telefono = trim($_POST['telefono']);
        $password = trim($_POST['password']);
        
        if(empty($nombre) || empty($telefono) || empty($password)){
            $error = 'Completa todos los campos';
            $vista = 'registro';
        } 
        elseif(isset($_SESSION['clientes'][$nombre])){
            $error = 'Ese usuario ya está registrado';
            $vista = 'registro';
        } 
        else {
            // Guardar cliente
            $_SESSION['clientes'][$nombre] = [
                'tel' => $telefono,
                'pass' => $password
            ];
            // Iniciar sesión directo
            $_SESSION['usuario'] = $nombre;
            $_SESSION['rol'] = 'cliente';
            header("Location: index.php");
            exit();
        }
    }
    
    // LOGIN NORMAL
    if(isset($_POST['entrar'])){
        $rol = $_POST['rol'] ?? '';
        $usuario = trim($_POST['usuario'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        if(empty($rol)){
            $error = 'Selecciona un rol';
        }
        elseif(empty($usuario)){
            $error = 'Escribe un nombre de usuario';
        }
        // ADMIN: entra con cualquier dato
        elseif($rol == 'admin'){
            $_SESSION['usuario'] = $usuario;
            $_SESSION['rol'] = 'admin';
            header("Location: admin/inventario.php");
            exit();
        }
        // VENDEDOR: entra con cualquier dato
        elseif($rol == 'vendedor'){
            $_SESSION['usuario'] = $usuario;
            $_SESSION['rol'] = 'vendedor';
            header("Location: vendedor/punto_venta.php");
            exit();
        }
        // CLIENTE: validar contra los registrados
        elseif($rol == 'cliente'){
            if(empty($password)){
                $error = 'Escribe tu contraseña';
            }
            elseif(isset($_SESSION['clientes'][$usuario]) && $_SESSION['clientes'][$usuario]['pass'] == $password){
                $_SESSION['usuario'] = $usuario;
                $_SESSION['rol'] = 'cliente';
                header("Location: index.php");
                exit();
            } else {
                $error = 'Usuario o contraseña incorrectos';
            }
        }
    }
} // <- LLAVE QUE FALTABA
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Iniciar Sesión</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    max-width: 400px;
    border-radius: 15px;
}
</style>
</head>
<body>

<div class="card card-login p-4 shadow">
    
    <?php if($vista == 'login'): ?>
    <!-- VISTA LOGIN -->
    <h2 class="text-center mb-4 fw-bold">Iniciar Sesión</h2>
    
    <?php if($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

   <form method="POST">
    <!-- 1. ROL PRIMERO Y CON CLIENTE POR DEFECTO -->
    <div class="mb-3">
        <label class="form-label">Selecciona un Rol</label>
        <select name="rol" id="selectRol" class="form-select" required>
            <option value="cliente" selected>Cliente</option>
            <option value="admin">Admin</option>
            <option value="vendedor">Vendedor</option>
        </select>
    </div>

    <!-- 2. USUARIO -->
    <div class="mb-3">
        <label class="form-label">Usuario</label>
        <input type="text" name="usuario" class="form-control" placeholder="Nombre de usuario" required>
    </div>

    <!-- 3. CONTRASEÑA VISIBLE POR DEFECTO PORQUE ROL = CLIENTE -->
    <div class="mb-3" id="campoPassword">
        <label class="form-label">Contraseña</label>
        <input type="password" name="password" id="inputPassword" class="form-control" required>
        <small class="text-muted" id="avisoPass" style="display:none;">Admin/Vendedor no necesitan contraseña</small>
    </div>

    <button type="submit" name="entrar" class="btn btn-dark w-100 fw-bold mb-2">Entrar</button>
    
    <!-- 4. LINK VISIBLE POR DEFECTO PORQUE ROL = CLIENTE -->
    <div class="text-center" id="linkRegistro">
        <a href="?vista=registro" class="text-decoration-none">¿Eres cliente nuevo? Regístrate</a>
    </div>
</form>

    <?php else: ?>
    <!-- VISTA REGISTRO CLIENTE -->
    <h2 class="text-center mb-4 fw-bold">Registrar Cliente</h2>
    
    <?php if($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Nombre / Usuario</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Teléfono</label>
            <input type="text" name="telefono" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" name="registrar" class="btn btn-success w-100 fw-bold mb-2">Registrar Usuario</button>
        
        <div class="text-center">
            <a href="login.php" class="text-decoration-none">¿Ya tienes cuenta? Inicia Sesión</a>
        </div>
    </form>
    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectRol = document.getElementById('selectRol');
    const campoPass = document.getElementById('campoPassword');
    const linkRegistro = document.getElementById('linkRegistro');
    const avisoPass = document.getElementById('avisoPass');
    const inputPassword = document.getElementById('inputPassword');
    
    function actualizarVista() {
        const rol = selectRol.value;
        
        if(rol === 'admin' || rol === 'vendedor'){
            campoPass.style.display = 'none';
            linkRegistro.style.display = 'none';
            inputPassword.removeAttribute('required');
            inputPassword.value = '';
        } 
        else if(rol === 'cliente'){
            campoPass.style.display = 'block';
            linkRegistro.style.display = 'block';
            avisoPass.style.display = 'none';
            inputPassword.setAttribute('required', 'required');
        }
    }
    
    selectRol.addEventListener('change', actualizarVista);
    actualizarVista();
});
</script>

</body>
</html>