<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/datos.php"; // ← Importante: carga tu array

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $id_producto = $_POST['id_producto'];
    $cantidad = (int)$_POST['cantidad'];
    $talla = $_POST['talla'] ?? 'U';
    
    // Buscar en tu array $productos
    $producto = null;
    foreach($productos as $p){
        if($p['id'] == $id_producto){
            $producto = $p;
            break;
        }
    }
    
    if($producto){
        if(!isset($_SESSION['carrito'])){
            $_SESSION['carrito'] = [];
        }
        
        $key = $id_producto . '_' . $talla;
        
        if(isset($_SESSION['carrito'][$key])){
            $_SESSION['carrito'][$key]['cantidad'] += $cantidad;
        } else {
            $_SESSION['carrito'][$key] = [
                'id' => $id_producto,
                'nombre' => $producto['nombre'],
                'precio' => $producto['precio'],
                'img' => $producto['img'],
                'cantidad' => $cantidad,
                'talla' => $talla
            ];
        }
        
        $total_items = 0;
        foreach($_SESSION['carrito'] as $item){
            $total_items += $item['cantidad'];
        }
        
        echo json_encode(['success' => true, 'total' => $total_items]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
    }
    exit;
}
echo json_encode(['success' => false, 'error' => 'Método no permitido']);
?>