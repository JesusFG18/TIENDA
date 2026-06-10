<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/auth.php";
verificarRol('admin');

// Generar slug a partir del nombre
function generarSlug($texto) {
    $texto = strtolower(trim($texto));
    $acentos = array('á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u');
    $texto = strtr($texto, $acentos);
    $texto = preg_replace('/[^a-z0-9\s-]/', '', $texto);
    $texto = preg_replace('/[\s-]+/', '-', $texto);
    return trim($texto, '-');
}

// AGREGAR CATEGORÍA (solo nombre, el resto automático)
if(isset($_POST['agregar'])){
    $nombre = trim($_POST['nombre']);
    if(!empty($nombre)){
        $slug_base = generarSlug($nombre);
        $slug = $slug_base;
        $counter = 1;
        
        // Verificar unicidad del slug
        $check = $conn->prepare("SELECT id FROM categorias WHERE slug = ?");
        $check->bind_param("s", $slug);
        $check->execute();
        $check->store_result();
        while($check->num_rows > 0){
            $check->close();
            $slug = $slug_base . '-' . $counter++;
            $check = $conn->prepare("SELECT id FROM categorias WHERE slug = ?");
            $check->bind_param("s", $slug);
            $check->execute();
            $check->store_result();
        }
        $check->close();
        
        // Valores por defecto
        $icono = '?';
        $tipo = 'general';
        $orden = 0;
        
        $stmt = $conn->prepare("INSERT INTO categorias (nombre, slug, icono, tipo, orden, activo) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("ssssi", $nombre, $slug, $icono, $tipo, $orden);
        $stmt->execute();
        header("Location: categorias.php?ok=1");
        exit();
    }
}

// ACTIVAR o DESACTIVAR categoría según el estado actual
if(isset($_GET['toggle'])){
    $id = intval($_GET['toggle']);
    // Obtener estado actual
    $stmt = $conn->prepare("SELECT activo FROM categorias WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($row = $result->fetch_assoc()){
        $nuevo_estado = $row['activo'] ? 0 : 1;
        $stmt2 = $conn->prepare("UPDATE categorias SET activo = ? WHERE id = ?");
        $stmt2->bind_param("ii", $nuevo_estado, $id);
        $stmt2->execute();
        $stmt2->close();
    }
    $stmt->close();
    header("Location: categorias.php");
    exit();
}

// Cargar TODAS las categorías (activas e inactivas) para mostrar
$categorias = $conn->query("SELECT id, nombre, activo FROM categorias ORDER BY nombre");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin - Categorías</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #5e1920; }
        .badge-activo { background-color: #28a745; color: white; padding: 5px 10px; border-radius: 20px; font-size: 12px; }
        .badge-inactivo { background-color: #dc3545; color: white; padding: 5px 10px; border-radius: 20px; font-size: 12px; }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar text-white p-3" style="width: 250px;">
        <h4 class="text-center mb-4">ADMIN</h4>
        <ul class="nav flex-column">
            <li class="nav-item mb-2"><a href="inventario.php" class="nav-link text-white"><i class="bi bi-box"></i> Inventario</a></li>
            <li class="nav-item mb-2"><a href="pedidos.php" class="nav-link text-white"><i class="bi bi-cart"></i> Pedidos</a></li>
            <li class="nav-item mb-2"><a href="reportes.php" class="nav-link text-white"><i class="bi bi-graph-up"></i> Reportes</a></li>
            <li class="nav-item mb-2"><a href="usuarios.php" class="nav-link text-white"><i class="bi bi-people"></i> Usuarios</a></li>
            <li class="nav-item mb-2 bg-danger rounded"><a href="categorias.php" class="nav-link text-white"><i class="bi bi-tags"></i> Secciones/Categorías</a></li>
            <li class="nav-item mt-4"><a href="../logout.php" class="nav-link text-white"><i class="bi bi-box-arrow-left"></i> Cerrar Sesión</a></li>
        </ul>
    </div>

    <!-- Contenido -->
    <div class="flex-grow-1 p-4">
        <?php if(isset($_GET['ok'])): ?>
            <div class="alert alert-success">Categoría agregada correctamente</div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Secciones / Categorías</h5>
            </div>
            <div class="card-body">
                <!-- Formulario para agregar (solo nombre) -->
                <form method="POST" class="mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Nombre de la Categoría</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej: Ropa, Calzado, Accesorios" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" name="agregar" class="btn btn-success w-100">
                                <i class="bi bi-plus-circle"></i> Agregar
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Tabla: solo ID, Nombre y Estado/Acciones -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($cat = $categorias->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $cat['id']; ?></td>
                                <td><?php echo htmlspecialchars($cat['nombre']); ?></td>
                                <td>
                                    <?php if($cat['activo']): ?>
                                        <span class="badge-activo">ACTIVA</span>
                                    <?php else: ?>
                                        <span class="badge-inactivo">INACTIVA</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?toggle=<?php echo $cat['id']; ?>" class="btn btn-sm <?php echo $cat['activo'] ? 'btn-warning' : 'btn-success'; ?>" onclick="return confirm('¿Cambiar estado de esta categoría?')">
                                        <?php if($cat['activo']): ?>
                                            <i class="bi bi-eye-slash"></i> Desactivar
                                        <?php else: ?>
                                            <i class="bi bi-eye"></i> Activar
                                        <?php endif; ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>