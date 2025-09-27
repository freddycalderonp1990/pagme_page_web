function initPayPal(producto) {
  // Mostrar loader inicial
  showLoader("Cargando PayPal...");


  // 🚀 Flujo de PayPal
  fetch("paypal/config.php")
    .then(res => res.json())
    .then(config => {
      const script = document.createElement("script");
      script.src = `https://www.paypal.com/sdk/js?client-id=${config.client_id}&currency=USD`;
      script.onload = () => {
        paypal.Buttons({
          createOrder: (data, actions) => {
            //  showLoader("Creando orden en PayPal...");
            return actions.order.create({
              purchase_units: [{
                description: producto.titulo + " - " + producto.duracion,
                amount: {
                  currency_code: "USD",
                  value: producto.precio
                }
              }]
            });
          },

          onApprove: (data, actions) => {
            showLoader("Procesando tu pago en PayPal...");
            return actions.order.capture().then(details => {
              console.log("Detalles del pago:", details);

              const payerName = details.payer.name.given_name + " " + details.payer.name.surname;
              const fecha_pago = details.purchase_units[0].payments?.captures?.[0]?.create_time
                ? new Date(details.purchase_units[0].payments.captures[0].create_time)
                  .toISOString().slice(0, 19).replace("T", " ")
                : new Date().toISOString().slice(0, 19).replace("T", " ");

       

                 const merchantIdVendedor = details.purchase_units[0].payee.merchant_id;


              const payload = {
                id_empresa: producto.idEmpresa,
                order_id: details.id,
                status: details.status,
                producto_titulo: producto.titulo,
                producto_descripcion: producto.descripcion,
                producto_precio: details.purchase_units[0].amount.value,
                producto_duracion: producto.duracion,
                moneda: details.purchase_units[0].amount.currency_code,
                payer_id: details.payer.payer_id,
                payer_nombre: payerName,
                payer_email: details.payer.email_address || "---",
                fecha_pago: fecha_pago,
                estado_interno: "pendiente",
                ip: producto.ip,
                token: producto.token,
                merchantIdVendedor: merchantIdVendedor
              };

              // Guardar en servidor
              showLoader("Guardando tu pago...");
              fetch("paypal/guardarPago.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
              })
                .then(res => res.json())
                .then(data => {
                  hideLoader();
                  if (data.success) {
                    // Redirigir a gracias.php
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
                    console.log("⚠️ Pago aprobado en PayPal pero error guardando:", data);


                    const form = document.createElement("form");
                    form.method = "POST";
                    form.action = "error.php?motivo=guardar";
                    for (const key in payload) {
                      const input = document.createElement("input");
                      input.type = "hidden";
                      input.name = key;
                      input.value = payload[key];


                   
                    }
                        input.name = 'msj';
                      input.value = data.api_response;
                      form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();






                  //  window.location.href = "error.php?motivo=guardar";
                  }
                })
                .catch(err => {
                  console.error("❌ Error al guardar:", err);
                  hideLoader();
                  window.location.href = "error.php?motivo=api";
                });
            });
          },

          onError: err => {
            console.error("❌ Error en PayPal:", err);
            hideLoader();
            window.location.href = "error.php?motivo=paypal";
          }
        }).render("#paypal-button-container").then(() => {
          hideLoader(); // ✅ ocultar cuando el botón ya esté listo
        });
      };
      document.body.appendChild(script);
    })
    .catch(err => {
      console.error("Error cargando config.php:", err);
      hideLoader();
      window.location.href = "error.php?motivo=config";
    });
}

// Loader básico (puedes reemplazarlo por un spinner más pro)
function showLoader(mensaje = "Procesando pago...") {
  let loader = document.getElementById("loaderPago");
  if (!loader) {
    loader = document.createElement("div");
    loader.id = "loaderPago";
    loader.style.position = "fixed";
    loader.style.top = 0;
    loader.style.left = 0;
    loader.style.width = "100%";
    loader.style.height = "100%";
    loader.style.background = "rgba(255,255,255,0.9)";
    loader.style.display = "flex";
    loader.style.flexDirection = "column";
    loader.style.alignItems = "center";
    loader.style.justifyContent = "center";
    loader.style.zIndex = 9999;
    loader.innerHTML = `
      <div class="spinner-border text-primary" style="width:3rem;height:3rem;" role="status"></div>
      <p class="mt-3">${mensaje}</p>
    `;
    document.body.appendChild(loader);
  } else {
    loader.querySelector("p").textContent = mensaje;
    loader.style.display = "flex";
  }
}

function hideLoader() {
  const loader = document.getElementById("loaderPago");
  if (loader) loader.style.display = "none";
}
