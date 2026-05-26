jQuery(function ($) {
  "use strict";

  /**
   * Object to handle Monnify admin functions.
   */
  var wc_monnify_admin = {
    /**
     * Initialize.
     */
    init: function () {
      // Toggle api key settings.
      $(document.body).on(
        "change",
        "#woocommerce_monnify_testmode",
        function () {
          var test_secret_key = $("#woocommerce_monnify_test_secret_key")
              .parents("tr")
              .eq(0),
            test_public_key = $("#woocommerce_monnify_test_public_key")
              .parents("tr")
              .eq(0),
            test_contracts_key = $("#woocommerce_monnify_test_contracts_key")
              .parents("tr")
              .eq(0),
            live_secret_key = $("#woocommerce_monnify_live_secret_key")
              .parents("tr")
              .eq(0),
            live_public_key = $("#woocommerce_monnify_live_public_key")
              .parents("tr")
              .eq(0),
            live_contracts_key = $("#woocommerce_monnify_live_contracts_key")
              .parents("tr")
              .eq(0);

          if ($(this).is(":checked")) {
            test_secret_key.show();
            test_public_key.show();
            test_contracts_key.show();
            live_secret_key.hide();
            live_public_key.hide();
            live_contracts_key.hide();
          } else {
            test_secret_key.hide();
            test_public_key.hide();
            test_contracts_key.hide();
            live_secret_key.show();
            live_public_key.show();
            live_contracts_key.show();
          }
        }
      );

      $("#woocommerce_monnify_testmode").change();

      $(".wc-wc-monnify-payment-gateway-icons").select2({
        templateResult: formatMonnifyPaymentIcons,
        templateSelection: formatMonnifyPaymentIconDisplay
      });
    }
  };

  function formatMonnifyPaymentIcons(payment_method) {
    if (!payment_method.id) {
      return payment_method.text;
    }

    var $payment_method = $(
      '<span><img src=" ' +
        wc_monnify_admin_params.plugin_url +
        "/assets/images/" +
        payment_method.element.value.toLowerCase() +
        '.png" class="img-flag" style="height: 15px; weight:18px;" /> ' +
        payment_method.text +
        "</span>"
    );

    return $payment_method;
  }

  function formatMonnifyPaymentIconDisplay(payment_method) {
    return payment_method.text;
  }

  wc_monnify_admin.init();
});
