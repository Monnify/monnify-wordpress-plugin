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
  cart_url
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
    onLoadStart: () => {
      //console.log("Sdk load started");
    },
    onLoadComplete: () => {
     //console.log("SDK is UP");
    },
    onComplete: function (response) {
      // const str = JSON.stringify(response, null, 4);
      // console.log(str);
      jQuery("#wc-monnify-official-button").prop("disabled", true);
      jQuery("#cancel-btn").remove();
      jQuery("#seye").html(
        `<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">Your payment is being confirmed, please keep the page open while we process your order.</p>`
      );

      // Payment not confirmed, will verify via API
      const verifyUrl = `${redirect_url}&mnfy_reference=${response.transactionReference}`;
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

jQuery(function ($) {
  "use strict";

  const wc_monnify_payment = {
    init: function () {
      const run_monnify = () => {
        const woo_monnify_params = window.woo_monnify_params || {};
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
          cart_url
        } = woo_monnify_params;

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
          paymentMethods: selectedPaymentMethods , // Use the selected payment methods here,
          redirect_url: mon_redirect_url,
          currency,
          cart_url
        });
      };

      $("#wc-monnify-official-button").click(function (e) {
        e.preventDefault();
        run_monnify();
      });

      run_monnify();
    }
  };

  wc_monnify_payment.init();
});

