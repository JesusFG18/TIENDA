<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/auth.php";
verificarRol('admin');

$msg = '';

// AGREGAR CATEGORIA
if(isset($_POST['agregar'])){
    $nombre = trim($_POST['nombre']);
    if(!empty($nombre)){
        $stmt = $conn->prepare("INSERT INTO categorias (nombre, activo) VALUES (?, 1)");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        header("Location: categorias.php?ok=1");
        exit();
    }
}

// ELIMINAR CATEGORIA
if(isset($_GET['eliminar'])){
    $id = intval($_GET['eliminar']);
    $conn->query("UPDATE categorias SET activo = 0 WHERE id = $id");
    header("Location: categorias.php?del=1");
    exit();
}

$categorias = $conn->query("SELECT * FROM categorias WHERE activo = 1 ORDER BY nombre");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Categorías</title>
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
            <li class="nav-item mb-2"><a href="usuarios.php" class="nav-link text-white"><i class="bi bi-people"></i> Usuarios</a></li>
            <li class="nav-item mb-2 bg-danger rounded"><a href="categorias.php" class="nav-link text-white"><i class="bi bi-tags"></i> Secciones/Categorías</a></li>
            <li class="nav-item mb-2"><a href="../index.php" class="nav-link text-white"><i class="bi bi-shop"></i> Ir a Tienda</a></li>
            <li class="nav-item mt-4"><a href="../logout.php" class="nav-link text-white"><i class="bi bi-box-arrow-left"></i> Cerrar Sesión</a></li>
        </ul>
    </div>

    <!-- CONTENIDO -->
    <div class="flex-grow-1 p-4">
        <?php if(isset($_GET['ok'])): ?>
        <div class="alert alert-success">Categoría agregada correctamente</div>
        <?php endif; ?>

        <?php if(isset($_GET['del'])): ?>
        <div class="alert alert-warning">Categoría eliminada</div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Secciones / Categorías</h5>
            </div>
            <div class="card-body">
                
                <form method="POST" class="mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Nombre de la Categoría</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej: Ropa, Calzado, Accesorios" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" name="agregar" class="btn btn-success w-100">
                                <i class="bi bi-plus-circle"></i> Agregar
                            </button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($cat = $categorias->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $cat['id']; ?></td>
                                <td><strong><?php echo $cat['nombre']; ?></strong></td>
                                <td>
                                    <a href="?eliminar=<?php echo $cat['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar categoría?')">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>