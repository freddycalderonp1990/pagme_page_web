function initPayPal(producto, ipCliente) {

  
  fetch('paypal/config.php')
    .then(res => res.json())
    .then(config => {
      const script = document.createElement("script");
      script.src = `https://www.paypal.com/sdk/js?client-id=${config.client_id}&currency=USD`;
      script.onload = () => {
        paypal.Buttons({
          createOrder: (data, actions) => actions.order.create({
            purchase_units: [{
              description: producto.titulo + " - " + producto.duracion,
              amount: {
                currency_code: "USD",
                value: producto.precio
              }
            }]
          }),

          onApprove: (data, actions) => actions.order.capture().then(details => {
            console.log("Detalles del pago:", details);

            const payerName = details.payer.name.given_name + " " + details.payer.name.surname;
            const fecha_pago = details.purchase_units[0].payments?.captures?.[0]?.create_time
              ? new Date(details.purchase_units[0].payments.captures[0].create_time)
                  .toISOString().slice(0, 19).replace("T", " ")
              : new Date().toISOString().slice(0, 19).replace("T", " ");

            const payload = {
              id_empresa: 1,
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
              fecha_pago,
              estado_interno: "pendiente",
              ip: ipCliente
            };

            fetch("paypal/guardarPago.php", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
              if (data.success) {
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
                alert("⚠️ Pago aprobado en PayPal pero no se guardó en el servidor");
                console.log(data);
              }
            })
            .catch(err => console.error("❌ Error al guardar en API:", err));
          }),

          onError: err => console.error("❌ Error en PayPal:", err)
        }).render('#paypal-button-container');
      };
      document.body.appendChild(script);
    })
    .catch(err => console.error("Error cargando config.php:", err));
}
