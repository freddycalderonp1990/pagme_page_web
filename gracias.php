<?php
// Cargar variables de entorno
require_once __DIR__ . '/config/env_loader.php';
require_once __DIR__ . '/services/PagoValidator.php';




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
$id_vendedor               = $data['id_vendedor'] ?? '';

$msj = $data['msj'] ?? '';

$emailEnviado = $data['emailEnviado'] ?? false;

$idPagoPaypal = $data['idPagoPaypal'] ?? 0;




if (!PagoValidator::validarToken($data)) {

  if (strlen($orderId) < 5) {
    http_response_code(404);
    exit;
  }
  http_response_code(401);

  die("❌ Error: datos inválidos o manipulados.");
  exit;
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

<main class="d-flex justify-content-center align-items-start min-vh-100 py-5 bg-light" style="overflow-y:auto;">
  <div class="card shadow-sm text-center p-5 my-4" style="max-width:500px; width:100%;">
    <h2 class="mb-3">¡Pago Exitoso! 🎉</h2>

    <!-- Logo QR -->
    <img
      src="qr/generar_qr.php?id=<?= htmlspecialchars($orderId) ?>"
      alt="QR del pago PagMe"
      class="img-fluid mx-auto d-block mb-3"
      style="width:150px; height:150px; object-fit:contain; border-radius:8px;">

    <p>Gracias por tu compra, <strong><?= htmlspecialchars($payerNombre) ?></strong>.</p>
    <p>Tu pago ha sido procesado correctamente.</p>

    <div class="alert alert-light border text-start">
      <strong>ID de Empresa:</strong> <?= htmlspecialchars($idEmpresa) ?><br>
      <strong>ID de Pago:</strong> <?= htmlspecialchars($orderId) ?><br>
      <strong>ID del Cliente:</strong> <?= htmlspecialchars($payerId) ?><br>
      <strong>ID Vendedor:</strong> <?= htmlspecialchars($id_vendedor) ?><br>
      <strong>Email:</strong> <?= htmlspecialchars($payerEmail) ?><br>
      <strong>Producto:</strong> <?= htmlspecialchars($productoTitulo) ?><br>
      <strong>Precio:</strong> <?= htmlspecialchars($productoPrecio) . " " . htmlspecialchars($moneda) ?><br>
      <strong>Descripción:</strong> <?= htmlspecialchars($productoDescripcion) ?><br>
      <strong>Fecha de Pago:</strong> <?= htmlspecialchars($fechaPago) ?><br>
      <strong>ID Pago Interno:</strong> <?= htmlspecialchars($idPagoPaypal) ?>
    </div>

    <?php if ($emailEnviado === "true" || $emailEnviado === true): ?>
      <div class="alert alert-success text-start mt-3">
        ✅ El recibo de pago ha sido enviado a tu correo:
        <strong><?= htmlspecialchars($payerEmail) ?></strong>.
        Por favor revisa tu bandeja de entrada (y la carpeta de spam si no lo encuentras).
      </div>
    <?php else: ?>
      <div class="alert alert-danger text-start mt-3">
        ⚠️ Tu pago fue aprobado, pero no pudimos enviar el recibo al correo electrónico
        <strong><?= htmlspecialchars($payerEmail) ?></strong>.
        Puedes realizar una captura para su respaldo.
      </div>
    <?php endif; ?>

    <?php if (!empty($msj)): ?>
      <div class="alert alert-warning text-start mt-3">
        <strong>Observación:</strong><br>
        <?= htmlspecialchars($msj) ?>
      </div>
    <?php endif; ?>

    <div class="d-flex justify-content-center gap-3 mt-4">
      <form action="recibo.php" method="POST" target="_blank">
        <input type="hidden" name="id_empresa" value="<?= htmlspecialchars($idEmpresa) ?>">
        <input type="hidden" name="order_id" value="<?= htmlspecialchars($orderId) ?>">
        <input type="hidden" name="payer_id" value="<?= htmlspecialchars($payerId) ?>">
        <input type="hidden" name="id_vendedor" value="<?= htmlspecialchars($id_vendedor) ?>">
        <input type="hidden" name="payer_nombre" value="<?= htmlspecialchars($payerNombre) ?>">
        <input type="hidden" name="payer_email" value="<?= htmlspecialchars($payerEmail) ?>">
        <input type="hidden" name="producto_titulo" value="<?= htmlspecialchars($productoTitulo) ?>">
        <input type="hidden" name="producto_descripcion" value="<?= htmlspecialchars($productoDescripcion) ?>">
        <input type="hidden" name="producto_precio" value="<?= htmlspecialchars($productoPrecio) ?>">
        <input type="hidden" name="producto_duracion" value="<?= htmlspecialchars($productoDuracion) ?>">
        <input type="hidden" name="moneda" value="<?= htmlspecialchars($moneda) ?>">
        <input type="hidden" name="fecha_pago" value="<?= htmlspecialchars($fechaPago) ?>">
      </form>
    </div>
  </div>
</main>


</body>

</html>