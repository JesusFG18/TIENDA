<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/auth.php";
verificarRol('admin');

// AGREGAR/EDITAR PRODUCTO
if(isset($_POST['guardar_producto'])){
    $id = trim($_POST['id']);
    $id_subcategoria = intval($_POST['id_subcategoria']);
    $nombre = trim($_POST['nombre']);
    $descripcion_corta = trim($_POST['descripcion_corta']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $precio_oferta = !empty($_POST['precio_oferta']) ? floatval($_POST['precio_oferta']) : NULL;
    $stock = intval($_POST['stock']);
    $material = trim($_POST['material']);
    $nuevo = isset($_POST['nuevo']) ? 1 : 0;
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $activo = isset($_POST['activo']) ? 1 : 0;
    $editar = !empty($_POST['editar']) ? true : false;
    
    // Validar ID único solo si es nuevo
    if(!$editar){
        $check = $conn->prepare("SELECT id FROM productos WHERE id =?");
        $check->bind_param("s", $id);
        $check->execute();
        if($check->get_result()->num_rows > 0){
            header("Location: inventario.php?error=ID duplicado");
            exit();
        }
        $check->close();
    }
    
    // Subir imagen
    $img_principal = $_POST['img_actual'] ?? '';
    if(isset($_FILES['img_principal']) && $_FILES['img_principal']['error'] == 0){
        $ext = pathinfo($_FILES['img_principal']['name'], PATHINFO_EXTENSION);
        
        // Obtener slugs para carpeta
        $sub_data = $conn->query("SELECT s.slug as sub_slug, c.slug as cat_slug 
                                  FROM subcategorias s 
                                  JOIN categorias c ON s.id_categoria = c.id 
                                  WHERE s.id = $id_subcategoria")->fetch_assoc();
        
        $cat_slug = $sub_data['cat_slug'] ?? 'otros';
        $sub_slug = $sub_data['sub_slug'] ?? 'otros';
        
        $ruta_carpeta = '../img/productos/' . $cat_slug . '/' . $sub_slug . '/';
        if(!is_dir($ruta_carpeta)){
            mkdir($ruta_carpeta, 0777, true);
        }
        
        $nombre_img = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $nombre)) . '_' . time() . '.' . $ext;
        $img_principal = 'img/productos/' . $cat_slug . '/' . $sub_slug . '/' . $nombre_img;
        move_uploaded_file($_FILES['img_principal']['tmp_name'], $ruta_carpeta . $nombre_img);
    }
    
    if($editar){
        // UPDATE
        $stmt = $conn->prepare("UPDATE productos SET id_subcategoria=?, nombre=?, descripcion_corta=?, descripcion=?, precio=?, stock=?, precio_oferta=?, img_principal=?, material=?, nuevo=?, destacado=?, activo=? WHERE id=?");
        $stmt->bind_param("isssdisssiiii", $id_subcategoria, $nombre, $descripcion_corta, $descripcion, $precio, $stock, $precio_oferta, $img_principal, $material, $nuevo, $destacado, $activo, $id);
    } else {
        // INSERT
        $stmt = $conn->prepare("INSERT INTO productos (id, id_subcategoria, nombre, descripcion_corta, descripcion, precio, stock, precio_oferta, img_principal, material, nuevo, destacado, activo) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sisssdisssiii", $id, $id_subcategoria, $nombre, $descripcion_corta, $descripcion, $precio, $stock, $precio_oferta, $img_principal, $material, $nuevo, $destacado, $activo);
    }
    
    if($stmt->execute()){
        header("Location: inventario.php?ok=1");
    } else {
        header("Location: inventario.php?error=" . urlencode($stmt->error));
    }
    exit();
}

// DESACTIVAR PRODUCTO
if(isset($_GET['desactivar'])){
    $id = $_GET['desactivar'];
    $stmt = $conn->prepare("UPDATE productos SET activo = 0 WHERE id =?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    header("Location: inventario.php?del=1");
    exit();
}

// ACTIVAR PRODUCTO
if(isset($_GET['activar'])){
    $id = $_GET['activar'];
    $stmt = $conn->prepare("UPDATE productos SET activo = 1 WHERE id =?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    header("Location: inventario.php?ok=1");
    exit();
}

// CARGAR PRODUCTOS CON SUBCATEGORIA - SIN FILTRO DE ACTIVO PARA VER TODOS
$productos = $conn->query("SELECT p.*, 
                           COALESCE(s.nombre, 'Sin subcategoría') as subcategoria, 
                           COALESCE(c.nombre, 'Sin categoría') as categoria 
                           FROM productos p 
                           LEFT JOIN subcategorias s ON p.id_subcategoria = s.id 
                           LEFT JOIN categorias c ON s.id_categoria = c.id
                           ORDER BY p.activo DESC, p.id DESC");

// CARGAR SUBCATEGORIAS PARA EL SELECT
$subcategorias_result = $conn->query("SELECT s.id, s.nombre as sub, c.nombre as cat 
                               FROM subcategorias s 
                               JOIN categorias c ON s.id_categoria = c.id 
                               WHERE s.activo = 1 AND c.activo = 1
                               ORDER BY c.nombre, s.nombre");
$subcategorias = $subcategorias_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin - Inventario</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
body { background-color: #f8f9fa; }
.sidebar { min-height: 100vh; background-color: #5e1920; }
.table-desactivado { opacity: 0.5; }
.img-thumb { width:50px; height:50px; object-fit:cover; }
</style>
</head>
<body>

<div class="d-flex">
    <!-- SIDEBAR -->
    <div class="sidebar text-white p-3" style="width: 250px;">
        <h4 class="text-center mb-4">ADMIN</h4>
        <ul class="nav flex-column">
            <li class="nav-item mb-2 bg-danger rounded"><a href="inventario.php" class="nav-link text-white"><i class="bi bi-box"></i> Inventario</a></li>
            <li class="nav-item mb-2"><a href="pedidos.php" class="nav-link text-white"><i class="bi bi-cart"></i> Pedidos</a></li>
            <li class="nav-item mb-2"><a href="reportes.php" class="nav-link text-white"><i class="bi bi-graph-up"></i> Reportes</a></li>
            <li class="nav-item mb-2"><a href="usuarios.php" class="nav-link text-white"><i class="bi bi-people"></i> Usuarios</a></li>
            <li class="nav-item mb-2"><a href="categorias.php" class="nav-link text-white"><i class="bi bi-tags"></i> Secciones/Categorías</a></li>
            <li class="nav-item mt-4"><a href="../logout.php" class="nav-link text-white"><i class="bi bi-box-arrow-left"></i> Cerrar Sesión</a></li>
        </ul>
    </div>

    <!-- CONTENIDO -->
    <div class="flex-grow-1 p-4">
        
        <?php if(isset($_GET['ok'])): ?>
        <div class="alert alert-success alert-dismissible fade show">Producto guardado correctamente<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <?php if(isset($_GET['del'])): ?>
        <div class="alert alert-warning alert-dismissible fade show">Producto desactivado<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        
        <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">Error: <?php echo htmlspecialchars($_GET['error']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Inventario de Productos</h5>
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalProducto" onclick="limpiarModal()">
                    <i class="bi bi-plus-circle"></i> Agregar Producto
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Imagen</th>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Categoría</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($productos->num_rows > 0): ?>
                                <?php while($p = $productos->fetch_assoc()): ?>
                                <tr class="<?php echo $p['activo'] == 0 ? 'table-desactivado' : ''; ?>">
                                    <td><?php echo $p['id']; ?></td>
                                    <td>
                                        <?php 
                                        $img_src = !empty($p['img_principal']) ? '../' . $p['img_principal'] : '../img/no-image.png';
                                        ?>
                                        <img src="<?php echo $img_src; ?>" class="img-thumb rounded" onerror="this.onerror=null;this.src='../img/no-image.png';">
                                    </td>
                                    <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                                    <td>
                                        <?php if($p['precio_oferta']): ?>
                                            <span class="text-decoration-line-through text-muted">$<?php echo number_format($p['precio'], 2); ?></span><br>
                                            <span class="text-danger fw-bold">$<?php echo number_format($p['precio_oferta'], 2); ?></span>
                                        <?php else: ?>
                                            $<?php echo number_format($p['precio'], 2); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $p['stock'] > 10 ? 'success' : ($p['stock'] > 0 ? 'warning' : 'danger'); ?>">
                                            <?php echo $p['stock']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($p['categoria'] . ' / ' . $p['subcategoria']); ?></td>
                                    <td>
                                        <?php if($p['activo'] == 1): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-sm btn-editar" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalProducto"
                                                data-id="<?php echo $p['id']; ?>"
                                                data-nombre="<?php echo htmlspecialchars($p['nombre'],ENT_QUOTES); ?>"
                                                data-desc-corta="<?php echo htmlspecialchars($p['descripcion_corta'],ENT_QUOTES); ?>"
                                                data-desc="<?php echo htmlspecialchars($p['descripcion'],ENT_QUOTES); ?>"
                                                data-precio="<?php echo $p['precio']; ?>"
                                                data-precio-oferta="<?php echo $p['precio_oferta']; ?>"
                                                data-stock="<?php echo $p['stock']; ?>"
                                                data-subcat="<?php echo $p['id_subcategoria']; ?>"
                                                data-material="<?php echo htmlspecialchars($p['material'],ENT_QUOTES); ?>"
                                                data-img="<?php echo $p['img_principal']; ?>"
                                                data-nuevo="<?php echo $p['nuevo']; ?>"
                                                data-destacado="<?php echo $p['destacado']; ?>"
                                                data-activo="<?php echo $p['activo']; ?>"
                                                title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        
                                        <?php if($p['activo'] == 1): ?>
                                        <a href="?desactivar=<?php echo $p['id']; ?>" class="btn btn-warning btn-sm" onclick="return confirm('¿Desactivar producto?')" title="Desactivar">
                                            <i class="bi bi-eye-slash"></i>
                                        </a>
                                        <?php else: ?>
                                        <a href="?activar=<?php echo $p['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('¿Activar producto?')" title="Activar">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center text-muted">No hay productos registrados</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL AGREGAR/EDITAR PRODUCTO -->
<div class="modal fade" id="modalProducto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="editar" id="editar_id" value="">
                <input type="hidden" name="img_actual" id="img_actual" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitulo">Agregar Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">ID Producto *</label>
                            <input type="text" name="id" id="input_id" class="form-control" maxlength="20" placeholder="021" required>
                        </div>
                        <div class="col-md-9 mb-3">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="nombre" id="input_nombre" class="form-control" maxlength="200" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descripción Corta</label>
                        <textarea name="descripcion_corta" id="input_desc_corta" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descripción Completa</label>
                        <textarea name="descripcion" id="input_desc" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Precio *</label>
                            <input type="number" step="0.01" name="precio" id="input_precio" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Precio Oferta</label>
                            <input type="number" step="0.01" name="precio_oferta" id="input_precio_oferta" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stock *</label>
                            <input type="number" name="stock" id="input_stock" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Subcategoría *</label>
                            <select name="id_subcategoria" id="input_subcat" class="form-select" required>
                                <option value="">Selecciona...</option>
                                <?php foreach($subcategorias as $s): ?>
                                <option value="<?php echo $s['id']; ?>"><?php echo $s['cat'] . ' - ' . $s['sub']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Material</label>
                            <input type="text" name="material" id="input_material" class="form-control" maxlength="100">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Imagen Principal</label>
                        <input type="file" name="img_principal" class="form-control" accept="image/*">
                        <div id="preview_img" class="mt-2"></div>
                        <small class="text-muted">Deja vacío para mantener la imagen actual al editar</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="nuevo" value="1" id="input_nuevo">
                                <label class="form-check-label">Producto Nuevo</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="destacado" value="1" id="input_destacado">
                                <label class="form-check-label">Destacado</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="activo" value="1" id="input_activo" checked>
                                <label class="form-check-label">Activo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="guardar_producto" class="btn btn-success">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Limpiar modal al agregar
function limpiarModal(){
    document.getElementById('modalTitulo').innerText = 'Agregar Producto';
    document.getElementById('editar_id').value = '';
    document.getElementById('input_id').readOnly = false;
    document.querySelector('#modalProducto form').reset();
    document.getElementById('preview_img').innerHTML = '';
    document.getElementById('input_activo').checked = true;
}

// Cargar datos al editar
document.querySelectorAll('.btn-editar').forEach(btn => {
    btn.addEventListener('click', function(){
        document.getElementById('modalTitulo').innerText = 'Editar Producto';
        document.getElementById('editar_id').value = this.dataset.id;
        document.getElementById('input_id').value = this.dataset.id;
        document.getElementById('input_id').readOnly = true;
        document.getElementById('input_nombre').value = this.dataset.nombre;
        document.getElementById('input_desc_corta').value = this.dataset.descCorta;
        document.getElementById('input_desc').value = this.dataset.desc;
        document.getElementById('input_precio').value = this.dataset.precio;
        document.getElementById('input_precio_oferta').value = this.dataset.precioOferta;
        document.getElementById('input_stock').value = this.dataset.stock;
        document.getElementById('input_subcat').value = this.dataset.subcat;
        document.getElementById('input_material').value = this.dataset.material;
        document.getElementById('img_actual').value = this.dataset.img;
        document.getElementById('input_nuevo').checked = this.dataset.nuevo == 1;
        document.getElementById('input_destacado').checked = this.dataset.destacado == 1;
        document.getElementById('input_activo').checked = this.dataset.activo == 1;
        
        if(this.dataset.img){
            document.getElementById('preview_img').innerHTML = '<img src="../'+this.dataset.img+'" width="100" class="rounded">';
        }
    });
});
</script>

</body>
</html>