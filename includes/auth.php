<?php

function verificarRol($rolRequerido){

    if(!isset($_SESSION['rol'])){

        header("Location: ../login.php");
        exit();

    }

    if($_SESSION['rol'] !== $rolRequerido){

        die("Acceso denegado");

    }

}

?>