<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
require_once "../includes/db.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$id_producto = trim($_POST['id_producto'] ?? '');
$cantidad = max(1, intval($_POST['cantidad'] ?? 1));

if (empty($id_producto)) {
    echo json_encode(['success' => false, 'error' => 'ID de producto vacío']);
    exit;
}

$id_sesion = session_id();
$id_usuario = $_SESSION['id_usuario'] ?? null;
$expira = date('Y-m-d H:i:s', strtotime('+30 minutes'));

$conn->begin_transaction();
try {
    // Bloquear producto
    $stmt = $conn->prepare("SELECT id, nombre, precio, precio_oferta, img_principal, stock 
                            FROM productos 
                            WHERE id = ? AND activo = 1 FOR UPDATE");
    $stmt->bind_param("s", $id_producto);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception('Producto no encontrado');
    }
    $producto = $result->fetch_assoc();
    $precio_final = $producto['precio_oferta'] ?? $producto['precio'];

    if ($producto['stock'] < $cantidad) {
        throw new Exception('Stock insuficiente. Disponible: ' . $producto['stock']);
    }

    // Insertar en carrito SIN talla
    if ($id_usuario) {
        $stmt = $conn->prepare("INSERT INTO carrito (id_sesion, id_usuario, id_producto, cantidad, fecha_expira)
                                VALUES (?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE cantidad = cantidad + VALUES(cantidad), fecha_expira = VALUES(fecha_expira)");
        $stmt->bind_param("sisss", $id_sesion, $id_usuario, $id_producto, $cantidad, $expira);
    } else {
        $stmt = $conn->prepare("INSERT INTO carrito (id_sesion, id_producto, cantidad, fecha_expira)
                                VALUES (?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE cantidad = cantidad + VALUES(cantidad), fecha_expira = VALUES(fecha_expira)");
        $stmt->bind_param("ssss", $id_sesion, $id_producto, $cantidad, $expira);
    }
    $stmt->execute();

    // Descontar stock
    $stmt = $conn->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
    $stmt->bind_param("is", $cantidad, $id_producto);
    $stmt->execute();

    // Actualizar sesión
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }
    $key = $id_producto; // Sin talla
    if (isset($_SESSION['carrito'][$key])) {
        $_SESSION['carrito'][$key]['cantidad'] += $cantidad;
    } else {
        $_SESSION['carrito'][$key] = [
            'id' => $id_producto,
            'nombre' => $producto['nombre'],
            'precio' => $precio_final,
            'img' => $producto['img_principal'],
            'cantidad' => $cantidad
        ];
    }

    // Total items
    $stmt = $conn->prepare("SELECT SUM(cantidad) as total FROM carrito WHERE id_sesion = ?");
    $stmt->bind_param("s", $id_sesion);
    $stmt->execute();
    $total_items = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

    $conn->commit();
    echo json_encode(['success' => true, 'total' => (int)$total_items, 'msg' => $producto['nombre'] . ' agregado al carrito']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>json_encode(['success' => false, 'error' => 'Método no permitido']);
?>