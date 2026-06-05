<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/auth.php";
verificarRol('admin');
$pedidos = $conn->query("SELECT p.*, u.nombre, u.usuario 
                         FROM pedidos p 
                         JOIN usuarios u ON p.id_usuario = u.id 
                         ORDER BY p.fecha_pedido DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Pedidos</title>
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
            <li class="nav-item mb-2 bg-danger rounded"><a href="pedidos.php" class="nav-link text-white"><i class="bi bi-cart"></i> Pedidos</a></li>
            <li class="nav-item mb-2"><a href="reportes.php" class="nav-link text-white"><i class="bi bi-graph-up"></i> Reportes</a></li>
            <li class="nav-item mb-2"><a href="usuarios.php" class="nav-link text-white"><i class="bi bi-people"></i> Usuarios</a></li>
            <li class="nav-item mb-2"><a href="categorias.php" class="nav-link text-white"><i class="bi bi-tags"></i> Secciones/Categorías</a></li>
            <li class="nav-item mb-2"><a href="../index.php" class="nav-link text-white"><i class="bi bi-shop"></i> Ir a Tienda</a></li>
            <li class="nav-item mt-4"><a href="../logout.php" class="nav-link text-white"><i class="bi bi-box-arrow-left"></i> Cerrar Sesión</a></li>
        </ul>
    </div>

    <!-- CONTENIDO -->
    <div class="flex-grow-1 p-4">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Pedidos / Ventas</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Folio</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($pedidos->num_rows > 0): ?>
                                <?php while($p = $pedidos->fetch_assoc()): ?>
                                <tr>
                                    <td><strong>#<?php echo $p['id']; ?></strong></td>
                                    <td><?php echo $p['cliente'] ?: 'Público'; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($p['fecha'])); ?></td>
                                    <td>$<?php echo number_format($p['total'], 2); ?></td>
                                    <td><span class="badge bg-success"><?php echo ucfirst($p['estado']); ?></span></td>
                                    <td>
                                        <a href="detalle_pedido.php?id=<?php echo $p['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted">No hay pedidos registrados</td></tr>
                            <?php endif; ?>
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