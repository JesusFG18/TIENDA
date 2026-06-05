<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/auth.php";
verificarRol('admin');

// MOSTRAR PASSWORD TEMPORAL DE ADMIN1
$mostrar_pass = false;
if(isset($_SESSION['admin_pass_temporal'])){
    $mostrar_pass = $_SESSION['admin_pass_temporal'];
    unset($_SESSION['admin_pass_temporal']);
}

// AGREGAR PRODUCTO
if(isset($_POST['agregar_producto'])){
    $nombre = trim($_POST['nombre']);
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $categoria = $_POST['categoria'];
    
    $stmt = $conn->prepare("INSERT INTO productos (nombre, precio, stock, categoria, activo) VALUES (?, ?, ?, ?, 1)");
    $stmt->bind_param("sdis", $nombre, $precio, $stock, $categoria);
    $stmt->execute();
    header("Location: inventario.php?ok=1");
    exit();
}

// ELIMINAR PRODUCTO
if(isset($_GET['eliminar'])){
    $id = intval($_GET['eliminar']);
    $conn->query("UPDATE productos SET activo = 0 WHERE id = $id");
    header("Location: inventario.php?del=1");
    exit();
}

// CARGAR PRODUCTOS
$productos = $conn->query("SELECT * FROM productos WHERE activo = 1 ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Inventario</title>
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
            <li class="nav-item mb-2 bg-danger rounded"><a href="inventario.php" class="nav-link text-white"><i class="bi bi-box"></i> Inventario</a></li>
            <li class="nav-item mb-2"><a href="pedidos.php" class="nav-link text-white"><i class="bi bi-cart"></i> Pedidos</a></li>
            <li class="nav-item mb-2"><a href="reportes.php" class="nav-link text-white"><i class="bi bi-graph-up"></i> Reportes</a></li>
            <li class="nav-item mb-2"><a href="usuarios.php" class="nav-link text-white"><i class="bi bi-people"></i> Usuarios</a></li>
            <li class="nav-item mb-2"><a href="categorias.php" class="nav-link text-white"><i class="bi bi-tags"></i> Secciones/Categorías</a></li>
            <li class="nav-item mb-2"><a href="../index.php" class="nav-link text-white"><i class="bi bi-shop"></i> Ir a Tienda</a></li>
            <li class="nav-item mt-4"><a href="../logout.php" class="nav-link text-white"><i class="bi bi-box-arrow-left"></i> Cerrar Sesión</a></li>
        </ul>
    </div>

    <!-- CONTENIDO -->
    <div class="flex-grow-1 p-4">
        
        <?php if($mostrar_pass): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <h5><i class="bi bi-key-fill"></i> Contraseña temporal generada</h5>
            <p class="mb-0">Tu nueva contraseña para <strong>admin1</strong> es: <code class="fs-5 text-dark"><?php echo $mostrar_pass; ?></code></p>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if(isset($_GET['ok'])): ?>
        <div class="alert alert-success">Producto agregado correctamente</div>
        <?php endif; ?>

        <?php if(isset($_GET['del'])): ?>
        <div class="alert alert-warning">Producto eliminado</div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Inventario de Productos</h5>
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalProducto">
                    <i class="bi bi-plus-circle"></i> Agregar Producto
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Categoría</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($productos->num_rows > 0): ?>
                                <?php while($p = $productos->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $p['id']; ?></td>
                                    <td><?php echo $p['nombre']; ?></td>
                                    <td>$<?php echo number_format($p['precio'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $p['stock'] > 10 ? 'success' : ($p['stock'] > 0 ? 'warning' : 'danger'); ?>">
                                            <?php echo $p['stock']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $p['categoria']; ?></td>
                                    <td>
                                        <a href="?eliminar=<?php echo $p['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar producto?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted">No hay productos registrados</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL AGREGAR PRODUCTO -->
<div class="modal fade" id="modalProducto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Precio</label>
                        <input type="number" step="0.01" name="precio" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stock</label>
                        <input type="number" name="stock" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <input type="text" name="categoria" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="agregar_producto" class="btn btn-success">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>