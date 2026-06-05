<?php
session_start();
require_once "includes/db.php";
require_once "includes/auth.php";

// Solo clientes logueados
if(!isset($_SESSION['id_usuario'])){
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// Obtener pedidos del cliente - CORREGIDO CON PREPARED STATEMENT
$stmt = $conn->prepare("SELECT * FROM pedidos WHERE id_usuario = ? ORDER BY fecha_pedido DESC");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$pedidos = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mis Compras - Novedades Económica</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
:root { --primary: #5e1920; }
.navbar-custom { background-color: var(--primary); }
.btn-primary { background-color: var(--primary); border-color: var(--primary); }
.btn-primary:hover { background-color: #4a1419; border-color: #4a1419; }
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">NOVEDADES<span style="color:#e6b800;">ECONÓMICA</span></a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <!-- CORRECCIÓN 1: Cambié carrito.php por carrito/ -->
            <a href="carrito/" class="btn btn-outline-light">
                <i class="bi bi-cart"></i> Carrito
            </a>
            <span class="text-white"><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
            <a href="logout.php" class="btn btn-light btn-sm">Cerrar Sesión</a>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-bag-check"></i> Mis Compras</h2>
        <a href="index.php" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Seguir Comprando
        </a>
    </div>
    
    <?php if(isset($_GET['exito'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> ¡Pedido #<?php echo $_GET['id']; ?> reservado con éxito!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if($pedidos->num_rows == 0): ?>
        <div class="text-center py-5">
            <i class="bi bi-cart-x" style="font-size: 4rem; color: #ccc;"></i>
            <h4 class="mt-3 text-muted">No has realizado compras aún</h4>
            <a href="index.php" class="btn btn-primary mt-3">Ir a comprar</a>
        </div>
    <?php else: ?>
        <?php while($pedido = $pedidos->fetch_assoc()): 
            // CORRECCIÓN 2: Usar prepared statement para detalle
            $stmt_detalle = $conn->prepare("SELECT dp.*, p.nombre, p.img_principal 
                                           FROM detalle_pedidos dp 
                                           JOIN productos p ON dp.id_producto = p.id 
                                           WHERE dp.id_pedido = ?");
            $stmt_detalle->bind_param("i", $pedido['id']);
            $stmt_detalle->execute();
            $detalle = $stmt_detalle->get_result();
        ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <div>
                    <strong>Pedido #<?php echo $pedido['id']; ?></strong>
                    <small class="text-muted ms-3"><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></small>
                </div>
                <span class="badge bg-<?php 
                    echo $pedido['estado']=='pendiente'?'warning text-dark':
                        ($pedido['estado']=='pagado'?'info':
                        ($pedido['estado']=='enviado'?'primary':
                        ($pedido['estado']=='entregado'?'success':
                        ($pedido['estado']=='cancelado'?'danger':'secondary')))); 
                ?>">
                    <?php echo ucfirst($pedido['estado']); ?>
                </span>
            </div>
            <div class="card-body">
                <?php while($prod = $detalle->fetch_assoc()): ?>
                <div class="row align-items-center mb-3 pb-3 border-bottom">
                    <div class="col-md-2">
                        <img src="<?php echo $prod['img_principal']; ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($prod['nombre']); ?>">
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-1"><?php echo htmlspecialchars($prod['nombre']); ?></h6>
                        <small class="text-muted">Cantidad: <?php echo $prod['cantidad']; ?></small>
                    </div>
                    <div class="col-md-2 text-center">
                        <span>$<?php echo number_format($prod['precio_unitario'], 2); ?></span>
                    </div>
                    <div class="col-md-2 text-end">
                        <strong>$<?php echo number_format($prod['subtotal'], 2); ?></strong>
                    </div>
                </div>
                <?php endwhile; ?>
                
                <div class="row mt-3">
                    <div class="col-md-8">
                        <p class="mb-1"><strong>Dirección:</strong> <?php echo htmlspecialchars($pedido['direccion']); ?></p>
                        <p class="mb-1"><strong>Teléfono:</strong> <?php echo htmlspecialchars($pedido['telefono_contacto']); ?></p>
                        <p class="mb-0"><strong>Método de pago:</strong> <?php echo htmlspecialchars($pedido['metodo_pago']); ?></p>
                    </div>
                    <div class="col-md-4 text-end">
                        <h4>Total: $<?php echo number_format($pedido['total'], 2); ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>