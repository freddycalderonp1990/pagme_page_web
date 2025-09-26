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
      <h2 class="mb-3 text-danger">❌ Error en el Pago</h2>

      <!-- Logo -->
      <img src="assets/img/logo.png"
           alt="Logo PagMe"
           class="img-fluid mx-auto d-block mb-3"
           style="max-width: 120px;">

      <p class="text-muted"><?= htmlspecialchars($mensaje) ?></p>

      <div class="alert alert-danger border text-start">
        <strong>Motivo:</strong> <?= htmlspecialchars($motivo) ?><br>
        <small>Si el problema persiste, por favor contacta con soporte.</small>
      </div>


    </div>
  </main>

</body>
</html>
