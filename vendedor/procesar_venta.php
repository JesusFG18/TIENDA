<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/auth.php";

verificarRol('vendedor');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$id_cliente = !empty($_POST['id_cliente']) ? intval($_POST['id_cliente']) : null;
$productos = json_decode($_POST['productos'] ?? '', true);
$tipo_pago = $_POST['tipo_pago'] ?? '';

if (empty($productos) || !is_array($productos)) {
    echo json_encode(['success' => false, 'error' => 'No se recibieron productos']);
    exit;
}

if (!in_array($tipo_pago, ['pagado', 'apartado'])) {
    echo json_encode(['success' => false, 'error' => 'Tipo de pago inválido']);
    exit;
}

$total = 0;
foreach ($productos as $p) {
    $total += $p['precio'] * $p['cantidad'];
}

$conn->begin_transaction();

try {
    // Insertar pedido
    $stmt = $conn->prepare("INSERT INTO pedidos (id_usuario, total, estado) VALUES (?, ?, ?)");
    $stmt->bind_param("ids", $id_cliente, $total, $tipo_pago);
    $stmt->execute();
    $id_pedido = $conn->insert_id;
    $stmt->close();

    // Insertar detalles y actualizar stock
    foreach ($productos as $p) {
        $id_producto = $p['id'];
        $cantidad = intval($p['cantidad']);
        $precio = floatval($p['precio']);

        // Verificar stock suficiente (opcional pero recomendado)
        $check = $conn->prepare("SELECT stock FROM productos WHERE id = ? FOR UPDATE");
        $check->bind_param("i", $id_producto);
        $check->execute();
        $stock_actual = $check->get_result()->fetch_assoc()['stock'] ?? 0;
        $check->close();

        if ($stock_actual < $cantidad) {
            throw new Exception("Stock insuficiente para producto ID $id_producto. Disponible: $stock_actual");
        }

        // Insertar detalle
        $stmt_det = $conn->prepare("INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
        $stmt_det->bind_param("iiid", $id_pedido, $id_producto, $cantidad, $precio);
        $stmt_det->execute();
        $stmt_det->close();

        // Descontar stock usando prepared statement
        $stmt_upd = $conn->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
        $stmt_upd->bind_param("ii", $cantidad, $id_producto);
        $stmt_upd->execute();
        $stmt_upd->close();
    }

    $conn->commit();
    echo json_encode(['success' => true, 'id_pedido' => $id_pedido]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>