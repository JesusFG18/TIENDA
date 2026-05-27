<?php

require_once "../includes/auth.php";
require_once "../includes/datos.php";

verificarRol('vendedor');

// PANEL ACTUAL
$panel = isset($_GET['panel']) ? $_GET['panel'] : 'ventas';

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Panel Vendedor</title>

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

        .topbar{
            background-color: white;
            border-radius: 15px;
            padding: 20px;
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

    </style>

</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <h3>

        VENDEDOR

    </h3>

    <a href="?panel=ventas">

        <i class="bi bi-cart-plus"></i>
        Ventas

    </a>

    <a href="?panel=pedidos">

        <i class="bi bi-bag-check"></i>
        Pedidos

    </a>

    <a href="?panel=apartados">

        <i class="bi bi-cash-coin"></i>
        Apartados

    </a>

    <a href="?panel=clientes">

        <i class="bi bi-people"></i>
        Clientes

    </a>

    <a href="../logout.php">

        <i class="bi bi-box-arrow-right"></i>
        Cerrar Sesión

    </a>

</div>

<!-- CONTENIDO -->
<div class="main-content">

<?php if($panel != 'apartados'): ?>

    <!-- TOPBAR -->
    <div class="topbar shadow-sm mb-4 d-flex justify-content-between align-items-center">

        <div>

            <h2 class="fw-bold mb-0">

                Panel Vendedor

            </h2>

            <small class="text-muted">

                Bienvenido Vendedor

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

                            5

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

                            Clientes

                        </h5>

                        <h2 class="fw-bold">

                            12

                        </h2>

                    </div>

                    <i class="bi bi-people fs-1 text-danger"></i>

                </div>

            </div>

        </div>

    </div>

<?php endif; ?>

<!-- PANEL VENTAS -->
<?php if($panel == 'ventas'): ?>

<div class="card shadow-sm border-0 tabla mb-4">

    <div class="card-header bg-dark text-white">

        <h5 class="mb-0">

            Punto de Venta

        </h5>

    </div>

    <div class="card-body">

        <div class="row g-3">

            <div class="col-md-3">

                <label class="form-label">

                    Cliente

                </label>

                <input type="text"
                       class="form-control"
                       placeholder="Nombre del cliente">

            </div>

            <div class="col-md-3">

                <label class="form-label">

                    Producto

                </label>

                <select class="form-select">

                    <?php foreach($productos as $producto): ?>

                    <option>

                        <?php echo $producto['nombre']; ?>

                    </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="col-md-2">

                <label class="form-label">

                    Cantidad

                </label>

                <input type="number"
                       class="form-control"
                       value="1"
                       min="1">

            </div>

            <div class="col-md-2">

                <label class="form-label">

                    Tipo Pago

                </label>

                <select class="form-select">

                    <option>

                        Pagado

                    </option>

                    <option>

                        Apartado

                    </option>

                </select>

            </div>

            <div class="col-md-2 d-flex align-items-end">

                <button class="btn btn-success w-100">

                    Agregar

                </button>

            </div>

        </div>

    </div>

</div>

<!-- TABLA PRODUCTOS -->
<div class="card shadow-sm border-0 tabla">

    <div class="card-header bg-dark text-white">

        <h5 class="mb-0">

            Productos Agregados

        </h5>

    </div>

    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0">

            <thead class="table-light">

                <tr>

                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Total</th>
                    <th>Pago Inicial</th>
                    <th>Estado</th>
                    <th>Acciones</th>

                </tr>

            </thead>

            <tbody>

                <tr>

                    <td>Camisa Negra</td>
                    <td>Camisas</td>
                    <td>$250</td>
                    <td>2</td>
                    <td>$500</td>
                    <td>$250</td>

                    <td>

                        <span class="badge bg-warning text-dark">

                            Apartado

                        </span>

                    </td>

                    <td>

                        <button class="btn btn-danger btn-sm">

                            Quitar

                        </button>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

    <div class="card-body border-top text-end">

        <h4 class="fw-bold">

            Total: $500

        </h4>

        <button class="btn btn-success">

            Finalizar Venta

        </button>

    </div>

</div>

<?php endif; ?>

<!-- PANEL PEDIDOS -->
<?php if($panel == 'pedidos'): ?>

<div class="card shadow-sm border-0 tabla">

    <div class="card-header bg-dark text-white">

        <h5 class="mb-0">

            Pedidos Pendientes

        </h5>

    </div>

    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0">

            <thead class="table-light">

                <tr>

                    <th>Cliente</th>
                    <th>Producto</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>

                </tr>

            </thead>

            <tbody>

                <tr>

                    <td>Juan Pérez</td>
                    <td>Vestido Azul</td>

                    <td>

                        <span class="badge bg-warning text-dark">

                            Apartado

                        </span>

                    </td>

                    <td>

                        27/05/2026 10:30 PM

                    </td>

                    <td>

                        <button class="btn btn-success btn-sm">

                            Entregado

                        </button>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>

<?php endif; ?>

<!-- PANEL APARTADOS -->
<?php if($panel == 'apartados'): ?>

<div class="card shadow-sm border-0">

    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">

        <h5 class="mb-0">

            Control de Apartados

        </h5>

        <button class="btn btn-dark btn-sm">

            Nuevo Apartado

        </button>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead class="table-light">

                    <tr>

                        <th>Cliente</th>
                        <th>Producto</th>
                        <th>Total</th>
                        <th>Anticipo</th>
                        <th>Restante</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>

                    </tr>

                </thead>

                <tbody>

                    <tr>

                        <td>Juan Pérez</td>
                        <td>Camisa Nike</td>
                        <td>$800</td>
                        <td>$400</td>
                        <td>$400</td>

                        <td>

                            <span class="badge bg-warning text-dark">

                                Apartado

                            </span>

                        </td>

                        <td>

                            27/05/2026 10:30 PM

                        </td>

                        <td>

                            <button class="btn btn-success btn-sm">

                                Abonar

                            </button>

                            <button class="btn btn-primary btn-sm">

                                Liquidar

                            </button>

                        </td>

                    </tr>

                    <tr>

                        <td>María López</td>
                        <td>Vestido Casual</td>
                        <td>$1200</td>
                        <td>$600</td>
                        <td>$0</td>

                        <td>

                            <span class="badge bg-success">

                                Pagado

                            </span>

                        </td>

                        <td>

                            26/05/2026 05:15 PM

                        </td>

                        <td>

                            <button class="btn btn-secondary btn-sm" disabled>

                                Finalizado

                            </button>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php endif; ?>

<!-- PANEL CLIENTES -->
<?php if($panel == 'clientes'): ?>

<div class="card shadow-sm border-0 tabla">

    <div class="card-header bg-dark text-white">

        <h5 class="mb-0">

            Clientes Registrados

        </h5>

    </div>

    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0">

            <thead class="table-light">

                <tr>

                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Compras</th>

                </tr>

            </thead>

            <tbody>

                <tr>

                    <td>María López</td>
                    <td>6671234567</td>
                    <td>4</td>

                </tr>

                <tr>

                    <td>Carlos Ruiz</td>
                    <td>6679876543</td>
                    <td>2</td>

                </tr>

            </tbody>

        </table>

    </div>

</div>

<?php endif; ?>

</div>

</body>

</html>