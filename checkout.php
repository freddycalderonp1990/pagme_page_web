<?php
// Seguridad básica
$titulo = $_GET['titulo'] ?? 'Producto sin título';
$descripcion = $_GET['descripcion'] ?? 'Sin descripción';
$precio = $_GET['precio'] ?? '0.00';
$duracion = $_GET['duracion'] ?? 'N/A';
$token = $_GET['token'] ?? '';

  $titulo= 'PAGOS DESDE APP';
  $precio= '10.0';
  $duracion= '1 MES';
  $descripcion= 'PARA PRUEBAS DESDE LA WEB';

// 🔹 Verificar token HMAC
$claveSecreta = "-CPFN-8aef9d9879896d-underpro-646654ddb-PAGME-76313ef65-freddy"; // guárdala en .env

   $input=$titulo.$descripcion.$precio.$duracion;
    $input=$claveSecreta.$input.$claveSecreta;

 
    $tokenEsperado = hash('sha512', $input);



if ($token !== $tokenEsperado) {
  //die("❌ Error: datos inválidos o manipulados.");
}

/*
$titulo = 'Plan Pagme 6 Mese4s';
$descripcion =  'Paga por la app descripción';
$precio =  '10.00';
$duracion = '6 Meses';*/

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
    'titulo' => $titulo,
    'descripcion' => $descripcion,
    'precio' => $precio,
    'duracion' => $duracion
  ], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;

  const ipCliente = "<?= $_SERVER['REMOTE_ADDR'] ?>";
</script>

<!-- 1️⃣ Primero cargo paypal.js -->
<script src="paypal/paypal.js"></script>

<!-- 2️⃣ Después lo llamo -->
<script>
  initPayPal(producto, ipCliente);
</script>

</body>

</html>