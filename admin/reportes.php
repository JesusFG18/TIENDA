<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/auth.php";
verificarRol('admin');

// VENTAS DE HOY
$ventas_hoy = $conn->query("SELECT COUNT(*) as total, SUM(total) as monto 
                            FROM pedidos 
                            WHERE DATE(fecha_pedido) = CURDATE() AND estado != 'cancelado'")->fetch_assoc();

// VENTAS DEL MES
$ventas_mes = $conn->query("SELECT COUNT(*) as total, SUM(total) as monto 
                            FROM pedidos 
                            WHERE MONTH(fecha_pedido) = MONTH(CURDATE()) 
                            AND YEAR(fecha_pedido) = YEAR(CURDATE()) 
                            AND estado != 'cancelado'")->fetch_assoc();

// PRODUCTOS MÁS VENDIDOS
$mas_vendidos = $conn->query("SELECT p.nombre, SUM(dp.cantidad) as total_vendido
                              FROM detalle_pedidos dp
                              JOIN productos p ON dp.id_producto = p.id
                              JOIN pedidos pe ON dp.id_pedido = pe.id
                              WHERE pe.estado != 'cancelado'
                              GROUP BY p.id, p.nombre
                              ORDER BY total_vendido DESC
                              LIMIT 5");

// TOTAL USUARIOS
$total_usuarios = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'cliente'")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Reportes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
body { background-color: #f8f9fa; }
.sidebar { min-height: 100vh; background-color: #5e1920; }
</style>
</head>
<body>

<div class="d-flex">
    <div class="sidebar text-white p-3" style="width: 250px;">
        <h4 class="text-center mb-4">ADMIN</h4>
        <ul class="nav flex-column">
            <li class="nav-item mb-2"><a href="inventario.php" class="nav-link text-white"><i class="bi bi-box"></i> Inventario</a></li>
            <li class="nav-item mb-2"><a href="pedidos.php" class="nav-link text-white"><i class="bi bi-cart"></i> Pedidos</a></li>
            <li class="nav-item mb-2 bg-danger rounded"><a href="reportes.php" class="nav-link text-white"><i class="bi bi-graph-up"></i> Reportes</a></li>
            <li class="nav-item mb-2"><a href="usuarios.php" class="nav-link text-white"><i class="bi bi-people"></i> Usuarios</a></li>
            <li class="nav-item mb-2"><a href="categorias.php" class="nav-link text-white"><i class="bi bi-tags"></i> Secciones/Categorías</a></li>
            <li class="nav-item mt-4"><a href="../logout.php" class="nav-link text-white"><i class="bi bi-box-arrow-left"></i> Cerrar Sesión</a></li>
        </ul>
    </div>

    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">Reportes y Estadísticas</h2>
        
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h6>Ventas Hoy</h6>
                        <h3><?php echo $ventas_hoy['total'] ?? 0; ?></h3>
                        <small>$<?php echo number_format($ventas_hoy['monto'] ?? 0, 2); ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h6>Ventas del Mes</h6>
                        <h3><?php echo $ventas_mes['total'] ?? 0; ?></h3>
                        <small>$<?php echo number_format($ventas_mes['monto'] ?? 0, 2); ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h6>Clientes</h6>
                        <h3><?php echo $total_usuarios['total'] ?? 0; ?></h3>
                        <small>Registrados</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Productos Más Vendidos</h5>
            </div>
            <div class="card-body">
                <?php if($mas_vendidos->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad Vendida</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($p = $mas_vendidos->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $p['nombre']; ?></td>
                            <td><span class="badge bg-success"><?php echo $p['total_vendido']; ?> unidades</span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-muted text-center">No hay ventas registradas aún</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>