<?php
session_start();
if(isset($_GET['key']) && isset($_SESSION['carrito'][$_GET['key']])){
    unset($_SESSION['carrito'][$_GET['key']]);
}
header("Location: index.php");
exit();
?>