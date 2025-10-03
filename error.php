<?php
// archivo error.php

$motivo = $_GET['motivo'] ?? 'desconocido';

// Mapear mensajes personalizados
$mensajes = [
  'config'  => 'No se pudo cargar la configuración de PayPal.',
  'paypal'  => 'Ocurrió un error en PayPal al procesar tu pago.',
  'guardar' => 'El pago fue aprobado, pero no se guardó en el servidor.',
  'api'     => 'Error de comunicación con el servidor al guardar tu pago.',
];

$mensaje = $mensajes[$motivo] ?? 'Ocurrió un error inesperado en el proceso de pago.';





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

$msj                 = $data['msj'] ?? 'no llega';





$showBtnImprimir = false;

if (strlen($orderId) > 5) {
  $showBtnImprimir = true;
}


?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <title>Error en el Pago</title>
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

  <main class="d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow-sm text-center p-5" style="max-width:500px">
      <h2 class="mb-3 text-danger">❌ Error en el Pago. <?php echo $msj ?></h2>

      <!-- Logo -->
      <img src="assets/img/logo.png"
        alt="Logo PagMe"
        class="img-fluid mx-auto d-block mb-3"
        style="max-width: 120px;">

      <p class="text-muted"><?= htmlspecialchars($mensaje) ?></p>

      <div class="alert alert-danger border text-start">
        <strong>Motivo:</strong> <?= htmlspecialchars($motivo) ?><br>
        <small>
          Si el problema persiste, por favor contacte con soporte.<br>
          Descargue su recibo como respaldo y guárdelo antes de comunicarse.
        </small>
      </div>

      <?php if ($showBtnImprimir === true): ?>
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
      <?php endif; ?>



    </div>
  </main>

</body>

</html>