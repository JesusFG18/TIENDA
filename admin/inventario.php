<?php

require_once "../includes/auth.php";
require_once "../includes/datos.php";

verificarRol('admin');

// PANEL ACTUAL
$panel = isset($_GET['panel']) ? $_GET['panel'] : 'inventario';

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Panel Administrador</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>

        body{
            background-color: #f4f4f4;
        }

        .sidebar{
            width: 250px;
            height: 100vh;
            background-color: #5e1920;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 20px;
        }

        .sidebar h3{
            color: white;
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar a{
            display: block;
            color: white;
            text-decoration: none;
            padding: 15px 20px;
            transition: 0.3s;
        }

        .sidebar a:hover{
            background-color: rgba(255,255,255,0.1);
        }

        .main-content{
            margin-left: 250px;
            padding: 30px;
        }

        .card-panel{
            border: none;
            border-radius: 15px;
            transition: 0.3s;
        }

        .card-panel:hover{
            transform: translateY(-5px);
        }

        .tabla{
            border-radius: 15px;
            overflow: hidden;
        }

        .topbar{
            background-color: white;
            border-radius: 15px;
            padding: 20px;
        }

    </style>

</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <h3>

        ADMIN

    </h3>

    <a href="?panel=inventario">

        <i class="bi bi-box-seam"></i>
        Inventario

    </a>

    <a href="?panel=pedidos">

        <i class="bi bi-cart-check"></i>
        Pedidos

    </a>

    <a href="?panel=reportes">

        <i class="bi bi-graph-up"></i>
        Reportes

    </a>

    <a href="?panel=usuarios">

        <i class="bi bi-people"></i>
        Usuarios

    </a>

    <a href="?panel=categorias">

        <i class="bi bi-tags"></i>
        Secciones/Categorías

    </a>

    <a href="../logout.php">

        <i class="bi bi-box-arrow-right"></i>
        Cerrar Sesión

    </a>

</div>

<!-- CONTENIDO -->
<div class="main-content">

<?php if($panel != 'usuarios' && $panel != 'categorias'): ?>

    <!-- TOPBAR -->
    <div class="topbar shadow-sm mb-4 d-flex justify-content-between align-items-center">

        <div>

            <h2 class="fw-bold mb-0">

                Panel Administrador

            </h2>

            <small class="text-muted">

                Bienvenido Administrador

            </small>

        </div>

        <div>

            <span class="badge bg-success p-2">

                En línea

            </span>

        </div>

    </div>

    <!-- TARJETAS -->
    <div class="row g-4 mb-4">

        <div class="col-md-4">

            <div class="card card-panel shadow-sm p-4">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h5>

                            Productos

                        </h5>

                        <h2 class="fw-bold">

                            <?php echo count($productos); ?>

                        </h2>

                    </div>

                    <i class="bi bi-box-seam fs-1 text-primary"></i>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card card-panel shadow-sm p-4">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h5>

                            Pedidos

                        </h5>

                        <h2 class="fw-bold">

                            0

                        </h2>

                    </div>

                    <i class="bi bi-cart-check fs-1 text-success"></i>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card card-panel shadow-sm p-4">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h5>

                            Usuarios

                        </h5>

                        <h2 class="fw-bold">

                            3

                        </h2>

                    </div>

                    <i class="bi bi-people fs-1 text-danger"></i>

                </div>

            </div>

        </div>

    </div>

<?php endif; ?>

<!-- PANEL INVENTARIO -->
<?php if($panel == 'inventario'): ?>

<div class="card shadow-sm border-0 tabla">

    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">

        <h5 class="mb-0">

            Inventario de Productos

        </h5>

        <button class="btn btn-success btn-sm">

            Agregar Producto

        </button>

    </div>

    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0">

            <thead class="table-light">

                <tr>

                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Acciones</th>

                </tr>

            </thead>

            <tbody>

                <?php foreach($productos as $producto): ?>

                <tr>

                    <td>

                        <img src="<?php echo $producto['img']; ?>"
                             width="60"
                             height="60"
                             style="object-fit: cover; border-radius: 10px;">

                    </td>

                    <td>

                        <?php echo $producto['nombre']; ?>

                    </td>

                    <td>

                        <?php echo ucfirst($producto['sub']); ?>

                    </td>

                    <td>

                        $<?php echo $producto['precio']; ?>

                    </td>

                    <td>

                        <?php echo $producto['stock']; ?>

                    </td>

                    <td>

                        <button class="btn btn-primary btn-sm">

                            Editar

                        </button>

                        <button class="btn btn-danger btn-sm">

                            Eliminar

                        </button>

                    </td>

                </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>

<?php endif; ?>

<!-- PANEL USUARIOS -->
<?php if($panel == 'usuarios'): ?>

<div class="card shadow-sm border-0">

    <div class="card-header bg-dark text-white">

        <h5 class="mb-0">

            Gestión de Usuarios

        </h5>

    </div>

    <div class="card-body">

        <form class="row g-3 mb-4">

            <div class="col-md-4">

                <label class="form-label">

                    Tipo Usuario

                </label>

                <select class="form-select"
                        id="tipoUsuario">

                    <option value="cliente">

                        Cliente

                    </option>

                    <option value="vendedor">

                        Vendedor

                    </option>

                    <option value="admin">

                        Admin

                    </option>

                </select>

            </div>

            <div class="col-md-4">

                <label class="form-label">

                    Nombre / Usuario

                </label>

                <input type="text"
                       class="form-control"
                       id="usuario">

            </div>

            <div class="col-md-4"
                 id="campoTelefono">

                <label class="form-label">

                    Número Telefónico

                </label>

                <input type="text"
                       class="form-control">

            </div>

            <div class="col-md-6">

                <label class="form-label">

                    Contraseña

                </label>

                <input type="text"
                       class="form-control"
                       id="password"
                       readonly>

            </div>

            <div class="col-12">

                <button type="submit"
                        class="btn btn-success">

                    Registrar Usuario

                </button>

            </div>

        </form>

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead class="table-light">

                    <tr>

                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Teléfono</th>
                        <th>Contraseña</th>
                        <th>Acciones</th>

                    </tr>

                </thead>

                <tbody>

                    <tr>

                        <td>

                            admin1

                        </td>

                        <td>

                            Admin

                        </td>

                        <td>

                            -

                        </td>

                        <td>

                            A8f2Kp1

                        </td>

                        <td>

                            <button class="btn btn-danger btn-sm">

                                Eliminar

                            </button>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php endif; ?>

<!-- PANEL CATEGORIAS -->
<?php if($panel == 'categorias'): ?>

<div class="card shadow-sm border-0">

    <div class="card-header bg-dark text-white">

        <h5 class="mb-0">

            Gestión de Secciones y Categorías

        </h5>

    </div>

    <div class="card-body">

        <!-- FORMULARIO -->
        <form class="row g-3 mb-4">

            <!-- NUEVA SECCION -->
            <div class="col-md-5">

                <label class="form-label">

                    Nueva Sección

                </label>

                <input type="text"
                       class="form-control"
                       placeholder="Ejemplo: Deportes">

            </div>

            <!-- NUEVA CATEGORIA -->
            <div class="col-md-5">

                <label class="form-label">

                    Nueva Categoría

                </label>

                <input type="text"
                       class="form-control"
                       placeholder="Ejemplo: Jerseys">

            </div>

            <!-- BOTON -->
            <div class="col-md-2 d-flex align-items-end">

                <button class="btn btn-success w-100">

                    Agregar

                </button>

            </div>

        </form>

        <!-- TABLA -->
        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead class="table-light">

                    <tr>

                        <th>Sección</th>
                        <th>Categoría</th>
                        <th>Acciones</th>

                    </tr>

                </thead>

                <tbody>

                    <tr>

                        <td>Deportes</td>
                        <td>Jerseys</td>

                        <td>

                            <button class="btn btn-danger btn-sm">

                                Eliminar

                            </button>

                        </td>

                    </tr>

                    <tr>

                        <td>Escolar</td>
                        <td>Mochilas</td>

                        <td>

                            <button class="btn btn-danger btn-sm">

                                Eliminar

                            </button>

                        </td>

                    </tr>

                    <tr>

                        <td>Tecnología</td>
                        <td>Audífonos</td>

                        <td>

                            <button class="btn btn-danger btn-sm">

                                Eliminar

                            </button>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>


<?php endif; ?>

</div>

<script>

const tipoUsuario = document.getElementById('tipoUsuario');

const campoTelefono = document.getElementById('campoTelefono');

const usuario = document.getElementById('usuario');

const password = document.getElementById('password');

if(tipoUsuario){

    tipoUsuario.addEventListener('change', function(){

        if(this.value == 'cliente'){

            campoTelefono.style.display = 'block';

            password.removeAttribute('readonly');

            password.value = '';

        }else{

            campoTelefono.style.display = 'none';

            generarPassword();

        }

    });

}

if(usuario){

    usuario.addEventListener('input', function(){

        if(tipoUsuario.value != 'cliente'){

            generarPassword();

        }

    });

}

function generarPassword(){

    let chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    let pass = "";

    for(let i = 0; i < 8; i++){

        pass += chars.charAt(Math.floor(Math.random() * chars.length));

    }

    password.value = pass;

}

</script>

</body>

</html>