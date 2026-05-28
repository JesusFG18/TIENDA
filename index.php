<?php

require_once "includes/datos.php";

$categoria = isset($_GET['cat']) ? $_GET['cat'] : null;
$subcat = isset($_GET['sub']) ? $_GET['sub'] : null;

$esHombre = ($categoria === 'hombre');
$esMujer = ($categoria === 'mujer');

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Novedades Economica</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background-color: #5e1920 !important;
}

.card-producto{
    border-radius: 15px;
    transition: 0.3s ease;
}

.card-producto:hover{
    transform: translateY(-5px);
}

.btn-reservar{
    background-color: #5e1920;
    border-radius: 20px;
}

.btn-reservar:hover{
    background-color: #7a1f29;
}

.cantidad-input{
    max-width: 50px;
}

.carousel-caption{
    background: rgba(0,0,0,0.55);
    padding: 20px;
    border-radius: 15px;
}

.carousel-caption h1,
.carousel-caption p{
    text-shadow: 2px 2px 8px rgba(0,0,0,0.8);
}

.carousel-control-prev-icon,
.carousel-control-next-icon{
    background-color: rgba(0,0,0,0.7);
    border-radius: 50%;
    padding: 25px;
}

@media(max-width:768px){

    .navbar .container{
        flex-direction: column;
        align-items: flex-start !important;
    }

}

</style>

</head>

<body class="text-white">
<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark"
     style="background-color: rgba(0,0,0,0.3);">

<div class="container-fluid px-4">

    <!-- GRUPO IZQUIERDA: NOMBRE + CATEGORÍAS PEGADAS -->
    <div class="d-flex align-items-center gap-3">
        
        <!-- NOMBRE TIENDA SOLO TEXTO -->
        <span class="navbar-brand fw-bold m-0">
            NOVEDADES_ECONOMICA
        </span>

        <!-- TIENDA SOLO CLIENTE -->
        <?php if(!isset($esAdmin) && !isset($esVendedor)): ?>
        <a href="index.php" class="text-white text-decoration-none fw-bold">
            TIENDA
        </a>
        <?php endif; ?>

        <!-- CATEGORIAS PEGADAS AL NOMBRE -->
        <div class="d-flex gap-3">
            <a href="<?php echo isset($esAdmin) ? '?panel=tienda&cat=hombre' : '?cat=hombre'; ?>"
               class="text-white text-decoration-none fw-bold">
                HOMBRE
            </a>
            <a href="<?php echo isset($esAdmin) ? '?panel=tienda&cat=mujer' : '?cat=mujer'; ?>"
               class="text-white text-decoration-none fw-bold">
                MUJER
            </a>
            <a href="<?php echo isset($esAdmin) ? '?panel=tienda&cat=ninos' : '?cat=ninos'; ?>"
               class="text-white text-decoration-none fw-bold">
                NIÑOS
            </a>
            <a href="<?php echo isset($esAdmin) ? '?panel=tienda&cat=accesorios' : '?cat=accesorios'; ?>"
               class="text-white text-decoration-none fw-bold">
                ACCESORIOS
            </a>
        </div>

    </div>

    <!-- LOGIN SOLO CLIENTES -->
    <?php if(!isset($esAdmin) && !isset($esVendedor)): ?>
    <a href="login.php" class="btn btn-dark ms-auto btn-sm">
        Iniciar Sesión
    </a>
    <?php endif; ?>

</div>
</nav>

<!-- CARRUSEL -->
<?php if(!$categoria): ?>

<div class="container my-4">

    <div id="carouselTienda"
         class="carousel slide"
         data-bs-ride="carousel">

        <!-- INDICADORES -->
        <div class="carousel-indicators">

            <button type="button"
                    data-bs-target="#carouselTienda"
                    data-bs-slide-to="0"
                    class="active">
            </button>

            <button type="button"
                    data-bs-target="#carouselTienda"
                    data-bs-slide-to="1">
            </button>

            <button type="button"
                    data-bs-target="#carouselTienda"
                    data-bs-slide-to="2">
            </button>

        </div>

        <!-- CONTENIDO -->
        <div class="carousel-inner rounded shadow overflow-hidden">

            <!-- SLIDE 1 -->
            <div class="carousel-item active">

                <div class="position-relative">

                    <img src="https://ovdivi.com/wp-content/uploads/2025/10/ov-carrusel-slider-productos-woocommerce-divi-wordpress_ov-divi.jpg"
                         class="d-block w-100"
                         style="height: 350px; object-fit: cover;"
                         alt="Productos de Temporada">

                    <div class="carousel-caption d-flex flex-column justify-content-center h-100">

                        <h1 class="fw-bold">
                            Productos de Temporada
                        </h1>

                        <p class="fs-5">
                            Descubre lo nuevo en moda hombre y mujer.
                        </p>

                    </div>

                </div>

            </div>

            <!-- SLIDE 2 -->
            <div class="carousel-item">

                <div class="position-relative">

                    <img src="https://addonmall.com/assets/uploads/2021/05/portada-carrusel.jpg"
                         class="d-block w-100"
                         style="height: 350px; object-fit: cover;"
                         alt="Ofertas Especiales">

                    <div class="carousel-caption d-flex flex-column justify-content-center h-100">

                        <h1 class="fw-bold">
                            Ofertas Especiales
                        </h1>

                        <p class="fs-5">
                            Aprovecha descuentos exclusivos esta semana.
                        </p>

                    </div>

                </div>

            </div>

            <!-- SLIDE 3 -->
            <div class="carousel-item">

                <div class="position-relative">

                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRJrH2O8s6AldY51wWWC83D5fv2c-DRS9dzWw&s"
                         class="d-block w-100"
                         style="height: 350px; object-fit: cover;"
                         alt="Productos Más Vendidos">

                    <div class="carousel-caption d-flex flex-column justify-content-center h-100">

                        <h1 class="fw-bold">
                            Productos Más Vendidos
                        </h1>

                        <p class="fs-5">
                            Los artículos favoritos de nuestros clientes.
                        </p>

                    </div>

                </div>

            </div>

        </div>

        <!-- CONTROLES -->
        <button class="carousel-control-prev"
                type="button"
                data-bs-target="#carouselTienda"
                data-bs-slide="prev">

            <span class="carousel-control-prev-icon"></span>

        </button>

        <button class="carousel-control-next"
                type="button"
                data-bs-target="#carouselTienda"
                data-bs-slide="next">

            <span class="carousel-control-next-icon"></span>

        </button>

    </div>

</div>

<?php endif; ?>

<!-- SUBCATEGORIAS -->
<?php if(($esHombre || $esMujer) && !$subcat): ?>
<div class="container my-4">
    <h3 class="mb-3">Categorias</h3>
    <div class="row g-3">
        <?php if($esHombre): ?>
        <div class="col-6 col-md-3">
            <a href="<?php echo isset($esAdmin) ? '?panel=tienda&cat=hombre&sub=camisas' : '?cat=hombre&sub=camisas'; ?>"
               class="btn btn-light w-100 py-4 fw-bold shadow">
                👕 Camisas
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="<?php echo isset($esAdmin) ? '?panel=tienda&cat=hombre&sub=pantalones' : '?cat=hombre&sub=pantalones'; ?>"
               class="btn btn-light w-100 py-4 fw-bold shadow">
                👖 Pantalones
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="<?php echo isset($esAdmin) ? '?panel=tienda&cat=hombre&sub=boxer' : '?cat=hombre&sub=boxer'; ?>"
               class="btn btn-light w-100 py-4 fw-bold shadow">
                🩲 Boxer
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="<?php echo isset($esAdmin) ? '?panel=tienda&cat=hombre&sub=sudadera' : '?cat=hombre&sub=sudadera'; ?>"
               class="btn btn-light w-100 py-4 fw-bold shadow">
                🧥 Sudadera/Sueter
            </a>
        </div>
        <?php elseif($esMujer): ?>
        <div class="col-6 col-md-3">
            <a href="<?php echo isset($esAdmin) ? '?panel=tienda&cat=mujer&sub=blusas' : '?cat=mujer&sub=blusas'; ?>"
               class="btn btn-light w-100 py-4 fw-bold shadow">
                👚 Blusas
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="<?php echo isset($esAdmin) ? '?panel=tienda&cat=mujer&sub=shorts' : '?cat=mujer&sub=shorts'; ?>"
               class="btn btn-light w-100 py-4 fw-bold shadow">
                🩳 Shorts
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="<?php echo isset($esAdmin) ? '?panel=tienda&cat=mujer&sub=vestidos' : '?cat=mujer&sub=vestidos'; ?>"
               class="btn btn-light w-100 py-4 fw-bold shadow">
                👗 Vestidos
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- PRODUCTOS -->
<div class="container py-4">

<div class="d-flex justify-content-between align-items-center mb-4">

    <h3 class="fw-bold text-white">

        <?php
            echo $subcat
                ? ucfirst($subcat)
                : "Productos Más Vendidos";
        ?>

    </h3>

   <?php if($subcat): ?>

<a href="<?php echo isset($esAdmin) ? '?panel=tienda&cat='.$categoria : '?cat='.$categoria; ?>"
   class="btn btn-outline-light btn-sm">

    ← Volver

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

?>

<?php if($mostrar): ?>

<div class="col">

<div class="card card-producto h-100 shadow border-0 p-3 align-items-center text-center">

    <img src="<?php echo $producto['img']; ?>"
         class="img-fluid mb-3"
         style="max-height: 150px; object-fit: contain;"
         alt="<?php echo $producto['nombre']; ?>">

    <div class="card-body w-100 p-0">

        <h5 class="fw-bold text-dark">
            <?php echo $producto['nombre']; ?>
        </h5>

        <h3 class="text-primary fw-bold">
            $<?php echo $producto['precio']; ?>
        </h3>

        <span class="badge bg-success bg-opacity-25 text-success mb-3">

            📦 <?php echo $producto['stock']; ?> disponibles

        </span>

        <div class="input-group mb-3 justify-content-center">

            <button class="btn btn-outline-secondary btn-restar">
                -
            </button>

            <input type="number"
                   class="form-control text-center cantidad-input"
                   value="1"
                   min="1">

            <button class="btn btn-outline-secondary btn-sumar">
                +
            </button>

        </div>

        <button class="btn btn-reservar w-100 text-white fw-bold">

            Reservar

        </button>

    </div>

</div>

</div>

<?php endif; ?>

<?php endforeach; ?>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>

document.querySelectorAll('.btn-sumar').forEach(button => {

    button.addEventListener('click', function(){

        let input = this.parentElement.querySelector('input');

        input.value = parseInt(input.value) + 1;

    });

});

document.querySelectorAll('.btn-restar').forEach(button => {

    button.addEventListener('click', function(){

        let input = this.parentElement.querySelector('input');

        if(parseInt(input.value) > 1){

            input.value = parseInt(input.value) - 1;

        }

    });

});

</script>

</body>
</html>