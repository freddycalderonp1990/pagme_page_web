<?php

// Cargar variables de entorno
require_once __DIR__ . '/config/env_loader.php';
require_once __DIR__ . '/services/PagoValidator.php';







// Seguridad básica
$titulo = $_GET['titulo'] ?? 'Producto sin título';
$descripcion = $_GET['descripcion'] ?? 'Sin descripción';
$precio = $_GET['precio'] ?? '10.00';
$num_dias = $_GET['numDias'] ?? 30;
$idEmpresa = $_GET['idEmpresa'] ?? 1;
$ip = $_GET['ip'] ?? $_SERVER['REMOTE_ADDR'];
$tipoPlanCupon = $_GET['tipo'] ?? '';
$idPlanCupon = $_GET['id'] ?? '';


$token = $_GET['token'] ?? '';






// Seguridad básica
/*
$titulo      = "Plan Normal";
$descripcion = "30 días";
$precio      = "8.0";

$num_dias       = 30;
$token       = "c9b4fae557ea3c184bb4d8199ae12a1566e2323aaf5217a00d5311528eae90fe528816f48a4584ba416738427a98f2aa45a7b4e8ec37f74b8624e555afd348d3";
$idEmpresa   = "1";
$ip          = "mi ip";*/

$dataValidar['id_empresa'] = $idEmpresa;
$dataValidar['producto_titulo'] = $titulo;
$dataValidar['producto_descripcion'] = $descripcion;
$dataValidar['producto_precio'] = $precio;
$dataValidar['num_dias'] = $num_dias;
$dataValidar['token'] = $token;


if (!PagoValidator::validarToken($dataValidar)) {
  die("❌ Error: datos inválidos o manipulados.");
}





$producto = [
  'idEmpresa'   => $idEmpresa,
  'titulo'      => $titulo,
  'descripcion' => $descripcion,
  'precio'      => $precio,
  'num_dias'    => $num_dias,
  'token'       => $token,
  'tipoPlanCupon'       => $tipoPlanCupon,
  'idPlanCupon'       => $idPlanCupon,
  'ip'          => $ip
];




?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <title>PagMe - Checkout</title>
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

  <main class="main mt-5 pt-5">
    <section class="container my-5">
      <div class="row justify-content-center">
        <div class="col-lg-6">
          <div class="card shadow-sm text-center p-4">
            <h3 class="mb-3"><?= htmlspecialchars($titulo) ?></h3>


            <!-- Imagen con tamaño controlado -->
            <img src="assets/img/logo.png"
              alt="Logo PagMe"
              class="img-fluid mx-auto d-block mb-3"
              style="max-width: 120px;">

            <p class="text-muted"><?= htmlspecialchars($descripcion) ?></p>
            <p>
              <strong>Precio:</strong> <?= htmlspecialchars($precio) ?> USD<br>
              <strong>Duración:</strong> <?= htmlspecialchars($num_dias) ?> Días
            </p>
            <div id="paypal-button-container" class="d-flex justify-content-center mt-3"></div>
          </div>
        </div>
      </div>
    </section>
  </main>



  <script>
    const producto = <?= json_encode($producto, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  </script>

  <!-- 1️⃣ Primero cargo paypal.js -->
  <script src="paypal/paypal.js"></script>

  <!-- 2️⃣ Después lo llamo -->
  <script>
    initPayPal(producto);
  </script>

</body>

</html>