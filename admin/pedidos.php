<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/auth.php";
verificarRol('admin');

// CANCELAR PEDIDO Y REGRESAR STOCK (opcional, ya que no hay botón pero se deja por si acaso)
if(isset($_POST['cancelar_pedido'])){
    $id_pedido = intval($_POST['id_pedido']);
    
    $conn->begin_transaction();
    try {
        // 1. Verificar que no esté ya cancelado
        $check = $conn->query("SELECT estado FROM pedidos WHERE id = $id_pedido")->fetch_assoc();
        if($check['estado'] == 'cancelado') {
            throw new Exception('Este pedido ya está cancelado');
        }
        
        // 2. Regresar stock de cada producto
        $detalles = $conn->query("SELECT id_producto, cantidad FROM detalle_pedidos WHERE id_pedido = $id_pedido");
        while($d = $detalles->fetch_assoc()){
            $stmt = $conn->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
            $stmt->bind_param("is", $d['cantidad'], $d['id_producto']);
            $stmt->execute();
        }
        
        // 3. Cambiar estado a cancelado
        $stmt = $conn->prepare("UPDATE pedidos SET estado = 'cancelado' WHERE id = ?");
        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();
        
        $conn->commit();
        header("Location: pedidos.php?ok=1");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: pedidos.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}

// CARGAR PEDIDOS
$pedidos = $conn->query("SELECT p.*, u.nombre as cliente_nombre, u.usuario 
                         FROM pedidos p 
                         LEFT JOIN usuarios u ON p.id_usuario = u.id 
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
.badge-pendiente { background-color: #ffc107; color: #000; }
.badge-pagado { background-color: #198754; }
.badge-enviado { background-color: #0dcaf0; color: #000; }
.badge-entregado { background-color: #0d6efd; }
.badge-cancelado { background-color: #dc3545; }
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
            <li class="nav-item mt-4"><a href="../logout.php" class="nav-link text-white"><i class="bi bi-box-arrow-left"></i> Cerrar Sesión</a></li>
        </ul>
    </div>

    <!-- CONTENIDO -->
    <div class="flex-grow-1 p-4">
        <?php if(isset($_GET['ok'])): ?>
        <div class="alert alert-success alert-dismissible fade show">Pedido cancelado y stock regresado<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        
        <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">Error: <?php echo htmlspecialchars($_GET['error']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

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
                            <?php 
                            $pedidos_array = [];
                            if($pedidos->num_rows > 0): 
                                while($p = $pedidos->fetch_assoc()): 
                                    $pedidos_array[] = $p;
                            ?>
                                <tr>
                                    <td><strong>#<?php echo $p['id']; ?></strong></td>
                                    <td><?php echo $p['cliente_nombre'] ?: ($p['usuario'] ?: 'Público'); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($p['fecha_pedido'])); ?></td>
                                    <td class="fw-bold">$<?php echo number_format($p['total'], 2); ?></td>
                                    <td><span class="badge badge-<?php echo $p['estado']; ?>"><?php echo ucfirst($p['estado']); ?></span></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalVer<?php echo $p['id']; ?>">
                                            <i class="bi bi-eye"></i> Ver
                                        </button>
                                    </td>
                                </tr>
                            <?php 
                                endwhile; 
                            else: 
                            ?>
                                <tr><td colspan="6" class="text-center text-muted">No hay pedidos registrados</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODALS (sin botón cancelar) -->
<?php foreach($pedidos_array as $p): ?>
<div class="modal fade" id="modalVer<?php echo $p['id']; ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Pedido #<?php echo $p['id']; ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Cliente:</strong> <?php echo $p['cliente_nombre'] ?: 'Público'; ?><br>
                        <strong>Usuario:</strong> <?php echo $p['usuario'] ?: 'N/A'; ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($p['fecha_pedido'])); ?><br>
                        <strong>Estado:</strong> <span class="badge badge-<?php echo $p['estado']; ?>"><?php echo ucfirst($p['estado']); ?></span>
                    </div>
                </div>
                <hr>
                <h6>Productos:</h6>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cant</th>
                            <th>Precio</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $det = $conn->query("SELECT dp.*, pr.nombre, pr.img_principal 
                                             FROM detalle_pedidos dp 
                                             LEFT JOIN productos pr ON dp.id_producto = pr.id 
                                             WHERE dp.id_pedido = " . $p['id']);
                        while($d = $det->fetch_assoc()):
                        ?>
                        <tr>
                            <td>
                                <?php $img_src = !empty($d['img_principal']) ? '../' . $d['img_principal'] : '../img/no-image.png'; ?>
                                <img src="<?php echo $img_src; ?>" width="40" class="me-2 rounded" onerror="this.onerror=null;this.src='../img/no-image.png';">
                                <?php echo htmlspecialchars($d['nombre']); ?>
                            </td>
                            <td><?php echo $d['cantidad']; ?></td>
                            <td>$<?php echo number_format($d['precio_unitario'], 2); ?></td>
                            <td>$<?php echo number_format($d['cantidad'] * $d['precio_unitario'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Total:</td>
                            <td class="fw-bold">$<?php echo number_format($p['total'], 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="modal-footer">
                <!-- Botón Cancelar Pedido eliminado -->
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>