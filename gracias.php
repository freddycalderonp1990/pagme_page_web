<?php
// archivo gracias.php

// Si llegas desde el JS vía POST
$order_id   = $_POST['order_id']   ?? 'N/A';
$payer_id   = $_POST['payer_id']   ?? 'N/A';
$payer_nombre = $_POST['payer_nombre'] ?? 'Cliente';
$payer_email= $_POST['payer_email']?? '---';
$producto   = $_POST['producto_titulo'] ?? 'Plan PagMe';
$descripcion= $_POST['producto_descripcion'] ?? '';
$precio     = $_POST['producto_precio'] ?? '0.00';
$duracion   = $_POST['producto_duracion'] ?? '---';
$moneda     = $_POST['moneda'] ?? 'USD';
$fecha_pago = $_POST['fecha_pago'] ?? date("Y-m-d H:i:s");
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

      <p>Gracias por tu compra, <strong><?= htmlspecialchars($payer_nombre) ?></strong>.</p>
      <p>Tu pago ha sido procesado correctamente.</p>

      <div class="alert alert-light border text-start">
        <strong>ID de Pago:</strong> <?= htmlspecialchars($order_id) ?><br>
        <strong>ID del Pagador:</strong> <?= htmlspecialchars($payer_id) ?><br>
        <strong>Email:</strong> <?= htmlspecialchars($payer_email) ?><br>
        <strong>Producto:</strong> <?= htmlspecialchars($producto) ?><br>
        <strong>Precio:</strong> <?= htmlspecialchars($precio) . " " . htmlspecialchars($moneda) ?><br>
        <strong>Duración:</strong> <?= htmlspecialchars($duracion) ?><br>
        <strong>Fecha de Pago:</strong> <?= htmlspecialchars($fecha_pago) ?>
      </div>

      <div class="d-flex justify-content-center gap-3 mt-4">
        <!-- Formulario para generar PDF -->
        <form action="recibo.php" method="POST" target="_blank">
          <input type="hidden" name="order_id" value="<?= htmlspecialchars($order_id) ?>">
          <input type="hidden" name="payer_id" value="<?= htmlspecialchars($payer_id) ?>">
          <input type="hidden" name="payer_nombre" value="<?= htmlspecialchars($payer_nombre) ?>">
          <input type="hidden" name="payer_email" value="<?= htmlspecialchars($payer_email) ?>">
          <input type="hidden" name="producto_titulo" value="<?= htmlspecialchars($producto) ?>">
          <input type="hidden" name="producto_descripcion" value="<?= htmlspecialchars($descripcion) ?>">
          <input type="hidden" name="producto_precio" value="<?= htmlspecialchars($precio) ?>">
          <input type="hidden" name="producto_duracion" value="<?= htmlspecialchars($duracion) ?>">
          <input type="hidden" name="moneda" value="<?= htmlspecialchars($moneda) ?>">
          <input type="hidden" name="fecha_pago" value="<?= htmlspecialchars($fecha_pago) ?>">
          <button type="submit" class="btn btn-dark">Descargar Recibo PDF</button>
        </form>

   
      </div>
    </div>
  </main>

</body>
</html>
