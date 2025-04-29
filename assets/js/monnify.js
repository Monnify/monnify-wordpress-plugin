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
  currency
}) {

  // const reference = `${Math.floor(Math.random() * 1000000000 + 1)}`;
  
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
      // console.log(woo_monnify_params)
    },
    onLoadComplete: () => {
     //console.log("SDK is UP");
    },
    onComplete: function (response) {
      const transactionReference = response.transactionReference;
      window.location.href = `${redirect_url}&mnfy_reference=${transactionReference}`;
      jQuery("#wc-monnify-official-button").prop("disabled", true);
      jQuery("#cancel-btn").remove();
      jQuery("#seye").html(
        `<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">Please keep the page open while we process your order</p>`
      );
    },
    onClose: function (data) {
      // Implement what should happen when the modal is closed here
      // console.log(data);
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
          currency
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
          currency
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
