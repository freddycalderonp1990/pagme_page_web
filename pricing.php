<section id="pricing" class="pricing section">
      <div class="container section-title" data-aos="fade-up">
        <h2>PRECIOS</h2>
        <p>Los días gratis o comprados siempre se acumulan con tu nuevo plan.</p>
      </div>

      <div class="container">
        <div class="row justify-content-center gy-4">

          <!-- Plan Gratis -->
          <div class="col-lg-3 d-flex align-items-stretch">
            <div class="promo-card h-100" style="background: linear-gradient(135deg, #6c757d, #adb5bd);">
              <h3>Plan Gratis</h3>


              <h4 class="precio-ahora">
                <span class="monto">$0</span>
                <span class="detalle">/ mes</span>
              </h4>
              <p class="msj">✨ Empieza sin riesgo y prueba todas las funciones durante <strong>30 días</strong>.</p>
              <a href="javascript:void(0)" onclick="enviarWhatsApp('Plan Gratis $0')" class="buy-btn">✨ Empieza
                Gratis</a>
            </div>
          </div>

          <!-- Plan Normal -->
          <div class="col-lg-3 d-flex align-items-stretch">
            <div class="promo-card h-100" style="background: linear-gradient(135deg, #195ba6, #437357);">
              <h3>Plan Normal</h3>



              <h4 class="precio-ahora">
                <span class="monto">$8</span>
                <span class="detalle">/ mes</span>
              </h4>
              <p class="msj">🗓️ Este plan añade <strong>30 días</strong> a los que aún tengas disponibles.</p>
              <a href="javascript:void(0)" onclick="enviarWhatsApp('Plan Normal $8 - Mensual')" class="buy-btn">✅
                Suscribirme</a>
            </div>
          </div>

          <!-- Plan Trimestral -->
          <div class="col-lg-3 d-flex align-items-stretch">
            <div class="promo-card h-100" style="background: linear-gradient(135deg, #6A11CB, #2575FC);">
              <h3>Plan Trimestral</h3>
              <p class="precio-antes">Precio Normal: $24.00</p>

              <h4 class="precio-ahora">
                <span class="monto">$15</span>
                <span class="detalle">/ 3 meses</span>
              </h4>
              <p class="ahorro">🎉 Ahorras $9 (37%)</p>
              <p class="msj">🗓️ Este plan añade <strong>90 días</strong> a los que aún tengas disponibles.</p>
              <a href="javascript:void(0)" onclick="enviarWhatsApp('Plan Trimestral $15 - 3 Meses')" class="buy-btn">🔥
                Aprovechar Promo</a>
            </div>
          </div>

          <!-- Plan Semestral -->
          <div class="col-lg-3 d-flex align-items-stretch">
            <div class="promo-card h-100" style="background: linear-gradient(135deg, #FF512F, #F09819);">
              <h3>Plan Semestral</h3>
              <p class="precio-antes">Precio Normal: $48.00</p>



              <h4 class="precio-ahora">
                <span class="monto">$25</span>
                <span class="detalle">/ 6 meses</span>
              </h4>
              <p class="ahorro">🎉 Ahorras $23 (48%)</p>
              <p class="msj">🗓️ Este plan añade <strong>180 días</strong> a los que aún tengas disponibles.</p>
              <a href="javascript:void(0)" onclick="enviarWhatsApp('Plan Semestral $25 - 6 Meses')" class="buy-btn">⭐
                Suscribirme</a>
            </div>
          </div>

          <!-- Plan Anual -->
          <div class="col-lg-3 d-flex align-items-stretch">
            <div class="promo-card h-100" style="background: linear-gradient(135deg, #11998E, #38EF7D);">
              <h3>Plan Anual</h3>
              <p class="precio-antes">Precio Normal: $96.00</p>
              <h4 class="precio-ahora">
                <span class="monto">$45</span>
                <span class="detalle">/ año</span>
              </h4>
              <p class="ahorro">🎉 Ahorras $51 (53%)</p>
              <p class="msj">🗓️ Este plan añade <strong>365 días</strong> a los que aún tengas disponibles.</p>
              <a href="javascript:void(0)" onclick="enviarWhatsApp('Plan Anual $45 - 12 Meses')" class="buy-btn">💎
                Obtener Promo</a>
            </div>
          </div>

        </div>
      </div>
    </section>

<!-- SDK de PayPal -->
<script src="https://www.paypal.com/sdk/js?client-id=TU_CLIENT_ID&currency=USD"></script>

<script>
  paypal.Buttons({
    createOrder: function (data, actions) {
      return actions.order.create({
        purchase_units: [{
          description: "Plan Normal - PagMe",
          amount: {
            value: '8.00'
          }
        }]
      });
    },
    onApprove: function (data, actions) {
      return actions.order.capture().then(function (details) {
        alert('Gracias por tu compra, ' + details.payer.name.given_name + '!');
        // Aquí podrías redirigir a una página PHP para registrar el pago
        // location.href = "registrar_pago.php?order_id=" + data.orderID;
      });
    }
  }).render('#paypal-button-container-plan-normal');

  // Repite el bloque paypal.Buttons() para los otros planes con su valor y descripción
</script>
