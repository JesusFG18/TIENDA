<?php
session_start();
require_once "includes/db.php";
require_once "includes/auth.php";

if(!isset($_SESSION['carrito_id'])) {
    header("Location: carrito/");
    exit;
}

$id_sesion = $_SESSION['carrito_id'];
$id_usuario = $_SESSION['user_id'] ?? NULL;

// Verificar que hay items en el carrito
$items = $conn->query("SELECT c.*, p.nombre, p.precio, p.precio_oferta 
                       FROM carrito c 
                       JOIN productos p ON c.id_producto = p.id 
                       WHERE c.id_sesion = '$id_sesion'")->fetch_all(MYSQLI_ASSOC);

if(empty($items)) {
    header("Location: carrito/?error=vacío");
    exit;
}

// Calcular total
$total = 0;
foreach($items as $item) {
    $precio = $item['precio_oferta'] ?? $item['precio'];
    $total += $precio * $item['cantidad'];
}

$conn->begin_transaction();
try {
    // 1. Crear pedido con estado 'pendiente' - NO pagado
    $stmt = $conn->prepare("INSERT INTO pedidos (id_usuario, total, estado, fecha_pedido) VALUES (?, ?, 'pendiente', NOW())");
    $stmt->bind_param("id", $id_usuario, $total);
    $stmt->execute();
    $id_pedido = $conn->insert_id;
    
    // 2. Por cada item: descontar stock real y liberar reserva
    foreach($items as $item) {
        $precio = $item['precio_oferta'] ?? $item['precio'];
        
        $stmt = $conn->prepare("UPDATE productos SET stock = stock - ?, stock_reservado = stock_reservado - ? WHERE id = ?");
        $stmt->bind_param("iis", $item['cantidad'], $item['cantidad'], $item['id_producto']);
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario, talla) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isids", $id_pedido, $item['id_producto'], $item['cantidad'], $precio, $item['talla']);
        $stmt->execute();
    }
    
    // 3. Vaciar carrito de BD y SESSION
    $conn->query("DELETE FROM carrito WHERE id_sesion = '$id_sesion'");
    unset($_SESSION['carrito']);
    
    $conn->commit();
    
    // Redirigir a página de éxito
    header("Location: pedido_reservado.php?pedido=" . $id_pedido);
    exit;
    
} catch (Exception $e) {
    $conn->rollback();
    header("Location: carrito/?error=" . urlencode($e->getMessage()));
    exit;
}
?>