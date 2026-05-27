<?php

session_start();

if(isset($_POST['entrar'])){

    $_SESSION['rol'] = $_POST['rol'];

    header("Location: dashboard.php");
    exit();

}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-dark">

<div class="container mt-5">

    <div class="card p-4 mx-auto shadow"
         style="max-width: 400px;">

        <h3 class="text-center mb-4">
            Iniciar Sesión
        </h3>

        <form method="POST">

            <label class="form-label">
                Selecciona un Rol
            </label>

            <select name="rol"
                    class="form-select mb-3">

                <option value="admin">
                    Administrador
                </option>

                <option value="vendedor">
                    Vendedor
                </option>

                <option value="cliente">
                    Cliente
                </option>

            </select>

            <button type="submit"
                    name="entrar"
                    class="btn btn-primary w-100">

                Entrar

            </button>

        </form>

    </div>

</div>

</body>
</html>