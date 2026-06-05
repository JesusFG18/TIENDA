<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/auth.php";
verificarRol('admin');

$msg = '';

function generarPassword($longitud = 8) {
    return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $longitud);
}

// REGISTRAR USUARIO
if(isset($_POST['registrar'])){
    $rol = $_POST['rol'];
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);
    
    // Solo cliente pide nombre y telefono
    $nombre = ($rol == 'cliente') ? trim($_POST['nombre']) : $usuario;
    $telefono = ($rol == 'cliente') ? trim($_POST['telefono']) : '';
    
    if(empty($usuario)){
        $msg = '<div class="alert alert-danger">El usuario es obligatorio</div>';
    } else {
        if(empty($password)) $password = generarPassword(8);
        
        $pass_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO usuarios (usuario, password, rol, nombre, telefono, activo, creado_por) VALUES (?, ?, ?, ?, ?, 1, ?)");
        $stmt->bind_param("sssssi", $usuario, $pass_hash, $rol, $nombre, $telefono, $_SESSION['id_usuario']);
        
        if($stmt->execute()){
            $msg = '<div class="alert alert-success">Usuario <strong>'.$usuario.'</strong> creado. Contraseña: <strong>'.$password.'</strong></div>';
        } else {
            $msg = '<div class="alert alert-danger">Error: El usuario ya existe</div>';
        }
    }
}

// ELIMINAR USUARIO - BORRADO COMPLETO DE BD
if(isset($_GET['eliminar'])){
    $id = intval($_GET['eliminar']);
    if($id != $_SESSION['id_usuario']){
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: usuarios.php?ok=1");
        exit();
    }
}

// CARGAR USUARIOS - QUITÉ EL WHERE activo = 1 PORQUE YA SE BORRAN
$usuarios = $conn->query("SELECT id, usuario, rol, nombre, telefono FROM usuarios ORDER BY rol DESC, id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Usuarios</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
body { background-color: #f8f9fa; }
.sidebar { min-height: 100vh; background-color: #5e1920; }
</style>
</head>
<body>

<div class="d-flex">
    <!-- SIDEBAR -->
    <div class="sidebar text-white p-3" style="width: 250px;">
        <h4 class="text-center mb-4">ADMIN</h4>
        <ul class="nav flex-column">
            <li class="nav-item mb-2"><a href="inventario.php" class="nav-link text-white"><i class="bi bi-box"></i> Inventario</a></li>
            <li class="nav-item mb-2"><a href="pedidos.php" class="nav-link text-white"><i class="bi bi-cart"></i> Pedidos</a></li>
            <li class="nav-item mb-2"><a href="reportes.php" class="nav-link text-white"><i class="bi bi-graph-up"></i> Reportes</a></li>
            <li class="nav-item mb-2 bg-danger rounded"><a href="usuarios.php" class="nav-link text-white"><i class="bi bi-people"></i> Usuarios</a></li>
            <li class="nav-item mb-2"><a href="categorias.php" class="nav-link text-white"><i class="bi bi-tags"></i> Secciones/Categorías</a></li>
            <li class="nav-item mb-2"><a href="../index.php" class="nav-link text-white"><i class="bi bi-shop"></i> Ir a Tienda</a></li>
            <li class="nav-item mt-4"><a href="../logout.php" class="nav-link text-white"><i class="bi bi-box-arrow-left"></i> Cerrar Sesión</a></li>
        </ul>
    </div>

    <!-- CONTENIDO -->
    <div class="flex-grow-1 p-4">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Gestión de Usuarios</h5>
            </div>
            <div class="card-body">
                
                <?php echo $msg; ?>
                <?php if(isset($_GET['ok'])): ?>
                <div class="alert alert-danger">Usuario eliminado permanentemente de la base de datos</div>
                <?php endif; ?>

                <!-- FORMULARIO DINÁMICO -->
                <form method="POST" class="mb-4">
                    <div class="row">
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Tipo Usuario</label>
                            <select name="rol" id="selectRol" class="form-select" required>
                                <option value="cliente">Cliente</option>
                                <option value="vendedor">Vendedor</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Usuario *</label>
                            <input type="text" name="usuario" class="form-control" placeholder="Ej: juan123" required>
                        </div>
                        <div class="col-md-3 mb-3 campo-cliente">
                            <label class="form-label">Nombre completo</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Solo para clientes">
                        </div>
                        <div class="col-md-2 mb-3 campo-cliente">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" name="telefono" class="form-control" placeholder="6120000000">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Contraseña *</label>
                            <input type="text" name="password" class="form-control" placeholder="Vacío = aleatoria">
                        </div>
                        <div class="col-md-1 mb-3 d-flex align-items-end">
                            <button type="submit" name="registrar" class="btn btn-success w-100">
                                <i class="bi bi-person-plus"></i>
                            </button>
                        </div>
                    </div>
                </form>

                <!-- TABLA -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Usuario</th>
                                <th>Nombre</th>
                                <th>Rol</th>
                                <th>Teléfono</th>
                                <th>Contraseña</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($usuarios->num_rows > 0): ?>
                                <?php while($u = $usuarios->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo $u['usuario']; ?></strong></td>
                                    <td><?php echo $u['nombre']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $u['rol']=='admin'?'danger':($u['rol']=='vendedor'?'warning text-dark':'secondary'); ?>">
                                            <?php echo ucfirst($u['rol']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $u['telefono'] ?: '-'; ?></td>
                                    <td><code>••••••••</code></td>
                                    <td>
                                        <?php if($u['id'] != $_SESSION['id_usuario']): ?>
                                        <a href="?eliminar=<?php echo $u['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿ELIMINAR PERMANENTEMENTE a <?php echo $u['usuario']; ?>? Esta acción no se puede deshacer.')">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </a>
                                        <?php else: ?>
                                        <span class="badge bg-info">Tú</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted">No hay usuarios registrados</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('selectRol').addEventListener('change', function() {
    const camposCliente = document.querySelectorAll('.campo-cliente');
    if(this.value === 'cliente') {
        camposCliente.forEach(c => c.style.display = 'block');
    } else {
        camposCliente.forEach(c => c.style.display = 'none');
    }
});
document.getElementById('selectRol').dispatchEvent(new Event('change'));
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>