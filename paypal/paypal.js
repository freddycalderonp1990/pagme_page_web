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
                payer_email: details.payer.email_address || "----",
                fecha_pago: fecha_pago,
                estado_interno: "pendiente",
                ip: producto.ip,
                num_dias: producto.num_dias,
                token: producto.token,
                id_vendedor: merchantIdVendedor
              };

              // Formulario oculto para redirección a gracias.php
              const form = document.createElement("form");
              form.method = "POST";
              form.action = "gracias.php";

              // Añadir payload al form
              for (const key in payload) {
                const input = document.createElement("input");
                input.type = "hidden";
                input.name = key;
                input.value = payload[key];
                form.appendChild(input);
              }

              // Enviar a backend
              showLoader("Guardando tu pago...");
              fetch("paypal/guardarPago.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
              })
                .then(async res => {
                  const text = await res.text();
                  console.log("Respuesta cruda:", text);
                  try {
                    return JSON.parse(text);
                  } catch (e) {
                    throw new Error("Respuesta inválida: " + text);
                  }
                })
                .then(data => {
                  hideLoader();

                  // Input msj
                  const inputMsj = document.createElement("input");
                  inputMsj.type = "hidden";
                  inputMsj.name = "msj";
                  inputMsj.value = data.success
                    ? ""
                    : "Pago aprobado en PayPal pero no pudo ser guardado - " + (data.message || "Error desconocido");
                  form.appendChild(inputMsj);

                  // Input emailEnviado
                  const inputEmail = document.createElement("input");
                  inputEmail.type = "hidden";
                  inputEmail.name = "emailEnviado";
                  inputEmail.value = data.emailEnviado; // true o false
                  form.appendChild(inputEmail);

                  // Input idPagoPaypal
                  const inputIdPago = document.createElement("input");
                  inputIdPago.type = "hidden";
                  inputIdPago.name = "idPagoPaypal";
                  inputIdPago.value = data.success ? data.idPagoPaypal : 0;
                  form.appendChild(inputIdPago);

                  // Reforzar token en el form
                  const inputToken = document.createElement("input");
                  inputToken.type = "hidden";
                  inputToken.name = "token";
                  inputToken.value = payload.token;
                  form.appendChild(inputToken);

                  // Agregar form y enviar
                  document.body.appendChild(form);
                  form.submit();
                })
                .catch(err => {
                  hideLoader();

                  // Input msj
                  const inputMsj = document.createElement("input");
                  inputMsj.type = "hidden";
                  inputMsj.name = "msj";
                  inputMsj.value = "Pago aprobado en PayPal pero no pudo ser guardado (catch) - " + (err.message || err.toString());
                  form.appendChild(inputMsj);

                  // Reforzar token también en el catch
                  const inputToken = document.createElement("input");
                  inputToken.type = "hidden";
                  inputToken.name = "token";
                  inputToken.value = payload.token;
                  form.appendChild(inputToken);

                  document.body.appendChild(form);
                 form.submit();
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

// Loader básico
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
