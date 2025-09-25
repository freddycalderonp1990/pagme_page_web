<?php
// Seguridad básica
$titulo = $_POST['titulo'] ?? 'Producto sin título';
$descripcion = $_POST['descripcion'] ?? 'Sin descripción';
$precio = $_POST['precio'] ?? '0.00';
$duracion = $_POST['duracion'] ?? 'N/A';
$token = $_POST['token'] ?? '';

// 🔹 Verificar token HMAC
$claveSecreta = "CLAVE_SECRETA"; // guárdala en .env
$tokenEsperado = hash_hmac('sha256', "$precio|$duracion", $claveSecreta);

if ($token !== $tokenEsperado) {
  //die("❌ Error: datos inválidos o manipulados.");
}

$titulo = 'Plan Pagme 6 Mese4s';
$descripcion =  'Paga por la app descripción';
$precio =  '10.00';
$duracion = '6 Meses';

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
            <img src="assets/img/logo.webp"
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
    const producto = {
      titulo: "<?= addslashes($titulo) ?>",
      descripcion: "<?= addslashes($descripcion) ?>",
      precio: "<?= $precio ?>",
      duracion: "<?= addslashes($duracion) ?>"
    };

    fetch('paypal/config.php')
      .then(res => res.json())
      .then(config => {
        const script = document.createElement("script");
        script.src = `https://www.paypal.com/sdk/js?client-id=${config.client_id}&currency=USD`;
        script.onload = () => {
          paypal.Buttons({
            createOrder: (data, actions) => actions.order.create({
              purchase_units: [{
                description: "<?= addslashes($titulo) ?> - <?= addslashes($duracion) ?>",
                amount: {
                  currency_code: "USD",
                  value: "<?= $precio ?>"
                }
              }]
            }),

            onApprove: (data, actions) => actions.order.capture().then(details => {

              console.log("Detalles del pago:", details);

              const payerName = details.payer.name.given_name + " " + details.payer.name.surname;

              const fecha_pago = details.purchase_units[0].payments?.captures?.[0]?.create_time ?
                new Date(details.purchase_units[0].payments.captures[0].create_time)
                .toISOString().slice(0, 19).replace("T", " ") :
                new Date().toISOString().slice(0, 19).replace("T", " ");

              const moneda = details.purchase_units[0].amount.currency_code;

              const producto_titulo = "<?= addslashes($titulo) ?>";
              const producto_descripcion = "<?= addslashes($descripcion) ?>";
              const producto_precio = details.purchase_units[0].amount.value;
              const producto_duracion = "<?= addslashes($duracion) ?>";

              const payer_id = details.payer.payer_id;
              const payer_email = details.payer.email_address || "---";

              // 🔹 Payload para backend
              const payload = {
                id_empresa: 1, // setea según usuario logueado
                order_id: details.id,
                status: details.status,
                producto_titulo,
                producto_descripcion,
                producto_precio,
                producto_duracion,
                moneda,
                payer_id,
                payer_nombre: payerName,
                payer_email,
                fecha_pago,
                estado_interno: "pendiente",
                ip: "<?= $_SERVER['REMOTE_ADDR'] ?>"
              };




              // 🔹 Enviar a tu backend local
              fetch("paypal/guardarPago.php", {
                  method: "POST",
                  headers: {
                    "Content-Type": "application/json"
                  },
                  body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                  console.log("Servidor respondió:", data);
                  if (data.success) {
                    //  alert("📌 Pago registrado con éxito ");
                    // opcional → redirigir a página de confirmación


                    const form = document.createElement("form");
                    form.method = "POST";
                    form.action = "gracias.php";



                    for (const key in payload) {
                      const input = document.createElement("input");
                      input.type = "hidden";
                      input.name = key;
                      input.value = payload[key];
                      form.appendChild(input);
                    }

                    document.body.appendChild(form);
                    form.submit();
                  } else {
                    // alert("⚠️ El pago fue aprobado en PayPal pero no se guardó");
                    console.log(data);
                  }
                })
                .catch(err => console.error("Error al guardar en API:", err));
            }),

            onError: err => console.error("❌ Error en PayPal:", err)
          }).render('#paypal-button-container');
        };
        document.body.appendChild(script);
      })
      .catch(err => console.error("Error cargando config.php:", err));
  </script>

</body>

</html>