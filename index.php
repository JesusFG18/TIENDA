<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "includes/datos.php";

$categoria = isset($_GET['cat']) ? $_GET['cat'] : null;
$subcat = isset($_GET['sub']) ? $_GET['sub'] : null;

$esHombre = ($categoria === 'hombre');
$esMujer = ($categoria === 'mujer');

// Variables para validar roles
$usuario_logueado = $_SESSION['usuario'] ?? null;
$rol = $_SESSION['rol'] ?? null;
$esAdmin = $rol == 'admin';
$esVendedor = $rol == 'vendedor';
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
.talla-select:invalid{ border-color: #dc3545; }
.carousel-control-prev-icon, .carousel-control-next-icon{ background-color: #fff; border-radius: 50%; padding: 25px; filter: drop-shadow(0 2px 8px rgba(0,0,0,0.2)); }
.carousel-indicators button{ background-color: #5e1920; width: 30px; height: 5px; border-radius: 5px; }
.btn-reservar { background-color: #5e1920; }
.btn-reservar:hover { background-color: #4a1419; }
@media(max-width:992px){ .navbar-center{ display: none !important; } }
</style>
</head>

<body>
<!-- NAVBAR CORREGIDO -->
<nav class="navbar navbar-expand-lg navbar-custom py-3">
<div class="container-fluid px-4">

    <!-- LOGO IZQUIERDA -->
    <a class="navbar-brand fw-bold fs-3 m-0" href="index.php">
        <span class="text-white">NOVEDADES</span><span style="color: #f0ad4e;">ECONÓMICA</span>
    </a>

    <!-- CATEGORIAS CENTRO -->
    <div class="mx-auto d-none d-lg-flex gap-2 navbar-center">
        <a href="index.php" class="btn btn-sm px-4 py-2 fw-bold text-white <?php echo !$categoria ? 'btn-nav-active' : ''; ?>">
            NOVEDADES
        </a>
        <a href="?cat=hombre" class="btn btn-sm px-4 py-2 fw-bold text-white <?php echo $categoria=='hombre' ? 'btn-nav-active' : ''; ?>">
            HOMBRE
        </a>
        <a href="?cat=mujer" class="btn btn-sm px-4 py-2 fw-bold text-white <?php echo $categoria=='mujer' ? 'btn-nav-active' : ''; ?>">
            MUJER
        </a>
        <a href="?cat=ninos" class="btn btn-sm px-4 py-2 fw-bold text-white <?php echo $categoria=='ninos' ? 'btn-nav-active' : ''; ?>">
            NIÑOS
        </a>
        <a href="?cat=accesorios" class="btn btn-sm px-4 py-2 fw-bold text-white <?php echo $categoria=='accesorios' ? 'btn-nav-active' : ''; ?>">
            ACCESORIOS
        </a>
    </div>

    <!-- CARRITO + LOGIN DERECHA - OCULTO PARA ADMIN/VENDEDOR -->
    <?php if(!$esAdmin && !$esVendedor): ?>
    <div class="d-flex align-items-center gap-3">
        <!-- CARRITO - CORRECCIÓN 1: Cambié carrito.php por carrito/ -->
        <a href="carrito/" class="position-relative text-white text-decoration-none">
            <i class="bi bi-cart3 fs-4"></i>
            <?php 
            $total_items = 0;
            if(isset($_SESSION['carrito'])) {
                // CORRECCIÓN 2: Tu carrito guarda arrays, no números directos
                foreach($_SESSION['carrito'] as $item){
                    $total_items += $item['cantidad'];
                }
            }
            ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">
                <?php echo $total_items; ?>
            </span>
        </a>
        
        <!-- LOGIN / USUARIO -->
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

<!-- HERO BANNER NUEVO -->
<?php if(!$categoria): ?>
<div style="background-color: #fff; padding: 30px 0;">
<div class="container">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
        
        <div class="carousel-inner rounded-4 overflow-hidden shadow-lg">
            <!-- SLIDE 1 -->
            <div class="carousel-item active">
                <div class="hero-banner">
                    <div class="row g-0 align-items-center" style="min-height: 450px;">
                        <div class="col-lg-6 p-5">
                            <span class="badge-coleccion mb-3 d-inline-block">
                                COLECCIÓN 2026
                            </span>
                            <h1 class="fw-bold text-white mb-3" style="font-size: 3.5rem; line-height: 1.1;">
                                Productos Más<br>Vendidos
                            </h1>
                            <p class="text-white mb-4 fs-5">
                                Descubre las tendencias que todos están comprando esta temporada
                            </p>
                            <a href="#productos" class="btn btn-hero">
                                Ver Colección
                            </a>
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

        <!-- CONTROLES -->
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

<!-- SUBCATEGORIAS -->
<?php if(($esHombre || $esMujer) && !$subcat): ?>
<div class="container py-5 bg-white">
    <h3 class="fw-bold mb-4" style="color: #6A0F1A;">Categorías</h3>
    <div class="row g-3">
        <?php if($esHombre): ?>
        <div class="col-6 col-md-3">
            <a href="?cat=hombre&sub=camisas" class="btn w-100 py-4 fw-bold shadow-sm text-white" style="background-color: #6A0F1A; border-radius: 15px;">
                👕 Camisas
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="?cat=hombre&sub=pantalones" class="btn w-100 py-4 fw-bold shadow-sm text-white" style="background-color: #6A0F1A; border-radius: 15px;">
                👖 Pantalones
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="?cat=hombre&sub=boxer" class="btn w-100 py-4 fw-bold shadow-sm text-white" style="background-color: #6A0F1A; border-radius: 15px;">
                🩲 Boxer
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="?cat=hombre&sub=sudadera" class="btn w-100 py-4 fw-bold shadow-sm text-white" style="background-color: #6A0F1A; border-radius: 15px;">
                🧥 Sudadera/Sueter
            </a>
        </div>
        <?php elseif($esMujer): ?>
        <div class="col-6 col-md-3">
            <a href="?cat=mujer&sub=blusas" class="btn w-100 py-4 fw-bold shadow-sm text-white" style="background-color: #6A0F1A; border-radius: 15px;">
                👚 Blusas
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="?cat=mujer&sub=shorts" class="btn w-100 py-4 fw-bold shadow-sm text-white" style="background-color: #6A0F1A; border-radius: 15px;">
                🩳 Shorts
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="?cat=mujer&sub=vestidos" class="btn w-100 py-4 fw-bold shadow-sm text-white" style="background-color: #6A0F1A; border-radius: 15px;">
                👗 Vestidos
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- PRODUCTOS -->
<div id="productos" class="section-productos">
<div class="container">

<div class="text-center mb-5">
    <h2 class="fw-bold" style="font-size: 2.5rem; color: #2c3e50;">
        <?php echo $subcat ? ucfirst($subcat) : "Productos Más Vendidos"; ?>
    </h2>
    <p class="text-muted fs-5">Los favoritos de nuestros clientes</p>
    
    <?php if($subcat): ?>
    <a href="?cat=<?php echo $categoria; ?>" class="btn btn-outline-dark btn-sm mt-2">
        ← Volver a <?php echo ucfirst($categoria); ?>
    </a>
    <?php endif; ?>
</div>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">

<?php foreach($productos as $producto): ?>

<?php
$mostrar = false;
if(!$categoria){
    $mostrar = true;
}
elseif($categoria === $producto['cat']){
    if(!$subcat){
        $mostrar = true;
    }
    elseif($subcat === $producto['sub']){
        $mostrar = true;
    }
}

$esNuevo = isset($producto['nuevo']) && $producto['nuevo'] === true;
?>

<?php if($mostrar): ?>

<div class="col">

<div class="card card-producto h-100 p-3 text-center border-0 shadow-sm position-relative overflow-hidden" 
     data-id="<?php echo $producto['id']; ?>" 
     data-nombre="<?php echo $producto['nombre']; ?>"
     data-precio="<?php echo $producto['precio']; ?>"
     data-desc="<?php echo htmlspecialchars($producto['descripcion'] ?? ''); ?>"
     data-desc-corta="<?php echo htmlspecialchars($producto['descripcion_corta'] ?? ''); ?>"
     data-material="<?php echo $producto['material'] ?? ''; ?>">

    <?php if($esNuevo): ?>
    <span class="position-absolute top-0 end-0 mt-2 me-2 badge rounded-pill text-white fw-bold" 
          style="background-color: #5e1920; font-size: 11px; z-index: 2;">NUEVO</span>
    <?php endif; ?>

    <div class="position-relative bg-white rounded-3 mb-2 p-3 img-container" style="height: 180px;">
        <img src="<?php echo $producto['img']; ?>" 
             class="img-fluid producto-img w-100 h-100" 
             style="object-fit: contain;"
             alt="<?php echo $producto['nombre']; ?>">
        <button class="btn btn-sm btn-vista-rapida text-white fw-semibold rounded-pill px-3 py-1" 
                style="background: rgba(94, 25, 32, 0.95); font-size: 12px; white-space: nowrap; position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%);"
                data-bs-toggle="modal" data-bs-target="#modalVistaRapida">
            <i class="bi bi-eye"></i> Vista rápida
        </button>
    </div>

    <div class="card-body w-100 p-0 d-flex flex-column">

        <h5 class="fw-bold text-dark mb-1 producto-nombre">
            <?php echo $producto['nombre']; ?>
        </h5>

        <?php if(isset($producto['descripcion_corta'])): ?>
        <p class="text-muted small mb-2 producto-desc-corta">
            <?php echo $producto['descripcion_corta']; ?>
        </p>
        <?php endif; ?>

        <div class="producto-acciones mt-auto">
            <div class="mb-2">
                <span class="fw-bold" style="color: #5e1920; font-size: 1.6rem;">$<?php echo number_format($producto['precio'], 2); ?></span>
            </div>

            <span class="badge bg-success bg-opacity-25 text-success mb-2 px-3 py-1 rounded-pill" style="font-size: 11px;">
                📦 <?php echo $producto['stock']; ?> disponibles
            </span>

            <select class="form-select form-select-sm mb-2 mx-auto talla-select rounded-3" 
                    style="max-width: 150px; font-size: 13px;" required>
                <option value="">Selecciona talla</option>
                <?php if(isset($producto['tallas'])): ?>
                    <?php foreach($producto['tallas'] as $talla): ?>
                        <option value="<?php echo $talla; ?>"><?php echo $talla; ?></option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="U">U</option>
                    <option value="CH">CH</option>
                    <option value="M">M</option>
                    <option value="G">G</option>
                    <option value="XG">XG</option>
                <?php endif; ?>
            </select>

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

<?php endif; ?>

<?php endforeach; ?>

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

</body>
</html>