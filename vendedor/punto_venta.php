<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../includes/db.php";
require_once "../includes/auth.php";

verificarRol('vendedor');

$panel = isset($_GET['panel'])? $_GET['panel'] : 'ventas';
$ruta_base = '../';
$mensaje = '';

// PROCESAR ALTA DE CLIENTE - Sin email
if(isset($_POST['crear_cliente']) && $panel == 'clientes'){
    $usuario = trim($_POST['usuario']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);

    $check = $conn->prepare("SELECT id FROM usuarios WHERE usuario =?");
    $check->bind_param("s", $usuario);
    $check->execute();

    if($check->get_result()->num_rows > 0){
        $mensaje = '<div class="alert alert-danger alert-dismissible fade show">Usuario ya existe<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    } else {
        $stmt = $conn->prepare("INSERT INTO usuarios (usuario, password, nombre, telefono, rol) VALUES (?,?,?,?, 'cliente')");
        $stmt->bind_param("ssss", $usuario, $password, $nombre, $telefono);

        if($stmt->execute()){
            $mensaje = '<div class="alert alert-success alert-dismissible fade show">Cliente creado correctamente<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        } else {
            $mensaje = '<div class="alert alert-danger">Error: '.$conn->error.'</div>';
        }
        $stmt->close();
    }
    $check->close();
}

// CONTADORES REALES PARA LAS TARJETAS
$total_productos = $conn->query("SELECT COUNT(*) as total FROM productos")->fetch_assoc()['total'];
$total_pedidos = $conn->query("SELECT COUNT(*) as total FROM pedidos WHERE estado = 'pendiente'")->fetch_assoc()['total'];
$total_clientes = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'cliente'")->fetch_assoc()['total'];

// DATOS PARA CADA PANEL
if($panel == 'ventas'){
    $productos = $conn->query("SELECT * FROM productos WHERE stock > 0 ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);
}

if($panel == 'pedidos'){
    $pedidos = $conn->query("SELECT p.*, COALESCE(u.nombre, 'Cliente de mostrador') as cliente, u.telefono
                             FROM pedidos p
                             LEFT JOIN usuarios u ON p.id_usuario = u.id
                             WHERE p.estado = 'pendiente'
                             ORDER BY p.id DESC");
}

if($panel == 'apartados'){
    $apartados = $conn->query("SELECT p.*, COALESCE(u.nombre, 'Cliente de mostrador') as cliente, u.telefono
                               FROM pedidos p
                               LEFT JOIN usuarios u ON p.id_usuario = u.id
                               WHERE p.estado = 'apartado'
                               ORDER BY p.id DESC");
}

if($panel == 'clientes'){
    $clientes = $conn->query("SELECT u.*, COUNT(p.id) as total_compras
                              FROM usuarios u
                              LEFT JOIN pedidos p ON u.id = p.id_usuario AND p.estado = 'pagado'
                              WHERE u.rol = 'cliente'
                              GROUP BY u.id
                              ORDER BY u.fecha_registro DESC");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Vendedor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body{ background-color: #f4f4f4; }
     .sidebar{ width: 250px; height: 100vh; background-color: #5e1920; position: fixed; top: 0; left: 0; padding-top: 20px; z-index: 1000; }
     .sidebar h3{ color: white; text-align: center; margin-bottom: 30px; }
     .sidebar a{ display: block; color: white; text-decoration: none; padding: 15px 20px; transition: 0.3s; }
     .sidebar a:hover{ background-color: rgba(255,255,255,0.1); }
     .sidebar a.active{ background-color: rgba(0,0,0,0.3); }
     .main-content{ margin-left: 250px; padding: 30px; }
     .topbar{ background-color: white; border-radius: 15px; padding: 20px; }
     .card-panel{ border: none; border-radius: 15px; transition: 0.3s; }
     .card-panel:hover{ transform: translateY(-5px); }
     .tabla{ border-radius: 15px; overflow: hidden; }
        @media(max-width:768px){.sidebar{ width: 100%; height: auto; position: relative; }.main-content{ margin-left: 0; } }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h3>VENDEDOR</h3>
    <a href="?panel=ventas" class="<?php echo $panel=='ventas'? 'active' : '';?>">
        <i class="bi bi-cart-plus"></i> Ventas
    </a>
    <a href="?panel=pedidos" class="<?php echo $panel=='pedidos'? 'active' : '';?>">
        <i class="bi bi-bag-check"></i> Pedidos
    </a>
    <a href="?panel=apartados" class="<?php echo $panel=='apartados'? 'active' : '';?>">
        <i class="bi bi-cash-coin"></i> Apartados
    </a>
    <a href="?panel=clientes" class="<?php echo $panel=='clientes'? 'active' : '';?>">
        <i class="bi bi-people"></i> Clientes
    </a>
    <a href="../logout.php">
        <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
    </a>
</div>

<!-- CONTENIDO -->
<div class="main-content">
    <div class="topbar shadow-sm mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-0">Panel Vendedor</h2>
            <small class="text-muted">Bienvenido <?php echo htmlspecialchars($_SESSION['usuario']?? 'Vendedor');?></small>
        </div>
        <div><span class="badge bg-success p-2">En línea</span></div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card card-panel shadow-sm p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Productos</h5>
                        <h2 class="fw-bold"><?php echo $total_productos;?></h2>
                    </div>
                    <i class="bi bi-box-seam fs-1 text-primary"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-panel shadow-sm p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Pedidos</h5>
                        <h2 class="fw-bold"><?php echo $total_pedidos;?></h2>
                    </div>
                    <i class="bi bi-cart-check fs-1 text-success"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-panel shadow-sm p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Clientes</h5>
                        <h2 class="fw-bold"><?php echo $total_clientes;?></h2>
                    </div>
                    <i class="bi bi-people fs-1 text-danger"></i>
                </div>
            </div>
        </div>
    </div>

<?php echo $mensaje;?>

<!-- PANEL VENTAS -->
<?php if($panel == 'ventas'):?>
<div class="card shadow-sm border-0 tabla mb-4">
    <div class="card-header bg-dark text-white"><h5 class="mb-0">Punto de Venta</h5></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Cliente</label>
                <select class="form-select" id="select-cliente">
                    <option value="">Cliente de mostrador</option>
                    <?php
                    $clientes_list = $conn->query("SELECT id, nombre FROM usuarios WHERE rol = 'cliente' ORDER BY nombre");
                    while($c = $clientes_list->fetch_assoc()):?>
                    <option value="<?php echo $c['id'];?>"><?php echo htmlspecialchars($c['nombre']?? '');?></option>
                    <?php endwhile;?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Producto</label>
                <select class="form-select" id="select-producto">
                    <option value="">Selecciona producto</option>
                    <?php foreach($productos as $producto):?>
                    <option value="<?php echo $producto['id'];?>"
                            data-precio="<?php echo $producto['precio'];?>"
                            data-stock="<?php echo $producto['stock'];?>"
                            data-img="<?php echo $ruta_base. ($producto['img_principal']?? $producto['img']?? '');?>"
                            data-nombre="<?php echo htmlspecialchars($producto['nombre']?? '');?>">
                        <?php echo htmlspecialchars($producto['nombre']?? '');?> - $<?php echo $producto['precio'];?> - Stock: <?php echo $producto['stock'];?>
                    </option>
                    <?php endforeach;?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Cantidad</label>
                <input type="number" class="form-control" value="1" min="1" id="input-cantidad">
            </div>
            <div class="col-md-2">
                <label class="form-label">Tipo Pago</label>
                <select class="form-select" id="select-pago">
                    <option value="pagado">Pagado</option>
                    <option value="apartado">Apartado</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-success w-100" id="btn-agregar">
                    <i class="bi bi-plus-circle"></i> Agregar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 tabla">
    <div class="card-header bg-dark text-white"><h5 class="mb-0">Productos Agregados</h5></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tabla-venta">
            <thead class="table-light">
                <tr>
                    <th>Imagen</th><th>Producto</th><th>Precio</th><th>Cantidad</th>
                    <th>Total</th><th>Estado</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <div class="card-body border-top text-end">
        <h4 class="fw-bold">Total: $<span id="total-venta">0</span></h4>
        <button class="btn btn-success" id="btn-finalizar">
            <i class="bi bi-check-circle"></i> Finalizar Venta
        </button>
    </div>
</div>
<?php endif;?>

<!-- PANEL PEDIDOS CON 3 BOTONES - PAGADO EN LUGAR DE ENTREGADO -->
<?php if($panel == 'pedidos'):?>
<div class="card shadow-sm border-0 tabla">
    <div class="card-header bg-dark text-white"><h5 class="mb-0">Pedidos Pendientes</h5></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>ID</th><th>Cliente</th><th>Teléfono</th><th>Total</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php if($pedidos->num_rows > 0):?>
                    <?php while($p = $pedidos->fetch_assoc()):?>
                    <tr>
                        <td>#<?php echo $p['id'];?></td>
                        <td><?php echo htmlspecialchars($p['cliente']?? '');?></td>
                        <td><?php echo htmlspecialchars($p['telefono']?? '');?></td>
                        <td>$<?php echo number_format($p['total'], 2);?></td>
                        <td><span class="badge bg-warning text-dark">Pendiente</span></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($p['fecha_pedido']));?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-success" onclick="cambiarEstado(<?php echo $p['id'];?>, 'pagado')" title="Marcar como Pagado">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                                <button class="btn btn-warning" onclick="cambiarEstado(<?php echo $p['id'];?>, 'apartado')" title="Mandar a Apartado">
                                    <i class="bi bi-cash-coin"></i>
                                </button>
                                <button class="btn btn-danger" onclick="cambiarEstado(<?php echo $p['id'];?>, 'cancelado')" title="Cancelar">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile;?>
                <?php else:?>
                    <tr><td colspan="7" class="text-center text-muted">No hay pedidos pendientes</td></tr>
                <?php endif;?>
            </tbody>
        </table>
    </div>
</div>
<?php endif;?>

<!-- PANEL APARTADOS -->
<?php if($panel == 'apartados'):?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-warning text-dark"><h5 class="mb-0">Control de Apartados</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr><th>ID</th><th>Cliente</th><th>Total</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php if($apartados->num_rows > 0):?>
                        <?php while($a = $apartados->fetch_assoc()):?>
                        <tr>
                            <td>#<?php echo $a['id'];?></td>
                            <td><?php echo htmlspecialchars($a['cliente']?? '');?></td>
                            <td>$<?php echo number_format($a['total'], 2);?></td>
                            <td><span class="badge bg-warning text-dark">Apartado</span></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($a['fecha_pedido']));?></td>
                            <td>
                                <button class="btn btn-primary btn-sm" onclick="cambiarEstado(<?php echo $a['id'];?>, 'pagado')">Liquidar</button>
                                <button class="btn btn-danger btn-sm" onclick="cambiarEstado(<?php echo $a['id'];?>, 'cancelado')">Cancelar</button>
                            </td>
                        </tr>
                        <?php endwhile;?>
                    <?php else:?>
                        <tr><td colspan="6" class="text-center text-muted">No hay apartados</td></tr>
                    <?php endif;?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif;?>

<!-- PANEL CLIENTES SIN EMAIL -->
<?php if($panel == 'clientes'):?>
<div class="card shadow-sm border-0 tabla mb-4">
    <div class="card-header bg-dark text-white"><h5 class="mb-0"><i class="bi bi-person-plus"></i> Dar de Alta Cliente</h5></div>
    <div class="card-body">
        <form method="POST">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Usuario *</label>
                    <input type="text" name="usuario" class="form-control" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Contraseña *</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Nombre completo *</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control">
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" name="crear_cliente" class="btn btn-primary w-100">
                        <i class="bi bi-person-plus"></i> Crear Cliente
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0 tabla">
    <div class="card-header bg-dark text-white"><h5 class="mb-0">Clientes Registrados</h5></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Teléfono</th><th>Compras</th></tr>
            </thead>
            <tbody>
                <?php while($cliente = $clientes->fetch_assoc()):?>
                <tr>
                    <td><?php echo $cliente['id'];?></td>
                    <td><?php echo htmlspecialchars($cliente['usuario']?? '');?></td>
                    <td><?php echo htmlspecialchars($cliente['nombre']?? '');?></td>
                    <td><?php echo htmlspecialchars($cliente['telefono']?? 'Sin teléfono');?></td>
                    <td><span class="badge bg-info"><?php echo $cliente['total_compras'];?></span></td>
                </tr>
                <?php endwhile;?>
            </tbody>
        </table>
    </div>
</div>
<?php endif;?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let carritoVenta = [];

document.getElementById('btn-agregar')?.addEventListener('click', function(){
    const select = document.getElementById('select-producto');
    const option = select.options[select.selectedIndex];

    if(!select.value) return alert('Selecciona un producto');

    const producto = {
        id: select.value,
        nombre: option.dataset.nombre,
        precio: parseFloat(option.dataset.precio),
        stock: parseInt(option.dataset.stock || 999),
        img: option.dataset.img,
        cantidad: parseInt(document.getElementById('input-cantidad').value),
        tipo_pago: document.getElementById('select-pago').value
    };

    const existe = carritoVenta.findIndex(p => p.id == producto.id);
    if(existe >= 0){
        carritoVenta[existe].cantidad += producto.cantidad;
    } else {
        carritoVenta.push(producto);
    }

    actualizarTablaVenta();
    document.getElementById('input-cantidad').value = 1;
});

function actualizarTablaVenta(){
    const tbody = document.querySelector('#tabla-venta tbody');
    tbody.innerHTML = '';
    let total = 0;

    carritoVenta.forEach((p, index) => {
        const subtotal = p.precio * p.cantidad;
        total += subtotal;
        tbody.innerHTML += `
            <tr>
                <td><img src="${p.img}" width="50" class="rounded"></td>
                <td>${p.nombre}</td>
                <td>$${p.precio.toFixed(2)}</td>
                <td>${p.cantidad}</td>
                <td>$${subtotal.toFixed(2)}</td>
                <td><span class="badge bg-${p.tipo_pago=='pagado'?'success':'warning'}">${p.tipo_pago}</span></td>
                <td><button class="btn btn-danger btn-sm" onclick="eliminarDeVenta(${index})"><i class="bi bi-trash"></i></button></td>
            </tr>
        `;
    });

    document.getElementById('total-venta').innerText = total.toFixed(2);
}

function eliminarDeVenta(index){
    carritoVenta.splice(index, 1);
    actualizarTablaVenta();
}

document.getElementById('btn-finalizar')?.addEventListener('click', function(){
    if(carritoVenta.length === 0) return alert('Agrega productos');

    this.disabled = true;
    this.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';

    const formData = new FormData();
    formData.append('id_cliente', document.getElementById('select-cliente').value);
    formData.append('tipo_pago', document.getElementById('select-pago').value);
    formData.append('productos', JSON.stringify(carritoVenta));

    fetch('procesar_venta.php', {
        method: 'POST',
        body: formData
    })
.then(r => r.json())
.then(data => {
        if(data.success){
            alert('Venta #' + data.id_pedido + ' registrada correctamente');
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'No se pudo procesar'));
            document.getElementById('btn-finalizar').disabled = false;
            document.getElementById('btn-finalizar').innerHTML = '<i class="bi bi-check-circle"></i> Finalizar Venta';
        }
    })
.catch(err => {
        alert('Error de conexión');
        document.getElementById('btn-finalizar').disabled = false;
        document.getElementById('btn-finalizar').innerHTML = '<i class="bi bi-check-circle"></i> Finalizar Venta';
    });
});

function cambiarEstado(id, nuevoEstado){
    const textos = {
        'pagado': 'marcar como PAGADO',
        'apartado': 'mandar a APARTADO',
        'cancelado': 'CANCELAR'
    };

    if(confirm('¿Deseas ' + textos[nuevoEstado] + ' el pedido #' + id + '?')){
        fetch('actualizar_pedido.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + id + '&estado=' + nuevoEstado
        }).then(r => r.json()).then(data => {
            console.log(data);
            if(data.success && data.affected > 0) {
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'No se actualizó ningún registro'));
            }
        }).catch(err => {
            alert('Error de conexión: ' + err);
        });
    }
}
</script>
</body>
</html>