<?php
// Configurar sesión correctamente
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
require_once "../includes/db.php";

$id_sesion = session_id();

$conn->begin_transaction();
try {
    // Obtener todos los productos del carrito con sus cantidades
    $stmt = $conn->prepare("SELECT id_producto, cantidad FROM carrito WHERE id_sesion = ?");
    $stmt->bind_param("s", $id_sesion);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Devolver el stock de cada producto
    foreach ($items as $item) {
        $upd = $conn->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
        $upd->bind_param("is", $item['cantidad'], $item['id_producto']);
        $upd->execute();
        $upd->close();
    }

    // Eliminar todos los registros del carrito
    $del = $conn->prepare("DELETE FROM carrito WHERE id_sesion = ?");
    $del->bind_param("s", $id_sesion);
    $del->execute();
    $del->close();

    // Limpiar la sesión del carrito
    unset($_SESSION['carrito']);

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    // Opcional: registrar error
}

// Redirigir a la página principal (fuera de la carpeta carrito)
header("Location: ../index.php");
exit();
?>