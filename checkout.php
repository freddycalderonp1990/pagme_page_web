<?php

// Cargar variables de entorno
require_once __DIR__ . '/config/env_loader.php';
require_once __DIR__ . '/services/PagoValidator.php';






    



// Seguridad básica
$titulo = $_GET['titulo'] ?? 'Producto sin título';
$descripcion = $_GET['descripcion'] ?? 'Sin descripción';
$precio = $_GET['precio'] ?? '10.00';
$duracion = $_GET['duracion'] ?? 'N/A';
$token = $_GET['token'] ?? '';
$idEmpresa = $_GET['idEmpresa'] ?? 1;
$ip = $_GET['ip'] ?? $_SERVER['REMOTE_ADDR'];




// Seguridad básica
/*
$titulo      = "PAGOS DESDE APP";
$descripcion = "PARA PRUEBAS DESDE LA WEB";
$precio      = "8.0";
$duracion    = "1 MES";
$token       = "1196fbc7aa5845df2cac52b7284056742d310de48ce7260c70f332c1f727d0c184f39f5de3411eb5e661c68df35842e8884343890ee59c6f8f25e870d93144e1";
$idEmpresa   = "10";
$ip          = "mi ip";*/

$dataValidar['id_empresa'] = $idEmpresa;
$dataValidar['producto_titulo'] = $titulo;
$dataValidar['producto_descripcion'] = $descripcion;
$dataValidar['producto_precio'] = $precio;
$dataValidar['producto_duracion'] = $duracion;
$dataValidar['token'] = $token;


if (!PagoValidator::validarToken($dataValidar)) {
  die("❌ Error: datos inválidos o manipulados.");
}




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
            <img src="assets/img/logo2.png"
              alt="Logo PagMe"
              class="img-fluid mx-auto d-block mb-3"
              style="max-width: 120px;">

            <p class="text-muted"><?= htmlspecialchars($descripcion) ?></p>
            <p>
              <strong>Precio:</strong> <?= htmlspecialchars($precio) ?> USD<br>
              <strong>Duración:</strong> <?= htmlspecialchars($duracion) ?>
            </p>
            <div id="paypal-button-container" class="d-flex justify-content-center mt-3"></div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <script>
    const producto = <?= json_encode([
                        'idEmpresa' => $idEmpresa,
                        'titulo' => $titulo,
                        'descripcion' => $descripcion,
                        'precio' => $precio,
                        'duracion' => $duracion,
                        'token' => $token,
                        'ip' => $ip
                      ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  </script>

  <!-- 1️⃣ Primero cargo paypal.js -->
  <script src="paypal/paypal.js"></script>

  <!-- 2️⃣ Después lo llamo -->
  <script>
    initPayPal(producto);
  </script>

</body>

</html>