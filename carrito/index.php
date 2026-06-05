<?php
session_start();
require_once "../includes/db.php";

$carrito = $_SESSION['carrito'] ?? [];
$total = 0;

// Procesar reservación
if(isset($_POST['reservar']) && !empty($carrito) && isset($_SESSION['id_usuario'])){
    $id_usuario = $_SESSION['id_usuario'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $metodo_pago = $_POST['metodo_pago'];
    
    foreach($carrito as $item){
        $total += $item['precio'] * $item['cantidad'];
    }
    
    $stmt = $conn->prepare("INSERT INTO pedidos (id_usuario, total, estado, direccion, telefono_contacto, metodo_pago) VALUES (?, ?, 'pendiente', ?, ?, ?)");
    $stmt->bind_param("idsss", $id_usuario, $total, $direccion, $telefono, $metodo_pago);
    $stmt->execute();
    $id_pedido = $conn->insert_id;
    
    foreach($carrito as $item){
        $subtotal = $item['precio'] * $item['cantidad'];
        $stmt = $conn->prepare("INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isidd", $id_pedido, $item['id'], $item['cantidad'], $item['precio'], $subtotal);
        $stmt->execute();
    }
    
    unset($_SESSION['carrito']);
    header("Location: ../mis_compras.php?exito=1&id=" . $id_pedido);
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Carrito - Novedades Económica</title>
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

<nav class="navbar navbar-dark navbar-custom py-3">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../index.php">NOVEDADES<span style="color:#e6b800;">ECONÓMICA</span></a>
        <!-- QUITÉ EL BOTÓN "SEGUIR COMPRANDO" DE AQUÍ PARA QUE NO ESTÉ DUPLICADO -->
    </div>
</nav>

<div class="container my-5">
    <h2 class="mb-4"><i class="bi bi-cart"></i> Mi Carrito</h2>
    
    <?php if(empty($carrito)): ?>
        <div class="text-center py-5">
            <i class="bi bi-cart-x" style="font-size: 5rem; color: #ddd;"></i>
            <h4 class="mt-3 text-muted">Tu carrito está vacío</h4>
            <a href="../index.php" class="btn btn-primary btn-lg mt-3">Ir a comprar</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <?php foreach($carrito as $key => $item): 
                    $subtotal = $item['precio'] * $item['cantidad'];
                    $total += $subtotal;
                ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <img src="<?php echo $item['img']; ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($item['nombre']); ?>">
                            </div>
                            <div class="col-md-4">
                                <h6><?php echo htmlspecialchars($item['nombre']); ?></h6>
                                <small class="text-muted">Talla: <?php echo htmlspecialchars($item['talla']); ?></small><br>
                                <small class="text-muted">$<?php echo number_format($item['precio'], 2); ?> c/u</small>
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="badge bg-secondary"><?php echo $item['cantidad']; ?> pz</span>
                            </div>
                            <div class="col-md-2 text-center">
                                <strong>$<?php echo number_format($subtotal, 2); ?></strong>
                            </div>
                            <div class="col-md-2 text-end">
                                <a href="eliminar.php?key=<?php echo $key; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar producto?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <div class="d-flex justify-content-between">
                    <a href="vaciar.php" class="btn btn-outline-danger" onclick="return confirm('¿Vaciar todo el carrito?')">
                        <i class="bi bi-trash"></i> Vaciar Carrito
                    </a>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Resumen de Compra</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Subtotal:</span>
                            <strong>$<?php echo number_format($total, 2); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Envío:</span>
                            <strong class="text-success">GRATIS</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <h5>Total:</h5>
                            <h4 class="text-primary">$<?php echo number_format($total, 2); ?></h4>
                        </div>
                        
                        <?php if(isset($_SESSION['id_usuario'])): ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Dirección de envío *</label>
                                <textarea name="direccion" class="form-control" rows="2" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Teléfono *</label>
                                <input type="tel" name="telefono" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Método de pago *</label>
                                <select name="metodo_pago" class="form-select" required>
                                    <option value="">Selecciona...</option>
                                    <option value="Efectivo">Efectivo contra entrega</option>
                                    <option value="Transferencia">Transferencia</option>
                                    <option value="Tarjeta">Tarjeta en tienda</option>
                                </select>
                            </div>
                            <button type="submit" name="reservar" class="btn btn-success w-100 btn-lg">
                                <i class="bi bi-check-circle"></i> Reservar Pedido
                            </button>
                        </form>
                        <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> 
                            <a href="../login.php" class="alert-link">Inicia sesión</a> para reservar
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>