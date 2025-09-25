paypal.Buttons({
  style: {
    layout: 'vertical',
    color:  'gold',
    shape:  'rect',
    label:  'paypal'
  },



  // Crear la orden dinámicamente desde PHP
  createOrder: function(data, actions) {
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

  // Capturar el pago
  onApprove: function(data, actions) {
    return actions.order.capture().then(function(details) {
      alert('Pago completado por ' + details.payer.name.given_name);
      console.log(details);

      // 🔹 Opcional: enviar datos al servidor
      fetch('paypal/guardarPago.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(details)
      }).then(res => res.json())
        .then(data => console.log("Servidor respondió:", data))
        .catch(err => console.error("Error al guardar:", err));
    });
  },

  // Manejo de errores
  onError: function(err) {
    console.error(err);
    alert("Ocurrió un error al procesar el pago.");
  }

}).render('#paypal-button-container');
