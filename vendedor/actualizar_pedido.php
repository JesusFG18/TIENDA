<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/auth.php";
verificarRol('vendedor'); // o 'admin' según corresponda

header('Content-Type: application/json');

$id = intval($_POST['id'] ?? 0);
$estado = $_POST['estado'] ?? '';

$estados_validos = ['pendiente', 'apartado', 'entregado', 'cancelado', 'pagado'];

if (!in_array($estado, $estados_validos)) {
    echo json_encode(['success' => false, 'error' => 'Estado inválido']);
    exit;
}

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID de pedido no proporcionado']);
    exit;
}

// Obtener estado actual del pedido
$check = $conn->prepare("SELECT estado FROM pedidos WHERE id = ?");
$check->bind_param("i", $id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'error' => "Pedido #$id no existe"]);
    exit;
}

$estado_actual = $result->fetch_assoc()['estado'];
$check->close();

// Si ya está cancelado, no hacer nada
if ($estado_actual === 'cancelado') {
    echo json_encode(['success' => false, 'error' => 'El pedido ya está cancelado', 'estado_actual' => $estado_actual]);
    exit;
}

$conn->begin_transaction();

try {
    // Si el nuevo estado es 'cancelado', revertir stock
    if ($estado === 'cancelado') {
        // Obtener los detalles del pedido
        $stmt = $conn->prepare("SELECT id_producto, cantidad FROM detalle_pedidos WHERE id_pedido = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $detalles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (empty($detalles)) {
            throw new Exception("No hay productos registrados en detalle_pedidos para el pedido #$id. No se puede revertir stock.");
        }

        // Revertir stock de cada producto
        foreach ($detalles as $detalle) {
            // Determinar tipo de dato de id_producto (puede ser INT o VARCHAR)
            // Si es numérico, bind "i", si es string, bind "s"
            $tipo_id = is_numeric($detalle['id_producto']) ? "i" : "s";
            $upd = $conn->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
            $upd->bind_param("i" . $tipo_id, $detalle['cantidad'], $detalle['id_producto']);
            $upd->execute();
            if ($upd->affected_rows === 0) {
                throw new Exception("Producto ID {$detalle['id_producto']} no encontrado en tabla productos");
            }
            $upd->close();
        }
    }

    // Actualizar el estado del pedido
    $stmt = $conn->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $estado, $id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'affected' => $affected,
        'id' => $id,
        'estado_anterior' => $estado_actual,
        'estado_nuevo' => $estado,
        'stock_revertido' => ($estado === 'cancelado')
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>