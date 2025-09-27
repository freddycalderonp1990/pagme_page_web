<?php
// Cargar variables de entorno
require_once __DIR__ . '/paypal/env_loader.php';

// archivo gracias.php

// Leer el JSON del POST
$data = $_POST;

// Variables del payload
$idEmpresa           = $data['id_empresa'] ?? 1;
$orderId             = $data['order_id'] ?? '';
$status              = $data['status'] ?? '';

$productoTitulo      = $data['producto_titulo'] ?? 'Producto sin título';
$productoDescripcion = $data['producto_descripcion'] ?? 'Sin descripción';
$productoPrecio      = $data['producto_precio'] ?? '0.00';
$productoDuracion    = $data['producto_duracion'] ?? 'N/A';
$moneda              = $data['moneda'] ?? 'USD';
$payerId             = $data['payer_id'] ?? '';
$payerNombre         = $data['payer_nombre'] ?? '';
$payerEmail          = $data['payer_email'] ?? '---';
$fechaPago           = $data['fecha_pago'] ?? '';
$estadoInterno       = $data['estado_interno'] ?? 'pendiente';
$ipCliente           = $data['ip'] ?? $_SERVER['REMOTE_ADDR'];
$token               = $data['token'] ?? '';
$merchantIdVendedor               = $data['merchantIdVendedor'] ?? '';



$requiredToken = filter_var($_ENV['REQUIRED_TOKEN'] ?? 'false', FILTER_VALIDATE_BOOLEAN);

if ($requiredToken) {

  $tokenEsperado = validarToken(
    $idEmpresa,
    $productoTitulo,
    $productoDescripcion,
    $productoPrecio,
    $productoDuracion
  );

  if ($token !== $tokenEsperado) {
    http_response_code(401);

    die("❌ Error: datos inválidos o manipulados.");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <title>Pago Exitoso</title>
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

  <main class="d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow-sm text-center p-5" style="max-width:500px">
      <h2 class="mb-3">¡Pago Exitoso! 🎉</h2>

      <!-- Logo -->
      <img src="assets/img/logo.png"
        alt="Logo PagMe"
        class="img-fluid mx-auto d-block mb-3"
        style="max-width: 120px;">

      <p>Gracias por tu compra, <strong><?= htmlspecialchars($payerNombre) ?></strong>.</p>
      <p>Tu pago ha sido procesado correctamente.</p>

      <div class="alert alert-light border text-start">
        <strong>ID de Empresa:</strong> <?= htmlspecialchars($idEmpresa) ?><br>
        <strong>ID de Pago:</strong> <?= htmlspecialchars($orderId) ?><br>
        <strong>ID del Cliente:</strong> <?= htmlspecialchars($payerId) ?><br>
        <strong>ID Vendedor:</strong> <?= htmlspecialchars($merchantIdVendedor) ?><br>
        <strong>Email:</strong> <?= htmlspecialchars($payerEmail) ?><br>
        <strong>Producto:</strong> <?= htmlspecialchars($productoTitulo) ?><br>
        <strong>Precio:</strong> <?= htmlspecialchars($productoPrecio) . " " . htmlspecialchars($moneda) ?><br>
        <strong>Duración:</strong> <?= htmlspecialchars($productoDuracion) ?><br>
        <strong>Fecha de Pago:</strong> <?= htmlspecialchars($fechaPago) ?>
      </div>

      <div class="d-flex justify-content-center gap-3 mt-4">
        <!-- Formulario para generar PDF -->
        <form action="recibo.php" method="POST" target="_blank">
          <input type="hidden" name="id_empresa" value="<?= htmlspecialchars($idEmpresa) ?>">
          <input type="hidden" name="order_id" value="<?= htmlspecialchars($orderId) ?>">
          <input type="hidden" name="payer_id" value="<?= htmlspecialchars($payerId) ?>">
          <input type="hidden" name="merchantIdVendedor" value="<?= htmlspecialchars($merchantIdVendedor) ?>">
          <input type="hidden" name="payer_nombre" value="<?= htmlspecialchars($payerNombre) ?>">
          <input type="hidden" name="payer_email" value="<?= htmlspecialchars($payerEmail) ?>">
          <input type="hidden" name="producto_titulo" value="<?= htmlspecialchars($productoTitulo) ?>">
          <input type="hidden" name="producto_descripcion" value="<?= htmlspecialchars($productoDescripcion) ?>">
          <input type="hidden" name="producto_precio" value="<?= htmlspecialchars($productoPrecio) ?>">
          <input type="hidden" name="producto_duracion" value="<?= htmlspecialchars($productoDuracion) ?>">
          <input type="hidden" name="moneda" value="<?= htmlspecialchars($moneda) ?>">
          <input type="hidden" name="fecha_pago" value="<?= htmlspecialchars($fechaPago) ?>">
          <button type="submit" class="btn btn-dark">Descargar Recibo PDF</button>
        </form>
      </div>
    </div>
  </main>

</body>

</html>