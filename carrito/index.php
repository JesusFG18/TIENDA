<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
require_once "../includes/db.php";

$id_sesion = session_id();

// Procesar reservación
if (isset($_POST['reservar']) && isset($_SESSION['id_usuario'])) {
    $id_usuario = $_SESSION['id_usuario'];
    $error = null;

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("SELECT c.*, p.nombre, p.precio, p.precio_oferta 
                                FROM carrito c 
                                JOIN productos p ON c.id_producto = p.id 
                                WHERE c.id_sesion = ?");
        $stmt->bind_param("s", $id_sesion);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (empty($items)) {
            throw new Exception('Carrito vacío');
        }

        $total_pedido = 0;
        foreach ($items as $item) {
            $precio = $item['precio_oferta'] ?? $item['precio'];
            $total_pedido += $precio * $item['cantidad'];
        }

        // Crear pedido
        $stmt = $conn->prepare("INSERT INTO pedidos (id_usuario, total, estado, fecha_pedido) VALUES (?, ?, 'pendiente', NOW())");
        $stmt->bind_param("id", $id_usuario, $total_pedido);
        $stmt->execute();
        $id_pedido = $conn->insert_id;

        foreach ($items as $item) {
            $precio = $item['precio_oferta'] ?? $item['precio'];
            $stmt_detalle = $conn->prepare("INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
            $stmt_detalle->bind_param("iisd", $id_pedido, $item['id_producto'], $item['cantidad'], $precio);
            $stmt_detalle->execute();
            $stmt_detalle->close();
        }

        $stmt = $conn->prepare("DELETE FROM carrito WHERE id_sesion = ?");
        $stmt->bind_param("s", $id_sesion);
        $stmt->execute();
        unset($_SESSION['carrito']);

        $conn->commit();
        header("Location: ../mis_compras.php?exito=1&id=" . $id_pedido);
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}

// Cargar carrito
$stmt = $conn->prepare("SELECT c.*, p.nombre, p.precio, p.precio_oferta, p.img_principal 
                        FROM carrito c 
                        JOIN productos p ON c.id_producto = p.id 
                        WHERE c.id_sesion = ?");
$stmt->bind_param("s", $id_sesion);
$stmt->execute();
$carrito_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total_carrito = 0;
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
    </div>
</nav>

<div class="container my-5">
    <h2 class="mb-4"><i class="bi bi-cart"></i> Mi Carrito</h2>
    
    <?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        Error: <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if (empty($carrito_items)): ?>
        <div class="text-center py-5">
            <i class="bi bi-cart-x" style="font-size: 5rem; color: #ddd;"></i>
            <h4 class="mt-3 text-muted">Tu carrito está vacío</h4>
            <a href="../index.php" class="btn btn-primary btn-lg mt-3">Ir a comprar</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <?php foreach ($carrito_items as $item): 
                    $precio = $item['precio_oferta'] ?? $item['precio'];
                    $subtotal = $precio * $item['cantidad'];
                    $total_carrito += $subtotal;
                ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <img src="../<?php echo $item['img_principal']; ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($item['nombre']); ?>" onerror="this.src='../img/no-image.png'">
                            </div>
                            <div class="col-md-6">
                                <h6><?php echo htmlspecialchars($item['nombre']); ?></h6>
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="badge bg-secondary"><?php echo $item['cantidad']; ?> pz</span>
                            </div>
                            <div class="col-md-2 text-center">
                                <strong>$<?php echo number_format($subtotal, 2); ?></strong>
                            </div>
                            <div class="col-md-2 text-end">
                                <a href="eliminar.php?id=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar producto?')">
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
                            <strong>$<?php echo number_format($total_carrito, 2); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Envío:</span>
                            <strong class="text-success">GRATIS</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <h5>Total:</h5>
                            <h4 style="color: #5e1920;">$<?php echo number_format($total_carrito, 2); ?></h4>
                        </div>
                        
                        <?php if (isset($_SESSION['id_usuario'])): ?>
                        <form method="POST">
                            <button type="submit" name="reservar" class="btn w-100 btn-lg text-white fw-bold" style="background-color: #5e1920;">
                                <i class="bi bi-bookmark-check"></i> Reservar Pedido
                            </button>
                        </form>
                        <small class="text-muted d-block mt-2 text-center">El stock ya fue reservado al agregar al carrito</small>
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