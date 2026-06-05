<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function verificarRol($rolRequerido){
    if(!isset($_SESSION['rol'])){
        header("Location: ../login.php");
        exit();
    }
    if($_SESSION['rol'] !== $rolRequerido){
        die("Acceso denegado. Tu rol: " . $_SESSION['rol']);
    }
}

function usuarioLogueado(){
    return isset($_SESSION['id_usuario']);
}
?>