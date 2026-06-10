<?php
// Configurar sesión correctamente
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
require_once "../includes/db.php";

$id_carrito = intval($_GET['id'] ?? 0);
$id_sesion = session_id();

if ($id_carrito) {
    $conn->begin_transaction();
    try {
        // Obtener id_producto y cantidad (sin talla)
        $stmt = $conn->prepare("SELECT id_producto, cantidad FROM carrito WHERE id = ? AND id_sesion = ?");
        $stmt->bind_param("is", $id_carrito, $id_sesion);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $id_producto = $row['id_producto'];
            $cantidad = $row['cantidad'];

            // Devolver stock
            $upd = $conn->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
            $upd->bind_param("is", $cantidad, $id_producto);
            $upd->execute();
            $upd->close();

            // Eliminar del carrito
            $del = $conn->prepare("DELETE FROM carrito WHERE id = ? AND id_sesion = ?");
            $del->bind_param("is", $id_carrito, $id_sesion);
            $del->execute();
            $del->close();
        }
        $stmt->close();

        // *** RECARGAR la sesión del carrito desde la base de datos ***
        $stmt = $conn->prepare("SELECT id_producto, cantidad, talla FROM carrito WHERE id_sesion = ?");
        $stmt->bind_param("s", $id_sesion);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $_SESSION['carrito'] = [];
        foreach ($items as $item) {
            $key = $item['id_producto']; // sin talla
            $_SESSION['carrito'][$key] = [
                'id' => $item['id_producto'],
                'cantidad' => $item['cantidad']
                // Puedes agregar más datos si los necesitas
            ];
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        // Opcional: registrar error
    }
}

header("Location: ../index.php");
exit();
?>