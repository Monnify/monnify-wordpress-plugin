function payWithMonnify({
  amount,
  reference,
  customerName,
  customerEmail,
  customerMobileNumber,
  apiKey,
  contractCode,
  paymentMethods,
  redirect_url,
  currency,
  cart_url,
  incomeSplitConfig // <- added
}) {
  
  MonnifySDK.initialize({
    amount,
    currency,
    reference,
    customerName,
    customerEmail,
    customerMobileNumber,
    apiKey,
    contractCode,
    paymentDescription: "Make Payment with Monnify",
    paymentMethods, // Use the selected payment methods here

    // include income split config if provided
    ...(Array.isArray(incomeSplitConfig) && incomeSplitConfig.length ? { incomeSplitConfig } : {}),

    onLoadStart: () => {
       console.log("Sdk load started");
        console.log(incomeSplitConfig);

    },
    onLoadComplete: () => {
     console.log("SDK is UP");
    },
    onComplete: function (response) {
      jQuery("#wc-monnify-official-button").prop("disabled", true);
      jQuery("#cancel-btn").remove();
      jQuery("#seye").html(
        `<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">Your payment is being confirmed, please keep the page open while we process your order.</p>`
      );

      // safe redirect: prefer provided redirect_url, but fallback if missing or invalid ("-1")
      var redirectTarget = redirect_url;
      try {
        // if server passed a sentinel value or empty, fallback to known safe locations
        if (!redirectTarget || redirectTarget === '-1' || redirectTarget === 'false') {
          // prefer a localized return URL if provided by server
          redirectTarget = (window.woo_monnify_params && window.woo_monnify_params.return_url) || window.location.href;
        }
      } catch (e) {
        redirectTarget = window.location.href;
      }

      var sep = redirectTarget.indexOf('?') === -1 ? '?' : '&';
      var verifyUrl = redirectTarget + sep + 'mnfy_reference=' + encodeURIComponent(response.transactionReference);
      window.location.href = verifyUrl;
    },
    onClose: function (data) {
      // const str = JSON.stringify(data, null, 4);
      // console.log(str);
      // Payment modal closed without completion
      if (data.paymentStatus === 'USER_CANCELLED') {
        jQuery("#wc-monnify-official-button").prop("disabled", true);
        jQuery("#seye").html(
          `<p class="woocommerce-error">Your payment was not completed. You are being redirected to your cart to try again.</p>`
        );

        setTimeout(() => {
          window.location.href = cart_url;
        }, 3000);
      }
    }
  });
}

(function(){
  // early check whether script loaded
  if (typeof console !== 'undefined') {
    console.log('[monnify.js] loaded');
  }
})();

jQuery(function ($) {
  "use strict";

  try {
    // show whether localized params exist
    if (typeof window !== 'undefined') {
      console.log('[monnify.js] window.woo_monnify_params:', window.woo_monnify_params);
    } else {
      console.log('[monnify.js] window not available');
    }

    const wc_monnify_payment = {
      init: function () {
        const run_monnify = () => {
          const woo_monnify_params = window.woo_monnify_params || {};
          // explicit debug right before using params
          console.log('[monnify.js] run_monnify - raw woo_monnify_params:', woo_monnify_params);

          const {
            amount,
            first_name, 
            last_name,
            email,
            phone,
            key,
            selectedPaymentMethods,
            contractCode,
            txnref,
            testmode,
            mon_redirect_url,
            currency,
            cart_url,
            incomeSplitConfig // <- read from localized params
          } = woo_monnify_params;

          // debug immediate values
          console.log('[monnify.js] values:', { amount, txnref, contractCode, key, currency, cart_url });
          console.log('[monnify.js] incomeSplitConfig (localized):', incomeSplitConfig);

          // normalize/marshal incomeSplitConfig to proper types
          var normalizedSplit = [];
          if (Array.isArray(incomeSplitConfig)) {
            normalizedSplit = incomeSplitConfig.map(function(item) {
              var o = { subAccountCode: String(item.subAccountCode) };
              if (typeof item.splitPercentage !== 'undefined') o.splitPercentage = Number(item.splitPercentage);
              if (typeof item.splitAmount !== 'undefined') o.splitAmount = Number(item.splitAmount);
              if (typeof item.feePercentage !== 'undefined') o.feePercentage = Number(item.feePercentage);
              o.feeBearer = !!item.feeBearer;
              Object.keys(o).forEach(function(k){ if (typeof o[k] === 'undefined' || o[k] === null) delete o[k]; });
              return o;
            });
          }
          console.log('[monnify.js] incomeSplitConfig (normalized):', normalizedSplit);
          
          const customerName = `${first_name} ${last_name}`;
          const customerEmail = email;
          const customerMobileNumber = phone;

          payWithMonnify({
            amount: Number(amount),
            reference: txnref, // use the generated wp reference
            customerName,
            customerEmail,
            customerMobileNumber,
            apiKey: key,
            contractCode,
            paymentMethods: selectedPaymentMethods,
            redirect_url: mon_redirect_url,
            currency,
            cart_url,
            incomeSplitConfig: normalizedSplit // <- pass into SDK call
          });
        };

        $("#wc-monnify-official-button").click(function (e) {
          e.preventDefault();
          run_monnify();
        });

        // do not auto-run if you prefer manual click — keep for testing if needed
        // run_monnify();
      }
    };

    wc_monnify_payment.init();

  } catch (err) {
    console.error('[monnify.js] runtime error:', err);
  }
});

