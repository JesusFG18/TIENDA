<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "includes/db.php";

$categoria_slug = isset($_GET['cat']) ? $_GET['cat'] : null;
$subcat_id = isset($_GET['sub']) ? intval($_GET['sub']) : null;

$categoria_id = null;
$categoria_nombre = null;

// Variables para roles
$usuario_logueado = $_SESSION['usuario'] ?? null;
$rol = $_SESSION['rol'] ?? null;
$esAdmin = ($rol == 'admin');
$esVendedor = ($rol == 'vendedor');

// Cargar categorías activas con orden personalizado: Hombre, Mujer, Niños, Accesorios, luego el resto alfabéticamente
$sql_cats = "SELECT id, nombre, slug FROM categorias WHERE activo = 1 
             ORDER BY CASE nombre 
                 WHEN 'Hombre' THEN 1
                 WHEN 'Mujer' THEN 2
                 WHEN 'Niños' THEN 3
                 WHEN 'Accesorios' THEN 4
                 ELSE 5
             END, nombre ASC";
$stmt_cats = $conn->prepare($sql_cats);
$stmt_cats->execute();
$categorias_result = $stmt_cats->get_result();
$categorias_array = $categorias_result->fetch_all(MYSQLI_ASSOC);
$stmt_cats->close();

// Si se seleccionó una categoría por slug, obtener su ID y nombre
if ($categoria_slug) {
    $stmt_cat = $conn->prepare("SELECT id, nombre FROM categorias WHERE slug = ? AND activo = 1");
    $stmt_cat->bind_param("s", $categoria_slug);
    $stmt_cat->execute();
    $cat_data = $stmt_cat->get_result()->fetch_assoc();
    if ($cat_data) {
        $categoria_id = $cat_data['id'];
        $categoria_nombre = $cat_data['nombre'];
    } else {
        $categoria_slug = null;
    }
    $stmt_cat->close();
}

// Cargar subcategorías si hay categoría seleccionada
$subcategorias_nav = [];
if ($categoria_id) {
    $stmt_sub = $conn->prepare("SELECT id, nombre FROM subcategorias WHERE id_categoria = ? AND activo = 1 ORDER BY nombre");
    $stmt_sub->bind_param("i", $categoria_id);
    $stmt_sub->execute();
    $subcategorias_nav = $stmt_sub->get_result();
    $stmt_sub->close();
}

// Construir consulta de productos con filtros (segura)
$sql = "SELECT p.*, s.nombre as subcategoria_nombre, c.nombre as categoria_nombre 
        FROM productos p 
        LEFT JOIN subcategorias s ON p.id_subcategoria = s.id 
        LEFT JOIN categorias c ON s.id_categoria = c.id
        WHERE p.activo = 1 AND p.stock > 0";
$params = [];
$types = "";

if ($categoria_id && !$subcat_id) {
    $sql .= " AND c.id = ?";
    $params[] = $categoria_id;
    $types .= "i";
} elseif ($subcat_id) {
    $sql .= " AND p.id_subcategoria = ?";
    $params[] = $subcat_id;
    $types .= "i";
}

$sql .= " ORDER BY p.nuevo DESC, p.fecha_creacion DESC";

$stmt_prod = $conn->prepare($sql);
if (!empty($params)) {
    $stmt_prod->bind_param($types, ...$params);
}
$stmt_prod->execute();
$productos_query = $stmt_prod->get_result();
$stmt_prod->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Novedades Económica</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="css/estilos.css">
<style>
body{ background-color: #fff !important; }
.navbar-custom{ background-color: #5e1920; }
.btn-nav-active{ background-color: rgba(0,0,0,0.3); border-radius: 25px; }
.hero-banner{ background-color: #5e1920; border-radius: 20px; }
.btn-hero{ background-color: #fff; color: #5e1920; border-radius: 15px; border: none; padding: 12px 30px; font-weight: bold; }
.btn-hero:hover{ background-color: #f8f9fa; color: #5e1920; }
.badge-coleccion{ background-color: #f0ad4e; color: #5e1920; border-radius: 20px; padding: 8px 20px; font-weight: 600; }
.section-productos{ background-color: #fff; padding: 60px 0; }
.carousel-control-prev-icon, .carousel-control-next-icon{ background-color: #fff; border-radius: 50%; padding: 25px; filter: drop-shadow(0 2px 8px rgba(0,0,0,0.2)); }
.carousel-indicators button{ background-color: #5e1920; width: 30px; height: 5px; border-radius: 5px; }
.btn-reservar { background-color: #5e1920; }
.btn-reservar:hover { background-color: #4a1419; }
@media(max-width:992px){ .navbar-center{ display: none !important; } }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom py-3">
<div class="container-fluid px-4">
    <a class="navbar-brand fw-bold fs-3 m-0" href="index.php">
        <span class="text-white">NOVEDADES</span><span style="color: #f0ad4e;">ECONÓMICA</span>
    </a>
    <div class="mx-auto d-none d-lg-flex gap-2 navbar-center">
        <a href="index.php" class="btn btn-sm px-4 py-2 fw-bold text-white <?php echo !$categoria_slug ? 'btn-nav-active' : ''; ?>">
            NOVEDADES
        </a>
        <?php
        $max_visibles = 5;
        $principales = array_slice($categorias_array, 0, $max_visibles);
        $restantes = array_slice($categorias_array, $max_visibles);
        ?>
        <?php foreach ($principales as $cat): ?>
        <a href="?cat=<?php echo $cat['slug']; ?>" class="btn btn-sm px-4 py-2 fw-bold text-white <?php echo ($categoria_slug == $cat['slug']) ? 'btn-nav-active' : ''; ?>">
            <?php echo strtoupper($cat['nombre']); ?>
        </a>
        <?php endforeach; ?>
        
        <?php if (count($restantes) > 0): ?>
        <div class="dropdown">
            <button class="btn btn-sm px-4 py-2 fw-bold text-white dropdown-toggle" type="button" data-bs-toggle="dropdown">
                MÁS
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <?php foreach ($restantes as $cat): ?>
                <li><a class="dropdown-item" href="?cat=<?php echo $cat['slug']; ?>"><?php echo strtoupper($cat['nombre']); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>

    <?php if(!$esAdmin && !$esVendedor): ?>
    <div class="d-flex align-items-center gap-3">
        <a href="carrito/" class="position-relative text-white text-decoration-none">
            <i class="bi bi-cart3 fs-4"></i>
            <?php 
            $total_items = 0;
            if(isset($_SESSION['carrito'])) {
                foreach($_SESSION['carrito'] as $item){
                    $total_items += $item['cantidad'];
                }
            }
            ?>
            <span id="cart-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">
                <?php echo $total_items; ?>
            </span>
        </a>
        
        <?php if(isset($_SESSION['usuario'])): ?>
            <div class="dropdown">
                <button class="btn btn-light btn-sm fw-bold dropdown-toggle" type="button" data-bs-toggle="dropdown" style="border-radius: 20px; color: #5e1920;">
                    <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['usuario']); ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="mis_compras.php"><i class="bi bi-bag-check"></i> Mis Compras</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</a></li>
                </ul>
            </div>
        <?php else: ?>
            <a href="login.php" class="btn btn-light btn-sm fw-bold" style="border-radius: 20px; color: #5e1920;">
                Iniciar Sesión
            </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
</nav>

<?php if(!$categoria_slug): ?>
<div style="background-color: #fff; padding: 30px 0;">
<div class="container">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner rounded-4 overflow-hidden shadow-lg">
            <div class="carousel-item active">
                <div class="hero-banner">
                    <div class="row g-0 align-items-center" style="min-height: 450px;">
                        <div class="col-lg-6 p-5">
                            <span class="badge-coleccion mb-3 d-inline-block">COLECCIÓN 2026</span>
                            <h1 class="fw-bold text-white mb-3" style="font-size: 3.5rem; line-height: 1.1;">
                                Productos Más<br>Vendidos
                            </h1>
                            <p class="text-white mb-4 fs-5">
                                Descubre las tendencias que todos están comprando esta temporada
                            </p>
                            <a href="#productos" class="btn btn-hero">Ver Colección</a>
                        </div>
                        <div class="col-lg-6 p-4">
                            <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=600" 
                                 class="img-fluid rounded-4" 
                                 style="height: 380px; width: 100%; object-fit: cover;"
                                 alt="Colección">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" style="width: 50px; left: -25px;">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" style="width: 50px; right: -25px;">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
</div>
</div>
<?php endif; ?>

<?php if($categoria_slug && !$subcat_id && $subcategorias_nav && $subcategorias_nav->num_rows > 0): ?>
<div class="container py-5 bg-white">
    <h3 class="fw-bold mb-4" style="color: #6A0F1A;">Categorías de <?php echo htmlspecialchars($categoria_nombre); ?></h3>
    <div class="row g-3">
        <?php while($sub = $subcategorias_nav->fetch_assoc()): ?>
        <div class="col-6 col-md-3">
            <a href="?cat=<?php echo $categoria_slug; ?>&sub=<?php echo $sub['id']; ?>" class="btn w-100 py-4 fw-bold shadow-sm text-white" style="background-color: #6A0F1A; border-radius: 15px;">
                <?php echo htmlspecialchars($sub['nombre']); ?>
            </a>
        </div>
        <?php endwhile; ?>
    </div>
</div>
<?php endif; ?>

<div id="productos" class="section-productos">
<div class="container">
<div class="text-center mb-5">
    <h2 class="fw-bold" style="font-size: 2.5rem; color: #2c3e50;">
        <?php 
        if($subcat_id){
            $stmt_subnom = $conn->prepare("SELECT nombre FROM subcategorias WHERE id = ?");
            $stmt_subnom->bind_param("i", $subcat_id);
            $stmt_subnom->execute();
            $sub_nombre = $stmt_subnom->get_result()->fetch_assoc()['nombre'] ?? 'Productos';
            $stmt_subnom->close();
            echo ucfirst(htmlspecialchars($sub_nombre));
        } else {
            echo "Productos Más Vendidos";
        }
        ?>
    </h2>
    <p class="text-muted fs-5">Los favoritos de nuestros clientes</p>
    <?php if($subcat_id): ?>
    <a href="?cat=<?php echo $categoria_slug; ?>" class="btn btn-outline-dark btn-sm mt-2">
        ← Volver a <?php echo htmlspecialchars($categoria_nombre); ?>
    </a>
    <?php endif; ?>
</div>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
<?php while($producto = $productos_query->fetch_assoc()): ?>
<?php $esNuevo = ($producto['nuevo'] == 1); ?>
<div class="col">
<div class="card card-producto h-100 p-3 text-center border-0 shadow-sm position-relative overflow-hidden" 
     data-id="<?php echo $producto['id']; ?>" 
     data-nombre="<?php echo htmlspecialchars($producto['nombre']); ?>"
     data-precio="<?php echo $producto['precio_oferta'] ?? $producto['precio']; ?>"
     data-desc="<?php echo htmlspecialchars($producto['descripcion'] ?? ''); ?>"
     data-desc-corta="<?php echo htmlspecialchars($producto['descripcion_corta'] ?? ''); ?>"
     data-material="<?php echo htmlspecialchars($producto['material'] ?? ''); ?>">

    <?php if($esNuevo): ?>
    <span class="position-absolute top-0 end-0 mt-2 me-2 badge rounded-pill text-white fw-bold" 
          style="background-color: #5e1920; font-size: 11px; z-index: 2;">NUEVO</span>
    <?php endif; ?>

    <div class="position-relative bg-white rounded-3 mb-2 p-3 img-container" style="height: 180px;">
        <img src="<?php echo $producto['img_principal']; ?>" 
             class="img-fluid producto-img w-100 h-100" 
             style="object-fit: contain;"
             alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
        <button class="btn btn-sm btn-vista-rapida text-white fw-semibold rounded-pill px-3 py-1" 
                style="background: rgba(94, 25, 32, 0.95); font-size: 12px; white-space: nowrap; position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%);"
                data-bs-toggle="modal" data-bs-target="#modalVistaRapida">
            <i class="bi bi-eye"></i> Vista rápida
        </button>
    </div>

    <div class="card-body w-100 p-0 d-flex flex-column">
        <h5 class="fw-bold text-dark mb-1 producto-nombre"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
        <?php if($producto['descripcion_corta']): ?>
        <p class="text-muted small mb-2 producto-desc-corta"><?php echo htmlspecialchars($producto['descripcion_corta']); ?></p>
        <?php endif; ?>

        <div class="producto-acciones mt-auto">
            <div class="mb-2">
                <?php if($producto['precio_oferta']): ?>
                    <span class="text-decoration-line-through text-muted" style="font-size: 1.2rem;">$<?php echo number_format($producto['precio'], 2); ?></span><br>
                    <span class="fw-bold" style="color: #dc3545; font-size: 1.6rem;">$<?php echo number_format($producto['precio_oferta'], 2); ?></span>
                <?php else: ?>
                    <span class="fw-bold" style="color: #5e1920; font-size: 1.6rem;">$<?php echo number_format($producto['precio'], 2); ?></span>
                <?php endif; ?>
            </div>

            <span class="badge bg-success bg-opacity-25 text-success mb-2 px-3 py-1 rounded-pill" style="font-size: 11px;">
                📦 <?php echo $producto['stock']; ?> disponibles
            </span>

            <!-- Sin selector de tallas -->
            <div class="input-group input-group-sm mb-2 justify-content-center">
                <button class="btn btn-outline-secondary btn-restar rounded-start-3" type="button">-</button>
                <input type="number" class="form-control text-center cantidad-input" value="1" min="1" max="<?php echo $producto['stock']; ?>" readonly>
                <button class="btn btn-outline-secondary btn-sumar rounded-end-3" type="button">+</button>
            </div>

            <button class="btn btn-reservar w-100 text-white fw-bold btn-agregar rounded-3 py-2">
                <i class="bi bi-cart-plus"></i> Agregar
            </button>
        </div>
    </div>
</div>
</div>
<?php endwhile; ?>
</div>
</div>
</div>

<!-- MODAL VISTA RÁPIDA -->
<div class="modal fade" id="modalVistaRapida" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 20px;">
      <div class="modal-header text-white" style="background-color: #5e1920; border-radius: 20px 20px 0 0;">
        <h5 class="modal-title fw-bold" id="modal-nombre">Producto</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <img src="" id="modal-img" class="img-fluid mb-3" style="max-height: 300px; object-fit: contain;">
        <h3 class="fw-bold" style="color: #5e1920;" id="modal-precio">$0</h3>
        <p class="text-muted small mb-2" id="modal-desc"></p>
        <p class="small"><strong>Material:</strong> <span id="modal-material"></span></p>
        <p class="badge bg-success bg-opacity-25 text-success" id="modal-stock"></p>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>

<!-- Script para corregir botones + y - y agregar al carrito instantáneo -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Función para actualizar cantidad (+/-)
    function actualizarCantidad(input, cambio) {
        let valor = parseInt(input.value) || 1;
        let max = parseInt(input.getAttribute('max')) || 99;
        let nuevo = valor + cambio;
        if (nuevo >= 1 && nuevo <= max) {
            input.value = nuevo;
        }
    }

    // Botones sumar
    document.querySelectorAll('.btn-sumar').forEach(btn => {
        btn.removeEventListener('click', window.sumarHandler);
        window.sumarHandler = function(e) {
            e.preventDefault();
            let input = this.parentElement.querySelector('.cantidad-input');
            if (input) actualizarCantidad(input, 1);
        };
        btn.addEventListener('click', window.sumarHandler);
    });

    // Botones restar
    document.querySelectorAll('.btn-restar').forEach(btn => {
        btn.removeEventListener('click', window.restarHandler);
        window.restarHandler = function(e) {
            e.preventDefault();
            let input = this.parentElement.querySelector('.cantidad-input');
            if (input) actualizarCantidad(input, -1);
        };
        btn.addEventListener('click', window.restarHandler);
    });

    // Agregar al carrito (sin tallas) con actualización instantánea
    document.querySelectorAll('.btn-agregar').forEach(btn => {
        // Clonar para eliminar eventos anteriores
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);
        
        newBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const card = this.closest('.card-producto');
            if (!card) return;
            
            const cantidadInput = card.querySelector('.cantidad-input');
            if (!cantidadInput) return;
            
            const cantidad = cantidadInput.value;
            const id = card.getAttribute('data-id');
            
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Agregando...';
            
            fetch('carrito/agregar.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `id_producto=${encodeURIComponent(id)}&cantidad=${encodeURIComponent(cantidad)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Actualizar badge del carrito instantáneamente
                    const badge = document.getElementById('cart-badge');
                    if (badge) badge.textContent = data.total;
                    
                    this.innerHTML = '<i class="bi bi-check-circle"></i> Agregado';
                    this.classList.remove('btn-reservar');
                    this.classList.add('btn-success');
                    setTimeout(() => {
                        this.innerHTML = '<i class="bi bi-cart-plus"></i> Agregar';
                        this.classList.remove('btn-success');
                        this.classList.add('btn-reservar');
                        this.disabled = false;
                        cantidadInput.value = 1;
                    }, 2000);
                } else {
                    alert('Error: ' + (data.error || 'No se pudo agregar al carrito'));
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-cart-plus"></i> Agregar';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al conectar con el servidor');
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-cart-plus"></i> Agregar';
            });
        });
    });
});
</script>
</body>
</html>