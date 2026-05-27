<?php

session_start();

if(!isset($_SESSION['rol'])){

    header("Location: login.php");
    exit();

}

switch($_SESSION['rol']){

    case 'admin':

        header("Location: admin/inventario.php");
        exit();

    break;

    case 'vendedor':

        header("Location: vendedor/punto_venta.php");
        exit();

    break;

    default:

        header("Location: index.php");
        exit();

}

?>